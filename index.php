<?php
// index.php
$baseDir = __DIR__ . '/myfile';

// Buat folder jika belum ada
if (!file_exists($baseDir)) {
    mkdir($baseDir, 0777, true);
}

$requestUri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$relativePath = trim(str_replace($scriptName, '', $requestUri), '/');
$targetPath = $baseDir . '/' . $relativePath;
$fullPath = file_exists($targetPath) ? realpath($targetPath) : $targetPath;

$realBase = realpath($baseDir);
$realTarget = realpath($fullPath) ?: $fullPath;

if (strpos($realTarget, $realBase) !== 0) {
    http_response_code(403);
    echo "Access denied.";
    exit;
}


// Handle download
if (isset($_GET['download'])) {
    if (is_file($fullPath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($fullPath) . '"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }
}

// Handle rename
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rename']) && isset($_POST['new_name'])) {
    $newName = basename($_POST['new_name']);
    $newPath = dirname($fullPath) . '/' . $newName;
    if (rename($fullPath, $newPath)) {
        header("Location: /server/" . ltrim(str_replace($baseDir, '', $newPath), '/'));
        exit;
    } else {
        echo "Gagal mengganti nama.";
    }
}

function listDirectory($dir, $relativePath = '') {
    $items = scandir($dir);
    echo '<div class="grid">';
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $itemPath = "$dir/$item";
        $itemRelPath = trim("$relativePath/$item", '/');
        $urlPath = "/server/" . $itemRelPath;

        echo '<div class="item">';
        if (is_dir($itemPath)) {
            echo "📁 <a href='$urlPath'><strong>$item</strong></a>";
        } else {
            echo "📄 <a href='$urlPath'>$item</a>";
        }
        echo " <a href='$urlPath?download=1' title='Download'>⬇️</a>";
        echo "<form method='post' class='inline-form'>
                <input type='text' name='new_name' placeholder='Rename...' required>
                <input type='hidden' name='rename' value='1'>
                <button>Rename</button>
              </form>";
        echo '</div>';
    }
    echo '</div>';
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>File Manager</title>
  <style>
    body {
        font-family: Arial, sans-serif;
        max-width: 900px;
        margin: 2rem auto;
        background: #f5f5f5;
        padding: 1rem;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    a { text-decoration: none; color: #333; }
    .grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 10px;
    }
    .item {
        background: #fff;
        padding: 10px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .inline-form {
        display: inline-block;
        margin-left: 10px;
    }
    input[type=text] {
        padding: 4px;
        font-size: 0.9em;
    }
    textarea {
        width: 100%;
        height: 400px;
        font-family: monospace;
    }
    button {
        padding: 4px 10px;
        font-size: 0.9em;
        margin-left: 5px;
    }
    form {
        margin-top: 1rem;
    }
  </style>
</head>
<body>
<h2>📂 File Manager</h2>
<a href="/server/">🏠 Home</a><br><br>
<?php
if (is_dir($fullPath)) {
    echo "<h3>Isi folder: <em>$relativePath</em></h3>";
    listDirectory($fullPath, $relativePath);
    echo "<hr>
    <form method='post' enctype='multipart/form-data'>
        <input type='file' name='upload_file' required>
        <button name='upload'>📤 Upload</button>
    </form>
    <form method='post'>
        <input type='text' name='folder_name' placeholder='Nama folder' required>
        <button name='create_folder'>📁 Buat Folder</button>
    </form>";

    // Upload
    if (isset($_FILES['upload_file'])) {
        $name = basename($_FILES['upload_file']['name']);
        move_uploaded_file($_FILES['upload_file']['tmp_name'], "$fullPath/$name");
        header("Location: /server/$relativePath");
        exit;
    }

    // Create folder
    if (isset($_POST['folder_name'])) {
        mkdir("$fullPath/" . basename($_POST['folder_name']));
        header("Location: /server/$relativePath");
        exit;
    }

} elseif (is_file($fullPath)) {
    $ext = pathinfo($fullPath, PATHINFO_EXTENSION);
    if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif'])) {
        echo "<img src='/server/$relativePath' style='max-width:100%; border: 1px solid #ccc;'>";
    } elseif (in_array(strtolower($ext), ['txt', 'html', 'md', 'log'])) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_content'])) {
            file_put_contents($fullPath, $_POST['edit_content']);
        }
        $content = htmlspecialchars(file_get_contents($fullPath));
        echo "<form method='post'>
              <textarea name='edit_content'>$content</textarea><br>
              <button>Simpan</button></form>";
    } else {
        echo "<p><a href='?download=1'>⬇️ Download file</a></p>";
    }
} else {
    echo "<p style='color:red;'>❌ Tidak ditemukan.</p>";
}
?>
</body>
</html>
