<?php
namespace App\libraries;
/**
* ADDRESSES FUNCTIONS
*/
class PermitidosList
{
	//metodo para agregar ip/address a mikrotik
	public static function add($API,$data, $debug, $error){
        $ADDLIST = Firewall::get_id_address_list_name($API, $data['oldtarget'], 'Permitidos');

        if($ADDLIST != 'notFound'){
            //editamos a address list activamos
            $ADDLIST = Firewall::set_address_list($API,$ADDLIST[0]['.id'],$data['newtarget'],'Permitidos','false',$data['name']);
        } else {
            $ADDLIST = Firewall::add_address_list($API, $data['newtarget'], 'Permitidos', 'false', $data['name']);

        }

        if ($debug == 1) {
            $msg = $error->process_error($ADDLIST);
            if ($msg) {
                return $msg;
            }
        }

	}
	//metodo para agregar ip/address a mikrotik
	public static function remove($API,$data, $debug, $error){
        $ADDLIST = Firewall::get_id_address_list_name($API, $data['address'], 'Permitidos');

        if($ADDLIST != 'notFound'){
            //editamos a address list activamos
            $ADDLIST = Firewall::remove_address_list($API,$ADDLIST[0]['.id']);
        }

        if ($debug == 1) {
            $msg = $error->process_error($ADDLIST);
            if ($msg) {
                return $msg;
            }
        }

	}

    //metodo para agregar ip/address a mikrotik
    public static function AddAddressList($API,$data, $debug, $error){
        $ADDLIST = Firewall::get_id_address_list_name($API, $data['address'], 'Permitidos');

        if($ADDLIST == 'notFound'){
            $ADDLIST = Firewall::add_address_list($API, $data['address'], 'Permitidos', 'false', $data['name']);
        }

        if ($debug == 1) {
            $msg = $error->process_error($ADDLIST);
            if ($msg) {
                return $msg;
            }
        }

    }

    public static function checkRuleForClientList($API, $list, $debug, $error)
    {
        $list = Firewall::get_id_client_list_filter_block($API, $list);

        if($list == 'notFound') {
            $ADDLIST = Firewall::filter_add_for_client_list($API,$list,'Block All Others');

            if ($debug == 1) {
                $msg = $error->process_error($ADDLIST);
                if ($msg) {
                    return $msg;
                }
            }
        }

    }
}
