<?php
// export_db.php
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = ''; // kosongkan kalau XAMPP default
$dbName = 'server'; // ganti dengan nama database kamu

$date = date('Ymd_His');
$backupDir = __DIR__ . '/backup';
$backupFile = "$backupDir/db_backup_$date.sql";

// Pastikan folder backup ada
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0777, true);
}

// Path ke mysqldump
$mysqldumpPath = '"C:/xampp/mysql/bin/mysqldump.exe"';

// Jalankan mysqldump
$command = "$mysqldumpPath -u$dbUser -p$dbPass $dbName > \"$backupFile\"";
system($command, $result);

if ($result === 0) {
    echo "✅ Backup berhasil: $backupFile\n";
} else {
    echo "❌ Gagal backup database.\n";
}
?>
