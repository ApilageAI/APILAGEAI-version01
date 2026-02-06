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
            {foreach from=$admin_users item=user}
              <tr class="border-t border-brand-dark/10" data-user-row="{$user.id}">
                <td class="py-2">{$user.id}</td>
                <td class="py-2 font-semibold text-brand-dark">{$user.first_name|escape} {$user.last_name|escape}</td>
                <td class="py-2">{$user.email|escape}</td>
                <td class="py-2">{$user.type|escape}</td>
                <td class="py-2">Rs. {$user.balance|number_format:2}</td>
                <td class="py-2">{$user.reg_date|escape}</td>
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
                  <button class="delete-user-btn rounded-lg border-2 border-brand-dark bg-brand-red text-white px-3 py-1 text-xs font-bold" data-user-id="{$user.id}">Delete</button>
                </td>
              </tr>
            {/foreach}
            {if $admin_users|@count == 0}
              <tr>
                <td colspan="8" class="py-4 text-center text-sm text-brand-dark/60">No users found.</td>
              </tr>
            {/if}
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
            {foreach from=$admin_bug_reports item=bug}
              <tr class="border-t border-brand-dark/10" data-bug-row="{$bug.id}">
                <td class="py-2">{$bug.id}</td>
                <td class="py-2">{$bug.email|escape}</td>
                <td class="py-2 max-w-md">{$bug.problem|escape}</td>
                <td class="py-2">
                  {if $bug.screenshot_path}
                    <a class="text-brand-blue underline" href="{$smarty.const.APP_URL}/{$bug.screenshot_path|escape}" target="_blank">View</a>
                  {else}
                    <span class="text-brand-dark/50">None</span>
                  {/if}
                </td>
                <td class="py-2">{$bug.created_at|escape}</td>
                <td class="py-2">
                  <select class="bug-status-select border-2 border-brand-dark rounded px-2 py-1 text-xs" data-bug-id="{$bug.id}">
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
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
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
  const growthLabels = {$admin_user_growth_labels|json_encode};
  const growthData = {$admin_user_growth_data|json_encode};
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
</script>

</body>
</html>
