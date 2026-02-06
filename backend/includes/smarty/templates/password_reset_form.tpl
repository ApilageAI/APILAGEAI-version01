{include file="components/head.tpl"}

<div class="auth-page">
  <div class="auth-shell auth-split">
    <aside class="auth-hero">
      <div class="auth-hero-top">
        <div class="auth-star">
          <span></span>
          <span></span>
        </div>
        <h1>Reset Your Password</h1>
        <p>Choose a new password to keep your ApilageAI account secure.</p>
      </div>
      <div class="auth-hero-footer">© 2026 ApilageAI. All rights reserved.</div>
    </aside>

    <section class="auth-card auth-card-plain">
      <div class="auth-brand">ApilageAI</div>

      {if isset($result.e) && $result.e}
        <div class="auth-alert error" style="display:block;">
          {$result.m}
        </div>
        {if isset($result.expired) && $result.expired}
          <p class="auth-footnote">If your link expired, <a class="auth-link" href="{$base_url}/auth/reset-request">request a new reset</a>.</p>
        {/if}
      {/if}

      {if (empty($reset_complete)) && (!isset($result.e) || !$result.e)}
      <div class="auth-header">
        <h2>Set a new password</h2>
        <p>Password must be longer than 5 characters.</p>
      </div>

      <form id="resetPasswordForm" class="auth-form" method="POST" action="{$base_url}/auth/reset-password?token={$smarty.get.token}">
        <label>New Password</label>
        <div class="auth-password">
          <input type="password" id="password" name="password" required placeholder="Enter new password" />
          <button type="button" id="toggleResetPassword"><i class="fa-regular fa-eye"></i></button>
        </div>

        <label>Confirm New Password</label>
        <div class="auth-password">
          <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm new password" />
          <button type="button" id="toggleResetConfirmPassword"><i class="fa-regular fa-eye"></i></button>
        </div>

        <button type="submit" class="auth-submit auth-submit-dark">Save New Password</button>

        <div class="auth-footnote">
          <span>Back to</span>
          <a href="{$base_url}/auth/login" class="auth-link">Login</a>
        </div>
      </form>
      {/if}
    </section>
  </div>

  <div id="resetSuccessOverlay" class="auth-loading" style="display:none;">
    <div class="auth-success-card">
      <div class="auth-success-icon">
        <i class="fa fa-check"></i>
      </div>
      <h3>Password Updated</h3>
      <p>Redirecting you back to login...</p>
    </div>
  </div>
</div>

<script>
  (function () {
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
              linear-gradient(180deg, #4543e8 0%, #2d2fc2 45%, #151a7e 100%);
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
  font-size: 40px;
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
  font-size: 24px;
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
.auth-submit {
  margin-top: 10px;
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
.auth-alert {
  padding: 10px 12px;
  border-radius: 10px;
  font-size: 13px;
}
.auth-alert.error {
  background: #fee2e2;
  color: #b91c1c;
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
