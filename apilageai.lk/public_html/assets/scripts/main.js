// Navbar scroll effect
window.addEventListener('scroll', function() {
  const navbar = document.getElementById('navbar');
  if (!navbar) {
    return;
  }
  if (window.scrollY > 20) {
    navbar.classList.remove('navbar-normal');
    navbar.classList.add('navbar-scrolled');
  } else {
    navbar.classList.remove('navbar-scrolled');
    navbar.classList.add('navbar-normal');
  }
});

// Feature cards video playback on hover
document.addEventListener('DOMContentLoaded', function() {
  const flipCards = document.querySelectorAll('.flip-card');

  flipCards.forEach(card => {
    const video = card.querySelector('video');
    if (video) {
      card.addEventListener('mouseenter', () => {
        video.play().catch(e => console.log('Video play failed:', e));
      });
      card.addEventListener('mouseleave', () => {
        video.pause();
        video.currentTime = 0; // Reset to start
      });
    }
  });
});

// Chat functionality
const chatMessages = document.getElementById('chat-messages');
const chatForm = document.getElementById('chat-form');
const chatInput = document.getElementById('chat-input');

async function sendMessage(message) {
  // Add user message
  const userMessageDiv = document.createElement('div');
  userMessageDiv.className = 'message-user';
  userMessageDiv.innerHTML = `
    <div class="message-bubble">
      <p>${message}</p>
    </div>
  `;
  chatMessages.appendChild(userMessageDiv);

  // Add loading indicator
  const loadingDiv = document.createElement('div');
  loadingDiv.className = 'message-model';
  loadingDiv.innerHTML = `
    <div class="loading-bubble">
      <svg class="w-4 h-4 text-brand-red animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
      </svg>
      <span class="text-xs font-bold text-brand-dark opacity-50">Thinking...</span>
    </div>
  `;
  chatMessages.appendChild(loadingDiv);
  chatMessages.scrollTop = chatMessages.scrollHeight;

  try {
    const response = await fetch("https://endpoint.apilageai.lk/api/chat", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Authorization": "Bearer apk_lKOtfJhVe7fJUfYfkTLNgBOxZqgkLRgK"
      },
      body: JSON.stringify({
        message: message,
        enableGoogleSearch: true,
        model: "SUPER"
      })
    });

    if (!response.ok) {
      throw new Error(`API Error: ${response.status}`);
    }

    const result = await response.json();
    const aiResponse = result.response || "No response received.";

    // Remove loading indicator
    chatMessages.removeChild(loadingDiv);

    // Add AI response
    const aiMessageDiv = document.createElement('div');
    aiMessageDiv.className = 'message-model';
    aiMessageDiv.innerHTML = `
      <div class="message-bubble">
        <p>${aiResponse}</p>
      </div>
    `;
    chatMessages.appendChild(aiMessageDiv);
  } catch (error) {
    console.error("ApilageAI API Error:", error);

    // Remove loading indicator
    chatMessages.removeChild(loadingDiv);

    // Add error message
    const errorMessageDiv = document.createElement('div');
    errorMessageDiv.className = 'message-model';
    errorMessageDiv.innerHTML = `
      <div class="message-bubble">
        <p>Oops! My brain is buffering. Check your connection or try again later.</p>
      </div>
    `;
    chatMessages.appendChild(errorMessageDiv);
  }

  chatMessages.scrollTop = chatMessages.scrollHeight;
}

if (chatForm && chatInput && chatMessages) {
  chatForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const message = chatInput.value.trim();
    if (message) {
      sendMessage(message);
      chatInput.value = '';
    }
  });
}

// Developer console
document.addEventListener('DOMContentLoaded', function() {
  const consoleRoot = document.getElementById('developer-console');
  if (!consoleRoot) {
    return;
  }

  const isAuthed = consoleRoot.dataset.auth === '1';
  const appBase = (window.APP_BASE_URL || window.location.origin || '').replace(/\/$/, '');
  const publicApiBase = (consoleRoot.dataset.apiBase || appBase || window.location.origin || '').replace(/\/$/, '');
  const apiBase = appBase ? `${appBase}/api/developer.php` : '/api/developer.php';

  const $ = (id) => document.getElementById(id);
  const createForm = $('create-api-key-form');
  const newKeyPanel = $('new-api-key-panel');
  const newKeyValue = $('new-api-key-value');
  const copyNewKeyBtn = $('copy-new-api-key');
  const keysBody = $('api-keys-body');
  const keySelect = $('api-key-select');
  const domainsInput = $('api-key-domains');
  const saveDomainsBtn = $('save-domains');
  const systemInput = $('api-key-system');
  const saveSystemBtn = $('save-system');
  const systemSaveStatus = $('system-save-status');
  const balanceValue = $('developer-balance-value');
  const rechargeAmountInput = $('recharge-amount');
  const rechargeButton = $('recharge-button');
  const usageSummary = $('api-usage-summary');
  const playgroundRun = $('playground-run');
  const playgroundOutput = $('playground-output');
  const playgroundUsage = $('playground-usage');

  let cachedKeys = [];

  const escapeHtml = (value) => {
    const div = document.createElement('div');
    div.textContent = value;
    return div.innerHTML;
  };

  const formatNumber = (value) => {
    if (value === null || value === undefined) return '0';
    return Number(value).toLocaleString();
  };

  const formatMoney = (value) => {
    const num = Number(value || 0);
    return num.toFixed(2);
  };

  const formatDate = (value) => {
    if (!value) return '—';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleDateString();
  };

  const apiCall = async (action, options = {}) => {
    const method = options.method || 'GET';
    const fetchOptions = {
      method,
      headers: {
        'Content-Type': 'application/json',
      },
    };
    if (method !== 'GET') {
      fetchOptions.body = JSON.stringify(options.body || {});
    }
    const resp = await fetch(`${apiBase}?act=${action}`, fetchOptions);
    const data = await resp.json().catch(() => ({}));
    if (!resp.ok || data.e) {
      const message = data.m || 'Request failed';
      throw new Error(message);
    }
    return data;
  };

  const renderKeys = () => {
    if (!keysBody) return;
    keysBody.innerHTML = '';

    if (!cachedKeys.length) {
      keysBody.innerHTML = '<tr><td colspan="6" class="px-3 py-6 text-center text-brand-dark/60">No API keys yet.</td></tr>';
      if (keySelect) {
        keySelect.innerHTML = '<option value="">Select an API key</option>';
      }
      return;
    }

    if (keySelect) {
      keySelect.innerHTML = '<option value="">Select an API key</option>';
      cachedKeys.forEach((key) => {
        const opt = document.createElement('option');
        opt.value = key.id;
        opt.textContent = key.name;
        keySelect.appendChild(opt);
      });
    }

    cachedKeys.forEach((key) => {
      const usage = key.usage || {};
      const statusBadge = key.status === 'active'
        ? '<span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-bold rounded-full bg-green-100 text-green-700 border border-green-500">Active</span>'
        : '<span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-bold rounded-full bg-gray-100 text-gray-700 border border-gray-400">Disabled</span>';

      const actions = `
        <div class="flex flex-wrap gap-2">
          <button data-action="toggle" data-id="${key.id}" data-status="${key.status === 'active' ? 'disabled' : 'active'}" class="btn-outline !py-1 !px-3 text-xs">
            ${key.status === 'active' ? 'Disable' : 'Enable'}
          </button>
          <button data-action="delete" data-id="${key.id}" class="btn-outline !py-1 !px-3 text-xs">Delete</button>
        </div>
      `;

      const row = document.createElement('tr');
      row.innerHTML = `
        <td class="px-3 py-3 border-b border-brand-dark/20 font-semibold">${escapeHtml(key.name)}</td>
        <td class="px-3 py-3 border-b border-brand-dark/20 font-mono text-xs">${escapeHtml(key.prefix)}••••</td>
        <td class="px-3 py-3 border-b border-brand-dark/20">${statusBadge}</td>
        <td class="px-3 py-3 border-b border-brand-dark/20 text-xs">
          <div>${formatNumber(usage.requests_30d || 0)} req</div>
          <div>${formatNumber(usage.tokens_30d || 0)} tokens</div>
          <div>LKR ${formatMoney(usage.cost_30d || 0)}</div>
        </td>
        <td class="px-3 py-3 border-b border-brand-dark/20 text-xs">${formatDate(key.last_used_at)}</td>
        <td class="px-3 py-3 border-b border-brand-dark/20">${actions}</td>
      `;
      keysBody.appendChild(row);
    });
  };

  const updateSettingsPanel = (keyId) => {
    const key = cachedKeys.find((item) => String(item.id) === String(keyId));
    if (!key) {
      if (domainsInput) domainsInput.value = '';
      if (systemInput) systemInput.value = '';
      if (usageSummary) usageSummary.innerHTML = '<p>Select a key to view usage.</p>';
      return;
    }

    if (domainsInput) {
      domainsInput.value = (key.domains || []).join('\n');
    }
    if (systemInput) {
      systemInput.value = key.system_prompt || '';
    }
    if (usageSummary) {
      const usage = key.usage || {};
      usageSummary.innerHTML = `
        <div><strong>Requests (30d):</strong> ${formatNumber(usage.requests_30d || 0)}</div>
        <div><strong>Tokens (30d):</strong> ${formatNumber(usage.tokens_30d || 0)}</div>
        <div><strong>Cost (30d):</strong> LKR ${formatMoney(usage.cost_30d || 0)}</div>
        <div><strong>Domains:</strong> ${(key.domains || []).length}</div>
      `;
    }
  };

  const loadKeys = async () => {
    if (!isAuthed) return;
    try {
      const data = await apiCall('list');
      cachedKeys = data.keys || [];
      if (balanceValue && data.balance !== undefined) {
        balanceValue.textContent = formatMoney(data.balance);
      }
      renderKeys();
      if (keySelect && keySelect.value) {
        updateSettingsPanel(keySelect.value);
      }
    } catch (err) {
      if (keysBody) {
        keysBody.innerHTML = `<tr><td colspan="6" class="px-3 py-6 text-center text-brand-dark/60">${escapeHtml(err.message)}</td></tr>`;
      }
    }
  };

  if (isAuthed) {
    loadKeys();
  }

  if (createForm) {
    createForm.addEventListener('submit', async (event) => {
      event.preventDefault();
      const nameInput = $('api-key-name');
      const nameValue = nameInput ? nameInput.value.trim() : '';
      if (!nameValue) return;
      try {
        const data = await apiCall('create', { method: 'POST', body: { name: nameValue } });
        if (newKeyValue) newKeyValue.textContent = data.key || '';
        if (newKeyPanel) newKeyPanel.classList.remove('hidden');
        if (nameInput) nameInput.value = '';
        await loadKeys();
      } catch (err) {
        alert(err.message);
      }
    });
  }

  if (copyNewKeyBtn && newKeyValue) {
    copyNewKeyBtn.addEventListener('click', async () => {
      try {
        await navigator.clipboard.writeText(newKeyValue.textContent || '');
        copyNewKeyBtn.textContent = 'Copied';
        setTimeout(() => {
          copyNewKeyBtn.textContent = 'Copy';
        }, 1500);
      } catch (_) {
        alert('Copy failed. Please copy manually.');
      }
    });
  }

  if (keysBody) {
    keysBody.addEventListener('click', async (event) => {
      const actionBtn = event.target.closest('[data-action]');
      if (!actionBtn) return;
      const action = actionBtn.getAttribute('data-action');
      const keyId = actionBtn.getAttribute('data-id');
      if (!keyId) return;

      if (action === 'delete') {
        if (!confirm('Delete this API key? This cannot be undone.')) return;
        try {
          await apiCall('delete', { method: 'POST', body: { key_id: keyId } });
          await loadKeys();
          updateSettingsPanel('');
        } catch (err) {
          alert(err.message);
        }
        return;
      }

      if (action === 'toggle') {
        const status = actionBtn.getAttribute('data-status');
        try {
          await apiCall('toggle', { method: 'POST', body: { key_id: keyId, status } });
          await loadKeys();
        } catch (err) {
          alert(err.message);
        }
      }
    });
  }

  if (keySelect) {
    keySelect.addEventListener('change', () => {
      updateSettingsPanel(keySelect.value);
    });
  }

  if (saveDomainsBtn) {
    saveDomainsBtn.addEventListener('click', async () => {
      const keyId = keySelect ? keySelect.value : '';
      if (!keyId) return alert('Select an API key first.');
      const rawDomains = domainsInput ? domainsInput.value : '';
      const domainCount = rawDomains
        .split(/[\r\n,]+/)
        .map((item) => item.trim())
        .filter(Boolean).length;
      if (domainCount > 5) {
        return alert('Maximum 5 domains allowed per key.');
      }
      try {
        await apiCall('update_domains', {
          method: 'POST',
          body: { key_id: keyId, domains: rawDomains },
        });
        await loadKeys();
        updateSettingsPanel(keyId);
      } catch (err) {
        alert(err.message);
      }
    });
  }

  if (saveSystemBtn) {
    saveSystemBtn.addEventListener('click', async () => {
      const keyId = keySelect ? keySelect.value : '';
      if (!keyId) return alert('Select an API key first.');
      try {
        await apiCall('update_system', {
          method: 'POST',
          body: { key_id: keyId, system: systemInput ? systemInput.value : '' },
        });
        await loadKeys();
        updateSettingsPanel(keyId);
        if (systemSaveStatus) {
          systemSaveStatus.textContent = 'System text saved successfully.';
          systemSaveStatus.classList.remove('hidden', 'text-red-600');
          systemSaveStatus.classList.add('text-green-600');
          setTimeout(() => {
            systemSaveStatus.classList.add('hidden');
          }, 2500);
        }
      } catch (err) {
        if (systemSaveStatus) {
          systemSaveStatus.textContent = err.message || 'Failed to save system text.';
          systemSaveStatus.classList.remove('hidden', 'text-green-600');
          systemSaveStatus.classList.add('text-red-600');
          setTimeout(() => {
            systemSaveStatus.classList.add('hidden');
          }, 3500);
        } else {
          alert(err.message);
        }
      }
    });
  }

  if (rechargeAmountInput) {
    document.querySelectorAll('[data-recharge-amount]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const value = btn.getAttribute('data-recharge-amount');
        if (rechargeAmountInput) rechargeAmountInput.value = value;
      });
    });
  }

  if (rechargeButton) {
    rechargeButton.addEventListener('click', () => {
      const amountRaw = rechargeAmountInput ? rechargeAmountInput.value : '';
      const amount = Number(amountRaw);
      if (!Number.isFinite(amount)) {
        alert('Enter a valid recharge amount.');
        return;
      }
      if (amount < 200 || amount > 20000) {
        alert('Recharge amount must be between 200 and 20000 LKR.');
        return;
      }
      const base = appBase || window.location.origin || '';
      window.location.href = `${base}/pay/${amount}`;
    });
  }

  if (playgroundRun) {
    playgroundRun.addEventListener('click', async () => {
      const keyInput = $('playground-key');
      const modelInput = $('playground-model');
      const systemInputField = $('playground-system');
      const promptInput = $('playground-prompt');
      const streamInput = $('playground-stream');
      const key = keyInput ? keyInput.value.trim() : '';
      const model = modelInput ? modelInput.value : 'free';
      const system = systemInputField ? systemInputField.value : '';
      const prompt = promptInput ? promptInput.value : '';
      const stream = streamInput ? streamInput.checked : false;

      if (!key || !prompt) {
        alert('API key and prompt are required.');
        return;
      }

      if (!publicApiBase) {
        alert('API base is not configured.');
        return;
      }

      playgroundOutput.textContent = stream ? '' : 'Working...';
      playgroundUsage.textContent = '';

      try {
        if (!stream) {
          const resp = await fetch(`${publicApiBase}/api/developer/text`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-API-Key': key,
            },
            body: JSON.stringify({ prompt, model, system }),
          });
          const data = await resp.json().catch(() => ({}));
          if (!resp.ok || data.e) {
            const msg = data.m || data.message || 'Request failed';
            const code = data.code ? ` (${data.code})` : '';
            throw new Error(`${msg}${code}`);
          }
          playgroundOutput.textContent = data.text || '';
          if (data.usage) {
            playgroundUsage.textContent = `Tokens: ${formatNumber(data.usage.total_tokens || 0)} | Cost: LKR ${formatMoney(data.usage.total_cost_lkr || 0)}`;
          }
          return;
        }

        const resp = await fetch(`${publicApiBase}/api/developer/stream`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-API-Key': key,
          },
          body: JSON.stringify({ prompt, model, system }),
        });

        if (!resp.ok || !resp.body) {
          const errData = await resp.json().catch(() => ({}));
          const msg = errData.m || errData.message || 'Streaming request failed';
          const code = errData.code ? ` (${errData.code})` : '';
          throw new Error(`${msg}${code}`);
        }

        const reader = resp.body.getReader();
        const decoder = new TextDecoder();
        let buffer = '';
        let streamError = '';

        while (true) {
          const { value, done } = await reader.read();
          if (done) break;
          buffer += decoder.decode(value, { stream: true });
          const parts = buffer.split('\n\n');
          buffer = parts.pop() || '';
          parts.forEach((part) => {
            const lines = part.split('\n').filter(Boolean);
            let eventName = 'message';
            let dataLine = '';
            lines.forEach((line) => {
              if (line.startsWith('event:')) {
                eventName = line.replace('event:', '').trim();
              } else if (line.startsWith('data:')) {
                dataLine += line.replace('data:', '').trim();
              }
            });
            if (!dataLine) return;
            let payload;
            try {
              payload = JSON.parse(dataLine);
            } catch (_) {
              payload = { delta: dataLine };
            }
            if (eventName === 'error') {
              const msg = payload.m || payload.message || 'Streaming error';
              const code = payload.code ? ` (${payload.code})` : '';
              streamError = `${msg}${code}`;
              playgroundOutput.textContent = streamError;
            } else if (eventName === 'done') {
              if (payload.usage) {
                playgroundUsage.textContent = `Tokens: ${formatNumber(payload.usage.total_tokens || 0)} | Cost: LKR ${formatMoney(payload.usage.total_cost_lkr || 0)}`;
              }
            } else if (payload.delta) {
              playgroundOutput.textContent += payload.delta;
            }
          });
          if (streamError) {
            try {
              await reader.cancel();
            } catch (_) {
              // ignore
            }
            break;
          }
        }
      } catch (err) {
        playgroundOutput.textContent = err.message;
      }
    });
  }

  const tabs = document.querySelectorAll('#code-tabs [data-tab]');
  if (tabs.length) {
    tabs.forEach((btn) => {
      btn.addEventListener('click', () => {
        const target = btn.getAttribute('data-tab');
        document.querySelectorAll('[data-panel]').forEach((panel) => {
          if (panel.getAttribute('data-panel') === target) {
            panel.classList.remove('hidden');
          } else {
            panel.classList.add('hidden');
          }
        });
      });
    });
  }
});

// Code block copy buttons
document.addEventListener('DOMContentLoaded', function () {
  const buttons = document.querySelectorAll('[data-copy-target]');
  if (!buttons.length) return;

  buttons.forEach((btn) => {
    btn.addEventListener('click', async () => {
      const targetId = btn.getAttribute('data-copy-target');
      if (!targetId) return;
      const target = document.getElementById(targetId);
      if (!target) return;
      const text = target.value || target.textContent || '';
      if (!text) return;

      const original = btn.textContent;
      const setCopied = () => {
        btn.textContent = 'copid';
        setTimeout(() => {
          btn.textContent = original;
        }, 1500);
      };

      try {
        await navigator.clipboard.writeText(text);
        setCopied();
      } catch (_) {
        try {
          if (target.select) {
            target.classList.remove('sr-only');
            target.select();
            document.execCommand('copy');
            target.classList.add('sr-only');
            setCopied();
          }
        } catch (err) {
          // ignore
        }
      }
    });
  });
});
