<?php
namespace App\libraries;
class Ipbin{

	public static function ip2bin($ip)
	{
	    if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false)
	        //return sprintf("%032s",base_convert(ip2long($ip),10,2));
	        return base_convert(ip2long($ip),10,2);
	    if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false)
	        return false;
	    if(($ip_n = inet_pton($ip)) === false) return false;
	    $bits = 15;
	    $ipbin = '';
	    while ($bits >= 0)
	    {
	        $bin = sprintf("%08b",(ord($ip_n[$bits])));
	        $ipbin = $bin.$ipbin;
	        $bits--;
	    }
	    return $ipbin;
	}

	public static function bin2ip($bin)
	{
	   if(strlen($bin) <= 32)
	       return long2ip(base_convert($bin,2,10));
	   if(strlen($bin) != 128)
	       return false;
	   $pad = 128 - strlen($bin);
	   for ($i = 1; $i <= $pad; $i++)
	   {
	       $bin = "0".$bin;
	   }
	   $bits = 0;
	   $ipv6 = '';
	   while ($bits <= 7)
	   {
	       $bin_part = substr($bin,($bits*16),16);
	       $ipv6 .= dechex(bindec($bin_part)).":";
	       $bits++;
	   }
	   return inet_ntop(inet_pton(substr($ipv6,0,-1)));
	}

	public static function ip2hex($ip)
	{
	    if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false)
	        return sprintf("%08x",ip2long($ip));
	    if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false)
	        return false;
	    if(($ip_n = inet_pton($ip)) === false) return false;
	    $bits = 15;
	    $ipbin = '';
	    while ($bits >= 0)
	    {
	        $bin = sprintf("%02x",(ord($ip_n[$bits])));
	        $ipbin = $bin.$ipbin;
	        $bits--;
	    }
	    return $ipbin;
	}

	public static function hex2ip($bin)
	{
	   if(strlen($bin) <= 8)
	       return long2ip(base_convert($bin,16,10));
	   if(strlen($bin) != 32)
	       return false;
	   $pad = 32 - strlen($bin);
	   for ($i = 1; $i <= $pad; $i++)
	   {
	       $bin = "0".$bin;
	   }
	   $bits = 0;
	   $ipv6 = '';
	   while ($bits <= 7)
	   {
	       $bin_part = substr($bin,($bits*4),4);
	       $ipv6 .= $bin_part.":";
	       $bits++;
	   }
	   return inet_ntop(inet_pton(substr($ipv6,0,-1)));
	}

	public static function getclassip($mask) {
		switch ($mask) {
			//Class A
			case '8':
				return 'A';
				break;
			case '9':
				return 'A';
				break;
			case '10':
				return 'A';
				break;
			case '11':
				return 'A';
				break;
			case '12':
				return 'A';
				break;
			case '13':
				return 'A';
				break;
			case '14':
				return 'A';
				break;
			case '15':
				return 'A';
				break;
			//Class B
			case '16':
				return 'B';
				break;
			case '17':
				return 'B';
				break;
			case '18':
				return 'B';
				break;
			case '19':
				return 'B';
				break;
			case '20':
				return 'B';
				break;
			case '21':
				return 'B';
				break;
			case '22':
				return 'B';
				break;
			case '23':
				return 'B';
				break;
			//Class C
			case '24':
				return 'C';
				break;
			case '25':
				return 'C';
				break;
			case '26':
				return 'C';
				break;
			case '27':
				return 'C';
				break;
			case '28':
				return 'C';
				break;
			case '29':
				return 'C';
				break;
			case '30':
				return 'C';
				break;
		}
	}


}


