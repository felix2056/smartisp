<?php

namespace App\Http\Controllers;

use App;
use App\Http\Controllers\Controller;
use App\models\GlobalSetting;
use Carbon\Carbon;

class BaseController extends Controller
{

    public $data = [];

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->data[$name];
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data[ $name ]);
    }
	
	
	/**
	 * UserBaseController constructor.
	 */
	public function __construct()
	{
		// Inject currently logged in user object into every view of user dashboard
		$this->middleware(function ($request, $next) {
			$this->global = GlobalSetting::first();
			
			Carbon::setLocale($this->global->locale);
			$this->company = $this->global->company;
			$this->user = auth()->guard('cashdesk')->user();
			if($this->user) {
				App::setLocale($this->user->locale);
			}
			return $next($request);
		});
	}
	
	/**
     * Setup the layout used by the controller.
     *
     * @return void
     */
    protected function setupLayout()
    {
        if (!is_null($this->layout)) {
            $this->layout = View::make($this->layout);
        }
    }

}
