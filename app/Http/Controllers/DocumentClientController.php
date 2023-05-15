<?php

namespace App\Http\Controllers;
use App\DataTables\DocumentDataTable;
use App\libraries\CheckUser;
use App\models\Client;
use App\models\Document;
use App\models\GlobalSetting;
use Illuminate\Support\Facades\Redirect;
use Barryvdh\DomPDF\Facade as PDF;

class DocumentClientController extends BaseController
{
    public function index(DocumentDataTable  $dataTable)
    {
        $user = CheckUser::isLogin();

        if ($user == 1)
            return Redirect::to('/');

        $client = Client::where('email', '=', $user)->orWhere('dni', '=', $user)->get();
        $global = GlobalSetting::all()->first();
        $data = array(
            "user" => $user,
            "name" => $client[0]->name,
            "company" => $global->company,
            "photo" => $client[0]->photo,
        );

        return $dataTable->render('documentsClient.index', $data);

    }
	
	public function viewContract($id)
	{
		$document = Document::find($id);
		$global = GlobalSetting::first();
		$companyName = $global->company;
		$clientName = $document->client->name;
		
		$content = $document->contract_content;
		
		$content = str_replace('COMPANY_NAME', $companyName, $content);
		
		$content = str_replace('CLIENT_NAME', $clientName, $content);
		
		return view('documents.view-contract', ['content' => $content]);
	}
	
	public function downloadContract($id)
	{
		$document = Document::find($id);
		$global = GlobalSetting::first();
		$companyName = $global->company;
		$clientName = $document->client->name;
		
		$content = $document->contract_content;
		
		$content = str_replace('COMPANY_NAME', $companyName, $content);
		
		$content = str_replace('CLIENT_NAME', $clientName, $content);
		
		$pdf = PDF::loadHTML($content);
		return $pdf->download('contract.pdf');
		
	}

}
