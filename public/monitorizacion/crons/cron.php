<?php

##################################
###       ERROR REPORTING      ###
##################################

$debug = false;

if($debug == false) {
    error_reporting(0);
    ini_set('display_errors', '0');
}

if($debug == true) {
    error_reporting(E_ALL & ~E_NOTICE);
    ini_set('display_errors', '1');
}


##################################
###       START     TIME       ###
##################################

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start_time = $time;

##################################
###       GENERAL VARS         ###
##################################

$scriptpath = dirname(__DIR__);
$result = "";

##################################
###      LOAD COMPONENTS       ###
##################################

# LOAD FUNCTIONS
require($scriptpath . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'functions.php');

# AUTOLOAD CLASSES
// composer
require $scriptpath . '/vendor/autoload.php';
use \JJG\Ping as Ping;

//other
spl_autoload_register('vendorClassAutoload');
spl_autoload_register('appClassAutoload');


# LOAD CONFIGURAGION FILE
require($scriptpath . DIRECTORY_SEPARATOR . 'config.php');

# INITIALIZE MEDOO
$database = new medoo($config);

# DATE & TIME
date_default_timezone_set(getConfigValue("timezone"));

# LOAD LANGUAGE

// get default app language
$lang = getConfigValue("default_lang");

// define language file path
$langfile = $scriptpath . DIRECTORY_SEPARATOR . "lang" . DIRECTORY_SEPARATOR . $lang . ".mo";

// define overriden language file path
$orlangfile = $scriptpath . DIRECTORY_SEPARATOR . "lang" . DIRECTORY_SEPARATOR . "override" . DIRECTORY_SEPARATOR . $lang . ".mo";

// load overriden language file (if exists)
if(file_exists($orlangfile)) {
    $streamer = new FileReader($orlangfile);
    $t = new gettext_reader($streamer);
}
// if overridden lang file does not exist, try to load normal language file (if exists)
else {
    if(file_exists($langfile)) {
        $streamer = new FileReader($langfile);
        $t = new gettext_reader($streamer);
    }
}

# TWITTER CONNECT



##################################
###         MAIN CRON          ###
##################################


# PROCESS

// check websites
$checked_websites = Website::checkAll();
$result .= "Checked $checked_websites websites.<br>";

// check checks
$checked_checks = Check::checkAll();
$result .= "Checked $checked_checks checks.<br>";



// process websites
$processed_websites = Website::processAll();
$result .= "<br>Processed $processed_websites websites.<br>";

// process checks
$processed_checks = Check::processAll();
$result .= "Processed $processed_checks checks.<br>";

// process servers
$processed_servers = Server::processAll();
$result .= "Processed $processed_servers servers.<br>";


// update geodata
//App::updateGeoData();

// purge old data
$purged_system = App::purgeSystemLogs();
$result .= "<br>Purged $purged_system system log items.<br>";

$purged_monitoring = App::purgeMonitoringHistory();
$result .= "Purged $purged_monitoring monitoring items.<br>";


##################################
###         END     TIME       ###
##################################

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$total_time = round(($finish - $start_time), 4);

$result .= "<br>Processed in $total_time seconds.<br>";

##################################
###    LOG AND PRIN RESULT     ###
##################################


$database->insert("core_cronlog", [
    "timestamp" => date('Y-m-d H:i:s'),
    "data" => $result,
    "execution_time" => $total_time,
]);


echo "<br>$result<br>";



?>
