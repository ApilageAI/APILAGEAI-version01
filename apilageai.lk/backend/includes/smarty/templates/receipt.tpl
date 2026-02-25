{include file="components/head.tpl"}

<style>
  @media print {
    body {
      background: #ffffff !important;
    }
    .no-print {
      display: none !important;
    }
    .receipt-shell {
      padding: 0 !important;
    }
    .receipt-card {
      box-shadow: none !important;
    }
  }
</style>

<div class="min-h-screen bg-[#F8FAFC] text-brand-dark font-sans receipt-shell">
  <div class="max-w-3xl mx-auto px-6 py-16">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8 no-print">
      <div class="flex items-center gap-3">
        <img src="{$smarty.const.APP_URL}/assets/images/icon.png" alt="ApilageAI" class="w-12 h-12 object-contain rounded-xl border-2 border-brand-dark bg-white p-1" />
        <div>
          <div class="text-sm font-bold uppercase tracking-wider text-brand-dark/70">ApilageAI Receipt</div>
          <div class="text-xl font-display font-black">Payment Receipt</div>
        </div>
      </div>
      <div class="flex flex-wrap gap-3">
        <button type="button" id="downloadReceiptBtn" class="btn-primary">Download PDF</button>
        <a href="{$smarty.const.APP_URL}/app?open=preferences" class="btn btn-secondary">Back to Billing</a>
      </div>
    </div>

    <div class="receipt-card bg-white border-2 border-brand-dark rounded-3xl shadow-hard overflow-hidden">
      <div class="bg-brand-dark text-white px-8 py-6 text-center">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-brand-blueLight border-2 border-brand-dark text-brand-dark text-xs font-bold uppercase tracking-wider mb-4 shadow-hard-sm">Receipt</div>
        <h1 class="text-2xl md:text-3xl font-display font-black">Thanks for upgrading</h1>
        <p class="text-white/80 text-sm mt-2">Your ApilageAI account is now more powerful.</p>
      </div>

      <div class="px-8 py-6 text-brand-dark">
        <p class="text-base font-medium">Hi <strong>{$receipt_user_name|escape}</strong>,</p>
        <p class="text-sm text-brand-dark/70 mt-2">Here is your payment receipt. Keep this for your records.</p>

        <div class="mt-6 bg-brand-blueLight/40 border-2 border-brand-dark rounded-2xl p-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm font-medium text-brand-dark">
            <div>
              <div class="text-xs uppercase tracking-wider text-brand-dark/60">Invoice ID</div>
              <div class="text-base font-bold">{$receipt_invoice_id|escape}</div>
            </div>
            <div>
              <div class="text-xs uppercase tracking-wider text-brand-dark/60">Status</div>
              <div class="text-base font-bold">{$receipt_status|escape}</div>
            </div>
            <div>
              <div class="text-xs uppercase tracking-wider text-brand-dark/60">Upgraded amount</div>
              <div class="text-base font-bold">Rs. {$receipt_amount|escape}</div>
            </div>
            <div>
              <div class="text-xs uppercase tracking-wider text-brand-dark/60">Paid on</div>
              <div class="text-base font-bold">{$receipt_date|escape} · {$receipt_time|escape}</div>
            </div>
          </div>
        </div>

        <div class="mt-6 text-sm text-brand-dark/70">
          <div class="font-bold text-brand-dark">Account email</div>
          <div>{$receipt_user_email|escape}</div>
        </div>

        <div class="mt-6 border-t-2 border-brand-dark/10 pt-5">
          <div class="text-sm text-brand-dark/70">How ApilageAI credit works</div>
          <a href="{$receipt_credit_link|escape}" class="text-brand-red font-bold">{$receipt_credit_link|escape}</a>
        </div>

        <div class="mt-6 text-sm text-brand-dark/60">
          If this receipt looks incorrect, please contact our support team.
        </div>
      </div>

      <div class="bg-brand-blueLight border-t-2 border-brand-dark px-8 py-4 text-xs text-brand-dark/80 text-center">
        ApilageAI · Sri Lanka
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    const btn = document.getElementById('downloadReceiptBtn');
    if (!btn) return;
    btn.addEventListener('click', () => {
      window.print();
    });
  })();
</script>

</body>
</html>
