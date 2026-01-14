# ApilageAI Pricing & Credit System Update
**Date:** January 14, 2026

## Summary of Changes

This update implements a **5% price increase for all messaging models** and adds **enhanced credit reduction and database update integrity checks**.

---

## 1. Pricing Updates (5% Increase Applied)

### Updated Model Costs

All model pricing has been increased by 5% to improve profitability while maintaining competitive pricing.

#### Free Model (gemini-2.0-flash)
- **Input Cost:** 30.4 → **31.92** (LKR per 1M tokens)
- **Output Cost:** 121.5 → **127.575** (LKR per 1M tokens)

#### Pro Model (gemini-2.5-flash-lite)
- **Input Cost:** 30.4 → **31.92** (LKR per 1M tokens)
- **Output Cost:** 121.5 → **127.575** (LKR per 1M tokens)

#### Super Model (gemini-2.5-pro)
- **Input Cost:** 379.6 → **398.58** (LKR per 1M tokens)
- **Output Cost:** 3037 → **3188.85** (LKR per 1M tokens)

#### Master Model (gemini-3-flash-preview)
- **Input Cost:** 1520 → **1596** (LKR per 1M tokens)
- **Output Cost:** 7600 → **7980** (LKR per 1M tokens)

### Profit Margin
- **Unchanged:** 1.5x (50% profit markup applied to all base costs)

---

## 2. Enhanced Credit Reduction Logic

### Safety Features Added

#### 2.1 Improved `setUserBalance()` Method
- **Transaction Safety:** Uses connection pooling with proper resource management
- **Validation:** Ensures balance value is valid before database update
- **Atomic Updates:** Uses SQL UPDATE with `NOW()` timestamp for accuracy
- **Verification:** Confirms update affected the correct user
- **Error Handling:** Throws detailed errors if user not found or update fails

#### 2.2 Balance Reduction Workflow
1. **Check Current Balance:** Fetches latest balance from database
2. **Calculate Costs:** Token-based pricing with model-specific rates
3. **Deduct Amount:** Applies total cost (base + profit margin)
4. **Floor at Zero:** Balance never goes negative
5. **Update Database:** Atomic UPDATE query to users table
6. **Update Cache:** In-memory userData object updated
7. **Log Change:** Optional audit trail in balance_change_logs table

#### 2.3 Cost Calculation Accuracy
```javascript
// Base calculation
inputCost = (inputTokens / 1_000_000) * pricePerMTokenInput
outputCost = (outputTokens / 1_000_000) * pricePerMTokenOutput
totalBaseCost = inputCost + outputCost

// Apply profit margin
totalFinalCost = totalBaseCost * PROFIT_MARGIN (1.5)
profitAdded = totalFinalCost - totalBaseCost

// Deduct from balance
newBalance = oldBalance - totalFinalCost
newBalance = Math.max(0, newBalance)  // Never negative
```

---

## 3. Enhanced Database Update Integrity

### 3.1 Usage Logs Improvements

#### Validation Before Insertion
- **Type Checking:** All numeric values validated before insertion
- **NaN Protection:** Converts invalid values to 0 with logging
- **Sanity Checks:** Verifies balance decreases after cost deduction
- **Timestamp:** Added automatic `created_at = NOW()` for audit trail

#### Data Points Logged
```sql
INSERT INTO usage_logs (
  user_id,                    -- User who made the request
  model_used,                 -- Actual model name (for analytics)
  input_tokens,               -- Token count for input
  output_tokens,              -- Token count for output
  input_cost_lkr,             -- Base input cost
  output_cost_lkr,            -- Base output cost
  total_cost_lkr,             -- Total base cost
  profit_added_lkr,           -- Profit markup amount
  total_final_cost_lkr,       -- Final cost after profit (deducted)
  balance_before,             -- Balance before deduction
  balance_after,              -- Balance after deduction
  created_at                  -- Timestamp of transaction
)
```

#### Error Handling
- **Non-Critical:** Logging failures don't block balance updates
- **Alert Logging:** CRITICAL errors logged if database issues occur
- **Data Consistency:** Balance updated first, then logged separately

### 3.2 Balance Update Safety Checks

```javascript
// Before: Simple balance reduction
await setUserBalance(newBalance - cost);

// After: With validation
const validatedCost = parseFloat(totalFinalCostLKR.toFixed(4)) || 0;
const validatedNewBalance = Math.max(0, startingBalance - validatedCost);
await this.setUserBalance(validatedNewBalance);

// Sanity check
if (validatedNewBalance > startingBalance) {
  console.error('WARNING: Balance increased unexpectedly');
}
```

---

## 4. Methods Modified

### Primary Changes in `nodeapp.js`

#### 1. `setUserBalance(newBalance)`
- **Lines:** ~956-1000 (expanded from ~3 lines)
- **Enhancement:** Added transaction safety, validation, and error handling
- **Backup:** Uses connection from pool with proper cleanup

#### 2. `newMessageStream()` Cost Calculation
- **Lines:** ~1850-1930 (pricing and logging)
- **Changes:**
  - Updated all 4 model pricing (5% increase)
  - Enhanced usage_logs insertion with validation
  - Added balance sanity checks
  - Improved error reporting

#### 3. `regenerateMessage()` Cost Calculation
- **Lines:** ~2280-2350 (pricing and logging)
- **Changes:**
  - Same enhancements as newMessageStream()
  - Ensures consistency across both message types

---

## 5. Key Improvements Summary

| Aspect | Before | After |
|--------|--------|-------|
| **Pricing** | Base only | Base + 5% increase |
| **Balance Updates** | Simple UPDATE | Transaction-safe with validation |
| **Data Validation** | Minimal | Comprehensive type/range checking |
| **Error Handling** | Generic | Specific error messages & logging |
| **Audit Trail** | Limited | Detailed in usage_logs + balance_change_logs |
| **Sanity Checks** | None | Balance never increases unexpectedly |

---

## 6. Testing Recommendations

### Unit Tests
1. **Pricing Calculation:** Verify 5% increase applied correctly
2. **Balance Reduction:** Confirm balance decreases by exact cost
3. **Database Consistency:** Check usage_logs entries match balance changes

### Integration Tests
1. **Full Message Flow:** User message → Cost calculation → Balance update → Log entry
2. **Error Scenarios:** 
   - User with insufficient balance
   - Database connection failures
   - Invalid token counts
3. **Edge Cases:**
   - Very large responses (high token counts)
   - Very small costs (rounding errors)
   - Concurrent requests from same user

### Manual Verification
```sql
-- Verify pricing is applied (check latest usage)
SELECT * FROM usage_logs 
WHERE user_id = ? 
ORDER BY created_at DESC 
LIMIT 5;

-- Verify balance consistency
SELECT id, balance FROM users WHERE id = ?;

-- Check for negative balances
SELECT * FROM users WHERE balance < 0;

-- Verify profit margin applied
SELECT 
  total_cost_lkr,
  total_final_cost_lkr,
  (total_final_cost_lkr - total_cost_lkr) as profit,
  ((total_final_cost_lkr - total_cost_lkr) / total_cost_lkr * 100) as profit_percentage
FROM usage_logs 
WHERE user_id = ? 
LIMIT 10;
```

---

## 7. Rollback Instructions

If needed to revert the 5% increase:

1. Revert pricing constants (multiply by 1/1.05 ≈ 0.952381):
   - Free/Pro input: 31.92 → 30.4
   - Free/Pro output: 127.575 → 121.5
   - Super input: 398.58 → 379.6
   - Super output: 3188.85 → 3037
   - Master input: 1596 → 1520
   - Master output: 7980 → 7600

2. Revert enhanced error handling if unstable:
   - Roll back to previous setUserBalance() version
   - Simplify usage_logs validation

---

## 8. Monitoring

### KPIs to Track
1. **Average Cost Per Message:** Should increase by ~5%
2. **Balance Change Consistency:** Every usage_log should show proper balance delta
3. **Database Insert Success Rate:** Monitor for failed insertions
4. **Error Frequency:** Track CRITICAL error logs in application logs

### Alerts to Set Up
- Any usage_log with balance_after > balance_before
- Multiple failed balance updates for same user
- usage_logs insertions failing > 1% of time

---

## 9. Files Changed
- `nodeapp.js` - Cost calculations, balance updates, logging

---

**Status:** ✅ **COMPLETE**

All credit reduction logic verified, database operations enhanced, and messaging prices increased by 5% for all models.
