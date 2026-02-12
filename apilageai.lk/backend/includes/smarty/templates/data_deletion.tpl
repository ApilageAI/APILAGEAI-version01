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
        <a href="{$smarty.const.APP_URL}/" class="hover:text-brand-red hover:underline decoration-2 underline-offset-4 transition-all">Home</a>
        <a href="{$smarty.const.APP_URL}/app" class="btn-primary !py-2 !px-5 !text-sm">Open App</a>
      </div>
    </div>
  </nav>

  <main class="pt-28 pb-20">
    <section class="container mx-auto px-6">
      <div class="max-w-4xl mx-auto">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-blueLight border-2 border-brand-dark text-brand-dark text-xs font-bold mb-6 uppercase tracking-wider">Account</div>
        <h1 class="text-4xl md:text-6xl font-display font-black text-brand-dark mb-4">Data Deletion Instructions</h1>
        <p class="text-brand-dark/70 text-base md:text-lg font-medium">Last updated: February 12, 2026</p>

        <div class="mt-10">
          <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">How to delete your data</h2>
          <ol class="mt-4 space-y-2 text-brand-dark/80 text-base font-medium list-decimal list-inside">
            <li>Log in to your ApilageAI account.</li>
            <li>Open the Settings / Account area inside the app.</li>
            <li>Scroll to the <strong>Danger Zone</strong> and click <strong>Delete My Account</strong>.</li>
            <li>Confirm the deletion in the prompt.</li>
          </ol>
        </div>

        <div class="mt-10">
          <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">What gets deleted</h2>
          <ul class="mt-4 space-y-2 text-brand-dark/80 text-base font-medium list-disc list-inside">
            <li>Your account profile and login credentials.</li>
            <li>Chats, messages, uploads, and generated content.</li>
            <li>Usage logs, preferences, and onboarding data.</li>
            <li>Social sign-in links (Google/Facebook) and sessions.</li>
            <li>Billing records associated with your account.</li>
          </ul>
          <p class="text-brand-dark/70 text-base font-medium mt-4">
            Deletion is permanent and cannot be undone.
          </p>
        </div>

        <div class="mt-10">
          <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">Trouble logging in?</h2>
          <p class="text-brand-dark/70 text-base font-medium">
            If you no longer have access to your account, use the password reset option on the login page to regain access,
            then follow the steps above.
          </p>
        </div>
      </div>
    </section>
  </main>
</div>
