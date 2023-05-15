<?php
namespace App\libraries;
use App\models\Router;

/**
* Get Router connect data
*/
class RouterConnect
{
	//metodo para obtener datos de conexion
	function get_connect($id){

		$router = Router::find($id);
		$encrypt = new Pencrypt();
		$ip = $router->ip;
		$login = $router->login;
		$port = $router->port;
		$password  = $router->password;
		$password = $encrypt->decode($password);
		$lan = $router->lan;
		$name = $router->name;

		$data = array('ip' => $ip,
					  'login' => $login,
					  'port' => $port,
					  'password' => $password,
					  'lan' => $lan,
					  'name' => $name,
					  'connect' => $router->connection
					 );

		return $data;
	}

}
