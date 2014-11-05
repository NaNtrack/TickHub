<?php

require('../configs/config.php');

if(!defined('TICKHUB') || strpos($_SERVER['REQUEST_URI'], basename(__FILE__)) ) {
	header("Location: ".SERVER_URL, 301);
	exit;
}

$uri = $_SERVER['REQUEST_URI'];
$controller = '';

if ( strpos($uri, '/tickspot') === 0 ) {
	$controller = 'tickspot.php';
} elseif( strpos($uri, '/cron') === 0 ) {
	//check referrer: must be LOCALHOST
	Log::getInstance()->log("Executing cron job from " .$_SERVER['REMOTE_ADDR']);
	if ( !in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1','184.106.151.152')) ) {
		header('HTTP/1.0 404 Not Found');
		require CONTROLLER_DIR.'404.php';
		exit;
	}
	$controller = 'cron.php';
} elseif( strpos($uri, '/github') === 0 ) {
	$controller = 'github.php';
} elseif( strpos($uri, '/login') === 0 ) {
	$controller = 'login.php';
} elseif( strpos($uri, '/logout') === 0 ) {
	$controller = 'logout.php';
} elseif( strpos($uri, '/dashboard') === 0 ) {
	$controller = 'dashboard.php';
} elseif( strpos($uri, '/settings') === 0 ) {
	$controller = 'settings.php';
} elseif( strpos($uri, '/login') === 0 ) {
	$controller = 'login.php';
} elseif( strpos($uri, '/signup') === 0 ) {
	$controller = 'signup.php';
} elseif( strpos($uri, '/ajax') === 0 ) {
	$controller = 'ajax.php';
}

unset ($uri);
if ( strlen($controller) > 0 ) {
	require_once CONTROLLER_DIR.$controller;
} else {
	header('HTTP/1.0 404 Not Found');
	require CONTROLLER_DIR.'404.php';
}