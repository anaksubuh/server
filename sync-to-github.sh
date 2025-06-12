#!/bin/bash

# Tambahkan path PHP dari XAMPP
export PATH=$PATH:/c/xampp/php

# Load GitHub token dari file .env
if [ -f .env ]; then
    export $(grep -v '^#' .env | xargs)
fi

# Backup database
echo "📦 Membackup database..."
php export_db.php

# Pastikan folder ini adalah git repo
if [ ! -d ".git" ]; then
    echo "🌀 Inisialisasi Git repo..."
    git init
    git branch -M main
    git remote add origin https://$GITHUB_TOKEN@github.com/anaksubuh/server.git
fi

# Commit dan push
echo "📤 Menyinkronkan ke GitHub..."
git add .
git commit -m "Backup on $(date '+%Y-%m-%d %H:%M:%S')" 2>/dev/null

# Tambahkan remote jika belum ada
git remote get-url origin >/dev/null 2>&1
if [ $? -ne 0 ]; then
    git remote add origin https://$GITHUB_TOKEN@github.com/anaksubuh/server.git
fi

# Push ke GitHub
git push -u origin main

echo "✅ Sinkronisasi selesai!"
