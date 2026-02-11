/**
 * ApilageAI Performance Cache
 * 
 * In-memory caching with TTL for frequently accessed data
 * Reduces database load significantly
 * 
 * @module performance/cache
 */

'use strict';

// ============================================================
// LRU Cache Implementation
// ============================================================
class LRUCache {
    constructor(options = {}) {
        this.maxSize = options.maxSize || 1000;
        this.defaultTTL = options.defaultTTL || 300000; // 5 minutes
        this.cache = new Map();
        this.hits = 0;
        this.misses = 0;

        // Cleanup expired entries every minute
        this.cleanupInterval = setInterval(() => this.cleanup(), 60000);
    }

    /**
     * Get value from cache
     * @param {string} key - Cache key
     * @returns {*} Cached value or undefined
     */
    get(key) {
        const entry = this.cache.get(key);

        if (!entry) {
            this.misses++;
            return undefined;
        }

        // Check if expired
        if (Date.now() > entry.expiresAt) {
            this.cache.delete(key);
            this.misses++;
            return undefined;
        }

        this.hits++;

        // Move to end (most recently used)
        this.cache.delete(key);
        this.cache.set(key, entry);

        return entry.value;
    }

    /**
     * Set value in cache
     * @param {string} key - Cache key
     * @param {*} value - Value to cache
     * @param {number} ttl - Time to live in ms (optional)
     */
    set(key, value, ttl = this.defaultTTL) {
        // Remove oldest if at capacity
        if (this.cache.size >= this.maxSize) {
            const oldestKey = this.cache.keys().next().value;
            this.cache.delete(oldestKey);
        }

        this.cache.set(key, {
            value,
            expiresAt: Date.now() + ttl,
            createdAt: Date.now(),
        });
    }

    /**
     * Delete from cache
     * @param {string} key - Cache key
     */
    delete(key) {
        this.cache.delete(key);
    }

    /**
     * Delete by prefix
     * @param {string} prefix - Key prefix
     */
    deleteByPrefix(prefix) {
        for (const key of this.cache.keys()) {
            if (key.startsWith(prefix)) {
                this.cache.delete(key);
            }
        }
    }

    /**
     * Clear all cache
     */
    clear() {
        this.cache.clear();
    }

    /**
     * Cleanup expired entries
     */
    cleanup() {
        const now = Date.now();
        for (const [key, entry] of this.cache.entries()) {
            if (now > entry.expiresAt) {
                this.cache.delete(key);
            }
        }
    }

    /**
     * Get cache statistics
     */
    getStats() {
        const total = this.hits + this.misses;
        return {
            size: this.cache.size,
            maxSize: this.maxSize,
            hits: this.hits,
            misses: this.misses,
            hitRate: total > 0 ? (this.hits / total * 100).toFixed(2) + '%' : '0%',
        };
    }

    /**
     * Destroy cache and cleanup interval
     */
    destroy() {
        if (this.cleanupInterval) {
            clearInterval(this.cleanupInterval);
        }
        this.cache.clear();
    }
}

// ============================================================
// Cache Instances
// ============================================================

// User data cache (balance, profile info)
const userCache = new LRUCache({
    maxSize: 500,
    defaultTTL: 60000, // 1 minute
});

// Conversation cache
const conversationCache = new LRUCache({
    maxSize: 200,
    defaultTTL: 120000, // 2 minutes
});

// Session validation cache
const sessionCache = new LRUCache({
    maxSize: 1000,
    defaultTTL: 30000, // 30 seconds
});

// Model selection cache (for auto mode)
const modelCache = new LRUCache({
    maxSize: 100,
    defaultTTL: 300000, // 5 minutes
});

// ============================================================
// Cache Helpers
// ============================================================

/**
 * Get or set pattern - fetch from cache or execute function
 * @param {LRUCache} cache - Cache instance
 * @param {string} key - Cache key
 * @param {Function} fetchFn - Function to call on cache miss
 * @param {number} ttl - TTL in ms
 * @returns {Promise<*>} Cached or fetched value
 */
async function getOrSet(cache, key, fetchFn, ttl) {
    const cached = cache.get(key);
    if (cached !== undefined) {
        return cached;
    }

    const value = await fetchFn();
    cache.set(key, value, ttl);
    return value;
}

/**
 * Invalidate user-related caches
 * @param {number} userId - User ID
 */
function invalidateUserCache(userId) {
    userCache.delete(`user:${userId}`);
    userCache.delete(`balance:${userId}`);
    sessionCache.deleteByPrefix(`session:${userId}:`);
}

/**
 * Invalidate conversation-related caches
 * @param {number} conversationId - Conversation ID
 */
function invalidateConversationCache(conversationId) {
    conversationCache.deleteByPrefix(`conv:${conversationId}`);
}

// ============================================================
// Exports
// ============================================================
module.exports = {
    LRUCache,
    userCache,
    conversationCache,
    sessionCache,
    modelCache,
    getOrSet,
    invalidateUserCache,
    invalidateConversationCache,

    // Get all stats
    getAllStats: () => ({
        user: userCache.getStats(),
        conversation: conversationCache.getStats(),
        session: sessionCache.getStats(),
        model: modelCache.getStats(),
    }),
};
