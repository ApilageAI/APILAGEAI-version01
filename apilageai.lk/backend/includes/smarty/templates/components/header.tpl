 <!-- Navbar -->
    <nav id="navbar" class="navbar-normal fixed top-0 left-0 right-0 z-50 transition-all duration-300">
      <div class="container mx-auto px-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <img
            src="{$smarty.const.APP_URL}/assets/images/icon.png"
            alt="ApilageAI Logo"
            class="w-10 h-10 object-contain hover:rotate-6 transition-transform duration-300"
          />
          <span class="text-xl font-bold font-display text-brand-dark tracking-tight">
            Apilage<span class="text-brand-red underline decoration-wavy decoration-2 underline-offset-4">AI</span>
          </span>
        </div>

        <div class="hidden md:flex items-center gap-8 text-sm font-bold text-brand-dark/80">
          <a href="#features" class="hover:text-brand-red hover:underline decoration-2 underline-offset-4 transition-all">Features</a>
          <a href="#pricing" class="hover:text-brand-red hover:underline decoration-2 underline-offset-4 transition-all">Pricing</a>
          <a href="#developers" class="hover:text-brand-red hover:underline decoration-2 underline-offset-4 transition-all">Developers</a>
          <a href="{$smarty.const.APP_URL}/blog" class="hover:text-brand-red hover:underline decoration-2 underline-offset-4 transition-all">Blog</a>
        </div>

        <div class="flex items-center gap-4">
          <a href="{$smarty.const.APP_URL}/app" class="btn-primary !py-2 !px-5 !text-sm">Start Chat</a>
        </div>
      </div>
    </nav>

    <div class="min-h-screen bg-white bg-grid-pattern text-brand-dark font-sans selection:bg-brand-red selection:text-white">
      <!-- Hero Section -->
      <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
        <div class="container mx-auto px-6 relative z-10">
          <div class="w-full">
            <p class="text-center text-3xl md:text-5xl font-black mb-6">
              <span class="text-brand-dark">Most of</span>
              <span class="text-brand-blue">Sri Lankan students</span><span class="text-brand-red">🇱🇰</span>
              <span class="text-brand-dark">love</span>
              <span class="text-brand-red">Apilageai</span>
            </p>
            <div class="relative w-full aspect-video max-w-6xl mx-auto border-2 border-brand-dark shadow-hard bg-black">
              <div class="absolute top-3 left-3 flex items-center gap-2 z-10">
                <span class="w-3 h-3 rounded-full bg-[#FF5F57] border border-brand-dark"></span>
                <span class="w-3 h-3 rounded-full bg-[#FFBD2E] border border-brand-dark"></span>
                <span class="w-3 h-3 rounded-full bg-[#28C840] border border-brand-dark"></span>
              </div>
              <iframe
                class="absolute inset-0 w-full h-full pointer-events-none"
                src="https://www.youtube.com/embed/-N5WyLAJ-Qs?autoplay=1&mute=1&controls=0&disablekb=1&fs=0&modestbranding=1&rel=0&loop=1&playlist=-N5WyLAJ-Qs&iv_load_policy=3&playsinline=1"
                title="ApilageAI Intro Video"
                frameborder="0"
                referrerpolicy="strict-origin-when-cross-origin"
                allow="autoplay; encrypted-media"
                tabindex="-1"
                aria-hidden="true"
              ></iframe>
            </div>
            <div class="mt-10 text-center">
              <div class="text-lg md:text-xl font-bold text-brand-dark mb-4">Get started free</div>
              <div class="flex flex-col md:flex-row items-center justify-center gap-4">
                <a href="{$smarty.const.APP_URL}/auth/google" class="btn-secondary w-full md:w-auto !py-3 !px-6">
                  <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/3/3c/Google_Favicon_2025.svg/250px-Google_Favicon_2025.svg.png" alt="Google" width="18" height="18" style="width:18px;height:18px;margin-right:8px;vertical-align:middle;" /> Continue with Google
                </a>
                <a href="{$smarty.const.APP_URL}/auth/login?mode=register" class="btn-secondary w-full md:w-auto !py-3 !px-6">
                  <i class="fa fa-envelope mr-2"></i> Continue with Email
                </a>
              </div>
            </div>
          </div>
        </div>
      </section>
