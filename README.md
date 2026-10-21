# Template Studio - WordPress Custom Template Manager

[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

A powerful WordPress plugin that lets you create, edit, and manage custom page templates directly from your admin panel. No FTP required, no theme modifications needed.

![Template Studio Banner](https://via.placeholder.com/1200x400/0a0a0f/a78bfa?text=Template+Studio)

## ✨ Features

### 🎨 Visual Template Editor
- **Built-in Code Editor** with syntax highlighting (CodeMirror)
- **Live PHP/HTML/CSS editing** with auto-completion
- **Dark mode interface** with futuristic design
- **Two-way sync** between template name and code

### 🚀 Template Management
- **Seamless AJAX Operations** - Delete, duplicate, and assign templates with zero page reload
- **Bulk Selection & Actions** - Select multiple templates for instant bulk deletion or ZIP export
- **Import & Export Tab** - Drag & drop single `.php` templates or `.zip` archives with automatic header parsing
- **Export Options** - Export all templates as a ZIP archive, selected templates, or individual `.php` files
- **Create unlimited templates** from scratch or duplicate existing ones
- **Edit templates** with a powerful code editor
- **Delete templates** with automatic cleanup
- **Duplicate templates** for quick variations
- **Template versioning** with file modification tracking

### 📄 Page Assignment
- **One-click assignment** to any page
- **Auto-detect homepage** candidates
- **Bulk assignment** capabilities
- **Front page quick-apply** button
- **Visual assignment interface** showing current assignments

### ⚙️ Advanced Settings
- **Development mode** with automatic cache busting
- **Multiple cache strategies** (version, filemtime, timestamp)
- **Template directory** management
- **Status dashboard** showing plugin health

### 🎯 User Experience
- **Futuristic dark UI** with glowing effects and animations
- **Responsive design** works on all devices
- **Intuitive navigation** with tabbed interface
- **Real-time feedback** with floating toast notices and button loading states
- **SVG icons** for crisp visuals

## 📦 Installation

### From GitHub

1. Download the latest release or clone the repository:
```bash
git clone https://github.com/yourusername/saur8-custom-template.git
```

2. Upload the `saur8-custom-template` folder to `/wp-content/plugins/`

3. Activate the plugin through the 'Plugins' menu in WordPress

4. Navigate to 'Template Studio' in the admin menu

### Manual Installation

1. Download the plugin ZIP file
2. Go to WordPress Admin → Plugins → Add New
3. Click "Upload Plugin" and select the ZIP file
4. Click "Install Now" and then "Activate"

## 🎯 Quick Start

### Creating Your First Template

1. Go to **Template Studio → Create New**
2. Edit the template name in the sidebar
3. Write your custom HTML/PHP in the editor
4. Optionally assign it to a page immediately
5. Click **Save Template**

### Managing Templates via AJAX

- **Delete**: Click the 🗑️ **Delete** button to remove a template immediately with no page refresh.
- **Duplicate**: Click 📋 **Duplicate** to create a clone and insert it into the list dynamically.
- **Front Page**: Click ⭐ **Front Page** to assign a template to your static homepage instantly.
- **Bulk Actions**: Check the select box on multiple templates to delete or export as a ZIP archive at once.

### Importing & Exporting Templates

1. Go to **Template Studio → Import / Export**
2. **To Export**: Click "Export All Templates as ZIP", or pick specific templates from the checklist.
3. **To Import**: Drag and drop a `.php` file or `.zip` archive into the dropzone, then click "Import Template(s)".

## 🛠️ Development

### File Structure

```
saur8-custom-template/
├── saur8-custom-template.php    # Main plugin file
├── uninstall.php                # Cleanup on deletion
├── includes/
│   ├── class-plugin.php         # Core plugin class & script localization
│   ├── class-template-manager.php # Template CRUD, ZIP packaging, & import/export
│   ├── class-template-loader.php
│   ├── class-page-assignment.php
│   └── class-front-page.php
├── admin/
│   ├── class-admin-menu.php     # Menu registration, POST & AJAX handlers
│   ├── class-admin-notices.php
│   ├── includes/
│   │   └── icons.php            # SVG icon system
│   └── views/
│       ├── layout.php           # Header & tabbed navigation
│       ├── page-all-templates.php # AJAX-powered template table & bulk actions
│       ├── page-create-template.php
│       ├── page-edit-template.php
│       ├── page-import-export.php # Drag-and-drop import & ZIP export
│       ├── page-assignment.php
│       ├── page-front-page.php
│       └── page-settings.php
├── assets/
│   ├── css/
│   │   └── admin.css            # Futuristic dark theme, toolbar & toasts
│   └── js/
│       └── admin.js             # CodeMirror, AJAX handlers & bulk actions
└── languages/
    └── saur8-custom-template.pot
```

## 🔒 Security

- **Nonce verification** on all forms and AJAX requests (`saur8_ajax_nonce`, `check_ajax_referer`)
- **Capability checks** (`manage_options` required for all operations)
- **Input sanitization** on all file names and user inputs (`sanitize_file_name`)
- **Output escaping** for all dynamic content (`esc_html`, `esc_attr`, `esc_url`)
- **Path traversal protection** (`realpath` and boundary checks against `SAUR8_CT_TEMPLATES_DIR`)
- **SQL injection prevention** with `$wpdb->prepare()`

## 🌐 Translation Ready

The plugin is fully translation-ready with:
- Text domain: `saur8-custom-template`
- POT file included in `/languages/`
- All strings wrapped in translation functions

## 📋 Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Modern browser with JavaScript enabled

## 📄 License

This plugin is licensed under the GPL v2 or later.

---

Made with ❤️ for the WordPress community
