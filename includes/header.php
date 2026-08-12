<?php
/**
 * includes/header.php
 * ------------------------------------------------------------
 * Shared <head> + opening <body> tag for every page in the app.
 * Include this at the top of a page, then include
 * includes/footer.php at the bottom.
 *
 * Optional variable a page can set before including this file:
 *   $pageTitle  (string)  — shown in the browser tab. Defaults below.
 * ------------------------------------------------------------
 */
$pageTitle = $pageTitle ?? 'Nestly · Apartment Management System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          ink:    { 900:'#141A26', 800:'#1C2434', 700:'#26304A', 600:'#374361' },
          paper:  { 50:'#F7F8FA', 100:'#EFF1F5', 200:'#E3E7EE' },
          brand:  { 500:'#3457D5', 600:'#2A46B3' },
          paid:   { 50:'#ECFDF5', 500:'#0F766E', 600:'#0B5C56' },
          pending:{ 50:'#FFFBEB', 500:'#B45309' },
          unpaid: { 50:'#FEF2F2', 500:'#B91C1C', 600:'#991B1B' },
        },
        fontFamily: {
          display: ['"Space Grotesk"', 'sans-serif'],
          body: ['Inter', 'sans-serif'],
          mono: ['"JetBrains Mono"', 'monospace'],
        },
      }
    }
  }
</script>
<style>
  body { font-family: 'Inter', sans-serif; }
  .font-display { font-family: 'Space Grotesk', sans-serif; }
  .font-figures { font-family: 'JetBrains Mono', monospace; font-variant-numeric: tabular-nums; }

  ::-webkit-scrollbar { height: 8px; width: 8px; }
  ::-webkit-scrollbar-thumb { background: #CBD2E0; border-radius: 999px; }

  /* Shared form-validation styling used by every form in the app */
  .field-error { display: none; }
  .field-invalid .field-error { display: block; }
  .field-invalid input, .field-invalid select, .field-invalid textarea {
    border-color: #B91C1C !important;
    background-color: #FEF2F2;
  }
</style>
<?php if (!empty($extraHead)) echo $extraHead; /* page-specific <style>/<link> can be injected here */ ?>
</head>
<body class="bg-paper-50 text-ink-900 antialiased">
