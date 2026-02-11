<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset - ApilageAI</title>
</head>
<body style="margin: 0; padding: 0; background: #f4f5fb; font-family: 'Plus Jakarta Sans', 'Outfit', Arial, sans-serif;">
    <div style="max-width: 600px; margin: 32px auto; background: #ffffff; border-radius: 24px; overflow: hidden; box-shadow: 0 24px 60px rgba(17, 24, 39, 0.16);">
        <div style="background: linear-gradient(135deg, #e53e3e 0%, #c53030 45%, #9b2c2c 100%); padding: 36px 32px; text-align: center; color: #ffffff;">
            <img src="{$smarty.const.APP_URL}/assets/images/icon.png" alt="ApilageAI" style="width: 72px; height: 72px; object-fit: contain; margin-bottom: 12px;">
            <h1 style="margin: 0; font-size: 28px; font-weight: 700;">Reset your password</h1>
            <p style="margin: 10px 0 0; font-size: 15px; color: rgba(255, 255, 255, 0.85);">We received a request to reset your password.</p>
        </div>

        <div style="padding: 32px 36px; color: #1f2937; line-height: 1.6;">
            <p style="font-size: 16px; margin: 0 0 16px;">Hi <strong>{$name}</strong>,</p>
            <p style="font-size: 15px; margin: 0 0 20px;">Click the button below to set a new password. This link is valid for 5 minutes.</p>

            <div style="text-align: center; margin: 24px 0;">
                <a href="{$reset_link}" style="background: #e53e3e; color: #ffffff; text-decoration: none; padding: 14px 36px; border-radius: 999px; font-weight: 600; display: inline-block;">Reset Password</a>
            </div>

            <div style="background: #f9fafb; border: 1px solid #e5e7eb; padding: 16px; border-radius: 16px; font-size: 13px; color: #6b7280;">
                <div style="margin-bottom: 8px;">If the button does not work, paste this link into your browser:</div>
                <div style="word-break: break-all; color: #c53030;">{$reset_link}</div>
            </div>

            <p style="font-size: 13px; color: #6b7280; margin: 20px 0 0;">If you did not request a password reset, you can ignore this email.</p>
        </div>

        <div style="background: #f9fafb; padding: 18px 24px; text-align: center; font-size: 12px; color: #6b7280;">
            <p style="margin: 0 0 8px;">Need help? <a href="{$smarty.const.APP_URL}" style="color: #c53030; text-decoration: none;">Support</a></p>
            <p style="margin: 0;">&copy; 2026 ApilageAI. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
