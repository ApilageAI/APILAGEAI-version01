<?php
/* Smarty version 5.8.0, created on 2026-04-21 23:40:16
  from 'file:parents.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.8.0',
  'unifunc' => 'content_69e7bd880999d1_43504452',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '3957eb8c9e84919a7791543fafd7c8b1c50a6326' => 
    array (
      0 => 'parents.tpl',
      1 => 1771392054,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:components/head.tpl' => 1,
    'file:components/footer.tpl' => 1,
  ),
))) {
function content_69e7bd880999d1_43504452 (\Smarty\Template $_smarty_tpl) {
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
/app" class="btn-primary !py-2 !px-5 !text-sm">Start Chat</a>
      </div>
    </div>
  </nav>

  <main class="pt-28 pb-20">
    <section class="container mx-auto px-6">
      <div class="max-w-5xl mx-auto">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-blueLight border-2 border-brand-dark text-brand-dark text-xs font-bold mb-6 uppercase tracking-wider">Parents &amp; Safety</div>
        <h1 class="text-4xl md:text-6xl font-display font-black text-brand-dark mb-4">Parents &amp; Child Safety on ApilageAI</h1>
        <p class="text-brand-dark/70 text-base md:text-lg font-medium">Last updated: February 18, 2026</p>
        <p class="text-brand-dark/70 text-base md:text-lg font-medium mt-4">
          Child safety runs across the app and the experience is suitable for all ages. We actively prevent hate speech,
          nudity, and other harmful content, and we provide child safety guidance with more privacy for children and teens
          when they have sensitive questions or problems.
        </p>

        <div class="mt-8 grid md:grid-cols-3 gap-4">
          <div class="p-5 border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm">
            <h3 class="font-bold text-brand-dark">Suitable for All Ages</h3>
            <p class="text-sm text-brand-dark/70 mt-2">Designed for learners of every age, from primary students to adults.</p>
          </div>
          <div class="p-5 border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm">
            <h3 class="font-bold text-brand-dark">Safety Filters</h3>
            <p class="text-sm text-brand-dark/70 mt-2">We block hate speech, nudity, and harmful or abusive content.</p>
          </div>
          <div class="p-5 border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm">
            <h3 class="font-bold text-brand-dark">Privacy by Default</h3>
            <p class="text-sm text-brand-dark/70 mt-2">Chats and uploads stay private unless the user chooses to share.</p>
          </div>
        </div>

        <div class="mt-12">
          <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">Safety Across the App</h2>
          <ul class="mt-4 space-y-2 text-brand-dark/80 text-base font-medium">
            <li>Safety rules apply to conversations, images, public profiles, and shared links.</li>
            <li>We actively prevent hate speech, nudity, sexual content, graphic violence, and harmful behavior.</li>
            <li>Accounts that violate safety rules can be restricted or removed.</li>
          </ul>
        </div>

        <div class="mt-10">
          <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">Guidance for Children and Teens</h2>
          <p class="text-brand-dark/70 text-base font-medium">
            We encourage young users to avoid sharing personal information and to use ApilageAI for learning, creativity,
            and safe support. Our guidance is designed to help children and teens navigate sensitive topics privately and
            respectfully.
          </p>
        </div>

        <div class="mt-10">
          <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">Privacy for Sensitive Topics</h2>
          <ul class="mt-4 space-y-2 text-brand-dark/80 text-base font-medium">
            <li>Private chats are not displayed publicly unless the user chooses to share.</li>
            <li>Public profiles only show what the user decides to make visible.</li>
            <li>We focus on keeping children and teens safe while respecting their privacy.</li>
          </ul>
        </div>

        <div class="mt-10">
          <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">How Parents Can Help</h2>
          <ul class="mt-4 space-y-2 text-brand-dark/80 text-base font-medium">
            <li>Talk about safe and respectful online behavior.</li>
            <li>Review account settings and remind children not to share personal details.</li>
            <li>Encourage young users to come to a trusted adult when something feels wrong.</li>
          </ul>
        </div>
      </div>
    </section>
  </main>

  <?php $_smarty_tpl->renderSubTemplate("file:components/footer.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), (int) 0, $_smarty_current_dir);
}
}
