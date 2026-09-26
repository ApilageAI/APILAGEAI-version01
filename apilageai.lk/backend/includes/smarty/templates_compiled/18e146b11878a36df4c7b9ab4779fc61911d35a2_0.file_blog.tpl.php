<?php
/* Smarty version 5.8.0, created on 2026-04-22 09:39:01
  from 'file:blog.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.8.0',
  'unifunc' => 'content_69e849dde430d2_70902793',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '18e146b11878a36df4c7b9ab4779fc61911d35a2' => 
    array (
      0 => 'blog.tpl',
      1 => 1771008006,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:components/head.tpl' => 1,
    'file:components/footer.tpl' => 1,
  ),
))) {
function content_69e849dde430d2_70902793 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = '/home/apilageai/domains/apilageai.lk/backend/includes/smarty/templates';
$_smarty_tpl->renderSubTemplate("file:components/head.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), (int) 0, $_smarty_current_dir);
?>

<style>
  .blog-hero {
    background: radial-gradient(circle at top, rgba(56, 189, 248, 0.2), rgba(255, 59, 48, 0.12), transparent 65%);
  }
  .blog-card {
    transition: transform 0.15s ease, box-shadow 0.15s ease;
  }
  .blog-card:hover {
    transform: translateY(-3px);
    box-shadow: 6px 6px 0 0 #172554;
  }
  .blog-search {
    box-shadow: 4px 4px 0 0 #172554;
  }
  .help-card {
    border: 2px solid #172554;
    border-radius: 18px;
    background: #ffffff;
    box-shadow: 4px 4px 0 0 #172554;
  }
  .help-callout {
    background: linear-gradient(135deg, rgba(56, 189, 248, 0.22), rgba(255, 59, 48, 0.12));
  }
  .tag-pill {
    border: 2px solid #172554;
  }
</style>

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
/app" class="btn-primary !py-2 !px-5 !text-sm">Start Chat</a>
      </div>
    </div>
  </nav>

  <main class="pt-28 pb-20">
    <section class="blog-hero">
      <div class="container mx-auto px-6 py-12">
        <div class="max-w-5xl">
          <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-blueLight border-2 border-brand-dark text-brand-dark text-xs font-bold mb-6 uppercase tracking-wider">
            Blog
          </div>
          <h1 class="text-4xl md:text-6xl font-display font-black text-brand-dark mb-4">
            Sri Lanka's #1 Student AI Blog by ApilageAI
          </h1>
          <p class="text-brand-dark/70 text-base md:text-lg font-medium max-w-3xl">
            Search-optimized guides, AI study tips, and syllabus-focused insights for Sri Lankan students. Discover why
            ApilageAI is the most popular AI among students for learning, exams, and daily productivity.
          </p>
          <div class="mt-8 flex flex-wrap gap-3">
            <span class="tag-pill px-4 py-2 rounded-full text-xs font-bold bg-white">Sri Lanka AI</span>
            <span class="tag-pill px-4 py-2 rounded-full text-xs font-bold bg-white">Student Study Tips</span>
            <span class="tag-pill px-4 py-2 rounded-full text-xs font-bold bg-white">O/L & A/L Prep</span>
            <span class="tag-pill px-4 py-2 rounded-full text-xs font-bold bg-white">Syllabus Learning</span>
          </div>
        </div>
      </div>
    </section>

    <section class="container mx-auto px-6 mt-8">
      <div class="max-w-5xl mx-auto">
        <div class="help-card p-5">
          <div class="text-xs uppercase tracking-wider font-bold text-brand-dark/70 mb-3">Search posts</div>
          <label class="sr-only" for="blogSearch">Search blog posts</label>
          <div class="flex items-center gap-2 border-2 border-brand-dark rounded-full px-4 py-2 bg-white blog-search">
            <i class="fa-solid fa-magnifying-glass text-brand-dark/60"></i>
            <input id="blogSearch" type="search" class="w-full bg-transparent outline-none text-sm font-medium text-brand-dark" placeholder="Search AI study topics" />
          </div>
          <div id="blogSearchStatus" class="text-xs text-brand-dark/60 mt-2">Showing all posts.</div>
        </div>
      </div>
    </section>

    <section class="container mx-auto px-6 mt-10">
      <div class="max-w-5xl mx-auto">
        <div class="grid md:grid-cols-2 gap-6">
          <?php
$_from = $_smarty_tpl->getSmarty()->getRuntime('Foreach')->init($_smarty_tpl, $_smarty_tpl->getValue('blog_posts'), 'post');
$foreach0DoElse = true;
foreach ($_from ?? [] as $_smarty_tpl->getVariable('post')->value) {
$foreach0DoElse = false;
?>
            <article id="<?php echo $_smarty_tpl->getValue('post')['slug'];?>
" class="blog-card border-2 border-brand-dark rounded-2xl bg-white shadow-hard-sm p-6" data-blog-card data-blog-title="<?php echo mb_strtolower((string) $_smarty_tpl->getValue('post')['title'], 'UTF-8');?>
" data-blog-tags="<?php
$_from = $_smarty_tpl->getSmarty()->getRuntime('Foreach')->init($_smarty_tpl, $_smarty_tpl->getValue('post')['tags'], 'tag');
$foreach1DoElse = true;
foreach ($_from ?? [] as $_smarty_tpl->getVariable('tag')->value) {
$foreach1DoElse = false;
echo mb_strtolower((string) $_smarty_tpl->getValue('tag'), 'UTF-8');?>
 <?php
}
$_smarty_tpl->getSmarty()->getRuntime('Foreach')->restore($_smarty_tpl, 1);?>">
              <div class="text-xs uppercase tracking-wider font-bold text-brand-dark/60">SEO Highlight</div>
              <h2 class="text-2xl font-display font-black text-brand-dark mt-2 mb-3"><?php echo $_smarty_tpl->getValue('post')['title'];?>
</h2>
              <p class="text-sm text-brand-dark/70 font-medium mb-4"><?php echo $_smarty_tpl->getValue('post')['summary'];?>
</p>
              <div class="flex flex-wrap gap-2">
                <?php
$_from = $_smarty_tpl->getSmarty()->getRuntime('Foreach')->init($_smarty_tpl, $_smarty_tpl->getValue('post')['tags'], 'tag');
$foreach2DoElse = true;
foreach ($_from ?? [] as $_smarty_tpl->getVariable('tag')->value) {
$foreach2DoElse = false;
?>
                  <span class="px-3 py-1 rounded-full bg-brand-blueLight text-xs font-bold text-brand-dark"><?php echo $_smarty_tpl->getValue('tag');?>
</span>
                <?php
}
$_smarty_tpl->getSmarty()->getRuntime('Foreach')->restore($_smarty_tpl, 1);?>
              </div>
            </article>
          <?php
}
$_smarty_tpl->getSmarty()->getRuntime('Foreach')->restore($_smarty_tpl, 1);?>
        </div>
        <div id="blogEmptyState" class="help-card p-6 text-center text-brand-dark/60 mt-6 hidden">
          No posts match your search.
        </div>
      </div>
    </section>

    <section class="container mx-auto px-6 mt-12">
      <div class="max-w-5xl mx-auto">
        <div class="help-card help-callout p-6">
          <h2 class="text-2xl font-display font-black text-brand-dark mb-3">Improve your search results with ApilageAI</h2>
          <p class="text-brand-dark/80 font-medium mb-4">
            These posts are designed to improve search visibility for students looking for Sri Lankan AI learning tools.
            Explore more tips in the Help Center or start a chat to get instant guidance.
          </p>
          <div class="flex flex-wrap gap-4">
            <a href="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/help" class="btn-primary !px-6 !py-3">Visit Help Center</a>
            <a href="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/app" class="btn-primary bg-brand-dark text-white hover:bg-brand-dark/90 !px-6 !py-3">Start Chat</a>
          </div>
        </div>
      </div>
    </section>

    <section class="container mx-auto px-6">
      <?php echo '<script'; ?>
 type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "Blog",
          "name": "ApilageAI Blog",
          "url": "<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/blog",
          "description": "Sri Lanka's #1 student AI blog with syllabus learning, AI study tips, and exam preparation guides.",
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

<?php echo '<script'; ?>
>
  const blogInput = document.getElementById('blogSearch');
  const blogCards = Array.from(document.querySelectorAll('[data-blog-card]'));
  const blogStatus = document.getElementById('blogSearchStatus');
  const blogEmpty = document.getElementById('blogEmptyState');

  const updateBlogStatus = (count, query) => {
    if (!blogStatus) return;
    if (query) {
      blogStatus.textContent = 'Found ' + count + ' post' + (count === 1 ? '' : 's') + ' for "' + query + '".';
    } else {
      blogStatus.textContent = 'Showing all posts.';
    }
  };

  const filterBlog = (query) => {
    const q = (query || '').trim().toLowerCase();
    let visible = 0;
    blogCards.forEach((card) => {
      const title = card.getAttribute('data-blog-title') || '';
      const tags = card.getAttribute('data-blog-tags') || '';
      const match = !q || title.includes(q) || tags.includes(q);
      card.classList.toggle('hidden', !match);
      if (match) visible += 1;
    });
    if (blogEmpty) {
      blogEmpty.classList.toggle('hidden', visible !== 0);
    }
    updateBlogStatus(visible, q);
  };

  if (blogInput) {
    blogInput.addEventListener('input', (event) => filterBlog(event.target.value));
  }
  updateBlogStatus(blogCards.length, '');
<?php echo '</script'; ?>
>

  <?php $_smarty_tpl->renderSubTemplate("file:components/footer.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), (int) 0, $_smarty_current_dir);
}
}
