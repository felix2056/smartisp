<?php

namespace App\Imports;

use App\Classes\Reply;
use App\Events\ImportClients;
use App\Http\Controllers\SecurityController;
use App\libraries\AddClient;
use App\libraries\Pencrypt;
use App\libraries\StatusIp;
use App\models\BillingSettings;
use App\models\Client;
use App\models\ClientService;
use App\models\SuspendClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ClientImport implements ToCollection,WithHeadingRow
{

    private $requestData;
    public function __construct(array $data)
    {
        $this->requestData = $data;
    }

    public function collection(Collection $rows)
    {

        $en = new Pencrypt();
        foreach ($rows as $row)
        {
            $usedip = new StatusIp();

            DB::beginTransaction();
            if(!is_null($row['email'])) {

                $check = Client::where('email', $row['email'])->first();
                $ipCheck = ClientService::where('ip', $row['ip_cliente'])->first();
                if(!$check && !$ipCheck) {

                    $client = Client::create([
                        'name' => $row['nombre_completo'],
                        'phone' => $row['telefono'],
                        'email' => $row['email'],
                        'dni' => $row['numero_dnici'],
                        'password' => $en->encode($row['contrasena_portal']),
                        'address' => $row['direccion'],
                        'online' => "ver",
                    ]);

                    (new BillingSettings())->create([
                        'client_id' => $client->id,
                        'billing_date' => $this->requestData['billing_day'],
                        'billing_due_date' => $this->requestData['billing_due'],
                        'billing_invoice_pay_type' => $this->requestData['invoice_pay_type'],
                    ]);

                    $clientService = new ClientService();
                    $clientService->client_id = $client->id;
                    $clientService->ip = $row['ip_cliente'];
                    $clientService->mac = $row['mac'];

                    if($row['usuario_ppp_secrets'] != '') {
                        $clientService->user_hot = $row['usuario_ppp_secrets'];
                    }

                    $clientService->pass_hot = $en->encode($row['contrasena_ppp_secrets']);
                    $clientService->typeauth = 'userpass';
                    $clientService->onmikrotik = 1;
                    $clientService->billing_type = 'recurring';
                    $clientService->online = 'off';
                    $clientService->plan_id = $this->requestData['plan_id'];
                    $clientService->router_id = $this->requestData['router'];
                    $clientService->status = 'ac';
                    $clientService->date_in = $this->transformDate($row['fecha_de_ingreso']);
                    $clientService->save();

                    $usedip->is_used_ip($row['ip_cliente'], $client->id, true);

                    $suspend = new SuspendClient();
                    $suspend->client_id = $client->id;
                    $suspend->router_id = $this->requestData['router'];
                    $suspend->service_id = $clientService->id;
                    $suspend->save();

                    if($this->requestData['invoice_pay_type'] == 'prepay') {
	                    $addClient = new AddClient();
	                    $addClient->generateInvoiceMultipleService($clientService, $client->id);
                    }

                }
            }
            DB::commit();
        }

        $data = [
            'id' => $this->requestData['router']
        ];

        event(new ImportClients($data));

    }


    /**
     * Transform a date value into a Carbon object.
     *
     * @return \Carbon\Carbon|null
     */
    public function transformDate($value, $format = 'Y-m-d')
    {
        try {
            return \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
        } catch (\ErrorException $e) {
            return \Carbon\Carbon::createFromFormat($format, $value);
        }
    }
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

    }

}
