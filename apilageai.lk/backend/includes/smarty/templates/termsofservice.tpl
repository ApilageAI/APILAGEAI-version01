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
        <a href="{$smarty.const.APP_URL}/app" class="btn-primary !py-2 !px-5 !text-sm">Start Chat</a>
      </div>
    </div>
  </nav>

  <main class="pt-28 pb-20">
    <section class="container mx-auto px-6">
      <div class="max-w-4xl mx-auto">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-blueLight border-2 border-brand-dark text-brand-dark text-xs font-bold mb-6 uppercase tracking-wider">Legal</div>
        <h1 class="text-4xl md:text-6xl font-display font-black text-brand-dark mb-4">Terms of Service</h1>
        <p class="text-brand-dark/70 text-base md:text-lg font-medium">Last updated: February 7, 2026</p>

        <div class="mt-8 grid md:grid-cols-3 gap-4">
          <div class="p-4 border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm">
            <h3 class="font-bold text-brand-dark">Fair Use</h3>
            <p class="text-sm text-brand-dark/70 mt-2">Use the service responsibly and respect others.</p>
          </div>
          <div class="p-4 border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm">
            <h3 class="font-bold text-brand-dark">User Content</h3>
            <p class="text-sm text-brand-dark/70 mt-2">You control what you upload and share publicly.</p>
          </div>
          <div class="p-4 border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm">
            <h3 class="font-bold text-brand-dark">Payments</h3>
            <p class="text-sm text-brand-dark/70 mt-2">Charges are handled by our payment processor.</p>
          </div>
        </div>

        <div class="mt-12">
          <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">Agreement to These Terms</h2>
          <p class="text-brand-dark/70 text-base font-medium">
            By accessing or using ApilageAI, you agree to these Terms of Service and our Privacy Policy.
            If you do not agree, do not use the service.
          </p>
        </div>

        <div class="mt-10">
          <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">Eligibility and Accounts</h2>
          <ul class="mt-4 space-y-2 text-brand-dark/80 text-base font-medium">
            <li>You must be old enough to form a binding contract or have guardian consent.</li>
            <li>You are responsible for keeping your account credentials secure.</li>
            <li>You must provide accurate information and update it if it changes.</li>
          </ul>
        </div>

        <div class="mt-10">
          <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">Acceptable Use</h2>
          <ul class="mt-4 space-y-2 text-brand-dark/80 text-base font-medium">
            <li>Do not use the service for illegal, harmful, or abusive activities.</li>
            <li>Do not attempt to disrupt, reverse engineer, or exploit the platform.</li>
            <li>Respect intellectual property and the privacy of others.</li>
            <li>Do not submit content that violates laws or third-party rights.</li>
          </ul>
        </div>

        <div class="mt-10">
          <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">User Content and Sharing</h2>
          <p class="text-brand-dark/70 text-base font-medium">
            You own your content. By using ApilageAI, you grant us a limited license to host, process, and display
            your content solely to operate the service. Content remains private by default unless you choose to
            publish or share it.
          </p>
        </div>

        <div class="mt-10">
          <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">AI Output Disclaimer</h2>
          <p class="text-brand-dark/70 text-base font-medium">
            AI responses may be inaccurate, incomplete, or inappropriate. You are responsible for verifying outputs
            before relying on them. ApilageAI does not provide professional, legal, medical, or financial advice.
          </p>
        </div>

        <div class="mt-10">
          <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">Payments and Credits</h2>
          <ul class="mt-4 space-y-2 text-brand-dark/80 text-base font-medium">
            <li>Payments are processed via a third-party gateway (Payable) and are billed in LKR.</li>
            <li>You authorize charges for the amount shown at checkout.</li>
            <li>Transaction records such as invoice ID, amount, and payment status are stored for accounting.</li>
            <li>Credits or balances are added after the payment is confirmed by the processor.</li>
            <li>If a payment fails or is reversed, credits may be removed.</li>
          </ul>
        </div>

        <div class="mt-10">
          <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">Suspension and Termination</h2>
          <p class="text-brand-dark/70 text-base font-medium">
            We may suspend or terminate access if you violate these terms, abuse the service, or create risk for others.
            You may stop using the service at any time.
          </p>
        </div>

        <div class="mt-10">
          <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">Disclaimers and Liability</h2>
          <p class="text-brand-dark/70 text-base font-medium">
            The service is provided on an "as is" basis without warranties. To the extent permitted by law,
            ApilageAI is not liable for indirect, incidental, or consequential damages.
          </p>
        </div>

        <div class="mt-10">
          <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">Changes to These Terms</h2>
          <p class="text-brand-dark/70 text-base font-medium">
            We may update these Terms of Service from time to time. Continued use of the service means you accept
            the updated terms.
          </p>
        </div>

        <div class="mt-10">
          <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">Contact</h2>
          <p class="text-brand-dark/70 text-base font-medium">
            Apilageai PVT LTD, Kalutara South Sri Lanka. Contact: +94701840527.
          </p>
        </div>
      </div>
    </section>
  </main>

  {include file="components/footer.tpl"}
