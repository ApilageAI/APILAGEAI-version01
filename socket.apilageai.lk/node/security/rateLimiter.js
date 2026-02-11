/**
 * ApilageAI Enterprise Rate Limiter
 * 
 * Redis-backed rate limiting with multiple tiers:
 * - Socket event rate limiting
 * - API endpoint rate limiting
 * - Message submission rate limiting
 * - Brute force protection
 * 
 * @module security/rateLimiter
 */

'use strict';

// ============================================================
// Redis Client (optional)
// ============================================================
let RedisClient = null;
let redisInitAttempted = false;
let redisClient = null;

// ============================================================
// Configuration
// ============================================================
const config = {
    // Rate limit tiers
    limits: {
        // API endpoints
        api: {
            windowMs: parseInt(process.env.RATE_LIMIT_WINDOW_MS, 10) || 60000, // 1 minute
            maxRequests: parseInt(process.env.RATE_LIMIT_MAX_REQUESTS, 10) || 100,
        },
        // Socket events per second
        socket: {
            maxPerSecond: parseInt(process.env.RATE_LIMIT_SOCKET_MAX_PER_SECOND, 10) || 10,
        },
        // Message submission cooldown
        message: {
            cooldownMs: parseInt(process.env.RATE_LIMIT_MESSAGE_COOLDOWN_MS, 10) || 1000,
        },
        // Authentication attempts
        auth: {
            maxAttempts: parseInt(process.env.MAX_LOGIN_ATTEMPTS, 10) || 5,
            lockoutDurationMs: parseInt(process.env.LOGIN_LOCKOUT_DURATION_MS, 10) || 900000, // 15 minutes
        },
        // File uploads
        upload: {
            maxPerMinute: 10,
            maxPerHour: 50,
        },
        // AI requests
        ai: {
            maxPerMinute: 20,
            maxConcurrent: 3,
        },
    },
    
    // Key prefixes for rate limit storage
    keyPrefixes: {
        apiLimit: 'rl:api:',
        socketLimit: 'rl:socket:',
        messageLimit: 'rl:msg:',
        authAttempt: 'rl:auth:',
        authLockout: 'rl:lockout:',
        uploadLimit: 'rl:upload:',
        aiLimit: 'rl:ai:',
        concurrent: 'rl:concurrent:',
        blocked: 'rl:blocked:',
    },
};

const TRUST_PROXY = (process.env.TRUST_PROXY || '').toLowerCase() === 'true'
    || process.env.NODE_ENV === 'production';

function getRequestIp(req) {
    if (!req) return 'unknown';
    if (req.ip) return req.ip;
    if (TRUST_PROXY && req.headers && req.headers['x-forwarded-for']) {
        return String(req.headers['x-forwarded-for']).split(',')[0].trim();
    }
    return req.connection?.remoteAddress || req.socket?.remoteAddress || 'unknown';
}

// ============================================================
// In-Memory Store (Redis-free)
// ============================================================
const memoryStore = {
    kv: new Map(), // key -> { value, expiresAt }
    zsets: new Map(), // key -> { items: Array<{score, member}>, expiresAt }
};

function nowMs() {
    return Date.now();
}

function getKvEntry(key) {
    const entry = memoryStore.kv.get(key);
    if (!entry) return null;
    if (entry.expiresAt && entry.expiresAt <= nowMs()) {
        memoryStore.kv.delete(key);
        return null;
    }
    return entry;
}

function getZsetEntry(key) {
    const entry = memoryStore.zsets.get(key);
    if (!entry) return null;
    if (entry.expiresAt && entry.expiresAt <= nowMs()) {
        memoryStore.zsets.delete(key);
        return null;
    }
    return entry;
}

class MemoryPipeline {
    constructor(client) {
        this.client = client;
        this.ops = [];
    }
    zremrangebyscore(...args) { this.ops.push(['zremrangebyscore', args]); return this; }
    zcard(...args) { this.ops.push(['zcard', args]); return this; }
    zadd(...args) { this.ops.push(['zadd', args]); return this; }
    zrange(...args) { this.ops.push(['zrange', args]); return this; }
    expire(...args) { this.ops.push(['expire', args]); return this; }
    get(...args) { this.ops.push(['get', args]); return this; }
    setex(...args) { this.ops.push(['setex', args]); return this; }
    incr(...args) { this.ops.push(['incr', args]); return this; }
    decr(...args) { this.ops.push(['decr', args]); return this; }
    del(...args) { this.ops.push(['del', args]); return this; }
    async exec() {
        const results = this.ops.map(([method, args]) => {
            try {
                const value = this.client[method](...args);
                return [null, value];
            } catch (err) {
                return [err, null];
            }
        });
        this.ops = [];
        return results;
    }
}

class MemoryRedis {
    pipeline() { return new MemoryPipeline(this); }
    get(key) {
        const entry = getKvEntry(key);
        return entry ? entry.value : null;
    }
    setex(key, seconds, value) {
        memoryStore.kv.set(key, { value: String(value), expiresAt: nowMs() + (seconds * 1000) });
        return 'OK';
    }
    ttl(key) {
        const entry = getKvEntry(key);
        if (!entry) return -2;
        if (!entry.expiresAt) return -1;
        const ttl = Math.ceil((entry.expiresAt - nowMs()) / 1000);
        return ttl < 0 ? -2 : ttl;
    }
    incr(key) {
        const entry = getKvEntry(key);
        const next = (entry ? parseInt(entry.value, 10) || 0 : 0) + 1;
        memoryStore.kv.set(key, { value: String(next), expiresAt: entry ? entry.expiresAt : null });
        return next;
    }
    decr(key) {
        const entry = getKvEntry(key);
        const next = (entry ? parseInt(entry.value, 10) || 0 : 0) - 1;
        memoryStore.kv.set(key, { value: String(next), expiresAt: entry ? entry.expiresAt : null });
        return next;
    }
    del(...keys) {
        let count = 0;
        keys.forEach((key) => {
            if (memoryStore.kv.delete(key)) count += 1;
            if (memoryStore.zsets.delete(key)) count += 1;
        });
        return count;
    }
    expire(key, seconds) {
        const entry = memoryStore.kv.get(key);
        if (entry) {
            entry.expiresAt = nowMs() + (seconds * 1000);
            return 1;
        }
        const zset = memoryStore.zsets.get(key);
        if (zset) {
            zset.expiresAt = nowMs() + (seconds * 1000);
            return 1;
        }
        return 0;
    }
    zremrangebyscore(key, min, max) {
        const entry = getZsetEntry(key) || { items: [], expiresAt: null };
        const before = entry.items.length;
        entry.items = entry.items.filter((item) => item.score < min || item.score > max);
        memoryStore.zsets.set(key, entry);
        return before - entry.items.length;
    }
    zcard(key) {
        const entry = getZsetEntry(key);
        return entry ? entry.items.length : 0;
    }
    zadd(key, score, member) {
        const entry = getZsetEntry(key) || { items: [], expiresAt: null };
        entry.items.push({ score: Number(score), member: String(member) });
        memoryStore.zsets.set(key, entry);
        return 1;
    }
    zrange(key, start, stop, withScores) {
        const entry = getZsetEntry(key);
        if (!entry || entry.items.length === 0) return [];
        const sorted = entry.items.slice().sort((a, b) => a.score - b.score);
        const slice = sorted.slice(start, stop + 1);
        if (withScores === 'WITHSCORES') {
            const flat = [];
            slice.forEach((item) => {
                flat.push(item.member, String(item.score));
            });
            return flat;
        }
        return slice.map(item => item.member);
    }
    // Compatibility with Redis clients
    async quit() { return 'OK'; }
    disconnect() { /* no-op */ }
}

const memoryRedis = new MemoryRedis();
memoryRedis.status = 'ready';

async function initRedis() {
    if (redisClient && redisClient.status === 'ready') {
        return redisClient;
    }
    if (redisInitAttempted) {
        return redisClient;
    }
    redisInitAttempted = true;

    try {
        if (!RedisClient) {
            RedisClient = require('ioredis');
        }
    } catch (error) {
        console.warn('[Redis] ioredis not installed; using in-memory limiter');
        return null;
    }

    const redisUrl = String(process.env.REDIS_URL || '').trim();
    const redisHost = String(process.env.REDIS_HOST || '127.0.0.1').trim();
    const redisPort = parseInt(process.env.REDIS_PORT || '6379', 10);
    const redisPassword = process.env.REDIS_PASSWORD || undefined;
    const redisDb = Number.isFinite(parseInt(process.env.REDIS_DB || '0', 10))
        ? parseInt(process.env.REDIS_DB || '0', 10)
        : 0;
    const redisTls = String(process.env.REDIS_TLS || '').toLowerCase() === 'true';

    const options = {
        lazyConnect: true,
        enableReadyCheck: true,
        maxRetriesPerRequest: 1,
        retryStrategy(times) {
            return Math.min(times * 50, 1000);
        },
        reconnectOnError(err) {
            const message = err?.message || '';
            if (message.includes('READONLY')) {
                return 2;
            }
            return 1;
        },
    };

    if (redisTls) {
        options.tls = {};
    }

    let client;
    if (redisUrl) {
        client = new RedisClient(redisUrl, options);
    } else {
        client = new RedisClient({
            host: redisHost,
            port: redisPort,
            password: redisPassword,
            db: redisDb,
            ...options,
        });
    }

    client.on('error', (err) => {
        console.error('[Redis] Error:', err?.message || err);
    });
    client.on('ready', () => {
        console.log('[Redis] Ready for rate limiting');
    });

    try {
        await client.connect();
        redisClient = client;
        return redisClient;
    } catch (error) {
        console.warn('[Redis] Connection failed; using in-memory limiter:', error?.message || error);
        try { client.disconnect(); } catch (_) { }
        return null;
    }
}

function getRedisClient() {
    if (redisClient && redisClient.status === 'ready') {
        return redisClient;
    }
    return memoryRedis;
}

// ============================================================
// Rate Limiting Functions
// ============================================================

/**
 * Check and increment rate limit using sliding window
 * @param {string} key - Unique key for this limit (e.g., IP:endpoint)
 * @param {number} maxRequests - Maximum requests allowed
 * @param {number} windowMs - Time window in milliseconds
 * @returns {Promise<{allowed: boolean, remaining: number, resetIn: number, blocked: boolean}>}
 */
async function checkRateLimit(key, maxRequests, windowMs) {
    const redis = getRedisClient();
    
    // Fallback: if Redis is unavailable, allow the request (fail-open with logging)
    if (!redis) {
        console.warn('[RateLimit] Redis unavailable, allowing request (fail-open)');
        return { allowed: true, remaining: maxRequests, resetIn: 0, blocked: false };
    }

    try {
        const now = Date.now();
        const windowStart = now - windowMs;
        const fullKey = `${config.keyPrefixes.apiLimit}${key}`;

        // Use Redis transaction for atomicity
        const pipeline = redis.pipeline();
        
        // Remove old entries outside the window
        pipeline.zremrangebyscore(fullKey, 0, windowStart);
        
        // Count current entries in window
        pipeline.zcard(fullKey);
        
        // Add current request
        pipeline.zadd(fullKey, now, `${now}-${Math.random()}`);
        
        // Set expiry on the key
        pipeline.expire(fullKey, Math.ceil(windowMs / 1000) + 1);

        const results = await pipeline.exec();
        const currentCount = results[1][1] || 0;

        const allowed = currentCount < maxRequests;
        const remaining = Math.max(0, maxRequests - currentCount - 1);
        
        // Calculate reset time
        const oldestEntry = await redis.zrange(fullKey, 0, 0, 'WITHSCORES');
        const resetIn = oldestEntry.length >= 2 
            ? Math.max(0, windowMs - (now - parseInt(oldestEntry[1], 10)))
            : windowMs;

        return { allowed, remaining, resetIn, blocked: !allowed };
    } catch (error) {
        console.error('[RateLimit] Error:', error.message);
        // Fail-open on error
        return { allowed: true, remaining: maxRequests, resetIn: 0, blocked: false };
    }
}

/**
 * Check socket event rate limit (per-second sliding window)
 * @param {string} socketId - Socket ID
 * @param {string} eventName - Event name
 * @returns {Promise<{allowed: boolean, remaining: number}>}
 */
async function checkSocketRateLimit(socketId, eventName) {
    const redis = getRedisClient();
    
    if (!redis) {
        return { allowed: true, remaining: config.limits.socket.maxPerSecond };
    }

    try {
        const key = `${config.keyPrefixes.socketLimit}${socketId}:${eventName}`;
        const now = Date.now();
        const windowStart = now - 1000; // 1 second window

        const pipeline = redis.pipeline();
        pipeline.zremrangebyscore(key, 0, windowStart);
        pipeline.zcard(key);
        pipeline.zadd(key, now, `${now}-${Math.random()}`);
        pipeline.expire(key, 2);

        const results = await pipeline.exec();
        const currentCount = results[1][1] || 0;

        const allowed = currentCount < config.limits.socket.maxPerSecond;
        const remaining = Math.max(0, config.limits.socket.maxPerSecond - currentCount - 1);

        return { allowed, remaining };
    } catch (error) {
        console.error('[SocketRateLimit] Error:', error.message);
        return { allowed: true, remaining: config.limits.socket.maxPerSecond };
    }
}

/**
 * Check message submission rate limit (cooldown between messages)
 * @param {string} userId - User ID
 * @returns {Promise<{allowed: boolean, waitMs: number}>}
 */
async function checkMessageCooldown(userId) {
    const redis = getRedisClient();
    
    if (!redis) {
        return { allowed: true, waitMs: 0 };
    }

    try {
        const key = `${config.keyPrefixes.messageLimit}${userId}`;
        const now = Date.now();
        
        const lastMessage = await redis.get(key);
        
        if (lastMessage) {
            const elapsed = now - parseInt(lastMessage, 10);
            const cooldown = config.limits.message.cooldownMs;
            
            if (elapsed < cooldown) {
                return { allowed: false, waitMs: cooldown - elapsed };
            }
        }
        
        await redis.setex(key, Math.ceil(config.limits.message.cooldownMs / 1000) + 1, now);
        return { allowed: true, waitMs: 0 };
    } catch (error) {
        console.error('[MessageCooldown] Error:', error.message);
        return { allowed: true, waitMs: 0 };
    }
}

/**
 * Check and track authentication attempts (brute force protection)
 * @param {string} identifier - IP address or username
 * @returns {Promise<{allowed: boolean, attemptsRemaining: number, lockedUntil: number|null}>}
 */
async function checkAuthAttempt(identifier) {
    const redis = getRedisClient();
    
    if (!redis) {
        return { allowed: true, attemptsRemaining: config.limits.auth.maxAttempts, lockedUntil: null };
    }

    try {
        const lockoutKey = `${config.keyPrefixes.authLockout}${identifier}`;
        const attemptKey = `${config.keyPrefixes.authAttempt}${identifier}`;
        
        // Check if locked out
        const lockoutExpiry = await redis.ttl(lockoutKey);
        if (lockoutExpiry > 0) {
            return { 
                allowed: false, 
                attemptsRemaining: 0, 
                lockedUntil: Date.now() + (lockoutExpiry * 1000) 
            };
        }
        
        // Get current attempt count
        const attempts = parseInt(await redis.get(attemptKey) || '0', 10);
        const attemptsRemaining = Math.max(0, config.limits.auth.maxAttempts - attempts);
        
        return { allowed: true, attemptsRemaining, lockedUntil: null };
    } catch (error) {
        console.error('[AuthAttempt] Error:', error.message);
        return { allowed: true, attemptsRemaining: config.limits.auth.maxAttempts, lockedUntil: null };
    }
}

/**
 * Record a failed authentication attempt
 * @param {string} identifier - IP address or username
 * @returns {Promise<{locked: boolean, attemptsRemaining: number}>}
 */
async function recordFailedAuth(identifier) {
    const redis = getRedisClient();
    
    if (!redis) {
        return { locked: false, attemptsRemaining: config.limits.auth.maxAttempts };
    }

    try {
        const lockoutKey = `${config.keyPrefixes.authLockout}${identifier}`;
        const attemptKey = `${config.keyPrefixes.authAttempt}${identifier}`;
        const windowSeconds = Math.ceil(config.limits.auth.lockoutDurationMs / 1000);
        
        // Increment attempt count
        const attempts = await redis.incr(attemptKey);
        await redis.expire(attemptKey, windowSeconds);
        
        const attemptsRemaining = Math.max(0, config.limits.auth.maxAttempts - attempts);
        
        // If max attempts exceeded, create lockout
        if (attempts >= config.limits.auth.maxAttempts) {
            await redis.setex(lockoutKey, windowSeconds, 'locked');
            return { locked: true, attemptsRemaining: 0 };
        }
        
        return { locked: false, attemptsRemaining };
    } catch (error) {
        console.error('[RecordFailedAuth] Error:', error.message);
        return { locked: false, attemptsRemaining: config.limits.auth.maxAttempts };
    }
}

/**
 * Clear authentication attempts after successful login
 * @param {string} identifier - IP address or username
 */
async function clearAuthAttempts(identifier) {
    const redis = getRedisClient();
    
    if (!redis) return;

    try {
        const lockoutKey = `${config.keyPrefixes.authLockout}${identifier}`;
        const attemptKey = `${config.keyPrefixes.authAttempt}${identifier}`;
        
        await redis.del(lockoutKey, attemptKey);
    } catch (error) {
        console.error('[ClearAuthAttempts] Error:', error.message);
    }
}

/**
 * Check AI request rate limit
 * @param {string} userId - User ID
 * @returns {Promise<{allowed: boolean, remaining: number, concurrent: number}>}
 */
async function checkAIRateLimit(userId) {
    const redis = getRedisClient();
    
    if (!redis) {
        return { allowed: true, remaining: config.limits.ai.maxPerMinute, concurrent: 0 };
    }

    try {
        const minuteKey = `${config.keyPrefixes.aiLimit}minute:${userId}`;
        const concurrentKey = `${config.keyPrefixes.concurrent}ai:${userId}`;
        const now = Date.now();
        const windowStart = now - 60000;

        // Check per-minute limit
        const pipeline = redis.pipeline();
        pipeline.zremrangebyscore(minuteKey, 0, windowStart);
        pipeline.zcard(minuteKey);
        pipeline.get(concurrentKey);

        const results = await pipeline.exec();
        const minuteCount = results[1][1] || 0;
        const concurrentCount = parseInt(results[2][1] || '0', 10);

        const withinMinuteLimit = minuteCount < config.limits.ai.maxPerMinute;
        const withinConcurrentLimit = concurrentCount < config.limits.ai.maxConcurrent;
        const allowed = withinMinuteLimit && withinConcurrentLimit;

        const remaining = Math.max(0, config.limits.ai.maxPerMinute - minuteCount);

        return { allowed, remaining, concurrent: concurrentCount };
    } catch (error) {
        console.error('[AIRateLimit] Error:', error.message);
        return { allowed: true, remaining: config.limits.ai.maxPerMinute, concurrent: 0 };
    }
}

/**
 * Start tracking a concurrent AI request
 * @param {string} userId - User ID
 * @returns {Promise<string>} Request ID for tracking
 */
async function startAIRequest(userId) {
    const redis = getRedisClient();
    const requestId = `${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
    
    if (!redis) return requestId;

    try {
        const minuteKey = `${config.keyPrefixes.aiLimit}minute:${userId}`;
        const concurrentKey = `${config.keyPrefixes.concurrent}ai:${userId}`;
        const now = Date.now();

        const pipeline = redis.pipeline();
        pipeline.zadd(minuteKey, now, requestId);
        pipeline.expire(minuteKey, 61);
        pipeline.incr(concurrentKey);
        pipeline.expire(concurrentKey, 300); // 5 minute failsafe

        await pipeline.exec();
        return requestId;
    } catch (error) {
        console.error('[StartAIRequest] Error:', error.message);
        return requestId;
    }
}

/**
 * End tracking a concurrent AI request
 * @param {string} userId - User ID
 */
async function endAIRequest(userId) {
    const redis = getRedisClient();
    
    if (!redis) return;

    try {
        const concurrentKey = `${config.keyPrefixes.concurrent}ai:${userId}`;
        await redis.decr(concurrentKey);
    } catch (error) {
        console.error('[EndAIRequest] Error:', error.message);
    }
}

/**
 * Block an IP address
 * @param {string} ip - IP address to block
 * @param {number} durationSeconds - Block duration in seconds
 * @param {string} reason - Reason for blocking
 */
async function blockIP(ip, durationSeconds, reason) {
    const redis = getRedisClient();
    
    if (!redis) return;

    try {
        const key = `${config.keyPrefixes.blocked}ip:${ip}`;
        await redis.setex(key, durationSeconds, JSON.stringify({ reason, blockedAt: Date.now() }));
        console.warn(`[Security] IP blocked: ${ip}, reason: ${reason}, duration: ${durationSeconds}s`);
    } catch (error) {
        console.error('[BlockIP] Error:', error.message);
    }
}

/**
 * Check if an IP is blocked
 * @param {string} ip - IP address to check
 * @returns {Promise<{blocked: boolean, reason: string|null}>}
 */
async function isIPBlocked(ip) {
    const redis = getRedisClient();
    
    if (!redis) {
        return { blocked: false, reason: null };
    }

    try {
        const key = `${config.keyPrefixes.blocked}ip:${ip}`;
        const data = await redis.get(key);
        
        if (data) {
            const { reason } = JSON.parse(data);
            return { blocked: true, reason };
        }
        
        return { blocked: false, reason: null };
    } catch (error) {
        console.error('[IsIPBlocked] Error:', error.message);
        return { blocked: false, reason: null };
    }
}

// ============================================================
// Express Middleware
// ============================================================

/**
 * Express middleware for API rate limiting
 * @param {Object} options - Rate limit options
 * @returns {Function} Express middleware
 */
function apiRateLimiter(options = {}) {
    const windowMs = options.windowMs || config.limits.api.windowMs;
    const maxRequests = options.maxRequests || config.limits.api.maxRequests;
    const keyGenerator = options.keyGenerator || ((req) => {
        const ip = getRequestIp(req);
        return `${ip}:${req.path}`;
    });

    return async (req, res, next) => {
        try {
            const key = keyGenerator(req);
            
            // Check if IP is blocked
            const ip = getRequestIp(req);
            const blockStatus = await isIPBlocked(ip);
            if (blockStatus.blocked) {
                return res.status(403).json({
                    error: true,
                    message: 'Access denied',
                    code: 'IP_BLOCKED',
                });
            }
            
            const result = await checkRateLimit(key, maxRequests, windowMs);
            
            // Set rate limit headers
            res.set({
                'X-RateLimit-Limit': maxRequests,
                'X-RateLimit-Remaining': result.remaining,
                'X-RateLimit-Reset': Math.ceil(result.resetIn / 1000),
            });

            if (!result.allowed) {
                return res.status(429).json({
                    error: true,
                    message: 'Too many requests, please try again later',
                    code: 'RATE_LIMIT_EXCEEDED',
                    retryAfter: Math.ceil(result.resetIn / 1000),
                });
            }

            next();
        } catch (error) {
            console.error('[ApiRateLimiter] Middleware error:', error.message);
            next(); // Fail-open
        }
    };
}

/**
 * Create a stricter rate limiter for sensitive endpoints
 * @returns {Function} Express middleware
 */
function strictRateLimiter() {
    return apiRateLimiter({
        windowMs: 60000,
        maxRequests: 10,
    });
}

/**
 * Create an authentication rate limiter
 * @returns {Function} Express middleware
 */
function authRateLimiter() {
    return apiRateLimiter({
        windowMs: 900000, // 15 minutes
        maxRequests: 20,
        keyGenerator: (req) => {
            const ip = getRequestIp(req);
            return `auth:${ip}`;
        },
    });
}

// ============================================================
// Socket.IO Middleware
// ============================================================

/**
 * Socket.IO middleware for rate limiting events
 * @returns {Function} Socket.IO middleware
 */
function socketRateLimiter() {
    return async (socket, next) => {
        const originalEmit = socket.emit.bind(socket);
        const originalOn = socket.on.bind(socket);

        // Override socket.on to wrap event handlers with rate limiting
        socket.on = function(eventName, handler) {
            if (typeof handler !== 'function') {
                return originalOn(eventName, handler);
            }

            const wrappedHandler = async (...args) => {
                // Skip rate limiting for internal events
                const internalEvents = ['disconnect', 'error', 'connect', 'connecting'];
                if (internalEvents.includes(eventName)) {
                    return handler.apply(socket, args);
                }

                // Check socket rate limit
                const result = await checkSocketRateLimit(socket.id, eventName);
                
                if (!result.allowed) {
                    socket.emit('rate_limit_exceeded', {
                        event: eventName,
                        message: 'Too many events, please slow down',
                        code: 'SOCKET_RATE_LIMIT',
                    });
                    return;
                }

                return handler.apply(socket, args);
            };

            return originalOn(eventName, wrappedHandler);
        };

        next();
    };
}

// ============================================================
// Exports
// ============================================================
module.exports = {
    // Initialization
    getRedisClient,
    initRedis,
    
    // Core rate limiting
    checkRateLimit,
    checkSocketRateLimit,
    checkMessageCooldown,
    
    // Authentication protection
    checkAuthAttempt,
    recordFailedAuth,
    clearAuthAttempts,
    
    // AI request limiting
    checkAIRateLimit,
    startAIRequest,
    endAIRequest,
    
    // IP blocking
    blockIP,
    isIPBlocked,
    
    // Express middleware
    apiRateLimiter,
    strictRateLimiter,
    authRateLimiter,
    
    // Socket.IO middleware
    socketRateLimiter,
    
    // Configuration access
    config,
};
