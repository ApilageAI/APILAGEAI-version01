{include file="components/head.tpl"}
<script src="https://cdn.socket.io/4.8.1/socket.io.min.js" integrity="sha384-mkQ3/7FUtcGyoppY6bz/PORYoGqOl7/aSUMn2ymDOJcapfS6PHqxhRTMh1RR0Q6+" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>

<div id="app-loading-overlay" class="app-loading-overlay" aria-hidden="false">
    <div class="app-loading-card">
        <img class="app-loading-logo" src="{$smarty.const.APP_URL}/assets/images/icon.png" alt="Apilageai logo" />
    </div>
</div>

{if $is_guest}
<div id="guest-welcome-lightbox" class="guest-welcome-lightbox" aria-hidden="true">
    <div class="guest-welcome-card" role="dialog" aria-modal="true" aria-labelledby="guestWelcomeTitle">
        <div class="guest-welcome-media">
            <img src="{$smarty.const.APP_URL}/assets/images/guestuser.jpg" alt="Guest welcome" />
        </div>
        <div class="guest-welcome-content">
            <h3 id="guestWelcomeTitle">Welcome to Apilageai</h3>
            <p>To upload images, PDFs, generate graphs, or use memory, you need to log in.</p>
            <div class="guest-welcome-actions">
                <a class="guest-welcome-login" href="{$smarty.const.APP_URL}/auth/login">Log in</a>
                <button id="guest-continue-btn" class="guest-welcome-continue" type="button">No continue</button>
            </div>
        </div>
    </div>
</div>
{/if}

<div id="streak-checkin-overlay" class="streak-checkin-overlay" aria-hidden="true">
    <div class="streak-checkin-card" role="dialog" aria-modal="true" aria-labelledby="streakCheckinTitle">
        <div class="streak-checkin-header">
            <h3 id="streakCheckinTitle">Continue your learning streak</h3>
            <p>How much time did you study today?</p>
        </div>
        <div class="streak-checkin-time">
            <div class="streak-input">
                <label for="streakHours">Hours</label>
                <input id="streakHours" type="number" min="0" max="23" placeholder="0">
            </div>
            <div class="streak-input">
                <label for="streakMinutes">Minutes</label>
                <input id="streakMinutes" type="number" min="0" max="59" placeholder="0">
            </div>
        </div>
        <div class="streak-input">
            <label for="streakSummary">What did you learn?</label>
            <textarea id="streakSummary" rows="3" maxlength="140" placeholder="Short note about what you studied"></textarea>
        </div>
        <div id="streakCheckinMessage" class="streak-checkin-message" aria-live="polite"></div>
        <div class="streak-checkin-actions">
            <button id="streakSubmitBtn" class="btn btn-primary" type="button">Submit &amp; Continue Streak</button>
            <button id="streakEndBtn" class="btn btn-secondary" type="button">End Streak</button>
        </div>
    </div>
</div>

<div class="app-container">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header" style="display: flex; align-items: center; justify-content: center; position: relative;">
            <img class="sidebar-logo" src="{$smarty.const.APP_URL}/assets/images/icon.png" alt="Apilageai logo" style="width: 44px; height: 44px; object-fit: contain;" />
            <button id="sidebarback" class="sidebar-backn" aria-label="Open sidebar" style="position: absolute; right: 0;">
                <i class="fa fa-chevron-left" aria-hidden="true"></i>
            </button>
        </div>

    <div class="sidebar-items" style="padding: 16px; display: flex; flex-direction: column; gap: 12px;">
        <button class="sidebar-but new-chat-btn" id="sidebar-new-chat" type="button" title="New chat">
            <span class="sidebar-but-icon" aria-hidden="true">
                <picture>
                    <source srcset="https://fonts.gstatic.com/s/e/notoemoji/latest/270f_fe0f/512.webp" type="image/webp">
                    <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/270f_fe0f/512.gif" alt="✏" width="20" height="20">
                </picture>
            </span>
            <span class="sidebar-but-text">New chat</span>
            <span class="sidebar-but-shortcut" aria-hidden="true">⇧⌘O</span>
        </button>
        <button class="sidebar-but" id="open-conversation-gallery" type="button" title="Conversations">
            <span class="sidebar-but-icon" aria-hidden="true">💬</span>
            <span class="sidebar-but-text">Conversations</span>
            <span class="sidebar-but-shortcut" aria-hidden="true">⇧⌘K</span>
        </button>
        <button class="sidebar-but" id="open-share-modal" type="button" title="Share with friends">
            <span class="sidebar-but-icon" aria-hidden="true">
                <picture>
                    <source srcset="https://fonts.gstatic.com/s/e/notoemoji/latest/1f680/512.webp" type="image/webp">
                    <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f680/512.gif" alt="🚀" width="20" height="20">
                </picture>
            </span>
            <span class="sidebar-but-text">Share with friends</span>
            <span class="sidebar-but-shortcut" aria-hidden="true">⇧⌘S</span>
        </button>

        <!-- Collaborative voice mic (shown only for shared chats) -->
        <button class="sidebar-but" id="collab-mic-toggle" type="button" style="display:none;" title="Mic">
            <span class="sidebar-but-icon"><i class="fa fa-microphone-slash"></i></span>
            <span class="sidebar-but-text">Mic</span>
        </button>
        <button class="sidebar-but" id="mindmap-open-btn" type="button" title="Mind map">
            <span class="sidebar-but-icon" aria-hidden="true">🧠</span>
            <span class="sidebar-but-text">Mind map</span>
        </button>
        <button class="sidebar-but" id="mcqblust-gameyard-icon" type="button" title="MCQ game">
            <span class="sidebar-but-icon" aria-hidden="true">🎮</span>
            <span class="sidebar-but-text">MCQ game</span>
        </button>
        <button class="sidebar-but" type="button" onclick="window.open('{$smarty.const.APP_URL}/explore', '_self');" title="Explore">
            <span class="sidebar-but-icon" aria-hidden="true">
                <picture>
                    <source srcset="https://fonts.gstatic.com/s/e/notoemoji/latest/1f30e/512.webp" type="image/webp">
                    <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f30e/512.gif" alt="🌎" width="20" height="20">
                </picture>
            </span>
            <span class="sidebar-but-text">Explore</span>
        </button>
        {assign var=profileSlug value=$user->_data.public_profile_username}
        {if !$profileSlug}
            {assign var=profileSlug value=$user->_data.public_profile_token}
        {/if}
        <button class="sidebar-but" type="button" onclick="window.open('{$smarty.const.APP_URL}/{$profileSlug|default:''}', '_self');" title="Public Profile">
            <span class="sidebar-but-icon" aria-hidden="true">👤</span>
            <span class="sidebar-but-text">Public Profile</span>
        </button>
    </div>

 <!-- Sidebar Footer User Info -->
<div class="sidebar-footer">
    <button id="sidebarMinimize" class="sidebar-minimize-btn" aria-label="Minimize sidebar" style="display: none;">
        <i class="fa fa-bullseye" aria-hidden="true"></i>
        <span class="minimize-text">Focused</span>
    </button>
  <div class="sidebar-footer-userinfo" id="sidebarUserInfo" title="Open settings">
    <div class="user-avatar">
      <img
        src="{$user->_data.image|user_image_url}"
        alt="{$user->_data.first_name|default:'Guest'} Avatar"
        onerror="this.onerror=null;this.src='{$smarty.const.APP_URL}/assets/images/user.png';"
      />
    </div>
    <div class="user-details">
      <div class="user-name" id="sidebar-user-name">{$user->_data['first_name']|default:'Guest'}</div>
      <div class="user-credit-text" id="sidebar-credit-text">Credit: Loading...</div>
      <div class="credit-bar-container">
        <div class="credit-bar-fill" id="sidebar-credit-bar" style="width: 0%;"></div>
      </div>
    </div>
  </div>
</div>

</aside>
    <!-- sidebar end -->

    <!-- sidebar for notes -->

    <div id="rightsidebar2" class="sidebar2 canvas-sidebar whiteboard-sidebar" aria-hidden="true">

        <!-- Whiteboard Header -->
        <div class="sidebar-header canvas-header">
            <h3>🧩 Whiteboard</h3>
            <div class="canvas-header-actions">
                <button id="canvas-fullscreen-btn" type="button" class="canvas-header-btn" title="Fullscreen"><i class="fa-solid fa-expand"></i></button>
                <button id="canvas-close-btn" type="button" class="canvas-close-btn" title="Close">&times;</button>
            </div>
        </div>

        <!-- Whiteboard container -->
        <div class="wt-wrapper">
            <div id="wt-container" class="wt-container" aria-label="Whiteboard" role="application"></div>
            <div id="wt-status" class="wt-status">Loading whiteboard…</div>
        </div>

    </div>
<!-- Whiteboard sidebar END -->

    
  <!-- Desmos Graphing Sidebar -->
    <aside class="right-sidebar" id="rightSidebar">
        <div class="right-sidebar-header">
            <h4>අපිලගේ Graph Calculator</h4>
            <button class="right-sidebar-close" id="closeRightSidebar">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Desmos Graph Display -->
        <div class="desmos-container" id="desmos-graph" style="height: 600px;"></div>

<!-- Input box -->
        <div class="graph-input-container">
            <input type="text" class="graph-input" id="graphFunctionInput" name="graph_expression" placeholder="Enter custom expression" autocomplete="off" aria-label="Graph expression">
            <button class="graph-submit" id="graphFunctionSubmit"><i class="fas fa-chart-line"></i></button>
        </div>


        <!-- Controls -->
        <div class="graph-controls mt-3">
            <button class="btn btn-sm btn-warning" id="resetGraph"><i class="fas fa-rotate-right"></i> Size</button>
            <button class="btn btn-sm btn-danger" id="clearAllGraphs"><i class="fas fa-trash"></i> All</button>
        </div>
    </aside>
    <!-- DESMOS GRAPH -->

    <!-- modal lightbox named talkingassit as requested -->
  <div id="talkingassit" class="modal hidden" aria-hidden="true">
    <div class="panel" role="dialog" aria-labelledby="ta-title">
      <h3 id="ta-title">Apilageai talkingassit</h3>
      <div class="status" id="talk-status">නැවත සක්‍රීයයි</div>

      <div class="mic-visual" id="mic-visual">
        <div class="bar"></div>
        <div class="bar"></div>
        <div class="bar"></div>
        <div class="bar"></div>
        <div class="bar"></div>
      </div>

      <div class="transcript" id="transcript">කතා අසනවා...</div>

      <button id="cut-call" class="cut-btn">කතා අවසන් කරන්න</button>

      <!-- hidden audio element to attach remote audio track so browser can play assistant voice -->
      <audio id="assistant-audio" autoplay playsinline class="hidden"></audio>

    </div>
  </div>
    <!-- Onboarding Lightbox -->

    <div id="onboarding-lightbox" class="onboard-lightbox">
        <div class="onboard-card">
            
            <form id="onboarding-form" class="onboard-steps-container">
                <input type="hidden" name="interests" id="interests-hidden-input">
                <input type="hidden" name="preference" id="preference-hidden-input">

                <!-- Step 1: School -->
                <div id="step-0" class="onboard-step">
                    <div class="onboard-icon-wrapper"><i class="fas fa-rocket onboard-icon"></i></div>
                    <h2>Welcome to අපිලගේ AI</h2>
                    <p>Let's personalize your AI experience in a few simple steps to get you started.</p>
                    <input name="school" id="school-input" type="text" class="onboard-input" list="school-list" autocomplete="off" placeholder="ඔයාගේ School එක හෝ University එක?">
                    <div style="font-size: 12px; color: var(--text-secondary); margin-top: 6px;">
                        Search and select your school from the list.
                    </div>
                    <div class="onboard-checkbox-container">
                        <input id="not-student-checkbox" type="checkbox" name="not_student">
                        <label for="not-student-checkbox">මම student කෙනක් නෙමයි</label>
                    </div>
                </div>

                <!-- Step 2: Focused Areas -->
                <div id="step-1" class="onboard-step">
                     <div class="onboard-icon-wrapper"><i class="fas fa-crosshairs onboard-icon"></i></div>
                    <h2>What are your interests?</h2>
                    <p id="focus-area-subtitle">ඔයා වැඩිපුර AI පාවිච්චි කරන්නේ මොන වගේ දේවල් වලටද?</p>
                    <div class="onboard-focus-grid">
                        <div class="onboard-focus-card" data-interest="science"><div class="onboard-icon-wrapper"><i class="fas fa-flask onboard-icon"></i></div><p>Science</p></div>
                        <div class="onboard-focus-card" data-interest="life"><div class="onboard-icon-wrapper"><i class="fas fa-heart-pulse onboard-icon"></i></div><p>Life</p></div>
                        <div class="onboard-focus-card" data-interest="maths"><div class="onboard-icon-wrapper"><i class="fas fa-calculator onboard-icon"></i></div><p>Maths</p></div>
                        <div class="onboard-focus-card" data-interest="art"><div class="onboard-icon-wrapper"><i class="fas fa-palette onboard-icon"></i></div><p>Art</p></div>
                        <div class="onboard-focus-card" data-interest="business"><div class="onboard-icon-wrapper"><i class="fas fa-briefcase onboard-icon"></i></div><p>Business</p></div>
                        <div class="onboard-focus-card" data-interest="Coding"><div class="onboard-icon-wrapper"><i class="fa-solid fa-code onboard-icon"></i></div><p>Coding</p></div>
                    </div>
                </div>

                <!-- Step 3: AI Preference -->
                <div id="step-2" class="onboard-step">
                    <div class="onboard-icon-wrapper"><i class="fas fa-robot onboard-icon"></i></div>
                    <h2>AI Personality</h2>
                    <p>අපිලගේ AI මොනවගේද ඔයාත් එක්ක කතා කරන්න ඕන?</p>
                    <div class="onboard-preference-list">
                        <div class="onboard-preference-card" data-preference="friendly"><i class="fas fa-hand-holding-heart onboard-icon"></i><div><h3>Friendly & Casual</h3><p>Engaging and conversational.</p></div></div>
                        <div class="onboard-preference-card" data-preference="educational"><i class="fas fa-book-open onboard-icon"></i><div><h3>Informative</h3><p>Knowledgeable and fact-based.</p></div></div>
                        <div class="onboard-preference-card" data-preference="explanatory"><i class="fas fa-magnifying-glass-chart onboard-icon"></i><div><h3>Detailed</h3><p>Breaks down complex topics.</p></div></div>
                        <div class="onboard-preference-card" data-preference="concise"><i class="fas fa-bolt onboard-icon"></i><div><h3>To the Point</h3><p>Brief and direct.</p></div></div>
                    </div>
                </div>

                <!-- Step 4: All Ready -->
                <div id="step-3" class="onboard-step">
                    <div class="onboard-icon-wrapper"><i class="fas fa-check onboard-icon"></i></div>
                    <h2>ඔක්කොම හරි මෙන්න ඔයාටම ගැලපෙන අපිලගේ AI</h2>
                    <p>Apilage AI එක්ක Chat කරන්න පටන් ගන්න මෙන්න අහන්න දේවල් කිහිපයක්</p>
                    <div class="onboard-prompt-list">
                        <div class="onboard-prompt-example">"මට මේ පාර term test එකේ ළකුණු වැඩි කරගන්න ක්‍රමයක් කියන්න."</div>
                        <div class="onboard-prompt-example">"මට සිංහල O/L syllabus එකේ සංධි ටික කෙටි සටහනක් දෙන්න"</div>
                        <div class="onboard-prompt-example">"මගේ ඉස්කෝලේ grade 12 , Physics past papers වල වැඩිපුර මොනවද අහලා තියෙන්නේ?"</div>
                    </div>
                </div>
            </form>

            <div class="onboard-footer">
                <button id="back-btn" class="onboard-button onboard-back-btn invisible">Back</button>
                <div class="onboard-footer-center">
                    <div id="error-message" class="onboard-error-message"></div>
                    <div class="onboard-progress-dots">
                        <div id="dot-0" class="onboard-progress-dot"></div>
                        <div id="dot-1" class="onboard-progress-dot"></div>
                        <div id="dot-2" class="onboard-progress-dot"></div>
                        <div id="dot-3" class="onboard-progress-dot"></div>
                    </div>
                </div>
                <button id="next-btn" class="onboard-button onboard-next-btn">Next</button>
                <button id="continue-btn" class="onboard-button onboard-continue-btn hidden">Continue</button>
            </div>
        </div>
    </div>
    <datalist id="school-list"></datalist>


     <!-- MInd map-->
       <div id="mindmap-lightbox" class="mindmap-lightbox-overlay">
        <div class="mindmap-lightbox-content">
            <span id="mindmap-close-btn" class="mindmap-close-btn">&times;</span>
            
            <div class="mindmap-left-panel">
                <h3>Apilage Mind Map</h3>
                <p>Describe topic with what you wanna add !</p>
                <textarea id="mindmap-input" placeholder="උදාහරණයක් ලෙස: ශ්වසන පද්ධතියේ ක්‍රියාකාරීත්වය....රෝග, රෝග වලින් වැළකෙන ආකාරය , පද්ධතියේ විවිධ කොටස්"></textarea>
                <button id="mindmap-go-btn" class="mindmap-button">Map your mind</button>

                <div class="ai-assistant">
                    <h3>Ask Apilageai to</h3>
                    <p>type a general instruction to develop your mind map further.</p>
                    <textarea id="mindmap-develop-input" rows="3" placeholder="e.g., තවත් වැඩිදුර විස්තර එකතු කරන්න , ශ්වසන පද්ධතියේ ආසාත්මිකතා දක්වන්න"></textarea>
                    <button id="mindmap-develop-btn" class="mindmap-button">Edit mind map</button>
                </div>
            </div>

            <div id="mindmap-right-panel" class="mindmap-right-panel">
                <div class="mindmap-toolbar">
                    <button id="add-node-btn" title="Add Node"><i class="fa-solid fa-plus"></i></button>
                    <button id="add-text-btn" title="Add Text"><i class="fa-solid fa-font"></i></button>
                    <button id="connect-node-btn" title="Connect Nodes"><i class="fa-solid fa-link"></i></button>
                    <button id="zoom-in-btn" title="Zoom In"><i class="fa-solid fa-magnifying-glass-plus"></i></button>
                    <button id="zoom-out-btn" title="Zoom Out"><i class="fa-solid fa-magnifying-glass-minus"></i></button>
                    <button id="fullscreen-btn" title="Fullscreen"><i class="fa-solid fa-expand"></i></button>
                    <button id="save-png-btn" title="Save as PNG"><i class="fa-solid fa-image"></i></button>
                </div>
                <div id="mindmap-visualizer">
                    <div id="mindmap-canvas">
                        <!-- Nodes and SVG layer will be appended here -->
                    </div>
                </div>
                <div id="mindmap-loader" class="mindmap-loader" style="display: none;">
                    <div class="mindmap-spinner"></div>
                    <p>Visualizing your ideas...</p>
                </div>
                <div class="help-text">
                    Right-click a node for AI actions | Click a connector + <kbd>Delete</kbd> to remove
                </div>
            </div>
        </div>
    </div>
    
    <div id="context-menu">
        <div class="context-menu-item" id="ctx-add-subnodes">Add Sub-Nodes with AI</div>
    </div>
    <!-- MInd map end-->

<!-- Use setting-->

 <div class="preferencebox-overlay" id="preferenceboxOverlay">
        <div class="preferencebox" id="preferencebox">
            <button class="preferencebox-close-btn" id="preferenceboxCloseBtn" title="Close settings">&times;</button>
            
            <!-- Sidebar Navigation -->
            <aside class="preferencebox-sidebar">
                <nav>
                    <ul>
                        <li><a href="#" class="preferencebox-tab-link active" data-tab="general"><i class="fa fa-cog"></i> General</a></li>
                        {if !$is_guest}
                        <li><a href="#" class="preferencebox-tab-link" data-tab="public-profile"><i class="fa fa-user-circle"></i> Public Profile</a></li>
                        {/if}
                        <li><a href="#" class="preferencebox-tab-link" data-tab="ai"><i class="fa fa-robot"></i>Preference</a></li>
                        <li><a href="#" class="preferencebox-tab-link" data-tab="student-verification"><i class="fa fa-graduation-cap"></i> Student Verification</a></li>
                        <li><a href="#" class="preferencebox-tab-link" data-tab="billing"><i class="fa fa-credit-card"></i> Billing</a></li>
                        <li><a href="#" class="preferencebox-tab-link" data-tab="app"><i class="fa fa-cogs"></i> Account</a></li>
                    </ul>
                </nav>
            </aside>

            <!-- Main Content Area -->
            <main class="preferencebox-content">
                <!-- General Tab Content -->
                <div id="general" class="preferencebox-tab-content active">
                    <h2>General Settings</h2>
                    <div class="form-section">
                        <h3>Profile</h3>
                        <div class="profile-photo-section">
                            <img
                              id="profilePhoto"
                              src="{$user->_data.image|user_image_url}"
                              alt="{$user->_data.first_name|default:'Guest'} Avatar"
                              onerror="this.onerror=null;this.src='{$smarty.const.APP_URL}/assets/images/user.png';"
                            />
                            <div>
                                <input type="file" id="profilePhotoInput" accept="image/*" style="display:none;">
                                <button class="btn btn-primary" id="changePhotoBtn">Change Photo</button>
                                <span id="profileLoader" style="display:none; margin-left: 8px; font-size: 12px; color: var(--text-secondary);" aria-live="polite">Uploading...</span>
                                <p style="font-size: 12px; color: var(--text-secondary); margin-top: 8px;">JPG, GIF or PNG. 1MB max.</p>
                            </div>
                        </div>
                    </div>
                    <div class="form-section">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="firstName">First Name</label>
                                <input type="text" id="firstName" value="{$user->_data['first_name']|default:'Guest'}">
                            </div>
                            <div class="form-group">
                                <label for="lastName">Last Name</label>
                                <input type="text" id="lastName" value="{$user->_data['last_name']}">
                            </div>
                        </div>
                    </div>
                     <div class="form-section">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" value="{$user->_data['email']}">
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" value="{$user->_data['phone']}">
                            </div>
                        </div>
                    </div>
                    <div class="form-section">
                        <h3>Password</h3>
                        <button class="btn btn-secondary" id="changePasswordBtn">Change Password</button>
                        <div id="passwordAlertBox" style="display:none; margin-top:10px;"></div>
                    </div>
                    <div class="form-section">
                        <h3>Connected Profiles</h3>
                        <div class="connected-profiles">
                            <a href="#" id="connectGoogleBtn"><i class="fab fa-google" style="color:#DB4437;"></i> Connect with Google</a>
                            <span id="googleConnectedBadge" style="display:none; font-size: 12px; color: var(--text-secondary); margin-left: 8px;">Connected</span>
                        </div>
                    </div>
                <div class="form-section">
                  <button class="btn btn-primary" id="saveGeneralBtn">Save Changes</button>
                <div id="generalAlertBox" style="display:none; margin-top:10px;"></div>
                </div>
                </div>

                {if !$is_guest}
                <!-- Public Profile Tab Content -->
                <div id="public-profile" class="preferencebox-tab-content">
                    <h2>Public Profile</h2>
                    <div class="form-section">
                        <h3>Profile Link</h3>
                        <div class="form-group">
                            <label for="publicProfileUrl">Public Profile URL</label>
                            <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                                <input type="text" id="publicProfileUrl" readonly style="flex:1; min-width:220px;">
                                <a id="seePublicProfileBtn" class="btn btn-secondary" href="#" target="_blank" rel="noopener">See Public Profile</a>
                                <button class="btn btn-secondary" id="copyPublicProfileLinkBtn" type="button">Copy Link</button>
                            </div>
                            <div style="font-size: 12px; color: var(--text-secondary); margin-top: 6px;">
                                Default link uses a random token until you set a username.
                            </div>
                        </div>
                    </div>
                    <div class="form-section">
                        <h3>Username</h3>
                        <div class="form-group">
                            <label for="publicProfileUsername">User name</label>
                            <input type="text" id="publicProfileUsername" placeholder="yourname">
                            <div style="font-size: 12px; color: var(--text-secondary); margin-top: 4px;">
                                No spaces. Letters, numbers, underscores, and dashes only. Must be unique.
                            </div>
                        </div>
                    </div>
                    <div class="form-section">
                        <h3>Visibility</h3>
                        <div class="onboard-checkbox-container" style="margin-bottom: 12px;">
                            <input id="closePublicProfile" type="checkbox" name="close_public_profile">
                            <label for="closePublicProfile">Close public profile</label>
                        </div>
                    </div>
                    <div class="form-section">
                        <h3>Learning Streak</h3>
                        <div class="streak-about">
                            <h4>About Streaks</h4>
                            <p>If you fail to log in to Apilageai every day, your streak is canceled and 50 credits are reduced.</p>
                            <p>Streak is a challenge that tracks daily study progress. Students log what they studied and hours spent, earning badges like Gold, Silver, and Diamond for consistency.</p>
                            <p>Badges can convert to credits after proven success. The system also predicts results, analyzes performance, finds weak areas, and creates personalized study plans.</p>
                        </div>
                        <div class="form-group">
                            <label for="learningStreakName">Streak name</label>
                            <input type="text" id="learningStreakName" placeholder="e.g., Exam prep">
                            <div class="streak-help">Give your streak a short name to show in your profile.</div>
                        </div>
                        <div class="streak-actions">
                            <button class="btn btn-secondary" id="startLearningStreakBtn" type="button">Start Learning Streak</button>
                            <button class="btn btn-secondary" id="endLearningStreakBtn" type="button" style="display:none;">End Streak</button>
                            <span id="learningStreakStatus" style="font-size: 12px; color: var(--text-secondary);"></span>
                        </div>
                        <div id="learningStreakActive" class="streak-active-card" style="display:none;"></div>
                        <div id="learningStreakHistory" class="streak-history" style="display:none;">
                            <h4>Streak History</h4>
                            <ul id="learningStreakHistoryList" class="streak-history-list"></ul>
                        </div>
                        <div id="learningStreakBadges" class="streak-badges"></div>
                    </div>
                    <div class="form-section">
                        <button class="btn btn-primary" id="savePublicProfileBtn" type="button">Save Public Profile</button>
                        <div id="publicProfileAlertBox" style="display:none; margin-top:10px;"></div>
                    </div>
                </div>
                {/if}

                <!-- AI Preference Tab Content -->
                <div id="ai" class="preferencebox-tab-content">
                    <h2>Preference</h2>
                    <div class="form-section">
                        <div class="form-group">
                            <label>Interested Subjects</label>
                                                        <input type="text" id="subjectInput" placeholder="e.g., Maths, Science" aria-describedby="subjectHelpText">
                                                        <div id="subjectHelpText" style="font-size: 12px; color: var(--text-secondary); margin-top: 4px;">
                                                            Use only text, spaces, and commas. Max 5 values.
                                                        </div>
                        </div>
                    </div>
                    <div class="form-section">
                         <div class="form-group">
                            <label>AI Tone</label>
                            <div class="radio-group" id="aiToneRadios">
                              <label><input type="radio" name="aiTone" value="friendly"> Friendly and casual</label><br>
                              <label><input type="radio" name="aiTone" value="educational"> Informative</label><br>
                              <label><input type="radio" name="aiTone" value="explanatory"> Detailed</label><br>
                              <label><input type="radio" name="aiTone" value="concise"> To the point</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-section">
                        <h3>Memory</h3>
                        <div id="currentMemoryBox" class="memory-box">
                          Loading memory...
                        </div>
                        <p style="font-size: 14px; color: var(--text-secondary); margin-top: -10px; margin-bottom: 16px;">This will clear the AI's memory of past conversations.</p>
                        <button class="btn btn-secondary" id="clearMemoryBtn">Clear Memory</button>
                    </div>
                <div class="form-section">
                  <button class="btn btn-primary" id="savePreferenceBtn">Save Preferences</button>
                <div id="preferenceAlertBox" style="display:none; margin-top:10px;"></div>
                </div>
                </div>

                <!-- Student Verification Tab Content -->
                <div id="student-verification" class="preferencebox-tab-content">
                    <h2>Student Verification</h2>
                    <div class="form-section">
                        <div class="form-group" style="margin-bottom: 16px;">
                            <label for="schoolInput">School / Institute</label>
                            <input type="text" id="schoolInput" list="school-list" autocomplete="off" placeholder="Search your school from the list">
                            <div style="font-size: 12px; color: var(--text-secondary); margin-top: 4px;">
                                Select from the list. Either choose a school or check 'Not a student', not both.
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="onboard-checkbox-container" style="margin-bottom: 12px;">
                                <input id="notStudentInput" type="checkbox" name="not_student">
                                <label for="notStudentInput">මම student කෙනක් නෙමයි</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-section">
                        <button class="btn btn-primary" id="saveStudentVerificationBtn" type="button">Save Student Verification</button>
                        <div id="studentVerificationAlertBox" style="display:none; margin-top:10px;"></div>
                    </div>
                    <div class="form-section">
                        <h3>Verify School Email</h3>
                        <button class="btn btn-secondary" id="verifySchoolEmailBtn" type="button" disabled>
                            <i class="fa fa-envelope"></i> Verify school student email (Dummy)
                        </button>
                        <div style="font-size: 12px; color: var(--text-secondary); margin-top: 6px;">Coming soon.</div>
                    </div>
                </div>

                <!-- Billing Tab Content -->
                <div id="billing" class="preferencebox-tab-content">
                    <h2>Billing</h2>
                    <div class="form-section">
                        <h3>Pay as you go</h3>
                        <div class="price-slider-container">
                            <label for="priceRange">Select Amount (Rs.)</label>
                             <div class="price-display" id="priceDisplay">Rs. 5000</div>
                            <input type="range" min="100" max="20000" value="5000" class="slider" id="priceRange">
                            <button class="btn btn-primary" id="payButton">Pay Rs. 5000</button>
                        </div>
                    </div>
                     <div class="form-section">
                        <h3>Billing History</h3>
                        <div class="table-scroll-container">
                            <table class="billing-history-table">
                                <thead>
                                  <tr>
                                    <th>Invoice ID</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Receipt</th>
                                  </tr>
                                </thead>
                                <tbody id="billingHistoryBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- App Setting Tab Content -->
                <div id="app" class="preferencebox-tab-content">
                    <h2>Account</h2>
                    <div class="form-section">
                        <div class="setting-item">
                            <span>Theme</span>
                            <a href="#" id="theme-toggle" class="theme-toggle-btn btn btn-secondary" role="button">
                                <span class="light-icon">Light Mode</span>
                                <span class="dark-icon" style="color: white;">Dark Mode</span>
                            </a>
                        </div>
                        <div class="setting-item">
                           <span>Bug Report</span>
                           <a href="#" id="reportBugLink" class="btn btn-secondary"><i class="fa fa-bug"></i> Report Bug</a>
                        </div>
                        <div class="setting-item">
                            <span>Contact Support</span>
                            <a href="#" class="btn btn-secondary"><i class="fa fa-headset"></i> Contact Support</a>
                        </div>
                         <div class="setting-item">
                            <span>Account</span>
                            <a href="{$smarty.const.APP_URL}/auth/signout" class="btn btn-secondary"><i class="fa fa-sign-out-alt"></i> Log Out</a>
                        </div>
                    </div>
                    <div class="danger-zone">
                        <h3>Danger Zone</h3>
                        <p>Once you delete your account, there is no going back. Please be certain.</p>
                        <button class="btn btn-danger" id="deleteAccountBtn">Delete My Account</button>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <div id="deleteAccountModal" class="delete-account-modal">
        <div class="delete-account-dialog">
            <h3 class="delete-account-title">Delete Account</h3>
            <p class="delete-account-message">
                When deleting your account, your data and all chats, plus your remaining credit balance, will be deleted and can’t be undone, recovered, or have any payments returned.
            </p>
            <div class="delete-account-actions">
                <button class="btn btn-secondary" id="cancelDeleteAccountBtn">Cancel</button>
                <button class="btn btn-danger" id="confirmDeleteAccountBtn">Yes, Delete</button>
            </div>
        </div>
    </div>


<!-- Use setting-->



<!-- Gameyard Lightbox start-->
 <!-- Lightbox / Modal -->
    <div id="mcqblust-lightbox" class="mcqblust-lightbox hidden">
        <div id="mcqblust-container">
            <button id="mcqblust-close-btn"><i class="fas fa-times"></i></button>

            <!-- Screen: Language Selection -->
            <div id="mcqblust-screen-language">
                <h2 class="screen-title">Choose Your Language</h2>
                <div class="flex-center-gap">
                    <button class="mcqblust-lang-btn" data-lang="Sinhala">සිංහල</button>
                    <button class="mcqblust-lang-btn" data-lang="English">English</button>
                </div>
            </div>

            <!-- Screen: Game Mode Selection -->
            <div id="mcqblust-screen-mode" class="hidden">
                <h2 class="screen-title">Select Game Mode</h2>
                <div class="flex-center-gap" style="flex-direction: column; align-items: center;">
                    <button id="mcqblust-btn-single-player" class="mode-btn"><i class="fas fa-user"></i> Single Player</button>
                </div>
                 <button class="mcqblust-back-btn" data-target="mcqblust-screen-language"><i class="fas fa-arrow-left"></i> Back</button>
            </div>

            <!-- Screen: Single Player Setup -->
            <div id="mcqblust-screen-single-setup" class="hidden">
                 <h2 class="screen-title">Single Player Setup</h2>
                 <form id="mcqblust-form-single-player" class="form-space-y">
                    <div class="form-grid">
                        <input type="text" id="mcqblust-single-subject" class="form-input" placeholder="Subject (e.g., Science, History)" required>
                        <select id="mcqblust-single-grade" class="form-select" required>
                            <option value="" disabled selected>Select Grade</option>
                        </select>
                        <select id="mcqblust-single-term" class="form-select" required>
                            <option value="1">1st Term</option>
                            <option value="2">2nd Term</option>
                            <option value="3">3rd Term</option>
                        </select>
                         <input type="number" id="mcqblust-single-timer" class="form-input" placeholder="Timer in minutes (optional)">
                    </div>
                    <textarea id="mcqblust-single-focus" class="form-input" rows="2" placeholder="Specific focus areas or units (optional)"></textarea>
                    <div>
                        <label for="mcqblust-single-mcqs">Number of MCQs: <span id="mcqblust-single-mcq-count-label">10</span></label>
                        <input type="range" id="mcqblust-single-mcqs" min="5" max="50" value="10" style="width: 100%;">
                    </div>
                    <div class="form-actions">
                        <button type="button" class="mcqblust-back-btn" data-target="mcqblust-screen-mode"><i class="fas fa-arrow-left"></i> Back</button>
                        <button type="submit" class="btn-primary">Generate Quiz</button>
                    </div>
                </form>
            </div>
            
            <!-- Multiplayer and Challenge screens removed -->

            <!-- Screen: Loading -->
            <div id="mcqblust-screen-loading" class="hidden" style="text-align: center; padding: 3rem 0;">
                <div class="spinner"></div>
                <p style="margin-top: 1.5rem; font-size: 1.125rem; font-weight: 600; color: #374151;">Generating your custom quiz with AI...</p>
                <p style="color: #6b7280;">Please wait a moment.</p>
            </div>
            
            <!-- Screen: Game/MCQ Display -->
            <div id="mcqblust-screen-game" class="hidden">
                <div class="game-header">
                    <div id="mcqblust-game-timer"></div>
                    <div class="question-counter">Question <span id="mcqblust-current-q-num"></span> of <span id="mcqblust-total-q-num"></span></div>
                </div>
                <div id="mcqblust-question-text"></div>
                <div id="mcqblust-options-container" class="options-grid"></div>
                <div class="game-nav">
                    <button id="mcqblust-btn-prev" class="btn-secondary"><i class="fas fa-step-backward"></i> Previous</button>
                    <div>
                        <button id="mcqblust-btn-skip">Skip</button>
                        <button id="mcqblust-btn-next" class="btn-primary">Next <i class="fas fa-arrow-right"></i></button>
                        <button id="mcqblust-btn-submit" class="btn-primary hidden">Submit</button>
                    </div>
                </div>
            </div>

            <!-- Screen: Single Player Results -->
            <div id="mcqblust-screen-results" class="hidden">
                <h2 class="screen-title">Quiz Results</h2>
                <p class="results-header">You scored <span id="mcqblust-final-score" style="color: #4f46e5; font-weight: 700;"></span> out of <span id="mcqblust-total-score" style="color: #4f46e5; font-weight: 700;"></span>!</p>
                <div id="mcqblust-results-summary" class="results-summary-container"></div>
                 <div style="text-align: center; margin-top: 2rem;">
                    <button id="mcqblust-btn-play-again" class="btn-primary">Play Again</button>
                </div>
            </div>

            <!-- Final Multiplayer Results screen removed -->
            <button id="mcqblust-btn-back-to-main" class="btn-secondary" style="display:none;">Back to Main</button>
        </div>
    </div>


    
    <!-- Main Content --> 
    <main class="main-content">
        <!-- Conversation Gallery View -->
<div id="conversation-gallery" class="conversation-gallery" style="display:none;">
    <section class="gallery-hero">
        <div class="gallery-hero-sheen"></div>
        <div class="gallery-hero-content">
            <div class="hero-text">
                <p class="hero-kicker">Conversation gallery</p>
                <h1>Make your studies more productive with APILAGEAI PRO</h1>
                <p class="hero-subtitle">Study alone or study with friends, we are here to help !</p>
            </div>
            <div class="hero-card" aria-hidden="true">
                <div class="hero-card-label">Quick actions</div>
                <ul class="hero-card-list">
                    <li>Publish or unpublish chats</li>
                    <li>Share with collaborators</li>
                    <li>Rename, edit, or delete</li>
                </ul>
            </div>
        </div>
    </section>

    <div class="gallery-controls">
        <div class="gallery-filter-chips" role="tablist" aria-label="Filter conversations">
            <button class="conversation-filter-chip is-active" data-filter="all" role="tab" aria-selected="true" aria-pressed="true">All</button>
            <button class="conversation-filter-chip" data-filter="published" role="tab" aria-selected="false" aria-pressed="false">Published</button>
            <button class="conversation-filter-chip" data-filter="owned" role="tab" aria-selected="false" aria-pressed="false">Owned by me</button>
            <button class="conversation-filter-chip" data-filter="shared-by-me" role="tab" aria-selected="false" aria-pressed="false">Shared by me</button>
            <button class="conversation-filter-chip" data-filter="shared-with-me" role="tab" aria-selected="false" aria-pressed="false">Shared with me</button>
        </div>

        <div class="gallery-search-sort">
            <div class="gallery-search">
                <i class="fa fa-search" aria-hidden="true"></i>
                <input type="text" id="conversation-search-input" name="conversation_search" placeholder="Search with name or keyword…" autocomplete="off" aria-label="Search conversations">
            </div>
            <div class="gallery-sort">
                <label for="conversation-sort-select">Sort by</label>
                <select id="conversation-sort-select" name="conversation_sort" aria-label="Sort conversations">
                    <option value="updated_desc">Latest edited</option>
                    <option value="updated_asc">Oldest edited</option>
                    <option value="created_desc">Latest chats</option>
                    <option value="created_asc">Oldest chats</option>
                </select>
            </div>
        </div>
    </div>

    <div class="gallery-hint">Tip: click a card to open it; use the icons to publish, edit, share, or delete without leaving the gallery.</div>

    <div id="conversation-gallery-grid" class="conversation-gallery-grid"></div>
</div>

    <!-- Share Modal -->
    <div id="share-modal" class="share-modal hidden" role="dialog" aria-labelledby="share-modal-title">
        <div class="share-modal-content">
            <div class="share-modal-header">
                <div>
                    <p class="share-modal-kicker">Share chat</p>
                    <h3 id="share-modal-title">Share with friends</h3>
                </div>
                <button id="share-modal-close" class="share-modal-close" aria-label="Close share modal">&times;</button>
            </div>
            <div class="share-modal-body">
                <p class="share-modal-subtitle">Send this chat to another user. Both of you can keep chatting, edit, or add more collaborators.</p>

                <div class="share-modal-row">
                    <label for="share-search-input">Search users</label>
                    <div class="share-search-box">
                        <i class="fa fa-search"></i>
                        <input id="share-search-input" name="share_search" type="text" placeholder="Type a name or email" autocomplete="off" aria-label="Search users to share" />
                    </div>
                    <div id="share-search-results" class="share-search-results"></div>
                </div>

                <div class="share-modal-row">
                    <div class="share-section-header">Already shared</div>
                    <div id="share-participants" class="share-pill-container"></div>
                </div>

                <div id="share-link-row" class="share-link-row hidden">
                    <input id="share-link-input" name="share_link" type="text" readonly aria-label="Share link" />
                    <button id="copy-share-link-btn" type="button">Copy link</button>
                </div>

                <div class="share-modal-row">
                    <div class="share-section-header">Publish to all users</div>
                    <p class="share-modal-subtitle" style="margin: 8px 0; font-size: 0.9em; color: #666;">Make this chat visible to all Apilageai users. They can read the conversation but cannot edit it.</p>
                    <div id="publish-status-container" style="display: flex; flex-direction: column; gap: 12px;">
                        <div id="publish-info-box" style="padding: 12px; background: #f0f8ff; border-radius: 6px; border-left: 4px solid #2196F3;">
                            <div style="font-size: 0.85em; color: #666;">
                                <span id="publish-status-text">Click below to publish this chat for all users.</span>
                            </div>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <button id="publish-chat-btn" type="button" class="share-modal-publish-btn" title="Publish this chat to all users">
                                <i class="fa fa-globe" style="margin-right: 6px;"></i> Publish Chat
                            </button>
                            <button id="unpublish-chat-btn" type="button" class="share-modal-unpublish-btn" style="display: none;" title="Make this chat private again">
                                <i class="fa fa-lock" style="margin-right: 6px;"></i> Unpublish
                            </button>
                        </div>
                        <div id="publish-link-container" style="display: none;">
                            <label for="publish-link-input" style="font-size: 0.85em; color: #666; display: block; margin-bottom: 4px;">Public link:</label>
                            <div style="display: flex; gap: 8px;">
                                <input id="publish-link-input" type="text" readonly aria-label="Public publish link" style="flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 0.85em;" />
                                <button id="copy-publish-link-btn" type="button" style="padding: 8px 12px; background: #2196F3; color: white; border: none; border-radius: 4px; cursor: pointer;">Copy</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="share-modal-status" class="share-modal-status"></div>
            </div>
        </div>
    </div>
   <!-- Navbar -->
<nav class="navbar">
    <!-- Left side: Sidebar toggle button -->
    <div class="navbar-left">
        {if $is_guest}
        <div class="guest-nav-stack">
            <a id="guest-login-btn" class="guest-login-btn" href="{$smarty.const.APP_URL}/auth/login">
                Log in
            </a>
        </div>
        {/if}
        <button id="toggleSidebar" class="sidebar-icon-btn" aria-label="Toggle sidebar" style="display: none;">
            <i class="fa fa-chevron-right" aria-hidden="true"></i>
        </button>
    </div>

    <!-- Center: Model Switcher -->
    <div class="navbar-center">
        <div id="modelSwitcher" class="model-switcher">
          <div class="brand-model">
            Chat
            <sup><span id="currentModelLabel"></span></sup>
          </div>
          <div class="dropdown">
            <button class="model-switcher-btn" id="modelDropdownBtn" type="button">
              ▼
            </button>
            <ul id="modelDropdownMenu" class="dropdown-menu"></ul>
          </div>
        </div>
    </div>

    <!-- Right side: Notification Bell -->
    <div class="navbar-right">
        <button id="streakIndicator" class="streak-indicator" type="button" aria-label="Learning streak" aria-haspopup="dialog" aria-expanded="false">
            <span class="streak-emoji" aria-hidden="true">
                <picture>
                    <source srcset="https://fonts.gstatic.com/s/e/notoemoji/latest/1f525/512.webp" type="image/webp">
                    <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f525/512.gif" alt="🔥" width="18" height="18">
                </picture>
            </span>
            <span id="streakDayCount" class="streak-day-count">0</span>
        </button>
        <div id="streakInfoPopover" class="streak-popover" role="dialog" aria-hidden="true" aria-labelledby="streakPopoverTitle">
            <button id="streakPopoverClose" class="streak-popover-close" type="button" aria-label="Close streak info">&times;</button>
            <h4 id="streakPopoverTitle">Learning Streak</h4>
            <p>Track your daily study time, keep your streak alive, and earn badges as you grow.</p>
            <button id="streakPopoverGo" class="btn btn-primary" type="button">Go to Streak</button>
        </div>
        <div class="notification-wrapper">
          <button id="notificationBell" class="notification-btn">
            <picture>
                <source srcset="https://fonts.gstatic.com/s/e/notoemoji/latest/1f514/512.webp" type="image/webp">
                <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f514/512.gif" alt="🔔" width="32" height="32">
            </picture>
            <span id="notificationCount" class="notification-count" style="display:none;">0</span>
          </button>

          <!-- Dropdown -->
          <div id="notificationDropdown" class="notification-dropdown">
            <div class="dropdown-header">
              <span>Notifications</span>
              <button id="clearNotifications" class="clear-btn">Clear All</button>
            </div>
            <ul id="notificationList" class="notification-list">
              <li class="no-notification">No notifications</li>
            </ul>
          </div>
        </div>
    </div>
</nav>
        <div style="margin-top: 62px;"></div>

        <div class="chat-area-wrapper">
            {if $view === "chat" && !is_empty($old_chat)}
          
            <div class="messages-container y-overflow-auto" id="messages-container">

            </div>
            {else}
            <div id="empty-chat-state" style="display:none;"></div>
            <div class="y-overflow-auto p-4 chat-start-container">
                <div class="empty-greeting fade-in slide-up">
                    <h1 class="fw-medium text-dark text-center"><span class="greeting-text">ගැම්මක් අල්ලමු </span>
                        {$user->_data['first_name']|default:'Guest'} !
                    </h1>
                </div>
                <div style="height: 24px;"></div> <!-- Reduced spacer for closer greeting and chat input -->
            </div>
            {/if}

            <!-- Error Banner (sticky above chat input) -->
            {if isset($error_message) && $error_message != ''}
            <div id="errorTag" class="error-banner">
              <span class="error-icon"><i class="fa fa-exclamation-triangle"></i></span>
              <span class="error-text">{$error_message}</span>
              <div class="error-buttons">
                <button class="btn-upgrade" onclick="openPreferenceBoxWithBillingTab()">Upgrade</button>
                <button class="btn-close" onclick="document.getElementById('errorTag').style.display='none'">Close</button>
              </div>
            </div>
            {/if}
            <!-- Chat Input Area -->
            <div class="chat-wrapper">
                <!-- Scroll to bottom button -->
                <button id="scroll-to-bottom-btn" class="scroll-to-bottom-btn" style="display: none; align-self: center;" title="Scroll to latest messages">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div id="chatInputContainer" class="chat-input-container" style="display: flex; flex-direction: column; gap: 8px;">
                    <!-- Trial Ended Banner -->
                    <div id="trial-ended-banner" class="trial-ended-banner" style="display: none;">
                        <div class="trial-ended-banner-content">
                            <div class="trial-ended-message">
                                <strong>Your Access to Apilageai-Master is ended</strong>
                                <p>Use another model now or upgrade to Pro</p>
                            </div>
                            <div class="trial-ended-banner-actions">
                                <button id="banner-upgrade-btn" class="banner-upgrade-button" type="button">Upgrade to Pro</button>
                                <button id="banner-close-btn" class="banner-close-button" type="button" aria-label="Close banner">×</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chat input box above icons -->
                    <div class="chat-input-center" style="flex: 1 1 auto; min-width: 0;">
                        <div class="max-w-3xl mx-auto px-4" style="padding: 0;">
                            <form id="chat-form" class="position-relative">
                                <div id="suggestions-dropdown" class="chat-suggestion position-absolute" style="display: none;"></div>
                                <textarea id="message-input" name="message" placeholder="type @ to get suggestions..." class="chat-input"
                                    autocomplete="off" aria-label="Message" style="width: 100%;"></textarea>
                            </form>
                        </div>
                    </div>
                    <!-- Attachment image preview (if any) -->
                    <div id="attachment-container" style="align-self: flex-end;">
                        <div id="document-attachment-container" style="display:none; margin-bottom: 8px;">
                            <div id="document-preview-wrapper" style="display:flex;flex-wrap:wrap;gap:8px;"></div>
                            <label id="document-reference-container" style="display:none; align-items:center; gap:6px; margin-top:6px; font-size:12px; color:#555;">
                                <input type="checkbox" id="documentReferenceToggle" />
                                Always, use PDF
                            </label>
                        </div>
                        <div class="preview-wrapper">
                            <img class="preview-image" id="imagePreview" src="#" alt="Preview">
                            <button class="remove-btn" id="removeImage">×</button>
                        </div>
                    </div>
                    <!-- Icon row: left and right groups aligned under input -->
                    <div class="chat-bottom-row" style="display: flex; justify-content: space-between; align-items: center;">
                      <div class="chat-input-left" style="display: flex; gap: 8px; align-items: center; position: relative;">
                        <div class="dropup" style="position: relative;">
                          <button class="btn-icon" id="button-drop" type="button" aria-label="More tools">
                            <i class="fa-solid fa-plus"></i>
                          </button>
                          <div class="dropup-menu">
                            <button id="enebleThink" class="dropup-menu-item" type="button"><i class="fas fa-flask"></i>DeepThink</button>
                            <button id="toggleGraphBtn" class="dropup-menu-item" type="button"><i class="fas fa-chart-line"></i> Graph</button>
                             <button id="toggleCanvasBtn" class="dropup-menu-item" type="button"><i class="fa-solid fa-pen-to-square"></i> Canvas</button>
                             <button id="uploadDocumentBtn" class="dropup-menu-item" type="button"><i class="fa-solid fa-file-lines"></i>PDF assistant</button>
                             
                          </div>
                        </div>
                        
                        <!-- Feature badge will be inserted dynamically here -->
                        <div id="selected-feature-placeholder" style="display: flex; align-items: center; gap: 8px;"></div>

                        <button type="button" id="fileAttach" class="btn-icon" aria-label="Attach image">
                          <i class="fas fa-images"></i>
                          <input class="d-none" id="fileInput" type="file" name="f" accept="image/png, image/jpeg, image/webp, image/gif" multiple aria-label="Attach image" />
                        </button>
                        <input class="d-none" id="documentInput" type="file" name="documents" accept="application/pdf,text/plain,text/markdown,.pdf,.txt,.md" multiple aria-label="Attach document" />
                      </div>
                      <div class="chat-input-right" style="display: flex; gap: 8px;">
                                                <div id="collab-speaking-indicator" class="collab-speaking-indicator" style="display:none;"></div>
                        <button type="submit" id="send-button" class="btn-icon text-muted" aria-label="Send"><i class="fa-solid fa-paper-plane"></i></button>
                      </div>
                    </div>
                </div>
            </div>
           
        </div>
        <div id="bugReportModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Report a Bug</h2>
                <form id="bugReportForm">
                    <div class="form-group">
                        <label for="bug-email">Email (optional):</label>
                        <input type="email" id="bug-email" name="email" autocomplete="email" aria-label="Your email">
                        <small class="error-message" id="emailError"></small>
                    </div>
                    <div class="form-group">
                        <label for="bug-problem">Describe the problem (minimum 4 words):</label>
                        <textarea id="bug-problem" name="problem" required aria-label="Problem description" autocomplete="off"></textarea>
                        <small class="error-message" id="problemError"></small>
                    </div>
                    <div class="form-group">
                        <label for="bug-screenshot">Upload Screenshot (optional, max 10MB):</label>
                        <input type="file" id="bug-screenshot" name="screenshot" accept="image/*" aria-label="Screenshot">
                        <small class="error-message" id="screenshotError"></small>
                        <div id="bugImagePreview" style="margin-top: 10px; display: none;">
                            <img id="previewImage" src="#" alt="Preview" style="max-width: 100%; max-height: 200px;">
                        </div>
                    </div>
                    <button type="submit" id="sendReport">Send Report</button>
                </form>
                <div id="successMessage" style="display: none;">
                    <p>Thank you! We'll review it soon as we can.</p>
                </div>
            </div>
        </div>
    <script>
    // Observe chat changes to reposition input automatically
    const chatMessages = document.getElementById('messages-container');
    if (chatMessages) {
        const observer = new MutationObserver(() => {
            if (typeof positionChatInputContainer === 'function') {
                positionChatInputContainer();
            }
        });
        observer.observe(chatMessages, { childList: true });
    }
    </script>
    </main>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/3.0.4/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://www.desmos.com/api/v1.10/calculator.js?apiKey=b77098fe4afd4179b5626ad2c0f17ad6"></script>

<!-- Canvas Document Editor Libraries -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">

<script>
  window.userBalance = {$user->_data['balance']|default:0|intval};
</script>

<script>
  window.APP_BASE_URL = '{$smarty.const.APP_URL}';
  window.NODE_API_BASE = '{$smarty.const.NODE_API_BASE}';
  window.IS_GUEST = {if $is_guest}true{else}false{/if};
  window.WHITEBOARD_TEAM_CLIENT_ID = '{$smarty.const.WHITEBOARD_TEAM_CLIENT_ID}';
</script>
<script src="{$smarty.const.APP_URL}/assets/scripts/libs/dialog-js/main.min.js?V=01.03.04.2025"></script>
<script src="{$smarty.const.APP_URL}/assets/scripts/mp.min.js?V=01.22.22.2025"></script>
<script src="{$smarty.const.APP_URL}/assets/scripts/app.min.js?V=1.30.01.2026{get_hash_token()}"></script>
<script src="{$smarty.const.APP_URL}/assets/scripts/prefrence.min.js?V=1.25.2.2026{get_hash_token()}"></script>
<script src="{$smarty.const.APP_URL}/assets/scripts/learning-streak.js?V=1.00.00.2026{get_hash_token()}"></script>
<script src="{$smarty.const.APP_URL}/assets/scripts/notifications.js?V=03.01.10.2025"></script>
<script type="module" src="{$smarty.const.APP_URL}/assets/scripts/gm.min.js?V=12.20.10.2025"></script>
<script src="{$smarty.const.APP_URL}/assets/scripts/ob.js?V=10.26.09.2025{get_hash_token()}"></script>
<script src="{$smarty.const.APP_URL}/assets/scripts/report-data.js?V=1.25.01.2026{get_hash_token()}"></script>
<script>
  (function () {
    const params = new URLSearchParams(window.location.search);
    if (params.get('open') !== 'preferences') return;
    const trigger = () => {
      const userInfo = document.getElementById('sidebarUserInfo');
      if (userInfo) {
        userInfo.click();
      }
    };
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => setTimeout(trigger, 0));
    } else {
      setTimeout(trigger, 0);
    }
  })();
</script>

<style>
body.guest-mode .sidebar,
body.guest-mode #sidebar,
body.guest-mode #toggleSidebar,
body.guest-mode .sidebar-footer,
body.guest-mode #rightsidebar2,
body.guest-mode .sidebar2,
body.guest-mode #rightSidebar,
body.guest-mode .right-sidebar,
body.guest-mode .streak-indicator,
body.guest-mode .notification-wrapper,
body.guest-mode #modelSwitcher,
body.guest-mode #button-drop,
body.guest-mode #fileAttach,
body.guest-mode #documentInput,
body.guest-mode #document-attachment-container,
body.guest-mode #attachment-container,
body.guest-mode #uploadDocumentBtn,
body.guest-mode #toggleCanvasBtn,
body.guest-mode #toggleGraphBtn,
body.guest-mode #enebleThink,
body.guest-mode #open-conversation-gallery,
body.guest-mode #open-share-modal,
body.guest-mode #mindmap-open-btn,
body.guest-mode #mcqblust-gameyard-icon,
body.guest-mode #collab-mic-toggle,
body.guest-mode #conversation-gallery,
body.guest-mode #share-modal {
  display: none !important;
}
body.guest-mode .app-container {
  grid-template-columns: 1fr;
}
body.guest-mode .main-content {
  margin-left: 0 !important;
}
.guest-welcome-lightbox {
  position: fixed;
  inset: 0;
  display: none;
  align-items: center;
  justify-content: center;
  background: rgba(10, 10, 10, 0.55);
  z-index: 1000001;
  padding: 24px;
}
.guest-welcome-lightbox.is-visible {
  display: flex;
}
.guest-welcome-card {
  width: min(520px, 92vw);
  background: #ffffff;
  border-radius: 18px;
  overflow: hidden;
  box-shadow: 0 18px 40px rgba(0, 0, 0, 0.28);
  display: flex;
  flex-direction: column;
}
.guest-welcome-media img {
  display: block;
  width: 100%;
  height: auto;
}
.guest-welcome-content {
  padding: 18px 20px 20px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  text-align: center;
}
.guest-welcome-content h3 {
  margin: 0;
  font-size: 20px;
  color: #111827;
}
.guest-welcome-content p {
  margin: 0;
  font-size: 14px;
  color: #4b5563;
}
.guest-welcome-actions {
  margin-top: 6px;
  display: flex;
  gap: 10px;
  justify-content: center;
  flex-wrap: wrap;
}
.guest-welcome-login {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 9px 16px;
  border-radius: 10px;
  background: #111827;
  color: #ffffff;
  font-weight: 600;
  font-size: 13px;
  text-decoration: none;
}
.guest-welcome-continue {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 9px 16px;
  border-radius: 10px;
  border: 1px solid #d1d5db;
  background: #f9fafb;
  color: #111827;
  font-weight: 600;
  font-size: 13px;
}
.guest-cta {
  margin-top: 6px;
  font-size: 12px;
  color: #777;
  text-align: center;
}
.guest-new-chat-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  border-radius: 10px;
  border: 1px solid #e5e7eb;
  background: #ffffff;
  color: #111827;
  font-weight: 600;
  font-size: 13px;
}
.guest-new-chat-btn:hover {
  border-color: #111827;
}
.guest-nav-stack {
  display: inline-flex;
  flex-direction: column;
  gap: 6px;
}
.guest-login-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 7px 12px;
  border-radius: 10px;
  border: 1px dashed #e5e7eb;
  background: #f9fafb;
  color: #111827;
  font-weight: 600;
  font-size: 12px;
  text-decoration: none;
}
.guest-login-btn:hover {
  border-color: #111827;
}
.navbar-right {
  position: relative;
}
.streak-indicator {
  appearance: none;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  margin-right: 10px;
  border-radius: 999px;
  background: #fff2e6;
  border: 1px solid #f6c79f;
  color: #9a3412;
  font-weight: 600;
  font-size: 12px;
  line-height: 1;
  cursor: pointer;
}
.streak-indicator.is-idle {
  background: #fff7ed;
}
.streak-day-count {
  min-width: 14px;
  text-align: center;
  display: none;
}
.streak-emoji img {
  display: block;
}
.streak-popover {
  position: absolute;
  right: 0;
  top: 42px;
  width: min(260px, 85vw);
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 12px;
  box-shadow: 0 16px 30px rgba(15, 23, 42, 0.18);
  display: none;
  z-index: 10000;
}
.streak-popover.is-visible {
  display: block;
}
.streak-popover h4 {
  margin: 0 22px 6px 0;
  font-size: 14px;
  color: #0f172a;
}
.streak-popover p {
  margin: 0 0 10px;
  font-size: 12px;
  color: #475569;
}
.streak-popover-close {
  position: absolute;
  top: 6px;
  right: 8px;
  border: none;
  background: transparent;
  font-size: 18px;
  line-height: 1;
  color: #94a3b8;
  cursor: pointer;
}
.streak-popover-close:hover {
  color: #475569;
}
.streak-checkin-overlay {
  position: fixed;
  inset: 0;
  display: none;
  align-items: center;
  justify-content: center;
  padding: 24px;
  background: rgba(15, 23, 42, 0.6);
  z-index: 1000002;
}
.streak-checkin-overlay.is-visible {
  display: flex;
}
.streak-checkin-card {
  width: min(520px, 94vw);
  background: #ffffff;
  border-radius: 18px;
  box-shadow: 0 22px 50px rgba(15, 23, 42, 0.25);
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}
.streak-checkin-header h3 {
  margin: 0;
  font-size: 18px;
  color: #0f172a;
}
.streak-checkin-header p {
  margin: 6px 0 0;
  font-size: 13px;
  color: #475569;
}
.streak-checkin-time {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}
.streak-input {
  display: flex;
  flex-direction: column;
  gap: 6px;
  font-size: 12px;
  color: #475569;
}
.streak-input input,
.streak-input textarea {
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 8px 10px;
  font-size: 14px;
  color: #0f172a;
}
.streak-input textarea {
  resize: vertical;
  min-height: 70px;
}
.streak-checkin-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  justify-content: flex-end;
}
.streak-checkin-message {
  font-size: 12px;
  color: #b91c1c;
  min-height: 16px;
}
.streak-actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
}
.streak-about {
  margin-bottom: 12px;
  padding: 10px 12px;
  border-radius: 12px;
  background: #fff7ed;
  border: 1px solid #fde68a;
  color: #92400e;
  font-size: 12px;
  line-height: 1.5;
}
.streak-about h4 {
  margin: 0 0 6px;
  font-size: 13px;
  color: #7c2d12;
}
.streak-about p {
  margin: 0 0 6px;
}
.streak-about p:last-child {
  margin-bottom: 0;
}
.streak-help {
  font-size: 12px;
  color: var(--text-secondary);
  margin-top: 4px;
}
.streak-active-card {
  margin-top: 10px;
  padding: 10px 12px;
  border-radius: 12px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  font-size: 13px;
  color: #0f172a;
}
.streak-history {
  margin-top: 12px;
}
.streak-history h4 {
  margin: 0 0 6px;
  font-size: 13px;
  color: #1f2937;
}
.streak-history-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  gap: 6px;
  font-size: 12px;
  color: #334155;
}
.streak-badges {
  margin-top: 14px;
  display: grid;
  gap: 12px;
}
.streak-badge {
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 12px;
  align-items: center;
  padding: 10px 12px;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  background: #ffffff;
}
.streak-badge img {
  width: 44px;
  height: 44px;
  object-fit: contain;
}
.streak-badge-emoji img {
  width: 32px;
  height: 32px;
  display: block;
}
[data-theme="dark"] .streak-indicator {
  background: var(--red-50);
  border-color: var(--border-color);
  color: var(--text-primary);
}
[data-theme="dark"] .streak-indicator.is-idle {
  background: #2a1c14;
}
[data-theme="dark"] .streak-popover {
  background: var(--card-bg);
  border-color: var(--border-color);
  box-shadow: 0 16px 30px rgba(0, 0, 0, 0.4);
}
[data-theme="dark"] .streak-about {
  background: #2a1c14;
  border-color: #3b2f26;
  color: #f5e6d3;
}
[data-theme="dark"] .streak-about h4 {
  color: #f2c685;
}
[data-theme="dark"] .streak-popover h4 {
  color: var(--text-primary);
}
[data-theme="dark"] .streak-popover p {
  color: var(--text-secondary);
}
[data-theme="dark"] .streak-popover-close {
  color: #94a3b8;
}
[data-theme="dark"] .streak-active-card {
  background: var(--card-bg);
  border-color: var(--border-color);
  color: var(--text-primary);
}
[data-theme="dark"] .streak-badge {
  background: var(--card-bg);
  border-color: var(--border-color);
}
[data-theme="dark"] .streak-badge-title {
  color: var(--text-primary);
}
[data-theme="dark"] .streak-badge-meta {
  color: var(--text-secondary);
}
[data-theme="dark"] .streak-progress {
  background: #2d3748;
}
[data-theme="dark"] .streak-checkin-card {
  background: #111827;
  color: #e2e8f0;
  box-shadow: 0 22px 50px rgba(0, 0, 0, 0.55);
}
[data-theme="dark"] .streak-checkin-header h3 {
  color: #f8fafc;
}
[data-theme="dark"] .streak-checkin-header p {
  color: #94a3b8;
}
[data-theme="dark"] .streak-input {
  color: #94a3b8;
}
[data-theme="dark"] .streak-input input,
[data-theme="dark"] .streak-input textarea {
  background: #0f172a;
  border-color: #1f2937;
  color: #e2e8f0;
}
[data-theme="dark"] .streak-checkin-message {
  color: #fca5a5;
}
.streak-badge-title {
  font-size: 13px;
  font-weight: 600;
  color: #0f172a;
}
.streak-badge-meta {
  font-size: 12px;
  color: #64748b;
  margin-top: 4px;
}
.streak-progress {
  margin-top: 6px;
  height: 6px;
  background: #e2e8f0;
  border-radius: 999px;
  overflow: hidden;
}
.streak-progress-bar {
  height: 100%;
  background: linear-gradient(90deg, #f97316, #f59e0b);
  width: 0%;
}
.sidebar,
.sidebar-items {
  overflow-x: hidden;
}
.sidebar-but .sidebar-but-shortcut {
  position: relative;
  z-index: 3;
}

/* Preferencebox mobile UX improvements */
@media (max-width: 900px) {
  .preferencebox-overlay {
    padding: 12px;
    align-items: stretch;
  }

  .preferencebox {
    width: 100%;
    height: 100%;
    max-height: none;
  }
}

@media (max-width: 768px) {
  .preferencebox-overlay {
    padding: 0;
  }

  .preferencebox {
    height: 100vh;
    height: 100dvh;
    border-radius: 0;
  }

  .preferencebox-close-btn {
    width: 36px;
    height: 36px;
    font-size: 18px;
    top: 10px;
    right: 10px;
  }

  .preferencebox-sidebar {
    padding: 48px 12px 12px;
    border-right: none;
    border-bottom: 1px solid var(--border-color);
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    background: var(--sidebar-bg);
  }

  .preferencebox-sidebar nav ul {
    display: flex;
    gap: 8px;
  }

  .preferencebox-sidebar nav a {
    white-space: nowrap;
    border: 1px solid var(--border-color);
    background: var(--card-bg);
    padding: 8px 12px;
  }

  .preferencebox-sidebar nav a.active {
    border-color: var(--primary-red);
  }

  .preferencebox-content {
    padding: 20px 16px 24px;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    overscroll-behavior: contain;
  }

  .preferencebox-content h2 {
    font-size: 20px;
    padding-bottom: 12px;
    margin-bottom: 18px;
  }

  .form-section {
    margin-bottom: 24px;
  }

  .profile-photo-section {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }

  .setting-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }

  .streak-actions {
    flex-direction: column;
    align-items: stretch;
    gap: 8px;
  }

  .streak-actions .btn {
    width: 100%;
  }

  .delete-account-actions {
    flex-direction: column;
    align-items: stretch;
  }

  .delete-account-actions .btn {
    width: 100%;
  }

  .price-slider-container {
    padding: 16px;
  }

  .table-scroll-container {
    overflow-x: auto;
  }

  .billing-history-table {
    min-width: 560px;
  }
}

@media (max-width: 520px) {
  .preferencebox-sidebar nav a {
    font-size: 13px;
  }

  .preferencebox-sidebar nav a i {
    display: none;
  }
}
</style>

<!-- ===== Whiteboard Team SDK ===== -->
<script src="https://www.whiteboard.team/dist/api.js"></script>
<script src="{$smarty.const.APP_URL}/assets/js/canvas_v2.js"></script>

<!-- ===== Whiteboard Sidebar CSS ===== -->
<style>
.whiteboard-sidebar { display: flex; flex-direction: column; overflow: hidden; }

.whiteboard-sidebar .canvas-header {
  background: transparent;
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-bottom: none;
}

.whiteboard-sidebar .canvas-header h3 {
  color: #f8fafc;
  text-shadow: 0 1px 2px rgba(15, 23, 42, 0.5);
}

.wt-wrapper {
  position: relative;
  flex: 1;
  overflow: hidden;
  background: linear-gradient(180deg, rgba(248, 250, 252, 0.9), rgba(248, 250, 252, 0.6));
}
[data-theme="dark"] .wt-wrapper { background: #0b1220; }

.wt-container {
  width: 100%;
  height: 100%;
}

.wt-status {
  position: absolute;
  bottom: 12px;
  left: 12px;
  padding: 6px 10px;
  border-radius: 12px;
  font-size: 12px;
  background: rgba(15, 23, 42, 0.75);
  color: #fff;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.2s ease;
}
.wt-status.visible { opacity: 1; }
</style>

</body>
</html>
