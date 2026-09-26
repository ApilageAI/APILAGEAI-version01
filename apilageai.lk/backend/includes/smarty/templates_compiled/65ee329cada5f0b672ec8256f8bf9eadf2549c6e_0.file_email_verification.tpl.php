<?php
/* Smarty version 5.8.0, created on 2026-04-04 20:49:36
  from 'file:email_verification.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.8.0',
  'unifunc' => 'content_69d12c08efd1b8_37726314',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '65ee329cada5f0b672ec8256f8bf9eadf2549c6e' => 
    array (
      0 => 'email_verification.tpl',
      1 => 1773126435,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:components/head.tpl' => 1,
  ),
))) {
function content_69d12c08efd1b8_37726314 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = '/home/apilageai/domains/apilageai.lk/backend/includes/smarty/templates';
$_smarty_tpl->renderSubTemplate("file:components/head.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), (int) 0, $_smarty_current_dir);
?>

<div class="auth-layout auth-layout--verify">
    <div class="background-accent top-right"></div>
    <div class="background-accent bottom-left"></div>
    <div class="bg-pattern"></div>

    <div class="auth-wrapper">
        <div class="auth-container" style="max-width: 600px; margin: 0 auto;">
            <div class="verification-result-card animate-fade-in">
                <?php if ($_smarty_tpl->getValue('result')['e']) {?>
                                        <div class="verification-failed">
                        <i class="fa-solid fa-times-circle"></i>
                        <h2>Verification Failed</h2>
                        <p><?php echo $_smarty_tpl->getValue('result')['m'];?>
</p>
                        
                        <?php if ((true && (true && null !== ($_smarty_tpl->getValue('result')['expired'] ?? null))) && $_smarty_tpl->getValue('result')['expired']) {?>
                            <button onclick="requestNewLink()" class="auth-submit-button" style="margin-top: 20px;">
                                <i class="fa-solid fa-envelope"></i> Request New Verification Link
                            </button>
                        <?php }?>
                        
                        <a href="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/auth/login" class="back-link">
                            <i class="fa-solid fa-arrow-left"></i> Back to Login
                        </a>
                    </div>
                <?php } else { ?>
                                        <div class="verification-success">
                        <div class="success-animation">
                            <i class="fa-solid fa-check-circle"></i>
                        </div>
                        <h2>Email Verified!</h2>
                        <p><?php echo $_smarty_tpl->getValue('result')['m'];?>
</p>
                        
                        <?php if ((true && (true && null !== ($_smarty_tpl->getValue('result')['already_verified'] ?? null))) && $_smarty_tpl->getValue('result')['already_verified']) {?>
                            <p class="info-text">Your email was already verified.</p>
                        <?php } else { ?>
                            <p class="info-text">Welcome to Apilage AI! You can now access all features.</p>
                        <?php }?>
                        
                        <a href="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/app" class="auth-submit-button" style="margin-top: 20px;">
                            Go to App <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                <?php }?>
            </div>
        </div>
    </div>
</div>


<?php echo '<script'; ?>
>
function requestNewLink() {
    const email = prompt('Enter your email address to receive a new verification link:');
    
    if (!email) {
        return;
    }

    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert('Please enter a valid email address.');
        return;
    }

    // Show loading state
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';

    fetch('<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/auth/resend-verification', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/x-www-form-urlencoded' 
        },
        body: 'email=' + encodeURIComponent(email)
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalText;

        if (data.e) {
            alert('Error: ' + data.m);
        } else {
            alert('Success! ' + data.m + '\n\nPlease check your email inbox.');
            setTimeout(() => {
                window.location.href = '<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/auth/login';
            }, 2000);
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        console.error('Error:', err);
        alert('Failed to send verification email. Please try again or contact support.');
    });
}

// Auto-redirect after successful verification
<?php if (!$_smarty_tpl->getValue('result')['e'] && !(true && (true && null !== ($_smarty_tpl->getValue('result')['already_verified'] ?? null)))) {?>
    setTimeout(function() {
        window.location.href = '<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/app';
    }, 5000);
<?php }
echo '</script'; ?>
>

</body>
</html>
<?php }
}
