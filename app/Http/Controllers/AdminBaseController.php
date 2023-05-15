<?php

namespace App\Http\Controllers;

use App;
use App\Http\Controllers\Controller;
use App\models\GlobalSetting;
use App\models\Plan;
use App\models\Router;
use Carbon\Carbon;

class AdminBaseController extends Controller
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
			$this->user = auth()->user();
			if($this->user) {
                App::setLocale($this->global->locale);
                $perm = \DB::table('permissions')->where('user_id', '=', $this->user->id)->get();

                $allRouters = Router::all();
                $allPlans = Plan::all();
                $this->clients = $perm[0]->access_clients;
                $this->plans = $perm[0]->access_plans;
                $this->routers = $perm[0]->access_routers;
                $this->users = $perm[0]->access_users;
                $this->system = $perm[0]->access_system;
                $this->bill = $perm[0]->access_pays;
                $this->template = $perm[0]->access_templates;
                $this->ticket = $perm[0]->access_tickets;
                $this->sms = $perm[0]->access_sms;
                $this->reports = $perm[0]->access_reports;
                $this->v = $this->global->version;
                $this->st = $this->global->status;
                $this->lv = $this->global->license;
                $this->company = $this->global->company;
                $this->permissions = $perm->first();
                $this->allRouters = $allRouters;
                $this->allPlans = $allPlans;
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
