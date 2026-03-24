/**
 * Template Studio Admin JavaScript
 */

(function($) {
  'use strict';

  let editor = null;

  $(document).ready(function() {
    initCodeMirror();
    initTwoWaySync();
    initDeleteConfirm();
    initDuplicateConfirm();
    initAutoResize();
  });

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

    // Force wrapping
    editor.setOption('lineWrapping', true);

    // Sync editor content back to textarea before form submit
    const form = textarea.closest('form');
    if (form) {
      form.addEventListener('submit', function() {
        textarea.value = editor.getValue();
      });
    }

    // Refresh editor after a short delay to ensure proper rendering
    setTimeout(function() {
      editor.refresh();
    }, 100);
  }

  function initTwoWaySync() {
    const nameInput = document.getElementById('saur8-template-name');
    
    if (!nameInput || !editor) {
      return;
    }

    // Update editor when name input changes
    nameInput.addEventListener('input', function() {
      const newName = this.value;
      const content = editor.getValue();
      
      // Replace Template Name in the comment
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

    // Update name input when editor changes
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

  function initDeleteConfirm() {
    $('.saur8-delete-btn').on('click', function(e) {
      if (!confirm('Are you sure you want to delete this template? This cannot be undone.')) {
        e.preventDefault();
        return false;
      }
    });
  }

  function initDuplicateConfirm() {
    $('.saur8-duplicate-btn').on('click', function(e) {
      if (!confirm('Duplicate this template?')) {
        e.preventDefault();
        return false;
      }
    });
    
    $('.saur8-apply-frontpage-btn').on('click', function(e) {
      const pageTitle = $(this).attr('title').replace('Apply to ', '');
      if (!confirm('Apply this template to ' + pageTitle + '?')) {
        e.preventDefault();
        return false;
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
        
        // Adjust height based on screen size
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
    
    // Refresh on window resize to maintain wrapping
    let resizeTimeout;
    window.addEventListener('resize', function() {
      clearTimeout(resizeTimeout);
      resizeTimeout = setTimeout(function() {
        editor.refresh();
      }, 250);
    });
  }

})(jQuery);
