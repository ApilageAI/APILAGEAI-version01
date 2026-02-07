<?php
/* Smarty version 5.7.0, created on 2026-02-04 22:22:14
  from 'file:dashboard.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.7.0',
  'unifunc' => 'content_6983793eb66e19_40011875',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '7666cb67f861cc586a59a95311b1712734d9b060' => 
    array (
      0 => 'dashboard.tpl',
      1 => 1770222752,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:components/head.tpl' => 1,
  ),
))) {
function content_6983793eb66e19_40011875 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = '/Users/dinethgunawardana/Downloads/apilageai/backend/includes/smarty/templates';
$_smarty_tpl->renderSubTemplate("file:components/head.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), (int) 0, $_smarty_current_dir);
?>
<body>
  <div class="main-container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <div class="logo">
          <h1 class="logo-title">Apilage AI</h1>
          <span class="logo-badge">Gallery</span>
        </div>
        <button id="sidebar-toggle" class="sidebar-toggle">
          <i class="fas fa-chevron-left"></i>
        </button>
      </div>
      <nav class="sidebar-nav">
         <a href="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/app" class="nav-link">
         <i class="fas fa-comments"></i> <span>AI Chat</span>
        </a>
        <a href="#" class="nav-link active" data-tab="explore">
         <i class="fa fa-user"></i> <span>My Images</span>
        </a>
        <a href="#" class="nav-link" data-tab="friends">
          <i class="fas fa-user-friends"></i> <span>Explore</span>
        </a>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <header class="header">
        <div class="container">
          <div class="header-content">
            <div class="search-container">
              <i class="fas fa-search search-icon"></i>
              <input
                type="text"
                id="search-input"
                placeholder="Search your images..."
                class="search-input"
              />
            </div>
            <div class="header-actions">
              <button id="theme-toggle" class="theme-toggle">
                <i id="theme-icon" class="fas fa-sun"></i>
              </button>
              <img
                src="<?php if (!( !true || empty($_smarty_tpl->getValue('user')->_data['image']))) {
echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/uploads/<?php echo $_smarty_tpl->getValue('user')->_data['image'];
} else {
echo (defined('APP_URL') ? constant('APP_URL') : null);?>/assets/images/user.png<?php }?>"
                alt="<?php echo $_smarty_tpl->getValue('user')->_data['first_name'];?>
 Avatar"
                class="profile-pic"
                onerror="this.onerror=null;this.src='<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>/assets/images/user.png';"
              />
            </div>
          </div>
        </div>
      </header>

      <div id="image-gallery" class="container">
        <div class="image-grid"></div>
      </div>
    </main>

    <!-- Bottom Navigation -->
    <nav class="bottom-nav">
      <a href="#" class="bottom-nav-link active" data-tab="explore">
       <i class="fa fa-user"></i> <span>My Images</span>
      </a>
      <a href="#" class="bottom-nav-link" data-tab="friends">
       <i class="fas fa-compass"></i> <span>Explore</span>
      </a>
      <a href="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/app/" class="bottom-nav-link">
        <i class="fas fa-comments"></i> <span>AI Chat</span>
      </a>
    </nav>
  </div>

     <?php echo '<script'; ?>
 src="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>/assets/scripts/dashboard.min.js?V=04.22.10.2025"><?php echo '</script'; ?>
>
</body>
</html><?php }
}
