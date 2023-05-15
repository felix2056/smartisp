<?php
namespace App\libraries;
use Illuminate\Support\Facades\Session;

/**
*  Check login for client
*/
class CheckUser
{
    public static function isLogin()
    {
    	$en = new Pencrypt();

		if (Session::has('Ruser'))
		{
   			 $value = Session::get('Ruser');
   			 return $en->decode($value);
		}

		if (isset($_COOKIE["Ruser"])) {
			return $en->decode($_COOKIE["Ruser"]);
		}

		return 1;
    }

}
