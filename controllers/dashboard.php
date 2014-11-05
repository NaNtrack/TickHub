<?php

$user = User::getLoggedUser();
if ( $user == null ) {
	header('Location: ' . SERVER_URL);
	die();
}
GithubRepository::getRepositories($user->getID());
if ( $_SERVER['REQUEST_URI'] == '/dashboard/github/success/') {
	GithubRepository::getRepositories($user->getID());
}

$ts_clients = TickSpotClientsProjectsTasks::getClients($user->getID());
$tickspot_clients = array('' => " - Select - ");
foreach ( $ts_clients as $ts_client ) {
	$tickspot_clients[$ts_client['client_id']] = $ts_client['client_name'];
}

$gh_repositories = GithubView::getRepositories($user->getID());
$github_repositories = array('' => " - Select - ");
foreach ( $gh_repositories as $gh_repository ) {
	$github_repositories[$gh_repository['repo_id']] = $gh_repository['repo_name'];
}

$github_emails = array();


$smarty->assign('page', array(
    'title' => 'Dashboard - ' . APPLICATION_NAME,
    'styles' => array(
	'style.css?m='.md5(filemtime(WEBROOT_DIR.'media'.DS.'css'.DS.'style.css')),
	'lightbox.css?m='.md5(filemtime(WEBROOT_DIR.'media'.DS.'css'.DS.'lightbox.css')),
	'dashboard.css?m='.md5(filemtime(WEBROOT_DIR.'media'.DS.'css'.DS.'dashboard.css')),
	'jquery-ui-1.8.19.custom.css'
    ),
    'scripts' => array(
	'tickhub.js?m='.md5(filemtime(WEBROOT_DIR.'media'.DS.'js'.DS.'tickhub.js')),
	'dashboard.js?m='.md5(filemtime(WEBROOT_DIR.'media'.DS.'js'.DS.'dashboard.js'))
    ),
    'user' => array(
	'id' => $user->getID(),
	'email' => $user->getEmail(),
	'github_ok' => $user->getGithubAccessToken() != null,
	'tickspot_ok' => $user->getTickSpotUserID() != null,
	'github_authorize_url' => Github::getAuthorizationURL($user->getID()),
	'tickspot_clients' => $tickspot_clients,
	'github_repositories' => $github_repositories,
	'github_emails' => $github_emails
    )
));


$smarty->display('dashboard.tpl');