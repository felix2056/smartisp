<?php

namespace App\Http\Controllers;

use App\Classes\Reply;
use App\libraries\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use App\models\Conceptonota;
use App\models\Note;
use App\models\Invoices_dian;
use App\models\Dian_settings;
use App\models\GlobalSetting;
use App\models\Factel;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use DOMDocument;
use ZipArchive;
use Stenfrank\UBL21dian\XAdES\SignCreditNote;
use Stenfrank\UBL21dian\XAdES\SignDebitNote;
use Stenfrank\UBL21dian\Templates\SOAP\GetStatusZip;
use Stenfrank\UBL21dian\Templates\SOAP\SendTestSetAsync;
use Stenfrank\UBL21dian\Templates\SOAP\SendBillSync;
class NoteController extends Controller
{
	private $xml;
	private $cude;
	private $qr;
    public function create(Request $request, $idbill){
		$cmbconceptonota = Conceptonota::all();
		$number = DB::table('bill_customers')
        ->join('invoices_dian','invoices_dian.bill_customers_id', '=', 'bill_customers.id')
        ->where('bill_customers.id', '=', $idbill)
        ->where('invoices_dian.status_dian', '=', 'accepted')
		->select('invoices_dian.id','invoices_dian.number')->get()->first();
		if (!empty($number)) {
			return view('note.create', ['idinvoice' => $number->id,'number' => $number->number,'idbill' => $idbill,'cmbconceptonota'=>$cmbconceptonota]);
		}else{
			return view('note.create', ['idinvoice' => 0,'number' => 0,'idbill' => $idbill,'cmbconceptonota'=>$cmbconceptonota]);
		}
		
	}
	public function getItemdian(Request $request,$id_bill,$idtr){
		$bill_customer_item=DB::table('bill_customer_item')
		->where('bill_customer_id', '=', $id_bill)->get();
		
		return view('note.additem', ['bill_customer_item' => $bill_customer_item,'idtr'=>$idtr]);
	}
	public function getList(Request $request){
		return view('note.list', ['idinvoce' => 1]);
	}
	public function createnote(Request $request,$id){
        
        
        $global_settings = GlobalSetting::all();
		$global_settings = $global_settings->first();
		
		if ($global_settings->email == '')
            return Reply::error('Debe configurar los datos de envio de correo (Email SMTP principal)');
        if ($global_settings->password == '')
            return Reply::error('Debe configurar los datos de envio de correo (Email SMTP principal)');
        if ($global_settings->server == '')
            return Reply::error('Debe configurar los datos de envio de correo (Email SMTP principal)');
        if ($global_settings->port == '')
            return Reply::error('Debe configurar los datos de envio de correo (Email SMTP principal)');
		
		$settings_dian= Dian_settings::all();
		$settings_dian = $settings_dian->first();
		
        $invoices_dian = DB::table('invoices_dian')
        ->where('invoices_dian.id', '=', $request->input('invoices_dian_id'))
		->where('invoices_dian.status_dian', '=', 'accepted')
		->select('invoices_dian.id','invoices_dian.bill_customers_id','invoices_dian.resolutionnumber','invoices_dian.client_id','invoices_dian.date','invoices_dian.hour','invoices_dian.typeoperation_cod','invoices_dian.payment_cod','invoices_dian.prefix','invoices_dian.number','invoices_dian.cufe','invoices_dian.qr','invoices_dian.filename','invoices_dian.email','invoices_dian.phone','invoices_dian.cufe','invoices_dian.municipio_cod','invoices_dian.address','invoices_dian.bill_number','invoices_dian.nmoney','invoices_dian.subtotal','invoices_dian.totaltax','invoices_dian.total','invoices_dian.payment_date','invoices_dian.xero_id','invoices_dian.use_transactions','invoices_dian.status','invoices_dian.status_dian')->get()->first();
		
		$factel = Factel::all();
		$factel = $factel->first();
		
		$client = DB::table('clients')
		->where('clients.id', '=', $invoices_dian->client_id)->get()->first();
		
		if(!($request->input('input_total')>0)){
			return Reply::error('El tota debe ser mayor a 0');
		}
		if($request->input('note_note')==''){
			return Reply::error('Por favor ingrese la observación');
		}
		if($client->typedoc_cod=='31'  && count(explode("-",$client->dni))<=1){
			return Reply::error('El nit del cliente no tiene el digito de verificación');
		}
		if($invoices_dian->nmoney!='COP'){
			return Reply::error('Código de divisa inválido fv');
		}
		if($global_settings->nmoney!='COP'){
			return Reply::error('Código de divisa inválido setting');
		}
		if($client->email==''){
			return Reply::error('Se requiere el correo del cliente');
		}
		$numdetalle=count($request->input('description'));
		for ($i=0; $i < $numdetalle; $i++) {
			if($request->input('total')[$i]<=0){
				return Reply::error('El total del producto '.$request->input('description')[$i].' debe ser mayor a 0');
			}
		}
		$note_id=DB::table('note')->insertGetId([
            'invoices_dian_id' => $request->input('invoices_dian_id'),
			'date' => $request->input('note_date'),
			'hour' => date('h:i:s'),
			'prefix' => ($request->input('note_type')=='1')?$settings_dian->prefixnc:$settings_dian->prefixnd,
			'number' => $this->search_number($invoices_dian->resolutionnumber,$settings_dian->numberstart,$request->input('note_type')),
			'cude' => '',
			'filename' => '',
			'qr' => '',
			'conceptonote_cod' => $request->input('note_conceptonota'),
			'subtotal' => $request->input('subtotal'),
			'totaltax' => $request->input('input_totaltax'),
			'total' => $request->input('input_total'),
			'observaciones' => $request->input('note_note'),
			'typeoperation_cod' => $settings_dian->typeoperation_cod,
			'note_type' => $request->input('note_type'),
			'status_note' => 'rejected'
        ]);
        for ($i=0; $i < count($request->input('description')); $i++) {
			$subtotal=$request->input('quantity')[$i]*$request->input('price')[$i];
			$total_iva=$subtotal*($request->input('iva')[$i]/100);
            $detallenote = DB::table('detallenote')->insertGetId([
                'note_id' => $note_id,
                'description' => $request->input('description')[$i],
                'plan_id' => '1',
                'unit' => '94',
                'quantity' => $request->input('quantity')[$i],
                'price' => $request->input('price')[$i],
                'subtotal' => $subtotal,
                'iva' => $request->input('iva')[$i],
                'total_iva' => $total_iva,
                'total' => $total_iva+$subtotal,
            ]);
		}
		$note = Note::select('id', 'invoices_dian_id','date','hour','prefix','number','cude', 'filename', 'qr', 'conceptonote_cod', 'subtotal','totaltax', 'total', 'observaciones', 'typeoperation_cod', 'status_note','note_type')
		->where('id', $note_id)->first();
		$detallenote=DB::table('detallenote')->where('note_id', $note_id)->get();
		//Se crea el xml de la nota
		if ($request->input('note_type')=='1') {
			$this->xmlcreditnote($note,$detallenote,$invoices_dian,$settings_dian,$global_settings,$client);
		}else {
			$this->xmldebitnote($note,$detallenote,$invoices_dian,$settings_dian,$global_settings,$client);
		}
		$xml_string = $this->xml->saveXML();
        $pathCertificate = $factel->certificado_digital;
        $password =  $factel->pass_certificado;
		//Se firma el xml y se crea el zip
		if ($note->note_type=='1') {
			$enviados=($settings_dian->ncs+1);
		} else {
			$enviados=($settings_dian->nds+1);
		}
		$filename=$this->firmar_xml($pathCertificate,$password,$xml_string,$settings_dian->identificacion,$note->date,$settings_dian->softwarepin,$enviados,$note->note_type);
        $nombrezip=$this->fntnombrearchivo('z',$settings_dian->identificacion,"000",$note->date,($settings_dian->zips+1));
		$zip = new ZipArchive();
        $zip->open('js/lib_dian/comprobantes_colombia/'.$nombrezip.'.zip',ZipArchive::CREATE);
        $zip->addFile('js/lib_dian/comprobantes_colombia/'.$filename.'.xml',$filename.'.xml');
		$zip->close();
		//Actualizamos la nota
        DB::table('note')
        ->where('id',$note_id)
        ->update([
            'cude' => $this->cude,
            'qr' => $this->qr ,
            'filename' => $filename ,
		]);
		//Se envia la nota a la DIAN
		$respuesta="";
		$estado="NO AUTORIZADO";
        if($settings_dian->typeoperation_cod=='2'){
            $resp=$this->SendTestSetAsync($pathCertificate, $password,$settings_dian->testsetid,$nombrezip.'.zip',$nombrezip);
            ;
            if ($resp!='') {
                if ($resp->SendTestSetAsyncResponse->SendTestSetAsyncResult->ZipKey!='') {
                    $intentos=0;
                    $respuesta.=$resp->SendTestSetAsyncResponse->SendTestSetAsyncResult->ZipKey;
                    //esperamos 2 segundos para consultar el estado de la factura
                    sleep(2);
                    while ($intentos <= 5){
                        //consultamos si fue aceptado zip
                        $getStatusZip = new GetStatusZip($pathCertificate, $password);
                        $getStatusZip->trackId = $resp->SendTestSetAsyncResponse->SendTestSetAsyncResult->ZipKey;
                        $resp=$getStatusZip->signToSend()->getResponseToObject()->Envelope->Body->GetStatusZipResponse->GetStatusZipResult->DianResponse;
                        if (!empty($resp->StatusCode)){
                            if ($resp->IsValid=='true'){
								$respuesta='Aceptada';
                                if ($request->input('note_type')=='1') {
									$this->updateConsecutive($settings_dian->year,'ncs',$settings_dian->ncs,$settings_dian->zips);
								}else{
									$this->updateConsecutive($settings_dian->year,'nds',$settings_dian->nds,$settings_dian->zips);
								}
                            }else{
                                unlink('js/lib_dian/comprobantes_colombia/'.$nombrezip.'.zip');//Eliminar el zip
                                $respuesta=" ErrorMessage: ";
                                foreach ($resp->ErrorMessage as $key => $value) {
                                    $respuesta.=implode('\n',$value);
                                }
                            }
                            $intentos=10;
                        }else{
                            sleep(2);
                        }
                        $intentos++;
                    }
                }else{
                    $respuesta='Error en el ZipKey';
                    if($resp!=''){
                        if(isset($resp->SendTestSetAsyncResponse->SendTestSetAsyncResult->ErrorMessage)){
                            $respuesta.=' , ErrorMessage: ';
                            foreach ($resp->SendTestSetAsyncResponse->SendTestSetAsyncResult->ErrorMessage as $key => $string) {
                                $respuesta.=$string[$key];
                            }
                        }
                    }else{
						$respuesta.=' Error en el envio del SendTestSetAsync '.$nombrezip.'.zip';
                    }
                    unlink('js/lib_dian/comprobantes_colombia/'.$filename.'.xml');//Eliminar el xml
					unlink('js/lib_dian/comprobantes_colombia/'.$nombrezip.'.zip');//Eliminar el zip
                }
                
            }else{
                $respuesta.=' Error en el envio del SendTestSetAsync';
            }
        }else{
			$resp=$this->SendBillSync($pathCertificate,$password,$filename,$nombrezip.'.zip');
			if ($resp!='') {
				if ($resp->SendBillSyncResponse->SendBillSyncResult->IsValid!='false') {
					if ($resp->SendBillSyncResponse->SendBillSyncResult->IsValid!='false') {
						$intentos=0;
						$respuesta.=$resp->SendBillSyncResponse->SendBillSyncResult->XmlDocumentKey;
						//esperamos 2 segundos para consultar el estado de la nota
						sleep(2);
						while ($intentos <= 5){
							//consultamos si fue aceptado zip
							$getStatusZip = new GetStatusZip($pathCertificate, $password);
							$getStatusZip->To = 'https://vpfe.dian.gov.co/WcfDianCustomerServices.svc?wsdl';
							$getStatusZip->trackId = $resp->SendBillSyncResponse->SendBillSyncResult->XmlDocumentKey;
							$resp=$getStatusZip->signToSend()->getResponseToObject()->Envelope->Body->GetStatusZipResponse->GetStatusZipResult->DianResponse;
							if (!empty($resp->StatusCode)){
								if ($resp->IsValid=='true'){
									$respuesta='Aceptada';
									$this->updateConsecutive($settings_dian->year,'fes',$settings_dian->fes,$settings_dian->zips);
								}else{
									unlink('js/lib_dian/comprobantes_colombia/'.$nombrezip.'.zip');//Eliminar el zip
									$respuesta=" ErrorMessage: ";
									foreach ($resp->ErrorMessage as $key => $string) {
										$respuesta.=var_export($string,true);
									}
									if($resp->StatusCode=='89'){
										$respuesta.=$resp->StatusDescription;
									}
								}
								$intentos=10;
							}else{
								sleep(2);
							}
							$intentos++;
						}
					}else{
						$respuesta='Documento xml con errores en campos mandatorios';
						if($resp!=''){
							if(isset($resp->SendBillSyncResponse->SendBillSyncResult->ErrorMessage)){
								$respuesta.=' , ErrorMessage: ';
								foreach ($resp->SendBillSyncResponse->SendBillSyncResult->ErrorMessage as $key => $string) {
									$respuesta.=var_export($string,true);
								}
							}
						}else{
							$respuesta.=' Error en el envio del fntSendBillSync '.$nombrezip.'.zip';
						}
						//unlink('js/lib_dian/comprobantes_colombia/'.$filename.'.xml');//Eliminar el xml
						unlink('js/lib_dian/comprobantes_colombia/'.$nombrezip.'.zip');//Eliminar el zip
					}
				}else {
					$respuesta='Validación contiene errores en campos mandatorios';
					if(isset($resp->SendBillSyncResponse->SendBillSyncResult->ErrorMessage)){
						$respuesta.=' , ErrorMessage: ';
						foreach ($resp->SendBillSyncResponse->SendBillSyncResult->ErrorMessage as $key => $string) {
							$respuesta.=var_export($string,true);
						}
					}
					unlink('js/lib_dian/comprobantes_colombia/'.$nombrezip.'.zip');//Eliminar el zip
				}
            
            
            }else{
                $respuesta.=' Error en el envio del fntSendBillSync '.$nombrezip.'.zip';
			}
		}
		// Tipo 10 son las facturas, tipo 11 las notas crédito y tipo 12 las notas debitos
		//Se registra el estado del envio de la nota
		DB::table('sri')->insertGetId([
            'id_factura' => $id,
            'id_error' => 0,
            'mensaje' => $respuesta,
            'informacionAdicional' => '',
            'tipo' => 10+$request->input('note_type'),
            'claveAcceso' => '',
            'estado' => $estado,
		]);
		//
		if($respuesta!='Aceptada'){
			return Reply::error($respuesta);
		}
		//Actualizamos el estado de la nota
        DB::table('note')
        ->where('id',$note_id)
        ->update([
            'status_note' => "accepted",
		]);
		//Consulta los datos de la factura
        $fatura = DB::table('invoices_dian AS fv')
        ->join('clients AS c', 'c.id', '=', 'fv.client_id')
        ->join('typedoc', 'typedoc.cod', '=', 'c.typedoc_cod')
        ->join('typetaxpayer', 'typetaxpayer.cod', '=', 'c.typetaxpayer_cod')
        ->join('municipio', 'municipio.cod', '=', 'fv.municipio_cod')
        ->join('departamento', 'departamento.cod', '=', 'municipio.departamento_cod')
        ->where('fv.id', $note->invoices_dian_id)
        ->select('departamento.Description AS Departamento','municipio.Description AS Municipio','fv.cufe','fv.qr','fv.typeoperation_cod','fv.prefix','fv.number','fv.date', 'typedoc.Description AS typedocAdquiriente','c.dni AS identificationAdquiriente','c.name AS nameAdquiriente','typetaxpayer.Description AS typetaxpayerAdquiriente','c.address AS directionAdquiriente','c.email AS emailAdquiriente','c.phone AS phoneAdquiriente','fv.nmoney AS money','fv.subtotal','fv.totaltax AS iva','fv.total','fv.filename')->get()->first();
	   
		$settings_dian = DB::table('dian_settings')
        ->join('typedoc', 'typedoc.cod', '=', 'dian_settings.typedoc_cod')
        ->join('typetaxpayer', 'typetaxpayer.cod', '=', 'dian_settings.typetaxpayer_cod')
        ->join('municipio', 'municipio.cod', '=', 'dian_settings.municipio_cod')
        ->join('departamento', 'departamento.cod', '=', 'municipio.departamento_cod')
        ->select('departamento.Description AS Departamento','municipio.Description AS Municipio','typedoc.Description AS typedocEmisor','dian_settings.identificacion AS identificationEmisor','dian_settings.businessname AS nameEmisor','dian_settings.tradename','typetaxpayer.Description AS typetaxpayerEmisor','dian_settings.direction AS directionEmisor','dian_settings.resolutiondatestar','dian_settings.numberstart','dian_settings.numberend','dian_settings.resolutionnumber')->get()->first();
    
		$data=
		[
			'message' => 'Nota generada correctamente',
			'id'=>$note_id,
			'numberFactura'=>$fatura->prefix.$fatura->number,
			'dateFactura'=>$fatura->date,
			'note_type'=>$request->input('note_type'),
			''=>$respuesta,
			'cude'=> $this->cude,
			'qr'=> $this->qr,
			'typeoperation_cod'=> $note->typeoperation_cod,
			'prefix'=> $note->prefix,
			'number'=> $note->number,
			'date'=> $note->date,
			'typedocEmisor'=> $settings_dian->typedocEmisor,
			'typedocEmisor'=>$settings_dian->typedocEmisor,
            'identificationEmisor'=>$settings_dian->identificationEmisor,
            'nameEmisor'=>$settings_dian->nameEmisor,
            'tradename'=>$settings_dian->tradename,
            'typetaxpayerEmisor'=>$settings_dian->typetaxpayerEmisor,
            'directionEmisor'=>$settings_dian->Departamento.'/'.$settings_dian->Municipio.'/'.$settings_dian->directionEmisor,
			'emailEmisor'=>$global_settings->email,
            'phoneEmisor'=>'',
			'typedocAdquiriente'=> $fatura->typedocAdquiriente,
			'identificationAdquiriente'=> $fatura->identificationAdquiriente,
			'nameAdquiriente'=> $fatura->nameAdquiriente,
			'taxnameAdquiriente'=> '',$fatura->nameAdquiriente,
			'typetaxpayerAdquiriente'=> $fatura->typetaxpayerAdquiriente,
			'directionAdquiriente'=> $fatura->directionAdquiriente,
			'emailAdquiriente'=> $fatura->emailAdquiriente,
			'phoneAdquiriente'=> $fatura->phoneAdquiriente,
			'detalle'=> $detallenote,
			'money'=> $note->money,
			'subtotal'=> $note->subtotal,
			'iva'=> $note->totaltax,
			'total'=> $note->total,
			'resolution_number'=>$settings_dian->resolutionnumber,
            'resolution_desde'=>$settings_dian->numberstart,
            'resolution_hasta'=>$settings_dian->numberend,
            'resolution_date'=>$settings_dian->resolutiondatestar,
			'filename'=> $note->filename,
			'correo'=>'',
            'host_email'=>$global_settings->server,
            'email_origen'=>$global_settings->email,
            'passEmail'=>$global_settings->password,
            'port'=>$global_settings->port
		];
		return Reply::success('Invoice updated successfully.',$data);
    }
    public function xmlcreditnote($note,$detallenote,$invoices_dian,$settings_dian,$settings_global,$client){
        //Crea el XML
		$this->xml=new DOMDocument('1.0','UTF-8');
		$this->xml->xmlStandalone = false;
		$this->xml->formatOutput=true;
		//Crea el CreditNote
		$CreditNote=$this->fntCrearCreditNote();
		$this->fntCrearUBLExtensions_CreditNote($CreditNote,$settings_dian,$note);
		$this->fntCrearElemento($CreditNote,'cbc:UBLVersionID','UBL 2.1');
		$this->fntCrearElemento($CreditNote,'cbc:CustomizationID','05');
		$this->fntCrearElemento($CreditNote,'cbc:ProfileID','DIAN 2.1');
		$this->fntCrearElemento($CreditNote,'cbc:ProfileExecutionID',$invoices_dian->typeoperation_cod);
		$this->fntCrearElemento($CreditNote,'cbc:ID',$note->prefix.$note->number);
		$this->fntCrearElemento($CreditNote,'cbc:UUID',$note->cude,[
			["schemeID","2"],
			["schemeName","CUDE-SHA384"]
		]);
		$this->fntCrearElemento($CreditNote,'cbc:IssueDate',$note->date);
		$this->fntCrearElemento($CreditNote,'cbc:IssueTime',$note->hour.'-05:00');
		$this->fntCrearElemento($CreditNote,'cbc:CreditNoteTypeCode','91');
		$this->fntCrearElemento($CreditNote,'cbc:Note',$note->observaciones);
		$this->fntCrearElemento($CreditNote,'cbc:DocumentCurrencyCode',$invoices_dian->nmoney,[
			['listID','ISO 4217 Alpha'],
			['listAgencyID','6'],
			['listAgencyName','United Nations Economic Commission for Europe']
		]);
		//LineCountNumeric numero de lineas que tine el detalle note
		$numerodelineas='0';
		if (empty($detallenote)!=1) {
			$numerodelineas=count($detallenote);
		}
		$this->fntCrearElemento($CreditNote,'cbc:LineCountNumeric',$numerodelineas);
		$this->CrearDiscrepancyResponse($CreditNote,$invoices_dian->prefix,$invoices_dian->number,$note->conceptonote_cod,$note->observaciones);
		$this->fntCrearBillingReference($CreditNote,$invoices_dian->prefix,$invoices_dian->number,$invoices_dian->cufe,$invoices_dian->date);
		
		//EMPIEZA INFORMACION DEL EMISOR
        $AccountingSupplierParty=$this->xml->createElement("cac:AccountingSupplierParty");
        $AccountingSupplierParty=$CreditNote->appendChild($AccountingSupplierParty);
		$this->fntCrearElemento($AccountingSupplierParty,"cbc:AdditionalAccountID",$settings_dian->typetaxpayer_cod);
        $this->fntParty($AccountingSupplierParty,$settings_dian->typedoc_cod,$settings_dian->identificacion,$settings_dian->economicactivity_cod,$settings_dian->businessname,$settings_dian->municipio_cod,$settings_dian->direction,$settings_dian->typeresponsibility_cod,'',$settings_global->email,$settings_dian->prefix);
        //FINALIZA LA INFORMACIÓN DEL EMISOR
		//EMPIEZA LA INFORMACIÓN DEL CLIENTE
        $AccountingCustomerParty=$this->xml->createElement("cac:AccountingCustomerParty");
        $AccountingCustomerParty=$CreditNote->appendChild($AccountingCustomerParty);
        $this->fntCrearElemento($AccountingCustomerParty,"cbc:AdditionalAccountID",$client->typetaxpayer_cod);
        $this->fntParty($AccountingCustomerParty,$client->typedoc_cod,$client->dni,$client->economicactivity_cod,$client->name,$client->municipio_cod,$client->address,$client->typeresponsibility_cod,$client->phone,$client->email);
        //FINALIZA LA INFORMACIÓN DEL CLIENTE
        $this->fntCrearPaymentMeans($CreditNote,'1',$invoices_dian->id,$note->date);
		$this->fntCrearTaxTotal($CreditNote,$invoices_dian->nmoney,$detallenote);
		$this->LineExtensionAmount=0;
		$this->TaxExclusiveAmount=0;
		foreach ($detallenote as $d) {
			$this->LineExtensionAmount+=$d->quantity*$d->price;
			if ($d->iva>0)
			{
				$this->TaxExclusiveAmount=$d->quantity*$d->price;
			}
			
		}
		$this->fntCrearLegalMonetaryTotal_CreditNote($CreditNote,$invoices_dian->nmoney,$detallenote,$note->subtotal);
		$this->fntCrearNoteLines($CreditNote,$detallenote,$invoices_dian->nmoney,'1');
		$this->fntnombrearchivo(
		"nc",
		$client->dni,
		"000",
		$note->date,
		$settings_dian->ncs);
	}
	public function xmldebitnote($note,$detallenote,$invoices_dian,$settings_dian,$settings_global,$client){
        //Crea el XML
		$this->xml=new DOMDocument('1.0','UTF-8');
		$this->xml->xmlStandalone = false;
		$this->xml->formatOutput=true;
		//Crea el DebitNote
		$DebitNote=$this->fntCrearDebitNote();
		$this->fntCrearUBLExtensions_DebitNote($DebitNote,$settings_dian,$note);
		$this->fntCrearElemento($DebitNote,'cbc:UBLVersionID','UBL 2.1');
		$this->fntCrearElemento($DebitNote,'cbc:CustomizationID','05');
		$this->fntCrearElemento($DebitNote,'cbc:ProfileID','DIAN 2.1');
		$this->fntCrearElemento($DebitNote,'cbc:ProfileExecutionID',$invoices_dian->typeoperation_cod);
		$this->fntCrearElemento($DebitNote,'cbc:ID',$note->prefix.$note->number);
		$this->fntCrearElemento($DebitNote,'cbc:UUID',$note->cude,[
			["schemeID","2"],
			["schemeName","CUDE-SHA384"]
		]);
		$this->fntCrearElemento($DebitNote,'cbc:IssueDate',$note->date);
		$this->fntCrearElemento($DebitNote,'cbc:IssueTime',$note->hour.'-05:00');
		$this->fntCrearElemento($DebitNote,'cbc:Note',$note->observaciones);
		$this->fntCrearElemento($DebitNote,'cbc:DocumentCurrencyCode',$invoices_dian->nmoney,[
			['listID','ISO 4217 Alpha'],
			['listAgencyID','6'],
			['listAgencyName','United Nations Economic Commission for Europe']
		]);
		//LineCountNumeric numero de lineas que tine el detalle note
		$numerodelineas='0';
		if (empty($detallenote)!=1) {
			$numerodelineas=count($detallenote);
		}
		$this->fntCrearElemento($DebitNote,'cbc:LineCountNumeric',$numerodelineas);
		$this->CrearDiscrepancyResponse($DebitNote,$invoices_dian->prefix,$invoices_dian->number,$note->conceptonote_cod,$note->observaciones);
		$this->fntCrearBillingReference($DebitNote,$invoices_dian->prefix,$invoices_dian->number,$invoices_dian->cufe,$invoices_dian->date);
		
		//EMPIEZA INFORMACION DEL EMISOR
        $AccountingSupplierParty=$this->xml->createElement("cac:AccountingSupplierParty");
        $AccountingSupplierParty=$DebitNote->appendChild($AccountingSupplierParty);
		$this->fntCrearElemento($AccountingSupplierParty,"cbc:AdditionalAccountID",$settings_dian->typetaxpayer_cod);
        $this->fntParty($AccountingSupplierParty,$settings_dian->typedoc_cod,$settings_dian->identificacion,$settings_dian->economicactivity_cod,$settings_dian->businessname,$settings_dian->municipio_cod,$settings_dian->direction,$settings_dian->typeresponsibility_cod,'',$settings_global->email,$settings_dian->prefix);
        //FINALIZA LA INFORMACIÓN DEL EMISOR
		//EMPIEZA LA INFORMACIÓN DEL CLIENTE
        $AccountingCustomerParty=$this->xml->createElement("cac:AccountingCustomerParty");
        $AccountingCustomerParty=$DebitNote->appendChild($AccountingCustomerParty);
        $this->fntCrearElemento($AccountingCustomerParty,"cbc:AdditionalAccountID",$client->typetaxpayer_cod);
        $this->fntParty($AccountingCustomerParty,$client->typedoc_cod,$client->dni,$client->economicactivity_cod,$client->name,$client->municipio_cod,$client->address,$client->typeresponsibility_cod,$client->phone,$client->email);
        //FINALIZA LA INFORMACIÓN DEL CLIENTE
        $this->fntCrearPaymentMeans($DebitNote,'1',$invoices_dian->id,$note->date);
		$this->fntCrearTaxTotal($DebitNote,$invoices_dian->nmoney,$detallenote);
		$this->LineExtensionAmount=0;
		$this->TaxExclusiveAmount=0;
		foreach ($detallenote as $d) {
			$this->LineExtensionAmount+=$d->quantity*$d->price;
			if ($d->iva>0)
			{
				$this->TaxExclusiveAmount=$d->quantity*$d->price;
			}
			
		}
		$this->fntCrearRequestedMonetaryTotal_DebitNote($DebitNote,$invoices_dian->nmoney,$detallenote,$note->subtotal);
		$this->fntCrearNoteLines($DebitNote,$detallenote,$invoices_dian->nmoney,'2');
		$this->fntnombrearchivo(
			"nd",
			$client->dni,
			"000",
			$note->date,
			$settings_dian->nds);
    }
    private function fntCrearCreditNote(){
		//Crea el CreditNote
		$CreditNote=$this->xml->createElement("CreditNote");
		$CreditNote=$this->xml->appendChild($CreditNote);
		$CreditNote->setAttribute('xmlns','urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2');
		$CreditNote->setAttribute('xmlns:cac','urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
		$CreditNote->setAttribute('xmlns:cbc','urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
		$CreditNote->setAttribute('xmlns:ds',"http://www.w3.org/2000/09/xmldsig#");
		$CreditNote->setAttribute('xmlns:ext',"urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2");
		$CreditNote->setAttribute('xmlns:sts',"http://www.dian.gov.co/contratos/facturaelectronica/v1/Structures");
		$CreditNote->setAttribute('xmlns:xades',"http://uri.etsi.org/01903/v1.3.2#");
		$CreditNote->setAttribute('xmlns:xades141',"http://uri.etsi.org/01903/v1.4.1#");
		$CreditNote->setAttribute('xmlns:xsi',"http://www.w3.org/2001/XMLSchema-instance");
		$CreditNote->setAttribute('xsi:schemaLocation',"urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2     http://docs.oasis-open.org/ubl/os-UBL-2.1/xsd/maindoc/UBL-CreditNote-2.1.xsd");
		return $CreditNote;
	}
	private function fntCrearDebitNote(){
		//Crea el DebitNote
		$DebitNote=$this->xml->createElement("DebitNote");
		$DebitNote=$this->xml->appendChild($DebitNote);
		$DebitNote->setAttribute('xmlns','urn:oasis:names:specification:ubl:schema:xsd:DebitNote-2');
		$DebitNote->setAttribute('xmlns:cac','urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
		$DebitNote->setAttribute('xmlns:cbc','urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
		$DebitNote->setAttribute('xmlns:ds',"http://www.w3.org/2000/09/xmldsig#");
		$DebitNote->setAttribute('xmlns:ext',"urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2");
		$DebitNote->setAttribute('xmlns:sts',"http://www.dian.gov.co/contratos/facturaelectronica/v1/Structures");
		$DebitNote->setAttribute('xmlns:xades',"http://uri.etsi.org/01903/v1.3.2#");
		$DebitNote->setAttribute('xmlns:xades141',"http://uri.etsi.org/01903/v1.4.1#");
		$DebitNote->setAttribute('xmlns:xsi',"http://www.w3.org/2001/XMLSchema-instance");
		$DebitNote->setAttribute('xsi:schemaLocation',"urn:oasis:names:specification:ubl:schema:xsd:DebitNote-2     http://docs.oasis-open.org/ubl/os-UBL-2.1/xsd/maindoc/UBL-DebitNote-2.1.xsd");
		return $DebitNote;
	}
	function fntCrearUBLExtension1_CreditNote($padre,$settings_dian,$note){
		//Crear UBLExtension
		$UBLExtension=$this->xml->createElement("ext:UBLExtension");
		$UBLExtension=$padre->appendChild($UBLExtension);
		$ExtensionContent=$this->fntCrearElemento($UBLExtension,"ext:ExtensionContent");
		$DianExtensions=$this->fntCrearElemento($ExtensionContent,"sts:DianExtensions");
		$InvoiceSource=$this->fntCrearElemento($DianExtensions,"sts:InvoiceSource");
		$this->fntCrearElemento($InvoiceSource,"cbc:IdentificationCode","CO",[
			["listAgencyID","6"],
			["listAgencyName","United Nations Economic Commission for Europe"],
			["listSchemeURI","urn:oasis:names:specification:ubl:codelist:gc:CountryIdentificationCode-2.1"]
		]);
		$SoftwareProvider=$this->fntCrearElemento($DianExtensions,"sts:SoftwareProvider");
		$this->fntCrearElemento($SoftwareProvider,"sts:ProviderID","800197268",[
			["schemeAgencyID","195"],
			["schemeAgencyName","CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)"],
			["schemeID","4"],
			["schemeName","31"]
		]);
		$this->fntCrearElemento($SoftwareProvider,"sts:SoftwareID",$settings_dian->softwareid,[
			["schemeAgencyID","195"],
			["schemeAgencyName","CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)"]
		]);
		$this->fntCrearElemento($DianExtensions,"sts:SoftwareSecurityCode",$this->fntCalcularSoftwareSecurityCode($settings_dian,$note->number,$note->note_type),[
			["schemeAgencyID","195"],
			["schemeAgencyName","CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)"]
		]);

		$AuthorizationProvider=$this->fntCrearElemento($DianExtensions,"sts:AuthorizationProvider");
		$this->fntCrearElemento($AuthorizationProvider,"sts:AuthorizationProviderID","800197268",[
			["schemeAgencyID","195"],
			["schemeAgencyName","CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)"],
			["schemeID","4"],
			["schemeName","31"]
		]);
		$this->fntCrearElemento($DianExtensions,"sts:QRCode",$note->qr);
	}
	function fntCrearUBLExtension1_DebitNote($padre,$settings_dian,$note){
		//Crear UBLExtension
		$UBLExtension=$this->xml->createElement("ext:UBLExtension");
		$UBLExtension=$padre->appendChild($UBLExtension);
		$ExtensionContent=$this->fntCrearElemento($UBLExtension,"ext:ExtensionContent");
		$DianExtensions=$this->fntCrearElemento($ExtensionContent,"sts:DianExtensions");
		$InvoiceSource=$this->fntCrearElemento($DianExtensions,"sts:InvoiceSource");
		$this->fntCrearElemento($InvoiceSource,"cbc:IdentificationCode","CO",[
			["listAgencyID","6"],
			["listAgencyName","United Nations Economic Commission for Europe"],
			["listSchemeURI","urn:oasis:names:specification:ubl:codelist:gc:CountryIdentificationCode-2.1"]
		]);
		$SoftwareProvider=$this->fntCrearElemento($DianExtensions,"sts:SoftwareProvider");
		$this->fntCrearElemento($SoftwareProvider,"sts:ProviderID","800197268",[
			["schemeAgencyID","195"],
			["schemeAgencyName","CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)"],
			["schemeID","4"],
			["schemeName","31"]
		]);
		$this->fntCrearElemento($SoftwareProvider,"sts:SoftwareID",$settings_dian->softwareid,[
			["schemeAgencyID","195"],
			["schemeAgencyName","CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)"]
		]);
		$this->fntCrearElemento($DianExtensions,"sts:SoftwareSecurityCode",$this->fntCalcularSoftwareSecurityCode($settings_dian,$note->number,$note->note_type),[
			["schemeAgencyID","195"],
			["schemeAgencyName","CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)"]
		]);

		$AuthorizationProvider=$this->fntCrearElemento($DianExtensions,"sts:AuthorizationProvider");
		$this->fntCrearElemento($AuthorizationProvider,"sts:AuthorizationProviderID","800197268",[
			["schemeAgencyID","195"],
			["schemeAgencyName","CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)"],
			["schemeID","4"],
			["schemeName","31"]
		]);
		$this->fntCrearElemento($DianExtensions,"sts:QRCode",$note->qr);
	}
	function fntCrearUBLExtensions_CreditNote($padre,$settings_dian,$note){
		//Crear UBLExtensions
		$UBLExtensions=$this->xml->createElement("ext:UBLExtensions");
		$UBLExtensions=$padre->appendChild($UBLExtensions);
		$this->fntCrearUBLExtension1_CreditNote($UBLExtensions,$settings_dian,$note);

		$UBLExtension=$this->xml->createElement("ext:UBLExtension");
		$UBLExtensions->appendChild($UBLExtension);
		$ExtensionContent=$this->fntCrearElemento($UBLExtension,"ext:ExtensionContent");
		$UBLExtension->appendChild($ExtensionContent);
	}
	function fntCrearUBLExtensions_DebitNote($padre,$settings_dian,$note){
		//Crear UBLExtensions
		$UBLExtensions=$this->xml->createElement("ext:UBLExtensions");
		$UBLExtensions=$padre->appendChild($UBLExtensions);
		$this->fntCrearUBLExtension1_DebitNote($UBLExtensions,$settings_dian,$note);
		$UBLExtension=$this->xml->createElement("ext:UBLExtension");
		$UBLExtensions->appendChild($UBLExtension);
		$ExtensionContent=$this->fntCrearElemento($UBLExtension,"ext:ExtensionContent");
		$UBLExtension->appendChild($ExtensionContent);
	}
    function CrearDiscrepancyResponse($padre,$prefix,$number,$conceptonote_cod,$observaciones){
		$DiscrepancyResponse=$this->xml->createElement("cac:DiscrepancyResponse");
		$DiscrepancyResponse=$padre->appendChild($DiscrepancyResponse);
		$this->fntCrearElemento($DiscrepancyResponse,"cbc:ReferenceID",$prefix.$number);
		$this->fntCrearElemento($DiscrepancyResponse,"cbc:ResponseCode",$conceptonote_cod);
		$this->fntCrearElemento($DiscrepancyResponse,"cbc:Description",$observaciones);
    }
    function fntCrearBillingReference($padre,$prefix,$number,$cufe,$date){
		//Crear BillingReference
		$BillingReference=$this->xml->createElement("cac:BillingReference");
		$BillingReference=$padre->appendChild($BillingReference);
		$InvoiceDocumentReference=$this->xml->createElement("cac:InvoiceDocumentReference");
		$InvoiceDocumentReference=$BillingReference->appendChild($InvoiceDocumentReference);
		$this->fntCrearElemento($InvoiceDocumentReference,'cbc:ID',$prefix.$number);
		$this->fntCrearElemento($InvoiceDocumentReference,'cbc:UUID',$cufe,[
			["schemeName","CUFE-SHA384"]
		]);
		$this->fntCrearElemento($InvoiceDocumentReference,'cbc:IssueDate',$date);
    }
    function fntParty($padre,$typedoc_cod,$identificacion,$economicactivity_cod,$businessname,$municipio_cod,$Direccion,$typeresponsibility_cod,$telefono,$email,$prefix=''){
        $Party=$this->fntCrearElemento($padre,"cac:Party");
        if($typedoc_cod=='31')
        {
            $PartyIdentification=$this->fntCrearElemento($Party,"cac:PartyIdentification");
            $this->fntCrearElemento($PartyIdentification,"cbc:ID",explode("-", $identificacion)[0]);
        }
        if($economicactivity_cod!='0')
        {
            $PartyIdentification=$this->fntCrearElemento($Party,"cbc:IndustryClassificationCode",$economicactivity_cod);
        }
		$PartyName=$this->fntCrearElemento($Party,"cac:PartyName");
		$this->fntCrearElemento($PartyName,"cbc:Name",$businessname);
		$PhysicalLocation=$this->fntCrearElemento($Party,"cac:PhysicalLocation");
		$Address=$this->fntCrearElemento($PhysicalLocation,"cac:Address");
		$this->fntCrearAddress($Address,$municipio_cod,$Direccion);
  
		$PartyTaxScheme=$this->xml->createElement("cac:PartyTaxScheme");
		$PartyTaxScheme=$Party->appendChild($PartyTaxScheme);
        $this->fntCrearElemento($PartyTaxScheme,"cbc:RegistrationName",$businessname);
        if($typedoc_cod=='31'  && count(explode("-",$identificacion))>1){
            $this->fntCrearElemento($PartyTaxScheme,"cbc:CompanyID",explode("-", $identificacion)[0],[
                ['schemeAgencyID','195'],
                ['schemeAgencyName','CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)'],
                ['schemeID',explode("-", $identificacion)[1]],
                ['schemeName','31']
            ]);
        }else{
            $this->fntCrearElemento($PartyTaxScheme,"cbc:CompanyID", $identificacion,[
                ['schemeAgencyID','195'],
                ['schemeAgencyName','CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)'],
                ['schemeName',$typedoc_cod]
            ]);
        }
		$this->fntCrearElemento($PartyTaxScheme,"cbc:TaxLevelCode",$typeresponsibility_cod,[
			['listName','05']
		]);
		$RegistrationAddress=$this->fntCrearElemento($PartyTaxScheme,"cac:RegistrationAddress");

		$this->fntCrearAddress($RegistrationAddress,$municipio_cod,$Direccion);
		$TaxScheme=$this->fntCrearElemento($PartyTaxScheme,"cac:TaxScheme");
		$this->fntCrearElemento($TaxScheme,"cbc:ID",'01');
        $this->fntCrearElemento($TaxScheme,"cbc:Name",'IVA');
		$PartyLegalEntity=$this->fntCrearElemento($Party,"cac:PartyLegalEntity");
		$this->fntCrearElemento($PartyLegalEntity,"cbc:RegistrationName",$businessname);
		if ($typedoc_cod=='31' && count(explode("-",$identificacion))>1)
		{
			$this->fntCrearElemento($PartyLegalEntity,"cbc:CompanyID",explode("-",$identificacion)[0],[
				['schemeAgencyID','195'],
				['schemeAgencyName','CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)'],
				['schemeID',explode("-",$identificacion)[1]],
				['schemeName',$typedoc_cod]
			]);
		}
		else
		{
			$this->fntCrearElemento($PartyLegalEntity,"cbc:CompanyID",$identificacion,[
				['schemeAgencyID','195'],
				['schemeAgencyName','CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)'],
				['schemeName',$typedoc_cod]
			]);
        }
        if ($prefix!='')
		{
			$CorporateRegistrationScheme=$this->fntCrearElemento($PartyLegalEntity,"cac:CorporateRegistrationScheme");
			$this->fntCrearElemento($CorporateRegistrationScheme,"cbc:ID",$prefix);
		}
        $this->fntCrearContact($Party,$telefono,$email);
    }
	function fntCrearPaymentMeans($padre,$payment_cod,$id_invoces,$PaymentDueDate){
		$PaymentMeans=$this->xml->createElement("cac:PaymentMeans");
		$PaymentMeans=$padre->appendChild($PaymentMeans);
		$this->fntCrearElemento($PaymentMeans,"cbc:ID",$payment_cod);
        $this->fntCrearElemento($PaymentMeans,"cbc:PaymentMeansCode",'ZZZ');
		$this->fntCrearElemento($PaymentMeans,"cbc:PaymentDueDate",$PaymentDueDate);
		$this->fntCrearElemento($PaymentMeans,"cbc:PaymentID",$id_invoces);
    }
    function fntCrearTaxTotal($padre,$moneda,$detallenote){
		$TaxTotal=$this->xml->createElement("cac:TaxTotal");
		$TaxTotal=$padre->appendChild($TaxTotal);
        $porcentajesiva=[];
        $totaliva=0;
		foreach ($detallenote as $d){
			if($d->iva>0){
				if (isset($porcentajesiva[$d->iva])){
					$porcentajesiva[$d->iva]+=($d->quantity*$d->price);
				}
				else{
					$porcentajesiva[$d->iva]=($d->quantity*$d->price);
                }
                $totaliva+=$d->total_iva;
			}
        }
        $this->fntCrearElemento($TaxTotal,"cbc:TaxAmount",$this->fntFormatonumero($totaliva),[
			["currencyID",$moneda]
		]);
		foreach ($porcentajesiva as $key => $Subtotal){
			$TaxSubtotal=$this->fntCrearElemento($TaxTotal,"cac:TaxSubtotal",'');
			$this->fntCrearElemento($TaxSubtotal,"cbc:TaxableAmount",$this->fntFormatonumero($Subtotal),[
				["currencyID",$moneda]
			]);
			$this->fntCrearElemento($TaxSubtotal,"cbc:TaxAmount",$this->fntFormatonumero($Subtotal*$key/100),[
				["currencyID",$moneda]
			]);
			$TaxCategory=$this->fntCrearElemento($TaxSubtotal,"cac:TaxCategory",'');
			$TaxCategory=$TaxSubtotal->appendChild($TaxCategory);
			$this->fntCrearElemento($TaxCategory,"cbc:Percent",$this->fntFormatonumero($key));
			$TaxScheme=$this->fntCrearElemento($TaxCategory,"cac:TaxScheme");
			$TaxScheme=$TaxCategory->appendChild($TaxScheme);
			$this->fntCrearElemento($TaxScheme,"cbc:ID",'01');
			$this->fntCrearElemento($TaxScheme,"cbc:Name",'IVA');
		}
    }
	//TOTALES
    function fntCrearLegalMonetaryTotal_CreditNote($padre,$moneda,$detallenote,$subtotal){
		$LegalMonetaryTotal=$this->xml->createElement("cac:LegalMonetaryTotal");
		$LegalMonetaryTotal=$padre->appendChild($LegalMonetaryTotal);

		$this->fntCrearElemento($LegalMonetaryTotal,"cbc:LineExtensionAmount",$subtotal,[
			["currencyID",$moneda]
		]);
        $baseIVA=0;
        $totalconiva=0;
		foreach ($detallenote as $d) {
			if ($d->iva>0){
				$baseIVA+=($d->quantity*$d->price);
            }
            $totalconiva+=($d->quantity*$d->price)*(($d->iva/100)+1);
		}
		$this->fntCrearElemento($LegalMonetaryTotal,"cbc:TaxExclusiveAmount",$this->redondear_dos_decimal($baseIVA),[
			["currencyID",$moneda]
		]);
		$this->fntCrearElemento($LegalMonetaryTotal,"cbc:TaxInclusiveAmount",$this->redondear_dos_decimal($totalconiva),[
			["currencyID",$moneda]
		]);
		$this->fntCrearElemento($LegalMonetaryTotal,"cbc:PayableAmount",$this->redondear_dos_decimal($totalconiva),[
			["currencyID",$moneda]
		]);
	}
	function fntCrearRequestedMonetaryTotal_DebitNote($padre,$moneda,$detallenote,$subtotal){
		$RequestedMonetaryTotal=$this->xml->createElement("cac:RequestedMonetaryTotal");
		$RequestedMonetaryTotal=$padre->appendChild($RequestedMonetaryTotal);

		$this->fntCrearElemento($RequestedMonetaryTotal,"cbc:LineExtensionAmount",$subtotal,[
			["currencyID",$moneda]
		]);
        $baseIVA=0;
        $totalconiva=0;
		foreach ($detallenote as $d) {
			if ($d->iva>0){
				$baseIVA+=($d->quantity*$d->price);
            }
            $totalconiva+=($d->quantity*$d->price)*(($d->iva/100)+1);
		}
		$this->fntCrearElemento($RequestedMonetaryTotal,"cbc:TaxExclusiveAmount",$this->redondear_dos_decimal($baseIVA),[
			["currencyID",$moneda]
		]);
		$this->fntCrearElemento($RequestedMonetaryTotal,"cbc:TaxInclusiveAmount",$this->redondear_dos_decimal($totalconiva),[
			["currencyID",$moneda]
		]);
		$this->fntCrearElemento($RequestedMonetaryTotal,"cbc:PayableAmount",$this->redondear_dos_decimal($totalconiva),[
			["currencyID",$moneda]
		]);
    }
    function fntCrearNoteLines($padre,$detallenote,$moneda,$note_type){
	    //Muestra el detalle de la nota crédito
		$conta=0;
		$Subtotal=0;
		$total_iva=0;
	    foreach ($detallenote as $dnc)
	    {
			$conta++;
			$Subtotal=$Subtotal+($dnc->price*$dnc->quantity);
			$total_iva=$total_iva+$dnc->total_iva;
			$this->fntCrearNoteLine($padre,$conta,$dnc->quantity,$dnc->unit,$dnc->price,$dnc->iva,$dnc->description,$dnc->plan_id,$moneda,$note_type);
		}
	}
	function fntCrearNoteLine($padre,$ID,$Cantidad,$cod_unidadmedida,$Precio,$Porcentajeiva,$Nombreservicio,$Codigoservicio,$moneda,$note_type){
		$Iva=($Cantidad*$Precio)*($Porcentajeiva/100);
		if ($note_type=='1') {
			$NoteLine=$this->xml->createElement("cac:CreditNoteLine");
		} else {
			$NoteLine=$this->xml->createElement("cac:DebitNoteLine");
		}
		$NoteLine=$padre->appendChild($NoteLine);
		$this->fntCrearElemento($NoteLine,"cbc:ID",$ID);
		if ($note_type=='1') {
			$this->fntCrearElemento($NoteLine,"cbc:CreditedQuantity",$Cantidad,[
				['unitCode',$cod_unidadmedida]
			]);
		} else {
			$this->fntCrearElemento($NoteLine,"cbc:DebitedQuantity",$Cantidad,[
				['unitCode',$cod_unidadmedida]
			]);
		}
		$Total=($Cantidad*$Precio);
		$this->fntCrearElemento($NoteLine,"cbc:LineExtensionAmount",$this->fntFormatonumero($Total),[
			['currencyID',$moneda]
		]);
		$TaxTotal=$this->fntCrearElemento($NoteLine,"cac:TaxTotal");
		$this->fntCrearElemento($TaxTotal,"cbc:TaxAmount",$this->fntFormatonumero($Iva),[
			['currencyID',$moneda]
		]);
		$TaxSubtotal=$this->fntCrearElemento($TaxTotal,"cac:TaxSubtotal");
		if ($Iva>0)
		{
			$this->fntCrearElemento($TaxSubtotal,"cbc:TaxableAmount",$this->redondear_dos_decimal($Precio),[
				['currencyID',$moneda]
			]);
		}
		else
		{
			$this->fntCrearElemento($TaxSubtotal,"cbc:TaxableAmount",'0.00',[
				['currencyID',$moneda]
			]);
		}
		$this->fntCrearElemento($TaxSubtotal,"cbc:TaxAmount",$this->fntFormatonumero($Iva),[
			['currencyID',$moneda]
		]);
		$TaxCategory=$this->fntCrearElemento($TaxSubtotal,"cac:TaxCategory");
		$this->fntCrearElemento($TaxCategory,"cbc:Percent",$this->fntFormatonumero($Porcentajeiva));
		$TaxScheme=$this->fntCrearElemento($TaxCategory,"cac:TaxScheme");
		$this->fntCrearElemento($TaxScheme,"cbc:ID",'01');
		$this->fntCrearElemento($TaxScheme,"cbc:Name",'IVA');
		$Item=$this->fntCrearElemento($NoteLine,"cac:Item");
		$this->fntCrearElemento($Item,"cbc:Description",$Nombreservicio);
		$AdditionalItemIdentification=$this->fntCrearElemento($Item,"cac:AdditionalItemIdentification");
		$this->fntCrearElemento($AdditionalItemIdentification,"cbc:ID",$Codigoservicio,[
			['schemeID','999']
		]);
		$Price= $this->fntCrearElemento($NoteLine,"cac:Price");
		$this->fntCrearElemento($Price,"cbc:PriceAmount",$this->redondear_dos_decimal($Precio),[
			['currencyID',$moneda]
		]);
		$this->fntCrearElemento($Price,"cbc:BaseQuantity",$Cantidad.'.000000',[
			['unitCode',$cod_unidadmedida]
		]);
    }
    function fntnombrearchivo($prefijo,$nit,$dian,$fecha,$enviados){
        if(count(explode("-", $nit))>1)
        {
            $nit=explode("-", $nit);
            $nit=$nit[0];
        }
		$nit=$this->fntcompletarceros($nit,10);
		$enviados=$this->fntcompletarceros($enviados,8);
		$año=explode("-", $fecha)[0];
		$año=substr($año, -2);
		$this->nombrearchivo=$prefijo.$nit.$dian.$año.$enviados;
		return $this->nombrearchivo;
	}
	function fntCalcularSoftwareSecurityCode($settings_dian,$number,$note_type){
		//Id Software + Pin + Número
		if($note_type=='1'){
			return hash('sha384',($settings_dian->softwareid.$settings_dian->softwarepin.$settings_dian->prefixnc.$number));
		}else{
			return hash('sha384',($settings_dian->softwareid.$settings_dian->softwarepin.$settings_dian->prefixnd.$number));
		}
	}
	function fntCrearAddress($padre,$cod,$Direccion){
        $municipio = DB::table('municipio')
        ->select('municipio.Description AS Municipio', 'municipio.departamento_cod', 'departamento.Description AS Departamento', 'departamento.pais_cod', 'pais.Description AS Country')
        ->join('departamento', 'departamento.cod', '=', 'municipio.departamento_cod')
        ->join('pais', 'pais.cod', '=', 'departamento.pais_cod')
        ->where('municipio.cod', '=', $cod)
        ->first();
        if(!empty($municipio))
        {
            $this->fntCrearElemento($padre,"cbc:ID",$cod);
            $this->fntCrearElemento($padre,"cbc:CityName",$municipio->Municipio);
            $this->fntCrearElemento($padre,"cbc:CountrySubentity",$municipio->Departamento);
            $this->fntCrearElemento($padre,"cbc:CountrySubentityCode",$municipio->departamento_cod);
            $padreLine=$this->fntCrearElemento($padre,"cac:AddressLine");
            $this->fntCrearElemento($padreLine,"cbc:Line",$Direccion);
            $Country=$this->fntCrearElemento($padre,"cac:Country");
            $this->fntCrearElemento($Country,"cbc:IdentificationCode",$municipio->pais_cod);
            $this->fntCrearElemento($Country,"cbc:Name",$municipio->Country,[
                ['languageID','es']
            ]);
        }
	}
	function fntCrearContact($padre,$Telefono,$Correo){
		if (!($Telefono==''&&$Correo=='')) {
			$Contact=$this->fntCrearElemento($padre,"cac:Contact");
			if ($Telefono!='') {
				$this->fntCrearElemento($Contact,"cbc:Telephone",$Telefono);
			}
			if ($Correo!='') {
				$this->fntCrearElemento($Contact,"cbc:ElectronicMail",$Correo);
			}
		}
	}
	function redondear_dos_decimal($valor) {
		$valor=round($valor * 100) / 100;
		$pos = strpos($valor,".");
		if ($pos!==false)
		{
				$array=explode('.', $valor);
				$valor.=(strlen($array[1])>1)?'':'0';
		}
		else
		{
				$valor.='.00';
		}
		return $valor;
	}
	function fntcompletarceros($texto,$maxlongitud){
		$numcaracteres=$maxlongitud-strlen($texto);
		$auxtexto="";
		for ($i=0; $i < $numcaracteres; $i++)
		{
			$auxtexto.="0";
		}
		return $texto=$auxtexto.$texto;
    }
	public function fntFormatonumero($Numero,$decimal=2){
		return number_format($Numero, $decimal, '.', '');
    }
	function fntCrearElemento($padre,$elemento,$texto='',$atributos=[]){
		$elemento=$this->xml->createElement($elemento);
		$elemento=$padre->appendChild($elemento);
		if (!empty($atributos)){
			foreach ($atributos as $attr) {
				$elemento->setAttribute($attr[0],$attr[1]);
			}
		}
		if ($texto!=''){
			$textID=$this->xml->createTextNode($texto);
			$textID=$elemento->appendChild($textID);
		}
		return $elemento;
	}
	function search_number($resolutionnumber,$numberstart,$note_type){
        $number=DB::select('select MAX(n.number) AS number from note AS n INNER JOIN invoices_dian i where i.resolutionnumber=? AND n.note_type=?',[$resolutionnumber,$note_type]);
        if (empty($number)) {
            $number=(floatval($numberstart)+1);
        }elseif($numberstart>$number[0]->number+1){
            $number=(floatval($numberstart)+1);
        }
        else{
            $number=floatval($number[0]->number)+1;
        }
        return $number;
	}
	//Consecutivo de los documentos electronico (nc,nd)
    function updateConsecutive($year,$doc,$col_value,$zips){
       if($year!=intval(date('Y'))){
            Dian_settings::where('id','=', 1)->update(['year' => date('Y'),'fes' => 0,'ncs' => 0,'nds' => 0,'zips' => 0]);
            $col_value=0;
            $zips=0;
       }
       Dian_settings::where('id','=', 1)->update([$doc => $col_value+1,'zips' => $zips+1]);
    }
	function firmar_xml($pathCertificate,$passwors,$xmlString,$identificacion,$fecha,$pin,$enviados,$note_type){
		if ($note_type=='1') {
			$tipo='nc';
		} else {
			$tipo='nd';
		}
        $domDocument = new DOMDocument();
		$domDocument->loadXML($xmlString);
		if ($note_type=='1') {
			$signInvoice = new SignCreditNote($pathCertificate, $passwors, $xmlString,SignCreditNote::ALGO_SHA256,null,$pin);
		} else {
			$signInvoice = new SignDebitNote($pathCertificate, $passwors, $xmlString,SignDebitNote::ALGO_SHA256,null,$pin);
		}
        $nombrearchivo=$this->fntnombrearchivo($tipo,$identificacion,"000",$fecha,$enviados);
        //se obtiene el cufe
        $this->cude=$signInvoice->getCude();
        $this->qr=$signInvoice->getQR();
        //se guarda en el nombre del archivo y el obejto nstanciado
        file_put_contents('js/lib_dian/comprobantes_colombia/'.$nombrearchivo.'.xml', $signInvoice->xml);
        return $nombrearchivo;
	}
	function SendTestSetAsync($pathCertificate, $password,$testSetId,$filename,$nombrezip){
		$resp='';
		if(is_readable('js/lib_dian/comprobantes_colombia/'.$filename)) {
			$fileContent = file_get_contents('js/lib_dian/comprobantes_colombia/'.$filename);
			$zip = base64_encode($fileContent);
			$sendTestSetAsync = new SendTestSetAsync($pathCertificate, $password);
			$sendTestSetAsync->fileName = $nombrezip;
			$sendTestSetAsync->contentFile = $zip;
			$sendTestSetAsync->testSetId = $testSetId;
            $resp=$sendTestSetAsync->signToSend()->getResponseToObject()->Envelope->Body;
		}
		return $resp;
	}
	function SendBillSync($pathCertificate, $password,$filename,$nombrezip){
		$resp='';
		if(is_readable('js/lib_dian/comprobantes_colombia/'.$nombrezip)) {
			$fileContent = file_get_contents('js/lib_dian/comprobantes_colombia/'.$nombrezip);
			$zip = base64_encode($fileContent);
			$SendBillSync = new SendBillSync($pathCertificate, $password);
			$SendBillSync->To = 'https://vpfe.dian.gov.co/WcfDianCustomerServices.svc?wsdl';
			$SendBillSync->fileName = $filename;
			$SendBillSync->contentFile = $zip;
            $resp=$SendBillSync->signToSend()->getResponseToObject()->Envelope->Body;
		}
		return $resp;
	}

	public function getIndex() {

        $id = Auth::user()->id;
        $level = Auth::user()->level;
        $perm = DB::table('permissions')->where('user_id', '=', $id)->get();
        $access = $perm[0]->access_clients;
        //control permissions only access super administrator (sa)
        if ($level == 'ad' || $access == true) {
            $global = GlobalSetting::all()->first();

            $GoogleMaps = Helpers::get_api_options('googlemaps');

            if (count($GoogleMaps) > 0) {
                $key = $GoogleMaps['k'];
            } else {
                $key = 0;
            }
			$invoices_dian = Invoices_dian::select('id', 'status_dian')->get();
			$accepted=0;
			$rejected=0;
			for ($i=0; $i < $invoices_dian->count(); $i++) {
				if ($invoices_dian[$i]->status_dian=='accepted') {
					$accepted++;
				} else {
					$rejected++;
				}
			}
			$data['factura'] = [
                'accepted' => $accepted,
                'rejected' => $rejected,
                'quantity' => $invoices_dian->count()
			];

			$notecredit = Note::select('id', 'status_note')->where('note_type','=','1')->get();
			$accepted=0;
			$rejected=0;
			for ($i=0; $i < $notecredit->count(); $i++) {
				if ($notecredit[$i]->status_note=='accepted') {
					$accepted++;
				} else {
					$rejected++;
				}
			}
			$data['notecredit'] = [
				'accepted' => $accepted,
                'rejected' => $rejected,
                'quantity' => $notecredit->count()
			];
            $notedebit = Note::select('id', 'status_note')->where('note_type','=','2')->get();
			$accepted=0;
			$rejected=0;
			for ($i=0; $i < $notedebit->count(); $i++) {
				if ($notedebit[$i]->status_note=='accepted') {
					$accepted++;
				} else {
					$rejected++;
				}
			}
			$data['notedebit'] = [
				'accepted' => $accepted,
                'rejected' => $rejected,
                'quantity' => $notedebit->count()
            ];

            $permissions = array("clients" => $perm[0]->access_clients, "plans" => $perm[0]->access_plans, "routers" => $perm[0]->access_routers,
                "users" => $perm[0]->access_users, "system" => $perm[0]->access_system, "bill" => $perm[0]->access_pays,
                "template" => $perm[0]->access_templates, "ticket" => $perm[0]->access_tickets, "sms" => $perm[0]->access_sms,
                "reports" => $perm[0]->access_reports,
                "v" => $global->version, "st" => $global->status, "map" => $key,
                "lv" => $global->license, "company" => $global->company, 'data' => $data,
                'permissions' => $perm->first(),
                // menu options
            );



            if (Auth::user()->level == 'ad')
                @setcookie("hcmd", 'kR2RsakY98pHL', time() + 7200, "/", "", 0, true);

            $contents = View::make('note.index', $permissions);
            $response = Response::make($contents, 200);
            $response->header('Expires', 'Tue, 1 Jan 1980 00:00:00 GMT');
            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            $response->header('Pragma', 'no-cache');
            return $response;
        } else
        return Redirect::to('admin');
	}
	public function postLists($type) {
		if ($type==3) {
			$invoice=Invoices_dian::select();
			return DataTables::of($invoice)
			->addColumn('action', function ($row) {
				return '';
			})
			->addColumn('state', function ($row) {
				return 'Ready..';
			})
			->rawColumns(['action'])
			->make(true);
		} else {
			$note = DB::table('note')
			->join('invoices_dian','invoices_dian.id', '=', 'note.invoices_dian_id')
			->where('note.note_type', '=', $type)
			->select('note.id','note.date','note.hour','invoices_dian.number AS numberfactura','note.prefix','note.number','note.total','note.status_note','note.updated_at')->get();
			return DataTables::of($note)
			->addColumn('action', function ($row) {
				return '';
			})
			->addColumn('state', function ($row) {
				return 'Ready..';
			})
			->rawColumns(['action'])
			->make(true);
		}
	}
}
