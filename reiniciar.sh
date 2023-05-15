#!/bin/bash
chmod +x /var/www/reiniciar.sh
sudo service mysql restart
/etc/init.d/mysql restart

php artisan config:clear
php artisan cache:clear
php artisan config:cache
