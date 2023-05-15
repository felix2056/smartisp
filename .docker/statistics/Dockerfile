FROM ncareau/mikro-watch

RUN docker-php-ext-install mysqli

COPY DaemonCommand.php src/Command/DaemonCommand.php

COPY IpAccount.php src/Entity/IpAccount.php

COPY InfluxDBService.php src/Service/InfluxDBService.php

COPY AccountingService.php src/Service/AccountingService.php