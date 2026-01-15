# Trial Abuse Prevention System Implementation

## Overview
This system prevents users from reusing trial accounts by tracking:
- **IP Address**: Blocks trial reuse from the same IP for 30 days
- **Device Fingerprint**: Blocks trial reuse from the same device for 30 days
- **User ID**: Blocks the same user account from reusing trial

## Database Schema
A new table `trial_abuse_tracking` has been automatically created with:
```sql
CREATE TABLE trial_abuse_tracking (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  ip_address VARCHAR(45),
  device_fingerprint VARCHAR(255),
  trial_start_date DATE,
  trial_end_date DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ip_date (ip_address, trial_end_date),
  INDEX idx_device_date (device_fingerprint, trial_end_date),
  INDEX idx_user_date (user_id, trial_end_date)
);
```

## How It Works

### 1. IP Tracking (Automatic)
- Every socket connection captures the client's IP address
- Handles proxies via `x-forwarded-for` header
- If a trial is used from an IP, that IP is blocked for 30 days

### 2. Device Fingerprinting (Frontend Implementation Required)
Add this JavaScript to your frontend login/signup page:

```javascript
// Generate a unique device fingerprint (browser-based)
function getDeviceFingerprint() {
  const canvas = document.createElement('canvas');
  const ctx = canvas.getContext('2d');
  const text = navigator.userAgent + screen.width + screen.height + screen.colorDepth;
  ctx.textBaseline = 'top';
  ctx.font = '14px Arial';
  ctx.textBaseline = 'alphabetic';
  ctx.fillStyle = '#f60';
  ctx.fillRect(125, 1, 62, 20);
  ctx.fillStyle = '#069';
  ctx.fillText(text, 2, 15);
  ctx.fillStyle = 'rgba(102, 204, 0, 0.7)';
  ctx.fillText(text, 4, 17);
  
  const canvasData = canvas.toDataURL('image/png');
  const hash = btoa(canvasData).substring(0, 32);
  return hash;
}

// Set the device fingerprint as a cookie on login
function setDeviceFingerprint() {
  const fingerprint = getDeviceFingerprint();
  document.cookie = `DEVICE_FINGERPRINT=${fingerprint}; path=/; max-age=${30*24*60*60}`;
}

// Call this function on page load and after login
setDeviceFingerprint();
```

Alternatively, use a library like `fingerprintjs2`:
```javascript
FingerprintJS.load().then(fp => {
  fp.get().then(result => {
    document.cookie = `DEVICE_FINGERPRINT=${result.visitorId}; path=/; max-age=${30*24*60*60}`;
  });
});
```

### 3. Trial Recording Triggers
Trial usage is recorded when a user:
- **First message** on trial (using non-free model)
- **First image upload** on trial
- **First image generation** on trial

Only the first occurrence triggers recording to prevent duplicate entries.

## Trial Limits (Updated)
```javascript
const DAILY_TRIAL_LIMITS = {
  messages: 3,           // 3 messages per day
  image_uploads: 2,      // 2 image uploads per day
  image_generations: 5   // 5 image generations per day
};
```

## Trial Abuse Detection
When a user connects with an IP/device that has recently used a trial:
- Socket emits: `trial_abuse_detected` event
- User receives message: "This IP/device has already used a free trial recently. Please try again on [DATE]."
- Currently allows connection but flags the abuse (optional: can disconnect user)

## Configuration
To modify trial window duration, edit in `nodeapp.js`:
```javascript
const TRIAL_ABUSE_WINDOW_DAYS = 30;  // Change this value
```

## Backend Functions

### `checkTrialEligibility(userId, ipAddress, deviceFingerprint)`
Checks if user/IP/device is eligible for trial
Returns: `{ eligible: boolean, reason?: string, reusedRecord?: object }`

### `recordTrialUsage(userId, ipAddress, deviceFingerprint)`
Records a trial usage to block future attempts
Automatically called when user starts using trial

### `checkTrialAbuseEligibility(ipAddress, deviceFingerprint)` (ChatManager method)
User-friendly wrapper for checking trial eligibility

### `recordTrialUsageData(ipAddress, deviceFingerprint)` (ChatManager method)
User-friendly wrapper for recording trial usage

## Monitoring & Logs
The system logs:
- ✓ Trial eligibility verification: `✓ Trial eligibility verified for user {id} (IP: {ip})`
- ⚠️ Trial abuse detected: `⚠️ Trial abuse detected for user {id}: {reason}`
- 📝 Trial recorded: `📝 Trial recorded for user {id}, IP: {ip}, Device: {fingerprint}`
- 📝 First message/upload/generation recorded: `📝 First trial [type] recorded for user {id} to prevent reuse`

## Testing Trial Abuse Prevention

1. **IP Test**:
   - Create first trial account
   - Use at least 1 message/upload/generation
   - Try creating new account from same IP
   - Should see trial abuse warning

2. **Device Test**:
   - Ensure device fingerprint is set in cookies
   - Create first trial account
   - Try creating new account in same browser
   - Should see trial abuse warning

3. **Cross-Device**:
   - Different device/browser = new trial allowed (if IP is different)
   - Same device different browser = depends on fingerprint implementation

## Future Enhancements
- Add phone number verification (strongest anti-abuse)
- Add email domain tracking (one trial per domain per month)
- Add ML-based fraud detection
- Implement rate limiting on signup endpoint
- Add CAPTCHA to prevent bot signup
