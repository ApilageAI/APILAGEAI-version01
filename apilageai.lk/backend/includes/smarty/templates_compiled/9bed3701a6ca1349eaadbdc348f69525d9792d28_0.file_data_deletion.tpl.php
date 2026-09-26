<?php
/* Smarty version 5.8.0, created on 2026-04-21 13:42:56
  from 'file:data_deletion.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.8.0',
  'unifunc' => 'content_69e731886790a3_35280343',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '9bed3701a6ca1349eaadbdc348f69525d9792d28' => 
    array (
      0 => 'data_deletion.tpl',
      1 => 1773151752,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:components/head.tpl' => 1,
  ),
))) {
function content_69e731886790a3_35280343 (\Smarty\Template $_smarty_tpl) {
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
/" class="hover:text-brand-red hover:underline decoration-2 underline-offset-4 transition-all">Home</a>
        <a href="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/app" class="btn-primary !py-2 !px-5 !text-sm">Open App</a>
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
            <li>Social sign-in links (Google) and sessions.</li>
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
<?php }
}
