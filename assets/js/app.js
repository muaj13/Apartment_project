/**
 * assets/js/app.js
 * ------------------------------------------------------------
 * Shared frontend helpers reused across pages.
 * Include with: <script src="assets/js/app.js"></script>
 * ------------------------------------------------------------
 */

/**
 * Wires up a form to submit via fetch() to a PHP endpoint, showing
 * inline field errors and a success/error banner. Used identically
 * by the dashboard's Report Issue / Move-Out forms and the home
 * page's Sign In / Sign Up forms — one implementation, many forms.
 *
 * @param {Object}   opts
 * @param {string}   opts.formId    - id of the <form>
 * @param {string}   opts.endpoint  - PHP endpoint to POST to
 * @param {string}   opts.bannerId  - id of the element showing success/error text
 * @param {Object}   opts.fieldMap  - { inputName: wrapperElementId } for error highlighting
 * @param {Function} [opts.onSuccess] - called with the parsed response data on success
 */
function handleForm({ formId, endpoint, bannerId, fieldMap, onSuccess }) {
  const form = document.getElementById(formId);
  if (!form) return;
  const banner = document.getElementById(bannerId);
  const button = form.querySelector('button[type="submit"]');
  const label  = button.querySelector('.btn-label') || button;
  const originalLabel = label.textContent;

  function clearErrors() {
    Object.values(fieldMap).forEach(wrapId => {
      document.getElementById(wrapId)?.classList.remove('field-invalid');
    });
    banner.classList.add('hidden');
    banner.textContent = '';
  }

  function showBanner(message, isError) {
    banner.textContent = message;
    banner.classList.remove('hidden', 'bg-paid-50', 'text-paid-600', 'bg-unpaid-50', 'text-unpaid-600');
    banner.classList.add(isError ? 'bg-unpaid-50' : 'bg-paid-50', isError ? 'text-unpaid-600' : 'text-paid-600');
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    clearErrors();

    if (!form.checkValidity()) {
      form.querySelectorAll(':invalid').forEach(el => {
        const wrapId = fieldMap[el.name];
        if (wrapId) document.getElementById(wrapId)?.classList.add('field-invalid');
      });
      showBanner('Please fix the highlighted fields.', true);
      return;
    }

    button.disabled = true;
    label.textContent = 'Submitting…';

    try {
      const res = await fetch(endpoint, { method: 'POST', body: new FormData(form) });
      const data = await res.json();

      if (!res.ok || !data.ok) {
        if (data.errors) {
          Object.keys(data.errors).forEach(fieldName => {
            const wrapId = fieldMap[fieldName];
            if (wrapId) document.getElementById(wrapId)?.classList.add('field-invalid');
          });
        }
        showBanner(data.error || 'Please fix the highlighted fields.', true);
        return;
      }

      showBanner(data.message || 'Submitted successfully.', false);
      form.reset();
      if (onSuccess) onSuccess(data);
    } catch (err) {
      showBanner('Network error — please try again.', true);
    } finally {
      button.disabled = false;
      label.textContent = originalLabel;
    }
  });
}
