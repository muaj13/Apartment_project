<?php
/**
 * tools.php — Guest Tools page
 * ------------------------------------------------------------
 * Accessible without signing in ("Guest user — tools only" from
 * the home page). The calculator itself runs entirely client-side
 * in JS; no backend/database calls happen on this page at all.
 * ------------------------------------------------------------
 */
declare(strict_types=1);
session_start();

$pageTitle = 'Tools · Nestly';
include __DIR__ . '/includes/header.php';
?>

<header class="sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-paper-200">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-10 h-16 flex items-center justify-between">
    <a href="index.php" class="flex items-center gap-2.5">
      <div class="w-8 h-8 rounded-lg bg-brand-500 flex items-center justify-center font-display font-bold text-white text-xs">AM</div>
      <span class="font-display font-semibold text-ink-900">Nestly</span>
    </a>
    <a href="index.php#access" class="inline-flex items-center rounded-lg bg-ink-800 hover:bg-ink-700 text-white text-sm font-medium px-4 py-2 transition">
      Sign In
    </a>
  </div>
</header>

<main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-10 py-12 lg:py-16">
  <div class="mb-8">
    <span class="inline-block text-xs font-semibold tracking-wide uppercase text-brand-500 bg-brand-500/10 rounded-full px-3 py-1 mb-4">
      Guest Tool · No account needed
    </span>
    <h1 class="font-display text-2xl sm:text-3xl font-semibold text-ink-900">Monthly Bill Calculator</h1>
    <p class="mt-2 text-ink-600">Estimate a total monthly bill from rent, utilities, and service fees — useful before signing a lease.</p>
  </div>

  <div class="bg-white rounded-2xl border border-paper-200 shadow-sm p-6 sm:p-8">
    <form id="calcForm" class="grid sm:grid-cols-2 gap-5" novalidate>
      <div>
        <label for="calcRent" class="block text-sm font-medium text-ink-900 mb-1.5">Base Apartment Rent</label>
        <input type="number" id="calcRent" min="0" step="0.01" value="15000" inputmode="decimal"
          class="calc-input w-full rounded-lg border border-paper-200 bg-paper-50 px-3.5 py-2.5 text-sm font-figures focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
      </div>
      <div>
        <label for="calcElectricity" class="block text-sm font-medium text-ink-900 mb-1.5">Electricity Bill</label>
        <input type="number" id="calcElectricity" min="0" step="0.01" value="1850" inputmode="decimal"
          class="calc-input w-full rounded-lg border border-paper-200 bg-paper-50 px-3.5 py-2.5 text-sm font-figures focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
      </div>
      <div>
        <label for="calcWater" class="block text-sm font-medium text-ink-900 mb-1.5">Water Bill</label>
        <input type="number" id="calcWater" min="0" step="0.01" value="600" inputmode="decimal"
          class="calc-input w-full rounded-lg border border-paper-200 bg-paper-50 px-3.5 py-2.5 text-sm font-figures focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
      </div>
      <div>
        <label for="calcGas" class="block text-sm font-medium text-ink-900 mb-1.5">Gas Bill</label>
        <input type="number" id="calcGas" min="0" step="0.01" value="450" inputmode="decimal"
          class="calc-input w-full rounded-lg border border-paper-200 bg-paper-50 px-3.5 py-2.5 text-sm font-figures focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
      </div>
      <div class="sm:col-span-2">
        <label for="calcService" class="block text-sm font-medium text-ink-900 mb-1.5">Service Fee <span class="text-ink-600 font-normal">(guard · lift · trash)</span></label>
        <input type="number" id="calcService" min="0" step="0.01" value="1200" inputmode="decimal"
          class="calc-input w-full rounded-lg border border-paper-200 bg-paper-50 px-3.5 py-2.5 text-sm font-figures focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
      </div>
    </form>

    <div class="mt-6 pt-6 border-t border-dashed border-paper-200 flex items-center justify-between">
      <span class="font-display font-semibold text-ink-900">Estimated Total</span>
      <span id="calcTotal" class="font-figures font-bold text-2xl text-ink-900">৳0.00</span>
    </div>
    <p class="mt-2 text-xs text-ink-600">Updates automatically as you type. This estimate isn't saved anywhere.</p>
  </div>

  <div class="mt-8 text-center">
    <p class="text-sm text-ink-600">Want itemized statements, payment history, and issue reporting too?</p>
    <a href="index.php#access" class="inline-block mt-2 text-sm font-medium text-brand-500 hover:underline">Create a free account →</a>
  </div>
</main>

<footer class="border-t border-paper-200 py-8 text-center text-xs text-ink-600 mt-8">
  &copy; <?= date('Y') ?> Nestly Apartment Management System. Web Programming Course Project.
</footer>

<script>
  const calcInputs = document.querySelectorAll('.calc-input');
  const calcTotal  = document.getElementById('calcTotal');

  function formatMoney(amount) {
    return '৳' + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function recalculate() {
    let total = 0;
    calcInputs.forEach(input => {
      const value = parseFloat(input.value);
      total += Number.isFinite(value) && value >= 0 ? value : 0;
    });
    calcTotal.textContent = formatMoney(total);
  }

  calcInputs.forEach(input => input.addEventListener('input', recalculate));
  recalculate();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
