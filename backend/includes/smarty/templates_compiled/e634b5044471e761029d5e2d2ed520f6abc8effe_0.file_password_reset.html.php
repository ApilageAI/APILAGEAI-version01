<?php
/* Smarty version 5.7.0, created on 2026-02-06 22:11:48
  from 'file:emails/password_reset.html' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.7.0',
  'unifunc' => 'content_698619cc4ecdc4_08738648',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'e634b5044471e761029d5e2d2ed520f6abc8effe' => 
    array (
      0 => 'emails/password_reset.html',
      1 => 1770273753,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
))) {
function content_698619cc4ecdc4_08738648 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = '/Users/dinethgunawardana/Documents/GitHub/apilageai-personal/backend/includes/smarty/templates/emails';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Password Reset - Apilage AI</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f6f6f6; margin: 0; padding: 0;">
<div style="max-width: 600px; margin: 40px auto; background: #fff; border-radius: 15px; padding: 30px; box-shadow: 0 8px 20px rgba(0,0,0,0.1);">
  <h2 style="color: #333;">Hi <?php echo $_smarty_tpl->getValue('name');?>
,</h2>
  <p>We received a request to reset your Apilage AI account password.</p>
  <p>Click the button below to reset your password. This link is valid for 5 minutes only.</p>
  <p style="text-align: center; margin: 30px 0;">
    <a href="<?php echo $_smarty_tpl->getValue('reset_link');?>
" style="background: #667eea; color: #fff; padding: 15px 40px; border-radius: 50px; text-decoration: none; font-weight: bold;">Reset Password</a>
  </p>
  <p>If you did not request a password reset, please ignore this email.</p>
  <p style="font-size: small; color: #888;">© 2025 ApilageAI. All rights reserved.</p>
</div>
</body>
</html>
<?php }
}
