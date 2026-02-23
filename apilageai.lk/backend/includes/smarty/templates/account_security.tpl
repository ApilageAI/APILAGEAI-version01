{include file="components/head.tpl"}

<div class="min-h-screen bg-white bg-grid-pattern text-brand-dark font-sans selection:bg-brand-red selection:text-white">
  <nav id="navbar" class="navbar-normal fixed top-0 left-0 right-0 z-50 transition-all duration-300">
    <div class="container mx-auto px-6 flex items-center justify-between">
      <a href="{$smarty.const.APP_URL}/" class="flex items-center gap-3">
        <img src="{$smarty.const.APP_URL}/assets/images/icon.png" alt="ApilageAI Logo" class="w-10 h-10 object-contain" />
        <span class="text-xl font-bold font-display text-brand-dark tracking-tight">
          Apilage<span class="text-brand-red underline decoration-wavy decoration-2 underline-offset-4">AI</span>
        </span>
      </a>
      <div class="flex items-center gap-4 text-sm font-bold text-brand-dark/80">
        <a href="{$smarty.const.APP_URL}/help" class="hover:text-brand-red hover:underline decoration-2 underline-offset-4 transition-all">Help</a>
        <a href="{$smarty.const.APP_URL}/auth/login" class="btn-primary !py-2 !px-5 !text-sm">Log in</a>
      </div>
    </div>
  </nav>

  <main class="pt-28 pb-20">
    <section class="container mx-auto px-6">
      <div class="max-w-5xl mx-auto">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-blueLight border-2 border-brand-dark text-brand-dark text-xs font-bold mb-6 uppercase tracking-wider">Account Security</div>
        <h1 class="text-4xl md:text-6xl font-display font-black text-brand-dark mb-4">Protect your ApilageAI account</h1>
        <p class="text-brand-dark/70 text-base md:text-lg font-medium">Report security problems, reset your password, request account removal, and review sign-in activity. Last updated: February 23, 2026.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-10">
          <a href="#report-security" class="border-2 border-brand-dark rounded-2xl p-5 bg-white shadow-hard-sm hover:-translate-y-1 transition-all">
            <div class="text-xl font-display font-black text-brand-dark">Report a security issue</div>
            <p class="text-sm text-brand-dark/70 font-medium mt-1">Tell us about suspicious activity, phishing, or account compromise.</p>
          </a>
          <a href="{$smarty.const.APP_URL}/auth/reset-request" class="border-2 border-brand-dark rounded-2xl p-5 bg-white shadow-hard-sm hover:-translate-y-1 transition-all">
            <div class="text-xl font-display font-black text-brand-dark">Reset your password</div>
            <p class="text-sm text-brand-dark/70 font-medium mt-1">Send a password reset link to your verified email.</p>
          </a>
          <a href="#removal-request" class="border-2 border-brand-dark rounded-2xl p-5 bg-white shadow-hard-sm hover:-translate-y-1 transition-all">
            <div class="text-xl font-display font-black text-brand-dark">Request account removal</div>
            <p class="text-sm text-brand-dark/70 font-medium mt-1">Submit a removal request if you cannot access your account.</p>
          </a>
          <a href="#sign-in-activity" class="border-2 border-brand-dark rounded-2xl p-5 bg-white shadow-hard-sm hover:-translate-y-1 transition-all">
            <div class="text-xl font-display font-black text-brand-dark">Review sign-in activity</div>
            <p class="text-sm text-brand-dark/70 font-medium mt-1">See recent sign-ins and respond to unknown devices.</p>
          </a>
        </div>

        <section id="report-security" class="mt-14">
          <div class="border-2 border-brand-dark rounded-2xl p-6 md:p-8 bg-white shadow-hard">
            <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">Report a security problem</h2>
            <p class="text-brand-dark/70 text-base font-medium">Use this form to report suspicious logins, phishing messages, or account compromise. Do not include passwords or payment details.</p>

            <form class="mt-6 grid gap-4" data-security-form data-report-prefix="Security Report" action="{$smarty.const.APP_URL}/reportdata.php" method="post" enctype="multipart/form-data">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex flex-col gap-2">
                  <label class="text-sm font-bold text-brand-dark" for="securityEmail">Email address (optional)</label>
                  <input id="securityEmail" name="email" type="email" placeholder="name@email.com" value="{$security_user_email|escape}" class="border-2 border-brand-dark rounded-xl px-4 py-3 text-sm font-medium focus:outline-none" />
                </div>
                <div class="flex flex-col gap-2">
                  <label class="text-sm font-bold text-brand-dark" for="securityScreenshot">Screenshot (optional)</label>
                  <input id="securityScreenshot" name="screenshot" type="file" accept="image/png,image/jpeg,image/gif,image/webp" class="border-2 border-brand-dark rounded-xl px-4 py-3 text-sm font-medium bg-white" />
                </div>
              </div>
              <div class="flex flex-col gap-2">
                <label class="text-sm font-bold text-brand-dark" for="securityProblem">Describe the issue</label>
                <textarea id="securityProblem" name="problem" rows="5" placeholder="Tell us what happened, when it happened, and any steps you already took." class="border-2 border-brand-dark rounded-xl px-4 py-3 text-sm font-medium focus:outline-none" required></textarea>
                <p class="text-xs text-brand-dark/60 font-medium">Minimum 4 words. Your report goes to the ApilageAI security team.</p>
              </div>
              <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="btn-primary">Submit security report</button>
                <span class="text-sm font-medium text-brand-dark/70" data-form-status role="status" aria-live="polite"></span>
              </div>
            </form>
          </div>
        </section>

        <section id="password-reset" class="mt-14">
          <div class="border-2 border-brand-dark rounded-2xl p-6 md:p-8 bg-brand-blueLight/40 shadow-hard">
            <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">Request a password change</h2>
            <p class="text-brand-dark/70 text-base font-medium">Reset your password if you suspect someone has access, or if you want to sign out all other devices.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
              <div class="bg-white border-2 border-brand-dark rounded-xl p-4">
                <h3 class="text-lg font-bold text-brand-dark">Send reset link</h3>
                <p class="text-sm text-brand-dark/70 font-medium mt-1">We will email a password reset link to your verified address.</p>
                <a href="{$smarty.const.APP_URL}/auth/reset-request" class="btn-primary mt-4 inline-flex">Reset password</a>
              </div>
              <div class="bg-white border-2 border-brand-dark rounded-xl p-4">
                <h3 class="text-lg font-bold text-brand-dark">Change from settings</h3>
                <p class="text-sm text-brand-dark/70 font-medium mt-1">If you are logged in, open your account settings to update your password.</p>
                <a href="{$smarty.const.APP_URL}/app?open=preferences" class="btn-primary mt-4 inline-flex">Open settings</a>
              </div>
            </div>
          </div>
        </section>

        <section id="removal-request" class="mt-14">
          <div class="border-2 border-brand-dark rounded-2xl p-6 md:p-8 bg-white shadow-hard">
            <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">Request account removal</h2>
            <p class="text-brand-dark/70 text-base font-medium">If you cannot access your account, submit a removal request. We may ask for verification before processing.</p>
            <div class="flex flex-wrap items-center gap-3 mt-4">
              <a href="{$smarty.const.APP_URL}/data-deletion" class="btn-primary">Read deletion steps</a>
              <span class="text-sm text-brand-dark/60 font-medium">Already logged in? Delete your account inside Settings &gt; Danger Zone.</span>
            </div>

            <form class="mt-6 grid gap-4" data-security-form data-report-prefix="Removal Request" action="{$smarty.const.APP_URL}/reportdata.php" method="post" enctype="multipart/form-data">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex flex-col gap-2">
                  <label class="text-sm font-bold text-brand-dark" for="removalEmail">Account email</label>
                  <input id="removalEmail" name="email" type="email" placeholder="name@email.com" value="{$security_user_email|escape}" class="border-2 border-brand-dark rounded-xl px-4 py-3 text-sm font-medium focus:outline-none" required />
                </div>
                <div class="flex flex-col gap-2">
                  <label class="text-sm font-bold text-brand-dark" for="removalScreenshot">Screenshot (optional)</label>
                  <input id="removalScreenshot" name="screenshot" type="file" accept="image/png,image/jpeg,image/gif,image/webp" class="border-2 border-brand-dark rounded-xl px-4 py-3 text-sm font-medium bg-white" />
                </div>
              </div>
              <div class="flex flex-col gap-2">
                <label class="text-sm font-bold text-brand-dark" for="removalProblem">Tell us what you want removed</label>
                <textarea id="removalProblem" name="problem" rows="4" placeholder="Example: Please remove my account because I no longer use the service." class="border-2 border-brand-dark rounded-xl px-4 py-3 text-sm font-medium focus:outline-none" required></textarea>
                <p class="text-xs text-brand-dark/60 font-medium">Minimum 4 words. For faster processing, include the email used to register.</p>
              </div>
              <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="btn-primary">Submit removal request</button>
                <span class="text-sm font-medium text-brand-dark/70" data-form-status role="status" aria-live="polite"></span>
              </div>
            </form>
          </div>
        </section>

        <section id="sign-in-activity" class="mt-14">
          <div class="border-2 border-brand-dark rounded-2xl p-6 md:p-8 bg-brand-gray shadow-hard">
            <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">Unrecognized login activity</h2>
            <p class="text-brand-dark/70 text-base font-medium">If you see a sign-in you do not recognize, reset your password immediately and report the incident.</p>

            <div class="mt-6 bg-white border-2 border-brand-dark rounded-2xl p-4">
              <h3 class="text-lg font-bold text-brand-dark mb-3">Recent sign-ins</h3>
              {if $security_is_logged_in}
                {if !$security_sessions_available}
                  <p class="text-sm text-brand-dark/70 font-medium">Sign-in history is unavailable right now. Please check back later.</p>
                {elseif $security_has_sessions}
                  <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                      <thead>
                        <tr class="text-left text-brand-dark">
                          <th class="py-2 pr-4">Device</th>
                          <th class="py-2 pr-4">IP address</th>
                          <th class="py-2 pr-4">Last seen</th>
                          <th class="py-2">Status</th>
                        </tr>
                      </thead>
                      <tbody class="text-brand-dark/80 font-medium">
                        {foreach from=$security_sessions item=session}
                          <tr class="border-t border-brand-dark/20">
                            <td class="py-2 pr-4" title="{$session.client|escape}">
                              {$session.client|truncate:60|escape}
                            </td>
                            <td class="py-2 pr-4">{$session.ip|escape}</td>
                            <td class="py-2 pr-4">{$session.last_seen|escape}</td>
                            <td class="py-2">
                              {if $session.is_current}
                                <span class="inline-flex items-center gap-1 text-xs font-bold px-2 py-1 rounded-full bg-brand-blueLight border border-brand-dark">This device</span>
                              {else}
                                <span class="inline-flex items-center gap-1 text-xs font-bold px-2 py-1 rounded-full bg-white border border-brand-dark">Active</span>
                              {/if}
                            </td>
                          </tr>
                        {/foreach}
                      </tbody>
                    </table>
                  </div>
                  <p class="text-xs text-brand-dark/60 font-medium mt-3">Times are shown in server time. Resetting your password signs out other sessions.</p>
                {else}
                  <p class="text-sm text-brand-dark/70 font-medium">No active sign-in sessions were found for your account.</p>
                {/if}
              {else}
                <p class="text-sm text-brand-dark/70 font-medium">Log in to view recent sign-in activity for your account.</p>
                <a href="{$smarty.const.APP_URL}/auth/login" class="btn-primary mt-4 inline-flex">Log in</a>
              {/if}
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
              <div class="bg-white border-2 border-brand-dark rounded-xl p-4">
                <h4 class="text-base font-bold text-brand-dark">Step 1: Reset password</h4>
                <p class="text-sm text-brand-dark/70 font-medium mt-1">This signs out other devices and blocks unauthorized access.</p>
              </div>
              <div class="bg-white border-2 border-brand-dark rounded-xl p-4">
                <h4 class="text-base font-bold text-brand-dark">Step 2: Report the issue</h4>
                <p class="text-sm text-brand-dark/70 font-medium mt-1">Share details of the login you do not recognize.</p>
              </div>
              <div class="bg-white border-2 border-brand-dark rounded-xl p-4">
                <h4 class="text-base font-bold text-brand-dark">Step 3: Secure your email</h4>
                <p class="text-sm text-brand-dark/70 font-medium mt-1">Update your email password and enable its security checks.</p>
              </div>
            </div>
          </div>
        </section>

        <section class="mt-14">
          <div class="border-2 border-brand-dark rounded-2xl p-6 md:p-8 bg-white shadow-hard">
            <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">Security tips and features</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <h3 class="text-lg font-bold text-brand-dark">What ApilageAI does</h3>
                <ul class="mt-3 space-y-2 text-sm text-brand-dark/80 font-medium list-disc list-inside">
                  <li>We hash passwords and never store them in plain text.</li>
                  <li>New sessions are checked for device consistency and limited to five active sessions.</li>
                  <li>Password resets invalidate existing sessions for extra protection.</li>
                  <li>Security notifications may be sent when a new device signs in.</li>
                </ul>
              </div>
              <div>
                <h3 class="text-lg font-bold text-brand-dark">What you should do</h3>
                <ul class="mt-3 space-y-2 text-sm text-brand-dark/80 font-medium list-disc list-inside">
                  <li>Use a long, unique password for your ApilageAI account.</li>
                  <li>Never share your password or verification links.</li>
                  <li>Watch for phishing links and unexpected login emails.</li>
                  <li>Update your email security to protect password resets.</li>
                </ul>
              </div>
            </div>
            <div class="mt-6 flex flex-wrap items-center gap-3">
              <a href="{$smarty.const.APP_URL}/help" class="btn-primary">Visit Help Center</a>
              <a href="{$smarty.const.APP_URL}/termsofservice" class="text-sm font-bold text-brand-dark/70 hover:text-brand-red">Terms of Service</a>
              <a href="{$smarty.const.APP_URL}/privacypolicy" class="text-sm font-bold text-brand-dark/70 hover:text-brand-red">Privacy Policy</a>
            </div>
          </div>
        </section>
      </div>
    </section>
  </main>
</div>

<script>
  (function () {
    const forms = document.querySelectorAll('[data-security-form]');
    if (!forms.length) return;

    forms.forEach((form) => {
      const statusEl = form.querySelector('[data-form-status]');
      const submitBtn = form.querySelector('button[type="submit"]');
      const prefix = form.dataset.reportPrefix || 'Report';

      form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (submitBtn) submitBtn.disabled = true;
        if (statusEl) statusEl.textContent = 'Submitting...';

        try {
          const emailInput = form.querySelector('input[type=\"email\"]');
          const emailValue = emailInput ? emailInput.value : '';
          const formData = new FormData(form);
          const message = String(formData.get('problem') || '').trim();
          if (!message) {
            if (statusEl) statusEl.textContent = 'Please describe the issue.';
            if (submitBtn) submitBtn.disabled = false;
            return;
          }
          formData.set('problem', prefix + ': ' + message);

          const res = await fetch(form.action, { method: 'POST', body: formData, credentials: 'include' });
          const data = await res.json();
          if (!res.ok || !data.success) {
            if (statusEl) statusEl.textContent = data.message || 'Unable to submit. Please try again.';
          } else {
            if (statusEl) statusEl.textContent = data.message || 'Submitted successfully.';
            form.reset();
            if (emailInput) emailInput.value = emailValue;
          }
        } catch (err) {
          if (statusEl) statusEl.textContent = 'Unable to submit. Please try again.';
        }

        if (submitBtn) submitBtn.disabled = false;
      });
    });
  })();
</script>

</body>
</html>
