{include file="components/head.tpl"}

<div id="pageLoader" class="page-loader" aria-hidden="true">
  <div class="page-loader-card">
    <div class="spinner"></div>
    <p>Loading...</p>
  </div>
</div>

<div class="auth-page">
  <div class="auth-shell auth-shell-narrow">
    <header class="auth-header">
      <div class="auth-brand">
        <img src="{$smarty.const.APP_URL}/assets/images/icon.png" alt="ApilageAI" class="auth-logo" width="44" height="44" style="width:44px;height:44px;" />
        <span>ApilageAI</span>
      </div>
      <div class="auth-kicker">Set a new password.</div>
      <h1>Reset your password</h1>
      <p>Create a new password to keep your account secure.</p>
    </header>

    {if isset($result.e) && $result.e}
      <div class="auth-alert error" role="alert" style="display:block;">
        {$result.m}
      </div>
      {if isset($result.expired) && $result.expired}
        <p class="auth-footnote">If your link expired, <a class="auth-link" href="{$base_url}/auth/reset-request">request a new reset</a>.</p>
      {/if}
    {/if}

    {if (empty($reset_complete)) && (!isset($result.e) || !$result.e)}
    <form id="resetPasswordForm" class="auth-form" method="POST" action="{$base_url}/auth/reset-password?token={$smarty.get.token}">
      <div class="auth-field">
        <label>New Password</label>
        <div class="auth-password">
          <input type="password" id="password" name="password" autocomplete="new-password" required placeholder="Enter new password" />
          <button type="button" id="toggleResetPassword" aria-label="Toggle password visibility"><i class="fa-regular fa-eye"></i></button>
        </div>
      </div>

      <div class="auth-field">
        <label>Confirm New Password</label>
        <div class="auth-password">
          <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" required placeholder="Confirm new password" />
          <button type="button" id="toggleResetConfirmPassword" aria-label="Toggle password visibility"><i class="fa-regular fa-eye"></i></button>
        </div>
      </div>

      <button type="submit" class="auth-submit">Save new password</button>

      <div class="auth-foot">
        <span>Back to</span>
        <a href="{$base_url}/auth/login" class="auth-link">Login</a>
      </div>
    </form>
    {/if}
  </div>

  <div id="resetSuccessOverlay" class="auth-loading" style="display:none;">
    <div class="auth-loading-card">
      <div class="auth-success-icon">
        <i class="fa fa-check"></i>
      </div>
      <h3>Password updated</h3>
      <p>Redirecting you back to login...</p>
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
    if (loader) {
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
    }

    const resetComplete = {if isset($reset_complete) && $reset_complete}true{else}false{/if};
    if (resetComplete) {
      const overlay = document.getElementById('resetSuccessOverlay');
      if (overlay) overlay.style.display = 'flex';
      setTimeout(() => {
        window.location.href = '{$base_url}/auth/login';
      }, 1800);
    }

    function bindToggle(btnId, inputId) {
      const btn = document.getElementById(btnId);
      const input = document.getElementById(inputId);
      if (!btn || !input) return;
      btn.addEventListener('click', () => {
        const type = input.type === 'password' ? 'text' : 'password';
        input.type = type;
        btn.innerHTML = type === 'password'
          ? '<i class="fa-regular fa-eye"></i>'
          : '<i class="fa-regular fa-eye-slash"></i>';
      });
    }

    bindToggle('toggleResetPassword', 'password');
    bindToggle('toggleResetConfirmPassword', 'confirm_password');
  })();
</script>
