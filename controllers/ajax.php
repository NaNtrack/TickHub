<?php

header('Content-Type: application/json');

list($ajax_section, $ajax_action ) = @explode('/',substr($_SERVER['REQUEST_URI'],1));

if ( $ajax_action == 'login' ) {
	try{
		$email = $_POST['user_email'];
		$password = $_POST['user_password'];
		$user = User::login($email, $password);
		if ( $user != null ) {
			echo json_encode(array(
				'result' => 'OK'
			));
		} else {
			echo json_encode(array(
				'result' => 'ERROR',
				'reason' => 'Invalid username or password'
			));
		}		
	} catch(Exception $ex){
		Log::getInstance()->logException($ex);
		echo json_encode(array(
			'result'	=> 'ERROR',
			'reason'	=> $ex->getMessage()
		));
	}
} elseif ( $ajax_action == 'tickpost_connect' ) {
	try{
		$user = User::getLoggedUser();
		if ( $user == null ) {
			throw new Exception("You session has expired");
		}
		
		$company = $_POST['tickspot_company'];
		$email = Utils::enforceEmailValue($_POST['tickspot_email']);
		$password = $_POST['tickspot_password'];
		
		$tickspot = new TickSpot($company);
		
		if ( $tickspot->login($email, $password) ) {
			$userData = $tickspot->getUsers();
			$tickspot->getClientsProjectsTasks();
			foreach ( $userData as $tickspotUser ) {
				if ( $email == $tickspotUser->getEmail() ) {
					if ( TICKSPOT_PASSWORD_FILTER != null ) {
						$passwordFilterClassName = TICKSPOT_PASSWORD_FILTER;
						$passwordFilter = new $passwordFilterClassName();
						$tickspotUser->setPasswordFilter($passwordFilter);
						$tickspotUser->setPassword($password);
					}
					$tickspotUser->save();
					$user->setTickSpotUserID($tickspotUser->getUserID());
					$user->setTickSpotCompany($company);
					$user->save();
					
					break;
				}
			}
			echo json_encode(array(
				'result'	=> 'OK',
			));
		} else {
			throw new Exception("Invalid username or password");
		}
	} catch(Exception $ex){
		Log::getInstance()->logException($ex);
		echo json_encode(array(
			'result'	=> 'ERROR',
			'reason'	=> $ex->getMessage()
		));
	}
} elseif ( $ajax_action == 'tickspot_cpt' ) {
	try{
		$user = User::getLoggedUser();
		if ( $user == null ) {
			throw new Exception("You session has expired");
		}
		$select = $_POST['s'];
		switch ( $select ) {
			case 'projects':
				$clientID = $_POST['c'];
				$projects = TickSpotClientsProjectsTasks::getProjects($user->getID(), $clientID);
				$projects_array = array();
				foreach ( $projects as $project ) {
					array_push($projects_array, array(
					    'id' => $project['project_id'],
					    'name' => $project['project_name']
					));
				}				
				echo json_encode(array(
				    'result'	=> 'OK',
				    'projects' => $projects_array
				));
				break;
			case 'tasks':
				$projectID = $_POST['p'];
				$tasks = TickSpotClientsProjectsTasks::getTasks($user->getID(), $projectID);
				$tasks_array = array();
				foreach ( $tasks as $task ) {
					array_push($tasks_array, array(
					    'id' => $task['task_id'],
					    'name' => $task['task_name']
					));
				}				
				echo json_encode(array(
				    'result'	=> 'OK',
				    'tasks' => $tasks_array
				));
				break;
		}
	} catch(Exception $ex){
		Log::getInstance()->logException($ex);
		echo json_encode(array(
			'result'	=> 'ERROR',
			'reason'	=> $ex->getMessage()
		));
	}
} elseif ( $ajax_action == 'github_commits' ){
	try{
		$user = User::getLoggedUser();
		if ( $user == null ) {
			throw new Exception("You session has expired");
		}
		$repo_id = $_POST['r'];
		$emails = explode(",",$_POST['e']);
		echo json_encode(array(
			'result' => 'OK',
			'commits' => GithubView::getCommits($user->getID(), $repo_id, $emails)
		));
	} catch(Exception $ex){
		Log::getInstance()->logException($ex);
		echo json_encode(array(
			'result'	=> 'ERROR',
			'reason'	=> $ex->getMessage()
		));
	}
} elseif ( $ajax_action == 'github_repo_email') {
	try{
		$user = User::getLoggedUser();
		if ( $user == null ) {
			throw new Exception("You session has expired");
		}
		$repo_id = $_POST['r'];
		echo json_encode(array(
			'result' => 'OK',
			'emails' => GithubView::getRepositoryEmails($user->getID(),$repo_id)
		));
	} catch(Exception $ex){
		Log::getInstance()->logException($ex);
		echo json_encode(array(
			'result'	=> 'ERROR',
			'reason'	=> $ex->getMessage()
		));
	}
} elseif ( $ajax_action == 'tickspot_entry' ) {
	try{
		$user = User::getLoggedUser();
		if ( $user == null ) {
			throw new Exception("You session has expired");
		}
		$taskID		= $_POST['tickspot_task'];
		@list($m,$d,$y)	= @explode("-",$_POST['tickspot_date']);
		$date		= "$y-$m-$d";
		$hour		= $_POST['tickspot_hours'];
		$minutes	= $_POST['tickspot_minutes'];
		$minutes	= ((float)$minutes * 100 / 60) /100;
		$hours		= (float)$hour + $minutes;
		$notes		= stripslashes($_POST['tickspot_message']);
		$added = TickSpot::createNewEntry($user->getID(), $taskID, $hours, $date, $notes);
		echo json_encode(array(
			'result'	=> 'OK'
		));
	} catch(Exception $ex){
		Log::getInstance()->logException($ex);
		echo json_encode(array(
			'result'	=> 'ERROR',
			'reason'	=> $ex->getMessage()
		));
	}
} elseif ( $ajax_action == 'add_commit') {
	try{
		$user = User::getLoggedUser();
		if ( $user == null ) {
			throw new Exception("You session has expired");
		}
		UserCommit::addCommit($user->getID(), $_POST['id']);
		echo json_encode(array(
			'result'	=> 'OK'
		));
	} catch(Exception $ex){
		Log::getInstance()->logException($ex);
		echo json_encode(array(
			'result'	=> 'ERROR',
			'reason'	=> $ex->getMessage()
		));
	}
} elseif ( $ajax_action == 'hide_commit') {
	try{
		$user = User::getLoggedUser();
		if ( $user == null ) {
			throw new Exception("You session has expired");
		}
		UserCommit::hideCommit($user->getID(), $_POST['id']);
		echo json_encode(array(
			'result'	=> 'OK'
		));
	} catch(Exception $ex){
		Log::getInstance()->logException($ex);
		echo json_encode(array(
			'result'	=> 'ERROR',
			'reason'	=> $ex->getMessage()
		));
	}
} else {
	echo json_encode(array(
		'result'	=> 'ERROR',
		'reason'	=> 'Invalid request'
	));
}