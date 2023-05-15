#!/bin/bash

set -x

wget --no-check-certificate http://instalar.smartisp.us/smartisp_install_new.zip -O smartisp_install_new.zip -q --show-progress --progress=bar:force

mkdir -m 777 www2

cp smartisp_install_new.zip www2

cp -rf /var/www/resources/views/templates ./www2

cp -rf /var/www/public/assets/img ./www2

cp -rf /var/www/public/js/lib_firma_sri/src/services/uploads/ ./www2

cp -rf /var/www/public/monitorizacion/config.php ./www2

cp -rf /var/www/config/mail.php ./www2

rm smartisp_install_new.zip

unzip www2/smartisp_install_new.zip -d www2

chown -R www-data:www-data ./www2/smartisp_install_new/www/

chmod 777 ./www2/smartisp_install_new/www/

chown -R www-data:www-data /var/www/storage/logs

apt-get -y install supervisor

# install docker and docker-compose for graph microservices

apt-get remove docker docker-engine docker.io containerd runc -y

systemctl stop grafana-server.service

apt-get remove --auto-remove grafana influxdb -y

apt-get install -y apt-transport-https ca-certificates curl gnupg lsb-release

curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

echo "deb [arch=amd64 signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

apt-get update

apt-get install docker-ce docker-ce-cli containerd.io -y

apt-get install docker-compose -y

# add docker user

groupadd docker

usermod -aG docker $USER

# enable docker start at boot

systemctl enable docker.service

systemctl enable containerd.service

cp -rf www2/smartisp_install_new/www/public /var/www

cp -rf www2/smartisp_install_new/www/app /var/www

cp -rf www2/smartisp_install_new/www/resources /var/www

cp -rf www2/smartisp_install_new/www/routes /var/www

cp -rf www2/smartisp_install_new/www/database /var/www

cp -rf www2/smartisp_install_new/www/config /var/www

cp -rf www2/smartisp_install_new/www/composer.json /var/www

cp -rf www2/smartisp_install_new/www/package.json /var/www

cp -rf www2/smartisp_install_new/www/composer.lock /var/www

#copy the docker-compose.yaml and .docker directory

cp -rf www2/smartisp_install_new/www/docker-compose.yaml /var/www

cp -rf www2/smartisp_install_new/www/.docker /var/www


cp -rf www2/templates /var/www/resources/views/

cp -rf www2/img /var/www/public/assets/

cp -rf www2/uploads /var/www/public/js/lib_firma_sri/src/services/

cp -rf www2/config.php /var/www/public/monitorizacion

cp -rf www2/mail.php /var/www/config

mv www2/smartisp_install_new/script_crontab /root

sudo chown -R www-data:www-data /etc/supervisor

sudo chmod -R 777 /etc/supervisor

sudo chmod -R 777 /var/www/storage/logs

cp www2/smartisp_install_new/supervisord.conf /etc/supervisor/supervisord.conf

mv www2/smartisp_install_new/supervisord.conf /etc/supervisor/supervisord.conf

cp www2/smartisp_install_new/queue-work.conf /etc/supervisor/conf.d/queue-work.conf

mv www2/smartisp_install_new/queue-work.conf /etc/supervisor/conf.d/queue-work.conf

rm -r www2

chmod -R 777 /var/www/resources/lang

php artisan cache:clear

rm -rf /var/www/bootstrap/cache/*

cd /var/www

composer install --no-interaction

sudo fallocate -l 2G /swapfile

sudo chmod 600 /swapfile

sudo mkswap /swapfile

sudo swapon /swapfile

composer update barryvdh/laravel-debugbar

cd /var/www

# start smartisp graph microservices -->this builds and starts the microservices (influxdb, grafana, micro-watch) and the customs scripts to support multirouter and network ranges

docker-compose -f docker-compose.yaml up -d

composer dump-autoload

composer update

php artisan migrate

php artisan db:seed templatesSeeder

crontab -l 2>/dev/null; echo '23:3030 23 * * * /sbin/shutdown -r now\n0 */1 * * * /etc/init.d/freeradius restart\n*/3 * * * * /usr/bin/php /var/www/public/automate/waipros.php\n*/20 * * * * /usr/bin/php /var/www/public/automate/ipstatus.php\n*/15 * * * * /usr/bin/php /var/www/public/automate/system.php\n*/15 * * * * /usr/bin/php /var/www/public/automate/smartbw.php\n*/3 * * * * /usr/bin/php /var/www/public/automate/monitoreo.php\n*/6 * * * * /usr/bin/php /var/www/public/automate/queues.php\n* * * * * /usr/bin/php /var/www/artisan schedule:run >> /dev/null 2>&1' | crontab  -

php artisan config:clear

php artisan cache:clear

php artisan config:cache

apt-get -y install libapache2-mod-php7.3

a2enmod php7.3

sudo apt-get -y install jq

#### @ovihub start of code ###

# create organisation, api key, datasource, dashboard and panels -->this snippet creates via API

sleep 4

curl -X POST http://admin:smartisp@localhost:3000/api/user/using/1

graph_key=$(curl -X POST -H "Content-Type: application/json" -d '{"name":"smartispgraphkey", "role": "Admin"}' http://admin:smartisp@localhost:3000/api/auth/keys | jq -r '.key')

curl -H "Content-Type: application/json" -H "Authorization: Bearer $graph_key" -X POST -d '{"name":"influxdb",

"type":"influxdb",

"url":"influxdb:8086",

"password":"smartisp",

"user":"admin",

"database":"smartseries",

"access":"proxy",

"basicAuth":false}' http://localhost:3000/api/datasources

curl -H "Content-Type: application/json" -H "Authorization: Bearer $graph_key" -X POST -d '{

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

}' http://localhost:3000/api/dashboards/db


curl -H "Content-Type: application/json" -H "Authorization: Bearer $graph_key" -X PUT -d '{"name":"smartisp"}' http://localhost:3000/api/org

### @ovihub end of code

# Activando la version 7.3 de php

sudo a2dismod php7.4

sudo a2enmod php7.3

sudo update-alternatives --set php /usr/bin/php7.3

sudo update-alternatives --set phar /usr/bin/phar7.3

#sudo service supervisor restart

service apache2 restart

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

echo "Limpiando cache..."

cd /var/www/

php artisan config:clear

php artisan cache:clear

php artisan config:cache

echo "OK"

echo "----------------------------"

cd /root/script_crontab

sudo chmod +x cronrestarter.sh

sudo cp cronrestarter.sh /usr/bin/cronrestarter.sh && cp cronrestarter.service /lib/systemd/system/cronrestarter.service

sudo systemctl daemon-reload

sudo systemctl enable cronrestarter.service

sudo systemctl start cronrestarter.service

echo "Instalando freeradius..."
apt-get -y install freeradius freeradius-mysql freeradius-utils

## For collect mysql root DB_PASSWORD_RADIUS ##
DB_DATABASE=$(find /var/www/ -type f -name ".env" -not -path "*collector*" -exec grep 'DB_DATABASE'  {} \; | awk -F "=" '{print $2}' | head -n 1)
DB_USERNAME=$(find /var/www/ -type f -name ".env" -exec grep 'DB_USERNAME'  {} \; | awk -F "=" '{print $2}' | head -n 1)
DB_PASSWORD_RADIUS=$(find /var/www/ -type f -name ".env" -exec grep 'DB_PASSWORD'  {} \; | awk -F "=" '{print $2}' | head -n 1)
ZONAENV=$(find /var/www/ -type f -name ".env" -exec grep 'APP_TIMEZONE'  {} \; | awk -F "=" '{print $2}' | head -n 1)

# Create radius database  if not exist##
PASSDBRADIUS=$DB_PASSWORD_RADIUS

if ! mysql -u root -p$DB_PASSWORD_RADIUS -e 'use radius'; then
    if [ $USER = "root" ]; then
        echo "\nConfigurando base de datos para radius..."
        mysql -u root -p$DB_PASSWORD_RADIUS -e "DROP DATABASE IF EXISTS radius;"
        mysql -u root -p$DB_PASSWORD_RADIUS -e "CREATE DATABASE radius /*\!40100 DEFAULT CHARACTER SET utf8 */;"
        mysql -u root -p$DB_PASSWORD_RADIUS -e "GRANT ALL PRIVILEGES ON radius.* TO '$'@'localhost';"
        mysql -u root -p$DB_PASSWORD_RADIUS -e "FLUSH PRIVILEGES;"
        echo "Llenando base de datos freeradius..."
        mysql --user=root --password=$DB_PASSWORD_RADIUS --host=localhost radius < /etc/freeradius/3.0/mods-config/sql/main/mysql/schema.sql
    fi
fi


IPPUBLIC=$(dig +short myip.opendns.com @resolver1.opendns.com)
  ZONAH=`cat /etc/timezone`
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
  DB_DATABASE=$DB_DATABASE
  DB_USERNAME=root
  DB_PASSWORD=$DB_PASSWORD_RADIUS

  DB_DATABASE_RADIUS=radius
  DB_USERNAME_RADIUS=root
  DB_PASSWORD_RADIUS=$DB_PASSWORD_RADIUS
  DB_HOST_RADIUS=$IPPUBLIC
  DB_PORT_RADIUS=3306
  DB_HOST_RADIUS_DATABASE=127.0.0.1

  BROADCAST_DRIVER=log
  CACHE_DRIVER=file
  QUEUE_CONNECTION=sync
  SESSION_DRIVER=file
  SESSION_LIFETIME=120

  REDIS_HOST=127.0.0.1
  REDIS_PASSWORD=$DB_PASSWORD_RADIUS
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
  " > /var/www/.env

  echo "<?php  \$config= array(
  'database_type'=>'mysql',
  'database_name'=>'$DB_DATABASE',
  'server'=>'localhost',
  'username'=>'root',
  'password'=>'$DB_PASSWORD_RADIUS',
  'charset'=>'utf8',
  'port'=>3306,
  'encryption_key'=>'8d215526d546f6ea1897e11a93461af434f893b8037d5fc1aefb50bc9f4c67d1' ); ?>" > /var/www/public/monitorizacion/config.php

#indicamos al archivo de sudo que www-data puede reiniciar el servicio
echo "Modificando archivo sudoers para www-data"
if ! grep -q "www-data ALL=(ALL) NOPASSWD: /etc/init.d/freeradius restart" "/etc/sudoers";
then
sudo cat >> /etc/sudoers << EOF
www-data ALL=(ALL) NOPASSWD: /etc/init.d/freeradius restart
EOF
fi

if ! grep -q "www-data ALL=(ALL) NOPASSWD: /usr/bin/killall" "/etc/sudoers";
then
sudo cat >> /etc/sudoers << EOF
www-data ALL=(ALL) NOPASSWD: /usr/bin/killall
EOF
fi

if ! grep -q "www-data ALL=(ALL) NOPASSWD: /usr/sbin/freeradius" "/etc/sudoers";
then
sudo cat >> /etc/sudoers << EOF
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
	password = "'$DB_PASSWORD_RADIUS'"
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


echo "Instalando redis..."
apt-get -y install redis-server
echo "verify last line conf of redis.conf and put the same password of mysql"
if ! grep -q "requirepass $DB_PASSWORD_RADIUS" "/etc/redis/redis.conf";
then
sudo cat >> /etc/redis/redis.conf << EOF
requirepass $DB_PASSWORD_RADIUS
EOF
fi

service redis-server restart

