<?php
/* Smarty version 5.8.0, created on 2026-04-22 00:26:31
  from 'file:about.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.8.0',
  'unifunc' => 'content_69e7c85fd18539_32834090',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'ecd6848d96b8db9c4c80be5f79000e4ecd7f9eeb' => 
    array (
      0 => 'about.tpl',
      1 => 1770791136,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:components/head.tpl' => 1,
    'file:components/footer.tpl' => 1,
  ),
))) {
function content_69e7c85fd18539_32834090 (\Smarty\Template $_smarty_tpl) {
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
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-blueLight border-2 border-brand-dark text-brand-dark text-xs font-bold mb-6 uppercase tracking-wider">About</div>
        <h1 class="text-4xl md:text-6xl font-display font-black text-brand-dark mb-4">
          About ApilageAI: Sri Lanka's Native-Language AI for Grades 1-13
        </h1>
        <p class="text-brand-dark/70 text-base md:text-lg font-medium">
          ApilageAI is built with one clear mission: help every Sri Lankan student learn in their native language, from
          Grade 1 to Grade 13, with syllabus-aligned answers and trustworthy educational guidance.
        </p>

        <div class="mt-8 grid md:grid-cols-3 gap-4">
          <div class="p-5 border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm">
            <h3 class="font-bold text-brand-dark">Syllabus First</h3>
            <p class="text-sm text-brand-dark/70 mt-2">
              Answers are guided by the Sri Lankan syllabus and newly published education models.
            </p>
          </div>
          <div class="p-5 border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm">
            <h3 class="font-bold text-brand-dark">Native Language Learning</h3>
            <p class="text-sm text-brand-dark/70 mt-2">
              Support in Sinhala, Tamil, and English so students can learn in the language they trust.
            </p>
          </div>
          <div class="p-5 border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm">
            <h3 class="font-bold text-brand-dark">Trusted Sources</h3>
            <p class="text-sm text-brand-dark/70 mt-2">
              We prioritize information from Sri Lankan government and government-recommended websites.
            </p>
          </div>
        </div>

        <div class="mt-12">
          <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">Our Mission</h2>
          <p class="text-brand-dark/70 text-base font-medium">
            ApilageAI exists to make quality education accessible to every Sri Lankan student, regardless of location,
            language, or background. We focus on curriculum-aligned explanations, exam readiness, and daily learning
            support for Grades 1-13.
          </p>
        </div>

        <div class="mt-10">
          <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">Aligned With the Sri Lankan Syllabus</h2>
          <p class="text-brand-dark/70 text-base font-medium">
            ApilageAI is specially trained to answer according to the official Sri Lankan syllabus and updates from
            newly published education models. We gather knowledge from trusted government sources and
            government-recommended websites to keep learning accurate and relevant.
          </p>
          <ul class="mt-4 space-y-2 text-brand-dark/80 text-base font-medium">
            <li>Grade 1-5 primary learning support.</li>
            <li>Grade 6-9 junior secondary guidance.</li>
            <li>O/L and A/L exam preparation with step-by-step explanations.</li>
            <li>Subject support across Maths, Science, English, ICT, and more.</li>
          </ul>
        </div>

        <div class="mt-10 grid md:grid-cols-2 gap-6">
          <div class="p-6 border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm">
            <h3 class="font-bold text-brand-dark text-lg mb-3">Made for Students and Teenagers</h3>
            <p class="text-brand-dark/70 text-base font-medium">
              From homework help to last-minute exam revision, ApilageAI gives students the confidence to learn and
              practice every day with clear, friendly explanations.
            </p>
          </div>
          <div class="p-6 border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm">
            <h3 class="font-bold text-brand-dark text-lg mb-3">Useful for Everyday Tasks</h3>
            <p class="text-brand-dark/70 text-base font-medium">
              Beyond schoolwork, ApilageAI helps with writing, summaries, planning, translations, and daily tasks that
              make student life easier.
            </p>
          </div>
        </div>

        <div class="mt-10">
          <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">Well-being and Support</h2>
          <p class="text-brand-dark/70 text-base font-medium">
            We also provide supportive, counseling-style guidance for study stress, motivation, and healthy routines.
            ApilageAI is not a medical or mental health service, and it cannot replace professional counseling. If you
            are in distress, please reach out to a trusted adult or a qualified professional.
          </p>
        </div>

        <div class="mt-10">
          <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">Why Sri Lankan Students Choose ApilageAI</h2>
          <ul class="mt-4 space-y-2 text-brand-dark/80 text-base font-medium">
            <li>Sri Lanka focused AI built for local curriculum and culture.</li>
            <li>Native-language learning in Sinhala, Tamil, and English.</li>
            <li>Trusted education data from government and recommended sources.</li>
            <li>Friendly explanations that simplify complex topics.</li>
            <li>Support for schoolwork, daily tasks, and student life.</li>
          </ul>
        </div>

        <div class="mt-10">
          <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">Frequently Asked Questions</h2>
          <div class="mt-4 space-y-4">
            <div class="p-5 border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm">
              <h3 class="font-bold text-brand-dark">Is ApilageAI suitable for Grades 1-13?</h3>
              <p class="text-brand-dark/70 text-base font-medium mt-2">
                Yes. ApilageAI is designed to support the full Sri Lankan school journey from Grade 1 through Grade 13.
              </p>
            </div>
            <div class="p-5 border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm">
              <h3 class="font-bold text-brand-dark">Does ApilageAI follow the Sri Lankan syllabus?</h3>
              <p class="text-brand-dark/70 text-base font-medium mt-2">
                Our answers are aligned with the Sri Lankan syllabus and updated educational models, supported by
                trusted government and recommended sources.
              </p>
            </div>
            <div class="p-5 border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm">
              <h3 class="font-bold text-brand-dark">Which languages does ApilageAI support?</h3>
              <p class="text-brand-dark/70 text-base font-medium mt-2">
                ApilageAI supports Sinhala, Tamil, and English to help students learn in their preferred language.
              </p>
            </div>
            <div class="p-5 border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm">
              <h3 class="font-bold text-brand-dark">Can ApilageAI help with everyday tasks?</h3>
              <p class="text-brand-dark/70 text-base font-medium mt-2">
                Yes. We help with summaries, homework planning, translations, note taking, and other daily tasks.
              </p>
            </div>
          </div>
        </div>

        <div class="mt-12">
          <div class="p-8 border-2 border-brand-dark rounded-2xl bg-brand-blueLight shadow-hard">
            <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-3">Start Learning With ApilageAI</h2>
            <p class="text-brand-dark/80 text-base font-medium mb-6">
              Join thousands of Sri Lankan students using ApilageAI for syllabus-aligned learning, everyday tasks, and
              trusted guidance in their native language.
            </p>
            <div class="flex flex-wrap gap-4">
              <a href="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/app" class="btn-primary !px-6 !py-3">Start Chat</a>
              <a href="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/auth/register" class="btn-primary bg-brand-dark text-white hover:bg-brand-dark/90 !px-6 !py-3">Create Free Account</a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="container mx-auto px-6">
      <?php echo '<script'; ?>
 type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "AboutPage",
          "name": "About ApilageAI",
          "url": "<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/about",
          "description": "ApilageAI is Sri Lanka's native-language AI for Grades 1-13, aligned with the Sri Lankan syllabus and trusted government sources.",
          "publisher": {
            "@type": "Organization",
            "name": "ApilageAI",
            "url": "<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
",
            "logo": {
              "@type": "ImageObject",
              "url": "<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/assets/images/logo.png"
            }
          }
        }
      <?php echo '</script'; ?>
>
    </section>
  </main>

  <?php $_smarty_tpl->renderSubTemplate("file:components/footer.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), (int) 0, $_smarty_current_dir);
}
}
