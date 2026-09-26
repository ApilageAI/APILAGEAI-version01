<?php
/* Smarty version 5.7.0, created on 2026-02-05 10:34:16
  from 'file:emails/magic_login.html' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.7.0',
  'unifunc' => 'content_698424d0bab555_85397532',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '5efb7e00103651139cb1c451ab0452578fce6e25' => 
    array (
      0 => 'emails/magic_login.html',
      1 => 1770267299,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
))) {
function content_698424d0bab555_85397532 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = '/Users/dinethgunawardana/Downloads/apilageai/backend/includes/smarty/templates/emails';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Your ApilageAI Login Link</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f6f6f6; margin: 0; padding: 0;">
<div style="max-width: 600px; margin: 40px auto; background: #fff; border-radius: 15px; padding: 30px; box-shadow: 0 8px 20px rgba(0,0,0,0.1);">
  <h2 style="color: #333;">Hi <?php echo $_smarty_tpl->getValue('name');?>
,</h2>
  <p>Use the button below to log in to your ApilageAI account.</p>
  <p>This link is valid for 5 minutes and can be used only once.</p>
  <p style="text-align: center; margin: 30px 0;">
    <a href="<?php echo $_smarty_tpl->getValue('login_link');?>
" style="background: #0f172a; color: #fff; padding: 15px 40px; border-radius: 50px; text-decoration: none; font-weight: bold;">Log In</a>
  </p>
  <p>If you did not request this login link, please ignore this email.</p>
  <p style="font-size: small; color: #888;">© 2025 ApilageAI. All rights reserved.</p>
</div>
</body>
</html>
<?php }
}
