{include file="components/head.tpl"}

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

  <main id="developer-console" data-auth="{if $is_logged_in}1{else}0{/if}" data-api-base="{$smarty.const.APP_URL}" class="pt-28 pb-20">
    <section class="container mx-auto px-6">
      <div class="max-w-6xl mx-auto">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-blueLight border-2 border-brand-dark text-brand-dark text-xs font-bold mb-6 uppercase tracking-wider">Developer Console</div>
        <h1 class="text-4xl md:text-6xl font-display font-black text-brand-dark mb-4">ApilageAI Developer Platform &amp; API Hub</h1>
        <p class="text-brand-dark/70 text-base md:text-lg font-medium">Last updated: February 19, 2026</p>
        <p class="text-brand-dark/70 text-base md:text-lg font-medium mt-4">
          Manage API keys, lock down allowed domains, monitor usage, and ship streaming or direct responses to your own platform.
          Default ApilageAI system text is always applied and cannot be viewed or changed.
          For advanced guides, visit the <a href="{$smarty.const.APP_URL}/developer-api-documentation" class="text-brand-blue underline">Developer API Documentation</a>.
        </p>
      </div>
    </section>

    <section id="manage-api" class="container mx-auto px-6 mt-16">
      <div class="max-w-6xl mx-auto">
        <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-4">Manage API&rsquo;s</h2>
        <p class="text-brand-dark/70 text-base font-medium mb-8">
          Create, delete, enable/disable API keys, add allowed domains, and view usage for each key.
        </p>

        {if $is_logged_in}
          <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
              <div class="p-6 border-2 border-brand-dark rounded-2xl bg-white shadow-hard-sm">
                <h3 class="text-xl font-bold text-brand-dark mb-4">Create New API Key</h3>
                <p class="text-xs text-brand-dark/60 mb-4">Maximum 10 API keys per account.</p>
                <form id="create-api-key-form" class="grid md:grid-cols-3 gap-4">
                  <div class="md:col-span-2">
                    <label class="text-sm font-bold text-brand-dark/70">Key Name</label>
                    <input id="api-key-name" type="text" placeholder="My LMS Integration" class="mt-2 w-full border-2 border-brand-dark rounded-xl px-4 py-3 text-sm font-medium" maxlength="80" required />
                  </div>
                  <div class="flex items-end">
                    <button type="submit" class="btn-primary w-full">Generate Key</button>
                  </div>
                </form>
                <div id="new-api-key-panel" class="hidden mt-5 border-2 border-brand-dark rounded-xl p-4 bg-brand-blueLight">
                  <div class="text-xs font-bold text-brand-dark/70 uppercase tracking-wider mb-2">New API Key (copy now)</div>
                  <div class="flex flex-col md:flex-row md:items-center gap-3">
                    <code id="new-api-key-value" class="flex-1 bg-white border-2 border-brand-dark rounded-lg px-3 py-2 text-sm font-mono text-brand-dark break-all"></code>
                    <button id="copy-new-api-key" type="button" class="btn-outline !py-2 !px-4">Copy</button>
                  </div>
                  <p class="text-xs text-brand-dark/60 mt-2">
                    For security, the full key is shown only once.
                  </p>
                </div>
              </div>

              <div class="p-6 border-2 border-brand-dark rounded-2xl bg-white shadow-hard-sm">
                <h3 class="text-xl font-bold text-brand-dark mb-4">Your API Keys</h3>
                <div class="overflow-x-auto">
                  <table class="w-full border-2 border-brand-dark text-sm">
                    <thead class="bg-brand-blueLight">
                      <tr>
                        <th class="text-left px-3 py-2 border-b-2 border-brand-dark">Name</th>
                        <th class="text-left px-3 py-2 border-b-2 border-brand-dark">Key</th>
                        <th class="text-left px-3 py-2 border-b-2 border-brand-dark">Status</th>
                        <th class="text-left px-3 py-2 border-b-2 border-brand-dark">Usage (30d)</th>
                        <th class="text-left px-3 py-2 border-b-2 border-brand-dark">Last Used</th>
                        <th class="text-left px-3 py-2 border-b-2 border-brand-dark">Actions</th>
                      </tr>
                    </thead>
                    <tbody id="api-keys-body">
                      <tr>
                        <td colspan="6" class="px-3 py-6 text-center text-brand-dark/60">Loading API keys...</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <p class="text-xs text-brand-dark/60 mt-3">
                  Default allowed domains: localhost, 127.0.0.1, and {$smarty.const.APP_URL|regex_replace:"/^https?:\/\//":""}. You can update them anytime (max 5 domains per key).
                </p>
              </div>
            </div>

            <div class="space-y-6">
              <div class="p-6 border-2 border-brand-dark rounded-2xl bg-white shadow-hard-sm">
                <h3 class="text-xl font-bold text-brand-dark mb-4">Balance &amp; Recharge</h3>
                <p class="text-sm font-medium text-brand-dark/70">Current Balance</p>
                <div class="text-2xl font-black text-brand-dark mt-1">LKR <span id="developer-balance-value">0.00</span></div>

                <div class="mt-5">
                  <label class="text-sm font-bold text-brand-dark/70">Recharge Amount (LKR)</label>
                  <input id="recharge-amount" type="number" min="200" max="20000" step="100" placeholder="200 - 20000" class="mt-2 w-full border-2 border-brand-dark rounded-xl px-4 py-3 text-sm font-medium" />
                  <div class="flex flex-wrap gap-2 mt-3">
                    <button type="button" class="btn-outline !py-1 !px-3 text-xs" data-recharge-amount="200">200</button>
                    <button type="button" class="btn-outline !py-1 !px-3 text-xs" data-recharge-amount="500">500</button>
                    <button type="button" class="btn-outline !py-1 !px-3 text-xs" data-recharge-amount="1000">1000</button>
                    <button type="button" class="btn-outline !py-1 !px-3 text-xs" data-recharge-amount="2000">2000</button>
                    <button type="button" class="btn-outline !py-1 !px-3 text-xs" data-recharge-amount="5000">5000</button>
                    <button type="button" class="btn-outline !py-1 !px-3 text-xs" data-recharge-amount="10000">10000</button>
                    <button type="button" class="btn-outline !py-1 !px-3 text-xs" data-recharge-amount="20000">20000</button>
                  </div>
                  <button id="recharge-button" type="button" class="btn-primary w-full mt-4">Recharge Now</button>
                  <p class="text-xs text-brand-dark/60 mt-3">
                    Note: Your credit recharging is pay as you go, no need recharge monthly just recrage and use till all crdits spends, All recharged credits are valid for AI chats also.
                  </p>
                </div>
              </div>
              <div class="p-6 border-2 border-brand-dark rounded-2xl bg-white shadow-hard-sm">
                <h3 class="text-xl font-bold text-brand-dark mb-4">Key Settings</h3>
                <label class="text-sm font-bold text-brand-dark/70">Select Key</label>
                <select id="api-key-select" class="mt-2 w-full border-2 border-brand-dark rounded-xl px-3 py-2 text-sm font-medium">
                  <option value="">Select an API key</option>
                </select>

                <div class="mt-5">
                  <label class="text-sm font-bold text-brand-dark/70">Allowed Domains</label>
                  <textarea id="api-key-domains" rows="5" placeholder="example.com&#10;app.example.com&#10;localhost" class="mt-2 w-full border-2 border-brand-dark rounded-xl px-3 py-2 text-sm font-medium"></textarea>
                  <p class="text-xs text-brand-dark/60 mt-2">Max 5 domains per key.</p>
                  <button id="save-domains" type="button" class="btn-outline w-full mt-3">Save Domains</button>
                </div>

                <div class="mt-5">
                  <label class="text-sm font-bold text-brand-dark/70">Custom System Text</label>
                  <textarea id="api-key-system" rows="6" placeholder="You are an assistant for our school platform..." class="mt-2 w-full border-2 border-brand-dark rounded-xl px-3 py-2 text-sm font-medium"></textarea>
                  <button id="save-system" type="button" class="btn-outline w-full mt-3">Save System Text</button>
                  <p id="system-save-status" class="text-xs text-brand-dark/60 mt-2 hidden"></p>
                  <p class="text-xs text-brand-dark/60 mt-2">
                    Your custom system text is added on top of ApilageAI&rsquo;s default system text, which cannot be viewed or changed.
                  </p>
                </div>
              </div>

              <div class="p-6 border-2 border-brand-dark rounded-2xl bg-white shadow-hard-sm">
                <h3 class="text-xl font-bold text-brand-dark mb-4">Usage Overview</h3>
                <div id="api-usage-summary" class="space-y-2 text-sm text-brand-dark/70">
                  <p>Select a key to view usage.</p>
                </div>
              </div>
            </div>
          </div>
        {else}
          <div class="p-6 border-2 border-brand-dark rounded-2xl bg-white shadow-hard-sm">
            <h3 class="text-xl font-bold text-brand-dark mb-2">Sign in to manage API keys</h3>
            <p class="text-brand-dark/70 text-sm mb-4">Create API keys, manage domains, and view usage after signing in.</p>
            <a href="{$smarty.const.APP_URL}/auth/login" class="btn-primary">Login</a>
          </div>
        {/if}
      </div>
    </section>

    <section id="playground" class="container mx-auto px-6 mt-16">
      <div class="max-w-6xl mx-auto">
        <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-4">Playground</h2>
        <p class="text-brand-dark/70 text-base font-medium mb-6">
          Test streaming or direct responses before shipping to your platform. Playground usage is billed at the same pricing as production.
        </p>
        <div class="grid lg:grid-cols-2 gap-6">
          <div class="p-6 border-2 border-brand-dark rounded-2xl bg-white shadow-hard-sm space-y-4">
            <div>
              <label class="text-sm font-bold text-brand-dark/70">API Key</label>
              <input id="playground-key" type="text" placeholder="apk_..." class="mt-2 w-full border-2 border-brand-dark rounded-xl px-4 py-3 text-sm font-medium" />
            </div>
            <div>
              <label class="text-sm font-bold text-brand-dark/70">Model</label>
              <select id="playground-model" class="mt-2 w-full border-2 border-brand-dark rounded-xl px-3 py-2 text-sm font-medium">
                <option value="free">free</option>
                <option value="pro">pro</option>
                <option value="super">super</option>
                <option value="master">master</option>
                <option value="loard">loard</option>
              </select>
            </div>
            <div>
              <label class="text-sm font-bold text-brand-dark/70">System Text (Optional)</label>
              <textarea id="playground-system" rows="3" placeholder="You are an assistant for our app..." class="mt-2 w-full border-2 border-brand-dark rounded-xl px-3 py-2 text-sm font-medium"></textarea>
            </div>
            <div>
              <label class="text-sm font-bold text-brand-dark/70">Prompt</label>
              <textarea id="playground-prompt" rows="5" placeholder="Explain Newton's laws in simple terms." class="mt-2 w-full border-2 border-brand-dark rounded-xl px-3 py-2 text-sm font-medium"></textarea>
            </div>
            <label class="flex items-center gap-3 text-sm font-bold text-brand-dark/70">
              <input id="playground-stream" type="checkbox" class="w-4 h-4 border-2 border-brand-dark" />
              Stream response
            </label>
            <button id="playground-run" class="btn-primary w-full">Run Playground</button>
          </div>

          <div class="p-6 border-2 border-brand-dark rounded-2xl bg-white shadow-hard-sm">
            <h3 class="text-lg font-bold text-brand-dark mb-3">Output</h3>
            <pre id="playground-output" class="bg-brand-gray border-2 border-brand-dark rounded-xl p-4 text-sm font-mono text-brand-dark whitespace-pre-wrap min-h-[320px]">Response will appear here.</pre>
            <div id="playground-usage" class="mt-4 text-xs text-brand-dark/70"></div>
          </div>
        </div>
      </div>
    </section>

    <section id="examples" class="container mx-auto px-6 mt-16">
      <div class="max-w-6xl mx-auto">
        <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-4">Code Examples</h2>
        <p class="text-sm text-brand-dark/60 mb-3">Base URL: {$smarty.const.APP_URL}</p>
        <div class="flex flex-wrap gap-2 mb-4" id="code-tabs">
          <button class="btn-outline !py-2 !px-4" data-tab="python">Python</button>
          <button class="btn-outline !py-2 !px-4" data-tab="js">JS</button>
          <button class="btn-outline !py-2 !px-4" data-tab="node">Node</button>
          <button class="btn-outline !py-2 !px-4" data-tab="php">PHP</button>
          <button class="btn-outline !py-2 !px-4" data-tab="react">React</button>
        </div>

        <div class="border-2 border-brand-dark rounded-2xl bg-white shadow-hard-sm p-4 space-y-4">
          <div class="hidden" data-panel="python">
            <div class="bg-[#0b1120] rounded-2xl border-2 border-brand-dark shadow-hard p-4 text-gray-200">
              <div class="flex items-center justify-between gap-4 border-b border-slate-700 pb-3 mb-4">
                <div class="flex items-center gap-3">
                  <div class="flex gap-2">
                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                    <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                  </div>
                  <span class="text-xs font-bold uppercase tracking-widest text-brand-blue">Python</span>
                </div>
                <button type="button" class="btn-outline !py-1 !px-3 !text-xs" data-copy-target="dev-code-python">Copy</button>
              </div>
              <div class="bg-[#0f172a] border border-slate-700 rounded-xl p-4 overflow-x-auto">
                <ol class="list-decimal list-inside space-y-1 text-xs md:text-sm font-mono text-slate-200">
{literal}
                  <li>import requests</li>
                  <li></li>
                  <li>API_KEY = "YOUR_API_KEY"</li>
                  <li>BASE_URL = "BASE_URL"</li>
                  <li></li>
                  <li>payload = {</li>
                  <li>&nbsp;&nbsp;"prompt": "Explain Newton\u2019s laws",</li>
                  <li>&nbsp;&nbsp;"model": "pro",</li>
                  <li>&nbsp;&nbsp;"system": "You are a tutor."</li>
                  <li>}</li>
                  <li></li>
                  <li>resp = requests.post(</li>
                  <li>&nbsp;&nbsp;f"{BASE_URL}/api/developer/text",</li>
                  <li>&nbsp;&nbsp;headers={"X-API-Key": API_KEY},</li>
                  <li>&nbsp;&nbsp;json=payload</li>
                  <li>)</li>
                  <li>print(resp.json()["text"])</li>
{/literal}
                </ol>
              </div>
            </div>
            <textarea id="dev-code-python" class="sr-only">{literal}import requests

API_KEY = "YOUR_API_KEY"
BASE_URL = "BASE_URL"

payload = {
    "prompt": "Explain Newton\u2019s laws",
    "model": "pro",
    "system": "You are a tutor."
}

resp = requests.post(
    f"{BASE_URL}/api/developer/text",
    headers={"X-API-Key": API_KEY},
    json=payload
)
print(resp.json()["text"]){/literal}</textarea>
          </div>

          <div data-panel="js">
            <div class="bg-[#0b1120] rounded-2xl border-2 border-brand-dark shadow-hard p-4 text-gray-200">
              <div class="flex items-center justify-between gap-4 border-b border-slate-700 pb-3 mb-4">
                <div class="flex items-center gap-3">
                  <div class="flex gap-2">
                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                    <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                  </div>
                  <span class="text-xs font-bold uppercase tracking-widest text-brand-blue">JavaScript</span>
                </div>
                <button type="button" class="btn-outline !py-1 !px-3 !text-xs" data-copy-target="dev-code-js">Copy</button>
              </div>
              <div class="bg-[#0f172a] border border-slate-700 rounded-xl p-4 overflow-x-auto">
                <ol class="list-decimal list-inside space-y-1 text-xs md:text-sm font-mono text-slate-200">
{literal}
                  <li>const API_KEY = "YOUR_API_KEY";</li>
                  <li>const BASE_URL = "BASE_URL";</li>
                  <li></li>
                  <li>const resp = await fetch(`${BASE_URL}/api/developer/text`, {</li>
                  <li>&nbsp;&nbsp;method: "POST",</li>
                  <li>&nbsp;&nbsp;headers: {</li>
                  <li>&nbsp;&nbsp;&nbsp;&nbsp;"Content-Type": "application/json",</li>
                  <li>&nbsp;&nbsp;&nbsp;&nbsp;"X-API-Key": API_KEY</li>
                  <li>&nbsp;&nbsp;},</li>
                  <li>&nbsp;&nbsp;body: JSON.stringify({</li>
                  <li>&nbsp;&nbsp;&nbsp;&nbsp;prompt: "Explain Newton\u2019s laws",</li>
                  <li>&nbsp;&nbsp;&nbsp;&nbsp;model: "pro",</li>
                  <li>&nbsp;&nbsp;&nbsp;&nbsp;system: "You are a tutor."</li>
                  <li>&nbsp;&nbsp;})</li>
                  <li>});</li>
                  <li></li>
                  <li>const data = await resp.json();</li>
                  <li>console.log(data.text);</li>
{/literal}
                </ol>
              </div>
            </div>
            <textarea id="dev-code-js" class="sr-only">{literal}const API_KEY = "YOUR_API_KEY";
const BASE_URL = "BASE_URL";

const resp = await fetch(`${BASE_URL}/api/developer/text`, {
  method: "POST",
  headers: {
    "Content-Type": "application/json",
    "X-API-Key": API_KEY
  },
  body: JSON.stringify({
    prompt: "Explain Newton\u2019s laws",
    model: "pro",
    system: "You are a tutor."
  })
});

const data = await resp.json();
console.log(data.text);{/literal}</textarea>
          </div>

          <div class="hidden" data-panel="node">
            <div class="bg-[#0b1120] rounded-2xl border-2 border-brand-dark shadow-hard p-4 text-gray-200">
              <div class="flex items-center justify-between gap-4 border-b border-slate-700 pb-3 mb-4">
                <div class="flex items-center gap-3">
                  <div class="flex gap-2">
                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                    <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                  </div>
                  <span class="text-xs font-bold uppercase tracking-widest text-brand-blue">Node.js</span>
                </div>
                <button type="button" class="btn-outline !py-1 !px-3 !text-xs" data-copy-target="dev-code-node">Copy</button>
              </div>
              <div class="bg-[#0f172a] border border-slate-700 rounded-xl p-4 overflow-x-auto">
                <ol class="list-decimal list-inside space-y-1 text-xs md:text-sm font-mono text-slate-200">
{literal}
                  <li>import fetch from "node-fetch";</li>
                  <li></li>
                  <li>const API_KEY = "YOUR_API_KEY";</li>
                  <li>const BASE_URL = "BASE_URL";</li>
                  <li></li>
                  <li>const resp = await fetch(`${BASE_URL}/api/developer/text`, {</li>
                  <li>&nbsp;&nbsp;method: "POST",</li>
                  <li>&nbsp;&nbsp;headers: {</li>
                  <li>&nbsp;&nbsp;&nbsp;&nbsp;"Content-Type": "application/json",</li>
                  <li>&nbsp;&nbsp;&nbsp;&nbsp;"X-API-Key": API_KEY</li>
                  <li>&nbsp;&nbsp;},</li>
                  <li>&nbsp;&nbsp;body: JSON.stringify({</li>
                  <li>&nbsp;&nbsp;&nbsp;&nbsp;prompt: "Explain Newton\u2019s laws",</li>
                  <li>&nbsp;&nbsp;&nbsp;&nbsp;model: "pro"</li>
                  <li>&nbsp;&nbsp;})</li>
                  <li>});</li>
                  <li></li>
                  <li>const data = await resp.json();</li>
                  <li>console.log(data.text);</li>
{/literal}
                </ol>
              </div>
            </div>
            <textarea id="dev-code-node" class="sr-only">{literal}import fetch from "node-fetch";

const API_KEY = "YOUR_API_KEY";
const BASE_URL = "BASE_URL";

const resp = await fetch(`${BASE_URL}/api/developer/text`, {
  method: "POST",
  headers: {
    "Content-Type": "application/json",
    "X-API-Key": API_KEY
  },
  body: JSON.stringify({
    prompt: "Explain Newton\u2019s laws",
    model: "pro"
  })
});

const data = await resp.json();
console.log(data.text);{/literal}</textarea>
          </div>

          <div class="hidden" data-panel="php">
            <div class="bg-[#0b1120] rounded-2xl border-2 border-brand-dark shadow-hard p-4 text-gray-200">
              <div class="flex items-center justify-between gap-4 border-b border-slate-700 pb-3 mb-4">
                <div class="flex items-center gap-3">
                  <div class="flex gap-2">
                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                    <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                  </div>
                  <span class="text-xs font-bold uppercase tracking-widest text-brand-blue">PHP</span>
                </div>
                <button type="button" class="btn-outline !py-1 !px-3 !text-xs" data-copy-target="dev-code-php">Copy</button>
              </div>
              <div class="bg-[#0f172a] border border-slate-700 rounded-xl p-4 overflow-x-auto">
                <ol class="list-decimal list-inside space-y-1 text-xs md:text-sm font-mono text-slate-200">
{literal}
                  <li>$apiKey = "YOUR_API_KEY";</li>
                  <li>$baseUrl = "BASE_URL";</li>
                  <li></li>
                  <li>$payload = json_encode([</li>
                  <li>&nbsp;&nbsp;"prompt" => "Explain Newton\u2019s laws",</li>
                  <li>&nbsp;&nbsp;"model" => "pro"</li>
                  <li>]);</li>
                  <li></li>
                  <li>$ch = curl_init($baseUrl . "/api/developer/text");</li>
                  <li>curl_setopt($ch, CURLOPT_POST, true);</li>
                  <li>curl_setopt($ch, CURLOPT_HTTPHEADER, [</li>
                  <li>&nbsp;&nbsp;"Content-Type: application/json",</li>
                  <li>&nbsp;&nbsp;"X-API-Key: " . $apiKey</li>
                  <li>]);</li>
                  <li>curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);</li>
                  <li>curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);</li>
                  <li></li>
                  <li>$result = curl_exec($ch);</li>
                  <li>curl_close($ch);</li>
                  <li>echo $result;</li>
{/literal}
                </ol>
              </div>
            </div>
            <textarea id="dev-code-php" class="sr-only">{literal}$apiKey = "YOUR_API_KEY";
$baseUrl = "BASE_URL";

$payload = json_encode([
  "prompt" => "Explain Newton\u2019s laws",
  "model" => "pro"
]);

$ch = curl_init($baseUrl . "/api/developer/text");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Content-Type: application/json",
  "X-API-Key: " . $apiKey
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

$result = curl_exec($ch);
curl_close($ch);

echo $result;{/literal}</textarea>
          </div>

          <div class="hidden" data-panel="react">
            <div class="bg-[#0b1120] rounded-2xl border-2 border-brand-dark shadow-hard p-4 text-gray-200">
              <div class="flex items-center justify-between gap-4 border-b border-slate-700 pb-3 mb-4">
                <div class="flex items-center gap-3">
                  <div class="flex gap-2">
                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                    <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                  </div>
                  <span class="text-xs font-bold uppercase tracking-widest text-brand-blue">React</span>
                </div>
                <button type="button" class="btn-outline !py-1 !px-3 !text-xs" data-copy-target="dev-code-react">Copy</button>
              </div>
              <div class="bg-[#0f172a] border border-slate-700 rounded-xl p-4 overflow-x-auto">
                <ol class="list-decimal list-inside space-y-1 text-xs md:text-sm font-mono text-slate-200">
{literal}
                  <li>const runApilage = async () => {</li>
                  <li>&nbsp;&nbsp;const resp = await fetch("BASE_URL/api/developer/text", {</li>
                  <li>&nbsp;&nbsp;&nbsp;&nbsp;method: "POST",</li>
                  <li>&nbsp;&nbsp;&nbsp;&nbsp;headers: {</li>
                  <li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"Content-Type": "application/json",</li>
                  <li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"X-API-Key": "YOUR_API_KEY"</li>
                  <li>&nbsp;&nbsp;&nbsp;&nbsp;},</li>
                  <li>&nbsp;&nbsp;&nbsp;&nbsp;body: JSON.stringify({</li>
                  <li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;prompt: "Explain Newton\u2019s laws",</li>
                  <li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;model: "pro"</li>
                  <li>&nbsp;&nbsp;&nbsp;&nbsp;})</li>
                  <li>&nbsp;&nbsp;});</li>
                  <li>&nbsp;&nbsp;const data = await resp.json();</li>
                  <li>&nbsp;&nbsp;console.log(data.text);</li>
                  <li>};</li>
{/literal}
                </ol>
              </div>
            </div>
            <textarea id="dev-code-react" class="sr-only">{literal}const runApilage = async () => {
  const resp = await fetch("BASE_URL/api/developer/text", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-API-Key": "YOUR_API_KEY"
    },
    body: JSON.stringify({
      prompt: "Explain Newton\u2019s laws",
      model: "pro"
    })
  });
  const data = await resp.json();
  console.log(data.text);
};{/literal}</textarea>
          </div>
        </div>
      </div>
    </section>

    <section id="guide" class="container mx-auto px-6 mt-16">
      <div class="max-w-6xl mx-auto grid md:grid-cols-2 gap-6">
        <div class="p-6 border-2 border-brand-dark rounded-2xl bg-white shadow-hard-sm">
          <h3 class="text-xl font-bold text-brand-dark mb-3">API Usage &amp; Guide</h3>
          <ul class="space-y-2 text-sm text-brand-dark/70 font-medium">
            <li><span class="font-bold text-brand-dark">Endpoint:</span> {$smarty.const.APP_URL}/api/developer/text</li>
            <li><span class="font-bold text-brand-dark">Streaming:</span> {$smarty.const.APP_URL}/api/developer/stream</li>
            <li><span class="font-bold text-brand-dark">Auth Header:</span> X-API-Key: YOUR_API_KEY</li>
            <li><span class="font-bold text-brand-dark">Body:</span> prompt, model, system (optional), temperature (optional), max_tokens (optional)</li>
            <li><span class="font-bold text-brand-dark">Rate Limits:</span> TPM + RPD per model (see documentation).</li>
          </ul>
          <p class="text-xs text-brand-dark/60 mt-4">
            Streaming returns SSE data events with JSON {literal}{ "delta": "..." }{/literal} and a final done event.
          </p>
        </div>

        <div class="p-6 border-2 border-brand-dark rounded-2xl bg-white shadow-hard-sm">
          <h3 class="text-xl font-bold text-brand-dark mb-3">System Text Rules</h3>
          <ul class="space-y-2 text-sm text-brand-dark/70 font-medium">
            <li>ApilageAI default system text is always active and hidden.</li>
            <li>Your custom system text is appended on top of the default.</li>
            <li>Only Sinhala or English responses are allowed.</li>
          </ul>
        </div>
      </div>
    </section>

    <section id="pricing" class="container mx-auto px-6 mt-16">
      <div class="max-w-6xl mx-auto">
        <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-4">Token Pricing (LKR per 1M tokens)</h2>
        <p class="text-sm text-brand-dark/60 mb-3">Charges are deducted from your ApilageAI balance and include system text tokens.</p>
        <p class="text-sm text-brand-dark/60 mb-3">Developer API requests require a positive ApilageAI balance.</p>
        <div class="overflow-x-auto border-2 border-brand-dark rounded-2xl bg-white shadow-hard-sm">
          <table class="w-full text-sm">
            <thead class="bg-brand-blueLight">
              <tr>
                <th class="text-left px-4 py-3 border-b-2 border-brand-dark">Model</th>
                <th class="text-left px-4 py-3 border-b-2 border-brand-dark">Input</th>
                <th class="text-left px-4 py-3 border-b-2 border-brand-dark">Output</th>
              </tr>
            </thead>
            <tbody>
              <tr class="border-b border-brand-dark/20">
                <td class="px-4 py-3 font-semibold">free</td>
                <td class="px-4 py-3">1499.00</td>
                <td class="px-4 py-3">1299.00</td>
              </tr>
              <tr class="border-b border-brand-dark/20">
                <td class="px-4 py-3 font-semibold">pro</td>
                <td class="px-4 py-3">1999.00</td>
                <td class="px-4 py-3">1899.00</td>
              </tr>
              <tr class="border-b border-brand-dark/20">
                <td class="px-4 py-3 font-semibold">super</td>
                <td class="px-4 py-3">3599.00</td>
                <td class="px-4 py-3">3299.00</td>
              </tr>
              <tr class="border-b border-brand-dark/20">
                <td class="px-4 py-3 font-semibold">master</td>
                <td class="px-4 py-3">7999.00</td>
                <td class="px-4 py-3">7000.00</td>
              </tr>
              <tr>
                <td class="px-4 py-3 font-semibold">loard</td>
                <td class="px-4 py-3">10000.00</td>
                <td class="px-4 py-3">8999.00</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <section id="models" class="container mx-auto px-6 mt-16">
      <div class="max-w-6xl mx-auto">
        <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-4">Available Models</h2>
        <div class="grid md:grid-cols-5 gap-4">
          <div class="p-4 border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm text-center font-bold">free</div>
          <div class="p-4 border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm text-center font-bold">pro</div>
          <div class="p-4 border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm text-center font-bold">super</div>
          <div class="p-4 border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm text-center font-bold">master</div>
          <div class="p-4 border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm text-center font-bold">loard</div>
        </div>
      </div>
    </section>
  </main>

  {include file="components/footer.tpl"} 
