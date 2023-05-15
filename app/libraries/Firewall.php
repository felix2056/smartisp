<?php
namespace App\libraries;
/**
* FIREWALL FUNCTIONS
*/
class Firewall
{
	/////////////////////////// ADDRESS LIST RULES //////////////////////////


	//metodo para añadir registros en address list a los deudores (firewall)  default : list=avisos, disabled=true
	public static function add_address_list($API,$Address,$list,$op,$comment){
		$API->write("/ip/firewall/address-list/add",false);
		$API->write("=list=".$list,false);
		$API->write("=address=".$Address,false);
		$API->write("=comment=".$comment,false);
		$API->write("=disabled=".$op,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
		return $ARRAY;
	}

	//metodo para eliminar registro en arp
	public static function remove_address_list($API,$id){

		$API->write("/ip/firewall/address-list/remove",false);
		$API->write("=.id=".$id,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;
	}

	//metodo para listar Address List
	public static function list_address_list($API){

		$API->write("/ip/firewall/address-list/print",true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;
	}

	//metodo para editar registro en address list
	public static function set_address_list($API,$id,$Address,$list,$op,$comment){

		$API->write("/ip/firewall/address-list/set",false);
		$API->write("=.id=".$id,false);
		$API->write("=list=".$list,false);
		$API->write("=address=".$Address,false);
		$API->write("=comment=".$comment,false);
		$API->write("=disabled=".$op,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
		return $ARRAY;
	}

	public static function get_id_address_list_name($API,$Address,$list){

		$ID = $API->comm('/ip/firewall/address-list/print', array(
			".proplist" => ".id",
			"?address" => $Address,
			"?list" => $list
		));

		//verificamos si el usuario esta en el router
		if(count($ID)>0)
			return $ID;
		else
			return "notFound";
	}

	//metodo para buscar y recuperar la ID de un registro en address list
	public static function get_id_address_list($API,$Address){

		$ID = $API->comm('/ip/firewall/address-list/print', array(
			".proplist" => ".id",
			"?address" => $Address
		));

		//verificamos si el usuario esta en el router
		if(count($ID)>0)
			return $ID;
		else
			return "notFound";
	}

	//metyodo para buscar y recuperar la ID de un registro en address list para PCQ
	public static function get_id_address_list_pcq($API,$Address,$comment){

		$API->write('/ip/firewall/address-list/print',false);
		$API->write('?address='.$Address,false);
		$API->write('?comment='.$comment,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		//verificamos si el usuario esta en el router
		if(count($ARRAY)>0){
			//vericiamos que sea diferente de avisos
			if ($ARRAY[0]['list']!='avisos') {

				return $ARRAY;

			}else{
				return 'notFound';
			}
		}
		else
			return "notFound";

	}

	//metyodo para buscar y recuperar la ID de un registro en address list para PCQ
	public static function get_id_address_list_by_name($API,$Address, $list){

		$API->write('/ip/firewall/address-list/print',false);
		$API->write('?address='.$Address,false);
		$API->write('?list='.$list,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		//verificamos si el usuario esta en el router
		if(count($ARRAY)>0){
			//vericiamos que sea diferente de avisos
			if ($ARRAY[0]['list']!='avisos') {

				return $ARRAY;

			}else{
				return 'notFound';
			}
		}
		else
			return "notFound";

	}

	//metodo para habilitar o deshabilitar ip de address list
	public static function block_address($API,$id,$st){

		$API->write("/ip/firewall/address-list/set",false);
		$API->write("=.id=".$id,false);
		$API->write("=disabled=".$st,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
		return $ARRAY;
	}

	//metodo para contar todas las reglas del filter rules retorna entero
	public static function count_filter_all($API){

		$API->write("/ip/firewall/filter/print",false);
		$API->write("=count-only=",true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
		return $ARRAY;
	}


	/////////////////////////// FILTER RULES //////////////////////////

	//metodo para añadir filters block
	public static function filter_add_block($API,$Address,$mac,$comment,$order){

		$API->write("/ip/firewall/filter/add",false);
		if ($order>0) {
			$API->write("=place-before=0",false);
		}

		$API->write("=chain=forward",false);
		$API->write("=action=drop",false);
		$API->write("=src-address=".$Address,false);
		//$API->write("=in-interface=".$lan,false);

		if ($mac!='00:00:00:00:00:00') {
			$API->write("=src-mac-address=".$mac,false);
		}else{
			$API->write("=!src-mac-address=",false);
		}

		$API->write("=comment=".$comment,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
		return $ARRAY;
	}

	//metodo para editar filters block
	public static function filter_set_block($API,$id,$Address,$mac){

		$API->write("/ip/firewall/filter/set",false);
		$API->write("=.id=".$id,true);
		$API->write("=chain=forward",false);
		$API->write("=action=drop",false);
		$API->write("=src-address=".$Address,false);

		if ($mac!='00:00:00:00:00:00') {
			$API->write("=src-mac-address=".$mac,false);
		}else{
			$API->write("=!src-mac-address=",false);
		}

		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
		return $ARRAY;
	}


	//metodo para eliminar filters block
	public static function remove_filter_block($API,$id){

		$API->write("/ip/firewall/filter/remove",false);
		$API->write("=.id=".$id,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;
	}

	//metodo para buscar filters block
	public static function get_id_filter_block($API,$Address){

		$ID = $API->comm('/ip/firewall/filter/print', array(
			".proplist" => ".id",
			"?src-address" => $Address
		));

		if(count($ID)>0)
			return $ID;
		else
			return "notFound";
	}

	//metodo para agregar regla de bloqueo total menos dns (Aviso de corte)
	public static function filter_block_udp($API,$interface){

		$API->write("/ip/firewall/filter/add",false);
		$API->write("=chain=forward",false);
		$API->write("=protocol=udp",false);
//		$API->write("=in-interface=".$interface,false);
		$API->write("=dst-port=!53",false);
		$API->write("=src-address-list=avisos",false);
		$API->write("=action=drop",false);
		$API->write("=comment=Smartisp avisos Smartisp-dns",true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;

	}

	//metodo para editar regla de bloqueo filter tcp
	public static function filter_set_tcp($API,$id,$interface){

		$API->write("/ip/firewall/filter/set",false);
		$API->write("=.id=".$id,false);
		$API->write("=chain=forward",false);
		$API->write("=protocol=tcp",false);
//		$API->write("=in-interface=".$interface,false);
		$API->write("=dst-port=!80",false);
		$API->write("=src-address-list=avisos",false);
		$API->write("=action=drop",false);
		$API->write("=comment=Smartisp avisos Smartisp-tcp",true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;
	}

	//metodo para editar regla de bloqueo filter udp
	public static function filter_set_udp($API,$id,$interface){

		$API->write("/ip/firewall/filter/set",false);
		$API->write("=.id=".$id,false);
		$API->write("=chain=forward",false);
		$API->write("=protocol=udp",false);
//		$API->write("=in-interface=".$interface,false);
		$API->write("=dst-port=!53",false);
		$API->write("=src-address-list=avisos",false);
		$API->write("=action=drop",false);
		$API->write("=comment=Smartisp avisos Smartisp-dns",true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;
	}


	//metodo para agregar regla de bloqueo total menos http (Aviso de corte)
	public static function filter_block_tcp($API,$interface){

		$API->write("/ip/firewall/filter/add",false);
		$API->write("=chain=forward",false);
		$API->write("=protocol=tcp",false);
//		$API->write("=in-interface=".$interface,false);
		$API->write("=dst-port=!80",false);
		$API->write("=src-address-list=avisos",false);
		$API->write("=action=drop",false);
		$API->write("=comment=Smartisp avisos Smartisp-tcp",true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;

	}

	//metodo para buscar regla de bloqueo
	public static function find_block_filter($API,$word){

		$ID = $API->comm('/ip/firewall/filter/print', array(
			".proplist" => ".id",
			"?comment" => $word
		));

		if(count($ID)>0)
			return $ID;
		else
			return "notFound";
	}

	//metodo para agregar regla nat redireccionamiento a web proxy avisos pppoe
	public static function add_nat_adv_ppp($API,$network,$protocol){

		$API->write("/ip/firewall/nat/add", false);
		$API->write("=chain=dstnat",false);
		$API->write("=src-address=".$network,false);
		$API->write("=protocol=tcp",false);
		if ($protocol=='http') {
			$API->write("=dst-port=80",false);
		}
		if ($protocol=='https') {
			$API->write("=dst-port=443",false);
		}
		$API->write("=src-address-list=avisos",false);
		$API->write("=action=redirect",false);
		$API->write("=to-ports=3128",false);
		$API->write("=comment=Smartisp Avisos ".$protocol."-".$network,true);

		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;

	}

	//metodo para editar regla nat redireccionamiento a web proxy avisos pppoe
	public static function set_block_nat_ppp($API,$id,$network){

		$API->write('/ip/firewall/nat/set', false);
		$API->write("=src-address-list=avisos",false);
		$API->write("=src-address=".$network,false);
		$API->write("=action=redirect",false);
		$API->write("=to-ports=3128",false);
		$API->write('=.id='.$id,true);

		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;
	}



	//metodo para agregar regla nat redireccionamiento a web proxy avisos
	public static function add_nat_adv($API,$interface){

		$API->write("/ip/firewall/nat/add", false);
		$API->write("=chain=dstnat",false);
		$API->write("=protocol=tcp",false);
		$API->write("=dst-port=80",false);
		$API->write("=src-address-list=avisos",false);
		$API->write("=action=redirect",false);
		$API->write("=to-ports=3128",false);
		$API->write("=comment=Smartisp Avisos",true);

		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;
	}

	//metodo para agregar regla nat redireccionamiento a web proxy avisos
	public static function add_nat_adv_2($API,$interface){

		$API->write("/ip/firewall/nat/add", false);
		$API->write("=chain=dstnat",false);
		$API->write("=protocol=tcp",false);
		$API->write("=dst-port=443",false);
		$API->write("=src-address-list=avisos",false);
		$API->write("=action=redirect",false);
		$API->write("=to-ports=3128",false);
		$API->write("=comment=SmartISP Avisos",true);

		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;
	}

	//metodo para buscar regla nat redireccionamiento a web proxy avisos
	public static function find_block_nat($API,$word){

		$ID = $API->comm('/ip/firewall/nat/print', array(
			".proplist" => ".id",
			"?comment" => $word
		));

		if(count($ID)>0)
			return $ID;
		else
			return "notFound";
	}

	//metodo para editar regla nat redireccionamiento a web proxy
	public static function set_block_nat($API,$id,$lan,$control){

		$API->write('/ip/firewall/nat/set', false);

//		if ($control=='pp' || $control=='pa') {
//			$API->write('=!in-interface=',false); //set to default
//		}
//		else{
//			$API->write('=in-interface='.$lan,false);
//		}

		$API->write("=src-address-list=avisos",false);
		$API->write("=action=redirect",false);
		$API->write("=to-ports=3128",false);
		$API->write('=.id='.$id,true);

		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;
	}

	//metodo para eliminar regla nat redireccionamiento a web proxy
	public static function remove_block_nat($API,$id){

		$API->write('/ip/firewall/nat/remove', false);
		$API->write('=.id='.$id,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;
	}

	////// MANGLE RULES //////

	//metodo para acrear reglas planes en mangle para PCQ
	public static function add_mangle_postrouting($API,$plan,$srcaddress,$comment){

		$API->write('/ip/firewall/mangle/add', false);
		$API->write("=chain=postrouting",false);
		$API->write("=action=mark-packet",false);
		$API->write("=new-packet-mark=".$plan,false);
		$API->write("=dst-address-list=".$srcaddress,false);
		$API->write("=comment=".$comment,true);

		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;

	}

	//metodo para setear reglas planes en mangle para PCQ
	public static function set_mangle_postrouting($API,$id,$plan,$srcaddress,$comment){

		$API->write('/ip/firewall/mangle/set', false);
		$API->write("=.id=".$id,false);
		$API->write("=chain=postrouting",false);
		$API->write("=action=mark-packet",false);
		$API->write("=new-packet-mark=".$plan,false);
		$API->write("=dst-address-list=".$srcaddress,false);
		$API->write("=comment=".$comment,true);

		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;

	}

	//metodo para crear reglas planes en mangle para PCQ
	public static function add_mangle_forward($API,$plan,$srcaddress,$comment){

		$API->write('/ip/firewall/mangle/add', false);
		$API->write("=chain=forward",false);
		$API->write("=action=mark-packet",false);
		$API->write("=new-packet-mark=".$plan,false);
		$API->write("=src-address-list=".$srcaddress,false);
		$API->write("=comment=".$comment,true);

		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;

	}

	//metodo para eliminar reglas mangle
	public static function delete_mangle($API,$id){

		$API->write('/ip/firewall/mangle/remove', false);
		$API->write("=.id=".$id,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;
	}

	//metodo para setear reglas planes en mangle para PCQ
	public static function set_mangle_forward($API,$id,$plan,$srcaddress,$comment){

		$API->write('/ip/firewall/mangle/set', false);
		$API->write('=.id='.$id,false);
		$API->write("=chain=forward",false);
		$API->write("=action=mark-packet",false);
		$API->write("=new-packet-mark=".$plan,false);
		$API->write("=src-address-list=".$srcaddress,false);
		$API->write("=comment=".$comment,true);

		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;

	}

	//metodo para buscar regla
	public static function find_mangle($API,$word){

		$ID = $API->comm('/ip/firewall/mangle/print', array(
			".proplist" => ".id",
			"?comment" => $word
		));

		if(count($ID)>0)
			return $ID;
		else
			return "notFound";
	}

    //metodo para buscar filters block
    public static function filter_id_for_client_list($API,$list){

        $ID = $API->comm('/ip/firewall/filter/print', array(
            ".proplist" => ".id",
            "?src-address-list" => $list
        ));

        if(count($ID)>0)
            return $ID;
        else
            return "notFound";
    }

//    //metodo para añadir filters block
    public static function filter_add_for_client_list($API,$list,$comment)
    {

        $API->write("/ip/firewall/filter/add",false);

        $API->write("=chain=forward",false);
        $API->write("=action=drop",false);
        $API->write("=src-address-list=!Permitidos",false);
        $API->write("=dst-address-list=!Permitidos",false);

        $API->write("=comment=".$comment,true);
        $READ = $API->read(false);
        $ARRAY = $API->parseResponse($READ);
        return $ARRAY;
    }

    //metodo para buscar filters block
    public static function get_id_client_list_filter_block($API,$list){

        $ID = $API->comm('/ip/firewall/filter/print', array(
            ".proplist" => ".id",
            "?comment" => "Block All Others",
            "?src-address-list" => '!'.$list
        ));

        if(count($ID)>0)
            return $ID;
        else
            return "notFound";
    }
}
