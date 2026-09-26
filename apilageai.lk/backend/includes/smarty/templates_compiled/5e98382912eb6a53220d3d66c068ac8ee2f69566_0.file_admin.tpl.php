<?php
/* Smarty version 5.8.0, created on 2026-04-20 21:18:16
  from 'file:admin.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.8.0',
  'unifunc' => 'content_69e64ac0ef83a5_78381126',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '5e98382912eb6a53220d3d66c068ac8ee2f69566' => 
    array (
      0 => 'admin.tpl',
      1 => 1771815763,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:components/head.tpl' => 1,
  ),
))) {
function content_69e64ac0ef83a5_78381126 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = '/home/apilageai/domains/apilageai.lk/backend/includes/smarty/templates';
$_smarty_tpl->renderSubTemplate("file:components/head.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), (int) 0, $_smarty_current_dir);
?>

<main class="min-h-screen bg-brand-gray">
  <div class="container mx-auto px-6 py-10" data-csrf="<?php echo $_smarty_tpl->getValue('csrf_token');?>
">
    <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
      <div>
        <span class="inline-flex items-center gap-2 rounded-full border-2 border-brand-dark bg-brand-blueLight px-3 py-1 text-xs font-bold uppercase tracking-wider text-brand-dark">
          Admin Console
        </span>
        <h1 class="mt-4 text-3xl md:text-5xl font-display font-black text-brand-dark">Apilage Admin</h1>
        <p class="mt-2 text-brand-dark/70 text-lg font-medium">Manage users, earnings, sessions, and reports.</p>
      </div>
      <div class="flex items-center gap-3">
        <a href="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/app" class="px-4 py-2 rounded-lg border-2 border-brand-dark bg-white text-brand-dark font-bold shadow-hard-sm hover:shadow-hard transition-all">Back to App</a>
        <a href="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/auth/logout" class="px-4 py-2 rounded-lg border-2 border-brand-dark bg-brand-red text-white font-bold shadow-hard-sm hover:shadow-hard transition-all">Sign Out</a>
      </div>
    </div>

    <section class="mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
      <div class="rounded-2xl border-2 border-brand-dark bg-white p-6 shadow-hard">
        <p class="text-xs uppercase tracking-wider text-brand-dark/60">Total Users</p>
        <p class="mt-3 text-3xl font-black text-brand-dark"><?php echo $_smarty_tpl->getValue('admin_stats')['total_users'];?>
</p>
      </div>
      <div class="rounded-2xl border-2 border-brand-dark bg-white p-6 shadow-hard">
        <p class="text-xs uppercase tracking-wider text-brand-dark/60">New Users (30d)</p>
        <p class="mt-3 text-3xl font-black text-brand-dark"><?php echo $_smarty_tpl->getValue('admin_stats')['new_users_30'];?>
</p>
      </div>
      <div class="rounded-2xl border-2 border-brand-dark bg-white p-6 shadow-hard">
        <p class="text-xs uppercase tracking-wider text-brand-dark/60">Active Users Now</p>
        <p class="mt-3 text-3xl font-black text-brand-dark"><?php echo $_smarty_tpl->getValue('admin_stats')['active_users_now'];?>
</p>
        <p class="text-xs text-brand-dark/50 mt-1">Last 15 minutes</p>
      </div>
      <div class="rounded-2xl border-2 border-brand-dark bg-white p-6 shadow-hard">
        <p class="text-xs uppercase tracking-wider text-brand-dark/60">Earnings (Paid)</p>
        <p class="mt-3 text-3xl font-black text-brand-dark">Rs. <?php echo $_smarty_tpl->getSmarty()->getModifierCallback('number_format')($_smarty_tpl->getValue('admin_stats')['earnings_paid'],2);?>
</p>
        <p class="text-xs text-brand-dark/50 mt-1">Pending: Rs. <?php echo $_smarty_tpl->getSmarty()->getModifierCallback('number_format')($_smarty_tpl->getValue('admin_stats')['earnings_pending'],2);?>
</p>
      </div>
    </section>

    <section class="mt-10 grid gap-6 lg:grid-cols-3">
      <div class="rounded-2xl border-2 border-brand-dark bg-white p-6 shadow-hard lg:col-span-2">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div>
            <h2 class="text-xl font-bold text-brand-dark">New User Growth</h2>
            <p class="text-sm text-brand-dark/70 mt-1">Signups grouped by day, week, month, or year.</p>
          </div>
          <select id="userGrowthRange" class="rounded-lg border-2 border-brand-dark bg-white px-3 py-2 text-sm font-semibold text-brand-dark">
            <option value="daily">Last 30 days (daily)</option>
            <option value="weekly">Last 12 weeks (weekly)</option>
            <option value="monthly">Last 12 months (monthly)</option>
            <option value="yearly">Last 5 years (yearly)</option>
          </select>
        </div>
        <div class="mt-4" style="height:240px;">
          <canvas id="userGrowthChart" style="width:100%;height:100%;display:block;"></canvas>
        </div>
      </div>
      <div class="rounded-2xl border-2 border-brand-dark bg-white p-6 shadow-hard">
        <h2 class="text-xl font-bold text-brand-dark">Send Notifications</h2>
        <p class="text-sm text-brand-dark/70 mt-1">Notify selected users or everyone.</p>
        <form id="notificationForm" method="post" action="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/admin_actions.php" class="mt-4 space-y-3">
          <input type="hidden" name="action" value="send_notification">
          <input type="hidden" name="csrf_token" value="<?php echo $_smarty_tpl->getValue('csrf_token');?>
">
          <textarea name="message" rows="4" class="w-full rounded-lg border-2 border-brand-dark p-3 text-sm" placeholder="Write a notification message..."></textarea>
          <label class="flex items-center gap-2 text-sm font-bold text-brand-dark">
            <input type="checkbox" id="notifyAll" name="send_all" value="1" class="h-4 w-4"> Send to all users
          </label>
          <div class="max-h-48 overflow-y-auto border-2 border-brand-dark rounded-lg p-2 bg-brand-gray">
            <?php
$_from = $_smarty_tpl->getSmarty()->getRuntime('Foreach')->init($_smarty_tpl, $_smarty_tpl->getValue('admin_users'), 'user');
$foreach0DoElse = true;
foreach ($_from ?? [] as $_smarty_tpl->getVariable('user')->value) {
$foreach0DoElse = false;
?>
              <label class="flex items-center gap-2 text-sm text-brand-dark py-1">
                <input type="checkbox" name="user_ids[]" value="<?php echo $_smarty_tpl->getValue('user')['id'];?>
" class="h-4 w-4">
                <span class="font-semibold"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['first_name'], ENT_QUOTES, 'UTF-8', true);?>
 <?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['last_name'], ENT_QUOTES, 'UTF-8', true);?>
</span>
                <span class="text-xs text-brand-dark/60">(<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['email'], ENT_QUOTES, 'UTF-8', true);?>
)</span>
              </label>
            <?php
}
$_smarty_tpl->getSmarty()->getRuntime('Foreach')->restore($_smarty_tpl, 1);?>
            <?php if ($_smarty_tpl->getSmarty()->getModifierCallback('count')($_smarty_tpl->getValue('admin_users')) == 0) {?>
              <p class="text-sm text-brand-dark/60">No users found.</p>
            <?php }?>
          </div>
          <button type="submit" class="w-full rounded-lg border-2 border-brand-dark bg-brand-blueLight px-4 py-2 font-bold text-brand-dark shadow-hard-sm hover:shadow-hard">Send Notification</button>
        </form>
      </div>
    </section>

    <section class="mt-10 rounded-2xl border-2 border-brand-dark bg-white p-6 shadow-hard">
      <h2 class="text-xl font-bold text-brand-dark">Active Users</h2>
      <p class="text-sm text-brand-dark/70 mt-1">Sessions active in the last 15 minutes.</p>
      <div class="mt-4 overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="text-left text-brand-dark/70">
            <tr>
              <th class="py-2">User</th>
              <th class="py-2">Email</th>
              <th class="py-2">Last Seen</th>
              <th class="py-2">IP</th>
              <th class="py-2">Client</th>
              <th class="py-2">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
$_from = $_smarty_tpl->getSmarty()->getRuntime('Foreach')->init($_smarty_tpl, $_smarty_tpl->getValue('admin_active_sessions'), 'session');
$foreach1DoElse = true;
foreach ($_from ?? [] as $_smarty_tpl->getVariable('session')->value) {
$foreach1DoElse = false;
?>
              <tr class="border-t border-brand-dark/10">
                <td class="py-2 font-semibold text-brand-dark"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('session')['first_name'], ENT_QUOTES, 'UTF-8', true);?>
 <?php echo htmlspecialchars((string)$_smarty_tpl->getValue('session')['last_name'], ENT_QUOTES, 'UTF-8', true);?>
</td>
                <td class="py-2"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('session')['email'], ENT_QUOTES, 'UTF-8', true);?>
</td>
                <td class="py-2"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('session')['last_seen'], ENT_QUOTES, 'UTF-8', true);?>
</td>
                <td class="py-2"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('session')['ip'], ENT_QUOTES, 'UTF-8', true);?>
</td>
                <td class="py-2 max-w-xs truncate"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('session')['client'], ENT_QUOTES, 'UTF-8', true);?>
</td>
                <td class="py-2">
                  <button class="logout-user-btn rounded-lg border-2 border-brand-dark bg-white px-3 py-1 text-xs font-bold" data-user-id="<?php echo $_smarty_tpl->getValue('session')['user_id'];?>
">Log out</button>
                </td>
              </tr>
            <?php
}
$_smarty_tpl->getSmarty()->getRuntime('Foreach')->restore($_smarty_tpl, 1);?>
            <?php if ($_smarty_tpl->getSmarty()->getModifierCallback('count')($_smarty_tpl->getValue('admin_active_sessions')) == 0) {?>
              <tr>
                <td colspan="6" class="py-4 text-center text-sm text-brand-dark/60">No active sessions.</td>
              </tr>
            <?php }?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="mt-10 rounded-2xl border-2 border-brand-dark bg-white p-6 shadow-hard">
      <h2 class="text-xl font-bold text-brand-dark">Users & Onboarding</h2>
      <p class="text-sm text-brand-dark/70 mt-1">All user records with onboarding details.</p>
      <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
        <form method="get" class="flex flex-wrap items-center gap-2">
          <input type="text" name="user_search" value="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('admin_user_search'), ENT_QUOTES, 'UTF-8', true);?>
" placeholder="Search users..." class="rounded-lg border-2 border-brand-dark px-3 py-2 text-sm">
          <select name="user_sort" class="rounded-lg border-2 border-brand-dark bg-white px-3 py-2 text-sm font-semibold text-brand-dark">
            <option value="reg_desc" <?php if ($_smarty_tpl->getValue('admin_user_sort') == 'reg_desc') {?>selected<?php }?>>Newest registrations</option>
            <option value="login_desc" <?php if ($_smarty_tpl->getValue('admin_user_sort') == 'login_desc') {?>selected<?php }?>>Latest logins</option>
            <option value="login_asc" <?php if ($_smarty_tpl->getValue('admin_user_sort') == 'login_asc') {?>selected<?php }?>>Oldest logins</option>
            <option value="balance_desc" <?php if ($_smarty_tpl->getValue('admin_user_sort') == 'balance_desc') {?>selected<?php }?>>Highest balance</option>
            <option value="balance_asc" <?php if ($_smarty_tpl->getValue('admin_user_sort') == 'balance_asc') {?>selected<?php }?>>Lowest balance</option>
          </select>
          <input type="hidden" name="txn_search" value="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('admin_txn_search'), ENT_QUOTES, 'UTF-8', true);?>
">
          <button type="submit" class="rounded-lg border-2 border-brand-dark bg-brand-blueLight px-4 py-2 text-sm font-bold text-brand-dark shadow-hard-sm hover:shadow-hard">Apply</button>
        </form>
        <div class="text-xs text-brand-dark/60">Showing <?php echo $_smarty_tpl->getValue('admin_user_pagination')['total'];?>
 users</div>
      </div>
      <div class="mt-4 overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="text-left text-brand-dark/70">
            <tr>
              <th class="py-2">ID</th>
              <th class="py-2">Name</th>
              <th class="py-2">Email</th>
              <th class="py-2">Type</th>
              <th class="py-2">Balance</th>
              <th class="py-2">Registered</th>
              <th class="py-2">Last Login</th>
              <th class="py-2">Onboarding</th>
              <th class="py-2">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
$_from = $_smarty_tpl->getSmarty()->getRuntime('Foreach')->init($_smarty_tpl, $_smarty_tpl->getValue('admin_users'), 'user');
$foreach2DoElse = true;
foreach ($_from ?? [] as $_smarty_tpl->getVariable('user')->value) {
$foreach2DoElse = false;
?>
              <tr class="border-t border-brand-dark/10" data-user-row="<?php echo $_smarty_tpl->getValue('user')['id'];?>
">
                <td class="py-2"><?php echo $_smarty_tpl->getValue('user')['id'];?>
</td>
                <td class="py-2 font-semibold text-brand-dark"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['first_name'], ENT_QUOTES, 'UTF-8', true);?>
 <?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['last_name'], ENT_QUOTES, 'UTF-8', true);?>
</td>
                <td class="py-2"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['email'], ENT_QUOTES, 'UTF-8', true);?>
</td>
                <td class="py-2"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['type'], ENT_QUOTES, 'UTF-8', true);?>
</td>
                <td class="py-2">Rs. <?php echo $_smarty_tpl->getSmarty()->getModifierCallback('number_format')($_smarty_tpl->getValue('user')['balance'],2);?>
</td>
                <td class="py-2"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['reg_date'], ENT_QUOTES, 'UTF-8', true);?>
</td>
                <td class="py-2">
                  <?php if ($_smarty_tpl->getValue('user')['last_login']) {?>
                    <?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['last_login'], ENT_QUOTES, 'UTF-8', true);?>

                  <?php } else { ?>
                    <span class="text-brand-dark/50">-</span>
                  <?php }?>
                </td>
                <td class="py-2">
                  <details class="text-xs text-brand-dark/70">
                    <summary class="cursor-pointer font-semibold text-brand-dark">View details</summary>
                    <div class="mt-2 space-y-1">
                      <div><span class="font-semibold">Phone:</span> <?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['phone'], ENT_QUOTES, 'UTF-8', true);?>
</div>
                      <div><span class="font-semibold">Email verified:</span> <?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['email_verified'], ENT_QUOTES, 'UTF-8', true);?>
</div>
                      <div><span class="font-semibold">Image:</span> <?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['image'], ENT_QUOTES, 'UTF-8', true);?>
</div>
                      <div><span class="font-semibold">Memory:</span> <?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['memory'], ENT_QUOTES, 'UTF-8', true);?>
</div>
                      <div><span class="font-semibold">Subscription:</span> <?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['subscription_status'], ENT_QUOTES, 'UTF-8', true);?>
</div>
                      <div><span class="font-semibold">Onboard complete:</span> <?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['onboard_complete'], ENT_QUOTES, 'UTF-8', true);?>
</div>
                      <div><span class="font-semibold">Failed logins:</span> <?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['failed_login_attempts'], ENT_QUOTES, 'UTF-8', true);?>
</div>
                      <div><span class="font-semibold">Locked until:</span> <?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['locked_until'], ENT_QUOTES, 'UTF-8', true);?>
</div>
                      <div class="pt-2 font-semibold text-brand-dark">Onboarding</div>
                      <div><span class="font-semibold">School:</span> <?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['school'], ENT_QUOTES, 'UTF-8', true);?>
</div>
                      <div><span class="font-semibold">Not student:</span> <?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['not_student'], ENT_QUOTES, 'UTF-8', true);?>
</div>
                      <div><span class="font-semibold">Interests:</span> <?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['interests'], ENT_QUOTES, 'UTF-8', true);?>
</div>
                      <div><span class="font-semibold">Preference:</span> <?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['preference'], ENT_QUOTES, 'UTF-8', true);?>
</div>
                      <div><span class="font-semibold">Created:</span> <?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['onboarding_created_at'], ENT_QUOTES, 'UTF-8', true);?>
</div>
                      <div><span class="font-semibold">Updated:</span> <?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['onboarding_updated_at'], ENT_QUOTES, 'UTF-8', true);?>
</div>
                    </div>
                  </details>
                </td>
                <td class="py-2 flex flex-col gap-2">
                  <button class="logout-user-btn rounded-lg border-2 border-brand-dark bg-white px-3 py-1 text-xs font-bold" data-user-id="<?php echo $_smarty_tpl->getValue('user')['id'];?>
">Log out</button>
                  <button class="edit-user-btn rounded-lg border-2 border-brand-dark bg-brand-blueLight px-3 py-1 text-xs font-bold" data-user-id="<?php echo $_smarty_tpl->getValue('user')['id'];?>
">Edit</button>
                  <button class="delete-user-btn rounded-lg border-2 border-brand-dark bg-brand-red text-white px-3 py-1 text-xs font-bold" data-user-id="<?php echo $_smarty_tpl->getValue('user')['id'];?>
">Delete</button>
                </td>
              </tr>
              <tr class="border-t border-brand-dark/10" data-user-edit-row="<?php echo $_smarty_tpl->getValue('user')['id'];?>
" style="display:none;">
                <td colspan="9" class="py-3">
                  <form class="user-edit-form space-y-3" data-user-id="<?php echo $_smarty_tpl->getValue('user')['id'];?>
">
                    <div class="text-xs font-bold uppercase tracking-wide text-brand-dark/60">Profile</div>
                    <div class="grid gap-3 md:grid-cols-3">
                      <label class="text-xs font-semibold text-brand-dark">First name
                        <input name="first_name" value="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['first_name'], ENT_QUOTES, 'UTF-8', true);?>
" class="mt-1 w-full rounded-lg border-2 border-brand-dark px-3 py-2 text-sm">
                      </label>
                      <label class="text-xs font-semibold text-brand-dark">Last name
                        <input name="last_name" value="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['last_name'], ENT_QUOTES, 'UTF-8', true);?>
" class="mt-1 w-full rounded-lg border-2 border-brand-dark px-3 py-2 text-sm">
                      </label>
                      <label class="text-xs font-semibold text-brand-dark">Email
                        <input name="email" value="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['email'], ENT_QUOTES, 'UTF-8', true);?>
" class="mt-1 w-full rounded-lg border-2 border-brand-dark px-3 py-2 text-sm">
                      </label>
                      <label class="text-xs font-semibold text-brand-dark">Phone
                        <input name="phone" value="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['phone'], ENT_QUOTES, 'UTF-8', true);?>
" class="mt-1 w-full rounded-lg border-2 border-brand-dark px-3 py-2 text-sm">
                      </label>
                      <label class="text-xs font-semibold text-brand-dark">Type
                        <select name="type" class="mt-1 w-full rounded-lg border-2 border-brand-dark bg-white px-3 py-2 text-sm">
                          <option value="1" <?php if ($_smarty_tpl->getValue('user')['type'] == '1') {?>selected<?php }?>>User</option>
                          <option value="2" <?php if ($_smarty_tpl->getValue('user')['type'] == '2') {?>selected<?php }?>>Admin</option>
                          <option value="3" <?php if ($_smarty_tpl->getValue('user')['type'] == '3') {?>selected<?php }?>>Super Admin</option>
                        </select>
                      </label>
                      <label class="text-xs font-semibold text-brand-dark">Balance
                        <input name="balance" value="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['balance'], ENT_QUOTES, 'UTF-8', true);?>
" class="mt-1 w-full rounded-lg border-2 border-brand-dark px-3 py-2 text-sm">
                      </label>
                      <label class="text-xs font-semibold text-brand-dark">Email verified
                        <select name="email_verified" class="mt-1 w-full rounded-lg border-2 border-brand-dark bg-white px-3 py-2 text-sm">
                          <option value="0" <?php if ($_smarty_tpl->getValue('user')['email_verified'] == 0) {?>selected<?php }?>>No</option>
                          <option value="1" <?php if ($_smarty_tpl->getValue('user')['email_verified'] == 1) {?>selected<?php }?>>Yes</option>
                        </select>
                      </label>
                      <label class="text-xs font-semibold text-brand-dark">Subscription
                        <select name="subscription_status" class="mt-1 w-full rounded-lg border-2 border-brand-dark bg-white px-3 py-2 text-sm">
                          <option value="0" <?php if ($_smarty_tpl->getValue('user')['subscription_status'] == 0) {?>selected<?php }?>>Inactive</option>
                          <option value="1" <?php if ($_smarty_tpl->getValue('user')['subscription_status'] == 1) {?>selected<?php }?>>Active</option>
                        </select>
                      </label>
                      <label class="text-xs font-semibold text-brand-dark">Onboard complete
                        <select name="onboard_complete" class="mt-1 w-full rounded-lg border-2 border-brand-dark bg-white px-3 py-2 text-sm">
                          <option value="0" <?php if ($_smarty_tpl->getValue('user')['onboard_complete'] == 0) {?>selected<?php }?>>No</option>
                          <option value="1" <?php if ($_smarty_tpl->getValue('user')['onboard_complete'] == 1) {?>selected<?php }?>>Yes</option>
                        </select>
                      </label>
                      <label class="text-xs font-semibold text-brand-dark">Failed logins
                        <input name="failed_login_attempts" value="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['failed_login_attempts'], ENT_QUOTES, 'UTF-8', true);?>
" class="mt-1 w-full rounded-lg border-2 border-brand-dark px-3 py-2 text-sm">
                      </label>
                      <label class="text-xs font-semibold text-brand-dark">Locked until
                        <input name="locked_until" value="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['locked_until'], ENT_QUOTES, 'UTF-8', true);?>
" placeholder="YYYY-MM-DD HH:MM:SS" class="mt-1 w-full rounded-lg border-2 border-brand-dark px-3 py-2 text-sm">
                      </label>
                      <label class="text-xs font-semibold text-brand-dark md:col-span-3">Image path
                        <input name="image" value="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['image'], ENT_QUOTES, 'UTF-8', true);?>
" class="mt-1 w-full rounded-lg border-2 border-brand-dark px-3 py-2 text-sm">
                      </label>
                      <label class="text-xs font-semibold text-brand-dark md:col-span-3">Memory
                        <textarea name="memory" rows="3" class="mt-1 w-full rounded-lg border-2 border-brand-dark px-3 py-2 text-sm"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['memory'], ENT_QUOTES, 'UTF-8', true);?>
</textarea>
                      </label>
                    </div>
                    <div class="text-xs font-bold uppercase tracking-wide text-brand-dark/60">Onboarding</div>
                    <div class="grid gap-3 md:grid-cols-3">
                      <label class="text-xs font-semibold text-brand-dark">School
                        <input name="school" value="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['school'], ENT_QUOTES, 'UTF-8', true);?>
" class="mt-1 w-full rounded-lg border-2 border-brand-dark px-3 py-2 text-sm">
                      </label>
                      <label class="text-xs font-semibold text-brand-dark">Not student
                        <select name="not_student" class="mt-1 w-full rounded-lg border-2 border-brand-dark bg-white px-3 py-2 text-sm">
                          <option value="0" <?php if ($_smarty_tpl->getValue('user')['not_student'] == 0) {?>selected<?php }?>>No</option>
                          <option value="1" <?php if ($_smarty_tpl->getValue('user')['not_student'] == 1) {?>selected<?php }?>>Yes</option>
                        </select>
                      </label>
                      <label class="text-xs font-semibold text-brand-dark">Preference
                        <select name="preference" class="mt-1 w-full rounded-lg border-2 border-brand-dark bg-white px-3 py-2 text-sm">
                          <option value="">None</option>
                          <option value="friendly" <?php if ($_smarty_tpl->getValue('user')['preference'] == 'friendly') {?>selected<?php }?>>Friendly</option>
                          <option value="educational" <?php if ($_smarty_tpl->getValue('user')['preference'] == 'educational') {?>selected<?php }?>>Educational</option>
                          <option value="explanatory" <?php if ($_smarty_tpl->getValue('user')['preference'] == 'explanatory') {?>selected<?php }?>>Explanatory</option>
                          <option value="concise" <?php if ($_smarty_tpl->getValue('user')['preference'] == 'concise') {?>selected<?php }?>>Concise</option>
                        </select>
                      </label>
                      <label class="text-xs font-semibold text-brand-dark md:col-span-3">Interests
                        <textarea name="interests" rows="2" class="mt-1 w-full rounded-lg border-2 border-brand-dark px-3 py-2 text-sm"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('user')['interests'], ENT_QUOTES, 'UTF-8', true);?>
</textarea>
                      </label>
                    </div>
                    <div class="flex flex-wrap gap-2">
                      <button type="submit" class="rounded-lg border-2 border-brand-dark bg-brand-blueLight px-4 py-2 text-sm font-bold text-brand-dark shadow-hard-sm hover:shadow-hard">Save changes</button>
                      <button type="button" class="cancel-user-edit-btn rounded-lg border-2 border-brand-dark bg-white px-4 py-2 text-sm font-bold" data-user-id="<?php echo $_smarty_tpl->getValue('user')['id'];?>
">Close</button>
                    </div>
                  </form>
                </td>
              </tr>
            <?php
}
$_smarty_tpl->getSmarty()->getRuntime('Foreach')->restore($_smarty_tpl, 1);?>
            <?php if ($_smarty_tpl->getSmarty()->getModifierCallback('count')($_smarty_tpl->getValue('admin_users')) == 0) {?>
              <tr>
                <td colspan="9" class="py-4 text-center text-sm text-brand-dark/60">No users found.</td>
              </tr>
            <?php }?>
          </tbody>
        </table>
      </div>
      <?php if ($_smarty_tpl->getValue('admin_user_pagination')['total_pages'] > 1) {?>
        <div class="mt-4 flex flex-wrap gap-2">
          <?php
$__section_pg_0_loop = (is_array(@$_loop=$_smarty_tpl->getValue('admin_user_pagination')['total_pages']) ? count($_loop) : max(0, (int) $_loop));
$__section_pg_0_total = $__section_pg_0_loop;
$_smarty_tpl->tpl_vars['__smarty_section_pg'] = new \Smarty\Variable(array());
if ($__section_pg_0_total !== 0) {
for ($__section_pg_0_iteration = 1, $_smarty_tpl->tpl_vars['__smarty_section_pg']->value['index'] = 0; $__section_pg_0_iteration <= $__section_pg_0_total; $__section_pg_0_iteration++, $_smarty_tpl->tpl_vars['__smarty_section_pg']->value['index']++){
?>
            <?php $_smarty_tpl->assign('pageNum', ($_smarty_tpl->getValue('__smarty_section_pg')['index'] ?? null)+1, false, NULL);?>
            <a href="<?php echo $_smarty_tpl->getValue('admin_user_page_base');?>
user_page=<?php echo $_smarty_tpl->getValue('pageNum');?>
"
               class="rounded-lg border-2 border-brand-dark px-3 py-1 text-sm font-bold <?php if ($_smarty_tpl->getValue('pageNum') == $_smarty_tpl->getValue('admin_user_pagination')['page']) {?>bg-brand-blueLight text-brand-dark<?php } else { ?>bg-white text-brand-dark<?php }?>">
              <?php echo $_smarty_tpl->getValue('pageNum');?>

            </a>
          <?php
}
}
?>
        </div>
      <?php }?>
    </section>

    <section class="mt-10 rounded-2xl border-2 border-brand-dark bg-white p-6 shadow-hard">
      <h2 class="text-xl font-bold text-brand-dark">Bug Reports</h2>
      <p class="text-sm text-brand-dark/70 mt-1">Track issues and update their status.</p>
      <div class="mt-4 overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="text-left text-brand-dark/70">
            <tr>
              <th class="py-2">ID</th>
              <th class="py-2">Email</th>
              <th class="py-2">Problem</th>
              <th class="py-2">Screenshot</th>
              <th class="py-2">Created</th>
              <th class="py-2">Status</th>
              <th class="py-2">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
$_from = $_smarty_tpl->getSmarty()->getRuntime('Foreach')->init($_smarty_tpl, $_smarty_tpl->getValue('admin_bug_reports'), 'bug');
$foreach3DoElse = true;
foreach ($_from ?? [] as $_smarty_tpl->getVariable('bug')->value) {
$foreach3DoElse = false;
?>
              <tr class="border-t border-brand-dark/10" data-bug-row="<?php echo $_smarty_tpl->getValue('bug')['id'];?>
">
                <td class="py-2"><?php echo $_smarty_tpl->getValue('bug')['id'];?>
</td>
                <td class="py-2"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('bug')['email'], ENT_QUOTES, 'UTF-8', true);?>
</td>
                <td class="py-2 max-w-md"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('bug')['problem'], ENT_QUOTES, 'UTF-8', true);?>
</td>
                <td class="py-2">
                  <?php if ($_smarty_tpl->getValue('bug')['screenshot_url']) {?>
                    <a class="text-brand-blue underline" href="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('bug')['screenshot_url'], ENT_QUOTES, 'UTF-8', true);?>
" target="_blank">View</a>
                  <?php } else { ?>
                    <span class="text-brand-dark/50">None</span>
                  <?php }?>
                </td>
                <td class="py-2"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('bug')['created_at'], ENT_QUOTES, 'UTF-8', true);?>
</td>
                <td class="py-2">
                  <select class="bug-status-select border-2 border-brand-dark rounded px-2 py-1 text-xs" data-bug-id="<?php echo $_smarty_tpl->getValue('bug')['id'];?>
" name="bug_status" aria-label="Bug status">
                    <option value="open" <?php if ($_smarty_tpl->getValue('bug')['status'] == 'open') {?>selected<?php }?>>Open</option>
                    <option value="fixed" <?php if ($_smarty_tpl->getValue('bug')['status'] == 'fixed') {?>selected<?php }?>>Fixed</option>
                  </select>
                </td>
                <td class="py-2">
                  <button class="delete-bug-btn rounded-lg border-2 border-brand-dark bg-white px-3 py-1 text-xs font-bold" data-bug-id="<?php echo $_smarty_tpl->getValue('bug')['id'];?>
">Delete</button>
                </td>
              </tr>
            <?php
}
$_smarty_tpl->getSmarty()->getRuntime('Foreach')->restore($_smarty_tpl, 1);?>
            <?php if ($_smarty_tpl->getSmarty()->getModifierCallback('count')($_smarty_tpl->getValue('admin_bug_reports')) == 0) {?>
              <tr>
                <td colspan="7" class="py-4 text-center text-sm text-brand-dark/60">No bug reports.</td>
              </tr>
            <?php }?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="mt-10 rounded-2xl border-2 border-brand-dark bg-white p-6 shadow-hard">
      <h2 class="text-xl font-bold text-brand-dark">Earnings & Transactions</h2>
      <p class="text-sm text-brand-dark/70 mt-1">Recent payments and revenue totals.</p>
      <div class="mt-4 grid gap-4 md:grid-cols-3">
        <div class="rounded-xl border-2 border-brand-dark bg-brand-blueLight p-4">
          <p class="text-xs uppercase tracking-wider text-brand-dark/70">Total Earnings</p>
          <p class="mt-2 text-2xl font-black text-brand-dark">Rs. <?php echo $_smarty_tpl->getSmarty()->getModifierCallback('number_format')($_smarty_tpl->getValue('admin_stats')['earnings_total'],2);?>
</p>
        </div>
        <div class="rounded-xl border-2 border-brand-dark bg-brand-blueLight p-4">
          <p class="text-xs uppercase tracking-wider text-brand-dark/70">Paid</p>
          <p class="mt-2 text-2xl font-black text-brand-dark">Rs. <?php echo $_smarty_tpl->getSmarty()->getModifierCallback('number_format')($_smarty_tpl->getValue('admin_stats')['earnings_paid'],2);?>
</p>
        </div>
        <div class="rounded-xl border-2 border-brand-dark bg-brand-blueLight p-4">
          <p class="text-xs uppercase tracking-wider text-brand-dark/70">Pending</p>
          <p class="mt-2 text-2xl font-black text-brand-dark">Rs. <?php echo $_smarty_tpl->getSmarty()->getModifierCallback('number_format')($_smarty_tpl->getValue('admin_stats')['earnings_pending'],2);?>
</p>
        </div>
      </div>
      <form method="get" class="mt-6 flex flex-wrap items-center gap-2">
        <input type="text" name="txn_search" value="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('admin_txn_search'), ENT_QUOTES, 'UTF-8', true);?>
" placeholder="Search transactions..." class="rounded-lg border-2 border-brand-dark px-3 py-2 text-sm">
        <input type="hidden" name="user_search" value="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('admin_user_search'), ENT_QUOTES, 'UTF-8', true);?>
">
        <input type="hidden" name="user_sort" value="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('admin_user_sort'), ENT_QUOTES, 'UTF-8', true);?>
">
        <button type="submit" class="rounded-lg border-2 border-brand-dark bg-brand-blueLight px-4 py-2 text-sm font-bold text-brand-dark shadow-hard-sm hover:shadow-hard">Search</button>
        <span class="text-xs text-brand-dark/60">Showing latest 10 records</span>
      </form>
      <div class="mt-6 overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="text-left text-brand-dark/70">
            <tr>
              <th class="py-2">Invoice</th>
              <th class="py-2">User</th>
              <th class="py-2">Email</th>
              <th class="py-2">Amount</th>
              <th class="py-2">Paid</th>
              <th class="py-2">Created</th>
            </tr>
          </thead>
          <tbody>
            <?php
$_from = $_smarty_tpl->getSmarty()->getRuntime('Foreach')->init($_smarty_tpl, $_smarty_tpl->getValue('admin_transactions'), 'txn');
$foreach4DoElse = true;
foreach ($_from ?? [] as $_smarty_tpl->getVariable('txn')->value) {
$foreach4DoElse = false;
?>
              <tr class="border-t border-brand-dark/10">
                <td class="py-2"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('txn')['invoice_id'], ENT_QUOTES, 'UTF-8', true);?>
</td>
                <td class="py-2"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('txn')['first_name'], ENT_QUOTES, 'UTF-8', true);?>
 <?php echo htmlspecialchars((string)$_smarty_tpl->getValue('txn')['last_name'], ENT_QUOTES, 'UTF-8', true);?>
</td>
                <td class="py-2"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('txn')['email'], ENT_QUOTES, 'UTF-8', true);?>
</td>
                <td class="py-2">Rs. <?php echo $_smarty_tpl->getSmarty()->getModifierCallback('number_format')($_smarty_tpl->getValue('txn')['amount'],2);?>
</td>
                <td class="py-2">
                  <?php if ($_smarty_tpl->getValue('txn')['paid'] == 1) {?>
                    <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-1 text-xs font-bold text-green-800">Paid</span>
                  <?php } else { ?>
                    <span class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-1 text-xs font-bold text-yellow-800">Pending</span>
                  <?php }?>
                </td>
                <td class="py-2"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('txn')['created_at'], ENT_QUOTES, 'UTF-8', true);?>
</td>
              </tr>
            <?php
}
$_smarty_tpl->getSmarty()->getRuntime('Foreach')->restore($_smarty_tpl, 1);?>
            <?php if ($_smarty_tpl->getSmarty()->getModifierCallback('count')($_smarty_tpl->getValue('admin_transactions')) == 0) {?>
              <tr>
                <td colspan="6" class="py-4 text-center text-sm text-brand-dark/60">No transactions found.</td>
              </tr>
            <?php }?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="mt-10 rounded-2xl border-2 border-brand-dark bg-white p-6 shadow-hard">
      <h2 class="text-xl font-bold text-brand-dark">Free User Limits</h2>
      <p class="text-sm text-brand-dark/70 mt-1">Daily usage counters per free user.</p>
      <div class="mt-4 overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="text-left text-brand-dark/70">
            <tr>
              <th class="py-2">User ID</th>
              <th class="py-2">User</th>
              <th class="py-2">Date</th>
              <th class="py-2">Messages</th>
              <th class="py-2">Image Uploads</th>
              <th class="py-2">File Uploads</th>
              <th class="py-2">Image Generations</th>
              <th class="py-2">Window</th>
              <th class="py-2">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
$_from = $_smarty_tpl->getSmarty()->getRuntime('Foreach')->init($_smarty_tpl, $_smarty_tpl->getValue('admin_free_user_daily_usage'), 'usage');
$foreach5DoElse = true;
foreach ($_from ?? [] as $_smarty_tpl->getVariable('usage')->value) {
$foreach5DoElse = false;
?>
              <tr class="border-t border-brand-dark/10" data-user-id="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('usage')['user_id'], ENT_QUOTES, 'UTF-8', true);?>
" data-date="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('usage')['date'], ENT_QUOTES, 'UTF-8', true);?>
" data-window-id="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('usage')['window_id'], ENT_QUOTES, 'UTF-8', true);?>
">
                <td class="py-2"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('usage')['user_id'], ENT_QUOTES, 'UTF-8', true);?>
</td>
                <td class="py-2">
                  <div class="font-semibold text-brand-dark"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('usage')['first_name'], ENT_QUOTES, 'UTF-8', true);?>
 <?php echo htmlspecialchars((string)$_smarty_tpl->getValue('usage')['last_name'], ENT_QUOTES, 'UTF-8', true);?>
</div>
                  <div class="text-xs text-brand-dark/60"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('usage')['email'], ENT_QUOTES, 'UTF-8', true);?>
</div>
                </td>
                <td class="py-2"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('usage')['date'], ENT_QUOTES, 'UTF-8', true);?>
</td>
                <td class="py-2">
                  <input data-field="messages_used" name="messages_used" aria-label="Messages used" value="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('usage')['messages_used'], ENT_QUOTES, 'UTF-8', true);?>
" class="w-24 rounded-lg border-2 border-brand-dark px-2 py-1 text-xs">
                </td>
                <td class="py-2">
                  <input data-field="image_uploads_used" name="image_uploads_used" aria-label="Image uploads used" value="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('usage')['image_uploads_used'], ENT_QUOTES, 'UTF-8', true);?>
" class="w-28 rounded-lg border-2 border-brand-dark px-2 py-1 text-xs">
                </td>
                <td class="py-2">
                  <input data-field="file_uploads_used" name="file_uploads_used" aria-label="File uploads used" value="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('usage')['file_uploads_used'], ENT_QUOTES, 'UTF-8', true);?>
" class="w-24 rounded-lg border-2 border-brand-dark px-2 py-1 text-xs">
                </td>
                <td class="py-2">
                  <input data-field="image_generations_used" name="image_generations_used" aria-label="Image generations used" value="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('usage')['image_generations_used'], ENT_QUOTES, 'UTF-8', true);?>
" class="w-28 rounded-lg border-2 border-brand-dark px-2 py-1 text-xs">
                </td>
                <td class="py-2"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('usage')['window_id'], ENT_QUOTES, 'UTF-8', true);?>
</td>
                <td class="py-2">
                  <button class="save-free-usage-btn rounded-lg border-2 border-brand-dark bg-brand-blueLight px-3 py-1 text-xs font-bold">Save</button>
                </td>
              </tr>
            <?php
}
$_smarty_tpl->getSmarty()->getRuntime('Foreach')->restore($_smarty_tpl, 1);?>
            <?php if ($_smarty_tpl->getSmarty()->getModifierCallback('count')($_smarty_tpl->getValue('admin_free_user_daily_usage')) == 0) {?>
              <tr>
                <td colspan="9" class="py-4 text-center text-sm text-brand-dark/60">No free user usage records found.</td>
              </tr>
            <?php }?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="mt-10 rounded-2xl border-2 border-brand-dark bg-white p-6 shadow-hard">
      <h2 class="text-xl font-bold text-brand-dark">Trial Abuse Tracking</h2>
      <p class="text-sm text-brand-dark/70 mt-1">Review and edit trial abuse tracking entries.</p>
      <div class="mt-4 overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="text-left text-brand-dark/70">
            <tr>
              <th class="py-2">ID</th>
              <th class="py-2">User ID</th>
              <th class="py-2">User</th>
              <th class="py-2">IP</th>
              <th class="py-2">Fingerprint</th>
              <th class="py-2">Trial Start</th>
              <th class="py-2">Trial End</th>
              <th class="py-2">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
$_from = $_smarty_tpl->getSmarty()->getRuntime('Foreach')->init($_smarty_tpl, $_smarty_tpl->getValue('admin_trial_abuse_tracking'), 'row');
$foreach6DoElse = true;
foreach ($_from ?? [] as $_smarty_tpl->getVariable('row')->value) {
$foreach6DoElse = false;
?>
              <tr class="border-t border-brand-dark/10" data-trial-abuse-row="<?php echo $_smarty_tpl->getValue('row')['id'];?>
">
                <td class="py-2"><?php echo $_smarty_tpl->getValue('row')['id'];?>
</td>
                <td class="py-2">
                  <input data-field="user_id" name="user_id" aria-label="User ID" value="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('row')['user_id'], ENT_QUOTES, 'UTF-8', true);?>
" class="w-24 rounded-lg border-2 border-brand-dark px-2 py-1 text-xs">
                </td>
                <td class="py-2">
                  <div class="font-semibold text-brand-dark"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('row')['first_name'], ENT_QUOTES, 'UTF-8', true);?>
 <?php echo htmlspecialchars((string)$_smarty_tpl->getValue('row')['last_name'], ENT_QUOTES, 'UTF-8', true);?>
</div>
                  <div class="text-xs text-brand-dark/60"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('row')['email'], ENT_QUOTES, 'UTF-8', true);?>
</div>
                </td>
                <td class="py-2">
                  <input data-field="ip_address" name="ip_address" aria-label="IP address" value="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('row')['ip_address'], ENT_QUOTES, 'UTF-8', true);?>
" class="w-36 rounded-lg border-2 border-brand-dark px-2 py-1 text-xs">
                </td>
                <td class="py-2">
                  <input data-field="device_fingerprint" name="device_fingerprint" aria-label="Device fingerprint" value="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('row')['device_fingerprint'], ENT_QUOTES, 'UTF-8', true);?>
" class="w-48 rounded-lg border-2 border-brand-dark px-2 py-1 text-xs">
                </td>
                <td class="py-2">
                  <input data-field="trial_start_date" name="trial_start_date" aria-label="Trial start date" value="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('row')['trial_start_date'], ENT_QUOTES, 'UTF-8', true);?>
" placeholder="YYYY-MM-DD" class="w-32 rounded-lg border-2 border-brand-dark px-2 py-1 text-xs">
                </td>
                <td class="py-2">
                  <input data-field="trial_end_date" name="trial_end_date" aria-label="Trial end date" value="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('row')['trial_end_date'], ENT_QUOTES, 'UTF-8', true);?>
" placeholder="YYYY-MM-DD" class="w-32 rounded-lg border-2 border-brand-dark px-2 py-1 text-xs">
                </td>
                <td class="py-2">
                  <button class="save-trial-abuse-btn rounded-lg border-2 border-brand-dark bg-brand-blueLight px-3 py-1 text-xs font-bold" data-id="<?php echo $_smarty_tpl->getValue('row')['id'];?>
">Save</button>
                </td>
              </tr>
            <?php
}
$_smarty_tpl->getSmarty()->getRuntime('Foreach')->restore($_smarty_tpl, 1);?>
            <?php if ($_smarty_tpl->getSmarty()->getModifierCallback('count')($_smarty_tpl->getValue('admin_trial_abuse_tracking')) == 0) {?>
              <tr>
                <td colspan="8" class="py-4 text-center text-sm text-brand-dark/60">No trial abuse records found.</td>
              </tr>
            <?php }?>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</main>

<?php echo '<script'; ?>
 src="https://cdn.jsdelivr.net/npm/chart.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/assets/scripts/chart-lite.js?V=<?php echo $_smarty_tpl->getSmarty()->getModifierCallback('get_hash_number')();?>
"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
>
  (function () {
    var growthLabels = <?php echo json_encode($_smarty_tpl->getValue('admin_user_growth_labels'));?>
;
    var growthData = <?php echo json_encode($_smarty_tpl->getValue('admin_user_growth_data'));?>
;
    var growthSeries = <?php echo json_encode($_smarty_tpl->getValue('admin_user_growth_series'));?>
;
    var chartEl = document.getElementById("userGrowthChart");
    var growthRangeSelect = document.getElementById("userGrowthRange");
    var growthSeriesMap = (growthSeries && growthSeries.daily) ? growthSeries : {
      daily: { labels: growthLabels, data: growthData },
    };
    var getSeries = function (key) {
      return growthSeriesMap[key] || growthSeriesMap.daily || { labels: growthLabels, data: growthData };
    };
    var growthChart = null;
    var growthLiteKey = (growthRangeSelect && growthRangeSelect.value) ? growthRangeSelect.value : "daily";

    var renderLiteChart = function (key) {
      if (!chartEl || !window.ChartLite) return;
      var series = getSeries(key);
      window.ChartLite.line(chartEl, series.labels, series.data, {
        lineColor: "#172554",
        pointColor: "#0ea5e9",
      });
    };

    var renderGrowthChart = function () {
      if (!chartEl) return;
      var initialSeries = getSeries(growthLiteKey);
      if (window.Chart) {
        if (growthChart) {
          growthChart.data.labels = initialSeries.labels;
          growthChart.data.datasets[0].data = initialSeries.data;
          growthChart.update();
          return;
        }
        growthChart = new Chart(chartEl, {
          type: "line",
          data: {
            labels: initialSeries.labels,
            datasets: [{
              label: "New Users",
              data: initialSeries.data,
              borderColor: "#172554",
              backgroundColor: "rgba(56, 189, 248, 0.3)",
              fill: true,
              tension: 0.3,
              pointRadius: 2,
            }],
          },
          options: {
            responsive: true,
            scales: {
              y: { beginAtZero: true },
            },
          },
        });
        return;
      }
      if (window.ChartLite) {
        renderLiteChart(growthLiteKey);
      }
    };

    if (chartEl) {
      renderGrowthChart();
      window.setTimeout(renderGrowthChart, 200);
      window.addEventListener("resize", function () {
        renderGrowthChart();
      });
    }

    if (growthRangeSelect) {
      growthRangeSelect.addEventListener("change", function () {
        var nextKey = growthRangeSelect.value || "daily";
        growthLiteKey = nextKey;
        renderGrowthChart();
      });
    }
  })();
<?php echo '</script'; ?>
>
<?php echo '<script'; ?>
>
  const adminEndpoint = (window.APP_BASE_URL || window.location.origin).replace(/\/$/, "") + "/admin_actions.php";
  const csrfEl = document.querySelector("[data-csrf]");
  const csrfToken = csrfEl ? csrfEl.getAttribute("data-csrf") : "";

  function buildParams(action, payload = {}) {
    const params = new URLSearchParams();
    params.set("action", action);
    params.set("csrf_token", csrfToken);
    Object.entries(payload).forEach(([key, value]) => {
      if (Array.isArray(value)) {
        value.forEach((item) => params.append(key, item));
        return;
      }
      if (value === undefined || value === null) return;
      params.append(key, value);
    });
    return params;
  }

  async function postAdminAction(action, payload = {}) {
    const body = buildParams(action, payload);

    const response = await fetch(adminEndpoint, {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body,
    });

    const data = await response.json();
    if (!response.ok || !data.success) {
      throw new Error(data.message || "Request failed");
    }
    return data;
  }

  // Notifications
  const notifyForm = document.getElementById("notificationForm");
  const notifyAll = document.getElementById("notifyAll");
  if (notifyForm) {
    notifyForm.addEventListener("submit", async (event) => {
      event.preventDefault();
      const formData = new FormData(notifyForm);
      const message = (formData.get("message") || "").toString().trim();
      const sendAll = (notifyAll && notifyAll.checked) ? "1" : "0";
      const userIds = formData.getAll("user_ids[]");

      try {
        const payload = { message, send_all: sendAll };
        if (sendAll === "0") {
          payload["user_ids[]"] = userIds;
        }
        await postAdminAction("send_notification", payload);
        alert("Notification sent.");
        notifyForm.reset();
      } catch (error) {
        alert(error.message);
      }
    });
  }

  // Logout user buttons
  document.querySelectorAll(".logout-user-btn").forEach((button) => {
    button.addEventListener("click", async () => {
      const userId = button.getAttribute("data-user-id");
      if (!userId) return;
      try {
        await postAdminAction("logout_user", { user_id: userId });
        button.disabled = true;
        button.textContent = "Logged out";
      } catch (error) {
        alert(error.message);
      }
    });
  });

  // Delete user buttons
  document.querySelectorAll(".delete-user-btn").forEach((button) => {
    button.addEventListener("click", async () => {
      const userId = button.getAttribute("data-user-id");
      if (!userId) return;
      if (!confirm("Delete this user and all related data? This cannot be undone.")) return;
      try {
        await postAdminAction("delete_user", { user_id: userId });
        const row = document.querySelector('[data-user-row=\"' + userId + '\"]');
        if (row) row.remove();
      } catch (error) {
        alert(error.message);
      }
    });
  });

  // Edit user toggles
  document.querySelectorAll(".edit-user-btn").forEach((button) => {
    button.addEventListener("click", () => {
      const userId = button.getAttribute("data-user-id");
      if (!userId) return;
      const row = document.querySelector('[data-user-edit-row=\"' + userId + '\"]');
      if (!row) return;
      row.style.display = row.style.display === "none" || row.style.display === "" ? "table-row" : "none";
    });
  });

  document.querySelectorAll(".cancel-user-edit-btn").forEach((button) => {
    button.addEventListener("click", () => {
      const userId = button.getAttribute("data-user-id");
      if (!userId) return;
      const row = document.querySelector('[data-user-edit-row=\"' + userId + '\"]');
      if (row) row.style.display = "none";
    });
  });

  document.querySelectorAll(".user-edit-form").forEach((form) => {
    form.addEventListener("submit", async (event) => {
      event.preventDefault();
      const userId = form.getAttribute("data-user-id");
      if (!userId) return;
      const formData = new FormData(form);
      const payload = { user_id: userId };
      formData.forEach((value, key) => {
        payload[key] = value;
      });
      try {
        await postAdminAction("update_user_profile", payload);
        alert("User updated.");
        window.location.reload();
      } catch (error) {
        alert(error.message);
      }
    });
  });

  // Bug status updates
  document.querySelectorAll(".bug-status-select").forEach((select) => {
    select.addEventListener("change", async () => {
      const bugId = select.getAttribute("data-bug-id");
      if (!bugId) return;
      try {
        await postAdminAction("update_bug_status", { bug_id: bugId, status: select.value });
      } catch (error) {
        alert(error.message);
      }
    });
  });

  // Delete bug reports
  document.querySelectorAll(".delete-bug-btn").forEach((button) => {
    button.addEventListener("click", async () => {
      const bugId = button.getAttribute("data-bug-id");
      if (!bugId) return;
      if (!confirm("Delete this bug report?")) return;
      try {
        await postAdminAction("delete_bug", { bug_id: bugId });
        const row = document.querySelector('[data-bug-row=\"' + bugId + '\"]');
        if (row) row.remove();
      } catch (error) {
        alert(error.message);
      }
    });
  });

  // Free user daily usage update
  document.querySelectorAll(".save-free-usage-btn").forEach((button) => {
    button.addEventListener("click", async () => {
      const row = button.closest("tr");
      if (!row) return;
      const userId = row.dataset.userId || "";
      const date = row.dataset.date || "";
      const windowId = row.dataset.windowId || "";
      const messagesEl = row.querySelector('[data-field=\"messages_used\"]');
      const imageUploadsEl = row.querySelector('[data-field=\"image_uploads_used\"]');
      const fileUploadsEl = row.querySelector('[data-field=\"file_uploads_used\"]');
      const imageGenerationsEl = row.querySelector('[data-field=\"image_generations_used\"]');
      const payload = {
        user_id: userId,
        date,
        window_id: windowId,
        messages_used: messagesEl ? messagesEl.value : "",
        image_uploads_used: imageUploadsEl ? imageUploadsEl.value : "",
        file_uploads_used: fileUploadsEl ? fileUploadsEl.value : "",
        image_generations_used: imageGenerationsEl ? imageGenerationsEl.value : "",
      };
      try {
        await postAdminAction("update_free_user_daily_usage", payload);
        alert("Free user usage updated.");
      } catch (error) {
        alert(error.message);
      }
    });
  });

  // Trial abuse tracking update
  document.querySelectorAll(".save-trial-abuse-btn").forEach((button) => {
    button.addEventListener("click", async () => {
      const row = button.closest("tr");
      const id = button.getAttribute("data-id");
      if (!row || !id) return;
      const userIdEl = row.querySelector('[data-field=\"user_id\"]');
      const ipEl = row.querySelector('[data-field=\"ip_address\"]');
      const fpEl = row.querySelector('[data-field=\"device_fingerprint\"]');
      const startEl = row.querySelector('[data-field=\"trial_start_date\"]');
      const endEl = row.querySelector('[data-field=\"trial_end_date\"]');
      const payload = {
        id,
        user_id: userIdEl ? userIdEl.value : "",
        ip_address: ipEl ? ipEl.value : "",
        device_fingerprint: fpEl ? fpEl.value : "",
        trial_start_date: startEl ? startEl.value : "",
        trial_end_date: endEl ? endEl.value : "",
      };
      try {
        await postAdminAction("update_trial_abuse", payload);
        alert("Trial abuse record updated.");
      } catch (error) {
        alert(error.message);
      }
    });
  });
<?php echo '</script'; ?>
>

</body>
</html>
<?php }
}
