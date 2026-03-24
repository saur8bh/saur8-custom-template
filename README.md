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
- **Real-time feedback** with success/error notifications
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

### Assigning Templates to Pages

**Method 1: During Creation/Edit**
- Use the "Assign After Saving" dropdown in the editor sidebar

**Method 2: Page Assignments Tab**
- Go to **Template Studio → Page Assignments**
- Use auto-detect for homepage or manual assignment
- Select page and template, click Assign

**Method 3: Quick Front Page Apply**
- Go to **Template Studio → All Templates**
- Click the ⭐ **Front Page** button on any template

### Setting Your Front Page

1. Go to **Template Studio → Front Page Settings**
2. Select your homepage from the dropdown
3. Optionally select a posts page
4. Click **Save Settings**

## 🛠️ Development

### File Structure

```
saur8-custom-template/
├── saur8-custom-template.php    # Main plugin file
├── uninstall.php                # Cleanup on deletion
├── includes/
│   ├── class-plugin.php         # Core plugin class
│   ├── class-template-manager.php
│   ├── class-template-loader.php
│   ├── class-page-assignment.php
│   └── class-front-page.php
├── admin/
│   ├── class-admin-menu.php
│   ├── class-admin-notices.php
│   ├── includes/
│   │   └── icons.php            # SVG icon system
│   └── views/
│       ├── layout.php
│       ├── page-all-templates.php
│       ├── page-create-template.php
│       ├── page-edit-template.php
│       ├── page-assignment.php
│       ├── page-front-page.php
│       └── page-settings.php
├── assets/
│   ├── css/
│   │   └── admin.css            # Futuristic dark theme
│   └── js/
│       └── admin.js             # CodeMirror & UI logic
└── languages/
    └── saur8-custom-template.pot
```

### Development Mode

Enable development mode for automatic cache busting:

1. Go to **Template Studio → Settings**
2. Check **Enable Development Mode**
3. Select **File Modification Time** cache strategy
4. Click **Save Settings**

When dev mode is enabled, CSS/JS files use file modification timestamps, so changes appear immediately without clearing cache.

### Cache Busting Strategies

- **Plugin Version** - Stable version number (recommended for production)
- **File Modification Time** - Auto cache-bust on file changes (for development)
- **Current Timestamp** - Always fresh (not recommended - impacts performance)

## 🎨 Customization

### Template Boilerplate

Every template starts with this structure:

```php
<?php
/**
 * Template Name: My Custom Template
 */

get_header();
?>

<main id="main-content">
  <!-- Your custom HTML/PHP here -->
</main>

<?php
get_footer();
?>
```

### Accessing WordPress Functions

Templates have full access to WordPress functions:

```php
// Get current post
global $post;

// Display post content
the_content();

// Custom query
$args = array('post_type' => 'page');
$query = new WP_Query($args);

// Get custom fields
$value = get_post_meta($post->ID, 'custom_field', true);
```

## 🔒 Security

- **Nonce verification** on all forms
- **Capability checks** (`manage_options` required)
- **Input sanitization** on all user inputs
- **Output escaping** for all dynamic content
- **Path traversal protection** for file operations
- **SQL injection prevention** with prepared statements

## 🌐 Translation Ready

The plugin is fully translation-ready with:
- Text domain: `saur8-custom-template`
- POT file included in `/languages/`
- All strings wrapped in translation functions

## 📋 Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Modern browser with JavaScript enabled

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 Changelog

### Version 1.0.0
- Initial release
- Template creation and editing with CodeMirror
- Page assignment system
- Front page settings
- Auto-detect homepage
- Development mode with cache busting
- Futuristic dark UI with animations
- SVG icon system
- Responsive design
- Translation ready

## 🐛 Bug Reports

If you find a bug, please create an issue on GitHub with:
- WordPress version
- PHP version
- Steps to reproduce
- Expected behavior
- Actual behavior
- Screenshots (if applicable)

## 📄 License

This plugin is licensed under the GPL v2 or later.

```
Copyright (C) 2024 Saurabh

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## 👨‍💻 Author

**Saurabh**

## 🙏 Acknowledgments

- CodeMirror for the excellent code editor
- WordPress community for inspiration
- All contributors and users

## 📞 Support

For support, please:
- Open an issue on GitHub
- Check existing issues for solutions
- Read the documentation above

---

Made with ❤️ for the WordPress community
