<?php
$TIMER_starttime = microtime( true );
global $TIMER_starttime;
session_start();
header('Content-type: text/html; charset=utf8');

define('TICKHUB', true);

define('DS', DIRECTORY_SEPARATOR);

define('THHHTP', $_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://');

/**
 * The Document Root directory (with trailing slash)
 */
define('WEBROOT_DIR'	, dirname(dirname(__FILE__)).DS.'webroot'.DS );

/**
 * Server URL
 */
define('SERVER_URL'	, THHHTP.$_SERVER['SERVER_NAME'].'/' );

/**
 * The classes directory
 */
define('CLASSES_DIR'	, dirname(dirname(__FILE__)).DS.'classes'.DS);

/**
 * The classes directory
 */
define('LIBS_DIR'	, dirname(dirname(__FILE__)).DS.'libs'.DS);

/**
 * The configuration directory
 */
define('CONFIG_DIR'	, dirname(dirname(__FILE__)).DS.'configs'.DS);

/**
 * The configuration directory
 */
define('LOGS_DIR'	, dirname(dirname(__FILE__)).DS.'logs'.DS);

/**
 * The controllers directory
 */
define('CONTROLLER_DIR', dirname(dirname(__FILE__)).DS.'controllers'.DS);

/**
 * The Smarty Template Engine Directory (with trailing slash)
 */
define('SMARTY_DIR'	, LIBS_DIR.'smarty'.DS);

/**
 * The Smarty Template directory
 */
define('SMARTY_TEMPLATE_DIR', dirname(dirname(__FILE__)).DS.'templates'.DS);

/**
 * The complied templates directory for Smarty (must be writeable)
 */
define('SMARTY_COMPILE_DIR' , dirname(dirname(__FILE__)).DS.'templates_c'.DS);

/**
 * The config directory for Smarty
 */
define('SMARTY_CONFIG_DIR'  , dirname(dirname(__FILE__)).DS.'config'.DS);

/**
 * The cache directory for Smarty (must be writeable)
 */
define('SMARTY_CACHE_DIR'   , dirname(dirname(__FILE__)).DS.'cache'.DS);


spl_autoload_register('tickhub_autoload');

//load configuration
$_Conf = false;

if ( function_exists('apc_fetch') ) {
	$_Conf = apc_fetch('tickhub_configuration');
}

if ( $_Conf === false ) {
	$_Conf = parse_ini_file(CONFIG_DIR.'tickhub.ini', true);
	if (function_exists('apc_store') ) {
		apc_store('tickhub_configuration', $_Conf, 86400);
	}
}

//[general]
define('APPLICATION_NAME'	, $_Conf['general']['application_name']);
define('APPLICATION_DESCRIPTION', $_Conf['general']['description']);
define('APPLICATION_KEYWORDS'	, $_Conf['general']['keywords']);
define('APPLICATION_AUTHOR'	, $_Conf['general']['author']);
define('LOG_FILE'		, LOGS_DIR.$_Conf['general']['log_file']);

//[github]
define('GITHUB_CLIENT_ID'	, $_Conf['github']['client_id']);
define('GITHUB_SECRET'		, $_Conf['github']['secret']);
define('GITHUB_AUTHORIZE_URL'	, $_Conf['github']['authorize_url']);
define('GITHUB_ACCESS_TOKEN_URL', $_Conf['github']['access_token_url']);
define('GITHUB_CALLBACK_URL'	, $_Conf['github']['callback_url']);
define('GITHUB_API_URL'		, $_Conf['github']['api_url']);


//[tickspot]
define('TICKSPOT_COMPANY_URL'			, $_Conf['tickspot']['company_url']);
define('TICKSPOT_CLIENTS_URL'			, $_Conf['tickspot']['clients_url']);
define('TICKSPOT_PROJECTS_URL'			, $_Conf['tickspot']['projects_url']);
define('TICKSPOT_TASKS_URL'			, $_Conf['tickspot']['tasks_url']);
define('TICKSPOT_CLIENTS_PROJECTS_TASKS_URL'	, $_Conf['tickspot']['clients_projects_tasks_url']);
define('TICKSPOT_ENTRIES_URL'			, $_Conf['tickspot']['entries_url']);
define('TICKSPOT_RECENT_TASKS_URL'		, $_Conf['tickspot']['recent_tasks_url']);
define('TICKSPOT_USERS_URL'			, $_Conf['tickspot']['users_url']);
define('TICKSPOT_CREATE_ENTRY_URL'		, $_Conf['tickspot']['create_entry_url']);
define('TICKSPOT_UPDATE_ENTRY_URL'		, $_Conf['tickspot']['update_entry_url']);
define('TICKSPOT_PASSWORD_FILTER'		, $_Conf['tickspot']['password_filter']);

//[database]
define('DATABASE_HOST'	, $_Conf['database']['host']);
define('DATABASE_DBNAME', $_Conf['database']['name']);
define('DATABASE_USER'	, $_Conf['database']['user']);
define('DATABASE_PASS'	, $_Conf['database']['pass']);

unset($_Conf);

function tickhub_autoload($className){
  	$fileName = loadClass($className, CLASSES_DIR);
  	if (file_exists($fileName))
  		require_once $fileName;
}

function loadClass($className, $baseDir){
	$dirHandle = opendir($baseDir);
	$theFile = false;
	while ( ($file = readdir($dirHandle)) != false ) {
		if ( $file != '.' && $file != '..' ) {
			if( is_dir($baseDir.$file) ){
				$theFile = loadClass($className, $baseDir.$file.DIRECTORY_SEPARATOR);
				if ( $theFile ) {
					return $theFile;
				}
			} else {
				$base = basename($file);
				if ( $base === $className.'.php' ) {
					closedir($dirHandle);
					return $baseDir.DIRECTORY_SEPARATOR.$file;
				}
			}
		}
	}
	closedir($dirHandle);
	return $theFile;
}

$user = User::getLoggedUser();
if ( $user != null ) {
	$session = new UserSession($_COOKIE['customer']);
	$session->setLastActivityDate(gmdate("Y-m-d H:i:s"));
	$session->save();
	unset($session);
}
unset ($user);

//Load the smarty Engine
require SMARTY_DIR.'Smarty.class.php';

$smarty = new Smarty();

$smarty->template_dir = SMARTY_TEMPLATE_DIR;
$smarty->compile_dir  = SMARTY_COMPILE_DIR;
$smarty->config_dir   = SMARTY_CONFIG_DIR;
$smarty->cache_dir    = SMARTY_CACHE_DIR;

/*Initial configuration*/
$smarty->assign('application', array(
    'name' => APPLICATION_NAME,
    'home_url' => SERVER_URL,
    'url' => $_SERVER['REQUEST_URI']
));
$smarty->assign('head', array(
    'description' => APPLICATION_DESCRIPTION,
    'keywords' => APPLICATION_KEYWORDS,
    'author' => APPLICATION_AUTHOR
));


function getTimerTime( $fromTime = null ) {
	global $TIMER_starttime;
	$end = microtime( true );
	$totaltime = $end - ($fromTime !== null ? $fromTime : $TIMER_starttime);
	$totaltime = round( $totaltime, 5 );
	return $totaltime;
}

