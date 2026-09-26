<?php
/* Smarty version 5.8.0, created on 2026-04-19 02:20:21
  from 'file:emails/payment_receipt.html' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.8.0',
  'unifunc' => 'content_69e3ee8d69ac58_88849071',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '8d554e6f0c5d639ec402ca374d987b4dc112a766' => 
    array (
      0 => 'emails/payment_receipt.html',
      1 => 1771874471,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
))) {
function content_69e3ee8d69ac58_88849071 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = '/home/apilageai/domains/apilageai.lk/backend/includes/smarty/templates/emails';
?><!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ApilageAI Payment Receipt</title>
</head>

<body
    style="margin: 0; padding: 0; background: #F8FAFC; font-family: 'Plus Jakarta Sans', 'Outfit', Arial, sans-serif;">
    <div style="max-width: 640px; margin: 32px auto; padding: 0 16px;">
        <div
            style="background: #ffffff; border: 2px solid #172554; border-radius: 24px; overflow: hidden; box-shadow: 6px 6px 0 #172554;">
            <div style="background: #172554; color: #ffffff; padding: 28px 32px; text-align: center;">
                <div
                    style="display: inline-block; background: #E0F2FE; color: #172554; border: 2px solid #172554; border-radius: 999px; font-size: 11px; font-weight: 700; letter-spacing: 1px; padding: 6px 12px; text-transform: uppercase; margin-bottom: 12px; box-shadow: 2px 2px 0 #0f172a;">
                    Receipt
                </div>
                <img src="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/assets/images/icon.png" alt="ApilageAI" style="width: 64px; height: 64px; object-fit: contain; margin: 0 auto 12px; display: block; background: #ffffff; border-radius: 16px; border: 2px solid #172554; padding: 6px;">
                <h1 style="margin: 0; font-size: 26px; font-weight: 800; letter-spacing: 0.2px;">Payment Receipt</h1>
                <p style="margin: 8px 0 0; font-size: 14px; color: rgba(255, 255, 255, 0.85);">Thanks for upgrading your
                    ApilageAI credit.</p>
            </div>

            <div style="padding: 28px 32px; color: #0f172a; line-height: 1.6;">
                <p style="font-size: 16px; margin: 0 0 16px;">Hi <strong><?php echo $_smarty_tpl->getValue('name');?>
</strong>,</p>
                <p style="font-size: 15px; margin: 0 0 20px;">Thanks for upgrading credit on your ApilageAI account.
                    Your account is now more powerful.</p>

                <div
                    style="background: #F8FAFC; border: 2px dashed #172554; padding: 16px; border-radius: 16px; font-size: 14px; color: #0f172a;">
                    <div style="font-weight: 700; margin-bottom: 8px;">Receipt details</div>
                    <div style="margin-bottom: 6px;"><strong>Invoice ID:</strong> <?php echo $_smarty_tpl->getValue('invoice_id');?>
</div>
                    <div style="margin-bottom: 6px;"><strong>Upgraded amount:</strong> Rs. <?php echo $_smarty_tpl->getValue('amount');?>
</div>
                    <div style="margin-bottom: 6px;"><strong>Paid on:</strong> <?php echo $_smarty_tpl->getValue('payment_date');?>
 at <?php echo $_smarty_tpl->getValue('payment_time');?>
</div>
                </div>

                <div style="text-align: center; margin: 24px 0;">
                    <a href="<?php echo $_smarty_tpl->getValue('receipt_link');?>
"
                        style="background: #FF3B30; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 999px; font-weight: 700; border: 2px solid #172554; box-shadow: 3px 3px 0 #172554; display: inline-block;">View
                        Receipt</a>
                </div>

                <p style="font-size: 14px; color: #475569; margin: 0 0 8px;">How ApilageAI credit works:</p>
                <p style="font-size: 14px; margin: 0;"><a href="<?php echo $_smarty_tpl->getValue('credit_link');?>
"
                        style="color: #FF3B30; font-weight: 700; text-decoration: none;">apilageai.lk/how_apilageai_credit_works</a>
                </p>

                <p style="font-size: 13px; color: #475569; margin: 20px 0 0;">If this wasn’t you, please contact our
                    support team immediately.</p>
            </div>

            <div
                style="background: #E0F2FE; border-top: 2px solid #172554; padding: 16px 24px; text-align: center; font-size: 12px; color: #172554;">
                <p style="margin: 0 0 6px;">Need help? <a href="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/help"
                        style="color: #FF3B30; text-decoration: none; font-weight: 700;">Help Center</a></p>
                <p style="margin: 0;">&copy; 2026 ApilageAI. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>

</html><?php }
}
