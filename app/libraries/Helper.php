<?php
namespace App\libraries;
class Helper {
  
  public static function json($success, $message)
  {
    return Response::json(array(
      'success' => $success,
      'message' => $message
    ));
  }

  public static function replace_word_mangle($string,$con,$con2){

   $plan= str_replace(" ", "_",$string);
   $srcaddress = $plan;
   $plan_a = "Smartisp-".$plan."-".$con;
   $plan_b = "Smartisp-".$plan."-".$con2;

   return ["plan_in"=>$plan_a,"plan_out"=>$plan_b, "srcaddress" => $srcaddress];

 }


 public static function replace_word($string){

  $newName = str_replace(" ", "_",$string);

  return $newName;

}


public static function get_unique_array($array){

  if (count($array)>0) {
   
    $serialized_array = array_map("serialize", $array);
    foreach ($serialized_array as $key => $val) {
      $result[$val] = true;
    }

    return array_map("unserialize", (array_keys($result))); 
  }
  else{
    return [];
  }
} 

} 
