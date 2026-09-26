<?php
/* Smarty version 5.8.0, created on 2026-04-22 09:46:32
  from 'file:emails/developer_api_key_created.html' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.8.0',
  'unifunc' => 'content_69e84ba0db9959_15350941',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '325d5d503764f858902741552b2ed36cbec6c4f0' => 
    array (
      0 => 'emails/developer_api_key_created.html',
      1 => 1771694665,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
))) {
function content_69e84ba0db9959_15350941 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = '/home/apilageai/domains/apilageai.lk/backend/includes/smarty/templates/emails';
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>API Key Created - ApilageAI</title>
</head>
<body style="margin: 0; padding: 0; background: #F8FAFC; font-family: 'Plus Jakarta Sans', 'Outfit', Arial, sans-serif;">
  <div style="max-width: 640px; margin: 32px auto; padding: 0 16px;">
    <div style="background: #ffffff; border: 2px solid #172554; border-radius: 24px; overflow: hidden; box-shadow: 6px 6px 0 #172554;">
      <div style="background: #172554; color: #ffffff; padding: 28px 32px; text-align: center;">
        <div style="display: inline-block; background: #E0F2FE; color: #172554; border: 2px solid #172554; border-radius: 999px; font-size: 11px; font-weight: 700; letter-spacing: 1px; padding: 6px 12px; text-transform: uppercase; margin-bottom: 12px; box-shadow: 2px 2px 0 #0f172a;">
          API
        </div>
        <img src="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/assets/images/icon.png" alt="ApilageAI" style="width: 64px; height: 64px; object-fit: contain; margin: 0 auto 12px; display: block; background: #ffffff; border-radius: 16px; border: 2px solid #172554; padding: 6px;">
        <h1 style="margin: 0; font-size: 26px; font-weight: 800; letter-spacing: 0.2px;">API key created</h1>
        <p style="margin: 8px 0 0; font-size: 14px; color: rgba(255, 255, 255, 0.85);">A new ApilageAI API key was created for your account.</p>
      </div>

      <div style="padding: 28px 32px; color: #0f172a; line-height: 1.6;">
        <p style="font-size: 16px; margin: 0 0 16px;">Hi <strong><?php echo $_smarty_tpl->getValue('name');?>
</strong>,</p>

        <div style="background: #E0F2FE; border: 2px solid #172554; border-radius: 16px; padding: 16px;">
          <table role="presentation" cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse; font-size: 14px; color: #0f172a;">
            <tr>
              <td style="padding: 6px 0; font-weight: 700; width: 40%;">Key name</td>
              <td style="padding: 6px 0;"><?php echo $_smarty_tpl->getValue('key_name');?>
</td>
            </tr>
            <tr>
              <td style="padding: 6px 0; font-weight: 700;">Key prefix</td>
              <td style="padding: 6px 0;"><?php echo $_smarty_tpl->getValue('key_prefix');?>
****</td>
            </tr>
            <tr>
              <td style="padding: 6px 0; font-weight: 700;">Created at</td>
              <td style="padding: 6px 0;"><?php echo $_smarty_tpl->getValue('created_at');?>
</td>
            </tr>
          </table>
        </div>

        <p style="font-size: 13px; color: #475569; margin: 18px 0 0;">If this was not you, log in and delete the key immediately.</p>

        <div style="text-align: center; margin: 22px 0 6px;">
          <a href="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/developer" style="background: #FF3B30; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 999px; font-weight: 700; border: 2px solid #172554; box-shadow: 3px 3px 0 #172554; display: inline-block;">Manage API Keys</a>
        </div>
      </div>

      <div style="background: #E0F2FE; border-top: 2px solid #172554; padding: 16px 24px; text-align: center; font-size: 12px; color: #172554;">
        <p style="margin: 0 0 6px;">Need help? <a href="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
" style="color: #FF3B30; text-decoration: none; font-weight: 700;">Support</a></p>
        <p style="margin: 0;">&copy; 2026 ApilageAI. All rights reserved.</p>
      </div>
    </div>
  </div>
</body>
</html>
<?php }
}
