<?php
/*
 PHP Class to Backup Mysql Database in SQL format
 The SQL backup can be saved into a ZIP archive
 From: http://coursesweb.net/
*/
namespace App\libraries;
use mysqli;
use PDO;
use ZipArchive;

class BackupMysql {
  private $dir ='';  //folder to store ZIP archive with SQL backup
  public $lang = [];  //array with texts from "lang_...json"

  // receives languuage indice $lang, and folder-path that stores backup
  function  __construct($lang ='en', $dir ='backup'){
    $this->setLang($lang);
    if($dir !='') $this->dir = trim($dir, '/') .'/';
  }

  //set $lang with texts from passed json file
  private function setLang($lang){
    if(file_exists('lang_'. $lang .'.json')) $this->lang = json_decode(file_get_contents('lang_'. $lang .'.json'), true);
    else if(file_exists('lang_en.json')) $this->lang = json_decode(file_get_contents('lang_en.json'), true);
    if(!is_array($this->lang)){
      $this->lang = [];
      echo $this->langTxt('er_json');
    }
  }

  //return the text from $key in $lang
  public function langTxt($key){
    if(isset($this->lang[$key])) return $this->lang[$key];
    else return $key;
  }

  //get and returns array with tables name from database
  public function getTables(){
    $re = [];
    $resql = $this->sqlExec('SHOW TABLES');
    $nr = $this->num_rows;
    for($i=0; $i<$nr; $i++) $re[] = $resql[$i]['Tables_in_'. $this->mysql['dbname']];
    return $re;
  }

  //return form with checkboxes with tables from $db
  public function getListTables(){
    $tables = $this->getTables();  //store tables name from database
    $re = ['f'=>'', 'er'=>''];

    //if bot error, set form, else add error in $re['er']
    if(!$this->error){
      $nr = count($tables);
      $re['f'] ='<form action="'. $_SERVER['REQUEST_URI'] .'" method="post" id="frm_cht"><h3>'. $this->langTxt('msg_select_tbles') .'</h3><label id="ch_all"><input type="checkbox">'. sprintf($this->langTxt('msg_select_all'), $nr) .'</label><br><div class="ch_tables">';

      for($i=0; $i<$nr; $i++) {
        if($i >0 && ($i %12) ==0) $re['f'] .='</div><div class="ch_tables">';  //close Div and open the next
        $re['f'] .= '<label><input type="checkbox" name="tables[]" value="'. $tables[$i] .'">'. $tables[$i] .'</label>';
      }
      $re['f'] .='</div><br>';  //close last .ch_tables
      $re['f'] = str_ireplace('<div class="ch_tables"></div>', '', $re['f']) .'<input type="submit" value="'. $this->langTxt('msg_backup') .'"></form>';
    }
    else $re['er'] = $this->error;
    return $re;
  }

  // returns string with the backup of tables from $tables (array with tables name), in SQL format
  public function getSqlBackup($tables){
     // Introduction information
    $re = '-- # A Mysql Backup System
-- # Export created: '. date('Y/m/d') .' on ' . date('h:i'). '
-- # Database : '. $this->mysql['dbname']. PHP_EOL;
    $re .= 'SET AUTOCOMMIT = 0 ;'. PHP_EOL;
    $re .= 'SET FOREIGN_KEY_CHECKS=0 ;'. PHP_EOL;

    // Cycle through each  table
    foreach($tables as $table){
      // Add table information
      $re .= PHP_EOL .'-- # Tabel structure for table `' . $table . '`'. PHP_EOL;
      $re.= 'DROP TABLE  IF EXISTS `'.$table.'`;'. PHP_EOL;

      // get and append table-shema into code
      $shema = $this->sqlExec('SHOW CREATE TABLE '.$table);
      $re.= $shema[0]['Create Table'] .';'. PHP_EOL;
      $shema ='';  //free memory

      // Get content of each table
      $resql = $this->sqlExec('SELECT * FROM '. $table);
      if($this->num_rows >0){
        $nr = $this->num_rows;

        //get columns and start code that will insert data into table with columns got from 1st row
        $re .= PHP_EOL .'INSERT INTO `'.$table .'` (`'. implode('`, `', array_keys($resql[0])) .'`) VALUES ';

        //get data from each row and column to be added with insert
        $data_rows = [];
        for($i=0; $i<$nr; $i++){
          //get data of each column in row
          $data_cols = [];
          foreach($resql[$i] AS $k => $v) {
            $data_cols[] = is_numeric($v) ? $v :"'". addslashes($v) ."'";
          }
          $data_rows[] = '('. implode(', ', $data_cols) .')';
          $data_cols ='';  //to free memory
        }
        $resql ='';  //free memory
        $re .= implode(', '. PHP_EOL, $data_rows) .';'. PHP_EOL;
        $data_rows ='';  //free memory
      }
    }
    $re .= PHP_EOL .'SET FOREIGN_KEY_CHECKS = 1; 
COMMIT; 
SET AUTOCOMMIT = 1; '. PHP_EOL;

    return $re;
  }

  //save the backup into a ZIP archive in $dir property. Receives array with the tables name to backup
  public function saveBkZip($tables){
    if($this->dir =='' || is_writable($this->dir)){
      $file_sql ='backup-'. $this->mysql['dbname'] .'-'. date('d-m-Y'). '@'. date('h.i.s') .'.sql';  //name of the SQL file with backup put in ZIP
      $file_zip = $this->dir . $file_sql .'.zip';  //location and file-name of ZIP
      $zip = new ZipArchive();
      $zip_open = $zip->open($file_zip, ZIPARCHIVE::CREATE);
      if($zip_open){
        $zip->addFromString($file_sql, $this->getSqlBackup($tables));
        $re = sprintf($this->langTxt('ok_saved'), $file_sql);
      }
      else $re = sprintf($this->langTxt('er_saved'), $file_sql);
      $zip->close();
    }
    else $re = sprintf($this->langTxt('er_write'), $this->dir);
    return $re;
  }

  //return form with radio-buttons with the backup ZIP archive in $dir
  public function getListZip(){
    $re ='<form action="'. $_SERVER['REQUEST_URI'] .'" method="post" id="frm_zip"><h3>'. $this->langTxt('msg_select_file') .'</h3><input type="hidden" id="dir_bk" value="'. $this->dir .'"><input type="hidden" name="bmd_zip" value="restore">';  //hidden fields used when request to #get_file / #del_file

    $files = glob($this->dir .'*.zip');
    $nr = count($files);

    //traverse the files, set name & size of each file
    for($i=0; $i<$nr; $i++) {
      $file = str_ireplace($this->dir, '', $files[$i]);
      $size = filesize($files[$i]);
      $size = ($size/1024 <1) ? number_format($size, 2) .' Bytes' : (($size/1024 >=1 && $size/(1024*1024) <1) ? number_format($size/1024, 2) .' KB' : number_format($size/(1024*1024), 2) .'MB');
      $re .= '<label><input type="radio" name="file" value="'. $file .'" class="zip_files">'. $file .' (<em>'. $size .'</em>)</label>';
    }
    $btn = ($nr >0) ? '<input type="button" value="'. $this->langTxt('msg_restore_bk') .'" id="res_file"> <input type="button" value="'. $this->langTxt('msg_get_file') .'" id="get_file"> <input type="button" value="'. $this->langTxt('msg_del_file') .'" id="del_file">' : $this->langTxt('msg_nozip');

    return $re . $btn .'</form>';
  }

  //restore the backup from $zp in $dir
  public function restore($zp){
    $zip = new ZipArchive();
    if($zip->open($this->dir . $zp) === TRUE) {
      $sql = $zip->getFromIndex(0);  //Get the backup content of the first file in ZIP
      $sql = preg_replace('/^-- # .*[\r\n]*/m', '', $sql);  //removes sql comments
      $zip->close();

      //if $sql size <50 MB execute with PHP, else from Shell
      if(floor(strlen($sql)/(1024*1024)) <50){
//        $resql = $this->multiQuery($zip->open($this->dir . 'temp.spl'));
//        $sql ='';  //free memory
//        $re = ($resql) ? $this->langTxt('ok_res_backup') : $this->langTxt('er_res_backup');

          if(file_put_contents($this->dir .'temp.sql', $sql)) {
              $restore_file = realpath($this->dir . 'temp.sql');

              $cmd = "mysql -h {$this->mysql['host']} -u {$this->mysql['user']} -p{$this->mysql['pass']} {$this->mysql['dbname']} < $restore_file";

              exec($cmd, $out, $ere);

              $re = (!$ere) ? $this->langTxt('ok_res_backup') : $this->langTxt('er_res_backup');
              unlink($this->dir .'temp.sql');  //delete the temp.sql
          }
      }
      else if(function_exists('exec')){
        //put the SQL into a temp.sql
        if(file_put_contents($this->dir .'temp.sql', $sql)){
          $zip ='';  $sql ='';  //free memory

          //get mysql.exe lcation, build command that can execute sql in shell (with exec())
          $resql = $this->sqlExec("SHOW VARIABLES LIKE 'basedir'");
          $mysql_exe = $resql[0]['Value']. '/bin/mysql.exe';
          $cmd = $mysql_exe ." -h {$this->mysql['host']} --user={$this->mysql['user']} --password={$this->mysql['pass']}  -D {$this->mysql['dbname']} < ". realpath($this->dir .'temp.sql');
          exec($cmd, $out, $ere);
          $re = (!$ere) ? $this->langTxt('ok_res_backup') : $this->langTxt('er_res_backup');

          @unlink($this->dir .'temp.sql');  //delete the temp.sql
        }
        else $re = sprintf($this->langTxt('er_write_in'), $this->dir);
      }
      else $this->langTxt('er_exec');
    }
    else $re = sprintf($this->langTxt('er_open'), $zp);

    return $re;
  }

  //return the $zip file for download, in $dir
  public function getZipFile($zip){
    if(file_exists($this->dir . $zip)){
      header('Pragma: public'); // required
      header('Expires: 0');
      header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
      header('Cache-Control: private',false);    // required for certain browsers
      header('Content-Type: application/zip');
      header('Content-Disposition: attachment; filename='. $zip .';' );
      header('Content-Transfer-Encoding: binary');
      header('Content-Length: '. filesize($this->dir . $zip));
      readfile($this->dir . $zip);
      exit;
    }
    else return sprintf($this->langTxt('er_file'), $zip);
  }

  //delete $file from $dir
  public function delFile($file){
   if(@unlink($this->dir . $file)) return $this->langTxt('ok_delete');
    else return sprintf($this->langTxt('er_delete'), $file);
  }

/* PROPERTIES METHODS FOR CONNECTING WITH PDO or MySQLi AND PERFORMING SQL QUERIES */

  protected $conn_mod = 'mysqli';  // 'pdo', or 'mysqli'
  public $mysql = [];  //data for connection to database [host, user, pass, dbname]
  protected $conn = false;            // stores the connection to mysql
  public $fetch = 'assoc';        // 'assoc' - columns with named index, 'num' - columns numerically indexed, Else - both
  public $num_rows =0;  //number of rows in select results
  public $error = false;          // to store and check for errors

  //store connection data in $mysql property
  //Receives array with: ['host'=>'mysql-server', 'user'=>'user-name', 'pass'=>'password', 'dbname'=>database-name]
  public function setMysql($conn_data){
    $this->mysql = $conn_data;
  }

  // to set the connection to mysql, with PDO, or MySQLi
  protected function setConn($mysql) {
    // sets the connection method, check if can use pdo or mysqli
    if($this->conn_mod == 'pdo') {
      if(extension_loaded('PDO') === true) $this->conn_mod = 'pdo';
      else if(extension_loaded('mysqli') === true) $this->conn_mod = 'mysqli';
    }
    else if($this->conn_mod == 'mysqli') {
      if(extension_loaded('mysqli') === true) $this->conn_mod = 'mysqli';
      else if(extension_loaded('PDO') === true) $this->conn_mod = 'pdo';
    }

    if($this->conn_mod == 'pdo') $this->connPDO($mysql);
    else if($this->conn_mod == 'mysqli') $this->connMySQLi($mysql);
    else $this->setSqlError($this-langTxt('er_conn'));
  }

  // for connecting to mysql with PDO
  protected function connPDO($mysql) {
    try {
      // Connect and create the PDO object
      $this->conn = new PDO("mysql:host=".$mysql['host']."; dbname=".$mysql['dbname'], $mysql['user'], $mysql['pass']);

      // Sets to handle the errors in the ERRMODE_EXCEPTION mode
      $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      // Sets transfer with encoding UTF-8
      $this->conn->exec('SET character_set_client="utf8",character_set_connection="utf8",character_set_results="utf8";');
    }
    catch(\PDOException $e) {
      $this->setSqlError($e->getMessage());
    }
  }

  // method that create the connection to mysql with MySQLi
  protected function connMySQLi($mysql) {
    // if the connection is successfully established
    if($this->conn = new mysqli($mysql['host'], $mysql['user'], $mysql['pass'], $mysql['dbname'])) {
      $this->conn->query('SET character_set_client="utf8",character_set_connection="utf8",character_set_results="utf8";');
    }
    else if (mysqli_connect_errno()) $this->setSqlError('MySQL connection failed: '. mysqli_connect_error());

  }

  // Performs SQL queries
  private function sqlExec($sql) {
    if($this->conn === false || $this->conn === NULL) $this->setConn($this->mysql);      // sets the connection to mysql
    $this->affected_rows = 0;  // resets previous registered data
    $re = true;

    // if there is a connection set ($conn property not false)
    if($this->conn !== false) {
      // gets the first word in $sql, to determine whenb SELECT query
      $ar_mode = explode(' ', trim($sql), 2);
      $mode = strtolower($ar_mode[0]);
      $this->error = false;   // to can perform current $sql if previous has error

      // execute query
      if($this->conn_mod == 'pdo') {
        try {
          if($mode == 'select' || $mode == 'show') {
            $sqlre = $this->conn->query($sql);
            $re = $this->getSelectPDO($sqlre);
          }
          else $this->conn->exec($sql);
        }
        catch(\PDOException $e) { $this->setSqlError($e->getMessage()); }
      }
      else if($this->conn_mod == 'mysqli') {
        $sqlre = $this->conn->query($sql);
        if($sqlre){
          if($mode == 'select' || $mode == 'show') $re = $this->getSelectMySQLi($sqlre);
        }
        else {
          if(isset($this->conn->error_list[0]['error'])) $this->setSqlError($this->conn->error_list[0]['error']);
          else $this->setSqlError('Unable to execute the SQL query');
        }
      }
    }

    // sets to return false in case of error
    if($this->error !== false) $re = false;
    return $re;
  }

  // gets and returns Select results performed with PDO
  // receives the object created with exec() statement
  protected function getSelectPDO($sqlre) {
    $re = [];
    // if fetch() returns at least one row (not false), adds the rows in $re for return (numerical, and associative)
    if($row = $sqlre->fetch()){
      do {
        // check each column if it has numeric value, to convert it from "string"
        foreach($row AS $k=>$v) {
          // to get columns with string or numeric keys, according to $fetch property
          if(($this->fetch == 'assoc' && is_int($k)) || ($this->fetch == 'num' && is_string($k))) { unset($row[$k]); continue; }
          if(is_numeric($v)) $row[$k] = $v + 0;
        }
        $re[] = $row;
      }
      while($row = $sqlre->fetch());
    }
    $this->num_rows = count($re);  //number of returned rows

    return $re;
  }

  // gets and returns Select results performed with MySQLi
  // receives the object created with query() statement
  protected function getSelectMySQLi($sqlre) {
    $re = [];
    $fetch = ($this->fetch == 'assoc') ? MYSQLI_ASSOC :(($this->fetch == 'num') ? MYSQLI_NUM : MYSQLI_BOTH);
    // gets the results to return
    while($row = $sqlre->fetch_array($fetch)) {
      $re[] = $row;
    }
    $this->num_rows = count($re);  //number of returned rows

    return $re;
  }

  //used to execute multi SQL queries in one string, $sql
  public function multiQuery($sql){
    if($this->conn === false || $this->conn === NULL) $this->setConn($this->mysql);      // sets the connection to mysql
    $this->affected_rows = 0;  // resets previous registered data

    // if there is a connection set ($conn property not false)
    if($this->conn !== false){
      if($this->conn_mod == 'pdo'){
        $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);
        $re = ($this->conn->exec($sql) !== false) ? true : false;
      }
      else if($this->conn_mod == 'mysqli'){
        //execute the multiQuery and wait till executes all queries
        if($this->conn->multi_query($sql)){
          do {
            $this->conn->next_result();
          } while ($this->conn->more_results());
          $re = true;
        }
        else $re = false;
      }
    }
    else $re = false;
    return $re;
  }

  // set sql error in $error
  protected function setSqlError($err) {
    $this->error = $err ;
  }
}
