<?php
/**
 * dashboard.php
 * ------------------------------------------------------------
 * Tenant Dashboard — Apartment Management System
 * ------------------------------------------------------------
 * Backend: PHP + PDO/MySQL (see config.php / data.php) with a
 * safe demo-data fallback so the page is always functional.
 * Frontend: HTML5 + Tailwind CSS (CDN).
 * ------------------------------------------------------------
 */
declare(strict_types=1);
require_once __DIR__ . '/data.php';

$tenantId = current_tenant_id();
$tenant   = get_tenant($tenantId);
$bills    = get_recent_bills($tenantId, 4);   // [0] = current month, [1..3] = history

$current = $bills[0] ?? null;
$history = array_slice($bills, 1);

$initials = '';
foreach (explode(' ', trim($tenant['full_name'])) as $part) {
    $initials .= mb_strtoupper(mb_substr($part, 0, 1));
}
$initials = mb_substr($initials, 0, 2);

$today       = new DateTime('today');
$minNotice   = (clone $today)->modify('+30 days')->format('Y-m-d');
$pageTitle = 'Tenant Dashboard · Apartment Management System';
$extraHead = <<<HTML
<style>
  /* Receipt / ledger perforation edge — the signature element (dashboard-only) */
  .receipt-edge { position: relative; }
  .receipt-edge::before {
    content: "";
    position: absolute;
    top: -1px; left: 0; right: 0; height: 0;
    border-top: 2px dashed #CBD2E0;
  }
  .receipt-notch {
    background:
      radial-gradient(circle at 0 0, transparent 9px, #F7F8FA 9.5px) top left,
      radial-gradient(circle at 100% 0, transparent 9px, #F7F8FA 9.5px) top right;
    background-size: 51% 100%;
    background-repeat: no-repeat;
    height: 10px;
  }
</style>
HTML;
include __DIR__ . '/includes/header.php';
?>

<div class="min-h-screen lg:flex">

  <!-- ============================================================
       SIDEBAR
  ============================================================= -->
  <aside id="sidebar"
    class="fixed inset-y-0 left-0 z-40 w-72 -translate-x-full lg:translate-x-0 transition-transform duration-200 bg-ink-800 text-white flex flex-col">

    <!-- Logo -->
    <div class="h-20 flex items-center gap-3 px-6 border-b border-white/10">
      <div class="w-9 h-9 rounded-lg bg-brand-500 flex items-center justify-center font-display font-bold text-sm">AM</div>
      <div>
        <p class="font-display font-semibold text-base leading-tight">Nestly</p>
        <p class="text-[11px] text-white/50 leading-tight tracking-wide">APARTMENT MANAGEMENT</p>
      </div>
      <button id="sidebarClose" class="ml-auto lg:hidden text-white/60 hover:text-white" aria-label="Close menu">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
      </button>
    </div>

    <!-- Tenant profile -->
    <div class="px-6 py-5 border-b border-white/10">
      <div class="flex items-center gap-3">
        <div class="w-11 h-11 rounded-full bg-brand-500/90 flex items-center justify-center font-display font-semibold text-sm shrink-0">
          <?= h($initials) ?>
        </div>
        <div class="min-w-0">
          <p class="font-medium text-sm truncate"><?= h($tenant['full_name']) ?></p>
          <p class="text-xs text-white/50 truncate">Apartment <?= h($tenant['apartment_no']) ?></p>
        </div>
      </div>
    </div>

    <!-- Nav -->
    <nav class="flex-1 px-3 py-5 space-y-1 text-sm">
      <a href="#statement" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-white/10 font-medium">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m6 10V7M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
        Monthly Statement
      </a>
      <a href="#trends" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-white/70 hover:bg-white/5 hover:text-white transition">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14"/></svg>
        Financial Trends
      </a>
      <a href="#report-issue" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-white/70 hover:bg-white/5 hover:text-white transition">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-8.25 3h.008v.008h-.008V15z"/></svg>
        Report an Issue
      </a>
      <a href="#move-out" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-white/70 hover:bg-white/5 hover:text-white transition">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
        Move-Out Notice
      </a>
    </nav>

    <!-- Log out -->
    <div class="p-4 border-t border-white/10">
      <a href="logout.php"
        class="flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-lg bg-white/5 hover:bg-white/10 text-sm font-medium text-white/90 transition">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6-9H6a2 2 0 00-2 2v14a2 2 0 002 2h7"/></svg>
        Log Out
      </a>
    </div>
  </aside>
  <div id="sidebarOverlay" class="fixed inset-0 z-30 bg-ink-900/40 hidden lg:hidden"></div>

  <!-- ============================================================
       MAIN CONTENT
  ============================================================= -->
  <div class="flex-1 lg:ml-72 min-w-0">

    <!-- Mobile topbar -->
    <header class="lg:hidden sticky top-0 z-20 bg-white border-b border-paper-200 h-16 flex items-center px-4 gap-3">
      <button id="sidebarOpen" class="text-ink-700" aria-label="Open menu">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
      <p class="font-display font-semibold">Tenant Dashboard</p>
    </header>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-10 py-8 lg:py-10 space-y-10">

      <!-- Page heading -->
      <div class="hidden lg:flex items-end justify-between">
        <div>
          <p class="text-sm text-ink-600">Welcome back,</p>
          <h1 class="font-display text-2xl font-semibold text-ink-900"><?= h($tenant['full_name']) ?></h1>
        </div>
        <p class="text-sm text-ink-600 font-figures"><?= date('l, F j, Y') ?></p>
      </div>

      <!-- ============================================================
           1. CURRENT MONTHLY STATEMENT — signature receipt card
      ============================================================= -->
      <section id="statement" class="scroll-mt-24">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-display text-lg font-semibold text-ink-900">Current Monthly Statement</h2>
          <?php if ($current): ?>
          <span class="text-xs text-ink-600 font-figures">
            <?= h(MONTH_NAMES[(int)$current['bill_month']]) ?> <?= h((string)$current['bill_year']) ?>
          </span>
          <?php endif; ?>
        </div>

        <?php if (!$current): ?>
          <div class="bg-white border border-paper-200 rounded-2xl p-8 text-center text-ink-600">
            No statement has been generated for this month yet.
          </div>
        <?php else: ?>
        <div class="bg-white rounded-2xl shadow-sm border border-paper-200 overflow-hidden max-w-xl mx-auto lg:mx-0">

          <!-- Receipt header -->
          <div class="px-7 pt-6 pb-5 flex items-start justify-between">
            <div>
              <p class="text-xs uppercase tracking-wider text-ink-600">Statement for</p>
              <p class="font-display font-semibold text-ink-900">Apartment <?= h($tenant['apartment_no']) ?></p>
            </div>
            <?php if ($current['status'] === 'paid'): ?>
              <span class="inline-flex items-center gap-1.5 rounded-full bg-paid-50 text-paid-600 text-xs font-semibold px-3 py-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-paid-500"></span> Paid
              </span>
            <?php else: ?>
              <span class="inline-flex items-center gap-1.5 rounded-full bg-unpaid-50 text-unpaid-600 text-xs font-semibold px-3 py-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-unpaid-500"></span> Unpaid
              </span>
            <?php endif; ?>
          </div>

          <div class="receipt-notch"></div>

          <!-- Line items -->
          <div class="px-7 py-5 space-y-3 text-sm receipt-edge">
            <div class="flex justify-between items-baseline">
              <span class="text-ink-600">Base Apartment Rent</span>
              <span class="font-figures font-medium"><?= money((float)$current['apartment_rent']) ?></span>
            </div>
            <div class="flex justify-between items-baseline">
              <span class="text-ink-600">Electricity Bill</span>
              <span class="font-figures font-medium"><?= money((float)$current['electricity_bill']) ?></span>
            </div>
            <div class="flex justify-between items-baseline">
              <span class="text-ink-600">Water Bill</span>
              <span class="font-figures font-medium"><?= money((float)$current['water_bill']) ?></span>
            </div>
            <div class="flex justify-between items-baseline">
              <span class="text-ink-600">Gas Bill</span>
              <span class="font-figures font-medium"><?= money((float)$current['gas_bill']) ?></span>
            </div>
            <div class="flex justify-between items-baseline">
              <span class="text-ink-600">Service Fee <span class="text-ink-600/60">(guard · lift · trash)</span></span>
              <span class="font-figures font-medium"><?= money((float)$current['service_fee']) ?></span>
            </div>
          </div>

          <div class="receipt-notch"></div>

          <!-- Total -->
          <div class="px-7 py-5 bg-paper-50">
            <div class="flex justify-between items-center">
              <span class="font-display font-semibold text-ink-900">Total Due</span>
              <span class="font-figures font-bold text-xl text-ink-900"><?= money((float)$current['total']) ?></span>
            </div>
            <p class="text-xs text-ink-600 mt-1">
              Due <?= date('F j, Y', strtotime($current['due_date'])) ?>
              <?php if ($current['status'] === 'paid' && $current['paid_at']): ?>
                &middot; Paid on <?= date('F j, Y', strtotime($current['paid_at'])) ?>
              <?php endif; ?>
            </p>
          </div>

          <!-- Actions -->
          <div class="px-7 pb-6 pt-5 flex gap-3">
            <?php if ($current['status'] === 'paid'): ?>
              <button type="button" onclick="openReceipt()"
                class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-ink-800 hover:bg-ink-700 text-white text-sm font-medium px-4 py-2.5 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H8a2 2 0 01-2-2V5a2 2 0 012-2h6l6 6v11a2 2 0 01-2 2z"/></svg>
                Download Receipt
              </button>
            <?php else: ?>
              <button type="button" onclick="openReceipt()"
                class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold px-4 py-2.5 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a4 4 0 00-8 0v2M5 9h14l1 11H4L5 9z"/></svg>
                Pay Now
              </button>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
      </section>

      <!-- ============================================================
           2. FINANCIAL TRENDS / COMPARISON TABLE
      ============================================================= -->
      <section id="trends" class="scroll-mt-24">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-display text-lg font-semibold text-ink-900">Financial Trends</h2>
          <span class="text-xs text-ink-600">Last <?= count($bills) ?> months</span>
        </div>

        <div class="bg-white rounded-2xl border border-paper-200 shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[720px]">
              <thead>
                <tr class="bg-paper-50 text-left text-ink-600 text-xs uppercase tracking-wide">
                  <th class="px-6 py-3.5 font-medium">Month</th>
                  <th class="px-6 py-3.5 font-medium">Base Rent</th>
                  <th class="px-6 py-3.5 font-medium">Utilities <span class="normal-case text-ink-600/60">(gas/water/elec.)</span></th>
                  <th class="px-6 py-3.5 font-medium">Service Fee</th>
                  <th class="px-6 py-3.5 font-medium">Total</th>
                  <th class="px-6 py-3.5 font-medium">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-paper-200">
                <?php foreach ($bills as $i => $b):
                  $utilities = (float)$b['electricity_bill'] + (float)$b['water_bill'] + (float)$b['gas_bill'];
                ?>
                <tr class="<?= $i === 0 ? 'bg-brand-500/5' : 'hover:bg-paper-50' ?> transition-colors">
                  <td class="px-6 py-4 font-medium text-ink-900">
                    <?= h(MONTH_NAMES[(int)$b['bill_month']]) ?> <?= h((string)$b['bill_year']) ?>
                    <?php if ($i === 0): ?><span class="ml-2 text-[10px] font-semibold text-brand-500 uppercase">Current</span><?php endif; ?>
                  </td>
                  <td class="px-6 py-4 font-figures text-ink-700"><?= money((float)$b['apartment_rent']) ?></td>
                  <td class="px-6 py-4 font-figures text-ink-700"><?= money($utilities) ?></td>
                  <td class="px-6 py-4 font-figures text-ink-700"><?= money((float)$b['service_fee']) ?></td>
                  <td class="px-6 py-4 font-figures font-semibold text-ink-900"><?= money((float)$b['total']) ?></td>
                  <td class="px-6 py-4">
                    <?php if ($b['status'] === 'paid'): ?>
                      <span class="inline-flex items-center gap-1.5 rounded-full bg-paid-50 text-paid-600 text-xs font-semibold px-2.5 py-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-paid-500"></span> Paid
                      </span>
                    <?php else: ?>
                      <span class="inline-flex items-center gap-1.5 rounded-full bg-unpaid-50 text-unpaid-600 text-xs font-semibold px-2.5 py-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-unpaid-500"></span> Unpaid
                      </span>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- ============================================================
           3. ACTION CENTER
      ============================================================= -->
      <section class="grid lg:grid-cols-2 gap-6">

        <!-- Report an Issue -->
        <div id="report-issue" class="scroll-mt-24 bg-white rounded-2xl border border-paper-200 shadow-sm p-6 sm:p-7">
          <h3 class="font-display font-semibold text-ink-900 mb-1">Report an Issue</h3>
          <p class="text-sm text-ink-600 mb-5">Let the landlord know about a maintenance problem in your unit.</p>

          <form id="issueForm" novalidate class="space-y-4">
            <div id="issueCategoryField">
              <label for="issueCategory" class="block text-sm font-medium text-ink-900 mb-1.5">Category</label>
              <select id="issueCategory" name="category" required
                class="w-full rounded-lg border border-paper-200 bg-paper-50 px-3.5 py-2.5 text-sm text-ink-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                <option value="" disabled selected>Select a category</option>
                <option value="Plumbing">Plumbing</option>
                <option value="Electrical">Electrical</option>
                <option value="Structural">Structural</option>
                <option value="Other">Other</option>
              </select>
              <p class="field-error text-xs text-unpaid-500 mt-1">Please choose a category.</p>
            </div>

            <div id="issueDescriptionField">
              <label for="issueDescription" class="block text-sm font-medium text-ink-900 mb-1.5">Description</label>
              <textarea id="issueDescription" name="description" rows="4" required minlength="10" maxlength="1000"
                placeholder="Describe what's wrong and where, e.g. 'Kitchen faucet leaking since Tuesday.'"
                class="w-full rounded-lg border border-paper-200 bg-paper-50 px-3.5 py-2.5 text-sm text-ink-900 placeholder:text-ink-600/50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent resize-none"></textarea>
              <p class="field-error text-xs text-unpaid-500 mt-1">Please add at least 10 characters describing the issue.</p>
            </div>

            <div id="issueBanner" class="hidden text-sm rounded-lg px-3.5 py-2.5"></div>

            <button type="submit"
              class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-ink-800 hover:bg-ink-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-medium px-4 py-2.5 transition">
              <span class="btn-label">Submit Complaint</span>
            </button>
          </form>
        </div>

        <!-- Move-Out Notice -->
        <div id="move-out" class="scroll-mt-24 bg-white rounded-2xl border border-paper-200 shadow-sm p-6 sm:p-7">
          <h3 class="font-display font-semibold text-ink-900 mb-1">Notice for Leaving the Apartment</h3>
          <p class="text-sm text-ink-600 mb-5">Formally notify the landlord of your intended move-out date. A minimum of 30 days' notice is required.</p>

          <form id="noticeForm" novalidate class="space-y-4">
            <div id="noticeDateField">
              <label for="moveOutDate" class="block text-sm font-medium text-ink-900 mb-1.5">Intended Departure Date</label>
              <input type="date" id="moveOutDate" name="move_out_date" required min="<?= h($minNotice) ?>"
                class="w-full rounded-lg border border-paper-200 bg-paper-50 px-3.5 py-2.5 text-sm text-ink-900 font-figures focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
              <p class="field-error text-xs text-unpaid-500 mt-1">Please choose a date at least 30 days from today.</p>
            </div>

            <div id="noticeReasonField">
              <label for="noticeReason" class="block text-sm font-medium text-ink-900 mb-1.5">Reason <span class="text-ink-600 font-normal">(optional)</span></label>
              <textarea id="noticeReason" name="reason" rows="3" maxlength="500"
                placeholder="Optional — helps the landlord plan ahead."
                class="w-full rounded-lg border border-paper-200 bg-paper-50 px-3.5 py-2.5 text-sm text-ink-900 placeholder:text-ink-600/50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent resize-none"></textarea>
              <p class="field-error text-xs text-unpaid-500 mt-1">Reason is too long.</p>
            </div>

            <div id="noticeBanner" class="hidden text-sm rounded-lg px-3.5 py-2.5"></div>

            <button type="submit"
              class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-unpaid-500 hover:bg-unpaid-600 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-medium px-4 py-2.5 transition">
              <span class="btn-label">Submit Notice</span>
            </button>
          </form>
        </div>
      </section>

      <footer class="text-center text-xs text-ink-600 pb-4">
        &copy; <?= date('Y') ?> Nestly Apartment Management System. Web Programming Course Project.
      </footer>
    </main>
  </div>
</div>

<!-- ============================================================
     RECEIPT MODAL (Pay Now / Download Receipt)
============================================================= -->
<?php if ($current): ?>
<div id="receiptModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
  <div id="receiptOverlay" class="absolute inset-0 bg-ink-900/50"></div>
  <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden">
    <div class="px-6 pt-6 pb-4 flex items-start justify-between">
      <div>
        <p class="font-display font-semibold text-ink-900">Payment Receipt</p>
        <p class="text-xs text-ink-600">Apartment <?= h($tenant['apartment_no']) ?> &middot; <?= h(MONTH_NAMES[(int)$current['bill_month']]) ?> <?= h((string)$current['bill_year']) ?></p>
      </div>
      <button onclick="closeReceipt()" class="text-ink-600 hover:text-ink-900" aria-label="Close">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
      </button>
    </div>
    <div class="px-6 py-4 space-y-2 text-sm border-y border-dashed border-paper-200">
      <div class="flex justify-between"><span class="text-ink-600">Tenant</span><span class="font-medium"><?= h($tenant['full_name']) ?></span></div>
      <div class="flex justify-between"><span class="text-ink-600">Amount</span><span class="font-figures font-semibold"><?= money((float)$current['total']) ?></span></div>
      <div class="flex justify-between"><span class="text-ink-600">Method</span><span class="font-medium">Demo Gateway</span></div>
      <div class="flex justify-between"><span class="text-ink-600">Reference</span><span class="font-figures text-xs" id="receiptRef">—</span></div>
    </div>
    <div class="px-6 py-5">
      <p id="receiptStatus" class="text-xs text-ink-600 mb-4">This is a demo transaction for coursework purposes — no real payment is processed.</p>
      <button onclick="window.print()" class="w-full rounded-lg bg-ink-800 hover:bg-ink-700 text-white text-sm font-medium px-4 py-2.5 transition">Print / Save as PDF</button>
    </div>
  </div>
</div>
<?php endif; ?>

<script src="assets/js/app.js"></script>
<script>
  // ---------- Sidebar (mobile) ----------
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');
  document.getElementById('sidebarOpen')?.addEventListener('click', () => {
    sidebar.classList.remove('-translate-x-full');
    overlay.classList.remove('hidden');
  });
  const closeSidebar = () => {
    sidebar.classList.add('-translate-x-full');
    overlay.classList.add('hidden');
  };
  document.getElementById('sidebarClose')?.addEventListener('click', closeSidebar);
  overlay?.addEventListener('click', closeSidebar);
  sidebar?.querySelectorAll('a').forEach(a => a.addEventListener('click', closeSidebar));

  // ---------- Receipt modal ----------
  function openReceipt() {
    const modal = document.getElementById('receiptModal');
    if (!modal) return;
    document.getElementById('receiptRef').textContent = 'TXN-' + Date.now().toString(36).toUpperCase();
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  }
  function closeReceipt() {
    const modal = document.getElementById('receiptModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  }
  document.getElementById('receiptOverlay')?.addEventListener('click', closeReceipt);

  // ---------- Forms (handleForm() comes from assets/js/app.js) ----------
  handleForm({
    formId: 'issueForm',
    endpoint: 'report_issue.php',
    bannerId: 'issueBanner',
    fieldMap: { category: 'issueCategoryField', description: 'issueDescriptionField' },
  });

  handleForm({
    formId: 'noticeForm',
    endpoint: 'move_out_notice.php',
    bannerId: 'noticeBanner',
    fieldMap: { move_out_date: 'noticeDateField', reason: 'noticeReasonField' },
  });
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
