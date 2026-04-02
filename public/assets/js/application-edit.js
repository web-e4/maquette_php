document.addEventListener('DOMContentLoaded', function () {

  // récupère l'id de l'offre depuis l'URL (/apply/21 ou ?id=21)
  var pathMatch = window.location.pathname.match(/\/apply\/(\d+)/);
  var offerId = pathMatch ? pathMatch[1] : (new URLSearchParams(window.location.search).get('id') || '');

  // attache les boutons "modifier"
  document.querySelectorAll('.btn-edit').forEach(attachEditListener);

  // ----------------------------------------------------------------
  // Quand on clique sur le crayon
  // ----------------------------------------------------------------

  function attachEditListener(btn) {
    btn.addEventListener('click', function () {

      var field = btn.dataset.field; // 'cv' ou 'letter'
      var row = btn.closest('.summary-row');

      // si un champ inline est déjà affiché, ne rien faire
      if (row.querySelector('.inline-edit-input, .inline-letter-textarea')) return;

      if (field === 'letter') {
        openLetterEditor(row, btn);
      } else if (field === 'cv') {
        openCvEditor(row, btn);
      }
    });
  }

  // ----------------------------------------------------------------
  // Éditeur inline pour la lettre de motivation (textarea)
  // ----------------------------------------------------------------

  function openLetterEditor(row, btn) {
    var valueEl = row.querySelector('.summary-value');
    var currentText = valueEl ? valueEl.textContent.trim() : '';

    // cache le bouton
    btn.style.display = 'none';

    var wrapper = document.createElement('div');
    wrapper.style.cssText = 'display: flex; flex-direction: column; gap: 8px; flex: 1';

    var textarea = document.createElement('textarea');
    textarea.className = 'form-input inline-letter-textarea';
    textarea.rows = 6;
    textarea.value = currentText;
    textarea.style.cssText = 'width: 100%; resize: vertical';

    var actions = document.createElement('div');
    actions.style.cssText = 'display: flex; gap: 8px';

    var confirmBtn = document.createElement('button');
    confirmBtn.type = 'button';
    confirmBtn.className = 'btn btn-solid';
    confirmBtn.style.cssText = 'padding: 6px 14px; font-size: 0.85rem';
    confirmBtn.textContent = 'Enregistrer';

    var cancelBtn = document.createElement('button');
    cancelBtn.type = 'button';
    cancelBtn.className = 'btn btn-ghost';
    cancelBtn.style.cssText = 'padding: 6px 14px; font-size: 0.85rem';
    cancelBtn.textContent = 'Annuler';

    actions.appendChild(confirmBtn);
    actions.appendChild(cancelBtn);
    wrapper.appendChild(textarea);
    wrapper.appendChild(actions);

    if (valueEl) {
      valueEl.replaceWith(wrapper);
    } else {
      row.insertBefore(wrapper, btn);
    }

    textarea.focus();

    cancelBtn.addEventListener('click', function () {
      if (valueEl) {
        wrapper.replaceWith(valueEl);
      } else {
        wrapper.remove();
      }
      btn.style.display = '';
    });

    confirmBtn.addEventListener('click', function () {
      var text = textarea.value.trim();
      confirmBtn.disabled = true;
      confirmBtn.textContent = 'Envoi…';

      var formData = new FormData();
      formData.append('_field', 'letter');
      formData.append('letter_text', text);
      formData.append('csrf_token', document.querySelector('meta[name="csrf_token"]') ? document.querySelector('meta[name="csrf_token"]').content : '');

      fetch('/apply/update?id=' + offerId, { method: 'POST', body: formData })
        .then(function (response) { return response.json(); })
        .then(function (data) {
          if (data.success) {
            // recrée le span avec le nouveau texte
            if (!valueEl) {
              valueEl = document.createElement('p');
              valueEl.className = 'form-input summary-value letter-text';
              valueEl.style.cssText = 'white-space: pre-wrap; min-height: 80px';
            }
            if (text !== '') {
              valueEl.textContent = text;
            } else {
              valueEl.textContent = 'Non fournie';
              valueEl.style.color = 'var(--color-text-muted)';
            }
            wrapper.replaceWith(valueEl);
            btn.style.display = '';
            showFlash('success', 'Lettre de motivation mise à jour.');
          } else {
            showFlash('error', data.message || 'Une erreur est survenue.');
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Enregistrer';
          }
        })
        .catch(function () {
          showFlash('error', 'Impossible de contacter le serveur.');
          confirmBtn.disabled = false;
          confirmBtn.textContent = 'Enregistrer';
        });
    });
  }

  // ----------------------------------------------------------------
  // Éditeur inline pour le CV (remplacement de fichier PDF)
  // ----------------------------------------------------------------

  function openCvEditor(row, btn) {
    var valueEl = row.querySelector('.summary-value');

    row.querySelectorAll('.btn-edit').forEach(function (b) { b.style.display = 'none'; });

    var wrapper = document.createElement('div');
    wrapper.innerHTML =
      '<label class="file-upload-btn" style="margin: 0; flex: 1; min-width: 0">' +
        '<input type="file" id="inline-file-cv" accept=".pdf" class="file-input-hidden inline-edit-input">' +
        '<span class="file-label-text" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap">Choisir un PDF…</span>' +
      '</label>' +
      '<button type="button" class="btn btn-solid btn-confirm" style="padding: 6px 14px; font-size: 0.85rem">Valider</button>' +
      '<button type="button" class="btn btn-ghost btn-cancel" style="padding: 6px 14px; font-size: 0.85rem">Annuler</button>';
    wrapper.className = 'inline-file-replace';
    wrapper.style.cssText = 'display: flex; align-items: center; gap: 8px; flex: 1; flex-wrap: wrap';

    var fileInput = wrapper.querySelector('input[type="file"]');
    var labelText = wrapper.querySelector('.file-label-text');
    var confirmBtn = wrapper.querySelector('.btn-confirm');
    var cancelBtn = wrapper.querySelector('.btn-cancel');

    if (valueEl) {
      valueEl.replaceWith(wrapper);
    } else {
      row.insertBefore(wrapper, btn);
    }

    fileInput.addEventListener('change', function () {
      labelText.textContent = fileInput.files[0] ? fileInput.files[0].name : 'Choisir un PDF…';
    });

    cancelBtn.addEventListener('click', function () {
      if (valueEl) wrapper.replaceWith(valueEl);
      else wrapper.remove();
      row.querySelectorAll('.btn-edit').forEach(function (b) { b.style.display = ''; });
    });

    confirmBtn.addEventListener('click', function () {
      if (!fileInput.files || fileInput.files.length === 0) {
        showInlineError(labelText, 'Veuillez choisir un fichier PDF.');
        return;
      }
      var file = fileInput.files[0];
      if (!file.name.toLowerCase().endsWith('.pdf')) {
        showInlineError(labelText, 'Le fichier doit être un PDF.');
        return;
      }
      if (file.size > 5 * 1024 * 1024) {
        showInlineError(labelText, 'Le fichier dépasse 5 Mo.');
        return;
      }

      confirmBtn.disabled = true;
      confirmBtn.textContent = 'Envoi…';

      var formData = new FormData();
      formData.append('cv', file);
      formData.append('_field', 'cv');
      var csrfMeta = document.querySelector('meta[name="csrf_token"]');
      if (csrfMeta) formData.append('csrf_token', csrfMeta.content);

      fetch('/apply/update?id=' + offerId, { method: 'POST', body: formData })
        .then(function (response) { return response.json(); })
        .then(function (data) {
          if (data.success) {
            if (!valueEl) {
              valueEl = document.createElement('a');
              valueEl.className = 'form-input summary-value';
              valueEl.target = '_blank';
              valueEl.style.color = 'var(--color-primary-dark)';
            }
            valueEl.textContent = data.newValue;
            if (valueEl.tagName === 'A') {
              valueEl.href = '/uploads/applications/' + data.newValue;
            }
            wrapper.replaceWith(valueEl);
            row.querySelectorAll('.btn-edit').forEach(function (b) { b.style.display = ''; });
            showFlash('success', 'CV remplacé avec succès.');
          } else {
            showFlash('error', data.message || 'Une erreur est survenue.');
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Valider';
          }
        })
        .catch(function () {
          showFlash('error', 'Impossible de contacter le serveur.');
          confirmBtn.disabled = false;
          confirmBtn.textContent = 'Valider';
        });
    });
  }

  // ----------------------------------------------------------------
  // Affiche un message d'erreur sous un champ
  // ----------------------------------------------------------------

  function showInlineError(inputEl, message) {
    var existing = inputEl.parentElement.querySelector('.inline-error');
    if (existing) existing.remove();

    var err = document.createElement('span');
    err.className = 'inline-error field-hint';
    err.textContent = message;
    err.style.color = 'var(--color-error, #b91c1c)';
    inputEl.after(err);

    inputEl.addEventListener('change', function () { err.remove(); }, { once: true });
  }

  // ----------------------------------------------------------------
  // Affiche un message flash en haut de la page
  // ----------------------------------------------------------------

  function showFlash(type, message) {
    var existing = document.querySelector('.flash');
    if (existing) existing.remove();

    var flash = document.createElement('div');
    flash.className = 'flash flash--' + type;
    flash.textContent = message;

    var anchor = document.querySelector('.publish-header');
    if (anchor) anchor.insertAdjacentElement('afterend', flash);

    setTimeout(function () {
      flash.style.transition = 'opacity 0.4s';
      flash.style.opacity = '0';
      setTimeout(function () { flash.remove(); }, 400);
    }, 4000);
  }

});
