<?php
/* Smarty version 5.8.0, created on 2026-04-19 02:27:44
  from 'file:how_apilageai_credit_works.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.8.0',
  'unifunc' => 'content_69e3f048c57a52_84552166',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'dfe9f731a020ac33a4cd857c948ab635fa692d57' => 
    array (
      0 => 'how_apilageai_credit_works.tpl',
      1 => 1771908025,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:components/head.tpl' => 1,
  ),
))) {
function content_69e3f048c57a52_84552166 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = '/home/apilageai/domains/apilageai.lk/backend/includes/smarty/templates';
$_smarty_tpl->renderSubTemplate("file:components/head.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), (int) 0, $_smarty_current_dir);
?>

<div class="min-h-screen bg-white bg-grid-pattern text-brand-dark font-sans selection:bg-brand-red selection:text-white">
  <nav id="navbar" class="navbar-normal fixed top-0 left-0 right-0 z-50 transition-all duration-300">
    <div class="container mx-auto px-6 flex items-center justify-between">
      <a href="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/" class="flex items-center gap-3">
        <img src="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/assets/images/icon.png" alt="ApilageAI Logo" class="w-10 h-10 object-contain" />
        <span class="text-xl font-bold font-display text-brand-dark tracking-tight">
          Apilage<span class="text-brand-red underline decoration-wavy decoration-2 underline-offset-4">AI</span>
        </span>
      </a>
      <div class="flex items-center gap-4 text-sm font-bold text-brand-dark/80">
        <a href="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/help" class="hover:text-brand-red hover:underline decoration-2 underline-offset-4 transition-all">Help</a>
        <a href="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/app" class="btn-primary !py-2 !px-5 !text-sm">Open App</a>
      </div>
    </div>
  </nav>

  <main class="pt-28 pb-20">
    <section class="container mx-auto px-6">
      <div class="max-w-4xl mx-auto">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-blueLight border-2 border-brand-dark text-brand-dark text-xs font-bold mb-6 uppercase tracking-wider">Credits</div>
        <h1 class="text-4xl md:text-6xl font-display font-black text-brand-dark mb-4">How ApilageAI credit works</h1>
        <p class="text-brand-dark/70 text-base md:text-lg font-medium">ApilageAI uses a pay-as-you-go credit balance in LKR. Top up when you need, and use credits across your AI tools. Last updated: February 23, 2026.</p>

        <div class="mt-10 space-y-8">
          <div>
            <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">1. Credits are a prepaid balance</h2>
            <p class="text-brand-dark/70 text-base font-medium">When you upgrade, the amount you pay is added to your ApilageAI balance. There is no monthly subscription—use the balance until it is spent, then top up again when needed.</p>
          </div>

          <div>
            <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">2. Credits are used across features</h2>
            <ul class="mt-4 space-y-2 text-brand-dark/80 text-base font-medium list-disc list-inside">
              <li>AI chats and answers are deducted from the same balance.</li>
              <li>Image generation and other advanced features use credits too.</li>
              <li>Developer API requests require a positive balance and deduct from credits.</li>
            </ul>
            <p class="text-brand-dark/70 text-base font-medium mt-4">Charges depend on usage. System text tokens may be included in usage deductions.</p>
          </div>

          <div>
            <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">3. Track your balance anytime</h2>
            <p class="text-brand-dark/70 text-base font-medium">You can see your live credit balance in the app sidebar and in the Billing tab. If your balance reaches zero, some premium actions may pause until you top up.</p>
            <a href="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/app?open=preferences" class="btn-primary mt-4 inline-flex">Open Billing</a>
          </div>

          <div>
            <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">4. Secure payments</h2>
            <p class="text-brand-dark/70 text-base font-medium">Payments are processed through a Sri Lankan bank approved secure gateway. Credits are added after the payment is confirmed.</p>
          </div>
        </div>

        <div class="mt-10">
          <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">Need help?</h2>
          <p class="text-brand-dark/70 text-base font-medium">If you have questions about charges or credits, reach out to the ApilageAI Help Center.</p>
          <a href="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/help" class="btn-primary mt-4 inline-flex">Visit Help Center</a>
        </div>
      </div>
    </section>
  </main>
</div>

</body>
</html>
<?php }
}
