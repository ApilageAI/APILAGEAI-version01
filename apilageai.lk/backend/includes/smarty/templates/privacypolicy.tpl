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
        <h1 class="text-4xl md:text-6xl font-display font-black text-brand-dark mb-4">Privacy Policy</h1>
        <p class="text-brand-dark/70 text-base md:text-lg font-medium">Last updated: February 7, 2026</p>

        <div class="mt-8 grid md:grid-cols-3 gap-4">
          <div class="p-4 border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm">
            <h3 class="font-bold text-brand-dark">Collected</h3>
            <p class="text-sm text-brand-dark/70 mt-2">Account details, content you submit, usage data, and payment status.</p>
          </div>
          <div class="p-4 border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm">
            <h3 class="font-bold text-brand-dark">Shared</h3>
            <p class="text-sm text-brand-dark/70 mt-2">Only with trusted providers and when you choose to publish or share.</p>
          </div>
          <div class="p-4 border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm">
            <h3 class="font-bold text-brand-dark">Protected</h3>
            <p class="text-sm text-brand-dark/70 mt-2">We use reasonable safeguards to keep your data secure.</p>
          </div>
        </div>

        <div class="mt-12">
          <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">Scope</h2>
          <p class="text-brand-dark/70 text-base font-medium">
            This Privacy Policy explains how ApilageAI collects, uses, and shares information when you visit or use
            apilageai.lk, the ApilageAI web app, and related services.
          </p>
        </div>

        <div class="mt-10">
          <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">Information We Collect</h2>
          <ul class="mt-4 space-y-2 text-brand-dark/80 text-base font-medium">
            <li>Account information: name, email, phone number, password hash, and verification status.</li>
            <li>Profile and settings: profile photo, language, preferences, and onboarding choices.</li>
            <li>Content you submit: prompts, messages, uploaded files, images, mindmaps, and feedback.</li>
            <li>Usage and device data: IP address, browser and device data, session tokens, logs, and timestamps.</li>
            <li>Payment records: invoice ID, amount, payment status, and payment gateway reference.</li>
          </ul>
        </div>

        <div class="mt-10">
          <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">How We Use Information</h2>
          <ul class="mt-4 space-y-2 text-brand-dark/80 text-base font-medium">
            <li>Provide, maintain, and improve the ApilageAI service and features.</li>
            <li>Personalize your experience and remember your settings.</li>
            <li>Process payments, manage credits, and prevent fraud or abuse.</li>
            <li>Communicate with you about updates, security, or support requests.</li>
            <li>Monitor performance, troubleshoot issues, and secure the platform.</li>
          </ul>
        </div>

        <div class="mt-10">
          <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">Sharing and Disclosure</h2>
          <ul class="mt-4 space-y-2 text-brand-dark/80 text-base font-medium">
            <li>Trusted service providers who help us operate the platform (hosting, email, analytics, storage).</li>
            <li>Payment processors (including Payable) to complete transactions and confirm payment status.</li>
            <li>When you choose to publish or share content through public or shared links.</li>
            <li>To comply with legal obligations or to protect rights, safety, and security.</li>
            <li>As part of a business transfer such as a merger or acquisition.</li>
          </ul>
          <p class="text-brand-dark/70 text-base font-medium mt-4">We do not sell your personal information.</p>
        </div>

        <div class="mt-10 grid md:grid-cols-2 gap-6">
          <div class="p-5 border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm">
            <h3 class="font-bold text-brand-dark">Shared Publicly Only When You Choose</h3>
            <ul class="mt-3 space-y-2 text-brand-dark/80 text-base font-medium">
              <li>Images you mark as public.</li>
              <li>Conversations you publish or share with a link.</li>
              <li>Public profiles or display names where you enable visibility.</li>
            </ul>
          </div>
          <div class="p-5 border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm">
            <h3 class="font-bold text-brand-dark">Not Shared Publicly by Default</h3>
            <ul class="mt-3 space-y-2 text-brand-dark/80 text-base font-medium">
              <li>Private chats, files, and uploads.</li>
              <li>Payment and transaction records.</li>
              <li>Email address, phone number, and security data.</li>
            </ul>
          </div>
        </div>

        <div class="mt-10">
          <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">Payments</h2>
          <p class="text-brand-dark/70 text-base font-medium">
            Payments are processed through a third-party gateway (Payable). We send the information required to complete
            the transaction, such as your name, email, phone, invoice ID, and amount. We do not store your full card
            details; card data is handled by the payment processor.
          </p>
        </div>

        <div class="mt-10">
          <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">Data Retention</h2>
          <p class="text-brand-dark/70 text-base font-medium">
            We keep your information for as long as needed to provide the service, comply with legal requirements, and
            resolve disputes. You can request account deletion, subject to required retention for legal or security needs.
          </p>
        </div>

        <div class="mt-10">
          <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">Security</h2>
          <p class="text-brand-dark/70 text-base font-medium">
            We use reasonable technical and organizational safeguards to protect your information. No method of
            transmission or storage is 100 percent secure, so we cannot guarantee absolute security.
          </p>
        </div>

        <div class="mt-10">
          <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">Your Choices</h2>
          <ul class="mt-4 space-y-2 text-brand-dark/80 text-base font-medium">
            <li>Update your profile details and settings in your account.</li>
            <li>Control whether content is shared or kept private.</li>
            <li>Request access to or deletion of your personal data.</li>
            <li>Opt out of optional communications where available.</li>
          </ul>
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
