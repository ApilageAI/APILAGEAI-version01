<?php
/* Smarty version 5.7.0, created on 2026-02-06 23:19:32
  from 'file:admin.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.7.0',
  'unifunc' => 'content_698629ac3924b8_41565605',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'd682ad6db6a4588f8ce94f489451e9a69667e389' => 
    array (
      0 => 'admin.tpl',
      1 => 1770399983,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:components/head.tpl' => 1,
  ),
))) {
function content_698629ac3924b8_41565605 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = '/Users/dinethgunawardana/Documents/GitHub/apilageai-personal/backend/includes/smarty/templates';
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
        <h2 class="text-xl font-bold text-brand-dark">New User Growth</h2>
        <p class="text-sm text-brand-dark/70 mt-1">Daily signups for the last 30 days.</p>
        <div class="mt-4">
          <canvas id="userGrowthChart" height="120"></canvas>
        </div>
      </div>
      <div class="rounded-2xl border-2 border-brand-dark bg-white p-6 shadow-hard">
        <h2 class="text-xl font-bold text-brand-dark">Send Notifications</h2>
        <p class="text-sm text-brand-dark/70 mt-1">Notify selected users or everyone.</p>
        <form id="notificationForm" class="mt-4 space-y-3">
          <textarea name="message" rows="4" class="w-full rounded-lg border-2 border-brand-dark p-3 text-sm" placeholder="Write a notification message..."></textarea>
          <label class="flex items-center gap-2 text-sm font-bold text-brand-dark">
            <input type="checkbox" id="notifyAll" class="h-4 w-4"> Send to all users
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
                  <button class="delete-user-btn rounded-lg border-2 border-brand-dark bg-brand-red text-white px-3 py-1 text-xs font-bold" data-user-id="<?php echo $_smarty_tpl->getValue('user')['id'];?>
">Delete</button>
                </td>
              </tr>
            <?php
}
$_smarty_tpl->getSmarty()->getRuntime('Foreach')->restore($_smarty_tpl, 1);?>
            <?php if ($_smarty_tpl->getSmarty()->getModifierCallback('count')($_smarty_tpl->getValue('admin_users')) == 0) {?>
              <tr>
                <td colspan="8" class="py-4 text-center text-sm text-brand-dark/60">No users found.</td>
              </tr>
            <?php }?>
          </tbody>
        </table>
      </div>
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
                  <?php if ($_smarty_tpl->getValue('bug')['screenshot_path']) {?>
                    <a class="text-brand-blue underline" href="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('bug')['screenshot_path'], ENT_QUOTES, 'UTF-8', true);?>
" target="_blank">View</a>
                  <?php } else { ?>
                    <span class="text-brand-dark/50">None</span>
                  <?php }?>
                </td>
                <td class="py-2"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('bug')['created_at'], ENT_QUOTES, 'UTF-8', true);?>
</td>
                <td class="py-2">
                  <select class="bug-status-select border-2 border-brand-dark rounded px-2 py-1 text-xs" data-bug-id="<?php echo $_smarty_tpl->getValue('bug')['id'];?>
">
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
  </div>
</main>

<?php echo '<script'; ?>
 src="https://cdn.jsdelivr.net/npm/chart.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
>
  const adminEndpoint = (window.APP_BASE_URL || window.location.origin).replace(/\/$/, "") + "/admin_actions.php";
  const csrfToken = document.querySelector("[data-csrf]")?.getAttribute("data-csrf") || "";

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

  // Chart
  const growthLabels = <?php echo json_encode($_smarty_tpl->getValue('admin_user_growth_labels'));?>
;
  const growthData = <?php echo json_encode($_smarty_tpl->getValue('admin_user_growth_data'));?>
;
  const chartEl = document.getElementById("userGrowthChart");
  if (chartEl && window.Chart) {
    new Chart(chartEl, {
      type: "line",
      data: {
        labels: growthLabels,
        datasets: [{
          label: "New Users",
          data: growthData,
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
  }

  // Notifications
  const notifyForm = document.getElementById("notificationForm");
  const notifyAll = document.getElementById("notifyAll");
  if (notifyForm) {
    notifyForm.addEventListener("submit", async (event) => {
      event.preventDefault();
      const formData = new FormData(notifyForm);
      const message = (formData.get("message") || "").toString().trim();
      const sendAll = notifyAll?.checked ? "1" : "0";
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
<?php echo '</script'; ?>
>

</body>
</html>
<?php }
}
