<?php
/* Smarty version 5.8.0, created on 2026-04-04 20:48:38
  from 'file:password_reset_form.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.8.0',
  'unifunc' => 'content_69d12bcec7ea71_44364096',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'bf98bbce50b5e5ca8264906b57c95e472e1da4d1' => 
    array (
      0 => 'password_reset_form.tpl',
      1 => 1773126387,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:components/head.tpl' => 1,
  ),
))) {
function content_69d12bcec7ea71_44364096 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = '/home/apilageai/domains/apilageai.lk/backend/includes/smarty/templates';
$_smarty_tpl->renderSubTemplate("file:components/head.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), (int) 0, $_smarty_current_dir);
?>

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
        <img src="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/assets/images/icon.png" alt="ApilageAI" class="auth-logo" width="44" height="44" style="width:44px;height:44px;" />
        <span>ApilageAI</span>
      </div>
      <div class="auth-kicker">Set a new password.</div>
      <h1>Reset your password</h1>
      <p>Create a new password to keep your account secure.</p>
    </header>

    <?php if ((true && (true && null !== ($_smarty_tpl->getValue('result')['e'] ?? null))) && $_smarty_tpl->getValue('result')['e']) {?>
      <div class="auth-alert error" role="alert" style="display:block;">
        <?php echo $_smarty_tpl->getValue('result')['m'];?>

      </div>
      <?php if ((true && (true && null !== ($_smarty_tpl->getValue('result')['expired'] ?? null))) && $_smarty_tpl->getValue('result')['expired']) {?>
        <p class="auth-footnote">If your link expired, <a class="auth-link" href="<?php echo $_smarty_tpl->getValue('base_url');?>
/auth/reset-request">request a new reset</a>.</p>
      <?php }?>
    <?php }?>

    <?php if ((( !$_smarty_tpl->hasVariable('reset_complete') || empty($_smarty_tpl->getValue('reset_complete')))) && (!(true && (true && null !== ($_smarty_tpl->getValue('result')['e'] ?? null))) || !$_smarty_tpl->getValue('result')['e'])) {?>
    <form id="resetPasswordForm" class="auth-form" method="POST" action="<?php echo $_smarty_tpl->getValue('base_url');?>
/auth/reset-password?token=<?php echo $_GET['token'];?>
">
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
        <a href="<?php echo $_smarty_tpl->getValue('base_url');?>
/auth/login" class="auth-link">Login</a>
      </div>
    </form>
    <?php }?>
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
      <a class="auth-help-link" href="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/termsofservice/">See terms and conditions</a>
      <a class="auth-help-link" href="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/privacypolicy/">See privacy policy</a>
    </div>
  </div>
</div>

<?php echo '<script'; ?>
>
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

    const resetComplete = <?php if ((true && ($_smarty_tpl->hasVariable('reset_complete') && null !== ($_smarty_tpl->getValue('reset_complete') ?? null))) && $_smarty_tpl->getValue('reset_complete')) {?>true<?php } else { ?>false<?php }?>;
    if (resetComplete) {
      const overlay = document.getElementById('resetSuccessOverlay');
      if (overlay) overlay.style.display = 'flex';
      setTimeout(() => {
        window.location.href = '<?php echo $_smarty_tpl->getValue('base_url');?>
/auth/login';
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
<?php echo '</script'; ?>
>
<?php }
}
