<?php
/* Smarty version 5.8.0, created on 2026-04-22 09:43:59
  from 'file:app.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.8.0',
  'unifunc' => 'content_69e84b079fa8a2_78691010',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'e6bf16cd6531138689ed2f7c3d31d20af874cfb8' => 
    array (
      0 => 'app.tpl',
      1 => 1773543829,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:components/head.tpl' => 1,
  ),
))) {
function content_69e84b079fa8a2_78691010 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = '/home/apilageai/domains/apilageai.lk/backend/includes/smarty/templates';
$_smarty_tpl->renderSubTemplate("file:components/head.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), (int) 0, $_smarty_current_dir);
echo '<script'; ?>
 src="https://cdn.socket.io/4.8.1/socket.io.min.js" integrity="sha384-mkQ3/7FUtcGyoppY6bz/PORYoGqOl7/aSUMn2ymDOJcapfS6PHqxhRTMh1RR0Q6+" crossorigin="anonymous" defer><?php echo '</script'; ?>
>

<div id="app-loading-overlay" class="app-loading-overlay" aria-hidden="false">
    <div class="app-loading-card">
        <img class="app-loading-logo" src="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/assets/images/icon.png" alt="Apilageai logo" />
    </div>
</div>

<?php if ($_smarty_tpl->getValue('is_guest')) {?>
<div id="guest-welcome-lightbox" class="guest-welcome-lightbox" aria-hidden="true">
    <div class="guest-welcome-card" role="dialog" aria-modal="true" aria-labelledby="guestWelcomeTitle">
        <div class="guest-welcome-media">
            <img src="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/assets/images/guestuser.jpg" alt="Guest welcome" />
        </div>
        <div class="guest-welcome-content">
            <h3 id="guestWelcomeTitle">Welcome to Apilageai</h3>
            <p>To upload images, PDFs, generate graphs, or use memory, you need to log in.</p>
            <div class="guest-welcome-actions">
                <a class="guest-welcome-login" href="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/auth/login">Log in</a>
                <button id="guest-continue-btn" class="guest-welcome-continue" type="button">No continue</button>
            </div>
        </div>
    </div>
</div>
<?php }?>

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

<div class="app-container" data-csrf="<?php echo $_smarty_tpl->getValue('csrf_token');?>
">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header" style="display: flex; align-items: center; justify-content: center; position: relative;">
            <img class="sidebar-logo" src="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/assets/images/icon.png" alt="Apilageai logo" style="width: 44px; height: 44px; object-fit: contain;" />
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
        <button class="sidebar-but" type="button" onclick="window.open('<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/explore', '_self');" title="Explore">
            <span class="sidebar-but-icon" aria-hidden="true">
                <picture>
                    <source srcset="https://fonts.gstatic.com/s/e/notoemoji/latest/1f30e/512.webp" type="image/webp">
                    <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f30e/512.gif" alt="🌎" width="20" height="20">
                </picture>
            </span>
            <span class="sidebar-but-text">Explore</span>
        </button>
        <?php $_smarty_tpl->assign('profileSlug', $_smarty_tpl->getValue('user')->_data['public_profile_username'], false, NULL);?>
        <?php if (!$_smarty_tpl->getValue('profileSlug')) {?>
            <?php $_smarty_tpl->assign('profileSlug', $_smarty_tpl->getValue('user')->_data['public_profile_token'], false, NULL);?>
        <?php }?>
        <button class="sidebar-but" type="button" onclick="window.open('<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/<?php echo (($tmp = $_smarty_tpl->getValue('profileSlug') ?? null)===null||$tmp==='' ? '' ?? null : $tmp);?>
', '_self');" title="Public Profile">
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
  <div class="sidebar-footer-userinfo" id="sidebarUserInfo" title="Account menu" role="button" aria-haspopup="menu" aria-expanded="false">
    <div class="user-avatar">
      <img
        src="<?php echo $_smarty_tpl->getSmarty()->getModifierCallback('user_image_url')($_smarty_tpl->getValue('user')->_data['image']);?>
"
        alt="<?php echo (($tmp = $_smarty_tpl->getValue('user')->_data['first_name'] ?? null)===null||$tmp==='' ? 'Guest' ?? null : $tmp);?>
 Avatar"
        loading="eager"
        decoding="async"
        onerror="this.onerror=null;this.src='<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/assets/images/user.png';"
      />
    </div>
    <div class="user-details">
      <div class="user-name" id="sidebar-user-name"><?php echo (($tmp = $_smarty_tpl->getValue('user')->_data['first_name'] ?? null)===null||$tmp==='' ? 'Guest' ?? null : $tmp);?>
</div>
      <div class="user-credit-text" id="sidebar-credit-text">Credit: Loading...</div>
      <div class="credit-bar-container">
        <div class="credit-bar-fill" id="sidebar-credit-bar" style="width: 0%;"></div>
      </div>
    </div>
  </div>
  <div id="sidebarUserMenu" class="sidebar-user-menu" role="menu" aria-hidden="true">
    <button class="sidebar-user-menu-item" type="button" data-action="upgrade">
      <i class="fa fa-arrow-up" aria-hidden="true"></i>
      Upgrade credit
    </button>
    <button class="sidebar-user-menu-item" type="button" data-action="settings">
      <i class="fa fa-cog" aria-hidden="true"></i>
      Open setting
    </button>
    <button class="sidebar-user-menu-item" type="button" data-action="help">
      <i class="fa fa-question-circle" aria-hidden="true"></i>
      Help
    </button>
    <div class="sidebar-user-menu-divider" role="separator" aria-hidden="true"></div>
    <button class="sidebar-user-menu-item" type="button" data-action="logout">
      <i class="fa fa-sign-out-alt" aria-hidden="true"></i>
      Log Out
    </button>
  </div>
</div>

</aside>
    <!-- sidebar end -->

    <!-- sidebar for notes -->

    <div id="rightsidebar2" class="sidebar2 canvas-sidebar excalidraw-sidebar" aria-hidden="true">

        <!-- Excalidraw Header -->
        <div class="sidebar-header canvas-header">
            <h3>Apilage-Canvas</h3>
            <div class="canvas-header-actions">
                <button id="canvas-fullscreen-btn" type="button" class="canvas-header-btn" title="Fullscreen"><i class="fa-solid fa-expand"></i></button>
                <button id="canvas-close-btn" type="button" class="canvas-close-btn" title="Close">&times;</button>
            </div>
        </div>

        <!-- Excalidraw container -->
        <div class="excalidraw-sidebar-body">
            <div id="excalidraw-sidebar-loading" class="excalidraw-sidebar-loading">Loading Excalidraw…</div>
            <div id="excalidraw-sidebar-root" class="excalidraw-sidebar-root" aria-label="Canvas" role="application"></div>
            <div class="excalidraw-sidebar-hint">Tip: mention <strong>@canvas</strong> to include the canvas in AI responses.</div>
        </div>

    </div>
<!-- Excalidraw sidebar END -->

    
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
            <div class="onboard-header">
                <div class="onboard-stream">
                    <div class="onboard-stream-line" aria-live="polite">
                        <span class="onboard-stream-text">Hello <?php echo (($tmp = $_smarty_tpl->getValue('user')->_data['first_name'] ?? null)===null||$tmp==='' ? 'there' ?? null : $tmp);?>
 just few steps to go</span>
                        <span class="onboard-stream-cursor" aria-hidden="true"></span>
                    </div>
                </div>
                <div class="onboard-progress">
                    <div class="onboard-progress-row">
                        <button type="button" id="back-btn" class="onboard-button onboard-back-btn">Go back</button>
                        <div class="onboard-progress-track">
                            <div id="onboard-progress-fill" class="onboard-progress-fill"></div>
                        </div>
                    </div>
                    <div id="onboard-progress-label" class="onboard-progress-label">Step 1 of 4</div>
                </div>
            </div>

            <form id="onboarding-form" class="onboard-steps-container">
                <input type="hidden" name="interests" id="interests-hidden-input">
                <input type="hidden" name="preference" id="preference-hidden-input">

                <!-- Step 1: School -->
                <div id="step-0" class="onboard-step">
                    <div class="onboard-step-badge">Step 1</div>
                    <h2>Select your school from the list</h2>
                    <p>Choose your school or university. If you are not a student, mark it below.</p>
                    <label class="onboard-label" for="school-input">School or University</label>
                    <input name="school" id="school-input" type="text" class="onboard-input" list="school-list" autocomplete="off" placeholder="Start typing your school name">
                    <div class="onboard-helper">Select your school from the list.</div>
                    <div class="onboard-checkbox-container">
                        <label class="onboard-checkbox" for="not-student-checkbox">
                            <input id="not-student-checkbox" type="checkbox" name="not_student">
                            <span>I'm not a student / My school is not listed</span>
                        </label>
                    </div>
                </div>

                <!-- Step 2: Focused Areas -->
                <div id="step-1" class="onboard-step">
                    <div class="onboard-step-badge">Step 2</div>
                    <h2><?php echo (($tmp = $_smarty_tpl->getValue('user')->_data['first_name'] ?? null)===null||$tmp==='' ? 'Friend' ?? null : $tmp);?>
, what are your interests?</h2>
                    <p id="focus-area-subtitle">Select at least 3 interests so we can personalize your experience.</p>
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
                    <div class="onboard-step-badge">Step 3</div>
                    <h2>AI Personality</h2>
                    <p>Choose how you want ApilageAI to respond when chatting with you.</p>
                    <div class="onboard-preference-list">
                        <div class="onboard-preference-card" data-preference="friendly"><i class="fas fa-hand-holding-heart onboard-icon"></i><div><h3>Friendly & Casual</h3><p>Engaging and conversational.</p></div></div>
                        <div class="onboard-preference-card" data-preference="educational"><i class="fas fa-book-open onboard-icon"></i><div><h3>Informative</h3><p>Knowledgeable and fact-based.</p></div></div>
                        <div class="onboard-preference-card" data-preference="explanatory"><i class="fas fa-magnifying-glass-chart onboard-icon"></i><div><h3>Detailed</h3><p>Breaks down complex topics.</p></div></div>
                        <div class="onboard-preference-card" data-preference="concise"><i class="fas fa-bolt onboard-icon"></i><div><h3>To the Point</h3><p>Brief and direct.</p></div></div>
                    </div>
                </div>

                <!-- Step 4: All Ready -->
                <div id="step-3" class="onboard-step">
                    <div class="onboard-complete onboard-complete--center">
                        <div class="onboard-complete-icon"><i class="fa-solid fa-circle-check"></i></div>
                        <h2>You are all catching up</h2>
                        <p>You're all set. Choose how you want to continue.</p>
                    </div>
                    <div class="onboard-free-cta">
                        <button type="button" class="onboard-button onboard-next-btn onboard-free-btn" data-onboard-plan="free">
                            Continue to Chat Free
                        </button>
                    </div>
                    <hr class="onboard-divider" />
                    <div class="onboard-pro-title">Try pro version</div>
                    <div class="onboard-plan-grid onboard-plan-grid--pro">
                        <button type="button" class="onboard-plan onboard-plan--tag" data-onboard-plan="starter" data-pay-url="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/pay/199.99">
                            <div class="onboard-plan-tagline">Starter</div>
                            <div class="onboard-plan-price">Rs 199.99</div>
                            <ul class="onboard-plan-features">
                                <li>Unlimited Chats</li>
                                <li>Unlimited Image Analysis</li>
                                <li>Unlimited Image Generation</li>
                                <li>Access to all models</li>
                                <li>Access to graphing &amp; code generation</li>
                            </ul>
                            <span class="onboard-plan-cta">One time recharge</span>
                        </button>
                        <button type="button" class="onboard-plan onboard-plan--tag" data-onboard-plan="unlock" data-pay-url="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/pay/499.99">
                            <div class="onboard-plan-tagline">Unlock more features</div>
                            <div class="onboard-plan-price">LKR 499.99</div>
                            <ul class="onboard-plan-features">
                                <li>150,000 AI Tokens</li>
                                <li>API Access Included</li>
                                <li>Valid for 60 days</li>
                                <li>Access to latest releases</li>
                                <li>Access to video generation model (Coming soon)</li>
                            </ul>
                            <span class="onboard-plan-cta">Best for power users</span>
                        </button>
                    </div>
                </div>

                <div class="onboard-step-actions">
                    <div id="error-message" class="onboard-error-message"></div>
                    <button type="button" id="next-btn" class="onboard-button onboard-next-btn">Next</button>
                </div>
            </form>
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
                        <?php if (!$_smarty_tpl->getValue('is_guest')) {?>
                        <li><a href="#" class="preferencebox-tab-link" data-tab="public-profile"><i class="fa fa-user-circle"></i> Public Profile</a></li>
                        <?php }?>
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
                              src="<?php echo $_smarty_tpl->getSmarty()->getModifierCallback('user_image_url')($_smarty_tpl->getValue('user')->_data['image']);?>
"
                              alt="<?php echo (($tmp = $_smarty_tpl->getValue('user')->_data['first_name'] ?? null)===null||$tmp==='' ? 'Guest' ?? null : $tmp);?>
 Avatar"
                              loading="lazy"
                              decoding="async"
                              onerror="this.onerror=null;this.src='<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/assets/images/user.png';"
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
                                <input type="text" id="firstName" value="<?php echo (($tmp = $_smarty_tpl->getValue('user')->_data['first_name'] ?? null)===null||$tmp==='' ? 'Guest' ?? null : $tmp);?>
">
                            </div>
                            <div class="form-group">
                                <label for="lastName">Last Name</label>
                                <input type="text" id="lastName" value="<?php echo $_smarty_tpl->getValue('user')->_data['last_name'];?>
">
                            </div>
                        </div>
                    </div>
                     <div class="form-section">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" value="<?php echo $_smarty_tpl->getValue('user')->_data['email'];?>
">
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" value="<?php echo $_smarty_tpl->getValue('user')->_data['phone'];?>
">
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

                <?php if (!$_smarty_tpl->getValue('is_guest')) {?>
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
                <?php }?>

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
                            <a href="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/auth/signout" class="btn btn-secondary"><i class="fa fa-sign-out-alt"></i> Log Out</a>
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
        <?php if ($_smarty_tpl->getValue('is_guest')) {?>
        <div class="guest-nav-stack">
            <a id="guest-login-btn" class="guest-login-btn" href="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/auth/login">
                Log in
            </a>
        </div>
        <?php }?>
        <button id="sidebarCloseBtn" class="sidebar-icon-btn sidebar-close-btn" aria-label="Close sidebar" style="display: none;">
            <i class="fa fa-chevron-left" aria-hidden="true"></i>
        </button>
        <button id="toggleSidebar" class="sidebar-icon-btn" aria-label="Toggle sidebar" style="display: none;">
            <i class="fa fa-chevron-right" aria-hidden="true"></i>
        </button>
    </div>

    <!-- Center: Model Switcher -->
    <div class="navbar-center">
        <div id="modelSwitcher" class="model-switcher">
          <div class="brand-model">
            Apilageai
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
        <button id="shareRocketBtn" class="notification-btn share-rocket-btn" type="button" aria-label="Share chat">
            <picture>
                <source srcset="https://fonts.gstatic.com/s/e/notoemoji/latest/1f680/512.webp" type="image/webp">
                <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f680/512.gif" alt="🚀" width="32" height="32">
            </picture>
        </button>
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
            <?php if ($_smarty_tpl->getValue('view') === "chat" && !$_smarty_tpl->getSmarty()->getModifierCallback('is_empty')($_smarty_tpl->getValue('old_chat'))) {?>
          
            <div class="messages-container y-overflow-auto" id="messages-container">

            </div>
            <?php } else { ?>
            <div id="empty-chat-state" style="display:none;"></div>
            <div class="y-overflow-auto p-4 chat-start-container">
                <div class="empty-greeting fade-in slide-up">
                    <h1 class="fw-medium text-dark text-center"><span class="greeting-text">ගැම්මක් අල්ලමු </span>
                        <?php echo (($tmp = $_smarty_tpl->getValue('user')->_data['first_name'] ?? null)===null||$tmp==='' ? 'Guest' ?? null : $tmp);?>
 !
                    </h1>
                </div>
                <div style="height: 24px;"></div> <!-- Reduced spacer for closer greeting and chat input -->
            </div>
            <?php }?>

            <!-- Subject AI Modal -->
            <div id="subject-ai-modal" class="subject-ai-modal" aria-hidden="true" style="display: none;">
                <div class="subject-ai-backdrop"></div>
                <div class="subject-ai-card" role="dialog" aria-modal="true" aria-labelledby="subjectAITitle">
                    <div class="subject-ai-header">
                        <h3 id="subjectAITitle">📚 Subject AI <span class="beta-badge">BETA</span></h3>
                        <button id="subjectAIClose" class="subject-ai-close" type="button" aria-label="Close">×</button>
                    </div>
                    <div class="subject-ai-body">
                        <div class="subject-ai-section">
                            <label class="subject-ai-label">Select Grade</label>
                            <div class="subject-ai-options">
                                <button type="button" class="subject-ai-btn grade-btn" data-grade="11">Grade 11</button>
                                <button type="button" class="subject-ai-btn grade-btn coming-soon" data-grade="10" disabled>
                                    Grade 10
                                    <span class="coming-soon-badge">Coming Soon</span>
                                </button>
                            </div>
                        </div>
                        <div class="subject-ai-section">
                            <label class="subject-ai-label">Select Subject</label>
                            <div class="subject-ai-options">
                                <button type="button" class="subject-ai-btn subject-btn coming-soon" data-subject="maths" disabled>
                                    Maths
                                    <span class="coming-soon-badge">Coming Soon</span>
                                </button>
                                <button type="button" class="subject-ai-btn subject-btn" data-subject="science" disabled>Science</button>
                            </div>
                        </div>
                        <div id="subject-ai-message" class="subject-ai-message"></div>
                    </div>
                    <div class="subject-ai-footer">
                        <button type="button" id="subject-ai-cancel" class="btn-secondary">Cancel</button>
                        <button type="button" id="subject-ai-start" class="btn-primary" disabled>Start Subject Mode</button>
                    </div>
                </div>
            </div>

            <!-- Error Banner (sticky above chat input) -->
            <?php if ((true && ($_smarty_tpl->hasVariable('error_message') && null !== ($_smarty_tpl->getValue('error_message') ?? null))) && $_smarty_tpl->getValue('error_message') != '') {?>
            <div id="errorTag" class="error-banner">
              <span class="error-icon"><i class="fa fa-exclamation-triangle"></i></span>
              <span class="error-text"><?php echo $_smarty_tpl->getValue('error_message');?>
</span>
              <div class="error-buttons">
                <button class="btn-upgrade" onclick="openPreferenceBoxWithBillingTab()">Upgrade</button>
                <button class="btn-close" onclick="document.getElementById('errorTag').style.display='none'">Close</button>
              </div>
            </div>
            <?php }?>
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
                             <button id="openSubjectAIBtn" class="dropup-menu-item" type="button"><i class="fa-solid fa-book"></i>Subject AI</button>

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
    <?php echo '<script'; ?>
>
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
    <?php echo '</script'; ?>
>
    </main>
</div>
<?php echo '<script'; ?>
 src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/3.0.4/jspdf.umd.min.js" defer><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="https://www.desmos.com/api/v1.10/calculator.js?apiKey=b77098fe4afd4179b5626ad2c0f17ad6" defer><?php echo '</script'; ?>
>

<!-- Canvas Document Editor Libraries -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">

<?php echo '<script'; ?>
>
  window.userBalance = <?php echo $_smarty_tpl->getSmarty()->getModifierCallback('intval')((($tmp = $_smarty_tpl->getValue('user')->_data['balance'] ?? null)===null||$tmp==='' ? 0 ?? null : $tmp));?>
;
<?php echo '</script'; ?>
>

<?php echo '<script'; ?>
>
  window.APP_BASE_URL = '<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
';
  window.NODE_API_BASE = '<?php echo (defined('NODE_API_BASE') ? constant('NODE_API_BASE') : null);?>
';
  window.IS_GUEST = <?php if ($_smarty_tpl->getValue('is_guest')) {?>true<?php } else { ?>false<?php }?>;
  window.CSRF_TOKEN = '<?php echo $_smarty_tpl->getValue('csrf_token');?>
';
<?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/assets/scripts/libs/dialog-js/main.min.js?V=01.03.04.2025" defer><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/assets/scripts/mp.min.js?V=01.22.22.2025" defer><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/assets/scripts/appbase.min.js?V=1.30.01.2026<?php echo $_smarty_tpl->getSmarty()->getModifierCallback('get_hash_token')();?>
" defer><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/assets/scripts/functions.min.js?V=1.30.01.2026<?php echo $_smarty_tpl->getSmarty()->getModifierCallback('get_hash_token')();?>
" defer><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/assets/scripts/utilities.min.js?V=1.30.01.2026<?php echo $_smarty_tpl->getSmarty()->getModifierCallback('get_hash_token')();?>
" defer><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/assets/scripts/prefrence.min.js?V=1.25.2.2026<?php echo $_smarty_tpl->getSmarty()->getModifierCallback('get_hash_token')();?>
" defer><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/assets/scripts/learning-streak.js?V=1.00.00.2026<?php echo $_smarty_tpl->getSmarty()->getModifierCallback('get_hash_token')();?>
" defer><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/assets/scripts/notifications.js?V=03.01.10.2025" defer><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 type="module" src="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/assets/scripts/gm.min.js?V=12.20.10.2025"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/assets/scripts/ob.js?V=10.26.09.2025<?php echo $_smarty_tpl->getSmarty()->getModifierCallback('get_hash_token')();?>
" defer><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/assets/scripts/report-data.js?V=1.25.01.2026<?php echo $_smarty_tpl->getSmarty()->getModifierCallback('get_hash_token')();?>
" defer><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/assets/scripts/subject-ai.js?V=1.0.0.2026" defer><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
>
  (function () {
    const params = new URLSearchParams(window.location.search);
    if (params.get('open') !== 'preferences') return;
    const trigger = () => {
      if (typeof window.openPreferenceBox === 'function') {
        window.openPreferenceBox('general');
        return;
      }
      const userInfo = document.getElementById('sidebarUserInfo');
      if (userInfo) userInfo.click();
    };
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => setTimeout(trigger, 0));
    } else {
      setTimeout(trigger, 0);
    }
  })();
<?php echo '</script'; ?>
>
</body>
</html>
<?php }
}
