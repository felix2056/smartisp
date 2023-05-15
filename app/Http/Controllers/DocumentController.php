<?php

namespace App\Http\Controllers;

use App\Classes\Reply;
use App\Http\Requests\Admin\Documents\ContractStoreRequest;
use App\Http\Requests\Admin\Documents\ContractUpdateRequest;
use App\Http\Requests\Admin\Documents\StoreRequest;
use App\Http\Requests\Admin\Documents\UpdateRequest;
use App\models\Document;
use App\models\Template;
use App\Service\CommonService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade as PDF;

class DocumentController extends BaseController
{
	public function __construct()
	{
		parent::__construct();
		$this->middleware(function ($request, $next) {
			$this->username = auth()->user()->username;
			$this->userId = auth()->user()->id;
			return $next($request);
		});
	}
	
    /**
     * @param Request $request
     * @param $clientId
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create(Request $request, $clientId)
	{
		return view('documents.create', ['clientId' => $clientId]);
	}
    /**
     * @param Request $request
     * @param $clientId
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function contractCreate(Request $request, $clientId)
	{
		$templates = Template::where('type', 'contract')->get();
		return view('documents.contract-create', ['clientId' => $clientId, 'templates' => $templates]);
	}

    /**
     * @param Request $request
     * @param $clientId
     * @return mixed
     * @throws \Exception
     */
    public function postList(Request $request, $clientId)
	{
		$documents = Document::join('clients', 'clients.id', '=', 'documents.client_id')
			->join('users', 'users.id', '=', 'documents.uploaded_by')
			->select('documents.id', 'users.name', 'documents.title', 'documents.created_at', 'documents.description', 'documents.type', 'documents.client_id', 'documents.document_name')
			->where('documents.client_id', $clientId);

		return Datatables::of($documents)
		->addColumn('action', function ($row) {

			$html='';
			
			if($row->type == "contract") {
				$html.='<a href="'.route('contracts.view', $row->id).'" target="_blank"  title="View"><span class="glyphicon glyphicon-print"></span></a>&nbsp;';
				$html.=' <a href="'.route('contracts.download', $row->id).'"  title="Remove"><span class="glyphicon glyphicon-download"></span></a>&nbsp;';

			} else {
				$html.='<a href="'.asset("/assets/documents/$row->client_id/$row->document_name").'" target="_blank"  title="View"><span class="glyphicon glyphicon-print"></span></a>&nbsp;';
				$html.=' <a href="'.route('documents.download', $row->id).'"  title="Download"><span class="glyphicon glyphicon-download"></span></a>&nbsp;';
			}
			
			if($row->type == "contract") {
				$html.='<a href="javascript:;" onclick="editContract(\''.$row->id.'\')" title="Edit"><span class="glyphicon glyphicon-edit"></span></a>&nbsp;';
			} else {
				
				$html.='<a href="javascript:;" onclick="editDocument(\''.$row->id.'\')" title="Edit"><span class="glyphicon glyphicon-edit"></span></a>&nbsp;';
			}
			
			$html.=' <a href="javascript:;" onclick="deleteDocument(\''.$row->id.'\')" title="Remove"><span class="glyphicon glyphicon-trash"></span></a>';
			
			return $html;
		})
		->addColumn('source', function ($row) {
			if($row->type == 'contract') {
				return "Generated";
			}
			return 'Uploaded';
		})
		->editColumn('created_at', function ($row) {
			return Carbon::parse($row->created_at)->format('m/d/Y');
		})
		->rawColumns(['action'])
		->make(true);
	}

    /**
     * @param StoreRequest $request
     * @return array|string[]
     * @throws ValidationException
     */
    public function store(StoreRequest $request)
	{
		try  {
			$document = new Document();
			$document->uploaded_by = auth()->user()->id;
			$document->client_id = $request->client_id;
			$document->title = $request->title;
			$document->description = $request->description;
			$document->visible_to_client = 0;
			
			if($request->has('visible_to_client')) {
				$document->visible_to_client = $request->visible_to_client;
			}
			
			if($request->has('file')) {
				$destinationPath = public_path() . "/assets/documents/$request->client_id";
				
				if(!File::exists($destinationPath)) {
					File::makeDirectory($destinationPath, 0755, true, true);
				}
				
				$url_photo = $request->file->getClientOriginalName();
				$upload_success = $request->file->move($destinationPath, $request->file->getClientOriginalName());
				
				if($upload_success) {
					$document->document_name = $url_photo;
				}
			}
			
			$document->save();
			$documentId = $document->id;
			CommonService::log("#$documentId Documento agregado", $this->username, 'success' , $this->userId);
		} catch (\Exception $exception) {
			return Reply::error($exception->getMessage());
		}
		
		
		return Reply::success('Document Successfully added.');
	}

    /**
     * @param Request $request
     * @param $paymentId
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit(Request $request, $documentId)
	{
		$document = Document::find($documentId);
		return view('documents.edit', ['clientId' => $request->client_id, 'document' => $document]);
	}
    /**
     * @param Request $request
     * @param $documentId
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function contractEdit(Request $request, $documentId)
	{
		$document = Document::find($documentId);
		$templates = Template::where('type', 'contract')->get();
		
		return view('documents.contract-edit', ['clientId' => $request->client_id, 'document' => $document, 'templates' => $templates]);
	}

    /**
     * @param UpdateRequest $request
     * @param $id
     * @return array|string[]
     * @throws ValidationException
     */
    public function update(UpdateRequest $request, $id)
	{
		DB::beginTransaction();

		$document = Document::find($id);

		$document->uploaded_by = auth()->user()->id;
		$document->client_id = $request->client_id;
		$document->title = $request->title;
		$document->description = $request->description;
		
		if($request->has('visible_to_client')) {
			$document->visible_to_client = $request->visible_to_client;
		}
		
		if($request->has('file')) {
			$destinationPath = public_path() . "/assets/documents/$request->client_id";
			
			if(!File::exists($destinationPath)) {
				File::makeDirectory($destinationPath, 0755, true, true);
			}
			
			$url_photo = $request->file->getClientOriginalName();
			$upload_success = $request->file->move($destinationPath, $request->file->getClientOriginalName());
			
			if($upload_success) {
				$document->document_name = $url_photo;
			}
		}

		$document->save();
		$documentId = $document->id;
		CommonService::log("#$documentId Documento actualizado", $this->username, 'success' , $this->userId);
		DB::commit();

		return Reply::success('Document Successfully updated.');
	}

    /**
     * @param Request $request
     * @param $id
     * @return array|string[]
     */
    public function delete(Request $request, $id)
	{
		DB::beginTransaction();
		
		Document::destroy($id);
		CommonService::log("#$id Documento eliminado", $this->username, 'success' , $this->userId);
		DB::commit();

		return Reply::success('Document Successfully deleted.');
	}

	
	public function viewDocument($id)
	{
    	$document = Document::find($id);
    	
		$destinationPath = public_path() . "/assets/documents/$document->client_id/$document->document_name";
		return response()->file($destinationPath);
	}

	
	public function downloadDocument($id)
	{
    	$document = Document::find($id);
    	
		$destinationPath = public_path() . "/assets/documents/$document->client_id/$document->document_name";
		
		$response = response()->download($destinationPath, $document->document_name, []);
		ob_end_clean();
		
		return $response;
		
	}
	
	
	/**
	 * @param StoreRequest $request
	 * @return array|string[]
	 * @throws ValidationException
	 */
	public function contractStore(ContractStoreRequest $request)
	{
		try  {
			$document = new Document();
			$document->uploaded_by = auth()->user()->id;
			$document->client_id = $request->client_id;
			$document->title = $request->title;
			$document->type = 'contract';
			$document->description = $request->description;
			$document->contract_content = $request->contract_content;
			$document->template_id = $request->templates;
			$document->visible_to_client = 0;
			
			if($request->has('visible_to_client')) {
				$document->visible_to_client = $request->visible_to_client;
			}
			
			$document->save();
			$documentId = $document->id;
			CommonService::log("#$documentId Contrato agregado", $this->username, 'success' , $this->userId);
			
		} catch (\Exception $exception) {
			return Reply::error($exception->getMessage());
		}
		
		
		return Reply::success('Contract Successfully added.');
	}
	
	
	/**
	 * @param UpdateRequest $request
	 * @param $id
	 * @return array|string[]
	 * @throws ValidationException
	 */
	public function contractUpdate(ContractUpdateRequest $request, $id)
	{
		DB::beginTransaction();
		
		$document = Document::find($id);
		
		$document->uploaded_by = auth()->user()->id;
		$document->client_id = $request->client_id;
		$document->title = $request->title;
		$document->type = 'contract';
		$document->template_id = $request->templates;
		$document->description = $request->description;
		$document->contract_content = $request->contract_content;
		
		if($request->has('visible_to_client')) {
			$document->visible_to_client = $request->visible_to_client;
		}
		
		$document->save();
		
		$documentId = $document->id;
		
		CommonService::log("#$documentId Contrato actualizado", $this->username, 'success' , $this->userId);
		DB::commit();
		
		return Reply::success('Document Successfully updated.');
	}
	
	public function viewContract($id)
	{
		$this->document = Document::find($id);
		$this->content = $this->prepareHtml($this->document);
		
		return view('documents.view-contract', $this->data);
	}
	
	public function downloadContract($id)
	{
		$document = Document::find($id);
		
		$content = $this->prepareHtml($document);
		
		$pdf = PDF::loadHTML($content);
		return $pdf->download('contract.pdf');
		
	}
	
	public function prepareHtml($document)
	{
		$companyName = $this->global->company;
		$companyEmail = $this->global->company_email;
		$companyDni = $this->global->dni;
		$companyPhone = $this->global->phone;
		$clientName = $document->client->name;
		$clientDni = $document->client->dni;
		$clientEmail = $document->client->email;
		$clientPhone = $document->client->phone;
		$dateRegistration = Carbon::parse($document->client->created_at)->format('j F Y');
		$content = $document->contract_content;
		
		$content = str_replace('COMPANY_NAME', $companyName, $content);
		$content = str_replace('EMAIL_ISP', $companyEmail, $content);
		$content = str_replace('DNI_ISP', $companyDni, $content);
		$content = str_replace('PHONE_ISP', $companyPhone, $content);
		$content = str_replace('CLIENT_NAME', $clientName, $content);
		$content = str_replace('DNI_CLIENT', $clientDni, $content);
		$content = str_replace('EMAIL_CLIENT', $clientEmail, $content);
		$content = str_replace('PHONE_CLIENT', $clientPhone, $content);
		$content = str_replace('DATE_REGISTRATION', $dateRegistration, $content);
		
		return $content;
	}
	
}
