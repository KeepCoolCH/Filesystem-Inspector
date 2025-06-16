<?php
@ob_end_flush(); flush();

$lang = $_GET['lang'] ?? 'en';
$translations = [
    'en' => [
        'title' => 'Filesystem-Inspector',
        'loading' => 'Loading directory structure and files...',
        'name' => 'Name',
        'size' => 'Size',
        'items' => 'Items',
        'modified' => 'Modified',
        'created' => 'Created',
        'permissions' => 'Permissions',
        'folder_icon' => '📁',
        'file_icon' => '📄',
        'language' => 'Language',
    ],
    'de' => [
        'title' => 'Dateisystem-Inspector',
        'loading' => 'Lädt Verzeichnisstruktur und Dateien...',
        'name' => 'Name',
        'size' => 'Größe',
        'items' => 'Objekte',
        'modified' => 'Geändert',
        'created' => 'Erstellt',
        'permissions' => 'Rechte',
        'folder_icon' => '📁',
        'file_icon' => '📄',
        'language' => 'Sprache',
    ]
];
$t = $translations[$lang] ?? $translations['en'];

function format_permissions($perm) {
    $map = [
        '0'=>'---', '1'=>'--x', '2'=>'-w-', '3'=>'-wx',
        '4'=>'r--', '5'=>'r-x', '6'=>'rw-', '7'=>'rwx'
    ];
    $txt = '';
    foreach (str_split($perm) as $digit) {
        $txt .= isset($map[$digit]) ? $map[$digit] : '???';
    }
    return "$perm ($txt)";
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($t['title']) ?></title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 2em; }
        .header, .folder-row, .file-row {
            display: flex; border-bottom: 1px solid #ccc; align-items: center;
        }
        .header { font-weight: bold; background: #eaeaea; }
        .entry { flex: 2; padding: 4px; white-space: nowrap; }
        .meta { flex: 1; padding: 4px; font-family: monospace; white-space: nowrap; }
        .folder-row .entry { cursor: pointer; }
        .children { margin-left: 0; }
        .lang-switch { position: absolute; top: 10px; right: 20px; }

        /* Spinner */
        #overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .spinner {
            border: 8px solid #d4f7d8;
            border-top: 8px solid #2ecc71;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin-bottom: 1em;
        }
        @keyframes spin {
            0% { transform: rotate(0deg);}
            100% { transform: rotate(360deg);}
        }
    </style>
    <script>
        function toggleFolder(id, el) {
            const target = document.getElementById(id);
            if (!target) return;
            if (target.style.display === 'none') {
                target.style.display = 'block';
                el.textContent = el.textContent.replace('▶', '▼');
            } else {
                target.style.display = 'none';
                el.textContent = el.textContent.replace('▼', '▶');
            }
        }
        window.onload = function() {
            document.getElementById("overlay").style.display = "none";
        };
    </script>
</head>
<body>
<!-- Spinner -->
<div id="overlay">
    <div class="spinner"></div>
    <div><?= htmlspecialchars($t['loading']) ?></div>
</div>

<!-- Sprache wechseln -->
<div class="lang-switch">
    <form method="get">
        <label><?= $t['language'] ?>:
            <select name="lang" onchange="this.form.submit()">
                <option value="en" <?= $lang === 'en' ? 'selected' : '' ?>>English</option>
                <option value="de" <?= $lang === 'de' ? 'selected' : '' ?>>Deutsch</option>
            </select>
        </label>
    </form>
</div>

<h1>📂 <?= htmlspecialchars($t['title']) ?></h1>
<div class="header">
    <div class="entry"><?= $t['name'] ?></div>
    <div class="meta"><?= $t['size'] ?></div>
    <div class="meta"><?= $t['items'] ?></div>
    <div class="meta"><?= $t['modified'] ?></div>
    <div class="meta"><?= $t['created'] ?></div>
    <div class="meta"><?= $t['permissions'] ?></div>
</div>

<?php
echo str_repeat(' ', 2048); @ob_flush(); @flush();

function format_bytes($bytes) {
    $units = ['B','KB','MB','GB','TB']; $i = 0;
    while ($bytes >= 1024 && $i < count($units)-1) { $bytes /= 1024; $i++; }
    return round($bytes, 2).' '.$units[$i];
}
function get_permissions($path) {
    return substr(sprintf('%o', fileperms($path)), -4);
}
function get_info($path) {
    return [
        'size' => is_dir($path) ? folder_size($path) : filesize($path),
        'mtime' => date("Y-m-d H:i:s", filemtime($path)),
        'ctime' => date("Y-m-d H:i:s", filectime($path)),
        'perms' => get_permissions($path),
        'items' => is_dir($path) ? count_items($path) : '-'
    ];
}
function count_items($path) {
    $items = scandir($path);
    return $items ? count(array_diff($items, ['.', '..'])) : 0;
}
function folder_size($path) {
    $total = 0;
    foreach (scandir($path) as $entry) {
        if ($entry === '.' || $entry === '..') continue;
        $full = $path . DIRECTORY_SEPARATOR . $entry;
        $total += is_dir($full) ? folder_size($full) : filesize($full);
    }
    return $total;
}
function list_directory($path, $level = 0, $id_prefix = '') {
    global $t;
    static $folderId = 0;
    if (!is_readable($path)) return;
    $info = get_info($path);
    $folderId++; $id = $id_prefix . 'f' . $folderId;
    $padding = $level * 20;
    echo "<div class='folder-row'>
        <div class='entry' onclick=\"toggleFolder('$id', this)\" style='padding-left: {$padding}px;'>▶ {$t['folder_icon']} " . htmlspecialchars(basename($path)) . "</div>
        <div class='meta'>" . format_bytes($info['size']) . "</div>
        <div class='meta'>{$info['items']}</div>
        <div class='meta'>{$info['mtime']}</div>
        <div class='meta'>{$info['ctime']}</div>
        <div class='meta'>" . format_permissions($info['perms']) . "</div>
    </div>";
    echo "<div id='$id' class='children' style='display:none;'>";

    $entries = @scandir($path);
    if ($entries) {
        $dirs = $files = [];
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') continue;
            $full = $path . DIRECTORY_SEPARATOR . $entry;
            is_dir($full) ? $dirs[] = $entry : $files[] = $entry;
        }
        natcasesort($dirs); natcasesort($files);
        foreach ($dirs as $entry) list_directory($path . DIRECTORY_SEPARATOR . $entry, $level + 1, $id_prefix . 'c' . $folderId);
        foreach ($files as $entry) {
            $full = $path . DIRECTORY_SEPARATOR . $entry;
            $info = get_info($full);
            $padding = ($level + 1) * 20;
            echo "<div class='file-row'>
                <div class='entry' style='padding-left: {$padding}px;'>{$t['file_icon']} " . htmlspecialchars($entry) . "</div>
                <div class='meta'>" . format_bytes($info['size']) . "</div>
                <div class='meta'>-</div>
                <div class='meta'>{$info['mtime']}</div>
                <div class='meta'>{$info['ctime']}</div>
                <div class='meta'>" . format_permissions($info['perms']) . "</div>
            </div>";
        }
    }
    echo "</div>";
}
function list_parents($start) {
    $parts = [];
    $dir = realpath($start);
    while ($dir && is_dir($dir)) {
        $parts[] = $dir;
        $parent = dirname($dir);
        if ($parent === $dir) break;
        $dir = $parent;
    }
    return array_reverse($parts);
}
$parents = list_parents(__DIR__);
foreach ($parents as $p) {
    echo "<h2>" . htmlspecialchars($p) . "</h2>";
    list_directory($p);
}
?>
</body>
</html>
