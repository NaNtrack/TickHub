<?php

if ( strpos($_SERVER['REQUEST_URI'],'/tickspot/connect/') === 0 ) {
	$smarty->display('lightbox/tickspot_connect.tpl');
} else {
	header('Location: ' .SERVER_URL.'dashboard/');
}