<?php
/* Smarty version 5.7.0, created on 2026-02-06 20:20:27
  from 'file:dashboard.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.7.0',
  'unifunc' => 'content_6985ffb316ffc7_96426092',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '209861c8a3e2f480d47e73059933b933b922b3b9' => 
    array (
      0 => 'dashboard.tpl',
      1 => 1770389402,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:components/head.tpl' => 1,
  ),
))) {
function content_6985ffb316ffc7_96426092 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = '/Users/dinethgunawardana/Documents/GitHub/apilageai-personal/backend/includes/smarty/templates';
$_smarty_tpl->renderSubTemplate("file:components/head.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), (int) 0, $_smarty_current_dir);
?>
<body>
  <div class="main-container images-shell">
    <aside class="rail">
      <div class="rail-brand" aria-label="Apilage AI">
        <img src="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/assets/images/icon.png" alt="Apilage AI logo" class="brand-logo" />
      </div>

      <nav class="rail-nav" aria-label="Primary">
        <a href="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/app" class="rail-link" title="AI Chat" aria-label="AI Chat">
          <i class="fa-regular fa-comments"></i>
        </a>
        <a href="#" class="rail-link active" data-tab="explore" title="My Images" aria-label="My Images">
          <i class="fa-regular fa-image"></i>
        </a>
        <a href="#" class="rail-link" data-tab="friends" title="Explore" aria-label="Explore">
          <i class="fa-regular fa-compass"></i>
        </a>
      </nav>

      <div class="rail-footer">
        <img
          src="<?php if (!( !true || empty($_smarty_tpl->getValue('user')->_data['image']))) {
echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/uploads/<?php echo $_smarty_tpl->getValue('user')->_data['image'];
} else {
echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/assets/images/user.png<?php }?>"
          alt="<?php echo $_smarty_tpl->getValue('user')->_data['first_name'];?>
 Avatar"
          class="profile-pic"
          onerror="this.onerror=null;this.src='<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/assets/images/user.png';"
        />
      </div>
    </aside>

    <main class="main-content">
      <section class="hero">
        <div class="container hero-inner">
          <div class="hero-top">
            <h1 class="hero-title">Images</h1>
            <div class="hero-meta">Create, remix, and explore visual ideas</div>
          </div>
          <div class="prompt-bar">
            <span class="prompt-icon" aria-hidden="true">
              <i class="fa-regular fa-image"></i>
            </span>
            <input
              type="text"
              id="search-input"
              placeholder="Describe a new image"
              class="prompt-input"
            />
            <button class="prompt-btn send" type="button" aria-label="Search">
              <i class="fa-solid fa-magnifying-glass"></i>
            </button>
          </div>
        </div>
      </section>

      <section class="section styles">
        <div class="container section-head">
          <h2>Try a style on an image</h2>
        </div>
        <div id="image-gallery" class="container">
          <div class="image-grid style-row"></div>
        </div>
      </section>

    </main>
  </div>

  <?php echo '<script'; ?>
 src="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/assets/scripts/dashboard.min.js?V=04.22.10.2025"><?php echo '</script'; ?>
>
</body>
</html>
<?php }
}
