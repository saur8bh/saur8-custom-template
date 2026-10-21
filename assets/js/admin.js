/**
 * Template Studio Admin JavaScript
 * Futuristic Dark Mode UI with seamless AJAX actions
 */

(function($) {
  'use strict';

  let editor = null;

  $(document).ready(function() {
    initCodeMirror();
    initTwoWaySync();
    initAutoResize();

    // AJAX Action Handlers for "?page=saur8-ct"
    initAjaxDelete();
    initAjaxDuplicate();
    initAjaxApplyFrontpage();
    initBulkActions();

    // Import / Export Page Functionality
    initImportDropzone();
    initExportChecklist();
  });

  /* -------------------------------------------------------------------------
   * Toast / Notification Utility
   * ------------------------------------------------------------------------- */
  function showNotice(message, type = 'success', duration = 4000) {
    let $container = $('.saur8-toast-container');
    if (!$container.length) {
      $container = $('<div class="saur8-toast-container"></div>').appendTo('body');
    }

    const iconMap = {
      success: '<svg class="saur8-icon" style="color:var(--saur8-success);" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>',
      error: '<svg class="saur8-icon" style="color:var(--saur8-danger);" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>',
      warning: '<svg class="saur8-icon" style="color:var(--saur8-warning);" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>'
    };

    const $toast = $(`
      <div class="saur8-toast saur8-toast--${type}">
        <div style="display:flex;align-items:center;gap:10px;">
          ${iconMap[type] || ''}
          <span>${message}</span>
        </div>
        <button type="button" class="saur8-toast__close" aria-label="Dismiss">&times;</button>
      </div>
    `);

    $container.append($toast);

    const timer = setTimeout(function() {
      dismissToast($toast);
    }, duration);

    $toast.find('.saur8-toast__close').on('click', function() {
      clearTimeout(timer);
      dismissToast($toast);
    });
  }

  function dismissToast($toast) {
    $toast.css({
      opacity: 0,
      transform: 'translateY(-10px) scale(0.95)'
    });
    setTimeout(function() {
      $toast.remove();
    }, 300);
  }

  function setButtonLoading($btn, text) {
    $btn.data('original-html', $btn.html());
    $btn.addClass('saur8-btn--loading').prop('disabled', true);
    const spinnerSvg = '<svg class="saur8-icon saur8-spin" style="width:14px;height:14px;margin-right:6px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"/></svg>';
    $btn.html(spinnerSvg + (text || ''));
  }

  function resetButtonLoading($btn) {
    const originalHtml = $btn.data('original-html');
    if (originalHtml) {
      $btn.html(originalHtml);
    }
    $btn.removeClass('saur8-btn--loading').prop('disabled', false);
  }

  /* -------------------------------------------------------------------------
   * AJAX Actions for All Templates (?page=saur8-ct)
   * ------------------------------------------------------------------------- */

  // 1. AJAX Delete Single Template
  function initAjaxDelete() {
    $(document).on('click', '.saur8-delete-btn', function(e) {
      e.preventDefault();
      const $btn = $(this);
      const filename = $btn.data('filename');
      const templateName = $btn.data('name') || filename;

      if (!filename) {
        return;
      }

      const confirmMsg = (typeof saur8_ajax !== 'undefined' && saur8_ajax.i18n && saur8_ajax.i18n.confirm_delete) 
        ? saur8_ajax.i18n.confirm_delete 
        : 'Are you sure you want to delete this template? This cannot be undone.';

      if (!confirm(confirmMsg)) {
        return;
      }

      const $row = $btn.closest('tr');
      setButtonLoading($btn, typeof saur8_ajax !== 'undefined' ? saur8_ajax.i18n.deleting : 'Deleting...');

      $.ajax({
        url: saur8_ajax.ajax_url,
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'saur8_ajax_delete_template',
          file: filename,
          _ajax_nonce: saur8_ajax.nonce
        },
        success: function(response) {
          if (response.success) {
            $row.css('background', 'rgba(239, 68, 68, 0.2)').fadeOut(350, function() {
              $row.remove();
              updateBulkUI();
              checkEmptyState(response.data.remaining);
            });
            showNotice(response.data.message || 'Template deleted successfully.', 'success');
          } else {
            resetButtonLoading($btn);
            showNotice(response.data.message || 'Failed to delete template.', 'error');
          }
        },
        error: function(xhr) {
          resetButtonLoading($btn);
          const errorMsg = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message 
            ? xhr.responseJSON.data.message 
            : (saur8_ajax.i18n.error_generic || 'Server error while deleting template.');
          showNotice(errorMsg, 'error');
        }
      });
    });
  }

  // 2. AJAX Duplicate Template
  function initAjaxDuplicate() {
    $(document).on('click', '.saur8-duplicate-btn', function(e) {
      e.preventDefault();
      const $btn = $(this);
      const filename = $btn.data('filename');

      if (!filename) {
        return;
      }

      const confirmMsg = (typeof saur8_ajax !== 'undefined' && saur8_ajax.i18n && saur8_ajax.i18n.confirm_duplicate) 
        ? saur8_ajax.i18n.confirm_duplicate 
        : 'Duplicate this template?';

      if (!confirm(confirmMsg)) {
        return;
      }

      const $currentRow = $btn.closest('tr');
      setButtonLoading($btn, typeof saur8_ajax !== 'undefined' ? saur8_ajax.i18n.duplicating : 'Duplicating...');

      $.ajax({
        url: saur8_ajax.ajax_url,
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'saur8_ajax_duplicate_template',
          file: filename,
          _ajax_nonce: saur8_ajax.nonce
        },
        success: function(response) {
          resetButtonLoading($btn);

          if (response.success && response.data.template) {
            const t = response.data.template;
            const starIcon = '<svg class="saur8-icon saur8-icon-inline" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
            const copyIcon = '<svg class="saur8-icon saur8-icon-inline" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>';
            const dlIcon = '<svg class="saur8-icon saur8-icon-inline" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM17 13l-5 5-5-5h3V9h4v4h3z"/></svg>';
            const trashIcon = '<svg class="saur8-icon saur8-icon-inline" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>';

            let frontPageBtn = '';
            if (t.has_front_page) {
              frontPageBtn = `
                <button type="button"
                        class="saur8-btn saur8-btn--accent saur8-btn--sm saur8-apply-frontpage-btn"
                        data-filename="${t.filename}"
                        data-page-title="${t.front_page_title}"
                        title="Apply to ${t.front_page_title}">
                    ${starIcon} <span>${saur8_ajax.i18n.front_page || 'Front Page'}</span>
                </button>
              `;
            }

            const newRowHtml = `
              <tr data-filename="${t.filename}" data-name="${t.name}" class="saur8-row-highlight">
                <td class="saur8-table__checkbox">
                    <input type="checkbox" class="saur8-checkbox saur8-template-checkbox" value="${t.filename}">
                </td>
                <td class="saur8-td-name">
                    <strong>${t.name}</strong>
                </td>
                <td class="saur8-td-file">
                    <code>${t.filename}</code>
                </td>
                <td class="saur8-td-assigned">
                    <span class="saur8-muted">—</span>
                </td>
                <td class="saur8-td-modified">
                    ${t.modified}
                </td>
                <td class="saur8-table__actions">
                    <a href="${t.edit_url}" class="saur8-btn saur8-btn--ghost saur8-btn--sm">${saur8_ajax.i18n.edit || 'Edit'}</a>
                    ${frontPageBtn}
                    <button type="button" class="saur8-btn saur8-btn--ghost saur8-btn--sm saur8-duplicate-btn" data-filename="${t.filename}" data-name="${t.name}">
                        ${copyIcon} <span>${saur8_ajax.i18n.duplicate || 'Duplicate'}</span>
                    </button>
                    <a href="${t.export_url}" class="saur8-btn saur8-btn--ghost saur8-btn--sm saur8-export-btn">
                        ${dlIcon} <span>${saur8_ajax.i18n.export || 'Export'}</span>
                    </a>
                    <button type="button" class="saur8-btn saur8-btn--danger saur8-btn--sm saur8-delete-btn" data-filename="${t.filename}" data-name="${t.name}">
                        ${trashIcon} <span>${saur8_ajax.i18n.delete || 'Delete'}</span>
                    </button>
                </td>
              </tr>
            `;

            const $newRow = $(newRowHtml).hide();
            $currentRow.after($newRow);
            $newRow.fadeIn(400);

            showNotice(response.data.message || 'Template duplicated successfully.', 'success');
            updateBulkUI();
          } else {
            showNotice(response.data.message || 'Failed to duplicate template.', 'error');
          }
        },
        error: function(xhr) {
          resetButtonLoading($btn);
          const errorMsg = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message 
            ? xhr.responseJSON.data.message 
            : (saur8_ajax.i18n.error_generic || 'Server error while duplicating template.');
          showNotice(errorMsg, 'error');
        }
      });
    });
  }

  // 3. AJAX Apply Template to Front Page
  function initAjaxApplyFrontpage() {
    $(document).on('click', '.saur8-apply-frontpage-btn', function(e) {
      e.preventDefault();
      const $btn = $(this);
      const filename = $btn.data('filename');
      const pageTitle = $btn.data('page-title') || 'Front Page';

      if (!filename) {
        return;
      }

      const confirmTpl = (typeof saur8_ajax !== 'undefined' && saur8_ajax.i18n && saur8_ajax.i18n.confirm_apply_frontpage) 
        ? saur8_ajax.i18n.confirm_apply_frontpage 
        : 'Apply this template to %s?';

      if (!confirm(confirmTpl.replace('%s', pageTitle))) {
        return;
      }

      const $row = $btn.closest('tr');
      setButtonLoading($btn, typeof saur8_ajax !== 'undefined' ? saur8_ajax.i18n.applying : 'Applying...');

      $.ajax({
        url: saur8_ajax.ajax_url,
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'saur8_ajax_apply_frontpage',
          file: filename,
          _ajax_nonce: saur8_ajax.nonce
        },
        success: function(response) {
          resetButtonLoading($btn);
          if (response.success) {
            // Update assigned column smoothly
            $row.find('.saur8-td-assigned').html(`<strong>${response.data.front_page_title}</strong>`);
            $row.addClass('saur8-row-highlight');
            setTimeout(function() {
              $row.removeClass('saur8-row-highlight');
            }, 2000);

            showNotice(response.data.message || 'Template applied to front page successfully.', 'success');
          } else {
            showNotice(response.data.message || 'Failed to apply template.', 'error');
          }
        },
        error: function(xhr) {
          resetButtonLoading($btn);
          const errorMsg = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message 
            ? xhr.responseJSON.data.message 
            : (saur8_ajax.i18n.error_generic || 'Server error while applying template to front page.');
          showNotice(errorMsg, 'error');
        }
      });
    });
  }

  // 4. Bulk Selectors & Bulk Actions
  function initBulkActions() {
    // Select All Checkbox
    $(document).on('change', '#saur8-select-all', function() {
      const isChecked = $(this).prop('checked');
      $('.saur8-template-checkbox').prop('checked', isChecked).trigger('change-visual');
      updateBulkUI();
    });

    // Individual Checkbox Change
    $(document).on('change', '.saur8-template-checkbox', function() {
      updateBulkUI();
    });

    // Custom visual toggle on row
    $(document).on('change change-visual', '.saur8-template-checkbox', function() {
      const $row = $(this).closest('tr');
      if ($(this).prop('checked')) {
        $row.addClass('saur8-row-selected');
      } else {
        $row.removeClass('saur8-row-selected');
      }
    });

    // Bulk Apply Button Click
    $('#saur8-bulk-apply').on('click', function(e) {
      e.preventDefault();
      const action = $('#saur8-bulk-action').val();
      const selectedFiles = [];

      $('.saur8-template-checkbox:checked').each(function() {
        selectedFiles.push($(this).val());
      });

      if (!action) {
        showNotice(saur8_ajax.i18n.please_select_action || 'Please select a bulk action.', 'warning');
        return;
      }

      if (selectedFiles.length === 0) {
        showNotice(saur8_ajax.i18n.no_templates_selected || 'Please select at least one template.', 'warning');
        return;
      }

      // Bulk Delete Action
      if (action === 'delete') {
        const confirmTpl = (typeof saur8_ajax !== 'undefined' && saur8_ajax.i18n && saur8_ajax.i18n.confirm_bulk_delete)
          ? saur8_ajax.i18n.confirm_bulk_delete
          : 'Are you sure you want to delete %d selected template(s)? This cannot be undone.';

        if (!confirm(confirmTpl.replace('%d', selectedFiles.length))) {
          return;
        }

        const $btn = $('#saur8-bulk-apply');
        setButtonLoading($btn, saur8_ajax.i18n.deleting || 'Deleting...');

        $.ajax({
          url: saur8_ajax.ajax_url,
          type: 'POST',
          dataType: 'json',
          data: {
            action: 'saur8_ajax_bulk_delete',
            files: selectedFiles,
            _ajax_nonce: saur8_ajax.nonce
          },
          success: function(response) {
            resetButtonLoading($btn);
            if (response.success) {
              // Animate away deleted rows
              selectedFiles.forEach(function(file) {
                $(`tr[data-filename="${file}"]`).css('background', 'rgba(239,68,68,0.2)').fadeOut(300, function() {
                  $(this).remove();
                  updateBulkUI();
                  checkEmptyState(response.data.remaining);
                });
              });

              $('#saur8-select-all').prop('checked', false);
              showNotice(response.data.message, 'success');
            } else {
              showNotice(response.data.message || 'Failed to delete selected templates.', 'error');
            }
          },
          error: function(xhr) {
            resetButtonLoading($btn);
            const errorMsg = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message 
              ? xhr.responseJSON.data.message 
              : (saur8_ajax.i18n.error_generic || 'Error processing bulk delete.');
            showNotice(errorMsg, 'error');
          }
        });
      }

      // Bulk Export Action (Streams ZIP)
      if (action === 'export') {
        const $form = $('#saur8-bulk-export-form');
        const $inputs = $('#saur8-bulk-export-inputs');
        $inputs.empty();

        selectedFiles.forEach(function(file) {
          $inputs.append(`<input type="hidden" name="template_files[]" value="${file}">`);
        });

        showNotice(saur8_ajax.i18n.exporting || 'Preparing ZIP download...', 'success', 2500);
        $form.submit();
      }
    });
  }

  function updateBulkUI() {
    const total = $('.saur8-template-checkbox').length;
    const checked = $('.saur8-template-checkbox:checked').length;
    const $selectAll = $('#saur8-select-all');
    const $applyBtn = $('#saur8-bulk-apply');
    const $countPill = $('#saur8-bulk-count');

    if (checked === 0) {
      $selectAll.prop('checked', false).prop('indeterminate', false);
      $applyBtn.prop('disabled', true);
    } else if (checked === total && total > 0) {
      $selectAll.prop('checked', true).prop('indeterminate', false);
      $applyBtn.prop('disabled', false);
    } else {
      $selectAll.prop('checked', false).prop('indeterminate', true);
      $applyBtn.prop('disabled', false);
    }

    const tpl = (typeof saur8_ajax !== 'undefined' && saur8_ajax.i18n && saur8_ajax.i18n.selected_count) 
      ? saur8_ajax.i18n.selected_count 
      : '%d selected';
    $countPill.text(tpl.replace('%d', checked));
  }

  function checkEmptyState(remainingCount) {
    const count = typeof remainingCount !== 'undefined' ? remainingCount : $('#saur8-templates-tbody tr').length;
    if (count <= 0) {
      $('#saur8-table-container').fadeOut(250, function() {
        $('#saur8-empty-state').fadeIn(300);
      });
    }
  }

  /* -------------------------------------------------------------------------
   * Import / Export Page Features
   * ------------------------------------------------------------------------- */
  function initImportDropzone() {
    const $dropzone = $('#saur8-dropzone');
    const $fileInput = $('#saur8-import-file-input');
    const $fileInfo = $('#saur8-file-info');
    const $content = $('.saur8-dropzone__content');
    const $filename = $('#saur8-filename');
    const $filesize = $('#saur8-filesize');
    const $removeBtn = $('#saur8-remove-file');
    const $submitBtn = $('#saur8-import-submit-btn');

    if (!$dropzone.length) {
      return;
    }

    ['dragenter', 'dragover'].forEach(eventName => {
      $dropzone[0].addEventListener(eventName, function(e) {
        e.preventDefault();
        e.stopPropagation();
        $dropzone.addClass('saur8-dropzone--active');
      }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
      $dropzone[0].addEventListener(eventName, function(e) {
        e.preventDefault();
        e.stopPropagation();
        $dropzone.removeClass('saur8-dropzone--active');
      }, false);
    });

    $dropzone[0].addEventListener('drop', function(e) {
      if (e.dataTransfer && e.dataTransfer.files.length) {
        $fileInput[0].files = e.dataTransfer.files;
        handleFileSelected(e.dataTransfer.files[0]);
      }
    }, false);

    $fileInput.on('change', function() {
      if (this.files && this.files[0]) {
        handleFileSelected(this.files[0]);
      }
    });

    $removeBtn.on('click', function(e) {
      e.stopPropagation();
      e.preventDefault();
      $fileInput.val('');
      $fileInfo.hide();
      $content.show();
      $submitBtn.prop('disabled', true);
    });

    function handleFileSelected(file) {
      const ext = file.name.split('.').pop().toLowerCase();
      if (ext !== 'php' && ext !== 'zip') {
        showNotice('Please select a valid .php template or .zip archive.', 'error');
        $fileInput.val('');
        $fileInfo.hide();
        $content.show();
        $submitBtn.prop('disabled', true);
        return;
      }

      $filename.text(file.name);
      $filesize.text(formatBytes(file.size));
      $content.hide();
      $fileInfo.css('display', 'inline-flex');
      $submitBtn.prop('disabled', false);
    }

    function formatBytes(bytes) {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // AJAX Form Submission for Import
    $('#saur8-import-form').on('submit', function(e) {
      e.preventDefault();
      const file = $fileInput[0].files[0];
      if (!file) {
        showNotice('Please select a file to import.', 'warning');
        return;
      }

      const formData = new FormData();
      formData.append('action', 'saur8_ajax_import_templates');
      formData.append('_ajax_nonce', saur8_ajax.nonce);
      formData.append('template_file', file);
      formData.append('overwrite', $('#saur8-overwrite').is(':checked') ? '1' : '0');

      setButtonLoading($submitBtn, saur8_ajax.i18n.importing || 'Importing...');

      $.ajax({
        url: saur8_ajax.ajax_url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
          resetButtonLoading($submitBtn);
          if (response.success) {
            showNotice(response.data.message || 'Template(s) imported successfully!', 'success', 6000);
            $removeBtn.trigger('click');
            // Refresh checklist on export side if present
            setTimeout(function() {
              location.reload();
            }, 1200);
          } else {
            showNotice(response.data.message || 'Import failed.', 'error', 6000);
          }
        },
        error: function(xhr) {
          resetButtonLoading($submitBtn);
          const errorMsg = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message 
            ? xhr.responseJSON.data.message 
            : (saur8_ajax.i18n.error_generic || 'Server error while importing template.');
          showNotice(errorMsg, 'error', 6000);
        }
      });
    });
  }

  function initExportChecklist() {
    const $selectAll = $('#saur8-export-select-all');
    const $checkboxes = $('.saur8-export-checkbox');
    const $btn = $('#saur8-export-selected-btn');
    const $count = $('#saur8-export-selected-count');

    if (!$checkboxes.length) {
      return;
    }

    $selectAll.on('change', function() {
      $checkboxes.prop('checked', $(this).is(':checked'));
      updateExportCount();
    });

    $checkboxes.on('change', function() {
      updateExportCount();
    });

    function updateExportCount() {
      const checked = $('.saur8-export-checkbox:checked').length;
      const total = $checkboxes.length;

      $count.text(checked + ' selected');
      $btn.prop('disabled', checked === 0);

      if (checked === 0) {
        $selectAll.prop('checked', false).prop('indeterminate', false);
      } else if (checked === total) {
        $selectAll.prop('checked', true).prop('indeterminate', false);
      } else {
        $selectAll.prop('checked', false).prop('indeterminate', true);
      }
    }
  }

  /* -------------------------------------------------------------------------
   * CodeMirror Editor & Sync Functions (Preserved)
   * ------------------------------------------------------------------------- */
  function initCodeMirror() {
    const textarea = document.getElementById('saur8-editor');
    if (!textarea) {
      return;
    }

    editor = CodeMirror.fromTextArea(textarea, {
      mode: 'application/x-httpd-php',
      theme: 'dracula',
      lineNumbers: true,
      matchBrackets: true,
      autoCloseBrackets: true,
      foldGutter: true,
      gutters: ['CodeMirror-linenumbers', 'CodeMirror-foldgutter'],
      lineWrapping: true,
      tabSize: 2,
      indentUnit: 2,
      indentWithTabs: false,
      viewportMargin: Infinity,
      extraKeys: {
        'Ctrl-Space': 'autocomplete',
        'Tab': function(cm) {
          if (cm.somethingSelected()) {
            cm.indentSelection('add');
          } else {
            cm.replaceSelection('  ', 'end');
          }
        }
      }
    });

    editor.setOption('lineWrapping', true);

    const form = textarea.closest('form');
    if (form) {
      form.addEventListener('submit', function() {
        textarea.value = editor.getValue();
      });
    }

    setTimeout(function() {
      editor.refresh();
    }, 100);
  }

  function initTwoWaySync() {
    const nameInput = document.getElementById('saur8-template-name');
    if (!nameInput || !editor) {
      return;
    }

    nameInput.addEventListener('input', function() {
      const newName = this.value;
      const content = editor.getValue();
      const updatedContent = content.replace(
        /(\*\s*Template Name:\s*)(.+)/i,
        '$1' + newName
      );
      if (updatedContent !== content) {
        const cursor = editor.getCursor();
        editor.setValue(updatedContent);
        editor.setCursor(cursor);
      }
    });

    editor.on('change', function() {
      const content = editor.getValue();
      const match = content.match(/\*\s*Template Name:\s*(.+)/i);
      if (match && match[1]) {
        const templateName = match[1].trim();
        if (nameInput.value !== templateName) {
          nameInput.value = templateName;
        }
      }
    });
  }

  function initAutoResize() {
    if (!editor) {
      return;
    }

    function resizeEditor() {
      const wrapper = editor.getWrapperElement();
      const header = document.querySelector('.saur8-header');
      const tabs = document.querySelector('.saur8-tabs');
      const windowHeight = window.innerHeight;
      const windowWidth = window.innerWidth;
      
      if (header && tabs) {
        const headerHeight = header.offsetHeight;
        const tabsHeight = tabs.offsetHeight;
        let availableHeight = windowHeight - headerHeight - tabsHeight - 200;
        
        if (windowWidth <= 768) {
          availableHeight = Math.max(400, availableHeight);
        } else if (windowWidth <= 1200) {
          availableHeight = Math.max(450, availableHeight);
        } else {
          availableHeight = Math.max(550, availableHeight);
        }
        
        wrapper.style.height = availableHeight + 'px';
        editor.setSize(null, availableHeight);
        editor.refresh();
      }
    }

    window.addEventListener('resize', resizeEditor);
    setTimeout(resizeEditor, 100);
    
    let resizeTimeout;
    window.addEventListener('resize', function() {
      clearTimeout(resizeTimeout);
      resizeTimeout = setTimeout(function() {
        editor.refresh();
      }, 250);
    });
  }

})(jQuery);
