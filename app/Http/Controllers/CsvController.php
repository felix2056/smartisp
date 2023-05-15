<?php

namespace App\Http\Controllers;
use App\Http\Requests\CsvRequest;
use App\libraries\BackupMysql;
use App\libraries\Files;
use App\libraries\MySQLBackup;
use App\libraries\Slog;
use App\libraries\UploadHandler;
use App\models\GlobalSetting;
use App\models\InvoiceCsvFile;
use App\Service\CommonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

class CsvController extends Controller
{
//	public function __construct()
//	{
//		parent::__construct();
//		$this->middleware(function ($request, $next) {
//			$this->username = auth()->user()->username;
//			$this->userId = auth()->user()->id;
//			return $next($request);
//		});
//	}

    public function store(CsvRequest $request)
    {
        $fillables = ['date', 'refint_num', 'fisc_num', 'type_doc', 'status', 'printer', 'doc_refer', 'numz', 'file_name', 'inv_content'];

        try {
            $invoiceCsvFile = new InvoiceCsvFile();

            foreach($fillables as $field) {
                if($request->has($field)) {
                    $invoiceCsvFile->$field = $request->$field;
                }
            }

            $invoiceCsvFile->save();

            return \response()->json([
                'message' => 'Successfully updated'
            ]);
        } catch(\Exception $exception) {
            return \response()->json([
                'message' => 'Something went wrong'
            ]);
        }

    }
}
