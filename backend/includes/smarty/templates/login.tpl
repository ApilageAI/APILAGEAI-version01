{include file="components/head.tpl"}

<div class="auth-page">
  <div class="auth-shell auth-split">
    <aside class="auth-hero">
      <div class="auth-hero-top">
        <div class="auth-star">
          <span></span>
          <span></span>
        </div>
        <h1>Hello ApilageAI! <span>👋</span></h1>
        <p>Skip repetitive and manual tasks. Get highly productive through automation and save tons of time.</p>
      </div>
      <div class="auth-hero-footer">© 2026 ApilageAI. All rights reserved.</div>
    </aside>

    <section class="auth-card auth-card-plain">
      <div class="auth-brand">ApilageAI</div>
      <div class="auth-header">
        <h2>Welcome Back!</h2>
        <p>Don’t have an account? <a href="#" class="auth-link" data-auth-tab="signup">Create a new account now</a>, it’s FREE!</p>
      </div>
      {if isset($smarty.get.error)}
        <div class="auth-alert error" style="display:block;">
          {if $smarty.get.error == 'google_auth_failed'}
            Google sign-in failed. Please try again.
          {elseif $smarty.get.error == 'google_already_linked'}
            This Google account is already linked to another user.
          {elseif $smarty.get.error == 'disposable_email_not_allowed'}
            Disposable email addresses are not allowed for Google sign-in.
          {elseif $smarty.get.error == 'magic_expired'}
            Your login link expired. Please request a new one.
          {else}
            Authentication failed. Please try again.
          {/if}
        </div>
      {/if}

      <div class="auth-panel" data-auth-panel="login">
        <form id="loginForm" class="auth-form">
          <label>Email</label>
          <input type="email" name="e" required placeholder="Email address" />

          <label>Password</label>
          <div class="auth-password">
            <input type="password" id="loginPassword" name="p" required placeholder="Password" />
            <button type="button" id="toggleLoginPassword"><i class="fa-regular fa-eye"></i></button>
          </div>

          <div class="auth-captcha">
            <div id="loginCaptcha"></div>
          </div>

          <div id="loginAlert" class="auth-alert"></div>
          <div id="resendWrap" class="auth-resend" style="display:none;"></div>

          <button type="submit" class="auth-submit auth-submit-dark">Login Now</button>

          <div class="auth-divider">
            <span>or</span>
          </div>

          <a class="auth-social-btn auth-social-wide" id="loginGoogle" href="{$smarty.const.APP_URL}/auth/google"><i class="fab fa-google"></i> Login with Google</a>
          <a class="auth-social-btn auth-social-wide" id="loginGlobbook" href="https://globbook.com/api/oauth?app_id=56532326578385"><i class="fa fa-earth-asia"></i> Login with Globbook</a>
          <a class="auth-social-btn auth-social-wide" id="loginFacebook" href="#"><i class="fab fa-facebook"></i> Login with Facebook</a>
          <a class="auth-social-btn auth-social-wide auth-guest-btn" id="loginGuest" href="{$smarty.const.APP_URL}/auth/guest"><i class="fa fa-user"></i> Continue without account</a>

          <div class="auth-footnote">
            <span>Forget password</span>
            <a href="#" class="auth-link" data-auth-tab="forgot">Click here</a>
          </div>
        </form>
      </div>

      <div class="auth-panel" data-auth-panel="signup" style="display:none;">
        <div class="auth-header">
          <h2>Create Account</h2>
          <p>Already have an account? <a href="#" class="auth-link" data-auth-tab="login">Login here</a>.</p>
        </div>
        <form id="signupForm" class="auth-form">
          <label>First Name</label>
          <input type="text" name="f" required placeholder="First name" />

          <label>Last Name</label>
          <input type="text" name="l" required placeholder="Last name" />

          <label>Email</label>
          <input type="email" name="e" required placeholder="Email address" />

          <label>Phone</label>
          <input type="tel" id="signupPhone" name="t" required placeholder="Phone number" />

          <label>Password</label>
          <div class="auth-password">
            <input type="password" id="signupPassword" name="p" required placeholder="Password" />
            <button type="button" id="toggleSignupPassword"><i class="fa-regular fa-eye"></i></button>
          </div>
          <small>Password must be at least 5 characters.</small>

          <label>Confirm Password</label>
          <div class="auth-password">
            <input type="password" id="signupConfirmPassword" name="p-c" required placeholder="Confirm password" />
            <button type="button" id="toggleSignupConfirmPassword"><i class="fa-regular fa-eye"></i></button>
          </div>

          <div class="auth-captcha">
            <div id="signupCaptcha"></div>
          </div>

          <div id="signupAlert" class="auth-alert"></div>
          <button type="submit" class="auth-submit auth-submit-dark">Create Account</button>
        </form>
      </div>

      <div class="auth-panel" data-auth-panel="forgot" style="display:none;">
        <div class="auth-header">
          <h2>Reset Password</h2>
          <p>Enter your email to receive a reset link.</p>
        </div>
        <form id="forgotForm" class="auth-form">
          <label>Email</label>
          <input type="email" name="email" required placeholder="Your email" />

          <div class="auth-captcha">
            <div id="forgotCaptcha"></div>
          </div>

          <div id="forgotAlert" class="auth-alert"></div>
          <button type="submit" class="auth-submit auth-submit-dark">Send Reset Link</button>
        </form>
      </div>
    </section>
  </div>

  <div id="loadingOverlay" class="auth-loading" style="display:none;">
    <div class="auth-loading-card">
      <div class="spinner"></div>
      <p>Working on it…</p>
    </div>
  </div>

  <div id="signupSuccessOverlay" class="auth-loading" style="display:none;">
    <div class="auth-success-card">
      <div class="auth-success-icon">
        <i class="fa fa-check"></i>
      </div>
      <h3>Account Created!</h3>
      <p>Check your inbox to verify your email and continue to the app.</p>
      <button type="button" id="successResendBtn" class="auth-submit auth-submit-outline">Resend verification email</button>
    </div>
  </div>
</div>

<script>
  window.AUTH_APP_BASE = '{$smarty.const.APP_URL}';
  window.AUTH_CAPTCHA_SITE_KEY = '{$smarty.const.RECAPTCHA_SITE_KEY}';
</script>
<script src="{$smarty.const.APP_URL}/assets/scripts/libs/dialog-js/main.min.js?V=01.03.04.2025"></script>
<script src="{$smarty.const.APP_URL}/assets/scripts/auth-traditional.js?V={get_hash_token()}"></script>

<style>
body {
  background: var(--bg-color);
  font-family: var(--font-family, "Plus Jakarta Sans", "Outfit", sans-serif);
  color: var(--text-primary);
}
.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 32px 20px;
  background: #f4f5fb;
}
.auth-shell {
  width: min(1100px, 100%);
  display: grid;
  gap: 0;
  border-radius: 24px;
  overflow: hidden;
  background: #fff;
  box-shadow: 0 24px 60px rgba(17, 24, 39, 0.16);
}
.auth-split {
  grid-template-columns: 1fr 0.95fr;
}
.auth-hero {
  background: radial-gradient(circle at top right, rgba(255,255,255,0.08), rgba(255,255,255,0) 45%),
              linear-gradient(180deg, #e53e3e 0%, #c53030 45%, #9b2c2c 100%);
  color: #fff;
  padding: 48px 44px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  position: relative;
  overflow: hidden;
}
.auth-hero::after {
  content: '';
  position: absolute;
  width: 420px;
  height: 420px;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 50%;
  top: -120px;
  left: -60px;
  box-shadow: 0 0 0 60px rgba(255, 255, 255, 0.04);
}
.auth-hero-top h1 {
  font-size: 44px;
  margin: 34px 0 16px;
  line-height: 1.1;
}
.auth-hero-top p {
  color: rgba(255,255,255,0.75);
  margin: 0;
  max-width: 320px;
}
.auth-star {
  position: relative;
  width: 42px;
  height: 42px;
}
.auth-star span {
  position: absolute;
  inset: 0;
  border-radius: 999px;
  border: 3px solid rgba(255, 255, 255, 0.9);
  clip-path: polygon(48% 0%, 52% 0%, 52% 100%, 48% 100%);
}
.auth-star span:last-child {
  transform: rotate(90deg);
}
.auth-hero-footer {
  color: rgba(255, 255, 255, 0.6);
  font-size: 13px;
}
.auth-card {
  background: #fff;
  padding: 44px 40px;
}
.auth-card-plain {
  border-left: 1px solid rgba(15, 23, 42, 0.06);
}
.auth-brand {
  font-weight: 700;
  font-size: 22px;
  margin-bottom: 30px;
  color: #111827;
}
.auth-header h2 {
  margin: 0 0 6px;
  color: #111827;
  font-size: 26px;
}
.auth-header p {
  margin: 0 0 18px;
  color: #6b7280;
}
.auth-form {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.auth-form label {
  font-size: 12px;
  color: #9ca3af;
}
.auth-form input {
  padding: 14px 10px 10px;
  border-radius: 0;
  border: none;
  border-bottom: 2px solid #e5e7eb;
  font-size: 14px;
  background: transparent;
  color: #111827;
  transition: border-color 0.2s ease;
}
.auth-form input:focus {
  outline: none;
  border-color: #111827;
}
.auth-password {
  display: flex;
  align-items: center;
  gap: 8px;
}
.auth-password input {
  flex: 1;
}
.auth-password button {
  border: none;
  background: transparent;
  cursor: pointer;
  color: #9ca3af;
}
.auth-captcha {
  display: flex;
  justify-content: center;
  margin: 10px 0 6px;
}
.auth-submit {
  margin-top: 6px;
  padding: 12px 16px;
  border: none;
  border-radius: 10px;
  background: #111827;
  color: #fff;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}
.auth-submit:hover {
  background: #1f2937;
  transform: translateY(-1px);
}
.auth-submit-dark {
  background: #111827;
}
.auth-submit-dark:hover {
  background: #1f2937;
}
.auth-submit-outline {
  margin-top: 8px;
  background: #fff;
  color: #111827;
  border: 1px solid #e5e7eb;
}
.auth-submit-outline:hover {
  background: #f3f4f6;
}
.auth-divider {
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 12px 0;
  color: #9ca3af;
  font-size: 12px;
}
.auth-divider::before,
.auth-divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: #e5e7eb;
}
.auth-social-btn {
  text-align: center;
  padding: 12px 14px;
  border-radius: 10px;
  border: 1px solid #e5e7eb;
  color: #111827;
  text-decoration: none;
  font-weight: 600;
  font-size: 14px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background: #fff;
  transition: all 0.2s ease;
}
.auth-guest-btn {
  border-style: dashed;
  background: #f9fafb;
  color: #1f2937;
}
.auth-social-btn:hover {
  border-color: #111827;
  transform: translateY(-1px);
}
.auth-social-wide {
  width: 100%;
}
.auth-alert {
  display: none;
  padding: 10px 12px;
  border-radius: 10px;
  font-size: 13px;
}
.auth-alert.error {
  display: block;
  background: #fee2e2;
  color: #b91c1c;
}
.auth-alert.success {
  display: block;
  background: #dcfce7;
  color: #15803d;
}
.auth-resend {
  margin-top: 4px;
}
.auth-link-btn {
  background: none;
  border: none;
  color: #111827;
  cursor: pointer;
  font-weight: 600;
  padding: 0;
}
.auth-link {
  color: #111827;
  font-weight: 600;
  text-decoration: underline;
}
.auth-footnote {
  margin-top: 14px;
  display: flex;
  gap: 8px;
  align-items: center;
  font-size: 13px;
  color: #9ca3af;
}
.auth-loading {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  backdrop-filter: blur(6px);
}
.auth-loading-card {
  background: #fff;
  padding: 18px 24px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  gap: 12px;
}
.auth-success-card {
  background: #fff;
  padding: 28px 30px;
  border-radius: 18px;
  text-align: center;
  min-width: min(340px, 90vw);
  box-shadow: 0 20px 40px rgba(15, 23, 42, 0.25);
}
.auth-success-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto 16px;
  border-radius: 999px;
  background: #22c55e;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
}
.auth-success-card h3 {
  margin: 0 0 8px;
  color: #111827;
}
.auth-success-card p {
  margin: 0;
  color: #6b7280;
  font-size: 14px;
}
@media (max-width: 1060px) {
  .auth-shell {
    grid-template-columns: 1fr;
  }
  .auth-hero {
    order: 2;
  }
  .auth-card {
    order: 1;
  }
}
@media (max-width: 720px) {
  .auth-hero {
    padding: 24px;
  }
  .auth-card {
    padding: 32px 24px;
  }
}
</style>
</body>
</html>
