<?php
/* Smarty version 5.7.0, created on 2026-01-20 00:54:50
  from 'file:login.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.7.0',
  'unifunc' => 'content_696e8502939190_18495219',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'ec15592379d894bc2ee0b2e360a2cade603cbe20' => 
    array (
      0 => 'login.tpl',
      1 => 1761747847,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:components/head.tpl' => 1,
  ),
))) {
function content_696e8502939190_18495219 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = '/home/apilageai/domains/apilageai.lk/backend/includes/smarty/templates';
$_smarty_tpl->renderSubTemplate("file:components/head.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), (int) 0, $_smarty_current_dir);
echo '<script'; ?>
>

<div class="auth-layout">
    <div class="background-accent top-right"></div>
    <div class="background-accent bottom-left"></div>
    <div class="bg-pattern"></div>

    <div class="auth-wrapper">
        <!-- Left Image Section -->
        <div class="auth-image">
            <img src="http://localhost:8888/assets/images/login.jpg" alt="Login illustration" />
        </div>

        <!-- Right Form Section -->
        <div class="auth-container">
            <div class="header animate-slide-down">
                <h1>Welcome To Apilageai</h1>
                <p>Sign in to your account</p>
            </div>

            <div class="toggle-message animate-fade-in">
                <p>Are you a new member?? <a href="http://localhost:8888/auth/register" id="signupLink">Sign up</a></p>
            </div>

                        <?php if ((true && (true && null !== ($_GET['verified'] ?? null))) && $_GET['verified'] == 'success') {?>
            <div class="alert alert-success animate-fade-in" style="margin: 20px 0;">
                <i class="fa-solid fa-check-circle"></i>
                <span>Email verified successfully! You can now sign in.</span>
            </div>
            <?php }?>

            <?php if ((true && (true && null !== ($_GET['error'] ?? null))) && $_GET['error'] == 'email_not_verified') {?>
            <div class="alert alert-warning animate-fade-in" style="margin: 20px 0;">
                <i class="fa-solid fa-exclamation-triangle"></i>
                <span>Please verify your email before signing in.</span>
            </div>
            <?php }?>

            <div id="loadingOverlay" style="display: none;">
                <div class="overlay-background">
                    <div class="spinner"></div>
                </div>
            </div>

                        <div id="verificationModal" class="verification-modal" style="display: none;">
                <div class="modal-content animate-fade-in">
                    <div class="modal-header">
                        <i class="fa-solid fa-envelope-circle-check"></i>
                        <h3>Email Verification Required</h3>
                    </div>
                    <div class="modal-body">
                        <p id="verificationMessage">Please verify your email address before signing in. Check your inbox for the verification link.</p>
                        <p class="verification-email" id="verificationEmail"></p>
                        <p class="resend-text">Didn't receive the email?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" onclick="closeVerificationModal()">Close</button>
                        <button type="button" class="btn-primary" onclick="resendVerificationEmail()" id="resendBtn">
                            <i class="fa-solid fa-paper-plane"></i> Resend Email
                        </button>
                    </div>
                </div>
            </div>

            <div class="social-login">
                <div class="social-buttons">
                    <a href="https://globbook.com/api/oauth?app_id=56532326578385" class="social-button animate-slide-up" style="animation-delay: 0.1s">
                        <i class="fa-solid fa-earth-asia"></i> <span>Globbook</span>
                    </a>
                    <a href="http://localhost:8888/auth/google" class="social-button animate-slide-up" style="animation-delay: 0.15s">
                        <i class="fa-brands fa-google"></i> <span>Google</span>
                    </a>
                </div>
            </div>

                            <div class="auth-divider animate-fade-in" style="animation-delay: 0.3s">
                    or continue with
                </div>

            <button id="showEmailLoginBtn" class="auth-submit-button" style="margin-top:20px;">
                Sign in with Email <i class="fa-solid fa-envelope"></i>
            </button>

            <div class="auth-card animate-fade-in">
                <div id="emailLoginSection" style="display:none;">
                    <form id="loginForm" class="form">
                        <div class="form-group">
                            <input type="email" id="identifier" placeholder="Email" class="auth-input animate-slide-up" style="animation-delay: 0.1s" name="e" required />
                            <p class="error-message" id="identifierError"></p>
                        </div>

                        <div class="form-group">
                            <div class="password-input-container">
                                <input type="password" id="password" placeholder="Password" class="auth-input animate-slide-up" style="animation-delay: 0.15s" name="p" required />
                                <button type="button" class="toggle-password" id="togglePassword">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                            <p class="error-message" id="passwordError"></p>
                        </div>

                        <center>
                            <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY;?>
"></div>
                        </center>

                        <div class="forgot-password animate-fade-in" style="animation-delay: 0.2s">
                            <a href="http://localhost:8888/auth/reset-request" id="forgotPasswordLink">Forgot password?</a>
                        </div>

                        <button type="submit" class="auth-submit-button animate-slide-up" style="animation-delay: 0.25s">
                            Sign in <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</div>
<?php echo '<script'; ?>
>
document.getElementById("showEmailLoginBtn").addEventListener("click", function() {
    var section = document.getElementById("emailLoginSection");
    section.style.display = "block";
    this.style.display = "none";
});
<?php echo '</script'; ?>
>
	<?php echo '<script'; ?>
 src="http://localhost:8888/assets/scripts/libs/dialog-js/main.min.js?V=01.03.04.2025"><?php echo '</script'; ?>
>
	<?php echo '<script'; ?>
 src="http://localhost:8888/assets/scripts/auth-login.min.js?V=<?php echo $_smarty_tpl->getSmarty()->getModifierCallback('get_hash_token')();?>
"><?php echo '</script'; ?>
>
</body>
</html><?php }
}
