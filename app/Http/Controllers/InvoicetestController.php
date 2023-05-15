<?php

namespace App\Http\Controllers;

use App\Classes\Reply;
use App\models\Dian_settings;
use App\models\Factel;
use Illuminate\Support\Facades\DB;
use DOMDocument;
use ZipArchive;
use Stenfrank\UBL21dian\XAdES\SignCreditNote;
use Stenfrank\UBL21dian\XAdES\SignDebitNote;
use Stenfrank\UBL21dian\XAdES\SignInvoice;
use Stenfrank\UBL21dian\Templates\SOAP\GetStatusZip;
use Stenfrank\UBL21dian\Templates\SOAP\SendTestSetAsync;
use Stenfrank\UBL21dian\Templates\SOAP\SendBillSync;
class InvoicetestController extends Controller
{
	private $xml;
	private $cude;
    private $qr;
	//type 1 nota credito, 2 nota debito, 3 factura
	function sendtestxml_dian(){
		$resp='El test de prueba no fue aceptado';
		$numfv=1;
		$numnc=1;
		$numnd=1;
		for ($i=0; $i < 30; $i++) { 
			$respuesta=$this->sendxml($numfv,3,1);
			if($respuesta!='Aceptada'){
				if($i<10){
					if($respuesta!='Aceptada'){
						$respuesta=$this->sendxml($numnc,1,990000000+$numfv,$this->cude);
						if($respuesta!='Aceptada'){
							$respuesta=$this->sendxml($numnd,2,990000000+$numfv,$this->cude);
							if($respuesta!='Aceptada'){
								$numnd++;
							}
						}
						$numnc++;
					}
				}
				$numfv++;
			}
		}
		if ($numfv>=8&&$numnc>=1&&$numnd>=1) {
			$resp='Pruebas ejecutadas con exito';
			DB::table('dian_settings')->insertGetId([ 
				'typeoperation_cod' => 1
			]);
		}else{
			$resp='Error al ejecutar las Pruebas';
		}
		if ($resp=='Pruebas ejecutadas con exito') {
			return Reply::success($resp);
		} else {
			return Reply::error($resp);
		}
	}
	public function sendxml($enviados,$type,$numberfv='',$cufefv=''){
		$number=990000000+$enviados;
		$settings_dian= Dian_settings::all();
        $settings_dian = $settings_dian->first();
        $factel = Factel::all();
		$factel = $factel->first();
        //Se crea el xml
        if($type==1){
            $this->xmlcreditnote($settings_dian,$number,$numberfv,$cufefv);
        }elseif($type==2){
            $this->xmldebitnote($settings_dian,$number,$numberfv,$cufefv);
        }else{
            $this->xmlinvoice($settings_dian,$number);
        }
		$xml_string = $this->xml->saveXML();
        $pathCertificate = $factel->certificado_digital;
		$password =  $factel->pass_certificado;
		$respuesta="";
		//Se firma el xml y se crea el zip
		$filename=$this->firmar_xml($pathCertificate,$password,$xml_string,$settings_dian->identificacion,date('Y-m-d'),$settings_dian->softwarepin,$settings_dian->tecnicalkey,$enviados,$type);
        $nombrezip=$this->fntnombrearchivo('z',$settings_dian->identificacion,"000",date('Y-m-d'),($settings_dian->zips+1));
		$zip = new ZipArchive();
        $zip->open('js/lib_dian/comprobantes_colombia/'.$nombrezip.'.zip',ZipArchive::CREATE);
        $zip->addFile('js/lib_dian/comprobantes_colombia/'.$filename.'.xml',$filename.'.xml');
		$zip->close();
		//Se envia a la DIAN
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
                        }else{
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
            }
            
        }else{
            $respuesta.=' Error en el envio del SendTestSetAsync';
        }
        unlink('js/lib_dian/comprobantes_colombia/'.$filename.'.xml');//Eliminar el xml
		unlink('js/lib_dian/comprobantes_colombia/'.$nombrezip.'.zip');//Eliminar el zip
		// Tipo 10 son las facturas, tipo 11 las notas crédito y tipo 12 las notas debitos
		//Se registra el estado del envio de la nota
		/*DB::table('sri')->insertGetId([ 
            'id_factura' => 1,
            'id_error' => 0,
            'mensaje' => $respuesta,
            'informacionAdicional' => '',
            'tipo' => 14,
            'claveAcceso' => '',
            'estado' => $respuesta,
		]);*/
		//
		return $respuesta;
	}
	public function xmlinvoice($settings_dian,$number) {
        $respuesta='Rechazada';
        $factel = Factel::all();
        $factel = $factel->first();
        
        $settings_dian = Dian_settings::all();
        $settings_dian = $settings_dian->first();
        //EMPIEZA DOCUMENTO SE CREA LA RAIZ FACTURA
        $this->xml=new \DOMDocument('1.0','UTF-8');//Se crea el docuemnto
		$this->xml->xmlStandalone = false;
		$this->xml->formatOutput=true;
        $Invoice=$this->xml->createElement("Invoice");
		$Invoice=$this->xml->appendChild($Invoice);
		$Invoice->setAttribute('xmlns','urn:oasis:names:specification:ubl:schema:xsd:Invoice-2');
		$Invoice->setAttribute('xmlns:cac','urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
		$Invoice->setAttribute('xmlns:cbc','urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
		$Invoice->setAttribute('xmlns:ds',"http://www.w3.org/2000/09/xmldsig#");
		$Invoice->setAttribute('xmlns:ext',"urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2");
		$Invoice->setAttribute('xmlns:sts',"http://www.dian.gov.co/contratos/facturaelectronica/v1/Structures"); 
		$Invoice->setAttribute('xmlns:xades',"http://uri.etsi.org/01903/v1.3.2#");
		$Invoice->setAttribute('xmlns:xades141',"http://uri.etsi.org/01903/v1.4.1#");
		$Invoice->setAttribute('xmlns:xsi',"http://www.w3.org/2001/XMLSchema-instance");
		$Invoice->setAttribute('xsi:schemaLocation',"urn:oasis:names:specification:ubl:schema:xsd:Invoice-2     http://docs.oasis-open.org/ubl/os-UBL-2.1/xsd/maindoc/UBL-Invoice-2.1.xsd");
        $this->fntCrearUBLExtensions($Invoice,$settings_dian,$number);
        $this->fntCrearElemento($Invoice,'cbc:UBLVersionID','UBL 2.1');
		$this->fntCrearElemento($Invoice,'cbc:CustomizationID','10');
        $this->fntCrearElemento($Invoice,'cbc:ProfileID','DIAN 2.1');
        $this->fntCrearElemento($Invoice,'cbc:ProfileExecutionID',$settings_dian->typeoperation_cod);
		$this->fntCrearElemento($Invoice,'cbc:ID',$settings_dian->prefix.$number);
		$this->fntCrearElemento($Invoice,'cbc:UUID','',[
			["schemeID","2"],
			["schemeName","CUFE-SHA384"]
		]);
		$this->fntCrearElemento($Invoice,'cbc:IssueDate',date('Y-m-d'));
		$this->fntCrearElemento($Invoice,'cbc:IssueTime',date('H:i:s').'-05:00');
		$this->fntCrearElemento($Invoice,'cbc:InvoiceTypeCode','01');
		$this->fntCrearElemento($Invoice,'cbc:DocumentCurrencyCode','COP',[
			['listAgencyID','6'],
			['listAgencyName','United Nations Economic Commission for Europe'],
			['listID','ISO 4217 Alpha']
        ]);
        $this->fntCrearElemento($Invoice,'cbc:LineCountNumeric','1');
        //EMPIEZA INFORMACION DEL EMISOR    
        $AccountingSupplierParty=$this->xml->createElement("cac:AccountingSupplierParty");
        $AccountingSupplierParty=$Invoice->appendChild($AccountingSupplierParty);
		$this->fntCrearElemento($AccountingSupplierParty,"cbc:AdditionalAccountID",$settings_dian->typetaxpayer_cod);
        $this->fntParty($AccountingSupplierParty,$settings_dian->typedoc_cod,$settings_dian->identificacion,$settings_dian->economicactivity_cod,$settings_dian->businessname,$settings_dian->municipio_cod,$settings_dian->direction,$settings_dian->typeresponsibility_cod,'4441500','ejemplo@gmail.com',$settings_dian->prefix);
        //FINALIZA LA INFORMACIÓN DEL EMISOR
        //EMPIEZA LA INFORMACIÓN DEL CLIENTE
        $AccountingCustomerParty=$this->xml->createElement("cac:AccountingCustomerParty");
        $AccountingCustomerParty=$Invoice->appendChild($AccountingCustomerParty);
        $this->fntCrearElemento($AccountingCustomerParty,"cbc:AdditionalAccountID",'2');
        $this->fntParty($AccountingCustomerParty,'13','12345678','8699','Cliente de Ejemplo','05001','Calle 100 No. 900 - 10  Centro','ZZ','4441500','ejemplo@gmail.com','',true);
		
		//EMPIEZA LA INFORMACIÓN DE PAGO
		$this->fntCrearPaymentMeans($Invoice,'1','1',date('Y-m-d'));
		$this->fntCrearTaxTotal_IVA($Invoice);
		$this->fntCrearLegalMonetaryTotal($Invoice);
		$this->fntCrearNoteLines($Invoice,'3');
    }
    public function xmlcreditnote($settings_dian,$number,$numberfv,$cufefv){
        //Crea el XML
		$this->xml=new DOMDocument('1.0','UTF-8');
		$this->xml->xmlStandalone = false;
		$this->xml->formatOutput=true;
		//Crea el CreditNote
		$CreditNote=$this->fntCrearCreditNote();
		$this->fntCrearUBLExtensions_CreditNote($CreditNote,$settings_dian,$number);
		$this->fntCrearElemento($CreditNote,'cbc:UBLVersionID','UBL 2.1');
		$this->fntCrearElemento($CreditNote,'cbc:CustomizationID','05');
		$this->fntCrearElemento($CreditNote,'cbc:ProfileID','DIAN 2.1');
		$this->fntCrearElemento($CreditNote,'cbc:ProfileExecutionID','2');
		$this->fntCrearElemento($CreditNote,'cbc:ID','SETP'.$number);
		$this->fntCrearElemento($CreditNote,'cbc:UUID','',[
			["schemeID","2"],
			["schemeName","CUDE-SHA384"]
		]);
		$this->fntCrearElemento($CreditNote,'cbc:IssueDate',date('Y-m-d'));
		$this->fntCrearElemento($CreditNote,'cbc:IssueTime',date('H:i:s').'-05:00');
		$this->fntCrearElemento($CreditNote,'cbc:CreditNoteTypeCode','91');
		$this->fntCrearElemento($CreditNote,'cbc:Note','Nota crèdito de prueba');
		$this->fntCrearElemento($CreditNote,'cbc:DocumentCurrencyCode','COP',[
			['listID','ISO 4217 Alpha'],
			['listAgencyID','6'],
			['listAgencyName','United Nations Economic Commission for Europe']
		]);
		//LineCountNumeric numero de lineas que tine el detalle
		$this->fntCrearElemento($CreditNote,'cbc:LineCountNumeric',1);
		$this->CrearDiscrepancyResponse($CreditNote,$numberfv);
		$this->fntCrearBillingReference($CreditNote,$numberfv,$cufefv);
		
		//EMPIEZA INFORMACION DEL EMISOR    
        $AccountingSupplierParty=$this->xml->createElement("cac:AccountingSupplierParty");
        $AccountingSupplierParty=$CreditNote->appendChild($AccountingSupplierParty);
		$this->fntCrearElemento($AccountingSupplierParty,"cbc:AdditionalAccountID",$settings_dian->typetaxpayer_cod);
        $this->fntParty($AccountingSupplierParty,$settings_dian->typedoc_cod,$settings_dian->identificacion,$settings_dian->economicactivity_cod,$settings_dian->businessname,$settings_dian->municipio_cod,$settings_dian->direction,$settings_dian->typeresponsibility_cod,'','ejemplo@gmail.com',$settings_dian->prefix,false);
        //FINALIZA LA INFORMACIÓN DEL EMISOR
		//EMPIEZA LA INFORMACIÓN DEL CLIENTE
        $AccountingCustomerParty=$this->xml->createElement("cac:AccountingCustomerParty");
        $AccountingCustomerParty=$CreditNote->appendChild($AccountingCustomerParty);
        $this->fntCrearElemento($AccountingCustomerParty,"cbc:AdditionalAccountID",'2');
        $this->fntParty($AccountingCustomerParty,'13','12345678','8699','Cliente de Ejemplo','05001','Calle 100 No. 900 - 10  Centro','ZZ','4441500','ejemplo@gmail.com');
        //FINALIZA LA INFORMACIÓN DEL CLIENTE
        $this->fntCrearPaymentMeans($CreditNote,'1','1',date('Y-m-d'));
		$this->fntCrearTaxTotal($CreditNote);
		$this->fntCrearLegalMonetaryTotal_CreditNote($CreditNote,'COP');
		$this->fntCrearNoteLines($CreditNote,'1');
		$this->fntnombrearchivo(
		"nc",
		'12345678',
		"000",
		date('Y-m-d'),
		$settings_dian->ncs);
	}
	public function xmldebitnote($settings_dian,$number,$numberfv,$cufefv){
        //Crea el XML
		$this->xml=new DOMDocument('1.0','UTF-8');
		$this->xml->xmlStandalone = false;
		$this->xml->formatOutput=true;
		//Crea el DebitNote
		$DebitNote=$this->fntCrearDebitNote();
		$this->fntCrearUBLExtensions_DebitNote($DebitNote,$settings_dian,$number);
		$this->fntCrearElemento($DebitNote,'cbc:UBLVersionID','UBL 2.1');
		$this->fntCrearElemento($DebitNote,'cbc:CustomizationID','05');
		$this->fntCrearElemento($DebitNote,'cbc:ProfileID','DIAN 2.1');
		$this->fntCrearElemento($DebitNote,'cbc:ProfileExecutionID','2');
		$this->fntCrearElemento($DebitNote,'cbc:ID','SETP'.$number);
		$this->fntCrearElemento($DebitNote,'cbc:UUID','',[
			["schemeID","2"],
			["schemeName","CUDE-SHA384"]
		]);
		$this->fntCrearElemento($DebitNote,'cbc:IssueDate',date('Y-m-d'));
		$this->fntCrearElemento($DebitNote,'cbc:IssueTime',date('H:i:s').'-05:00');
		$this->fntCrearElemento($DebitNote,'cbc:Note','Ejemplo de nota débito');
		$this->fntCrearElemento($DebitNote,'cbc:DocumentCurrencyCode','COP',[
			['listID','ISO 4217 Alpha'],
			['listAgencyID','6'],
			['listAgencyName','United Nations Economic Commission for Europe']
		]);
		$this->fntCrearElemento($DebitNote,'cbc:LineCountNumeric','1');
		$this->CrearDiscrepancyResponse($DebitNote,$numberfv);
		$this->fntCrearBillingReference($DebitNote,$numberfv,$cufefv);
		
		//EMPIEZA INFORMACION DEL EMISOR    
        $AccountingSupplierParty=$this->xml->createElement("cac:AccountingSupplierParty");
        $AccountingSupplierParty=$DebitNote->appendChild($AccountingSupplierParty);
		$this->fntCrearElemento($AccountingSupplierParty,"cbc:AdditionalAccountID",$settings_dian->typetaxpayer_cod);
        $this->fntParty($AccountingSupplierParty,$settings_dian->typedoc_cod,$settings_dian->identificacion,$settings_dian->economicactivity_cod,$settings_dian->businessname,$settings_dian->municipio_cod,$settings_dian->direction,$settings_dian->typeresponsibility_cod,'','ejemplo@gmail.com',$settings_dian->prefix,false);
        //FINALIZA LA INFORMACIÓN DEL EMISOR
		//EMPIEZA LA INFORMACIÓN DEL CLIENTE
        $AccountingCustomerParty=$this->xml->createElement("cac:AccountingCustomerParty");
        $AccountingCustomerParty=$DebitNote->appendChild($AccountingCustomerParty);
        $this->fntCrearElemento($AccountingCustomerParty,"cbc:AdditionalAccountID",'2');
        $this->fntParty($AccountingCustomerParty,'13','12345678','8699','Cliente de Ejemplo','05001','Calle 100 No. 900 - 10  Centro','ZZ','4441500','ejemplo@gmail.com');
        
        //FINALIZA LA INFORMACIÓN DEL CLIENTE
        $this->fntCrearPaymentMeans($DebitNote,'1','1',date('Y-m-d'));
		$this->fntCrearTaxTotal($DebitNote);
		$this->LineExtensionAmount=0;
		$this->TaxExclusiveAmount=0;
		$this->fntCrearRequestedMonetaryTotal_DebitNote($DebitNote,'COP');
		$this->fntCrearNoteLines($DebitNote,'2');
		$this->fntnombrearchivo(
			"nd",
			'12345678',
			"000",
			date('Y-m-d'),
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
	function fntCrearUBLExtension1($padre,$settings_dian,$numerofv){
		//Crear UBLExtension
		$UBLExtension=$this->xml->createElement("ext:UBLExtension");
		$UBLExtension=$padre->appendChild($UBLExtension);
		$ExtensionContent=$this->fntCrearElemento($UBLExtension,"ext:ExtensionContent");
		$DianExtensions=$this->fntCrearElemento($ExtensionContent,"sts:DianExtensions");
		$InvoiceControl=$this->fntCrearElemento($DianExtensions,"sts:InvoiceControl");
		$this->fntCrearElemento($InvoiceControl,"sts:InvoiceAuthorization",$settings_dian->resolutionnumber);
		$AuthorizationPeriod=$this->fntCrearElemento($InvoiceControl,"sts:AuthorizationPeriod");
		$this->fntCrearElemento($AuthorizationPeriod,"cbc:StartDate",$settings_dian->resolutiondatestar);
		$this->fntCrearElemento($AuthorizationPeriod,"cbc:EndDate",$settings_dian->resolutiondateend);
		$AuthorizedInvoices=$this->fntCrearElemento($InvoiceControl,"sts:AuthorizedInvoices");
		$this->fntCrearElemento($AuthorizedInvoices,"sts:Prefix",$settings_dian->prefix);
		$this->fntCrearElemento($AuthorizedInvoices,"sts:From",$settings_dian->numberstart);
		$this->fntCrearElemento($AuthorizedInvoices,"sts:To",$settings_dian->numberend);

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
		$this->fntCrearElemento($SoftwareProvider,"sts:ProviderID",$settings_dian->softwareid,[
			["schemeAgencyID","195"],
			["schemeAgencyName","CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)"]
		]);
		$this->fntCrearElemento($SoftwareProvider,"sts:SoftwareID",$settings_dian->softwareid,[
			["schemeAgencyID","195"],
			["schemeAgencyName","CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)"]
		]);

		$this->fntCrearElemento($DianExtensions,"sts:SoftwareSecurityCode",$this->fntCalcularSoftwareSecurityCode($settings_dian,$numerofv,3),[
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

		$this->fntCrearElemento($DianExtensions,"sts:QRCode","1");
	}
	function fntCrearUBLExtension1_CreditNote($padre,$settings_dian,$number){
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
		$this->fntCrearElemento($DianExtensions,"sts:SoftwareSecurityCode",$this->fntCalcularSoftwareSecurityCode($settings_dian,$number,1),[
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
		$this->fntCrearElemento($DianExtensions,"sts:QRCode",$this->qr);
	}
	function fntCrearUBLExtension1_DebitNote($padre,$settings_dian,$number){
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
		$this->fntCrearElemento($DianExtensions,"sts:SoftwareSecurityCode",$this->fntCalcularSoftwareSecurityCode($settings_dian,$number,'2'),[
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
		$this->fntCrearElemento($DianExtensions,"sts:QRCode",$this->qr);
	}
	function fntCrearUBLExtensions($padre,$settings_dian,$numerofv){
		//Crear UBLExtensions
		$UBLExtensions=$this->xml->createElement("ext:UBLExtensions");
		$UBLExtensions=$padre->appendChild($UBLExtensions);
		$this->fntCrearUBLExtension1($UBLExtensions,$settings_dian,$numerofv);

		$UBLExtension=$this->xml->createElement("ext:UBLExtension");
		$UBLExtensions->appendChild($UBLExtension);
		$ExtensionContent=$this->fntCrearElemento($UBLExtension,"ext:ExtensionContent");
		$UBLExtension->appendChild($ExtensionContent);
    }
	function fntCrearUBLExtensions_CreditNote($padre,$settings_dian,$number){
		//Crear UBLExtensions
		$UBLExtensions=$this->xml->createElement("ext:UBLExtensions");
		$UBLExtensions=$padre->appendChild($UBLExtensions);
		$this->fntCrearUBLExtension1_CreditNote($UBLExtensions,$settings_dian,$number);

		$UBLExtension=$this->xml->createElement("ext:UBLExtension");
		$UBLExtensions->appendChild($UBLExtension);
		$ExtensionContent=$this->fntCrearElemento($UBLExtension,"ext:ExtensionContent");
		$UBLExtension->appendChild($ExtensionContent);
	}
	function fntCrearUBLExtensions_DebitNote($padre,$settings_dian,$number){
		//Crear UBLExtensions
		$UBLExtensions=$this->xml->createElement("ext:UBLExtensions");
		$UBLExtensions=$padre->appendChild($UBLExtensions);
		$this->fntCrearUBLExtension1_DebitNote($UBLExtensions,$settings_dian,$number);
		$UBLExtension=$this->xml->createElement("ext:UBLExtension");
		$UBLExtensions->appendChild($UBLExtension);
		$ExtensionContent=$this->fntCrearElemento($UBLExtension,"ext:ExtensionContent");
		$UBLExtension->appendChild($ExtensionContent);
	}
    function CrearDiscrepancyResponse($padre,$number){
		$DiscrepancyResponse=$this->xml->createElement("cac:DiscrepancyResponse");
		$DiscrepancyResponse=$padre->appendChild($DiscrepancyResponse);
		$this->fntCrearElemento($DiscrepancyResponse,"cbc:ReferenceID",'SETP'.$number);
		$this->fntCrearElemento($DiscrepancyResponse,"cbc:ResponseCode",'1');
		$this->fntCrearElemento($DiscrepancyResponse,"cbc:Description",'Descripción fv');
    }
    function fntCrearBillingReference($padre,$number,$cufe){
		//Crear BillingReference
		$BillingReference=$this->xml->createElement("cac:BillingReference");
		$BillingReference=$padre->appendChild($BillingReference);
		$InvoiceDocumentReference=$this->xml->createElement("cac:InvoiceDocumentReference");
		$InvoiceDocumentReference=$BillingReference->appendChild($InvoiceDocumentReference);
		$this->fntCrearElemento($InvoiceDocumentReference,'cbc:ID','SETP'.$number);
		$this->fntCrearElemento($InvoiceDocumentReference,'cbc:UUID',$cufe,[
			["schemeName","CUFE-SHA384"]
		]);
		$this->fntCrearElemento($InvoiceDocumentReference,'cbc:IssueDate',date('Y-m-d'));
    }
    function fntParty($padre,$typedoc_cod,$identificacion,$economicactivity_cod,$businessname,$municipio_cod,$Direccion,$typeresponsibility_cod,$telefono,$email,$prefix='',$PartyIdentification=false){
        $Party=$this->fntCrearElemento($padre,"cac:Party");
        if($PartyIdentification)
        {
			if ($typedoc_cod=='31') {
				$PartyIdentification=$this->fntCrearElemento($Party,"cac:PartyIdentification");
				$this->fntCrearElemento($PartyIdentification,"cbc:ID",explode("-", $identificacion)[0],[
					["schemeName",$typedoc_cod],
					["schemeID",explode("-", $identificacion)[1]]
				]);
			} else {
				$PartyIdentification=$this->fntCrearElemento($Party,"cac:PartyIdentification");
				$this->fntCrearElemento($PartyIdentification,"cbc:ID",$identificacion,[
					["schemeName",$typedoc_cod],
					["schemeID",$identificacion]
				]);
			}
            
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
    function fntCrearTaxTotal($padre){
		$TaxTotal=$this->xml->createElement("cac:TaxTotal");
		$TaxTotal=$padre->appendChild($TaxTotal);
        $this->fntCrearElemento($TaxTotal,"cbc:TaxAmount",'950.00',[
			["currencyID",'COP']
		]);
		$TaxSubtotal=$this->fntCrearElemento($TaxTotal,"cac:TaxSubtotal",'');
        $this->fntCrearElemento($TaxSubtotal,"cbc:TaxableAmount",'5000.00',[
            ["currencyID",'COP']
        ]);
        $this->fntCrearElemento($TaxSubtotal,"cbc:TaxAmount",'950.00',[
            ["currencyID",'COP']
        ]);
        $TaxCategory=$this->fntCrearElemento($TaxSubtotal,"cac:TaxCategory",'');
        $TaxCategory=$TaxSubtotal->appendChild($TaxCategory);
        $this->fntCrearElemento($TaxCategory,"cbc:Percent",'19.00');
        $TaxScheme=$this->fntCrearElemento($TaxCategory,"cac:TaxScheme");
        $TaxScheme=$TaxCategory->appendChild($TaxScheme);
        $this->fntCrearElemento($TaxScheme,"cbc:ID",'01');
        $this->fntCrearElemento($TaxScheme,"cbc:Name",'IVA');
    }
    function fntCrearLegalMonetaryTotal_CreditNote($padre){
		$LegalMonetaryTotal=$this->xml->createElement("cac:LegalMonetaryTotal");
		$LegalMonetaryTotal=$padre->appendChild($LegalMonetaryTotal);

		$this->fntCrearElemento($LegalMonetaryTotal,"cbc:LineExtensionAmount",'5000.00',[
			["currencyID",'COP']
		]);
		$this->fntCrearElemento($LegalMonetaryTotal,"cbc:TaxExclusiveAmount",'5000.00',	[
			["currencyID",'COP']
		]);
		$this->fntCrearElemento($LegalMonetaryTotal,"cbc:TaxInclusiveAmount",'5950.00',[
			["currencyID",'COP']
		]);
		$this->fntCrearElemento($LegalMonetaryTotal,"cbc:PayableAmount",'5950.00',[
			["currencyID",'COP']
		]);
	}
	function fntCrearRequestedMonetaryTotal_DebitNote($padre){
		$RequestedMonetaryTotal=$this->xml->createElement("cac:RequestedMonetaryTotal");
		$RequestedMonetaryTotal=$padre->appendChild($RequestedMonetaryTotal);

		$this->fntCrearElemento($RequestedMonetaryTotal,"cbc:LineExtensionAmount",'5000.00',[
			["currencyID",'COP']
		]);
		$this->fntCrearElemento($RequestedMonetaryTotal,"cbc:TaxExclusiveAmount",'5000.00',[
			["currencyID",'COP']
		]);
		$this->fntCrearElemento($RequestedMonetaryTotal,"cbc:TaxInclusiveAmount",'5950.00',[
			["currencyID",'COP']
		]);
		$this->fntCrearElemento($RequestedMonetaryTotal,"cbc:PayableAmount",'5950.00',[
			["currencyID",'COP']
		]);
    }
    function fntCrearNoteLines($padre,$type){
	    //Muestra el detalle de la nota crédito
		$this->fntCrearNoteLine($padre,'1','1.000000','NIU','5000.00','19.00','950.00','Producto de ejemplo','140',$type);
	}
	function fntCrearNoteLine($padre,$ID,$Cantidad,$cod_unidadmedida,$Precio,$Porcentajeiva,$Iva,$Nombreservicio,$Codigoservicio,$note_type){
		if ($note_type=='1') {
			$NoteLine=$this->xml->createElement("cac:CreditNoteLine");
		}elseif($note_type=='2') {
			$NoteLine=$this->xml->createElement("cac:DebitNoteLine");
		}else {
			$NoteLine=$this->xml->createElement("cac:InvoiceLine");
		}
		$NoteLine=$padre->appendChild($NoteLine);
		$this->fntCrearElemento($NoteLine,"cbc:ID",$ID);
		if ($note_type=='1') {
			$this->fntCrearElemento($NoteLine,"cbc:CreditedQuantity",$Cantidad,[
				['unitCode',$cod_unidadmedida]
			]);
		}elseif($note_type=='2'){
			$this->fntCrearElemento($NoteLine,"cbc:DebitedQuantity",$Cantidad,[
				['unitCode',$cod_unidadmedida]
			]);
		}else {
			$this->fntCrearElemento($NoteLine,"cbc:InvoicedQuantity",$Cantidad,[
				['unitCode',$cod_unidadmedida]
			]);
		}
		$this->fntCrearElemento($NoteLine,"cbc:LineExtensionAmount",'5000.00',[
			['currencyID','COP']
		]);
		if ($note_type=='3') {
			$this->fntCrearElemento($NoteLine,"cbc:FreeOfChargeIndicator",'false');
		}
		$TaxTotal=$this->fntCrearElemento($NoteLine,"cac:TaxTotal");
		$this->fntCrearElemento($TaxTotal,"cbc:TaxAmount",'950.00',[
			['currencyID','COP']
		]);
		$TaxSubtotal=$this->fntCrearElemento($TaxTotal,"cac:TaxSubtotal");
		if ($Iva>0) 
		{
			$this->fntCrearElemento($TaxSubtotal,"cbc:TaxableAmount",'5000.00',[
				['currencyID','COP']
			]);
		}
		else
		{
			$this->fntCrearElemento($TaxSubtotal,"cbc:TaxableAmount",'0.00',[
				['currencyID','COP']
			]);
		}
		$this->fntCrearElemento($TaxSubtotal,"cbc:TaxAmount",'950.00',[
			['currencyID','COP']
		]);
		$TaxCategory=$this->fntCrearElemento($TaxSubtotal,"cac:TaxCategory");
		$this->fntCrearElemento($TaxCategory,"cbc:Percent",'19.00');
		$TaxScheme=$this->fntCrearElemento($TaxCategory,"cac:TaxScheme");
		$this->fntCrearElemento($TaxScheme,"cbc:ID",'01');
		$this->fntCrearElemento($TaxScheme,"cbc:Name",'IVA');
		$Item=$this->fntCrearElemento($NoteLine,"cac:Item");
		$this->fntCrearElemento($Item,"cbc:Description",$Nombreservicio);
		$StandardItemIdentification=$this->fntCrearElemento($Item,"cac:StandardItemIdentification");
		$this->fntCrearElemento($StandardItemIdentification,"cbc:ID",$Codigoservicio,[
			['schemeID','999'],
			['schemeName','EAN13']
		]);
		$AdditionalItemIdentification=$this->fntCrearElemento($Item,"cac:AdditionalItemIdentification");
		$this->fntCrearElemento($AdditionalItemIdentification,"cbc:ID",$Codigoservicio,[
			['schemeID','999'],
			['schemeName','EAN13'],
		]);
		$Price= $this->fntCrearElemento($NoteLine,"cac:Price");
		$this->fntCrearElemento($Price,"cbc:PriceAmount",'5000.00',[
			['currencyID','COP']
		]);
		$this->fntCrearElemento($Price,"cbc:BaseQuantity",$Cantidad,[
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
	function fntCalcularSoftwareSecurityCode($settings_dian,$number,$type){
		//Id Software + Pin + Número
		if($type=='1'){
			return hash('sha384',($settings_dian->softwareid.$settings_dian->softwarepin.$settings_dian->prefix.$number));
        }elseif($type=='2'){
            return hash('sha384',($settings_dian->softwareid.$settings_dian->softwarepin.$settings_dian->prefix.$number));
        }
        else{
			return hash('sha384',($settings_dian->softwareid.$settings_dian->softwarepin.$settings_dian->prefix.$number));
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
	function firmar_xml($pathCertificate,$passwors,$xmlString,$identificacion,$fecha,$pin,$technicalKey,$enviados,$type){
        $domDocument = new DOMDocument();
		$domDocument->loadXML($xmlString);
		if ($type=='1') {
            $tipo='nc';
            $signInvoice = new SignCreditNote($pathCertificate, $passwors, $xmlString,SignCreditNote::ALGO_SHA256,null,$pin);
		} elseif($type=='2') {
            $tipo='nd';
            $signInvoice = new SignDebitNote($pathCertificate, $passwors, $xmlString,SignDebitNote::ALGO_SHA256,null,$pin);
		}else{
            $tipo='fv';
            $signInvoice = new SignInvoice($pathCertificate, $passwors, $xmlString,SignInvoice::ALGO_SHA256,$technicalKey);
        }     
        $nombrearchivo=$this->fntnombrearchivo($tipo,$identificacion,"000",$fecha,$enviados);
        //se obtiene el cufe
		$this->cude=$signInvoice->getCude();
		$this->cufe=$signInvoice->getCufe();
        $this->qr=$signInvoice->getQR();
        //se guarda en el nombre del archivo y el obejto instanciado
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
			$SendBillSync->fileName = $filename;
			$SendBillSync->contentFile = $zip;
            $resp=$SendBillSync->signToSend()->getResponseToObject()->Envelope->Body;
		} 
		return $resp;
	}
	function fntCrearTaxTotal_IVA($padre){
		$TaxTotal=$this->xml->createElement("cac:TaxTotal");
		$TaxTotal=$padre->appendChild($TaxTotal);
        $this->fntCrearElemento($TaxTotal,"cbc:TaxAmount",'950.00',[
			["currencyID",'COP']
		]);
		$TaxSubtotal=$this->fntCrearElemento($TaxTotal,"cac:TaxSubtotal",'');
		$this->fntCrearElemento($TaxSubtotal,"cbc:TaxableAmount",'5000.00',[
			["currencyID",'COP']
		]);
		$this->fntCrearElemento($TaxSubtotal,"cbc:TaxAmount",'950.00',[
			["currencyID",'COP']
		]);
		$TaxCategory=$this->fntCrearElemento($TaxSubtotal,"cac:TaxCategory",'');
		$TaxCategory=$TaxSubtotal->appendChild($TaxCategory);
		$this->fntCrearElemento($TaxCategory,"cbc:Percent",'19.00');
		$TaxScheme=$this->fntCrearElemento($TaxCategory,"cac:TaxScheme");
		$TaxScheme=$TaxCategory->appendChild($TaxScheme);
		$this->fntCrearElemento($TaxScheme,"cbc:ID",'01');
		$this->fntCrearElemento($TaxScheme,"cbc:Name",'IVA');
	}
	function fntCrearLegalMonetaryTotal($padre){
		$LegalMonetaryTotal=$this->xml->createElement("cac:LegalMonetaryTotal");
		$LegalMonetaryTotal=$padre->appendChild($LegalMonetaryTotal);

		$this->fntCrearElemento($LegalMonetaryTotal,"cbc:LineExtensionAmount",'5000.00',[
			["currencyID",'COP']
		]);
		$this->fntCrearElemento($LegalMonetaryTotal,"cbc:TaxExclusiveAmount",'5000.00',[
			["currencyID",'COP']
		]);
		$this->fntCrearElemento($LegalMonetaryTotal,"cbc:TaxInclusiveAmount",'5950.00',[
			["currencyID",'COP']
		]);
		$this->fntCrearElemento($LegalMonetaryTotal,"cbc:PayableAmount",'5950.00',[
			["currencyID",'COP']
		]);
    }
}
