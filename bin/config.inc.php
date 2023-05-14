<?php
// Get our environment
define('DIR', realpath(__DIR__ . '/../'));

// Load deps
require_once(DIR . '/vendor/autoload.php');

// Load config file
require_once(DIR . '/config.php');

// Autoload Classes
function autoload($className)
{
    include DIR . '/lib/' . $className . '.class.php';
}
spl_autoload_register('autoload');

const LOGFILE = '/tmp/blogBuildLog.txt';

function doLog($txt)
{
    $mess = date('m/d/Y h:i:s') . ":\n" . $txt . "\n";
    echo $mess;
    file_put_contents(LOGFILE, $mess, FILE_APPEND);
}
