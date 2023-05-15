<?php

namespace App\Command;

use App\Service\AccountingService;
use App\Service\InfluxDBService;
use InfluxDB\Client;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Wrep\Daemonizable\Command\EndlessCommand;

class DaemonCommand extends EndlessCommand
{

    const INPUT_OPTION_TIMEOUT = 'timeout';

    protected $influxdb_database;
    protected $http_client;

    protected function configure()
    {
        $this
            ->setName('daemon')
            ->setDescription('Push data to InfluxDB endlessly')
            ->setHelp('This command allows you to push data periodically to InfluxDB as an endless process')
            ->setDefinition(
                new InputDefinition([
                    new InputOption(self::INPUT_OPTION_TIMEOUT, 't', InputOption::VALUE_OPTIONAL)
                ])
            )
            ->setTimeout(10);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->http_client = new \GuzzleHttp\Client(['verify' => getenv('MIKROTIK_SSL_VERIFY') == 'true']);

        $influx_client = new Client(getenv('INFLUXDB_HOST'), getenv('INFLUXDB_PORT'), getenv('INFLUXDB_USER'), getenv('INFLUXDB_PASS'));
        $this->influxdb_database = $influx_client->selectDB(getenv('INFLUXDB_DATABASE'));

        if ($input->getOption(self::INPUT_OPTION_TIMEOUT)) {
            $this->setTimeout($input->getOption(self::INPUT_OPTION_TIMEOUT));
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $conn = new \mysqli("127.0.0.1", getenv('DB_USERNAME'), getenv('DB_PASSWORD'), getenv('DB_DATABASE'));

        if ($conn->connect_errno) {
            echo "Failed to connect to MySQL: " . $conn->connect_error;
            exit();
        }

        $sql = "SELECT r.ip as ip, ad.network as network FROM routers r
                INNER JOIN address_routers ad ON r.id = ad.router_id";

        $result = $conn->query($sql);

        while ($router = $result->fetch_array(MYSQLI_ASSOC)) {

            $accounting_service = new AccountingService($this->http_client, (string)$router['network'], (string)$router['ip'], getenv('MIKROTIK_PORT'), getenv('MIKROTIK_PROTO'));
            $influxdb_service = new InfluxDBService($this->influxdb_database);

            try {
                $accounting_service->fetch();
                $accounting_service->parse();

                $this->throwExceptionOnShutdown();

                $influxdb_service->push($accounting_service->getData());
            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
            }
        }
    }
}
