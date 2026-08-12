<?php
/**
 * index.php — Home page
 * ------------------------------------------------------------
 * Public entry point: pick a role (Tenant / Landlord), sign in
 * or sign up, or continue as a Guest (tools only, no account).
 * ------------------------------------------------------------
 */
declare(strict_types=1);
require_once __DIR__ . '/data.php';

// If already signed in, skip the home page and go straight to the right dashboard.
if (!empty($_SESSION['role']) && $_SESSION['role'] === 'tenant' && !empty($_SESSION['tenant_id'])) {
    header('Location: dashboard.php'); exit;
}
if (!empty($_SESSION['role']) && $_SESSION['role'] === 'landlord' && !empty($_SESSION['landlord_id'])) {
    header('Location: landlord_dashboard.php'); exit;
}

$pageTitle = 'Nestly · Apartment Management System';
include __DIR__ . '/includes/header.php';
?>

<!-- ============================================================
     TOP NAV
============================================================= -->
<header class="sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-paper-200">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-10 h-16 flex items-center justify-between">
    <a href="index.php" class="flex items-center gap-2.5">
      <div class="w-8 h-8 rounded-lg bg-brand-500 flex items-center justify-center font-display font-bold text-white text-xs">AM</div>
      <span class="font-display font-semibold text-ink-900">Nestly</span>
    </a>
    <nav class="hidden sm:flex items-center gap-8 text-sm text-ink-700">
      <a href="#features" class="hover:text-ink-900 transition">Features</a>
      <a href="#tools" class="hover:text-ink-900 transition">Tools</a>
    </nav>
    <a href="#access" class="inline-flex items-center rounded-lg bg-ink-800 hover:bg-ink-700 text-white text-sm font-medium px-4 py-2 transition">
      Sign In
    </a>
  </div>
</header>

<!-- ============================================================
     HERO + ACCESS CARD
============================================================= -->
<section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-10 py-14 lg:py-20 grid lg:grid-cols-2 gap-12 items-center">

  <div>
    <span class="inline-block text-xs font-semibold tracking-wide uppercase text-brand-500 bg-brand-500/10 rounded-full px-3 py-1 mb-5">
      Web Programming Course Project
    </span>
    <h1 class="font-display text-4xl sm:text-5xl font-semibold text-ink-900 leading-tight">
      Rent, bills, and repairs —<br class="hidden sm:block"> all in one ledger.
    </h1>
    <p class="mt-5 text-ink-600 text-base sm:text-lg max-w-md">
      Nestly gives landlords a clear view of every unit's payments, and gives tenants
      a transparent, itemized statement every single month.
    </p>
    <div class="mt-8 flex flex-wrap gap-3">
      <div class="flex items-center gap-2 text-sm text-ink-700">
        <span class="w-1.5 h-1.5 rounded-full bg-paid-500"></span> Itemized monthly statements
      </div>
      <div class="flex items-center gap-2 text-sm text-ink-700">
        <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span> Issue reporting &amp; move-out notices
      </div>
    </div>
  </div>

  <!-- Access card -->
  <div id="access" class="scroll-mt-24 bg-white rounded-2xl shadow-sm border border-paper-200 p-6 sm:p-8 max-w-md w-full mx-auto lg:ml-auto lg:mr-0">

    <!-- Role toggle -->
    <div class="grid grid-cols-2 gap-2 bg-paper-100 rounded-xl p-1 mb-6" role="tablist" aria-label="Account type">
      <button type="button" id="roleTenantBtn" onclick="setRole('tenant')"
        class="role-btn rounded-lg py-2 text-sm font-medium transition">Tenant</button>
      <button type="button" id="roleLandlordBtn" onclick="setRole('landlord')"
        class="role-btn rounded-lg py-2 text-sm font-medium transition">Landlord / Admin</button>
    </div>

    <!-- Sign in / Sign up tab switch -->
    <div class="flex items-center gap-6 border-b border-paper-200 mb-6">
      <button type="button" id="tabSignInBtn" onclick="setTab('signin')"
        class="tab-btn -mb-px pb-3 text-sm font-medium border-b-2 transition">Sign In</button>
      <button type="button" id="tabSignUpBtn" onclick="setTab('signup')"
        class="tab-btn -mb-px pb-3 text-sm font-medium border-b-2 transition">Sign Up</button>
    </div>

    <!-- Sign In form -->
    <form id="signInForm" novalidate class="space-y-4">
      <input type="hidden" name="action" value="signin">
      <input type="hidden" name="role" id="signInRole" value="tenant">

      <div id="signInEmailField">
        <label for="signInEmail" class="block text-sm font-medium text-ink-900 mb-1.5">Email</label>
        <input type="email" id="signInEmail" name="email" required placeholder="you@example.com"
          class="w-full rounded-lg border border-paper-200 bg-paper-50 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
        <p class="field-error text-xs text-unpaid-500 mt-1">Enter a valid email address.</p>
      </div>

      <div id="signInPasswordField">
        <label for="signInPassword" class="block text-sm font-medium text-ink-900 mb-1.5">Password</label>
        <input type="password" id="signInPassword" name="password" required minlength="1" placeholder="••••••••"
          class="w-full rounded-lg border border-paper-200 bg-paper-50 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
        <p class="field-error text-xs text-unpaid-500 mt-1">Password is required.</p>
      </div>

      <div id="signInBanner" class="hidden text-sm rounded-lg px-3.5 py-2.5"></div>

      <button type="submit"
        class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 hover:bg-brand-600 disabled:opacity-50 text-white text-sm font-semibold px-4 py-2.5 transition">
        <span class="btn-label">Sign In</span>
      </button>
      <p class="text-xs text-ink-600 text-center">
        Demo: <span class="font-figures">ariana.karim@example.com</span> (tenant) or
        <span class="font-figures">rafiq.hasan@example.com</span> (landlord) — any password.
      </p>
    </form>

    <!-- Sign Up form -->
    <form id="signUpForm" novalidate class="space-y-4 hidden">
      <input type="hidden" name="action" value="signup">
      <input type="hidden" name="role" id="signUpRole" value="tenant">

      <div id="signUpNameField">
        <label for="signUpName" class="block text-sm font-medium text-ink-900 mb-1.5">Full Name</label>
        <input type="text" id="signUpName" name="full_name" required minlength="2" placeholder="Jane Doe"
          class="w-full rounded-lg border border-paper-200 bg-paper-50 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
        <p class="field-error text-xs text-unpaid-500 mt-1">Enter your full name.</p>
      </div>

      <div id="signUpEmailField">
        <label for="signUpEmail" class="block text-sm font-medium text-ink-900 mb-1.5">Email</label>
        <input type="email" id="signUpEmail" name="email" required placeholder="you@example.com"
          class="w-full rounded-lg border border-paper-200 bg-paper-50 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
        <p class="field-error text-xs text-unpaid-500 mt-1">Enter a valid email address.</p>
      </div>

      <div id="signUpPasswordField">
        <label for="signUpPassword" class="block text-sm font-medium text-ink-900 mb-1.5">Password</label>
        <input type="password" id="signUpPassword" name="password" required minlength="8" placeholder="At least 8 characters"
          class="w-full rounded-lg border border-paper-200 bg-paper-50 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
        <p class="field-error text-xs text-unpaid-500 mt-1">Password must be at least 8 characters.</p>
      </div>

      <div id="signUpConfirmField">
        <label for="signUpConfirm" class="block text-sm font-medium text-ink-900 mb-1.5">Confirm Password</label>
        <input type="password" id="signUpConfirm" name="confirm_password" required minlength="8" placeholder="Repeat password"
          class="w-full rounded-lg border border-paper-200 bg-paper-50 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
        <p class="field-error text-xs text-unpaid-500 mt-1">Passwords do not match.</p>
      </div>

      <div id="signUpBanner" class="hidden text-sm rounded-lg px-3.5 py-2.5"></div>

      <button type="submit"
        class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 hover:bg-brand-600 disabled:opacity-50 text-white text-sm font-semibold px-4 py-2.5 transition">
        <span class="btn-label">Create Account</span>
      </button>
    </form>

    <!-- Guest -->
    <div class="mt-6 pt-6 border-t border-paper-200 text-center">
      <p class="text-xs text-ink-600 mb-3">Just want to try the calculator? No account needed.</p>
      <a href="tools.php"
        class="inline-flex items-center justify-center gap-2 w-full rounded-lg bg-paper-100 hover:bg-paper-200 text-ink-800 text-sm font-medium px-4 py-2.5 transition">
        Continue as Guest
      </a>
    </div>
  </div>
</section>

<!-- ============================================================
     FEATURES
============================================================= -->
<section id="features" class="scroll-mt-24 bg-white border-y border-paper-200">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-10 py-16">
    <div class="max-w-xl mb-10">
      <h2 class="font-display text-2xl sm:text-3xl font-semibold text-ink-900">Our Features</h2>
      <p class="mt-2 text-ink-600">Everything a landlord and tenant need to stay on the same page, every month.</p>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php
      $features = [
        ['icon' => 'receipt', 'title' => 'Itemized Statements', 'desc' => 'Rent, electricity, water, gas, and service fees broken down clearly every month, with an automatic total.'],
        ['icon' => 'trend',   'title' => 'Financial Trends', 'desc' => 'Compare the current month against the last three at a glance — no spreadsheets required.'],
        ['icon' => 'badge',   'title' => 'Payment Status', 'desc' => 'Clear Paid / Unpaid badges for landlords and tenants alike, updated the moment a payment lands.'],
        ['icon' => 'wrench',  'title' => 'Issue Reporting', 'desc' => 'Tenants report plumbing, electrical, or structural issues directly — landlords see them instantly.'],
        ['icon' => 'door',    'title' => 'Move-Out Notices', 'desc' => 'A formal, dated notice workflow that respects the standard 30-day notice period.'],
        ['icon' => 'users',   'title' => 'Two Dashboards', 'desc' => 'A tenant view for personal bills, and a landlord view across every unit in the building.'],
      ];
      $icons = [
        'receipt' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m6 10V7M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/>',
        'trend'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14"/>',
        'badge'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'wrench'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M11 4a4 4 0 106 6l6 6-3 3-6-6a4 4 0 10-3-9z"/>',
        'door'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>',
        'users'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 100-8 4 4 0 000 8zm6 4c0-2.21-2.69-4-6-4s-6 1.79-6 4"/>',
      ];
      foreach ($features as $f): ?>
      <div class="rounded-2xl border border-paper-200 p-6 hover:shadow-sm transition">
        <div class="w-10 h-10 rounded-lg bg-brand-500/10 text-brand-500 flex items-center justify-center mb-4">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><?= $icons[$f['icon']] ?></svg>
        </div>
        <h3 class="font-display font-semibold text-ink-900 mb-1.5"><?= h($f['title']) ?></h3>
        <p class="text-sm text-ink-600 leading-relaxed"><?= h($f['desc']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============================================================
     TOOLS (guest-accessible)
============================================================= -->
<section id="tools" class="scroll-mt-24">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-10 py-16">
    <div class="max-w-xl mb-10">
      <h2 class="font-display text-2xl sm:text-3xl font-semibold text-ink-900">Tools</h2>
      <p class="mt-2 text-ink-600">Available to everyone — no account required.</p>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <a href="tools.php" class="group rounded-2xl border border-paper-200 bg-white p-6 hover:border-brand-500 hover:shadow-sm transition">
        <div class="w-10 h-10 rounded-lg bg-paid-50 text-paid-600 flex items-center justify-center mb-4">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m-6 4h6m-6 4h3M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
        </div>
        <h3 class="font-display font-semibold text-ink-900 mb-1.5">Monthly Bill Calculator</h3>
        <p class="text-sm text-ink-600 leading-relaxed">Estimate a total monthly bill from rent, utilities, and service fees before signing a lease.</p>
        <span class="inline-block mt-4 text-sm font-medium text-brand-500 group-hover:underline">Open tool →</span>
      </a>

      <div class="rounded-2xl border border-dashed border-paper-200 p-6 text-ink-600/60">
        <div class="w-10 h-10 rounded-lg bg-paper-100 flex items-center justify-center mb-4">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h3 class="font-display font-semibold mb-1.5">Move-Out Notice Checker</h3>
        <p class="text-sm leading-relaxed">Coming soon — check if a chosen date meets the 30-day notice requirement.</p>
      </div>

      <div class="rounded-2xl border border-dashed border-paper-200 p-6 text-ink-600/60">
        <div class="w-10 h-10 rounded-lg bg-paper-100 flex items-center justify-center mb-4">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14"/></svg>
        </div>
        <h3 class="font-display font-semibold mb-1.5">Rent Trend Estimator</h3>
        <p class="text-sm leading-relaxed">Coming soon — project next month's bill from your last 3 months of usage.</p>
      </div>
    </div>
  </div>
</section>

<footer class="border-t border-paper-200 py-8 text-center text-xs text-ink-600">
  &copy; <?= date('Y') ?> Nestly Apartment Management System. Web Programming Course Project.
</footer>

<style>
  .role-btn { color: #374361; }
  .role-btn.active { background: white; color: #141A26; box-shadow: 0 1px 2px rgba(20,26,38,0.08); }
  .tab-btn { color: #374361; border-color: transparent; }
  .tab-btn.active { color: #3457D5; border-color: #3457D5; }
</style>

<script src="assets/js/app.js"></script>
<script>
  // ---------- Role + tab state ----------
  let currentRole = 'tenant';
  let currentTab  = 'signin';

  function setRole(role) {
    currentRole = role;
    document.getElementById('signInRole').value = role;
    document.getElementById('signUpRole').value = role;
    document.getElementById('roleTenantBtn').classList.toggle('active', role === 'tenant');
    document.getElementById('roleLandlordBtn').classList.toggle('active', role === 'landlord');
  }

  function setTab(tab) {
    currentTab = tab;
    document.getElementById('signInForm').classList.toggle('hidden', tab !== 'signin');
    document.getElementById('signUpForm').classList.toggle('hidden', tab !== 'signup');
    document.getElementById('tabSignInBtn').classList.toggle('active', tab === 'signin');
    document.getElementById('tabSignUpBtn').classList.toggle('active', tab === 'signup');
  }

  setRole('tenant');
  setTab('signin');

  // ---------- Forms (handleForm() comes from assets/js/app.js) ----------
  handleForm({
    formId: 'signInForm',
    endpoint: 'auth.php',
    bannerId: 'signInBanner',
    fieldMap: { email: 'signInEmailField', password: 'signInPasswordField' },
    onSuccess: (data) => { if (data.redirect) window.location.href = data.redirect; },
  });

  handleForm({
    formId: 'signUpForm',
    endpoint: 'auth.php',
    bannerId: 'signUpBanner',
    fieldMap: {
      full_name: 'signUpNameField',
      email: 'signUpEmailField',
      password: 'signUpPasswordField',
      confirm_password: 'signUpConfirmField',
    },
    onSuccess: (data) => { if (data.redirect) window.location.href = data.redirect; },
  });
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
