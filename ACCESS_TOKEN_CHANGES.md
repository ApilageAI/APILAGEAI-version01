# Access Token System Changes

## Summary
Changed the access token system to use **one unique permanent token per conversation** instead of generating different random tokens for each user when sharing and publishing.

## What Changed

### Before
- **Sharing**: Generated a new random token (`crypto.randomBytes(24).toString('hex')`) each time a chat was shared with a user
- **Publishing**: Generated a new random token each time a chat was published
- **Problem**: Different users got different token URLs; different publish actions created different tokens
- Token format: 48-character hex strings (24 bytes)

### After
- **Sharing**: Uses the conversation ID as the token
- **Publishing**: Uses the conversation ID as the token
- **Benefit**: One permanent, consistent token per conversation that works for all users
- Token format: Simple numeric conversation ID (e.g., `123`)

## Modified Functions

### 1. `createShareLink(conversationId, targetUserId)` (Line 2763)
**Changes:**
- Token is now `String(conversationId)` instead of random bytes
- Uses `ON DUPLICATE KEY UPDATE` to handle multiple users sharing the same chat
- Same share URL will be generated for all users accessing that conversation

**Before:**
```javascript
const token = crypto.randomBytes(24).toString('hex');
INSERT INTO conversation_share_links VALUES (?, ?, ?, ?, NOW())
```

**After:**
```javascript
const token = String(conversationId);
INSERT INTO conversation_share_links VALUES (?, ?, ?, ?, NOW())
ON DUPLICATE KEY UPDATE shared_by = ?, updated_at = NOW()
```

### 2. `acceptShareToken(token)` (Line 2783)
**Changes:**
- Token is treated as the conversation ID
- Removed target_user_id validation (sharing now works for all users)
- Simplified the access check

**Before:**
```javascript
SELECT conversation_id, target_user_id FROM conversation_share_links WHERE token = ?
// Check if target_user_id matches current user
```

**After:**
```javascript
const conversationId = Number(token);
SELECT conversation_id FROM conversation_share_links WHERE token = ?
// No user restriction - any authenticated user can access
```

### 3. `publishConversation(conversationId)` (Line 2809)
**Changes:**
- Publish token is now `String(convId)` instead of random bytes
- Uses `ON DUPLICATE KEY UPDATE` for consistency
- Same publish URL for all publish actions on the same conversation

**Before:**
```javascript
const publishToken = crypto.randomBytes(24).toString('hex');
INSERT INTO conversation_published VALUES (?, ?, 'read_only', ?, NOW())
ON DUPLICATE KEY UPDATE publish_token = ?, ...
```

**After:**
```javascript
const publishToken = String(convId);
INSERT INTO conversation_published VALUES (?, ?, 'read_only', ?, NOW())
ON DUPLICATE KEY UPDATE access_level = 'read_only', published_by = ?, ...
```

## URL Examples

### Share Links
**Before:** `https://apilageai.lk/app/chat/123?share=a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6`
**After:** `https://apilageai.lk/app/chat/123?share=123`

### Publish Links
**Before:** `https://apilageai.lk/app/published/a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6`
**After:** `https://apilageai.lk/app/published/123`

## Benefits

1. **Single Token per Conversation**: No more different tokens for different users
2. **Permanent Tokens**: Tokens never change, even if you share multiple times
3. **Simpler URLs**: Cleaner, shorter share and publish URLs
4. **Consistent Access**: All users get the same token for the same conversation
5. **Better UX**: Easy to remember and share conversation numbers instead of long hex strings

## Database Impact

- **conversation_share_links table**: Now stores conversation_id as the token
- **conversation_published table**: Now stores conversation_id as the publish_token
- Existing UNIQUE constraints on `token` and `publish_token` prevent conflicts
- ON DUPLICATE KEY UPDATE handles cases where the same conversation is shared/published multiple times

## No Migration Needed

- The database schema supports storing numeric tokens (varchar(64))
- Existing data can coexist with new data
- Old shared links won't work with the new system (they were random tokens)
- New shares/publishes will use the numeric token format
