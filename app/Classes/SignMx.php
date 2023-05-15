<?php

namespace App\Classes;
use App\models\InvoiceSettings;
use Facturapi\Facturapi;
use App\models\InvoiceSat;
use App\models\Sri;
use App\models\GlobalSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use DB;
use Mail;
/**
 * This class is for sign invoices for Mexico depende of model InvoiceSetting and Module from facturapi.io for work.
 */

class SignMx{
    public function __construct($apikey, $isLive, $provider){
        $this->apikey = $apikey;
        $this->isLive = $isLive;
        $this->provider = $provider;
    }

    public function sendToSign($invoiceData){
        $facturapi = new Facturapi( $this->apikey );
        $invoiceConfig = InvoiceSettings::first();
        $invoice = $facturapi->Invoices->create([
            "customer" => $invoiceData['customer'],
            "items" => $invoiceData['items'],
            "payment_form" => \Facturapi\PaymentForm::EFECTIVO, // Default Efectivo
            "folio_number" => $invoiceConfig->folio,
            "series" => $invoiceConfig->serie
        ]);

        // dd($this->apikey);
        // dd($invoice);
        if( isset($invoice->uuid) ){
            InvoiceSat::create([
                'bill_customers_id' => $invoiceData['bill_id'],
                'uuid' => $invoice->uuid,
                'status' => $invoice->status,
                'verification_url' => $invoice->verification_url,
                'json_response' => json_encode($invoice),
            ]);
            $sri = new Sri();
            $sri->id_factura = $invoiceData['bill_id'];
            $sri->id_error = '';
            $sri->mensaje = $invoice->uuid;
            $sri->informacionAdicional = $invoice->id;
            $sri->tipo = 3;
            $sri->claveAcceso = '';
            $sri->estado = 'signed';
            $sri->timestamps = false;
            $sri->save();
            $invoiceConfig->folio = $invoiceConfig->folio + 1;
            $invoiceConfig->save();

            $user = Auth::user();
            $query = "SELECT 
                clients.email
                , clients.name
            FROM sri 
            INNER JOIN bill_customers ON bill_customers.id = sri.id_factura
            INNER JOIN clients ON clients.id = bill_customers.client_id
            WHERE informacionAdicional = :id";
            $client = DB::select( DB::raw($query), array(
                'id' => $invoice->id,
            ))[0];

            $global = GlobalSetting::all()->first();
            $pdfString = $this->getInvoicePdf($invoice->id);
            $xmlString = $this->getInvoiceXml($invoice->id);
            Storage::disk('local')->put($invoice->uuid .'.pdf', $pdfString);
            Storage::disk('local')->put($invoice->uuid .'.xml', $xmlString);
            $pathFiles = [
                Storage::disk('local')->path($invoice->uuid .'.pdf'),
                Storage::disk('local')->path($invoice->uuid .'.xml'),
            ];
            Mail::send('emails.invoice_mex', ['fullname' => $client->name], function($message) use ($client, $global, $pathFiles) {
                $message->from($global->email, $global->company);
                $message->to($client->email, $client->name)->subject('Factura Electrónica');
                $message->bcc($global->email);
                $message->attach($pathFiles[0]);
                $message->attach($pathFiles[1]);
            });
            Storage::disk('local')->delete($invoice->uuid .'.pdf');
            Storage::disk('local')->delete($invoice->uuid .'.xml');
    
            return ['uuid' => $invoice->uuid, 'code'=> 200];
        }
        return ['code'=> 500];
    }

    public function getInvoicePdf($idDoc){
        $facturapi = new Facturapi( $this->apikey );
        return $facturapi->Invoices->download_pdf($idDoc);
    }

    public function getInvoiceXml($idDoc){
        $facturapi = new Facturapi( $this->apikey );
        return $facturapi->Invoices->download_xml($idDoc);
    }

    public function sendEmail($idDoc, $emails){
        $facturapi = new Facturapi($this->apikey);
        try{
            $res = $facturapi->Invoices->send_by_email( $idDoc, $emails);
        }catch(\Exception $e){
            return 500;
        }
        return 'Correo Enviado Correctamente';
    }
}