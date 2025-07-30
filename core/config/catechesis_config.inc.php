<?php
// This is just a minimal configuration file.
// Most of the configuration keys are sensitive, and thus were moved to the catechesis data folder
// (which should not be accessible through a browser).


//Catechesis domain
//	Domain name of your site.
//	This must be the same domain name that the browser uses to
//	fetch your website, without the protocol specifier (don't use 'http(s)://').
//	For development on the local machine, use 'localhost'.
//	Takes the same format as the 'domain' parameter of the PHP setcookie function.
define('CATECHESIS_DOMAIN', 'localhost');

//HTTPS
//	Set this to true if your site uses HTTPS, to redirect all pages
//	automatically to HTTPS. A valid certificate must be present in the server
//	to avoid showing security warnings to the client.
define('CATECHESIS_HTTPS', true);

//Base URL
//	The base URL where Catechesis is installed.
//	If you installed it in a subdirectory of your site, such as '/catechesis',
//	then the URL would be 'https://CATECHESIS_DOMAIN/catechesis'.
// NOTE! If you use https protocol, please also ensure that you set define('UL_HTTPS', true) in ulogin/config/main.inc.php.
define('CATECHESIS_BASE_URL', 'https://localhost/catechesis');

//Root directory
//	The server directory where the Catechesis root is located.
// NOTE! Please also set the .htaccess file in the CatecheSis root to point to the page 'erro404.php'
// with a path relative to Apache's document root (NOT CatecheSis document root) and beginning with '/'.
if (!defined('CATECHESIS_ROOT_DIRECTORY')) {
    define('CATECHESIS_ROOT_DIRECTORY', realpath(__DIR__ . '/../../') . '/');
}


//CatecheSis data directory
//  The server directory where user-generated data is stored.
//  This directory should be outside the public_html folder, to guarantee it is NOT accessible through a browser.
if (!defined('CATECHESIS_DATA_DIRECTORY')) {
    $dataDir = getenv('CATECHESIS_DATA_DIRECTORY');
    if ($dataDir === false || $dataDir === '') {
        $dataDir = 'C:/xampp/catechesis-data';
    }
    define('CATECHESIS_DATA_DIRECTORY', $dataDir);
}


// Load the remaining configurations from file if available
$shadowConfig = constant('CATECHESIS_DATA_DIRECTORY') . '/config/catechesis_config.shadow.php';
if (file_exists($shadowConfig)) {
    require_once($shadowConfig);
}

// ---------------------------------------------------------------------------
// Provide dummy values for configuration constants when running tests
// ---------------------------------------------------------------------------
if (!defined('CATECHESIS_HOST')) define('CATECHESIS_HOST', 'localhost');
if (!defined('CATECHESIS_DB'))   define('CATECHESIS_DB',   'catechesis');

if (!defined('USER_DEFAULT_READ'))    define('USER_DEFAULT_READ',    'test');
if (!defined('PASS_DEFAULT_READ'))    define('PASS_DEFAULT_READ',    'test');
if (!defined('USER_DEFAULT_EDIT'))    define('USER_DEFAULT_EDIT',    'test');
if (!defined('PASS_DEFAULT_EDIT'))    define('PASS_DEFAULT_EDIT',    'test');
if (!defined('USER_DEFAULT_DELETE'))  define('USER_DEFAULT_DELETE',  'test');
if (!defined('PASS_DEFAULT_DELETE'))  define('PASS_DEFAULT_DELETE',  'test');
if (!defined('USER_GROUP_MANAGEMENT')) define('USER_GROUP_MANAGEMENT', 'test');
if (!defined('PASS_GROUP_MANAGEMENT')) define('PASS_GROUP_MANAGEMENT', 'test');
if (!defined('USER_USER_MANAGEMENT'))  define('USER_USER_MANAGEMENT',  'test');
if (!defined('PASS_USER_MANAGEMENT'))  define('PASS_USER_MANAGEMENT',  'test');
if (!defined('USER_LOG'))              define('USER_LOG',              'test');
if (!defined('PASS_LOG'))              define('PASS_LOG',              'test');
if (!defined('USER_LOG_CLEAN'))        define('USER_LOG_CLEAN',        'test');
if (!defined('PASS_LOG_CLEAN'))        define('PASS_LOG_CLEAN',        'test');
if (!defined('USER_CONFIG'))           define('USER_CONFIG',           'test');
if (!defined('PASS_CONFIG'))           define('PASS_CONFIG',           'test');
if (!defined('USER_ONLINE_ENROLLMENT')) define('USER_ONLINE_ENROLLMENT', 'test');
if (!defined('PASS_ONLINE_ENROLLMENT')) define('PASS_ONLINE_ENROLLMENT', 'test');
if (!defined('USER_CAPTCHA'))          define('USER_CAPTCHA',          'test');
if (!defined('PASS_CAPTCHA'))          define('PASS_CAPTCHA',          'test');
if (!defined('USER_UL_AUTH'))          define('USER_UL_AUTH',          'test');
if (!defined('PASS_UL_AUTH'))          define('PASS_UL_AUTH',          'test');
if (!defined('USER_UL_UPDATE'))        define('USER_UL_UPDATE',        'test');
if (!defined('PASS_UL_UPDATE'))        define('PASS_UL_UPDATE',        'test');
if (!defined('USER_UL_DELETE'))        define('USER_UL_DELETE',        'test');
if (!defined('PASS_UL_DELETE'))        define('PASS_UL_DELETE',        'test');
if (!defined('USER_UL_SESSION'))       define('USER_UL_SESSION',       'test');
if (!defined('PASS_UL_SESSION'))       define('PASS_UL_SESSION',       'test');
if (!defined('USER_UL_LOG'))           define('USER_UL_LOG',           'test');
if (!defined('PASS_UL_LOG'))           define('PASS_UL_LOG',           'test');

// Provide dummy values for optional configuration constants when running tests
if (!defined('CATECHESIS_UL_SITE_KEY')) {
    define('CATECHESIS_UL_SITE_KEY', 'test-key');
}
?>
