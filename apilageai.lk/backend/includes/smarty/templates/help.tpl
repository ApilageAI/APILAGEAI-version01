{include file="components/head.tpl"}

<style>
  :root {
    --help-red: #ff3b30;
    --help-blue: #38bdf8;
    --help-dark: #172554;
    --help-ink: #0f172a;
  }
  .help-layout {
    display: grid;
    gap: 32px;
    grid-template-columns: minmax(240px, 300px) minmax(0, 1fr);
  }
  .help-sidebar {
    position: sticky;
    top: 110px;
    align-self: start;
  }
  .help-topic {
    transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease, background 0.15s ease;
  }
  .help-topic:hover {
    transform: translateY(-2px);
  }
  .help-topic.active {
    border-color: var(--help-red);
    box-shadow: 4px 4px 0 0 var(--help-dark);
    background: #fff;
  }
  .topic-letter {
    width: 34px;
    height: 34px;
    border-radius: 12px;
    background: var(--help-ink);
    color: #fff;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
  }
  .help-section {
    scroll-margin-top: 120px;
  }
  .help-card {
    border: 2px solid var(--help-dark);
    border-radius: 18px;
    background: #ffffff;
    box-shadow: 4px 4px 0 0 var(--help-dark);
  }
  .help-callout {
    background: linear-gradient(135deg, rgba(56, 189, 248, 0.22), rgba(255, 59, 48, 0.12));
  }
  @media (max-width: 1024px) {
    .help-layout {
      grid-template-columns: 1fr;
    }
    .help-sidebar {
      position: relative;
      top: auto;
    }
  }
</style>

<div class="min-h-screen bg-white bg-grid-pattern text-brand-dark font-sans selection:bg-brand-red selection:text-white">
  <nav id="navbar" class="navbar-normal fixed top-0 left-0 right-0 z-50 transition-all duration-300">
    <div class="container mx-auto px-6 flex items-center justify-between">
      <a href="{$smarty.const.APP_URL}/" class="flex items-center gap-3">
        <img src="{$smarty.const.APP_URL}/assets/images/icon.png" alt="ApilageAI Logo" class="w-10 h-10 object-contain" />
        <span class="text-xl font-bold font-display text-brand-dark tracking-tight">
          Apilage<span class="text-brand-red underline decoration-wavy decoration-2 underline-offset-4">AI</span>
        </span>
      </a>
      <div class="flex items-center gap-4 text-sm font-bold text-brand-dark/80">
        <a href="{$smarty.const.APP_URL}/" class="hover:text-brand-red hover:underline decoration-2 underline-offset-4 transition-all">Home</a>
        <a href="{$smarty.const.APP_URL}/app" class="btn-primary !py-2 !px-5 !text-sm">Start Chat</a>
      </div>
    </div>
  </nav>

  <main class="pt-28 pb-20">
    <section class="container mx-auto px-6">
      <div class="max-w-6xl mx-auto">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-blueLight border-2 border-brand-dark text-brand-dark text-xs font-bold mb-6 uppercase tracking-wider">
          Help Center
        </div>
        <h1 class="text-4xl md:text-6xl font-display font-black text-brand-dark mb-4">
          ApilageAI Help Center: A-Z Guide for Sri Lankan Students and Creators
        </h1>
        <p class="text-brand-dark/70 text-base md:text-lg font-medium max-w-3xl">
          Explore every part of ApilageAI in one place. This SEO-friendly A-Z guide covers account creation, study tools,
          public profiles, learning streaks, pricing in LKR, data safety, and ways to contribute to the community.
        </p>

        <div class="mt-8 grid md:grid-cols-3 gap-4">
          <div class="p-5 border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm">
            <h3 class="font-bold text-brand-dark">A-Z Navigation</h3>
            <p class="text-sm text-brand-dark/70 mt-2">
              Jump instantly to any help topic using the left sidebar or the topic search.
            </p>
          </div>
          <div class="p-5 border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm">
            <h3 class="font-bold text-brand-dark">Sri Lanka Focused</h3>
            <p class="text-sm text-brand-dark/70 mt-2">
              Built for Sri Lankan students with syllabus-based learning, LKR pricing, and local support.
            </p>
          </div>
          <div class="p-5 border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm">
            <h3 class="font-bold text-brand-dark">Updated Guidance</h3>
            <p class="text-sm text-brand-dark/70 mt-2">
              Clear answers about public profiles, learning streaks, safety, and reporting issues.
            </p>
          </div>
        </div>
      </div>
    </section>

    <section class="container mx-auto px-6 mt-12">
      <div class="max-w-6xl mx-auto help-layout">
        <aside class="help-sidebar">
          <div class="help-card p-5">
            <div class="text-xs uppercase tracking-wider font-bold text-brand-dark/70 mb-3">Search sectors</div>
            <label class="sr-only" for="topicSearch">Search help sectors</label>
            <div class="flex items-center gap-2 border-2 border-brand-dark rounded-full px-4 py-2 bg-white">
              <i class="fa-solid fa-magnifying-glass text-brand-dark/60"></i>
              <input id="topicSearch" type="search" class="w-full bg-transparent outline-none text-sm font-medium text-brand-dark" placeholder="Search help topics" />
            </div>
            <div id="topicSearchStatus" class="text-xs text-brand-dark/60 mt-2">Showing 10 sectors.</div>
          </div>

          <nav class="mt-6 flex flex-col gap-3" aria-label="Help topics A to Z">
            <a class="help-topic border-2 border-brand-dark rounded-2xl p-3 bg-white flex items-start gap-3" href="#account-creation" data-topic-item data-topic="account creation signup register who can create what it does user data terms conditions">
              <span class="topic-letter">A</span>
              <span>
                <span class="block font-bold text-brand-dark">Account creation</span>
                <span class="block text-xs text-brand-dark/60">Sign up, eligibility, data, terms</span>
              </span>
            </a>
            <a class="help-topic border-2 border-brand-dark rounded-2xl p-3 bg-white flex items-start gap-3" href="#use-cases" data-topic-item data-topic="use cases how to use studies daily life image generation mindmaps visual graphs coding mcq games sri lankan syllabus">
              <span class="topic-letter">B</span>
              <span>
                <span class="block font-bold text-brand-dark">Use cases and how to use</span>
                <span class="block text-xs text-brand-dark/60">Studies, images, mindmaps, coding</span>
              </span>
            </a>
            <a class="help-topic border-2 border-brand-dark rounded-2xl p-3 bg-white flex items-start gap-3" href="#public-profiles" data-topic-item data-topic="public profiles learning streaks what they do profile visibility study streak">
              <span class="topic-letter">C</span>
              <span>
                <span class="block font-bold text-brand-dark">Public profiles and learning streaks</span>
                <span class="block text-xs text-brand-dark/60">Visibility, streak tracking</span>
              </span>
            </a>
            <a class="help-topic border-2 border-brand-dark rounded-2xl p-3 bg-white flex items-start gap-3" href="#free-pro" data-topic-item data-topic="free version pro version compare lkr pay as you go sri lankan bank approved payment gateway free credits">
              <span class="topic-letter">D</span>
              <span>
                <span class="block font-bold text-brand-dark">Free vs Pro</span>
                <span class="block text-xs text-brand-dark/60">LKR pricing and pay-as-you-go</span>
              </span>
            </a>
            <a class="help-topic border-2 border-brand-dark rounded-2xl p-3 bg-white flex items-start gap-3" href="#data-safety" data-topic-item data-topic="activity data safety account deletion how to delete what happens when delete">
              <span class="topic-letter">E</span>
              <span>
                <span class="block font-bold text-brand-dark">Activity and data safety</span>
                <span class="block text-xs text-brand-dark/60">Deletion, privacy, security</span>
              </span>
            </a>
            <a class="help-topic border-2 border-brand-dark rounded-2xl p-3 bg-white flex items-start gap-3" href="#account-blocked" data-topic-item data-topic="account deleted blocked ip blacklisting trial abuse nudity unwanted content">
              <span class="topic-letter">F</span>
              <span>
                <span class="block font-bold text-brand-dark">Why accounts get blocked</span>
                <span class="block text-xs text-brand-dark/60">Policy and safety reasons</span>
              </span>
            </a>
            <a class="help-topic border-2 border-brand-dark rounded-2xl p-3 bg-white flex items-start gap-3" href="#student-discounts" data-topic-item data-topic="school university student discounts sri lanka contact apilageai.lk">
              <span class="topic-letter">G</span>
              <span>
                <span class="block font-bold text-brand-dark">School and university students</span>
                <span class="block text-xs text-brand-dark/60">Discounts and education support</span>
              </span>
            </a>
            <a class="help-topic border-2 border-brand-dark rounded-2xl p-3 bg-white flex items-start gap-3" href="#volunteer-invest" data-topic-item data-topic="volunteering investing funding jobs web developer graphic designer student ambassador sri lankan only">
              <span class="topic-letter">H</span>
              <span>
                <span class="block font-bold text-brand-dark">Volunteer or invest</span>
                <span class="block text-xs text-brand-dark/60">Funding and open roles</span>
              </span>
            </a>
            <a class="help-topic border-2 border-brand-dark rounded-2xl p-3 bg-white flex items-start gap-3" href="#data-sources" data-topic-item data-topic="data sources government support websites educational youtube textbooks teachers guides modules">
              <span class="topic-letter">I</span>
              <span>
                <span class="block font-bold text-brand-dark">Data sources</span>
                <span class="block text-xs text-brand-dark/60">Government and education sources</span>
              </span>
            </a>
            <a class="help-topic border-2 border-brand-dark rounded-2xl p-3 bg-white flex items-start gap-3" href="#bugs-errors" data-topic-item data-topic="bugs errors report icon chat page">
              <span class="topic-letter">J</span>
              <span>
                <span class="block font-bold text-brand-dark">Bugs and errors</span>
                <span class="block text-xs text-brand-dark/60">Report issues fast</span>
              </span>
            </a>
            <div id="topicEmptyState" class="text-sm text-brand-dark/60 mt-2 hidden">No sectors match your search.</div>
          </nav>
        </aside>

        <div class="flex flex-col gap-8">
          <section id="account-creation" class="help-section help-card p-6" data-section>
            <h2 class="text-2xl font-display font-black text-brand-dark mb-3">A. Account creation</h2>
            <p class="text-brand-dark/70 font-medium">
              ApilageAI accounts unlock personalized learning, saved chats, public profiles, and learning streaks.
              Create a free account in minutes and start exploring Sri Lankan syllabus-aligned learning.
            </p>
            <div class="mt-4 grid md:grid-cols-2 gap-4">
              <div>
                <h3 class="font-bold text-brand-dark">How to create</h3>
                <p class="text-sm text-brand-dark/70 mt-2">
                  Visit the register page, enter your name, email, and a secure password. Accept the terms and you are in.
                </p>
              </div>
              <div>
                <h3 class="font-bold text-brand-dark">Who can create</h3>
                <p class="text-sm text-brand-dark/70 mt-2">
                  Students, teachers, parents, and lifelong learners can sign up with a valid email address.
                </p>
              </div>
              <div>
                <h3 class="font-bold text-brand-dark">What it does</h3>
                <p class="text-sm text-brand-dark/70 mt-2">
                  Your account stores your chats, image generations, mind maps, streaks, and public profile settings.
                </p>
              </div>
              <div>
                <h3 class="font-bold text-brand-dark">User data collected</h3>
                <p class="text-sm text-brand-dark/70 mt-2">
                  We collect essential details such as name, email, password, school info (optional), and usage history.
                </p>
              </div>
            </div>
            <div class="mt-4">
              <h3 class="font-bold text-brand-dark">Terms and conditions</h3>
              <p class="text-sm text-brand-dark/70 mt-2">
                By creating an account, you agree to the ApilageAI Terms and community safety policies.
              </p>
            </div>
          </section>

          <section id="use-cases" class="help-section help-card p-6" data-section>
            <h2 class="text-2xl font-display font-black text-brand-dark mb-3">B. Use cases and how to use</h2>
            <p class="text-brand-dark/70 font-medium">
              ApilageAI is built for learning and everyday tasks. Use the chat box to ask questions, generate content,
              and get syllabus-aligned explanations in Sinhala, Tamil, or English.
            </p>
            <ul class="mt-4 space-y-2 text-sm text-brand-dark/70 font-medium list-disc pl-5">
              <li><strong class="text-brand-dark">Studies:</strong> Homework help, exam revision, and subject explanations.</li>
              <li><strong class="text-brand-dark">Day-to-day life:</strong> Writing, planning, and daily guidance.</li>
              <li><strong class="text-brand-dark">Image generations:</strong> Create visuals for projects and learning.</li>
              <li><strong class="text-brand-dark">Mind maps:</strong> Turn lessons into structured visual outlines.</li>
              <li><strong class="text-brand-dark">Visual graphs:</strong> Summaries and charts for quick understanding.</li>
              <li><strong class="text-brand-dark">Coding:</strong> Code explanations, debugging, and project ideas.</li>
              <li><strong class="text-brand-dark">MCQ games:</strong> Practice questions for O/L and A/L exams.</li>
              <li><strong class="text-brand-dark">Sri Lankan syllabus based:</strong> Answers aligned with local curriculum.</li>
            </ul>
          </section>

          <section id="public-profiles" class="help-section help-card p-6" data-section>
            <h2 class="text-2xl font-display font-black text-brand-dark mb-3">C. Public profiles and learning streaks</h2>
            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <h3 class="font-bold text-brand-dark">What public profiles do</h3>
                <p class="text-sm text-brand-dark/70 mt-2">
                  Public profiles let you share selected chats and images with others, showcase your work, and build your
                  learning identity.
                </p>
              </div>
              <div>
                <h3 class="font-bold text-brand-dark">What learning streaks do</h3>
                <p class="text-sm text-brand-dark/70 mt-2">
                  Learning streaks track daily study activity and help you build consistent study habits over time.
                </p>
              </div>
            </div>
          </section>

          <section id="free-pro" class="help-section help-card p-6" data-section>
            <h2 class="text-2xl font-display font-black text-brand-dark mb-3">D. Free version and Pro version comparison</h2>
            <p class="text-brand-dark/70 font-medium">
              ApilageAI offers a free experience with optional Pro upgrades. Pricing is in LKR with pay-as-you-go billing.
            </p>
            <div class="mt-4 overflow-x-auto">
              <table class="w-full text-sm text-left border-collapse">
                <thead>
                  <tr class="text-brand-dark">
                    <th class="border-b-2 border-brand-dark pb-2">Feature</th>
                    <th class="border-b-2 border-brand-dark pb-2">Free</th>
                    <th class="border-b-2 border-brand-dark pb-2">Pro</th>
                  </tr>
                </thead>
                <tbody class="text-brand-dark/70">
                  <tr>
                    <td class="py-2 border-b border-brand-dark/20">Pricing (LKR)</td>
                    <td class="py-2 border-b border-brand-dark/20">Free access with limited credits</td>
                    <td class="py-2 border-b border-brand-dark/20">Pay as you go in LKR</td>
                  </tr>
                  <tr>
                    <td class="py-2 border-b border-brand-dark/20">Payment gateway</td>
                    <td class="py-2 border-b border-brand-dark/20">Not required</td>
                    <td class="py-2 border-b border-brand-dark/20">Sri Lankan bank approved secure gateway</td>
                  </tr>
                  <tr>
                    <td class="py-2 border-b border-brand-dark/20">Credits</td>
                    <td class="py-2 border-b border-brand-dark/20">Occasional free credits and gifts</td>
                    <td class="py-2 border-b border-brand-dark/20">Higher limits and top-ups</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>

          <section id="data-safety" class="help-section help-card p-6" data-section>
            <h2 class="text-2xl font-display font-black text-brand-dark mb-3">E. Activity, data safety, and account deletion</h2>
            <p class="text-brand-dark/70 font-medium">
              We take data safety seriously. Your account activity helps improve the experience while keeping access
              controls in place.
            </p>
            <div class="mt-4 grid md:grid-cols-2 gap-4">
              <div>
                <h3 class="font-bold text-brand-dark">How to delete your account</h3>
                <p class="text-sm text-brand-dark/70 mt-2">
                  Use the account settings to request deletion. If you need assistance, contact support for help.
                </p>
              </div>
              <div>
                <h3 class="font-bold text-brand-dark">What happens after deletion</h3>
                <p class="text-sm text-brand-dark/70 mt-2">
                  Your public profile is disabled and your account is removed from access. Some records may be retained
                  only for security and payment compliance.
                </p>
              </div>
            </div>
          </section>

          <section id="account-blocked" class="help-section help-card p-6" data-section>
            <h2 class="text-2xl font-display font-black text-brand-dark mb-3">F. Why my account was deleted or blocked</h2>
            <p class="text-brand-dark/70 font-medium">
              Accounts can be restricted to protect the community and platform integrity.
            </p>
            <ul class="mt-4 space-y-2 text-sm text-brand-dark/70 font-medium list-disc pl-5">
              <li>IP blacklisting or suspicious access patterns.</li>
              <li>Trial abuse or repeated policy violations.</li>
              <li>Uploading nudity or unwanted content.</li>
              <li>Breaking community rules or terms of use.</li>
            </ul>
          </section>

          <section id="student-discounts" class="help-section help-card p-6" data-section>
            <h2 class="text-2xl font-display font-black text-brand-dark mb-3">G. School and university student support</h2>
            <p class="text-brand-dark/70 font-medium">
              Special discounts or education-focused programs are available for Sri Lankan students and schools.
            </p>
            <p class="text-sm text-brand-dark/70 mt-2">
              Contact <a class="text-brand-red font-bold" href="mailto:contact@apilageai.lk">contact@apilageai.lk</a> to learn more.
            </p>
          </section>

          <section id="volunteer-invest" class="help-section help-card p-6" data-section>
            <h2 class="text-2xl font-display font-black text-brand-dark mb-3">H. Volunteering, jobs, and investing</h2>
            <p class="text-brand-dark/70 font-medium">
              Investing or funding opportunities are open, and volunteering or job roles are available.
            </p>
            <ul class="mt-4 space-y-2 text-sm text-brand-dark/70 font-medium list-disc pl-5">
              <li>Web development and engineering roles.</li>
              <li>Graphic design and creative roles.</li>
              <li>Student ambassadors (Sri Lankan only).</li>
            </ul>
          </section>

          <section id="data-sources" class="help-section help-card p-6" data-section>
            <h2 class="text-2xl font-display font-black text-brand-dark mb-3">I. Data sources used by ApilageAI</h2>
            <p class="text-brand-dark/70 font-medium">
              We prioritize Sri Lankan education sources to keep answers aligned with local curriculum.
            </p>
            <ul class="mt-4 space-y-2 text-sm text-brand-dark/70 font-medium list-disc pl-5">
              <li>Government-supported websites and official education portals.</li>
              <li>Educational YouTube channels and trusted learning platforms.</li>
              <li>Textbooks, newly released teacher guides, and curriculum modules.</li>
            </ul>
          </section>

          <section id="bugs-errors" class="help-section help-card p-6" data-section>
            <h2 class="text-2xl font-display font-black text-brand-dark mb-3">J. Bugs and errors</h2>
            <p class="text-brand-dark/70 font-medium">
              You can report bugs or errors using the error report icon on the chat page.
            </p>
          </section>

          <section class="help-section help-card help-callout p-6">
            <h2 class="text-2xl font-display font-black text-brand-dark mb-3">Need more help?</h2>
            <p class="text-brand-dark/80 font-medium mb-4">
              If you still have questions, reach out to the ApilageAI team and we will guide you.
            </p>
            <div class="flex flex-wrap gap-4">
              <a href="mailto:contact@apilageai.lk" class="btn-primary !px-6 !py-3">Email Support</a>
              <a href="{$smarty.const.APP_URL}/app" class="btn-primary bg-brand-dark text-white hover:bg-brand-dark/90 !px-6 !py-3">Start Chat</a>
            </div>
          </section>
        </div>
      </div>
    </section>

    <section class="container mx-auto px-6">
      <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "FAQPage",
          "mainEntity": [
            {
              "@type": "Question",
              "name": "How do I create an ApilageAI account?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "Visit the register page, enter your name, email, and password, then accept the terms to create a free account."
              }
            },
            {
              "@type": "Question",
              "name": "What can I use ApilageAI for?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "ApilageAI supports Sri Lankan syllabus-aligned studies, daily tasks, image generation, mind maps, visual graphs, coding help, and MCQ practice."
              }
            },
            {
              "@type": "Question",
              "name": "What is the difference between Free and Pro?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "The free version offers limited credits. Pro uses pay-as-you-go pricing in LKR with a Sri Lankan bank approved secure payment gateway."
              }
            },
            {
              "@type": "Question",
              "name": "How do I delete my account?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "Use account settings to request deletion or contact support for assistance."
              }
            },
            {
              "@type": "Question",
              "name": "How can I report bugs or errors?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "Report issues using the error report icon on the chat page."
              }
            }
          ]
        }
      </script>
    </section>
  </main>

<script>
  const topicInput = document.getElementById('topicSearch');
  const topicItems = Array.from(document.querySelectorAll('[data-topic-item]'));
  const topicStatus = document.getElementById('topicSearchStatus');
  const topicEmpty = document.getElementById('topicEmptyState');
  const sections = Array.from(document.querySelectorAll('[data-section]'));

  const updateStatus = (count, query) => {
    if (!topicStatus) return;
    if (query) {
      topicStatus.textContent = 'Found ' + count + ' sector' + (count === 1 ? '' : 's') + ' for "' + query + '".';
    } else {
      topicStatus.textContent = 'Showing ' + count + ' sectors.';
    }
  };

  const filterTopics = (query) => {
    const q = (query || '').trim().toLowerCase();
    let visible = 0;
    topicItems.forEach((item) => {
      const haystack = (item.dataset.topic || '').toLowerCase();
      const match = !q || haystack.includes(q);
      item.classList.toggle('hidden', !match);
      if (match) visible += 1;
    });
    if (topicEmpty) {
      topicEmpty.classList.toggle('hidden', visible !== 0);
    }
    updateStatus(visible, q);
  };

  if (topicInput) {
    topicInput.addEventListener('input', (event) => filterTopics(event.target.value));
  }

  topicItems.forEach((item) => {
    item.addEventListener('click', (event) => {
      const target = item.getAttribute('href');
      if (!target) return;
      const section = document.querySelector(target);
      if (section) {
        event.preventDefault();
        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        history.replaceState(null, '', target);
      }
    });
  });

  const setActiveTopic = (id) => {
    topicItems.forEach((item) => {
      const isActive = item.getAttribute('href') === ('#' + id);
      item.classList.toggle('active', isActive);
    });
  };

  if ('IntersectionObserver' in window && sections.length) {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            setActiveTopic(entry.target.id);
          }
        });
      },
      { rootMargin: '-30% 0px -60% 0px' }
    );
    sections.forEach((section) => observer.observe(section));
  }

  updateStatus(topicItems.length, '');
</script>

  {include file="components/footer.tpl"}
