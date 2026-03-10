{include file="components/head.tpl"}

{assign var=auth_state value="login"}
{if $smarty.get.mode == "register" || $smarty.get.mode == "signup"}
  {assign var=auth_state value="signup"}
{elseif $smarty.get.mode == "forgot" || $smarty.get.mode == "reset"}
  {assign var=auth_state value="forgot"}
{/if}

<div id="pageLoader" class="page-loader" aria-hidden="true">
  <div class="page-loader-card">
    <div class="spinner"></div>
    <p>Loading...</p>
  </div>
</div>

<div class="auth-page auth-page--landing">
  <div class="auth-shell auth-shell--landing" data-auth-state="{$auth_state}">
    <header class="auth-header">
      <div class="auth-brand">
        <img src="{$smarty.const.APP_URL}/assets/images/icon.png" alt="ApilageAI" class="auth-logo" width="44" height="44" style="width:44px;height:44px;" />
        <span>ApilageAI</span>
      </div>

      <div class="auth-headline auth-headline-login">
        <div class="auth-kicker">Your AI workspace.</div>
        <h1>Log in to your <span>ApilageAI</span> account</h1>
        <p>Use a trusted provider or continue with email.</p>
      </div>

      <div class="auth-headline auth-headline-signup">
        <div class="auth-kicker">Start your workspace.</div>
        <h1>Create your <span>ApilageAI</span> account</h1>
        <p>It takes less than a minute. No credit card required.</p>
      </div>

      <div class="auth-headline auth-headline-forgot">
        <div class="auth-kicker">Reset access.</div>
        <h1>Recover your <span>ApilageAI</span> account</h1>
        <p>We will send you a secure link to reset your password.</p>
      </div>
    </header>

    {if isset($smarty.get.error)}
      <div class="auth-alert error" role="alert" style="display:block;">
        {if $smarty.get.error == 'google_auth_failed'}
          Google sign-in failed. Please try again.
        {elseif $smarty.get.error == 'google_already_linked'}
          This Google account is already linked to another user.
        {elseif $smarty.get.error == 'disposable_email_not_allowed'}
          Disposable email addresses are not allowed for social sign-in.
        {elseif $smarty.get.error == 'magic_expired'}
          Your login link expired. Please request a new one.
        {else}
          Authentication failed. Please try again.
        {/if}
      </div>
    {/if}

    <div class="auth-providers">
      <a class="auth-provider" id="loginGoogle" href="{$smarty.const.APP_URL}/auth/google" aria-label="Continue with Google">
        <span class="auth-provider-badge auth-provider-badge--easy">Easy</span>
        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/3/3c/Google_Favicon_2025.svg/250px-Google_Favicon_2025.svg.png" alt="Google" width="20" height="20" style="width:20px;height:20px;" />
        <span>Google</span>
      </a>
      <a class="auth-provider" id="toggleEmailAuth" href="#" data-email-target="login" aria-label="Log in with Email">
        <i class="fa-regular fa-envelope"></i>
        <span>Log in with Email</span>
      </a>
      <a class="auth-provider" id="loginGuest" href="{$smarty.const.APP_URL}/auth/guest" aria-label="Continue without account">
        <span class="auth-provider-badge auth-provider-badge--limited">Limited acess</span>
        <i class="fa-regular fa-user"></i>
        <span>Continue without account</span>
      </a>
    </div>

    <div class="auth-panel" data-auth-panel="login" {if $auth_state != 'login'}style="display:none;"{/if}>
      <div class="auth-email-panel" data-email-panel="login" style="display:none;">
        <form id="loginForm" class="auth-form">
          <div class="auth-field">
            <label>Email</label>
            <input type="email" name="e" autocomplete="email" autocapitalize="none" required placeholder="name@company.com" />
          </div>

          <div class="auth-field">
            <label>Password</label>
            <div class="auth-password">
              <input type="password" id="loginPassword" name="p" autocomplete="current-password" required placeholder="Enter your password" />
              <button type="button" id="toggleLoginPassword" aria-label="Toggle password visibility"><i class="fa-regular fa-eye"></i></button>
            </div>
          </div>

          <div class="auth-captcha">
            <div id="loginCaptcha" class="cf-turnstile" data-sitekey="{$smarty.const.RECAPTCHA_SITE_KEY}"></div>
          </div>

          <div id="loginAlert" class="auth-alert" role="alert"></div>
          <div id="resendWrap" class="auth-resend" style="display:none;">
            <button type="button" id="resendBtn" class="auth-link-button" data-resend-label="Resend verification email" data-resend-cooldown-prefix="Resend in">
              Resend verification email
            </button>
          </div>

          <button type="submit" class="auth-submit">
            <span class="auth-submit__label">Log in</span>
            <span class="auth-submit__spinner" aria-hidden="true"></span>
          </button>
        </form>
      </div>

      <div class="auth-foot">
        <span>Forgot password?</span>
        <a href="#" class="auth-link" data-auth-tab="forgot">Reset it</a>
      </div>
      <div class="auth-foot">
        <span>New here?</span>
        <a href="#" class="auth-link" data-auth-tab="signup">Create an account</a>
      </div>
    </div>

    <div class="auth-panel" data-auth-panel="signup" {if $auth_state != 'signup'}style="display:none;"{/if}>
      <div class="auth-email-panel" data-email-panel="signup" style="display:none;">
        <form id="signupForm" class="auth-form">
          <div class="auth-grid">
            <div class="auth-field">
              <label>First Name</label>
              <input type="text" name="f" autocomplete="given-name" required placeholder="First name" />
            </div>
            <div class="auth-field">
              <label>Last Name</label>
              <input type="text" name="l" autocomplete="family-name" required placeholder="Last name" />
            </div>
          </div>

          <div class="auth-field">
            <label>Email</label>
            <input type="email" name="e" autocomplete="email" autocapitalize="none" required placeholder="name@company.com" />
          </div>

          <div class="auth-field">
            <label>Phone</label>
            <input type="tel" id="signupPhone" name="t" autocomplete="tel" inputmode="numeric" required placeholder="Phone number" />
          </div>

          <div class="auth-field">
            <label>Password</label>
            <div class="auth-password">
              <input type="password" id="signupPassword" name="p" autocomplete="new-password" required placeholder="Create a password" />
              <button type="button" id="toggleSignupPassword" aria-label="Toggle password visibility"><i class="fa-regular fa-eye"></i></button>
            </div>
            <small>Use at least 5 characters.</small>
          </div>

          <div class="auth-field">
            <label>Confirm Password</label>
            <div class="auth-password">
              <input type="password" id="signupConfirmPassword" name="p-c" autocomplete="new-password" required placeholder="Confirm your password" />
              <button type="button" id="toggleSignupConfirmPassword" aria-label="Toggle password visibility"><i class="fa-regular fa-eye"></i></button>
            </div>
          </div>

          <div class="auth-captcha">
            <div id="signupCaptcha" class="cf-turnstile" data-sitekey="{$smarty.const.RECAPTCHA_SITE_KEY}"></div>
          </div>

          <div id="signupAlert" class="auth-alert" role="alert"></div>
          <button type="submit" class="auth-submit">
            <span class="auth-submit__label">Create account</span>
            <span class="auth-submit__spinner" aria-hidden="true"></span>
          </button>

          <div class="auth-legal">
            By continuing, you agree to our
            <a href="{$smarty.const.APP_URL}/termsofservice/">Terms of Service</a>.
          </div>
        </form>
      </div>

      <div class="auth-foot">
        <span>Already have an account?</span>
        <a href="#" class="auth-link" data-auth-tab="login">Log in</a>
      </div>
    </div>

    <div class="auth-panel" data-auth-panel="forgot" {if $auth_state != 'forgot'}style="display:none;"{/if}>
      <form id="forgotForm" class="auth-form">
        <div class="auth-field">
          <label>Email</label>
          <input type="email" name="email" autocomplete="email" autocapitalize="none" required placeholder="name@company.com" />
        </div>

        <div class="auth-captcha">
          <div id="forgotCaptcha" class="cf-turnstile" data-sitekey="{$smarty.const.RECAPTCHA_SITE_KEY}"></div>
        </div>

        <div id="forgotAlert" class="auth-alert" role="alert"></div>
        <button type="submit" class="auth-submit">Send reset link</button>

        <div class="auth-foot">
          <span>Remembered your password?</span>
          <a href="#" class="auth-link" data-auth-tab="login">Back to login</a>
        </div>
      </form>
    </div>

    <div class="auth-footer">© 2026 ApilageAI. All rights reserved.</div>
  </div>

  <div id="loadingOverlay" class="auth-loading" style="display:none;">
    <div class="auth-loading-card">
      <div class="spinner"></div>
      <p>Working on it...</p>
    </div>
  </div>

  <div id="signupSuccessOverlay" class="auth-success-overlay" style="display:none;">
    <div class="auth-success-card">
      <div class="auth-success-icon">
        <i class="fa-solid fa-circle-check"></i>
      </div>
      <h3>Account created</h3>
      <p>Your account is created. Check your inbox to continue.</p>
      <div class="auth-success-actions">
        <button type="button" id="successResendBtn" class="auth-submit auth-submit-outline" data-resend-label="Resend email" data-resend-cooldown-prefix="Resend in">Resend email</button>
        <button type="button" class="auth-submit auth-submit-ghost" onclick="window.location.href='{$smarty.const.APP_URL}/auth/login'">Back to login</button>
      </div>
    </div>
  </div>

  <div class="auth-help" data-help-menu>
    <button type="button" class="auth-help-toggle" aria-expanded="false" aria-controls="authHelpMenu">?</button>
    <div id="authHelpMenu" class="auth-help-menu" role="menu">
      <div class="auth-help-title">Theme</div>
      <button type="button" class="auth-help-item" data-theme-choice="system">Default device theme</button>
      <button type="button" class="auth-help-item" data-theme-choice="light">Light mode</button>
      <button type="button" class="auth-help-item" data-theme-choice="dark">Dark mode</button>
      <div class="auth-help-divider"></div>
      <a class="auth-help-link" href="{$smarty.const.APP_URL}/termsofservice/">See terms and conditions</a>
      <a class="auth-help-link" href="{$smarty.const.APP_URL}/privacypolicy/">See privacy policy</a>
    </div>
  </div>
</div>

<script>
  window.AUTH_APP_BASE = '{$smarty.const.APP_URL}';
  window.AUTH_CAPTCHA_SITE_KEY = '{$smarty.const.RECAPTCHA_SITE_KEY}';
  window.AUTH_DEBUG = {if $smarty.const.APP_DEBUG}true{else}false{/if};
</script>
<script src="{$smarty.const.APP_URL}/assets/scripts/libs/dialog-js/main.min.js?V=01.03.04.2025"></script>
<script src="{$smarty.const.APP_URL}/assets/scripts/auth-traditional.js?V={get_hash_token()}"></script>
<script>
  (function () {
    const themeStorageKey = 'theme';
    const root = document.documentElement;
    const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
    const helpMenu = document.querySelector('[data-help-menu]');
    const helpToggle = helpMenu ? helpMenu.querySelector('.auth-help-toggle') : null;
    const helpPanel = helpMenu ? helpMenu.querySelector('.auth-help-menu') : null;
    const themeButtons = helpMenu ? helpMenu.querySelectorAll('[data-theme-choice]') : [];

    function applyTheme(choice) {
      if (!choice || choice === 'system') {
        root.classList.remove('dark');
        root.removeAttribute('data-theme');
        localStorage.removeItem(themeStorageKey);
        return;
      }
      const theme = choice === 'dark' ? 'dark' : 'light';
      if (theme === 'dark') {
        root.classList.add('dark');
      } else {
        root.classList.remove('dark');
      }
      root.setAttribute('data-theme', theme);
      localStorage.setItem(themeStorageKey, theme);
    }

    const savedTheme = localStorage.getItem(themeStorageKey);
    if (savedTheme) {
      applyTheme(savedTheme);
    } else if (prefersDarkScheme.matches) {
      root.classList.add('dark');
      root.setAttribute('data-theme', 'dark');
    } else {
      root.classList.remove('dark');
      root.setAttribute('data-theme', 'light');
    }

    if (helpToggle && helpPanel) {
      helpToggle.addEventListener('click', () => {
        const isOpen = helpMenu.classList.toggle('auth-help-open');
        helpToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      });
      document.addEventListener('click', (event) => {
        if (!helpMenu.contains(event.target)) {
          helpMenu.classList.remove('auth-help-open');
          helpToggle.setAttribute('aria-expanded', 'false');
        }
      });
    }

    if (themeButtons.length) {
      themeButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
          applyTheme(btn.dataset.themeChoice);
          if (helpMenu) helpMenu.classList.remove('auth-help-open');
          if (helpToggle) helpToggle.setAttribute('aria-expanded', 'false');
        });
      });
    }

    const loader = document.getElementById('pageLoader');
    if (!loader) return;
    const hide = () => {
      loader.classList.add('page-loader--hide');
      setTimeout(() => {
        loader.remove();
      }, 400);
    };
    if (document.readyState === 'complete') {
      hide();
    } else {
      window.addEventListener('load', hide, { once: true });
    }
  })();
</script>
