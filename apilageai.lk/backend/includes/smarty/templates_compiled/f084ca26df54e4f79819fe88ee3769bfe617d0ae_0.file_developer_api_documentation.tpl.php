<?php
/* Smarty version 5.8.0, created on 2026-04-21 20:27:56
  from 'file:developer_api_documentation.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.8.0',
  'unifunc' => 'content_69e79074aed8a9_42099168',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'f084ca26df54e4f79819fe88ee3769bfe617d0ae' => 
    array (
      0 => 'developer_api_documentation.tpl',
      1 => 1771523941,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:components/head.tpl' => 1,
    'file:components/footer.tpl' => 1,
  ),
))) {
function content_69e79074aed8a9_42099168 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = '/home/apilageai/domains/apilageai.lk/backend/includes/smarty/templates';
$_smarty_tpl->renderSubTemplate("file:components/head.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), (int) 0, $_smarty_current_dir);
?>

<div class="min-h-screen bg-white bg-grid-pattern text-brand-dark font-sans selection:bg-brand-red selection:text-white">
  <nav id="navbar" class="navbar-normal fixed top-0 left-0 right-0 z-50 transition-all duration-300">
    <div class="container mx-auto px-6 flex items-center justify-between">
      <a href="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/" class="flex items-center gap-3">
        <img src="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/assets/images/icon.png" alt="ApilageAI Logo" class="w-10 h-10 object-contain" />
        <span class="text-xl font-bold font-display text-brand-dark tracking-tight">
          Apilage<span class="text-brand-red underline decoration-wavy decoration-2 underline-offset-4">AI</span>
        </span>
      </a>
      <div class="flex items-center gap-4 text-sm font-bold text-brand-dark/80">
        <a href="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/developers" class="hover:text-brand-red hover:underline decoration-2 underline-offset-4 transition-all">Developers</a>
        <a href="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/app" class="btn-primary !py-2 !px-5 !text-sm">Start Chat</a>
      </div>
    </div>
  </nav>

  <main class="pt-28 pb-20">
    <section class="container mx-auto px-6">
      <div class="max-w-5xl mx-auto">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-blueLight border-2 border-brand-dark text-brand-dark text-xs font-bold mb-6 uppercase tracking-wider">
          Developer API Documentation
        </div>
        <h1 class="text-4xl md:text-6xl font-display font-black text-brand-dark mb-4">ApilageAI Developer API Documentation</h1>
        <p class="text-brand-dark/70 text-base md:text-lg font-medium">Last updated: February 19, 2026</p>
        <p class="text-brand-dark/70 text-base md:text-lg font-medium mt-4">
          Official documentation for ApilageAI&rsquo;s Sri Lanka focused developer API. Learn how to authenticate, stream responses,
          manage rate limits, and price tokens correctly with system text included.
        </p>
      </div>
    </section>

    <section class="container mx-auto px-6 mt-10">
      <div class="max-w-5xl mx-auto grid md:grid-cols-3 gap-6">
        <div class="md:col-span-1 p-5 border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm">
          <h2 class="text-lg font-bold text-brand-dark mb-3">Contents</h2>
          <ul class="space-y-2 text-sm font-medium text-brand-dark/70">
            <li><a class="underline" href="#overview">Overview</a></li>
            <li><a class="underline" href="#quickstart">Quickstart</a></li>
            <li><a class="underline" href="#auth">Authentication</a></li>
            <li><a class="underline" href="#endpoints">Endpoints</a></li>
            <li><a class="underline" href="#streaming">Streaming</a></li>
            <li><a class="underline" href="#pricing">Pricing</a></li>
            <li><a class="underline" href="#limits">Rate Limits</a></li>
            <li><a class="underline" href="#domains">Allowed Domains</a></li>
            <li><a class="underline" href="#errors">Errors</a></li>
            <li><a class="underline" href="#security">Security</a></li>
          </ul>
        </div>
        <div class="md:col-span-2 p-5 border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm">
          <p class="text-sm text-brand-dark/70 font-medium">
            Base URL: <span class="font-bold text-brand-dark"><?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
</span>
          </p>
          <p class="text-sm text-brand-dark/70 font-medium mt-3">
            Use the developer portal at <a class="underline" href="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/developers"><?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/developers</a> to create API keys,
            manage allowed domains, and monitor usage.
          </p>
        </div>
      </div>
    </section>

    <section id="overview" class="container mx-auto px-6 mt-12">
      <div class="max-w-5xl mx-auto">
        <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-4">Overview</h2>
        <p class="text-brand-dark/70 text-base font-medium">
          The ApilageAI Developer API delivers streaming and direct text responses optimized for Sri Lankan education workflows,
          multilingual support, and custom system instructions. Charges are calculated per 1M tokens and include system text.
        </p>
      </div>
    </section>

    <section id="quickstart" class="container mx-auto px-6 mt-12">
      <div class="max-w-5xl mx-auto">
        <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-4">Quickstart</h2>
        <div class="bg-[#0b1120] rounded-2xl border-2 border-brand-dark shadow-hard p-4 text-gray-200">
          <div class="flex items-center justify-between gap-4 border-b border-slate-700 pb-3 mb-4">
            <div class="flex items-center gap-3">
              <div class="flex gap-2">
                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                <div class="w-3 h-3 rounded-full bg-green-500"></div>
              </div>
              <span class="text-xs font-bold uppercase tracking-widest text-brand-blue">JavaScript (Fetch)</span>
            </div>
            <button type="button" class="btn-outline !py-1 !px-3 !text-xs" data-copy-target="doc-code-js">Copy</button>
          </div>
          <div class="bg-[#0f172a] border border-slate-700 rounded-xl p-4 overflow-x-auto">
            <ol class="list-decimal list-inside space-y-1 text-xs md:text-sm font-mono text-slate-200">

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

            </ol>
          </div>
        </div>
        <textarea id="doc-code-js" class="sr-only">const API_KEY = "YOUR_API_KEY";
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
console.log(data.text);</textarea>
      </div>
    </section>

    <section id="auth" class="container mx-auto px-6 mt-12">
      <div class="max-w-5xl mx-auto">
        <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-4">Authentication</h2>
        <ul class="space-y-2 text-sm font-medium text-brand-dark/70">
          <li>Send your API key in the `X-API-Key` header.</li>
          <li>API keys are limited to 10 per account.</li>
          <li>Each key can whitelist up to 5 allowed domains.</li>
          <li>Developer API requests require a positive ApilageAI balance.</li>
        </ul>
      </div>
    </section>

    <section id="endpoints" class="container mx-auto px-6 mt-12">
      <div class="max-w-5xl mx-auto">
        <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-4">Endpoints</h2>
        <div class="p-5 border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm mb-6">
          <h3 class="text-lg font-bold text-brand-dark">POST /api/developer/text</h3>
          <p class="text-sm text-brand-dark/70 mt-2">Direct response endpoint.</p>
          <ul class="space-y-1 text-sm text-brand-dark/70 mt-3">
            <li><strong>Body:</strong> prompt, model, system (optional), temperature (optional), max_tokens (optional)</li>
            <li><strong>Response:</strong> text, usage (token + cost breakdown)</li>
          </ul>
        </div>
        <div class="p-5 border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm">
          <h3 class="text-lg font-bold text-brand-dark">POST /api/developer/stream</h3>
          <p class="text-sm text-brand-dark/70 mt-2">Streaming SSE endpoint for real-time output.</p>
        </div>
      </div>
    </section>

    <section id="streaming" class="container mx-auto px-6 mt-12">
      <div class="max-w-5xl mx-auto">
        <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-4">Streaming (SSE)</h2>
        <p class="text-brand-dark/70 text-base font-medium mb-4">
          Streaming responses are sent as Server-Sent Events. Each `data:` event contains a JSON payload with `delta`.
          A final `event: done` includes usage totals and cost.
        </p>
        <pre class="bg-brand-gray border-2 border-brand-dark rounded-xl p-4 text-sm font-mono text-brand-dark whitespace-pre-wrap">data: {"delta":"First chunk"}\n\n
data: {"delta":"More text"}\n\n
event: done
data: {"usage":{"total_tokens":1234,"total_cost_lkr":12.34}}\n\n</pre>
      </div>
    </section>

    <section id="pricing" class="container mx-auto px-6 mt-12">
      <div class="max-w-5xl mx-auto">
        <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-4">Pricing (LKR per 1M tokens)</h2>
        <div class="overflow-x-auto border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm">
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
        <p class="text-xs text-brand-dark/60 mt-3">System text tokens are included in input charges and are deducted from your ApilageAI balance.</p>
      </div>
    </section>

    <section id="limits" class="container mx-auto px-6 mt-12">
      <div class="max-w-5xl mx-auto">
        <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-4">Rate Limits</h2>
        <div class="overflow-x-auto border-2 border-brand-dark rounded-xl bg-white shadow-hard-sm">
          <table class="w-full text-sm">
            <thead class="bg-brand-blueLight">
              <tr>
                <th class="text-left px-4 py-3 border-b-2 border-brand-dark">Model</th>
                <th class="text-left px-4 py-3 border-b-2 border-brand-dark">TPM</th>
                <th class="text-left px-4 py-3 border-b-2 border-brand-dark">RPD</th>
              </tr>
            </thead>
            <tbody>
              <tr class="border-b border-brand-dark/20">
                <td class="px-4 py-3 font-semibold">free</td>
                <td class="px-4 py-3">1,000,000</td>
                <td class="px-4 py-3">1,500</td>
              </tr>
              <tr class="border-b border-brand-dark/20">
                <td class="px-4 py-3 font-semibold">pro</td>
                <td class="px-4 py-3">1,000,000</td>
                <td class="px-4 py-3">1,500</td>
              </tr>
              <tr class="border-b border-brand-dark/20">
                <td class="px-4 py-3 font-semibold">super</td>
                <td class="px-4 py-3">1,000,000</td>
                <td class="px-4 py-3">1,500</td>
              </tr>
              <tr class="border-b border-brand-dark/20">
                <td class="px-4 py-3 font-semibold">master</td>
                <td class="px-4 py-3">1,000,000</td>
                <td class="px-4 py-3">1,500</td>
              </tr>
              <tr>
                <td class="px-4 py-3 font-semibold">loard</td>
                <td class="px-4 py-3">700</td>
                <td class="px-4 py-3">1,000</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <section id="domains" class="container mx-auto px-6 mt-12">
      <div class="max-w-5xl mx-auto">
        <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-4">Allowed Domains</h2>
        <p class="text-brand-dark/70 text-base font-medium">
          Each API key can store up to 5 allowed domains (e.g., `example.com`, `app.example.com`, or `https://app.example.com`).
          Requests from other origins are blocked.
        </p>
      </div>
    </section>

    <section id="errors" class="container mx-auto px-6 mt-12">
      <div class="max-w-5xl mx-auto">
        <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-4">Errors</h2>
        <p class="text-sm text-brand-dark/70 font-medium mb-3">
          All errors return JSON in the format <span class="font-mono">{"e":true,"m":"...","code":"..."}</span>.
          Streaming errors are sent as <span class="font-mono">event: error</span> with the same payload.
        </p>
        <ul class="space-y-2 text-sm font-medium text-brand-dark/70">
          <li><strong>400</strong> PROMPT_REQUIRED, PROMPT_TOO_LONG, SYSTEM_TOO_LONG.</li>
          <li><strong>405</strong> METHOD_NOT_ALLOWED.</li>
          <li><strong>401</strong> API_KEY_REQUIRED, INVALID_API_KEY.</li>
          <li><strong>403</strong> API_KEY_DISABLED, ORIGIN_NOT_ALLOWED, KEY_LEAK_DETECTED.</li>
          <li><strong>402</strong> INSUFFICIENT_BALANCE.</li>
          <li><strong>429</strong> TPM_LIMIT, RPD_LIMIT.</li>
          <li><strong>500</strong> GENERATION_FAILED.</li>
        </ul>
      </div>
    </section>

    <section id="security" class="container mx-auto px-6 mt-12">
      <div class="max-w-5xl mx-auto">
        <h2 class="text-2xl md:text-3xl font-display font-black text-brand-dark mb-4">Security &amp; Leak Detection</h2>
        <p class="text-brand-dark/70 text-base font-medium">
          ApilageAI automatically detects suspicious API key exposure (for example, public code repository leaks) and may disable the key
          to protect your balance. Always rotate keys if you suspect a leak.
        </p>
      </div>
    </section>
  </main>

  <?php $_smarty_tpl->renderSubTemplate("file:components/footer.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), (int) 0, $_smarty_current_dir);
}
}
