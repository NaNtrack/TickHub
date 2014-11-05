<?php

$signed = false;
$signing_error = '';
if ( isset($_POST) && isset($_POST['btn_submit']) ) {
	$email	= $_POST['_email'];
	$name	= $_POST['_name'];
	$pass	= trim($_POST['_pass']);
	$pass2	= trim($_POST['_pass_confirm']);
	
	$smarty->assign('_name', $name);
	$smarty->assign('_email', $email);
	
	$email = filter_var($email, FILTER_VALIDATE_EMAIL);
	if ( $email == false )
		$signing_error = 'Please especify a valid email'.PHP_EOL;
	
	if ( strlen($pass)  == 0 )
		$signing_error .= 'You need to specify a password'.PHP_EOL;
	elseif ( strlen($pass2) == 0)
		$signing_error .= 'You need to repeat your password'.PHP_EOL;
	elseif ( $pass != $pass2 )
		$signing_error .= 'Pasword does not match the confirm password'.PHP_EOL;
	
	if ( strlen($signing_error) == 0 ) {
		//add the user to the database
		$user = new User();
		$user->setGivenName($name);
		$user->setEmail($email);
		$user->setPassword($pass);
		$user->save();
		$logged = User::login($email, $pass);
		if ( $logged != null ) {
			header('Location: ' . SERVER_URL . 'dashboard/');
			exit;
		}
	} else
		$smarty->assign('signing_error', nl2br ($signing_error));
	
}


$smarty->assign('signed', $signed);
$smarty->assign('page', array(
    'title' => 'Signup - ' . APPLICATION_NAME,
    'styles' => array(
	'style.css?m='.md5(filemtime(WEBROOT_DIR.'media'.DS.'css'.DS.'style.css')),
	'lightbox.css?m='.md5(filemtime(WEBROOT_DIR.'media'.DS.'css'.DS.'lightbox.css'))
    ),
    'scripts' => array(
	'tickhub.js',
    )
));

$smarty->display('signup.tpl');