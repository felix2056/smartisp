#!/bin/bash
apt-get -y update
add-apt-repository -y ppa:ondrej/php
apt-get -y update
apt-get -y install apache2 build-essential mysql-client php7.3 php7.3-mysql libcurl4-gnutls-dev libmysqlclient15-dev libapache2-mod-php7.3  php7.3-xml php7.3-gd php7.3-opcache php7.3-mbstring php7.3-ssh2
apt-get -y install php7.3-mysql php7.3-odbc  curl libapache2-mod-perl2 libxml2 php7.3-mysql php7.3-curl unzip zip sudo
apt-get -y install perl libnet-ssleay-perl openssl libauthen-pam-perl libpam-runtime libio-pty-perl apt-show-versions python dos2unix ntp
apt-get -y install php7.3-mysql
apt-get -y install curl
apt-get -y install php7.3-curl
apt-get -y update
apt-get -y install zip
apt-get -y update
apt-get -y install mcrypt php7.3-mcrypt
apt-get -y install php7.3-zip
apt-get -y install php7.3-imap
apt-get -y install php7.3-bz2
apt-get -y install php7.3-ldap
apt-get -y install php7.3-gd
apt-get -y install php7.3-xml
apt-get -y install php7.3-xmlrpc
apt-get -y install php7.3-bcmath
apt-get -y install jq
apt-get -y install nano

apt-get -y install libapache2-mod-php7.3
apt-get -y install php7.3-mbstring
apt-get -y install supervisor
apt-get -y upgrade


# install docker and docker-compose for graph microservices
apt-get remove docker docker-engine docker.io containerd runc

apt-get install \
    apt-transport-https \
    ca-certificates \
    curl \
    gnupg \
    lsb-releaseapt-get install \
    apt-transport-https \
    ca-certificates \
    curl \
    gnupg \
    lsb-release

curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

echo \
  "deb [arch=amd64 signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu \
  $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

apt-get update
apt-get install docker-ce docker-ce-cli containerd.io -y
apt-get install docker-compose -y

# add docker user
groupadd docker
usermod -aG docker $USER

# enable docker start at boot
systemctl enable docker.service
systemctl enable containerd.service

dpkg-reconfigure tzdata
clear
UP=$(/etc/init.d/mysql status | grep running | grep -v not | wc -l);
if [ "$UP" -ne 1 ];
then

	while true; do
		DB=$(whiptail --inputbox "\nNombre de la Base de datos:" 9 66 smartisp --title "Smartisp configuración de mysql-server" --nocancel 3>&1 1>&2 2>&3)
		if [ -z "$DB" ]; then
			whiptail --title "Smartisp configuración de mysql-server" --msgbox "\nDebe ingresar un nombre válido inténtalo de nuevo." 9 66
		else
			break
		fi
	done

	while true; do
		PASSWORD=$(whiptail --passwordbox "\nContraseña para el usuario <<root>> de MySQL:" 9 66 --title "Smartisp configuración de mysql-server" --nocancel 3>&1 1>&2 2>&3)
		PASSCONFIRM=$(whiptail --passwordbox "\nConfirmar contraseña:" 9 66 --title "Smartisp configuración de mysql-server" --nocancel 3>&1 1>&2 2>&3)
		if [ -z "$PASSWORD" ]; then
			whiptail --title "Smartisp configuración de mysql-server" --msgbox "\nPor seguridad debe ingresar una contraseña inténtalo de nuevo." 9 66
		else
			if [ "$PASSWORD" != "$PASSCONFIRM" ]; then
				whiptail --title "Smartisp configuración de mysql-server" --msgbox "\nLa contraseña no coincide inténtalo de nuevo." 9 66
			else
				break
			fi
		fi
	done

	clear
	echo "mysql-server mysql-server/root_password select $PASSWORD" | debconf-set-selections
	echo "mysql-server mysql-server/root_password_again select $PASSWORD" | debconf-set-selections
	apt-get -y install mysql-server
	echo "phpmyadmin phpmyadmin/dbconfig-install boolean true" | debconf-set-selections
	echo "phpmyadmin phpmyadmin/app-password-confirm password $PASSWORD" | debconf-set-selections
	echo "phpmyadmin phpmyadmin/mysql/admin-pass password $PASSWORD" | debconf-set-selections
	echo "phpmyadmin phpmyadmin/mysql/app-pass password $PASSWORD" | debconf-set-selections
	echo "phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2" | debconf-set-selections
	apt-get install -y phpmyadmin

	clear
	echo "\nConfigurando base de datos..."
	USERBD="$(tr -dc a-z0-9 < /dev/urandom | head -c 6 | xargs)"
	PASSBD="$(tr -dc a-z0-9 < /dev/urandom | head -c 15 | xargs)"
	mysql -u root -p$PASSWORD -e "DROP DATABASE IF EXISTS $DB;"
	mysql -u root -p$PASSWORD -e "CREATE DATABASE $DB /*\!40100 DEFAULT CHARACTER SET utf8 */;"
	mysql -u root -p$PASSWORD -e "CREATE USER $USERBD@localhost IDENTIFIED BY '$PASSBD';"
	mysql -u root -p$PASSWORD -e "GRANT ALL PRIVILEGES ON $DB.* TO '$USERBD'@'localhost';"
	mysql -u root -p$PASSWORD -e "FLUSH PRIVILEGES;"
	mysql --user=root --password=$PASSWORD --host=localhost $DB < smartisp.sql

    echo "\nConfigurando base de datos para radius..."
    mysql -u root -p$PASSWORD -e "DROP DATABASE IF EXISTS radius;"
    mysql -u root -p$PASSWORD -e "CREATE DATABASE radius /*\!40100 DEFAULT CHARACTER SET utf8 */;"
    mysql -u root -p$PASSWORD -e "CREATE USER $USERBD@localhost IDENTIFIED BY '$PASSBD';"
    mysql -u root -p$PASSWORD -e "GRANT ALL PRIVILEGES ON radius.* TO '$USERBD'@'localhost';"
    mysql -u root -p$PASSWORD -e "FLUSH PRIVILEGES;"

    PASSDBRADIUS=$PASSWORD


    IPPUBLIC=$(dig +short myip.opendns.com @resolver1.opendns.com)
	ZONAH=`sudo cat /etc/timezone`
	echo "
	APP_NAME=Laravel
	APP_ENV=local
	APP_KEY=base64:RElRBJVjQNnUaGpJM4ESB9KfBYvabRZOY6MxSiGL4fQ=
	APP_DEBUG=true
	APP_URL=http://localhost
	APP_TIMEZONE=$ZONAH

	LOG_CHANNEL=stack

	DB_CONNECTION=mysql
	DB_HOST=localhost
	DB_PORT=3306
	DB_DATABASE=$DB
	DB_USERNAME=root
	DB_PASSWORD=$PASSWORD

    DB_DATABASE_RADIUS=radius
    DB_USERNAME_RADIUS=root
    DB_PASSWORD_RADIUS=$PASSWORD
    DB_HOST_RADIUS=$IPPUBLIC
    DB_PORT_RADIUS=3306
    DB_HOST_RADIUS_DATABASE=127.0.0.1

	BROADCAST_DRIVER=log
	CACHE_DRIVER=file
	QUEUE_CONNECTION=sync
	SESSION_DRIVER=file
	SESSION_LIFETIME=120

	REDIS_HOST=127.0.0.1
	REDIS_PASSWORD=$PASSWORD
	REDIS_PORT=6379

	MAIL_DRIVER=smtp
	MAIL_HOST=smtp.mailtrap.io
	MAIL_PORT=2525
	MAIL_USERNAME=null
	MAIL_PASSWORD=null
	MAIL_ENCRYPTION=null

	AWS_ACCESS_KEY_ID=
	AWS_SECRET_ACCESS_KEY=
	AWS_DEFAULT_REGION=us-east-1
	AWS_BUCKET=

	PUSHER_APP_ID=
	PUSHER_APP_KEY=
	PUSHER_APP_SECRET=
	PUSHER_APP_CLUSTER=mt1

	MIX_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
	MIX_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"


	PAYPAL_CLIENT_ID=AeDdYwJkGd4Cd548J2y16gQ8-VNlQ1t2Woqpmt8DBiAjqNwI6zovc_FZmz9e9Bfavq-vh7fISaTh-S_R
	PAYPAL_SECRET=EB7fzPfPDGNcyajRy5nG_i-ZVpkaHJ-5636SQs7Lr7Ufxqe0GwuCNSUCi6ZldJvXf9xHzaL7jE2grqlJ
	# sandbox/live
	PAYPAL_MODE=sandbox

	STRIPE_KEY=pk_test_6hEu6uOZAUTwuuQpasIGPo0G
	STRIPE_SECRET=sk_test_cCunDXXVEd3ZuUzzna70pf0u
	" > www/.env

	echo "<?php  \$config= array(
	'database_type'=>'mysql',
	'database_name'=>'$DB',
	'server'=>'localhost',
	'username'=>'root',
	'password'=>'$PASSWORD',
	'charset'=>'utf8',
	'port'=>3306,
	'encryption_key'=>'8d215526d546f6ea1897e11a93461af434f893b8037d5fc1aefb50bc9f4c67d1' ); ?>" > www/public/monitorizacion/config.php


else

	PASS=$(whiptail --passwordbox "\nIngrese la contraseña para el usuario <<root>> de MySQL:" 9 66 --title "Mikro Rocket configuración de mysql-server" --nocancel 3>&1 1>&2 2>&3)

	while ! mysql -u root -p$PASS  -e ";" ; do
		PASS=$(whiptail --passwordbox "\nContraseña Incorrecta, inténtalo de nuevo: " 9 66 --title "Mikro Rocket configuración de mysql-server" --nocancel 3>&1 1>&2 2>&3)
	done

	echo "mysql-server mysql-server/root_password select $PASS" | debconf-set-selections
	echo "mysql-server mysql-server/root_password_again select $PASS" | debconf-set-selections
	apt-get -y install mysql-server
	echo "phpmyadmin phpmyadmin/dbconfig-install boolean true" | debconf-set-selections
	echo "phpmyadmin phpmyadmin/app-password-confirm password $PASS" | debconf-set-selections
	echo "phpmyadmin phpmyadmin/mysql/admin-pass password $PASS" | debconf-set-selections
	echo "phpmyadmin phpmyadmin/mysql/app-pass password $PASS" | debconf-set-selections
	echo "phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2" | debconf-set-selections
	apt-get install -y phpmyadmin

	clear
	echo "\nConfigurando base de datos..."
	mysql -u root -p$PASS  -e "DROP DATABASE IF EXISTS $DB"
	USERBD="$(tr -dc a-z0-9 < /dev/urandom | head -c 6 | xargs)"
	PASSBD="$(tr -dc a-z0-9 < /dev/urandom | head -c 15 | xargs)"
	mysql -u root -p$PASS -e "DROP DATABASE IF EXISTS $DB;"
	mysql -u root -p$PASS -e "CREATE DATABASE $DB /*\!40100 DEFAULT CHARACTER SET utf8 */;"
	mysql -u root -p$PASS -e "CREATE USER $USERBD@localhost IDENTIFIED BY '$PASSBD';"
	mysql -u root -p$PASS -e "GRANT ALL PRIVILEGES ON $DB.* TO '$USERBD'@'localhost';"
	mysql -u root -p$PASS -e "FLUSH PRIVILEGES;"
	mysql -u root -p$PASS -e "set global sql_mode='';"
	mysql --user=root --password=$PASS --host=localhost $DB < smartisp.sql

    echo "\nConfigurando base de datos para radius..."
    mysql -u root -p$PASS -e "DROP DATABASE IF EXISTS radius;"
    mysql -u root -p$PASS -e "CREATE DATABASE radius /*\!40100 DEFAULT CHARACTER SET utf8 */;"
    mysql -u root -p$PASS -e "CREATE USER $USERBD@localhost IDENTIFIED BY '$PASSBD';"
    mysql -u root -p$PASS -e "GRANT ALL PRIVILEGES ON radius.* TO '$USERBD'@'localhost';"
    mysql -u root -p$PASS -e "FLUSH PRIVILEGES;"
    mysql -u root -p$PASS -e "set global sql_mode='';"

    PASSDBRADIUS=$PASS


	ZONAH=`sudo cat /etc/timezone`
	echo "
	APP_NAME=Laravel
	APP_ENV=local
	APP_KEY=base64:RElRBJVjQNnUaGpJM4ESB9KfBYvabRZOY6MxSiGL4fQ=
	APP_DEBUG=true
	APP_URL=http://localhost
	APP_TIMEZONE=$ZONAH

	LOG_CHANNEL=stack

	DB_CONNECTION=mysql
	DB_HOST=localhost
	DB_PORT=3306
	DB_DATABASE=$DB
	DB_USERNAME=root
	DB_PASSWORD=$PASS

      DB_DATABASE_RADIUS=radius
      DB_USERNAME_RADIUS=root
      DB_PASSWORD_RADIUS=$PASS
      DB_HOST_RADIUS=$IPPUBLIC
      DB_PORT_RADIUS=3306
      DB_HOST_RADIUS_DATABASE=127.0.0.1

	BROADCAST_DRIVER=log
	CACHE_DRIVER=file
	QUEUE_CONNECTION=sync
	SESSION_DRIVER=file
	SESSION_LIFETIME=120

	REDIS_HOST=127.0.0.1
	REDIS_PASSWORD=$PASSWORD
	REDIS_PORT=6379

	MAIL_DRIVER=smtp
	MAIL_HOST=smtp.mailtrap.io
	MAIL_PORT=2525
	MAIL_USERNAME=null
	MAIL_PASSWORD=null
	MAIL_ENCRYPTION=null

	AWS_ACCESS_KEY_ID=
	AWS_SECRET_ACCESS_KEY=
	AWS_DEFAULT_REGION=us-east-1
	AWS_BUCKET=

	PUSHER_APP_ID=
	PUSHER_APP_KEY=
	PUSHER_APP_SECRET=
	PUSHER_APP_CLUSTER=mt1

	MIX_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
	MIX_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"


	PAYPAL_CLIENT_ID=AeDdYwJkGd4Cd548J2y16gQ8-VNlQ1t2Woqpmt8DBiAjqNwI6zovc_FZmz9e9Bfavq-vh7fISaTh-S_R
	PAYPAL_SECRET=EB7fzPfPDGNcyajRy5nG_i-ZVpkaHJ-5636SQs7Lr7Ufxqe0GwuCNSUCi6ZldJvXf9xHzaL7jE2grqlJ
	# sandbox/live
	PAYPAL_MODE=sandbox

	STRIPE_KEY=pk_test_6hEu6uOZAUTwuuQpasIGPo0G
	STRIPE_SECRET=sk_test_cCunDXXVEd3ZuUzzna70pf0u
	" > www/.env

	echo "<?php \$config= array(
	'database_type'=>'mysql',
	'database_name'=>'$DB',
	'server'=>'localhost',
	'username'=>'root',
	'password'=>'$PASS',
	'charset'=>'utf8',
	'port'=>3306,
	'encryption_key'=>'8d215526d546f6ea1897e11a93461af434f893b8037d5fc1aefb50bc9f4c67d1' ); ?>" > www/public/monitorizacion/config.php

fi
echo "Instalando ioncube..."
cp laravel.conf /etc/apache2/sites-available/laravel.conf
rm laravel.conf

cp ioncube_loader_lin_7.3.so /usr/lib/php/20180731/ioncube_loader_lin_7.3.so
cp php.ini /etc/php/7.3/apache2/php.ini
cp php.ini /etc/php/7.3/cli/php.ini
cp 00-ioncube.ini /etc/php/7.3/apache2/conf.d/00-ioncube.ini
cp config.inc.php /etc/phpmyadmin/config.inc.php
rm ioncube_loader_lin_7.3.so

cp supervisord.conf /etc/supervisor/supervisord.conf
#rm supervisord.conf

cp queue-work.conf /etc/supervisor/conf.d/queue-work.conf
#rm queue-work.conf

echo "Instalando freeradius..."
apt-get -y install freeradius freeradius-mysql freeradius-utils
echo "llenando base de datos freeradius..."
mysql --user=root --password=$PASSDBRADIUS --host=localhost radius < /etc/freeradius/3.0/mods-config/sql/main/mysql/schema.sql

#indicamos al archivo de sudo que www-data puede reiniciar el servicio
echo "Modificando archivo sudoers para www-data"

sudo cat >> /etc/sudoers << EOF
www-data ALL=(root) NOPASSWD: /sbin/reboot
www-data ALL=(root) NOPASSWD: /sbin/shutdown
gammu ALL=(ALL) NOPASSWD: ALL
EOF

if ! grep -q "www-data ALL=(ALL) NOPASSWD: /etc/init.d/freeradius restart" "/etc/sudoers";
then
sudo cat >> /etc/sudoers << EOF
www-data ALL=(ALL) NOPASSWD: /etc/init.d/freeradius restart
www-data ALL=(ALL) NOPASSWD: /usr/bin/killall
www-data ALL=(ALL) NOPASSWD: /usr/sbin/freeradius
EOF
fi

echo "habilitando sql y coa en freeradius"
#habilitamos sql y coa en el servidor
sudo ln -s /etc/freeradius/3.0/sites-available/coa /etc/freeradius/3.0/sites-enabled/
sudo ln -s /etc/freeradius/3.0/mods-available/sql /etc/freeradius/3.0/mods-enabled/

echo "configurando sql para freeradius"
#configuramos el archivo sql
 echo '
  sql {
	driver = "rlm_sql_mysql"
        dialect = "mysql"
	server = "localhost"
	port = 3306
	login = "root"
	password = "'$PASSDBRADIUS'"
	radius_db = "radius"
	acct_table1 = "radacct"
	acct_table2 = "radacct"
	postauth_table = "radpostauth"
	authcheck_table = "radcheck"
	groupcheck_table = "radgroupcheck"
	authreply_table = "radreply"
	groupreply_table = "radgroupreply"
	usergroup_table = "radusergroup"
	read_groups = yes
	delete_stale_sessions = yes
	pool {
		start = ${thread[pool].start_servers}
		min = ${thread[pool].min_spare_servers}
		max = ${thread[pool].max_servers}
		spare = ${thread[pool].max_spare_servers}
		uses = 0
		retry_delay = 30
		lifetime = 0
		idle_timeout = 60
	}
	read_clients = yes
	client_table = "nas"
	group_attribute = "SQL-Group"
	$INCLUDE ${modconfdir}/${.:name}/main/${dialect}/queries.conf
}' > /etc/freeradius/3.0/mods-available/sql

service freeradius restart

echo "Instalando redis..."
apt-get -y install redis-server
echo "verify last line conf of redis.conf and put the same password of mysql"
if ! grep -q "requirepass $PASSWORD" "/etc/redis/redis.conf";
then
sudo cat >> /etc/redis/redis.conf << EOF
requirepass $PASSWORD
EOF
fi

service redis-server restart

#sudo service supervisor restart
service apache2 restart
echo "Instalando sistema..."



rm -r /var/www/html/
cp -r www /var/
rm -f -r www
chown -R www-data:www-data /var/www/
chmod 777 /var/www/
chmod -R 775 /var/www/storage
chmod -R 775 /var/www/bootstrap/cache


chmod -R g+rw /var/www/public/automate/smartbw.php

a2dissite 000-default.conf
a2ensite laravel.conf
a2enmod rewrite

# Activando la version 7.3 de php
sudo a2dismod php7.4
sudo a2enmod php7.3
sudo update-alternatives --set php /usr/bin/php7.3
sudo update-alternatives --set phar /usr/bin/phar7.3

#sudo service supervisor restart
service apache2 restart

#sudo systemctl enable supervisor

#cp -f sudoers /etc/
#rm sudoers
rm smartisp.sql
cd /tmp


curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

echo "OK"
echo "----------------------------"



echo "Ajustando memoria..."
sudo fallocate -l 2G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile
echo "OK"
echo "----------------------------"

echo "Instalando system..."
cd ..
cd /var/www/
composer install --no-interaction
php artisan key:generate
echo "OK"
echo "----------------------------"

echo "Limpiando cache..."
php artisan config:clear
php artisan cache:clear
php artisan config:cache
echo "OK"
echo "----------------------------"

echo "Instalando Bd..."
php artisan migrate
php artisan db:seed templatesSeeder
echo "OK"
echo "----------------------------"


echo "Reiniciando..."
service apache2 restart
echo "OK"
echo "----------------------------"

sudo systemctl enable supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start queue-work:*
#sudo systemctl restart supervisor
sudo service supervisor restart
sudo update-rc.d supervisor defaults
echo "[Unit]
Description=/etc/rc.local Compatibility
ConditionPathExists=/etc/rc.local

[Service]
Type=forking
ExecStart=/etc/rc.local start
TimeoutSec=0
StandardOutput=tty
RemainAfterExit=yes
SysVStartPriority=99

[Install]
WantedBy=multi-user.target" > /etc/systemd/system/rc-local.service
touch /etc/rc.local
chmod +x /etc/rc.local
systemctl enable rc-local
echo "#!/bin/bash
sleep 120
/etc/init.d/supervisor restart
exit 0" > /etc/rc.local

# start smartisp graph microservices

cd /var/www
#docker-compose up -d

docker-compose -f docker-compose.yaml up -d

systemctl daemon-reload

echo "Instalando servicios..."
crontab -l 2>/dev/null; echo '30 23 * * * /sbin/shutdown -r now\n0 */1 * * * /etc/init.d/freeradius restart\n*/3 * * * * /usr/bin/php /var/www/public/automate/waipros.php\n*/20 * * * * /usr/bin/php /var/www/public/automate/ipstatus.php\n*/15 * * * * /usr/bin/php /var/www/public/automate/system.php\n*/15 * * * * /usr/bin/php /var/www/public/automate/smartbw.php\n*/3 * * * * /usr/bin/php /var/www/public/automate/monitoreo.php\n*/6 * * * * /usr/bin/php /var/www/public/automate/queues.php\n* * * * * /usr/bin/php /var/www/artisan schedule:run >> /dev/null 2>&1' | crontab  -
myip=$(ip a s|sed -ne '/127.0.0.1/!{s/^[ \t]*inet[ \t]*\([0-9.]\+\)\/.*$/\1/p}')
whiptail --title "Smartisp instalación terminada" --msgbox "\nFelicitaciones la instalación de Smartisp ha finalizado.\n \nAcceso al sistema: http://${myip}/admin \nUsuario: admin\nContraseña: smartisp123" 13 66
clear
echo "OK"
echo "----------------------------"

# Activando la version 7.3 de php
sudo a2dismod php7.4
sudo a2enmod php7.3
sudo update-alternatives --set php /usr/bin/php7.3
sudo update-alternatives --set phar /usr/bin/phar7.3

#sudo service supervisor restart
service apache2 restart

echo "Limpiando cache..."
cd /var/www/
php artisan config:clear
php artisan cache:clear
php artisan config:cache
echo "OK"
echo "----------------------------"

# create organisation, api key, datasource, dashboard and panels

curl -X POST http://admin:smartisp@localhost:3000/api/user/using/1

graph_key=$(curl -X POST -H "Content-Type: application/json" -d '{"name":"smartispgraphkey", "role": "Admin"}' http://admin:smartisp@localhost:3000/api/auth/keys | jq -r '.key')

curl \
-H "Content-Type: application/json" \
-H "Authorization: Bearer $graph_key" \
-X POST \
-d '{"name":"influxdb",
"type":"influxdb",
"url":"influxdb:8086",
"password":"smartisp",
"user":"admin",
"database":"smartseries",
"access":"proxy",
"basicAuth":false}' \
http://localhost:3000/api/datasources

curl \
-H "Content-Type: application/json" \
-H "Authorization: Bearer $graph_key" \
-X POST \
-d '{
  "dashboard": {
    "id": null,
    "uid": "lZjJB-NGk",
    "title": "smartisp_graph",
    "tags": ["templated"],
    "timezone": "browser",
    "schemaVersion": 16,
    "version": 0,
    "refresh": "25s",
    "templating": {
      "list": [
        {
          "allValue": null,
          "datasource": "influxdb",
          "definition": "show tag values from \"autogen\".\"net_traffic\" with key=\"ip\"",
          "description": null,
          "error": null,
          "hide": 0,
          "includeAll": false,
          "label": null,
          "multi": false,
          "name": "client_ip",
          "options": [],
          "query": "show tag values from \"autogen\".\"net_traffic\" with key=\"ip\"",
          "refresh": 1,
          "regex": "",
          "skipUrlSync": false,
          "sort": 0,
          "type": "query"
        }
      ]
    },
    "time": {
      "from": "now-6h",
      "to": "now"
    },
    "panels": [
      {
        "datasource": "influxdb",
        "fieldConfig": {
          "defaults": {
            "color": {
              "mode": "palette-classic"
            },
            "custom": {
              "axisLabel": "",
              "axisPlacement": "auto",
              "barAlignment": 0,
              "drawStyle": "line",
              "fillOpacity": 0,
              "gradientMode": "none",
              "hideFrom": {
                "legend": false,
                "tooltip": false,
                "viz": false
              },
              "lineInterpolation": "smooth",
              "lineStyle": {
                "fill": "solid"
              },
              "lineWidth": 1,
              "pointSize": 5,
              "scaleDistribution": {
                "type": "linear"
              },
              "showPoints": "auto",
              "spanNulls": true,
              "stacking": {
                "group": "A",
                "mode": "none"
              },
              "thresholdsStyle": {
                "mode": "off"
              }
            },
            "mappings": [],
            "thresholds": {
              "mode": "absolute",
              "steps": [
                {
                  "color": "green",
                  "value": null
                },
                {
                  "color": "red",
                  "value": 80
                }
              ]
            },
            "unit": "Bps"
          },
          "overrides": []
        },
        "gridPos": {
          "h": 8,
          "w": 12,
          "x": 0,
          "y": 0
        },
        "id": 2,
        "options": {
          "legend": {
            "calcs": ["mean"],
            "displayMode": "list",
            "placement": "bottom"
          },
          "tooltip": {
            "mode": "single"
          }
        },
        "targets": [
          {
            "alias": "Download Speed",
            "groupBy": [
              {
                "params": ["$__interval"],
                "type": "time"
              },
              {
                "params": ["null"],
                "type": "fill"
              }
            ],
            "measurement": "net_traffic",
            "orderByTime": "ASC",
            "policy": "default",
            "queryType": "randomWalk",
            "refId": "A",
            "resultFormat": "time_series",
            "select": [
              [
                {
                  "params": ["down_byte"],
                  "type": "field"
                },
                {
                  "params": [],
                  "type": "mean"
                }
              ]
            ],
            "tags": [
              {
                "key": "ip",
                "operator": "=~",
                "value": "/^$client_ip$/"
              }
            ]
          },
          {
            "alias": "Upload Speed",
            "groupBy": [
              {
                "params": ["$__interval"],
                "type": "time"
              },
              {
                "params": ["null"],
                "type": "fill"
              }
            ],
            "hide": false,
            "measurement": "net_traffic",
            "orderByTime": "ASC",
            "policy": "default",
            "refId": "B",
            "resultFormat": "time_series",
            "select": [
              [
                {
                  "params": ["up_byte"],
                  "type": "field"
                },
                {
                  "params": [],
                  "type": "mean"
                }
              ]
            ],
            "tags": [
              {
                "key": "ip",
                "operator": "=~",
                "value": "/^$client_ip$/"
              }
            ]
          }
        ],
        "title": "Download/Upload Traffic",
        "transparent": true,
        "type": "timeseries"
      }
    ]
  },
  "folderId": 0,
  "overwrite": false
}
' \
http://localhost:3000/api/dashboards/db

curl \
-H "Content-Type: application/json" \
-H "Authorization: Bearer $graph_key" \
-X PUT \
-d '{"name":"smartisp"}' \
http://localhost:3000/api/org




cd /root/script_crontab
sudo chmod +x cronrestarter.sh
sudo cp cronrestarter.sh /usr/bin/cronrestarter.sh && cp cronrestarter.service /lib/systemd/system/cronrestarter.service
sudo systemctl daemon-reload
sudo systemctl enable cronrestarter.service
sudo systemctl start cronrestarter.service

echo "Terminando configuraciones..."
sleep 2
echo "OK"
echo "----------------------------"
echo "------------------------------------"
echo "INSTALACIÓN TERMINADA CON ÉXITO..."
echo "------------------------------------"
echo "* Ya puede cerrar la consola"
echo "------------------------------------"
sleep 2
reboot

