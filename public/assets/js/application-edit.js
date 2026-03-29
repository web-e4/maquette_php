document.addEventListener('DOMContentLoaded', function () {

  document.querySelectorAll('.btn-edit').forEach(function (btn) {
    attachEditListener(btn);
  });

  // ── Logique d'édition inline ────────────────────────────────

  function handleEditClick(btn) {
    const field      = btn.dataset.field;
    const summaryRow = btn.closest('.summary-row');
    const valueSpan  = summaryRow.querySelector('.summary-value');

    if (summaryRow.querySelector('.inline-edit-input')) return;

    const currentValue = valueSpan.textContent.trim();

    let inputEl;
    if (field === 'cv' || field === 'lettre') {
      inputEl        = document.createElement('input');
      inputEl.type   = 'file';
      inputEl.accept = '.pdf';
      inputEl.classList.add('file-input-hidden', 'inline-edit-input');
    } else {
      inputEl        = document.createElement('input');
      inputEl.type   = field === 'email' ? 'email' : 'text';
      inputEl.value  = currentValue;
      inputEl.classList.add('form-input', 'inline-edit-input');
    }

    const validateBtn     = document.createElement('button');
    validateBtn.type      = 'button';
    validateBtn.className = 'btn-edit-confirm';
    validateBtn.title     = 'Valider';
    validateBtn.innerHTML = '<img src="/assets/icons/check.svg" alt="Valider">';

    const cancelBtn     = document.createElement('button');
    cancelBtn.type      = 'button';
    cancelBtn.className = 'btn-edit-cancel';
    cancelBtn.title     = 'Annuler';
    cancelBtn.innerHTML = '<img src="/assets/icons/x.svg" alt="Annuler">';

    valueSpan.replaceWith(inputEl);
    btn.replaceWith(validateBtn, cancelBtn);

    if (field !== 'cv' && field !== 'lettre') {
      inputEl.focus();
      inputEl.select();
    }

    cancelBtn.addEventListener('click', function () {
      inputEl.replaceWith(valueSpan);
      validateBtn.remove();
      cancelBtn.replaceWith(btn);
    });

    validateBtn.addEventListener('click', function () {
      const offerId  = getOfferIdFromUrl();
      const formData = new FormData();

      if (field === 'cv' || field === 'lettre') {
        if (!inputEl.files || inputEl.files.length === 0) {
          showInlineError(inputEl, 'Veuillez choisir un fichier PDF.');
          return;
        }
        const file = inputEl.files[0];
        if (!file.name.endsWith('.pdf')) {
          showInlineError(inputEl, 'Le fichier doit être un PDF.');
          return;
        }
        if (file.size > 5 * 1024 * 1024) {
          showInlineError(inputEl, 'Le fichier dépasse 5 Mo.');
          return;
        }
        formData.append(field, file);
      } else {
        if (!inputEl.value.trim()) {
          showInlineError(inputEl, 'Ce champ ne peut pas être vide.');
          return;
        }
        formData.append(field, inputEl.value.trim());
      }

      formData.append('_field', field);

      validateBtn.disabled  = true;
      validateBtn.innerHTML = '<img src="/assets/icons/loader.svg" alt="Chargement" class="spin">';

      fetch('/apply/update?id=' + offerId, {
        method: 'POST',
        body:   formData,
      })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          if (data.success) {
            const newValue = (field === 'cv' || field === 'lettre')
              ? data.newValue
              : formData.get(field);

            valueSpan.textContent = newValue;
            inputEl.replaceWith(valueSpan);
            validateBtn.remove();
            cancelBtn.remove();

            // Recrée le bouton crayon avec le bon listener
            const newBtn = btn.cloneNode(true);
            summaryRow.appendChild(newBtn);
            attachEditListener(newBtn); // ← appel direct, pas de dispatchEvent

            showFlash('success', data.message || 'Modification enregistrée.');
          } else {
            showFlash('error', data.message || 'Une erreur est survenue.');
            validateBtn.disabled  = false;
            validateBtn.innerHTML = '<img src="/assets/icons/check.svg" alt="Valider">';
          }
        })
        .catch(function () {
          showFlash('error', 'Impossible de contacter le serveur.');
          validateBtn.disabled  = false;
          validateBtn.innerHTML = '<img src="/assets/icons/check.svg" alt="Valider">';
        });
    });
  }

  // ── Helpers ────────────────────────────────────────────────

  function attachEditListener(btn) {
    // Branche handleEditClick sur le bouton — pas de dispatchEvent
    btn.addEventListener('click', function () {
      handleEditClick(btn);
    });
  }

  function getOfferIdFromUrl() {
    const params = new URLSearchParams(window.location.search);
    return params.get('id') || '';
  }

  function showInlineError(inputEl, message) {
    const existing = inputEl.parentElement.querySelector('.inline-error');
    if (existing) existing.remove();

    const err         = document.createElement('span');
    err.className     = 'inline-error field-hint';
    err.textContent   = message;
    err.style.color   = 'var(--color-error, #b91c1c)';
    inputEl.insertAdjacentElement('afterend', err);

    inputEl.addEventListener('input',  function () { err.remove(); }, { once: true });
    inputEl.addEventListener('change', function () { err.remove(); }, { once: true });
  }

  function showFlash(type, message) {
    const existing = document.querySelector('.flash');
    if (existing) existing.remove();

    const flash         = document.createElement('div');
    flash.className     = 'flash flash--' + type;
    flash.textContent   = message;

    const container = document.querySelector('.publish-header');
    if (container) container.insertAdjacentElement('afterend', flash);

    setTimeout(function () {
      flash.style.transition = 'opacity 0.4s';
      flash.style.opacity    = '0';
      setTimeout(function () { flash.remove(); }, 400);
    }, 4000);
  }

});