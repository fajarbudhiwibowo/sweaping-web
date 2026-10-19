#!/bin/bash
# Pindah ke direktori project
cd /home/lsmw8652/public_html/sweaping.lsmkompas.id

# Konfigurasi Git (jika belum pernah diset di server)
git config user.name "Fajar Budhi Wibowo"
git config user.email "fajarbudhiwibowo@gmail.com"

# Lakukan commit & push
git add -A
git commit -m "Auto-commit: Backup mingguan & pembaruan sistem [$(date +'%Y-%m-%d')]"
git push origin main