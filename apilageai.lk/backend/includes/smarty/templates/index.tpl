{include file="components/head.tpl"}
{include file="components/header.tpl"}
      <!-- Marquee -->
      <section class="py-8 border-y-2 border-brand-dark bg-brand-dark text-white overflow-hidden">
        <div class="flex gap-10 animate-marquee" style="width: calc(200% + 4rem); animation-duration: 50s;">
          <div class="flex items-center gap-3 bg-white/10 border-2 border-brand-blueLight rounded-2xl px-4 py-3 shadow-hard-sm min-w-[320px]">
            <img src="{$smarty.const.APP_URL}/assets/images/user.png" alt="Nimal Perera" class="w-10 h-10 rounded-full border-2 border-brand-blueLight bg-white object-cover">
            <div class="whitespace-normal">
              <div class="text-sm font-bold text-white">Nimal Perera</div>
              <div class="text-xs text-brand-blueLight font-semibold">A/L Science Student</div>
              <div class="text-sm text-white/80">“Mindmaps made my revision super fast.”</div>
            </div>
          </div>

          <div class="flex items-center gap-3 bg-white/10 border-2 border-brand-blueLight rounded-2xl px-4 py-3 shadow-hard-sm min-w-[320px]">
            <img src="{$smarty.const.APP_URL}/assets/images/user.png" alt="Tharushi Silva" class="w-10 h-10 rounded-full border-2 border-brand-blueLight bg-white object-cover">
            <div class="whitespace-normal">
              <div class="text-sm font-bold text-white">Tharushi Silva</div>
              <div class="text-xs text-brand-blueLight font-semibold">O/L Student</div>
              <div class="text-sm text-white/80">“Sinhala answers feel natural and clear.”</div>
            </div>
          </div>

          <div class="flex items-center gap-3 bg-white/10 border-2 border-brand-blueLight rounded-2xl px-4 py-3 shadow-hard-sm min-w-[320px]">
            <img src="{$smarty.const.APP_URL}/assets/images/user.png" alt="Sahan Jayasinghe" class="w-10 h-10 rounded-full border-2 border-brand-blueLight bg-white object-cover">
            <div class="whitespace-normal">
              <div class="text-sm font-bold text-white">Sahan Jayasinghe</div>
              <div class="text-xs text-brand-blueLight font-semibold">University Student</div>
              <div class="text-sm text-white/80">“Flowcharts explain tough lessons fast.”</div>
            </div>
          </div>

          <div class="flex items-center gap-3 bg-white/10 border-2 border-brand-blueLight rounded-2xl px-4 py-3 shadow-hard-sm min-w-[320px]">
            <img src="{$smarty.const.APP_URL}/assets/images/user.png" alt="Malini Fernando" class="w-10 h-10 rounded-full border-2 border-brand-blueLight bg-white object-cover">
            <div class="whitespace-normal">
              <div class="text-sm font-bold text-white">Malini Fernando</div>
              <div class="text-xs text-brand-blueLight font-semibold">Tuition Teacher</div>
              <div class="text-sm text-white/80">“MCQ generator saves hours each week.”</div>
            </div>
          </div>

          <div class="flex items-center gap-3 bg-white/10 border-2 border-brand-blueLight rounded-2xl px-4 py-3 shadow-hard-sm min-w-[320px]">
            <img src="{$smarty.const.APP_URL}/assets/images/user.png" alt="Hiruni Karunaratne" class="w-10 h-10 rounded-full border-2 border-brand-blueLight bg-white object-cover">
            <div class="whitespace-normal">
              <div class="text-sm font-bold text-white">Hiruni Karunaratne</div>
              <div class="text-xs text-brand-blueLight font-semibold">A/L Arts Student</div>
              <div class="text-sm text-white/80">“Summaries help me remember key points.”</div>
            </div>
          </div>

          <!-- Duplicate for seamless loop -->
          <div class="flex items-center gap-3 bg-white/10 border-2 border-brand-blueLight rounded-2xl px-4 py-3 shadow-hard-sm min-w-[320px]">
            <img src="{$smarty.const.APP_URL}/assets/images/user.png" alt="Nimal Perera" class="w-10 h-10 rounded-full border-2 border-brand-blueLight bg-white object-cover">
            <div class="whitespace-normal">
              <div class="text-sm font-bold text-white">Nimal Perera</div>
              <div class="text-xs text-brand-blueLight font-semibold">A/L Science Student</div>
              <div class="text-sm text-white/80">“Mindmaps made my revision super fast.”</div>
            </div>
          </div>

          <div class="flex items-center gap-3 bg-white/10 border-2 border-brand-blueLight rounded-2xl px-4 py-3 shadow-hard-sm min-w-[320px]">
            <img src="{$smarty.const.APP_URL}/assets/images/user.png" alt="Tharushi Silva" class="w-10 h-10 rounded-full border-2 border-brand-blueLight bg-white object-cover">
            <div class="whitespace-normal">
              <div class="text-sm font-bold text-white">Tharushi Silva</div>
              <div class="text-xs text-brand-blueLight font-semibold">O/L Student</div>
              <div class="text-sm text-white/80">“Sinhala answers feel natural and clear.”</div>
            </div>
          </div>

          <div class="flex items-center gap-3 bg-white/10 border-2 border-brand-blueLight rounded-2xl px-4 py-3 shadow-hard-sm min-w-[320px]">
            <img src="{$smarty.const.APP_URL}/assets/images/user.png" alt="Sahan Jayasinghe" class="w-10 h-10 rounded-full border-2 border-brand-blueLight bg-white object-cover">
            <div class="whitespace-normal">
              <div class="text-sm font-bold text-white">Sahan Jayasinghe</div>
              <div class="text-xs text-brand-blueLight font-semibold">University Student</div>
              <div class="text-sm text-white/80">“Flowcharts explain tough lessons fast.”</div>
            </div>
          </div>

          <div class="flex items-center gap-3 bg-white/10 border-2 border-brand-blueLight rounded-2xl px-4 py-3 shadow-hard-sm min-w-[320px]">
            <img src="{$smarty.const.APP_URL}/assets/images/user.png" alt="Malini Fernando" class="w-10 h-10 rounded-full border-2 border-brand-blueLight bg-white object-cover">
            <div class="whitespace-normal">
              <div class="text-sm font-bold text-white">Malini Fernando</div>
              <div class="text-xs text-brand-blueLight font-semibold">Tuition Teacher</div>
              <div class="text-sm text-white/80">“MCQ generator saves hours each week.”</div>
            </div>
          </div>

          <div class="flex items-center gap-3 bg-white/10 border-2 border-brand-blueLight rounded-2xl px-4 py-3 shadow-hard-sm min-w-[320px]">
            <img src="{$smarty.const.APP_URL}/assets/images/user.png" alt="Hiruni Karunaratne" class="w-10 h-10 rounded-full border-2 border-brand-blueLight bg-white object-cover">
            <div class="whitespace-normal">
              <div class="text-sm font-bold text-white">Hiruni Karunaratne</div>
              <div class="text-xs text-brand-blueLight font-semibold">A/L Arts Student</div>
              <div class="text-sm text-white/80">“Summaries help me remember key points.”</div>
            </div>
          </div>
        </div>
      </section>
         <!-- Feature Demos Grid -->
      <section id="features" class="py-24 relative overflow-hidden bg-white">
        <div class="container mx-auto px-6">
          <div class="text-center max-w-3xl mx-auto mb-16">
             <div class="inline-block px-3 py-1 bg-brand-blueLight border-2 border-brand-dark rounded-full text-xs font-bold mb-4 uppercase tracking-wider text-brand-dark">Features</div>
            <h2 class="text-4xl md:text-6xl font-display font-black text-brand-dark mb-6">
              MORE THAN JUST <br/>
              <span class="text-brand-red decoration-wavy underline decoration-brand-dark">CHAT. <i class="fa fa-paper-plane"></i></span> 
            </h2>
            <p class="text-brand-dark/70 text-xl font-medium">
              We visualized learning. Hover over the cards to see ApilageAI in action.
            </p>
          </div>

          <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-7xl mx-auto">
            <div class="relative bg-yellow-200 border-2 border-brand-dark rounded-lg p-6 shadow-hard rotate-[-2deg]">
              <div class="absolute -top-3 left-6 w-12 h-6 bg-white/70 border-2 border-brand-dark shadow-hard-sm -rotate-2"></div>
              <h3 class="text-xl font-bold text-brand-dark mb-2">AI Mindmaps</h3>
              <p class="text-brand-dark/70 text-sm font-medium">Visualize complex topics instantly with auto-generated mindmaps.</p>
            </div>

            <div class="relative bg-pink-200 border-2 border-brand-dark rounded-lg p-6 shadow-hard rotate-[1.5deg]">
              <div class="absolute -top-3 left-6 w-12 h-6 bg-white/70 border-2 border-brand-dark shadow-hard-sm rotate-1"></div>
              <h3 class="text-xl font-bold text-brand-dark mb-2">Flowcharts</h3>
              <p class="text-brand-dark/70 text-sm font-medium">Convert processes and timelines into clear flowcharts.</p>
            </div>

            <div class="relative bg-blue-200 border-2 border-brand-dark rounded-lg p-6 shadow-hard rotate-[-1deg]">
              <div class="absolute -top-3 left-6 w-12 h-6 bg-white/70 border-2 border-brand-dark shadow-hard-sm -rotate-1"></div>
              <h3 class="text-xl font-bold text-brand-dark mb-2">MCQ Generator</h3>
              <p class="text-brand-dark/70 text-sm font-medium">Test your knowledge with unlimited AI-generated quizzes.</p>
            </div>

            <div class="relative bg-green-200 border-2 border-brand-dark rounded-lg p-6 shadow-hard rotate-[2deg]">
              <div class="absolute -top-3 left-6 w-12 h-6 bg-white/70 border-2 border-brand-dark shadow-hard-sm rotate-2"></div>
              <h3 class="text-xl font-bold text-brand-dark mb-2">Image Gen</h3>
              <p class="text-brand-dark/70 text-sm font-medium">Turn your ideas into images for presentations and notes.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Podcast Section -->
      <section id="podcast" class="py-24 bg-brand-blueLight/30 border-t-2 border-brand-dark relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-3 bg-grid-pattern opacity-20"></div>
        <div class="container mx-auto px-6 relative z-10">
          <div class="flex flex-col lg:flex-row items-center gap-12">
            <div class="flex-1 text-center lg:text-left">
              <div class="inline-block px-3 py-1 bg-white border-2 border-brand-dark rounded-full text-xs font-bold mb-4 uppercase tracking-wider text-brand-dark">Podcast</div>
              <h2 class="text-4xl md:text-6xl font-display font-black text-brand-dark mb-6">
                Listen to ApilageAI Podcast
              </h2>
              <p class="text-brand-dark/70 text-xl font-medium">
                Here is a little audio podcast about why ApilageAI is better than other AI platforms.
              </p>
            </div>

            <div class="flex-1 flex flex-col items-center lg:items-end gap-4">
              <button id="podcastToggle" type="button" class="group bg-white border-2 border-brand-dark rounded-2xl px-10 py-8 shadow-hard hover:shadow-hard-lg transition-all text-center">
                <div class="text-6xl md:text-7xl leading-none">📢</div>
                <div class="text-lg font-bold text-brand-dark mt-3">Click to play / click again to stop</div>
                <div data-status class="text-sm text-brand-dark/60 mt-1">Stopped</div>
              </button>
              <audio id="apilageaiPodcast" preload="none" src="https://apilageai.lk/assets/sounds/whyapilageaibetter.mp3"></audio>
              <p class="text-xs text-brand-dark/60 font-medium">Scroll down to stop the audio.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Memory Section -->
      <section id="memory" class="py-24 bg-white border-t-2 border-brand-dark relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(rgba(0,0,0,0.04)_1px,transparent_1px)] [background-size:22px_22px]"></div>
        <div class="container mx-auto px-6 relative z-10">
          <div class="text-center max-w-3xl mx-auto mb-12">
            <div class="inline-block px-3 py-1 bg-brand-blueLight border-2 border-brand-dark rounded-full text-xs font-bold mb-4 uppercase tracking-wider text-brand-dark">Memory</div>
            <h2 class="text-4xl md:text-6xl font-display font-black text-brand-dark mb-6">
              Most Advanced Memory Management
            </h2>
            <p class="text-brand-dark/70 text-xl font-medium">
              More personalized for students and for day-to-day tasks.
            </p>
          </div>

          <div class="flex flex-col lg:flex-row items-center gap-12">
            <div class="flex-1">
              <p class="text-lg text-brand-dark/80 font-medium mb-4">
                ApilageAI builds a living memory nest that connects lessons, habits, and daily routines. It adapts to how each student learns and keeps the most useful context at the center.
              </p>
              <p class="text-lg text-brand-dark/80 font-medium">
                The result is faster recall, clearer recommendations, and smarter next steps for both study plans and everyday tasks.
              </p>
            </div>
            <div class="flex-1 w-full">
              <img src="{$smarty.const.APP_URL}/assets/images/mesh.png" alt="Memory Mesh" class="w-full max-w-lg mx-auto lg:mx-0 rounded-2xl border-2 border-brand-dark shadow-hard bg-white">
            </div>
          </div>
        </div>
      </section>
      
       <!-- Master Model Section -->
      <section class="py-24 bg-white border-t-2 border-brand-dark">
        <div class="container mx-auto px-6">
          <div class="flex flex-col lg:flex-row items-center gap-16">
            <div class="flex-1 text-left">
              <h2 class="text-4xl md:text-6xl font-display font-black text-brand-dark mb-6">
                Introducing our latest model <span class="text-brand-red">Master model</span>
              </h2>
           -              <p class="text-xl text-brand-dark/70 mb-4 leading-relaxed font-medium">
                Master model can speak Sinhala like a Sri Lankan person and can answer 99.99% Sinhala accurate with complex problems like Maths, Physics, Chemistry, or any other subjects.
              </p>
             -              <p class="text-xl text-brand-dark/70 mb-4 leading-relaxed font-medium">
                It can generate sketches of Maths and Physics problems.
              </p>
              <p class="text-xl text-brand-red leading-relaxed font-medium">
                Soon it can generate educational explanatory videos.
            </div>
            <div class="flex-1">
              <img src="{$smarty.const.APP_URL}/assets/images/super.png" alt="Master Model Screenshot" class="w-full rounded-lg shadow-hard border-2 border-brand-dark">
            </div>
          </div>
        </div>
      </section>
       <!-- Pricing Section -->
      <section id="pricing" class="py-24 bg-brand-blueLight/30 relative">
        <div class="absolute top-0 left-0 w-full h-4 bg-grid-pattern opacity-20"></div>
        <div class="container mx-auto px-6">
           <div class="text-center mb-16">
              <h2 class="text-4xl md:text-5xl font-display font-black text-brand-dark mb-4">Pay As You Go <i class="fa fa-rocket"></i></h2>
              <p class="text-xl text-brand-dark/60 font-hand font-bold">No subscriptions. No nonsense.</p>
           </div>

           <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto items-end">
              <div class="bg-white p-8 rounded-2xl border-2 border-brand-dark shadow-hard hover:shadow-hard-lg transition-all">
                 <h3 class="font-bold text-2xl mb-2 text-brand-dark">Starter Pack</h3>
                 <div class="text-4xl font-black font-display mb-4 text-brand-dark">Rs. 200</div>
                 <ul class="space-y-3 mb-8 text-sm font-medium text-brand-dark/80">
                    <li class="flex items-center gap-2">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-brand-blue">
                        <polyline points="20,6 9,17 4,12"/>
                      </svg>
                      Unlimited Chats
                    </li>
                    <li class="flex items-center gap-2">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-brand-blue">
                        <polyline points="20,6 9,17 4,12"/>
                      </svg>
                      Unlimited Image Analysis
                    </li>
                    <li class="flex items-center gap-2">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-brand-blue">
                        <polyline points="20,6 9,17 4,12"/>
                      </svg>
                      Unlimited Image Generation
                    </li>
                    <li class="flex items-center gap-2">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-brand-blue">
                        <polyline points="20,6 9,17 4,12"/>
                      </svg>
                      Access to all models
                    </li>
                    <li class="flex items-center gap-2">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-brand-blue">
                        <polyline points="20,6 9,17 4,12"/>
                      </svg>
                      Access to graphing & code generation
                    </li>
                    <li class="flex items-center gap-2">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-brand-blue">
                        <polyline points="20,6 9,17 4,12"/>
                      </svg>
                      Valid for 60 days
                    </li>
                 </ul>
                 <a href="{$smarty.const.APP_URL}/pay/200" class="btn-secondary w-full">Top Up</a>
              </div>

              <div class="bg-brand-red p-8 rounded-2xl border-2 border-brand-dark shadow-hard-lg relative transform md:-translate-y-4">
                 <div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-brand-dark text-white px-4 py-1 rounded-full text-sm font-bold border-2 border-white">Most Popular</div>
<h3 class="font-bold text-2xl mb-2 text-white">Pro</h3>
                 <div class="text-4xl font-black font-display mb-4 text-white">Rs. 500</div>
                 <ul class="space-y-3 mb-8 text-white font-medium">
                    <li class="flex items-center gap-2">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-white">
                        <polyline points="20,6 9,17 4,12"/>
                      </svg>
                      Unlimited Chats
                    </li>
                    <li class="flex items-center gap-2">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-white">
                        <polyline points="20,6 9,17 4,12"/>
                      </svg>
                      Unlimited Image Analysis
                    </li>
                    <li class="flex items-center gap-2">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-white">
                        <polyline points="20,6 9,17 4,12"/>
                      </svg>
                      Unlimited Image Generation
                    </li>
                    <li class="flex items-center gap-2">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-white">
                        <polyline points="20,6 9,17 4,12"/>
                      </svg>
                      Access to all models
                    </li>
                    <li class="flex items-center gap-2">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-white">
                        <polyline points="20,6 9,17 4,12"/>
                      </svg>
                      Access to graphing & code generation
                    </li>
                    <li class="flex items-center gap-2">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-white">
                        <polyline points="20,6 9,17 4,12"/>
                      </svg>
                      Valid for 60 days
                    </li>
                 </ul>
                 <a href="{$smarty.const.APP_URL}/pay/500" class="btn-secondary w-full border-none !shadow-hard">Top Up Now</a>
              </div>

              <div class="bg-white p-8 rounded-2xl border-2 border-brand-dark shadow-hard hover:shadow-hard-lg transition-all">
                 <h3 class="font-bold text-2xl mb-2 text-brand-dark">Power User</h3>
                 <div class="text-4xl font-black font-display mb-4 text-brand-dark">Rs. 1000</div>
                 <ul class="space-y-3 mb-8 text-sm font-medium text-brand-dark/80">
                    <li class="flex items-center gap-2">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-brand-blue">
                        <polyline points="20,6 9,17 4,12"/>
                      </svg>
                      150,000 AI Tokens
                    </li>
                    <li class="flex items-center gap-2">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-brand-blue">
                        <polyline points="20,6 9,17 4,12"/>
                      </svg>
                      API Access Included
                    </li>
                    <li class="flex items-center gap-2">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-brand-blue">
                        <polyline points="20,6 9,17 4,12"/>
                      </svg>
                      Valid for 60 days
                    </li>
                    <li class="flex items-center gap-2">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-brand-blue">
                        <polyline points="20,6 9,17 4,12"/>
                      </svg>
                      Access to latest releases
                    </li>
                    <li class="flex items-center gap-2">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-brand-blue">
                        <polyline points="20,6 9,17 4,12"/>
                      </svg>
                      Access to video generation model (Coming soon)
                    </li>
                 </ul>
                 <a href="{$smarty.const.APP_URL}/pay/1000" class="btn-secondary w-full">Top Up</a>
              </div>
           </div>

           <div class="text-center mt-12">
             <p class="text-brand-dark/60 font-medium text-sm border-2 border-dashed border-brand-dark/30 inline-block px-4 py-2 rounded-lg bg-white">
                All recharges are valid for two month from the date of purchase.
             </p>
           </div>
        </div>
      </section>
   
     <!-- Developer Section -->
      <section id="developers" class="py-24 border-t-2 border-brand-dark bg-white overflow-hidden">
        <div class="container mx-auto px-6">
          <div class="flex flex-col lg:flex-row items-center gap-16">
             <div class="flex-1">
                <div class="flex items-center gap-2 mb-4">
                  <svg class="w-6 h-6 text-brand-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <polyline points="16,18 22,12 16,6"/>
                    <polyline points="8,6 2,12 8,18"/>
                  </svg>
                  <span class="font-bold font-mono text-brand-blue">DEVELOPERS</span>
                </div>
                <h2 class="text-4xl md:text-5xl font-display font-black text-brand-dark mb-6">
                  BUILD THE FUTURE <br/> WITH <span class="text-brand-red underline decoration-wavy">APILAGE AI</span>.
                </h2>
                <p class="text-lg text-brand-dark/70 mb-8 leading-relaxed">
                  Integrate Sri Lanka's most powerful educational AI models directly into your LMS, website, or mobile app. Native Sinhala support out of the box.
                </p>
                <div class="flex gap-4">
                  <a href="https://api.apilageai.lk" class="btn-primary bg-brand-dark text-white hover:bg-brand-dark/90">Get API Key</a>
                  <a href="https://api.apilageai.lk" class="btn-outline">Read Docs</a>
                </div>
             </div>
             <div class="flex-1 w-full relative">
                <div class="absolute -top-6 -right-6 w-24 h-24 bg-brand-blueLight rounded-full border-2 border-brand-dark hidden lg:block animate-pulse"></div>
                <div class="bg-[#172554] rounded-xl border-2 border-brand-dark shadow-hard p-4 font-mono text-sm text-gray-300 relative overflow-hidden">
                  <div class="flex gap-2 mb-4 border-b border-gray-700 pb-2">
                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                    <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                  </div>
                  <pre class="overflow-x-auto">
                    <code>
<span class="text-brand-blue">curl</span> -s -X POST <span class="text-green-400">APILAGEAPI_URL</span> \ <br>
  -H <span class="text-green-400">Content-Type: application/json</span> \ <br>
  -H <span class="text-green-400">Authorization: Bearer APILAGEAI_API</span> \ <br>
  -d <span class="text-green-400">{literal}{'message': 'Explain Newton law of motion', 'enableGoogleSearch': true}{/literal}</span> \  <br>
  | jq -r <span class="text-green-400">.response</span> <br>
                    </code>
                  </pre>
                </div>
             </div>
          </div>
        </div>
      </section>
      
         <!-- Mobile App Section -->
      <section class="py-20 bg-brand-blue text-white border-t-2 border-brand-dark relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(rgba(255,255,255,0.2)_1px,transparent_1px)] [background-size:20px_20px]"></div>
        <div class="container mx-auto px-6 text-center relative z-10">
          <svg class="w-16 h-16 mx-auto mb-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <rect x="5" y="2" width="14" height="20" rx="2" ry="2" stroke-width="2"/>
            <line x1="12" y1="18" x2="12" y2="18" stroke-width="2"/>
          </svg>
          <h2 class="text-4xl md:text-6xl font-display font-black mb-6">
            POCKET GENIUS.
          </h2>
          <p class="text-xl text-white/90 mb-10 max-w-2xl mx-auto font-medium">
            Download th e official ApilageAI mobile app. Scan notes, get answers, and study on the bus.
          </p>
          <a href="https://play.google.com/store/apps/details?id=com.apilageai.apilageai&hl=en" target="_blank" class="bg-white text-brand-dark px-8 py-4 rounded-xl border-2 border-brand-dark shadow-hard hover:shadow-hard-lg font-bold text-lg flex items-center gap-3 mx-auto transition-all">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2h-3"/>
            </svg>
            Download on Play Store
          </a>

          <div class="mt-8 flex justify-center gap-1">
             <svg class="w-5 h-5 fill-yellow-400 text-yellow-400" viewBox="0 0 24 24">
               <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26 12,2"/>
             </svg>
             <svg class="w-5 h-5 fill-yellow-400 text-yellow-400" viewBox="0 0 24 24">
               <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26 12,2"/>
             </svg>
             <svg class="w-5 h-5 fill-yellow-400 text-yellow-400" viewBox="0 0 24 24">
               <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26 12,2"/>
             </svg>
             <svg class="w-5 h-5 fill-yellow-400 text-yellow-400" viewBox="0 0 24 24">
               <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26 12,2"/>
             </svg>
             <svg class="w-5 h-5 fill-yellow-400 text-yellow-400" viewBox="0 0 24 24">
               <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26 12,2"/>
             </svg>
          </div>
          <p class="text-sm font-hand mt-2">4.8/5 Rating based on 100+ reviews</p>
        </div>
      </section>


<script>
  (function () {
    const button = document.getElementById('podcastToggle');
    const audio = document.getElementById('apilageaiPodcast');
    if (!button || !audio) return;
    const status = button.querySelector('[data-status]');

    const setState = (playing) => {
      button.setAttribute('aria-pressed', playing ? 'true' : 'false');
      if (status) {
        status.textContent = playing ? 'Playing... click again to stop' : 'Stopped';
      }
    };

    setState(false);

    button.addEventListener('click', async () => {
      if (audio.paused) {
        try {
          await audio.play();
          setState(true);
        } catch (err) {
          setState(false);
        }
      } else {
        audio.pause();
        setState(false);
      }
    });

    window.addEventListener('scroll', () => {
      if (!audio.paused) {
        audio.pause();
        setState(false);
      }
    }, { passive: true });

    audio.addEventListener('ended', () => setState(false));
  })();
</script>

{include file="components/footer.tpl"}
