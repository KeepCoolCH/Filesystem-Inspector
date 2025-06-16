# 📁 Filesystem Inspector

A lightweight PHP-based tool to visually inspect and browse directory structures.  
Supports folder navigation, file metadata display, symbolic permissions, multilingual interface, and a loading spinner for improved UX.

## 🌍 Features

- ✅ Tree-style view of directories and files
- ✅ Displays size, modification/creation date, and permission codes
- ✅ Shows human-readable symbolic permissions (e.g., `rwxr-xr-x`)
- ✅ Supports two languages: 🇩🇪 German and 🇬🇧 English
- ✅ Spinner overlay while scanning the filesystem
- ✅ Click-to-toggle folder contents
- ✅ Works as a single PHP file – simple to deploy

## 🚀 Installation

1. Upload `inspector.php` to your web server
2. Open it in your browser:  
   ```
   https://your-domain.com/inspector.php
   ```
3. Use the language switcher in the top-right to toggle between English and German

## 🔒 Permissions & Security

- Requires read access to the folders it scans
- Does **not** allow editing or deleting files (read-only)
- You can restrict access via `.htaccess` or other authentication mechanisms if needed

## 🛠️ Customization

You can easily adjust:
- Starting directory
- Language options
- Color theme and layout via embedded CSS
- Add your own features (e.g. ZIP download, search, etc.)

## 📄 License

MIT License – feel free to use, modify, and distribute.
