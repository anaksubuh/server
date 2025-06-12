#!/bin/bash

cd /c/xampp/htdocs/server || exit

git add .
git commit -m "Backup on $(date '+%Y-%m-%d %H:%M:%S')"
git push origin main
