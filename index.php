<?php
session_start();

// Get our environment
define('DIR', realpath(getcwd() . '/../'));

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

// Split URI path
function getPath()
{
    list($path, $queryString) = explode('?', $_SERVER['REQUEST_URI'] ?? '');
    $path = ltrim($path ?? '', '/');
    $path = preg_replace("/[^a-z\d\/_.-]+/", '', $path);
    $path = preg_replace("/\.+/", ".", $path);
    return $path;
}
$url = explode('/', getPath());
$thisUrl = 'https://' . $_SERVER['HTTP_HOST'] ?? 'https://blog.designly.biz';
$thisUrl .= $_SERVER['REQUEST_URI'] ?? '';
define('THIS_URL', $thisUrl);

// Log dump function
function logDump($data)
{
    file_put_contents('/tmp/blogLog.txt', print_r($data, 1));
}

// Determine route file to import
if (!count($url) || empty($url[0])) {
    include DEFAULT_ROUTE_FILE;
} else {
    $filePath = DIR . '/pages/' . $url[0] . '.php';

    // Validate the file path
    if (file_exists($filePath) && is_file($filePath) && is_readable($filePath)) {
        // Include the file
        include $filePath;
    } else {
        // Display a 404 page
        include DEFAULT_ROUTE_FILE;
    }
}
