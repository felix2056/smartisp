<?php
namespace App\libraries;
/**
* files procesor
*/
class Files
{
	//metodo para eliminar archivos
	function Delete($path,$df=false){

		if (is_dir($path) === true)
    	{
	        $files = array_diff(scandir($path), array('.', '..'));

	        foreach ($files as $file)
	        {
	            $this->Delete(realpath($path) . '/' . $file);
	        }

        	if($df)
        	  return rmdir($path);
    	}

	    else if (is_file($path) === true)
	    {
	        return unlink($path);
	    }

	    return false;
	}


	//metodo para convertir bytes a kb,mb,tb
	public static function FileSizeConvert($bytes) {

    $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            ),
        );

	    foreach($arBytes as $arItem)
	    {
	        if($bytes >= $arItem["VALUE"])
	        {
	            $result = $bytes / $arItem["VALUE"];
	            $result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
	            break;
	        }
	    }

   	 return $result;
	}



	//metod para listar archivos de un directorio
	public static function listdir( $dir ) {


		$result = array();
		$contenido = array();
		$i=0;
		global $contenido;
	        if ($gd = opendir($dir)) { //Abro directorio
	                while (($ar = readdir($gd)) !== false) { //recorro su interior
	                        if(preg_match("/.*\.zip/i",$ar)) { //compruebo extension
	                                $co = file_get_contents($dir.'/'.$ar); //extraigo su contenido
	                                preg_match_all("/[^a-zA-Z]t\('(.*)'(,.+)?\)/Ui",$co,$re); //compruebo funcion t()

	                                		$result[$i]['file'] = $ar;
	                                		$result[$i]['size'] = self::FileSizeConvert(filesize($dir.'/'.$ar));
	                                		$result[$i]['date'] = date ("d/m/Y H:i:s", filemtime($dir.'/'.$ar));

											$i++;

	                                        //flush(); //imprimo nombre de archivo

	                       } elseif(is_dir($ar) && $ar != '.'  && $ar != '..') { //si es un directorio..
	                               sefl::dir($ar); //recursivamente lo inspecciono tambien
	                       }
	                }

	                closedir($gd); //cierro el recurso

	                //retornamos
	                return $result;

	        } else {
	                return array('file' => 'notfound');
	        }
    }
	//metodo para verificar si hay archivos en un directorio
	public static function check_dir( $dir ){

		$files1 = scandir($dir);

		if(empty($files1['2']))
	    return true;

		return false;

	}

}
