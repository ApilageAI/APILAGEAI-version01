{include file="components/head.tpl"}
<body>
  <div class="main-container images-shell">
    <aside class="rail">
      <div class="rail-brand" aria-label="Apilage AI">
        <img src="{$smarty.const.APP_URL}/assets/images/icon.png" alt="Apilage AI logo" class="brand-logo" />
      </div>

      <nav class="rail-nav" aria-label="Primary">
        <a href="{$smarty.const.APP_URL}/app" class="rail-link" title="AI Chat" aria-label="AI Chat">
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
          src="{if !empty($user->_data.image)}{$smarty.const.APP_URL}/uploads/{$user->_data.image}{else}{$smarty.const.APP_URL}/assets/images/user.png{/if}"
          alt="{$user->_data.first_name} Avatar"
          class="profile-pic"
          onerror="this.onerror=null;this.src='{$smarty.const.APP_URL}/assets/images/user.png';"
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

  <script src="{$smarty.const.APP_URL}/assets/scripts/dashboard.min.js?V=04.22.10.2025"></script>
</body>
</html>
