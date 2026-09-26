/**
 * ApilageAI Security Module Index
 * 
 * Central export for all security-related modules
 * 
 * @module security
 */

'use strict';

const rateLimiter = require('./rateLimiter');
const middleware = require('./middleware');

// Re-export all modules
module.exports = {
    // Rate Limiter
    ...rateLimiter,

    // Middleware
    ...middleware,

    // Named exports for clarity
    rateLimiter,
    middleware,
};
