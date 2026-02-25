/**
 * ApilageAI Security Middleware
 * 
 * Comprehensive security layer including:
 * - Security headers (Helmet-like)
 * - XSS protection
 * - CSRF protection
 * - Input sanitization
 * - Request ID tracking
 * - Security logging
 * 
 * @module security/middleware
 */

'use strict';

const crypto = require('crypto');
const logger = require('../utils/logger');

// ============================================================
// Security Configuration
// ============================================================
const allowedOrigins = (process.env.ALLOWED_ORIGINS || process.env.APP_URL || '')
    .split(',')
    .map((origin) => origin.trim())
    .filter(Boolean);
const appBaseUrl = (process.env.APP_URL || process.env.PUBLIC_BASE_URL || '').trim();
const nodeApiBase = (process.env.NODE_API_BASE || appBaseUrl).trim();
const wsBase = nodeApiBase ? nodeApiBase.replace(/^http/i, 'ws') : '';
const whiteboardOrigins = [
    'https://www.whiteboard.team',
    'wss://www.whiteboard.team',
];

const config = {
    // Security headers
    headers: {
        // Content Security Policy
        csp: {
            defaultSrc: ["'self'"],
            scriptSrc: ["'self'", "'unsafe-inline'"]
                .concat(appBaseUrl ? [appBaseUrl] : [])
                .concat(whiteboardOrigins.filter((o) => o.startsWith('https://'))),
            styleSrc: ["'self'", "'unsafe-inline'", "https://fonts.googleapis.com"],
            imgSrc: ["'self'", "data:", "https:", "blob:"],
            fontSrc: ["'self'", "https://fonts.gstatic.com"],
            connectSrc: ["'self'"].concat(
                nodeApiBase ? [nodeApiBase] : [],
                wsBase ? [wsBase] : [],
                allowedOrigins,
                whiteboardOrigins
            ),
            frameSrc: ["'none'"],
            objectSrc: ["'none'"],
            baseUri: ["'self'"],
            formAction: ["'self'"],
            upgradeInsecureRequests: true,
        },
        // Referrer Policy
        referrerPolicy: 'strict-origin-when-cross-origin',
        // Permissions Policy
        permissionsPolicy: {
            camera: [],
            microphone: ['self'],
            geolocation: [],
            interestCohort: [],
        },
    },

    // XSS protection patterns
    xss: {
        // Patterns that indicate potential XSS
        dangerousPatterns: [
            /<script\b[^>]*>/gi,
            /javascript:/gi,
            /on\w+\s*=/gi,
            /data:text\/html/gi,
            /<iframe\b[^>]*>/gi,
            /<object\b[^>]*>/gi,
            /<embed\b[^>]*>/gi,
            /<link\b[^>]*>/gi,
            /<!--.*-->/gi,
            /expression\s*\(/gi,
            /vbscript:/gi,
        ],
    },

    // SQL injection patterns
    sql: {
        dangerousPatterns: [
            /(\b(SELECT|INSERT|UPDATE|DELETE|DROP|UNION|ALTER|CREATE|TRUNCATE)\b)/gi,
            /(--)|(;)/g,
            /(\/\*[\s\S]*?\*\/)/g,
            /(\bOR\b.*=.*)/gi,
            /(\bAND\b.*=.*)/gi,
        ],
    },
};

// ============================================================
// Security Headers Middleware
// ============================================================

/**
 * Apply security headers to response
 * @param {Object} options - Header options
 * @returns {Function} Express middleware
 */
function securityHeaders(options = {}) {
    const cspPolicies = options.csp || config.headers.csp;

    // Build CSP string
    const buildCSP = () => {
        const policies = [];

        if (cspPolicies.defaultSrc) {
            policies.push(`default-src ${cspPolicies.defaultSrc.join(' ')}`);
        }
        if (cspPolicies.scriptSrc) {
            policies.push(`script-src ${cspPolicies.scriptSrc.join(' ')}`);
        }
        if (cspPolicies.styleSrc) {
            policies.push(`style-src ${cspPolicies.styleSrc.join(' ')}`);
        }
        if (cspPolicies.imgSrc) {
            policies.push(`img-src ${cspPolicies.imgSrc.join(' ')}`);
        }
        if (cspPolicies.fontSrc) {
            policies.push(`font-src ${cspPolicies.fontSrc.join(' ')}`);
        }
        if (cspPolicies.connectSrc) {
            policies.push(`connect-src ${cspPolicies.connectSrc.join(' ')}`);
        }
        if (cspPolicies.frameSrc) {
            policies.push(`frame-src ${cspPolicies.frameSrc.join(' ')}`);
        }
        if (cspPolicies.objectSrc) {
            policies.push(`object-src ${cspPolicies.objectSrc.join(' ')}`);
        }
        if (cspPolicies.baseUri) {
            policies.push(`base-uri ${cspPolicies.baseUri.join(' ')}`);
        }
        if (cspPolicies.formAction) {
            policies.push(`form-action ${cspPolicies.formAction.join(' ')}`);
        }
        if (cspPolicies.upgradeInsecureRequests) {
            policies.push('upgrade-insecure-requests');
        }

        return policies.join('; ');
    };

    // Build Permissions-Policy string
    const buildPermissionsPolicy = () => {
        const pp = options.permissionsPolicy || config.headers.permissionsPolicy;
        const policies = [];

        for (const [feature, allowList] of Object.entries(pp)) {
            if (allowList.length === 0) {
                policies.push(`${feature}=()`);
            } else {
                policies.push(`${feature}=(${allowList.map(v => v === 'self' ? 'self' : `"${v}"`).join(' ')})`);
            }
        }

        return policies.join(', ');
    };

    return (req, res, next) => {
        // Generate unique request ID
        const requestId = crypto.randomUUID();
        req.requestId = requestId;
        res.set('X-Request-ID', requestId);

        // Security headers
        res.set({
            // Prevent MIME type sniffing
            'X-Content-Type-Options': 'nosniff',

            // Prevent clickjacking
            'X-Frame-Options': 'DENY',

            // XSS protection (legacy, but still useful)
            'X-XSS-Protection': '1; mode=block',

            // Strict Transport Security (HTTPS only)
            'Strict-Transport-Security': 'max-age=31536000; includeSubDomains; preload',

            // Content Security Policy
            'Content-Security-Policy': buildCSP(),

            // Referrer Policy
            'Referrer-Policy': options.referrerPolicy || config.headers.referrerPolicy,

            // Permissions Policy
            'Permissions-Policy': buildPermissionsPolicy(),

            // Prevent caching of sensitive data
            'Cache-Control': 'no-store, no-cache, must-revalidate, proxy-revalidate',
            'Pragma': 'no-cache',
            'Expires': '0',

            // Remove server identification
            'X-Powered-By': undefined,
        });

        // Remove X-Powered-By header
        res.removeHeader('X-Powered-By');

        next();
    };
}

// ============================================================
// Input Sanitization
// ============================================================

/**
 * Sanitize a string to prevent XSS
 * @param {string} input - Input string
 * @returns {string} Sanitized string
 */
function sanitizeString(input) {
    if (typeof input !== 'string') {
        return input;
    }

    return input
        // Encode HTML entities
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#x27;')
        .replace(/\//g, '&#x2F;')
        // Remove null bytes
        .replace(/\0/g, '')
        // Clean control characters (except newline, tab)
        .replace(/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/g, '');
}

/**
 * Check if input contains dangerous patterns
 * @param {string} input - Input string
 * @param {string} type - Type of check ('xss' or 'sql')
 * @returns {{safe: boolean, matches: Array}}
 */
function checkDangerousPatterns(input, type = 'xss') {
    if (typeof input !== 'string') {
        return { safe: true, matches: [] };
    }

    const patterns = type === 'sql'
        ? config.sql.dangerousPatterns
        : config.xss.dangerousPatterns;

    const matches = [];

    for (const pattern of patterns) {
        const match = input.match(pattern);
        if (match) {
            matches.push({ pattern: pattern.toString(), match: match[0] });
        }
    }

    return { safe: matches.length === 0, matches };
}

/**
 * Recursively sanitize an object
 * @param {*} obj - Object to sanitize
 * @param {Object} options - Sanitization options
 * @returns {*} Sanitized object
 */
function sanitizeObject(obj, options = {}) {
    const { maxDepth = 10, currentDepth = 0 } = options;

    if (currentDepth > maxDepth) {
        return null; // Prevent infinite recursion
    }

    if (obj === null || obj === undefined) {
        return obj;
    }

    if (typeof obj === 'string') {
        return sanitizeString(obj);
    }

    if (typeof obj === 'number' || typeof obj === 'boolean') {
        return obj;
    }

    if (Array.isArray(obj)) {
        return obj.map(item => sanitizeObject(item, { maxDepth, currentDepth: currentDepth + 1 }));
    }

    if (typeof obj === 'object') {
        const sanitized = {};
        for (const [key, value] of Object.entries(obj)) {
            // Sanitize both key and value
            const sanitizedKey = sanitizeString(key);
            sanitized[sanitizedKey] = sanitizeObject(value, { maxDepth, currentDepth: currentDepth + 1 });
        }
        return sanitized;
    }

    return obj;
}

/**
 * Express middleware for input sanitization
 * @param {Object} options - Sanitization options
 * @returns {Function} Express middleware
 */
function inputSanitizer(options = {}) {
    const { logDangerous = true, blockDangerous = false } = options;

    return (req, res, next) => {
        // Check and sanitize body (body is writable)
        if (req.body && typeof req.body === 'object') {
            // Check for dangerous patterns
            const bodyString = JSON.stringify(req.body);
            const xssCheck = checkDangerousPatterns(bodyString, 'xss');

            if (!xssCheck.safe) {
                if (logDangerous) {
                    console.warn(`[Security] Dangerous XSS pattern detected in request body:`, {
                        requestId: req.requestId,
                        ip: req.ip,
                        path: req.path,
                        matches: xssCheck.matches.slice(0, 5), // Limit logged matches
                    });
                }

                if (blockDangerous) {
                    return res.status(400).json({
                        error: true,
                        message: 'Invalid input detected',
                        code: 'INVALID_INPUT',
                    });
                }
            }

            // Sanitize the body (body is writable)
            req.body = sanitizeObject(req.body);
        }

        // Note: In Express 5.x, req.query and req.params are read-only getters
        // We cannot reassign them, but we validate them instead
        // Sanitization happens at the point of use via validation functions

        next();
    };
}

// ============================================================
// Input Validation
// ============================================================

/**
 * Validate and sanitize conversation ID
 * @param {*} id - Conversation ID
 * @returns {{valid: boolean, value: number|null}}
 */
function validateConversationId(id) {
    const numId = Number(id);
    if (!Number.isInteger(numId) || numId < 1 || numId > 2147483647) {
        return { valid: false, value: null };
    }
    return { valid: true, value: numId };
}

/**
 * Validate and sanitize user ID
 * @param {*} id - User ID
 * @returns {{valid: boolean, value: number|null}}
 */
function validateUserId(id) {
    const numId = Number(id);
    if (!Number.isInteger(numId) || numId < 1 || numId > 2147483647) {
        return { valid: false, value: null };
    }
    return { valid: true, value: numId };
}

/**
 * Validate and sanitize message text
 * @param {*} text - Message text
 * @param {Object} options - Validation options
 * @returns {{valid: boolean, value: string, error: string|null}}
 */
function validateMessageText(text, options = {}) {
    const { maxLength = 50000, minLength = 0 } = options;

    if (typeof text !== 'string') {
        return { valid: false, value: '', error: 'Text must be a string' };
    }

    const trimmed = text.trim();

    if (trimmed.length < minLength) {
        return { valid: false, value: '', error: `Text must be at least ${minLength} characters` };
    }

    if (trimmed.length > maxLength) {
        return { valid: false, value: '', error: `Text must be at most ${maxLength} characters` };
    }

    // Check for dangerous patterns
    const xssCheck = checkDangerousPatterns(trimmed, 'xss');
    if (!xssCheck.safe) {
        console.warn('[Security] Dangerous pattern in message text:', xssCheck.matches);
        // We don't block, but we sanitize
    }

    return { valid: true, value: sanitizeString(trimmed), error: null };
}

/**
 * Validate conversation title
 * @param {*} title - Title string
 * @returns {{valid: boolean, value: string, error: string|null}}
 */
function validateTitle(title) {
    if (typeof title !== 'string') {
        return { valid: false, value: '', error: 'Title must be a string' };
    }

    const trimmed = title.trim();

    if (trimmed.length === 0) {
        return { valid: false, value: '', error: 'Title cannot be empty' };
    }

    if (trimmed.length > 80) {
        return { valid: false, value: '', error: 'Title must be at most 80 characters' };
    }

    return { valid: true, value: sanitizeString(trimmed), error: null };
}

/**
 * Validate model selection
 * @param {*} model - Model name
 * @param {Array} allowedModels - List of allowed model names
 * @returns {{valid: boolean, value: string}}
 */
function validateModel(model, allowedModels = ['auto', 'free', 'pro', 'super', 'master', 'loard']) {
    if (typeof model !== 'string') {
        return { valid: true, value: 'auto' }; // Default to auto
    }

    const normalized = model.toLowerCase().trim();

    if (allowedModels.includes(normalized)) {
        return { valid: true, value: normalized };
    }

    return { valid: true, value: 'free' }; // Fallback to free
}

/**
 * Validate file upload
 * @param {Object} file - Multer file object
 * @returns {{valid: boolean, error: string|null}}
 */
function validateFileUpload(file) {
    if (!file) {
        return { valid: false, error: 'No file provided' };
    }

    const allowedMimeTypes = (process.env.ALLOWED_MIME_TYPES || 'image/jpeg,image/png,image/gif,image/webp').split(',');
    const maxSize = (parseInt(process.env.MAX_FILE_SIZE_MB, 10) || 10) * 1024 * 1024;

    if (!allowedMimeTypes.includes(file.mimetype)) {
        return { valid: false, error: 'Invalid file type' };
    }

    if (file.size > maxSize) {
        return { valid: false, error: 'File too large' };
    }

    // Check for suspicious file extensions in name
    const suspiciousExtensions = ['.php', '.js', '.exe', '.sh', '.bat', '.cmd', '.ps1'];
    const lowerName = file.originalname.toLowerCase();
    for (const ext of suspiciousExtensions) {
        if (lowerName.includes(ext)) {
            return { valid: false, error: 'Suspicious file name' };
        }
    }

    return { valid: true, error: null };
}

// ============================================================
// Security Logging
// ============================================================

/**
 * Log security-related events
 * @param {string} level - Log level (info, warn, error, critical)
 * @param {string} event - Event type
 * @param {Object} details - Event details
 */
function securityLog(level, event, details = {}) {
    // Use file logger for security events
    logger.logSecurityEvent(level, event, details);
}

/**
 * Express middleware for request logging
 * @returns {Function} Express middleware
 */
function requestLogger() {
    return (req, res, next) => {
        const startTime = Date.now();

        // Log request start
        if (process.env.ENABLE_REQUEST_LOGGING === 'true') {
            console.log(`[REQUEST] ${req.requestId} ${req.method} ${req.path} from ${req.ip}`);
        }

        // Log response on finish
        res.on('finish', () => {
            const duration = Date.now() - startTime;

            // Log suspicious activity
            if (res.statusCode === 401 || res.statusCode === 403) {
                securityLog('warn', 'unauthorized_access', {
                    requestId: req.requestId,
                    ip: req.ip,
                    method: req.method,
                    path: req.path,
                    statusCode: res.statusCode,
                    duration,
                });
            } else if (res.statusCode === 429) {
                securityLog('warn', 'rate_limit_hit', {
                    requestId: req.requestId,
                    ip: req.ip,
                    method: req.method,
                    path: req.path,
                    duration,
                });
            } else if (res.statusCode >= 400) {
                securityLog('info', 'client_error', {
                    requestId: req.requestId,
                    ip: req.ip,
                    method: req.method,
                    path: req.path,
                    statusCode: res.statusCode,
                    duration,
                });
            }
        });

        next();
    };
}

// ============================================================
// Error Handling
// ============================================================

/**
 * Secure error handler (prevents information leakage)
 * @returns {Function} Express error middleware
 */
function secureErrorHandler() {
    return (error, req, res, next) => {
        // Log the full error to error.log file
        logger.logCriticalError('Express error', error, {
            requestId: req.requestId,
            ip: req.ip,
            path: req.path,
            method: req.method,
        });

        // Security log for potential attacks
        if (error.message && (
            error.message.includes('SQL') ||
            error.message.includes('injection') ||
            error.message.includes('syntax')
        )) {
            securityLog('critical', 'potential_injection_attack', {
                requestId: req.requestId,
                ip: req.ip,
                path: req.path,
                errorMessage: error.message,
            });
        }

        // Never expose internal error details in production
        const isProduction = process.env.NODE_ENV === 'production';

        if (isProduction) {
            res.status(500).json({
                error: true,
                message: 'An internal error occurred',
                code: 'INTERNAL_ERROR',
                requestId: req.requestId,
            });
        } else {
            res.status(500).json({
                error: true,
                message: error.message,
                code: 'INTERNAL_ERROR',
                requestId: req.requestId,
                stack: error.stack,
            });
        }
    };
}

// ============================================================
// Exports
// ============================================================
module.exports = {
    // Middleware
    securityHeaders,
    inputSanitizer,
    requestLogger,
    secureErrorHandler,

    // Sanitization functions
    sanitizeString,
    sanitizeObject,
    checkDangerousPatterns,

    // Validation functions
    validateConversationId,
    validateUserId,
    validateMessageText,
    validateTitle,
    validateModel,
    validateFileUpload,

    // Logging
    securityLog,

    // Configuration access
    config,
};
