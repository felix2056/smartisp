#!/bin/bash
wget --no-check-certificate http://instalar.smartisp.us/smartisp_update.zip -O smartisp_update.zip -q --show-progress --progress=bar:force
mkdir -m 777 www2
cp smartisp_update.zip www2

rm smartisp_update.zip
unzip www2/smartisp_update.zip -d www2
chown -R www-data:www-data ./www2/smartisp_update/
chmod 777 ./www2/smartisp_update/update.sh
cp -rf www2/smartisp_update/update.sh /var/www
rm -r www2
