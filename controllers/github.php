<?php

if ( strpos($_SERVER['REQUEST_URI'],'/github/callback/') === 0 ) {
	Log::getInstance()->log("Github Callback: " . print_r($_GET, true));
	$ref = isset($_GET['ref'])?$_GET['ref']:null;
	if ( $ref != null ) {
		$user = null;
		try{
			$user = new User($ref);
		} catch (Exception $ex) {
			Log::getInstance()->log("[github callback] Exception using ref '$ref' {$ex->getMessage()}");
			$user = null;
		}
		if ( $user != null ) {
			$code = isset($_GET['code'])?$_GET['code']:null;
			if ( $code != null ) {
				$github = new Github($user->getID());
				$accessToken = $github->getAccessToken($code);
				header('Location: ' .SERVER_URL.'dashboard/github/'.($accessToken!= null?'success':'invalid').'/');
				exit;
			} else {
				header('Location: ' .SERVER_URL.'dashboard/github/invalid_code/');
				exit;
			}
		}
	}
	header('Location: ' .SERVER_URL.'dashboard/github/error/');
	exit;
} else {
	header('Location: ' .SERVER_URL.'dashboard/');
}
