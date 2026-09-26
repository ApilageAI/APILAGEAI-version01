/**
 * ApilageAI File Logger
 * 
 * Logs errors and security events to files
 * 
 * @module utils/logger
 */

'use strict';

const fs = require('fs');
const path = require('path');

// Log directory (same as app directory)
const LOG_DIR = __dirname.replace(/[\\/]utils$/, '');
const ERROR_LOG_PATH = path.join(LOG_DIR, 'error.log');
const SECURITY_LOG_PATH = path.join(LOG_DIR, 'security.log');

// Max log file size (10MB)
const MAX_LOG_SIZE = 10 * 1024 * 1024;

/**
 * Format timestamp for logs
 * @returns {string} ISO timestamp
 */
function getTimestamp() {
    return new Date().toISOString();
}

/**
 * Rotate log file if it exceeds max size
 * @param {string} logPath - Path to log file
 */
function rotateLogIfNeeded(logPath) {
    try {
        if (fs.existsSync(logPath)) {
            const stats = fs.statSync(logPath);
            if (stats.size > MAX_LOG_SIZE) {
                const backupPath = logPath.replace('.log', `.${Date.now()}.log`);
                fs.renameSync(logPath, backupPath);
            }
        }
    } catch (err) {
        console.error('Log rotation error:', err.message);
    }
}

/**
 * Append to log file
 * @param {string} logPath - Path to log file
 * @param {string} message - Message to log
 */
function appendToLog(logPath, message) {
    try {
        rotateLogIfNeeded(logPath);
        fs.appendFileSync(logPath, message + '\n', 'utf8');
    } catch (err) {
        console.error('Failed to write to log file:', err.message);
    }
}

/**
 * Log error to error.log file
 * @param {string} message - Error message
 * @param {Object} details - Additional details
 */
function logError(message, details = {}) {
    const logEntry = {
        timestamp: getTimestamp(),
        level: 'ERROR',
        message,
        ...details,
    };

    // Remove sensitive data
    if (logEntry.password) logEntry.password = '[REDACTED]';
    if (logEntry.token) logEntry.token = '[REDACTED]';
    if (logEntry.apiKey) logEntry.apiKey = '[REDACTED]';

    const logLine = JSON.stringify(logEntry);
    appendToLog(ERROR_LOG_PATH, logLine);

    // Also log to console
    console.error(`[ERROR] ${message}`);
}

/**
 * Log critical error to error.log file
 * @param {string} message - Error message
 * @param {Error} error - Error object
 * @param {Object} context - Request context
 */
function logCriticalError(message, error, context = {}) {
    const logEntry = {
        timestamp: getTimestamp(),
        level: 'CRITICAL',
        message,
        error: {
            name: error?.name,
            message: error?.message,
            stack: error?.stack,
        },
        context: {
            requestId: context.requestId,
            ip: context.ip,
            path: context.path,
            method: context.method,
            userId: context.userId,
        },
    };

    const logLine = JSON.stringify(logEntry);
    appendToLog(ERROR_LOG_PATH, logLine);

    // Also log to console
    console.error(`[CRITICAL] ${message}:`, error?.message);
}

/**
 * Log security event to security.log file
 * @param {string} level - Log level (info, warn, error, critical)
 * @param {string} event - Event type
 * @param {Object} details - Event details
 */
function logSecurityEvent(level, event, details = {}) {
    const logEntry = {
        timestamp: getTimestamp(),
        level: level.toUpperCase(),
        event,
        ...details,
    };

    // Remove sensitive data
    if (logEntry.password) logEntry.password = '[REDACTED]';
    if (logEntry.token) logEntry.token = '[REDACTED]';
    if (logEntry.apiKey) logEntry.apiKey = '[REDACTED]';

    const logLine = JSON.stringify(logEntry);
    appendToLog(SECURITY_LOG_PATH, logLine);

    // Critical and error also go to error.log
    if (level === 'critical' || level === 'error') {
        appendToLog(ERROR_LOG_PATH, logLine);
    }
}

/**
 * Log info message (console only unless enabled)
 * @param {string} message - Info message
 */
function logInfo(message) {
    if (process.env.ENABLE_REQUEST_LOGGING === 'true') {
        console.log(`[INFO] ${message}`);
    }
}

/**
 * Log warning message
 * @param {string} message - Warning message
 * @param {Object} details - Additional details
 */
function logWarn(message, details = {}) {
    const logEntry = {
        timestamp: getTimestamp(),
        level: 'WARN',
        message,
        ...details,
    };

    const logLine = JSON.stringify(logEntry);
    appendToLog(ERROR_LOG_PATH, logLine);

    console.warn(`[WARN] ${message}`);
}

module.exports = {
    logError,
    logCriticalError,
    logSecurityEvent,
    logInfo,
    logWarn,
    ERROR_LOG_PATH,
    SECURITY_LOG_PATH,
};
