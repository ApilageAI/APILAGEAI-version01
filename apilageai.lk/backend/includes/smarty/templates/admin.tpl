{include file="components/head.tpl"}

<main class="min-h-screen bg-brand-gray">
  <div class="container mx-auto px-6 py-10" data-csrf="{$csrf_token}">
    <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
      <div>
        <span class="inline-flex items-center gap-2 rounded-full border-2 border-brand-dark bg-brand-blueLight px-3 py-1 text-xs font-bold uppercase tracking-wider text-brand-dark">
          Admin Console
        </span>
        <h1 class="mt-4 text-3xl md:text-5xl font-display font-black text-brand-dark">Apilage Admin</h1>
        <p class="mt-2 text-brand-dark/70 text-lg font-medium">Manage users, earnings, sessions, and reports.</p>
      </div>
      <div class="flex items-center gap-3">
        <a href="{$smarty.const.APP_URL}/app" class="px-4 py-2 rounded-lg border-2 border-brand-dark bg-white text-brand-dark font-bold shadow-hard-sm hover:shadow-hard transition-all">Back to App</a>
        <a href="{$smarty.const.APP_URL}/auth/logout" class="px-4 py-2 rounded-lg border-2 border-brand-dark bg-brand-red text-white font-bold shadow-hard-sm hover:shadow-hard transition-all">Sign Out</a>
      </div>
    </div>

    <section class="mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
      <div class="rounded-2xl border-2 border-brand-dark bg-white p-6 shadow-hard">
        <p class="text-xs uppercase tracking-wider text-brand-dark/60">Total Users</p>
        <p class="mt-3 text-3xl font-black text-brand-dark">{$admin_stats.total_users}</p>
      </div>
      <div class="rounded-2xl border-2 border-brand-dark bg-white p-6 shadow-hard">
        <p class="text-xs uppercase tracking-wider text-brand-dark/60">New Users (30d)</p>
        <p class="mt-3 text-3xl font-black text-brand-dark">{$admin_stats.new_users_30}</p>
      </div>
      <div class="rounded-2xl border-2 border-brand-dark bg-white p-6 shadow-hard">
        <p class="text-xs uppercase tracking-wider text-brand-dark/60">Active Users Now</p>
        <p class="mt-3 text-3xl font-black text-brand-dark">{$admin_stats.active_users_now}</p>
        <p class="text-xs text-brand-dark/50 mt-1">Last 15 minutes</p>
      </div>
      <div class="rounded-2xl border-2 border-brand-dark bg-white p-6 shadow-hard">
        <p class="text-xs uppercase tracking-wider text-brand-dark/60">Earnings (Paid)</p>
        <p class="mt-3 text-3xl font-black text-brand-dark">Rs. {$admin_stats.earnings_paid|number_format:2}</p>
        <p class="text-xs text-brand-dark/50 mt-1">Pending: Rs. {$admin_stats.earnings_pending|number_format:2}</p>
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
        <form id="notificationForm" method="post" action="{$smarty.const.APP_URL}/admin_actions.php" class="mt-4 space-y-3">
          <input type="hidden" name="action" value="send_notification">
          <input type="hidden" name="csrf_token" value="{$csrf_token}">
          <textarea name="message" rows="4" class="w-full rounded-lg border-2 border-brand-dark p-3 text-sm" placeholder="Write a notification message..."></textarea>
          <label class="flex items-center gap-2 text-sm font-bold text-brand-dark">
            <input type="checkbox" id="notifyAll" name="send_all" value="1" class="h-4 w-4"> Send to all users
          </label>
          <div class="max-h-48 overflow-y-auto border-2 border-brand-dark rounded-lg p-2 bg-brand-gray">
            {foreach from=$admin_users item=user}
              <label class="flex items-center gap-2 text-sm text-brand-dark py-1">
                <input type="checkbox" name="user_ids[]" value="{$user.id}" class="h-4 w-4">
                <span class="font-semibold">{$user.first_name|escape} {$user.last_name|escape}</span>
                <span class="text-xs text-brand-dark/60">({$user.email|escape})</span>
              </label>
            {/foreach}
            {if $admin_users|@count == 0}
              <p class="text-sm text-brand-dark/60">No users found.</p>
            {/if}
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
            {foreach from=$admin_active_sessions item=session}
              <tr class="border-t border-brand-dark/10">
                <td class="py-2 font-semibold text-brand-dark">{$session.first_name|escape} {$session.last_name|escape}</td>
                <td class="py-2">{$session.email|escape}</td>
                <td class="py-2">{$session.last_seen|escape}</td>
                <td class="py-2">{$session.ip|escape}</td>
                <td class="py-2 max-w-xs truncate">{$session.client|escape}</td>
                <td class="py-2">
                  <button class="logout-user-btn rounded-lg border-2 border-brand-dark bg-white px-3 py-1 text-xs font-bold" data-user-id="{$session.user_id}">Log out</button>
                </td>
              </tr>
            {/foreach}
            {if $admin_active_sessions|@count == 0}
              <tr>
                <td colspan="6" class="py-4 text-center text-sm text-brand-dark/60">No active sessions.</td>
              </tr>
            {/if}
          </tbody>
        </table>
      </div>
    </section>

    <section class="mt-10 rounded-2xl border-2 border-brand-dark bg-white p-6 shadow-hard">
      <h2 class="text-xl font-bold text-brand-dark">Users & Onboarding</h2>
      <p class="text-sm text-brand-dark/70 mt-1">All user records with onboarding details.</p>
      <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
        <form method="get" class="flex flex-wrap items-center gap-2">
          <input type="text" name="user_search" value="{$admin_user_search|escape}" placeholder="Search users..." class="rounded-lg border-2 border-brand-dark px-3 py-2 text-sm">
          <select name="user_sort" class="rounded-lg border-2 border-brand-dark bg-white px-3 py-2 text-sm font-semibold text-brand-dark">
            <option value="reg_desc" {if $admin_user_sort == 'reg_desc'}selected{/if}>Newest registrations</option>
            <option value="login_desc" {if $admin_user_sort == 'login_desc'}selected{/if}>Latest logins</option>
            <option value="login_asc" {if $admin_user_sort == 'login_asc'}selected{/if}>Oldest logins</option>
            <option value="balance_desc" {if $admin_user_sort == 'balance_desc'}selected{/if}>Highest balance</option>
            <option value="balance_asc" {if $admin_user_sort == 'balance_asc'}selected{/if}>Lowest balance</option>
          </select>
          <input type="hidden" name="txn_search" value="{$admin_txn_search|escape}">
          <button type="submit" class="rounded-lg border-2 border-brand-dark bg-brand-blueLight px-4 py-2 text-sm font-bold text-brand-dark shadow-hard-sm hover:shadow-hard">Apply</button>
        </form>
        <div class="text-xs text-brand-dark/60">Showing {$admin_user_pagination.total} users</div>
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
            {foreach from=$admin_users item=user}
              <tr class="border-t border-brand-dark/10" data-user-row="{$user.id}">
                <td class="py-2">{$user.id}</td>
                <td class="py-2 font-semibold text-brand-dark">{$user.first_name|escape} {$user.last_name|escape}</td>
                <td class="py-2">{$user.email|escape}</td>
                <td class="py-2">{$user.type|escape}</td>
                <td class="py-2">Rs. {$user.balance|number_format:2}</td>
                <td class="py-2">{$user.reg_date|escape}</td>
                <td class="py-2">
                  {if $user.last_login}
                    {$user.last_login|escape}
                  {else}
                    <span class="text-brand-dark/50">-</span>
                  {/if}
                </td>
                <td class="py-2">
                  <details class="text-xs text-brand-dark/70">
                    <summary class="cursor-pointer font-semibold text-brand-dark">View details</summary>
                    <div class="mt-2 space-y-1">
                      <div><span class="font-semibold">Phone:</span> {$user.phone|escape}</div>
                      <div><span class="font-semibold">Email verified:</span> {$user.email_verified|escape}</div>
                      <div><span class="font-semibold">Image:</span> {$user.image|escape}</div>
                      <div><span class="font-semibold">Memory:</span> {$user.memory|escape}</div>
                      <div><span class="font-semibold">Subscription:</span> {$user.subscription_status|escape}</div>
                      <div><span class="font-semibold">Onboard complete:</span> {$user.onboard_complete|escape}</div>
                      <div><span class="font-semibold">Failed logins:</span> {$user.failed_login_attempts|escape}</div>
                      <div><span class="font-semibold">Locked until:</span> {$user.locked_until|escape}</div>
                      <div class="pt-2 font-semibold text-brand-dark">Onboarding</div>
                      <div><span class="font-semibold">School:</span> {$user.school|escape}</div>
                      <div><span class="font-semibold">Not student:</span> {$user.not_student|escape}</div>
                      <div><span class="font-semibold">Interests:</span> {$user.interests|escape}</div>
                      <div><span class="font-semibold">Preference:</span> {$user.preference|escape}</div>
                      <div><span class="font-semibold">Created:</span> {$user.onboarding_created_at|escape}</div>
                      <div><span class="font-semibold">Updated:</span> {$user.onboarding_updated_at|escape}</div>
                    </div>
                  </details>
                </td>
                <td class="py-2 flex flex-col gap-2">
                  <button class="logout-user-btn rounded-lg border-2 border-brand-dark bg-white px-3 py-1 text-xs font-bold" data-user-id="{$user.id}">Log out</button>
                  <button class="edit-user-btn rounded-lg border-2 border-brand-dark bg-brand-blueLight px-3 py-1 text-xs font-bold" data-user-id="{$user.id}">Edit</button>
                  <button class="delete-user-btn rounded-lg border-2 border-brand-dark bg-brand-red text-white px-3 py-1 text-xs font-bold" data-user-id="{$user.id}">Delete</button>
                </td>
              </tr>
              <tr class="border-t border-brand-dark/10" data-user-edit-row="{$user.id}" style="display:none;">
                <td colspan="9" class="py-3">
                  <form class="user-edit-form space-y-3" data-user-id="{$user.id}">
                    <div class="text-xs font-bold uppercase tracking-wide text-brand-dark/60">Profile</div>
                    <div class="grid gap-3 md:grid-cols-3">
                      <label class="text-xs font-semibold text-brand-dark">First name
                        <input name="first_name" value="{$user.first_name|escape}" class="mt-1 w-full rounded-lg border-2 border-brand-dark px-3 py-2 text-sm">
                      </label>
                      <label class="text-xs font-semibold text-brand-dark">Last name
                        <input name="last_name" value="{$user.last_name|escape}" class="mt-1 w-full rounded-lg border-2 border-brand-dark px-3 py-2 text-sm">
                      </label>
                      <label class="text-xs font-semibold text-brand-dark">Email
                        <input name="email" value="{$user.email|escape}" class="mt-1 w-full rounded-lg border-2 border-brand-dark px-3 py-2 text-sm">
                      </label>
                      <label class="text-xs font-semibold text-brand-dark">Phone
                        <input name="phone" value="{$user.phone|escape}" class="mt-1 w-full rounded-lg border-2 border-brand-dark px-3 py-2 text-sm">
                      </label>
                      <label class="text-xs font-semibold text-brand-dark">Type
                        <select name="type" class="mt-1 w-full rounded-lg border-2 border-brand-dark bg-white px-3 py-2 text-sm">
                          <option value="1" {if $user.type == '1'}selected{/if}>User</option>
                          <option value="2" {if $user.type == '2'}selected{/if}>Admin</option>
                          <option value="3" {if $user.type == '3'}selected{/if}>Super Admin</option>
                        </select>
                      </label>
                      <label class="text-xs font-semibold text-brand-dark">Balance
                        <input name="balance" value="{$user.balance|escape}" class="mt-1 w-full rounded-lg border-2 border-brand-dark px-3 py-2 text-sm">
                      </label>
                      <label class="text-xs font-semibold text-brand-dark">Email verified
                        <select name="email_verified" class="mt-1 w-full rounded-lg border-2 border-brand-dark bg-white px-3 py-2 text-sm">
                          <option value="0" {if $user.email_verified == 0}selected{/if}>No</option>
                          <option value="1" {if $user.email_verified == 1}selected{/if}>Yes</option>
                        </select>
                      </label>
                      <label class="text-xs font-semibold text-brand-dark">Subscription
                        <select name="subscription_status" class="mt-1 w-full rounded-lg border-2 border-brand-dark bg-white px-3 py-2 text-sm">
                          <option value="0" {if $user.subscription_status == 0}selected{/if}>Inactive</option>
                          <option value="1" {if $user.subscription_status == 1}selected{/if}>Active</option>
                        </select>
                      </label>
                      <label class="text-xs font-semibold text-brand-dark">Onboard complete
                        <select name="onboard_complete" class="mt-1 w-full rounded-lg border-2 border-brand-dark bg-white px-3 py-2 text-sm">
                          <option value="0" {if $user.onboard_complete == 0}selected{/if}>No</option>
                          <option value="1" {if $user.onboard_complete == 1}selected{/if}>Yes</option>
                        </select>
                      </label>
                      <label class="text-xs font-semibold text-brand-dark">Failed logins
                        <input name="failed_login_attempts" value="{$user.failed_login_attempts|escape}" class="mt-1 w-full rounded-lg border-2 border-brand-dark px-3 py-2 text-sm">
                      </label>
                      <label class="text-xs font-semibold text-brand-dark">Locked until
                        <input name="locked_until" value="{$user.locked_until|escape}" placeholder="YYYY-MM-DD HH:MM:SS" class="mt-1 w-full rounded-lg border-2 border-brand-dark px-3 py-2 text-sm">
                      </label>
                      <label class="text-xs font-semibold text-brand-dark md:col-span-3">Image path
                        <input name="image" value="{$user.image|escape}" class="mt-1 w-full rounded-lg border-2 border-brand-dark px-3 py-2 text-sm">
                      </label>
                      <label class="text-xs font-semibold text-brand-dark md:col-span-3">Memory
                        <textarea name="memory" rows="3" class="mt-1 w-full rounded-lg border-2 border-brand-dark px-3 py-2 text-sm">{$user.memory|escape}</textarea>
                      </label>
                    </div>
                    <div class="text-xs font-bold uppercase tracking-wide text-brand-dark/60">Onboarding</div>
                    <div class="grid gap-3 md:grid-cols-3">
                      <label class="text-xs font-semibold text-brand-dark">School
                        <input name="school" value="{$user.school|escape}" class="mt-1 w-full rounded-lg border-2 border-brand-dark px-3 py-2 text-sm">
                      </label>
                      <label class="text-xs font-semibold text-brand-dark">Not student
                        <select name="not_student" class="mt-1 w-full rounded-lg border-2 border-brand-dark bg-white px-3 py-2 text-sm">
                          <option value="0" {if $user.not_student == 0}selected{/if}>No</option>
                          <option value="1" {if $user.not_student == 1}selected{/if}>Yes</option>
                        </select>
                      </label>
                      <label class="text-xs font-semibold text-brand-dark">Preference
                        <select name="preference" class="mt-1 w-full rounded-lg border-2 border-brand-dark bg-white px-3 py-2 text-sm">
                          <option value="">None</option>
                          <option value="friendly" {if $user.preference == 'friendly'}selected{/if}>Friendly</option>
                          <option value="educational" {if $user.preference == 'educational'}selected{/if}>Educational</option>
                          <option value="explanatory" {if $user.preference == 'explanatory'}selected{/if}>Explanatory</option>
                          <option value="concise" {if $user.preference == 'concise'}selected{/if}>Concise</option>
                        </select>
                      </label>
                      <label class="text-xs font-semibold text-brand-dark md:col-span-3">Interests
                        <textarea name="interests" rows="2" class="mt-1 w-full rounded-lg border-2 border-brand-dark px-3 py-2 text-sm">{$user.interests|escape}</textarea>
                      </label>
                    </div>
                    <div class="flex flex-wrap gap-2">
                      <button type="submit" class="rounded-lg border-2 border-brand-dark bg-brand-blueLight px-4 py-2 text-sm font-bold text-brand-dark shadow-hard-sm hover:shadow-hard">Save changes</button>
                      <button type="button" class="cancel-user-edit-btn rounded-lg border-2 border-brand-dark bg-white px-4 py-2 text-sm font-bold" data-user-id="{$user.id}">Close</button>
                    </div>
                  </form>
                </td>
              </tr>
            {/foreach}
            {if $admin_users|@count == 0}
              <tr>
                <td colspan="9" class="py-4 text-center text-sm text-brand-dark/60">No users found.</td>
              </tr>
            {/if}
          </tbody>
        </table>
      </div>
      {if $admin_user_pagination.total_pages > 1}
        <div class="mt-4 flex flex-wrap gap-2">
          {section name=pg loop=$admin_user_pagination.total_pages}
            {assign var=pageNum value=$smarty.section.pg.index+1}
            <a href="{$admin_user_page_base}user_page={$pageNum}"
               class="rounded-lg border-2 border-brand-dark px-3 py-1 text-sm font-bold {if $pageNum == $admin_user_pagination.page}bg-brand-blueLight text-brand-dark{else}bg-white text-brand-dark{/if}">
              {$pageNum}
            </a>
          {/section}
        </div>
      {/if}
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
            {foreach from=$admin_bug_reports item=bug}
              <tr class="border-t border-brand-dark/10" data-bug-row="{$bug.id}">
                <td class="py-2">{$bug.id}</td>
                <td class="py-2">{$bug.email|escape}</td>
                <td class="py-2 max-w-md">{$bug.problem|escape}</td>
                <td class="py-2">
                  {if $bug.screenshot_url}
                    <a class="text-brand-blue underline" href="{$bug.screenshot_url|escape}" target="_blank">View</a>
                  {else}
                    <span class="text-brand-dark/50">None</span>
                  {/if}
                </td>
                <td class="py-2">{$bug.created_at|escape}</td>
                <td class="py-2">
                  <select class="bug-status-select border-2 border-brand-dark rounded px-2 py-1 text-xs" data-bug-id="{$bug.id}" name="bug_status" aria-label="Bug status">
                    <option value="open" {if $bug.status == 'open'}selected{/if}>Open</option>
                    <option value="fixed" {if $bug.status == 'fixed'}selected{/if}>Fixed</option>
                  </select>
                </td>
                <td class="py-2">
                  <button class="delete-bug-btn rounded-lg border-2 border-brand-dark bg-white px-3 py-1 text-xs font-bold" data-bug-id="{$bug.id}">Delete</button>
                </td>
              </tr>
            {/foreach}
            {if $admin_bug_reports|@count == 0}
              <tr>
                <td colspan="7" class="py-4 text-center text-sm text-brand-dark/60">No bug reports.</td>
              </tr>
            {/if}
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
          <p class="mt-2 text-2xl font-black text-brand-dark">Rs. {$admin_stats.earnings_total|number_format:2}</p>
        </div>
        <div class="rounded-xl border-2 border-brand-dark bg-brand-blueLight p-4">
          <p class="text-xs uppercase tracking-wider text-brand-dark/70">Paid</p>
          <p class="mt-2 text-2xl font-black text-brand-dark">Rs. {$admin_stats.earnings_paid|number_format:2}</p>
        </div>
        <div class="rounded-xl border-2 border-brand-dark bg-brand-blueLight p-4">
          <p class="text-xs uppercase tracking-wider text-brand-dark/70">Pending</p>
          <p class="mt-2 text-2xl font-black text-brand-dark">Rs. {$admin_stats.earnings_pending|number_format:2}</p>
        </div>
      </div>
      <form method="get" class="mt-6 flex flex-wrap items-center gap-2">
        <input type="text" name="txn_search" value="{$admin_txn_search|escape}" placeholder="Search transactions..." class="rounded-lg border-2 border-brand-dark px-3 py-2 text-sm">
        <input type="hidden" name="user_search" value="{$admin_user_search|escape}">
        <input type="hidden" name="user_sort" value="{$admin_user_sort|escape}">
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
            {foreach from=$admin_transactions item=txn}
              <tr class="border-t border-brand-dark/10">
                <td class="py-2">{$txn.invoice_id|escape}</td>
                <td class="py-2">{$txn.first_name|escape} {$txn.last_name|escape}</td>
                <td class="py-2">{$txn.email|escape}</td>
                <td class="py-2">Rs. {$txn.amount|number_format:2}</td>
                <td class="py-2">
                  {if $txn.paid == 1}
                    <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-1 text-xs font-bold text-green-800">Paid</span>
                  {else}
                    <span class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-1 text-xs font-bold text-yellow-800">Pending</span>
                  {/if}
                </td>
                <td class="py-2">{$txn.created_at|escape}</td>
              </tr>
            {/foreach}
            {if $admin_transactions|@count == 0}
              <tr>
                <td colspan="6" class="py-4 text-center text-sm text-brand-dark/60">No transactions found.</td>
              </tr>
            {/if}
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
            {foreach from=$admin_free_user_daily_usage item=usage}
              <tr class="border-t border-brand-dark/10" data-user-id="{$usage.user_id|escape}" data-date="{$usage.date|escape}" data-window-id="{$usage.window_id|escape}">
                <td class="py-2">{$usage.user_id|escape}</td>
                <td class="py-2">
                  <div class="font-semibold text-brand-dark">{$usage.first_name|escape} {$usage.last_name|escape}</div>
                  <div class="text-xs text-brand-dark/60">{$usage.email|escape}</div>
                </td>
                <td class="py-2">{$usage.date|escape}</td>
                <td class="py-2">
                  <input data-field="messages_used" name="messages_used" aria-label="Messages used" value="{$usage.messages_used|escape}" class="w-24 rounded-lg border-2 border-brand-dark px-2 py-1 text-xs">
                </td>
                <td class="py-2">
                  <input data-field="image_uploads_used" name="image_uploads_used" aria-label="Image uploads used" value="{$usage.image_uploads_used|escape}" class="w-28 rounded-lg border-2 border-brand-dark px-2 py-1 text-xs">
                </td>
                <td class="py-2">
                  <input data-field="file_uploads_used" name="file_uploads_used" aria-label="File uploads used" value="{$usage.file_uploads_used|escape}" class="w-24 rounded-lg border-2 border-brand-dark px-2 py-1 text-xs">
                </td>
                <td class="py-2">
                  <input data-field="image_generations_used" name="image_generations_used" aria-label="Image generations used" value="{$usage.image_generations_used|escape}" class="w-28 rounded-lg border-2 border-brand-dark px-2 py-1 text-xs">
                </td>
                <td class="py-2">{$usage.window_id|escape}</td>
                <td class="py-2">
                  <button class="save-free-usage-btn rounded-lg border-2 border-brand-dark bg-brand-blueLight px-3 py-1 text-xs font-bold">Save</button>
                </td>
              </tr>
            {/foreach}
            {if $admin_free_user_daily_usage|@count == 0}
              <tr>
                <td colspan="9" class="py-4 text-center text-sm text-brand-dark/60">No free user usage records found.</td>
              </tr>
            {/if}
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
            {foreach from=$admin_trial_abuse_tracking item=row}
              <tr class="border-t border-brand-dark/10" data-trial-abuse-row="{$row.id}">
                <td class="py-2">{$row.id}</td>
                <td class="py-2">
                  <input data-field="user_id" name="user_id" aria-label="User ID" value="{$row.user_id|escape}" class="w-24 rounded-lg border-2 border-brand-dark px-2 py-1 text-xs">
                </td>
                <td class="py-2">
                  <div class="font-semibold text-brand-dark">{$row.first_name|escape} {$row.last_name|escape}</div>
                  <div class="text-xs text-brand-dark/60">{$row.email|escape}</div>
                </td>
                <td class="py-2">
                  <input data-field="ip_address" name="ip_address" aria-label="IP address" value="{$row.ip_address|escape}" class="w-36 rounded-lg border-2 border-brand-dark px-2 py-1 text-xs">
                </td>
                <td class="py-2">
                  <input data-field="device_fingerprint" name="device_fingerprint" aria-label="Device fingerprint" value="{$row.device_fingerprint|escape}" class="w-48 rounded-lg border-2 border-brand-dark px-2 py-1 text-xs">
                </td>
                <td class="py-2">
                  <input data-field="trial_start_date" name="trial_start_date" aria-label="Trial start date" value="{$row.trial_start_date|escape}" placeholder="YYYY-MM-DD" class="w-32 rounded-lg border-2 border-brand-dark px-2 py-1 text-xs">
                </td>
                <td class="py-2">
                  <input data-field="trial_end_date" name="trial_end_date" aria-label="Trial end date" value="{$row.trial_end_date|escape}" placeholder="YYYY-MM-DD" class="w-32 rounded-lg border-2 border-brand-dark px-2 py-1 text-xs">
                </td>
                <td class="py-2">
                  <button class="save-trial-abuse-btn rounded-lg border-2 border-brand-dark bg-brand-blueLight px-3 py-1 text-xs font-bold" data-id="{$row.id}">Save</button>
                </td>
              </tr>
            {/foreach}
            {if $admin_trial_abuse_tracking|@count == 0}
              <tr>
                <td colspan="8" class="py-4 text-center text-sm text-brand-dark/60">No trial abuse records found.</td>
              </tr>
            {/if}
          </tbody>
        </table>
      </div>
    </section>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{$smarty.const.APP_URL}/assets/scripts/chart-lite.js?V={get_hash_number()}"></script>
<script>
  (function () {
    var growthLabels = {$admin_user_growth_labels|json_encode};
    var growthData = {$admin_user_growth_data|json_encode};
    var growthSeries = {$admin_user_growth_series|json_encode};
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
</script>
<script>
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
</script>

</body>
</html>
