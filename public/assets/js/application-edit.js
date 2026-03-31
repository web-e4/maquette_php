document.addEventListener('DOMContentLoaded', function () {

  const offerId = new URLSearchParams(window.location.search).get('id') || '';

  document.querySelectorAll('.btn-edit').forEach(attachEditListener);

  const deleteBtn = document.querySelector('.btn-delete-letter');
  if (deleteBtn) attachDeleteListener(deleteBtn);

  // ── Remplacement de fichier ─────────────────────────────────

  function attachEditListener(btn) {
    btn.addEventListener('click', function () {
      const field      = btn.dataset.field; // 'cv' ou 'letter'
      const summaryRow = btn.closest('.summary-row');

      if (summaryRow.querySelector('.inline-edit-input')) return;

      const valueSpan = summaryRow.querySelector('.summary-value');

      // Masque les boutons existants dans la ligne
      summaryRow.querySelectorAll('.btn-edit, .btn-delete-letter').forEach(function (b) {
        b.style.display = 'none';
      });

      // Crée le wrapper inline
      const wrapper = document.createElement('div');
      wrapper.className = 'inline-file-replace';
      wrapper.style.cssText = 'display:flex;align-items:center;gap:8px;flex:1;flex-wrap:wrap';

      const fileLabel = document.createElement('label');
      fileLabel.className = 'file-upload-btn';
      fileLabel.style.cssText = 'margin:0;flex:1;min-width:0';

      const fileInput = document.createElement('input');
      fileInput.type  = 'file';
      fileInput.accept = '.pdf';
      fileInput.classList.add('file-input-hidden', 'inline-edit-input');
      fileInput.id = 'inline-file-' + field;
      fileLabel.htmlFor = fileInput.id;

      const labelText = document.createElement('span');
      labelText.textContent = 'Choisir un PDF…';
      labelText.style.cssText = 'overflow:hidden;text-overflow:ellipsis;white-space:nowrap';

      fileLabel.append(fileInput, labelText);

      const confirmBtn = document.createElement('button');
      confirmBtn.type      = 'button';
      confirmBtn.className = 'btn btn-solid';
      confirmBtn.style.cssText = 'padding:6px 14px;font-size:0.85rem';
      confirmBtn.textContent = 'Valider';

      const cancelBtn = document.createElement('button');
      cancelBtn.type      = 'button';
      cancelBtn.className = 'btn btn-ghost';
      cancelBtn.style.cssText = 'padding:6px 14px;font-size:0.85rem';
      cancelBtn.textContent = 'Annuler';

      wrapper.append(fileLabel, confirmBtn, cancelBtn);
      valueSpan.replaceWith(wrapper);

      // Met à jour le label quand un fichier est sélectionné
      fileInput.addEventListener('change', function () {
        labelText.textContent = fileInput.files[0] ? fileInput.files[0].name : 'Choisir un PDF…';
      });

      cancelBtn.addEventListener('click', function () {
        wrapper.replaceWith(valueSpan);
        summaryRow.querySelectorAll('.btn-edit, .btn-delete-letter').forEach(function (b) {
          b.style.display = '';
        });
      });

      confirmBtn.addEventListener('click', function () {
        if (!fileInput.files || fileInput.files.length === 0) {
          showInlineError(labelText, 'Veuillez choisir un fichier PDF.');
          return;
        }
        const file = fileInput.files[0];
        if (!file.name.toLowerCase().endsWith('.pdf')) {
          showInlineError(labelText, 'Le fichier doit être un PDF.');
          return;
        }
        if (file.size > 5 * 1024 * 1024) {
          showInlineError(labelText, 'Le fichier dépasse 5 Mo.');
          return;
        }

        confirmBtn.disabled     = true;
        confirmBtn.textContent  = 'Envoi…';

        const formData = new FormData();
        formData.append(field, file);
        formData.append('_field', field);

        fetch('/apply/update?id=' + offerId, { method: 'POST', body: formData })
          .then(function (r) { return r.json(); })
          .then(function (data) {
            if (data.success) {
              valueSpan.textContent = data.newValue;
              valueSpan.style.color = '';
              wrapper.replaceWith(valueSpan);
              summaryRow.querySelectorAll('.btn-edit, .btn-delete-letter').forEach(function (b) {
                b.style.display = '';
              });
              showFlash('success', 'Fichier remplacé avec succès.');
            } else {
              showFlash('error', data.message || 'Une erreur est survenue.');
              confirmBtn.disabled    = false;
              confirmBtn.textContent = 'Valider';
            }
          })
          .catch(function () {
            showFlash('error', 'Impossible de contacter le serveur.');
            confirmBtn.disabled    = false;
            confirmBtn.textContent = 'Valider';
          });
      });
    });
  }

  // ── Suppression de la lettre ────────────────────────────────

  function attachDeleteListener(btn) {
    btn.addEventListener('click', function () {
      if (!confirm('Supprimer la lettre de motivation ?')) return;

      const summaryRow = btn.closest('.summary-row');
      const formData   = new FormData();
      formData.append('_field', 'delete_letter');

      fetch('/apply/update?id=' + offerId, { method: 'POST', body: formData })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (data.success) {
            // Réinitialise la ligne : "Non fournie" + bouton crayon (ajouter)
            summaryRow.innerHTML =
              '<span class="form-input summary-value" style="color:var(--color-text-muted)">Non fournie</span>' +
              '<button class="btn-edit" type="button" data-field="letter" title="Ajouter">' +
              '<img src="/assets/icons/pencil.svg" alt="Ajouter"></button>';
            attachEditListener(summaryRow.querySelector('.btn-edit'));
            showFlash('success', 'Lettre de motivation supprimée.');
          } else {
            showFlash('error', data.message || 'Une erreur est survenue.');
          }
        })
        .catch(function () {
          showFlash('error', 'Impossible de contacter le serveur.');
        });
    });
  }

  // ── Helpers ─────────────────────────────────────────────────

  function showInlineError(inputEl, message) {
    const existing = inputEl.parentElement.querySelector('.inline-error');
    if (existing) existing.remove();
    const err       = document.createElement('span');
    err.className   = 'inline-error field-hint';
    err.textContent = message;
    err.style.color = 'var(--color-error, #b91c1c)';
    inputEl.insertAdjacentElement('afterend', err);
    inputEl.addEventListener('change', function () { err.remove(); }, { once: true });
  }

  function showFlash(type, message) {
    const existing = document.querySelector('.flash');
    if (existing) existing.remove();
    const flash       = document.createElement('div');
    flash.className   = 'flash flash--' + type;
    flash.textContent = message;
    const anchor = document.querySelector('.publish-header');
    if (anchor) anchor.insertAdjacentElement('afterend', flash);
    setTimeout(function () {
      flash.style.transition = 'opacity 0.4s';
      flash.style.opacity    = '0';
      setTimeout(function () { flash.remove(); }, 400);
    }, 4000);
  }

});
