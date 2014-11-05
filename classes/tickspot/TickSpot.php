<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * TickSpot main class
 *
 * @author jaraya
 */
class TickSpot {
	
	/**
	 *
	 * @var string
	 */
	private $companyName;
	
	private $email;
	
	private $password;

	
	public function __construct( $companyName ) {
		if ( trim ( $companyName) == '' ) {
			throw new Exception("No company name specified");
		}
		$this->companyName = $companyName;
	}
	
	
	public function login ($email, $password) {
		$email = Utils::enforceEmailValue($email);
		$params = array(
		    'email' => $email,
		    'password' => $password
		);
		$xml = $this->xmlRequest(TICKSPOT_USERS_URL, $params);
		if ( $xml != null ) {
			$this->email = $email;
			$this->password = $password;
			return true;
		}
		return false;
	}
	
	
	/**
	 * Returns the company URL
	 *
	 * @return string The company URL
	 */
	public function getCompanyURL () {
		if ( $this->company_url == null ) {
			$this->company_url = str_replace('{company', $this->companyName, TICKSPOT_COMPANY_URL);
		}
		return $this->company_url;
	}
	
	
	private function xmlRequest ( $url, $params ) {
		$url = str_replace('{company}', $this->companyName, $url);
		$httpClient = new HttpClient($url, HttpClient::METHOD_GET, $params);
		try{
			$xml = $httpClient->doGetRequest();
			return $xml;
		} catch(Exception $ex) {
			Log::getInstance()->logException($ex);
			return null;
		} 
		return null;
	}
	
	
	public function getClientsProjectsTasks ( ) {
		$params = array(
		    'email' => $this->email,
		    'password' => $this->password
		);
		$userID = TickSpotUser::getUserIDByEmail($this->email);
		$xml = $this->xmlRequest(TICKSPOT_CLIENTS_PROJECTS_TASKS_URL, $params);
		$json = @json_decode(@json_encode(simplexml_load_string($xml)),true);
		return TickSpotClient::parseClients($json, $userID);
		
	}
	
	
	public function getUsers ( $project_id = null )  {
		$params = array(
		    'email' => $this->email,
		    'password' => $this->password
		);
		
		if ( $project_id !== null ) {
			$params['project_id'] = (int) $project_id;
		}
		$xml = $this->xmlRequest(TICKSPOT_USERS_URL, $params);
		$json = @json_decode(@json_encode(simplexml_load_string($xml)),true);
		return TickSpotUser::parseUsers($json);
	}
	
	
	public static function cron () {
		$pdo = Database::getInstance()->getConnection();
		$sql = 'SELECT * FROM ' . User::VIEW_TICKSPOT_USERS;
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if ( $rows && count($rows) > 0 ) {
			foreach ( $rows as $row ) {
				$company = $row['tickspot_company'];
				$email = $row['email'];
				$passwordFilterClassName = TICKSPOT_PASSWORD_FILTER;
				$passwordFilter = new $passwordFilterClassName();
				$password = $passwordFilter->decode($row['password']);
				$tickspot = new TickSpot($company);
				if ( $tickspot->login($email, $password) ) {
					$userData = $tickspot->getUsers();
					$tickspot->getClientsProjectsTasks();
				}
			}
		}
	}
	
	/**
	 * Adds a new tickspot entry for that user
	 *
	 * @param int $userID
	 * @param int $taskID
	 * @param float $hours
	 * @param string $date
	 * @param string $notes
	 * @return bool
	 */
	public static function createNewEntry ( $userID, $taskID, $hours, $date, $notes ) {
		$pdo = Database::getInstance()->getConnection();
		$sql = 'SELECT * FROM ' . User::VIEW_TICKSPOT_USERS . ' WHERE user_id = :user_id ';
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array(':user_id' => $userID));
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if ( $rows && count($rows) > 0 ) {
			$row = $rows[0];
			$passwordFilter = new MyPasswordFilter();
			$params = array(
			    'email' => $row['email'],
			    'password' => $passwordFilter->decode($row['password']),
			    'task_id' => $taskID,
			    'hours' => $hours,
			    'date' => $date,
			    'notes' => $notes
			);
			$tickspot = new TickSpot($row['tickspot_company']);
			$xml = $tickspot->xmlRequest(TICKSPOT_CREATE_ENTRY_URL, $params);
			if ( $xml == null ) {
				throw new Exception('Unable to create the tickspot entry.');
			}
			return true;
			Log::getInstance()->log(print_r($xml, true));
			$json = @json_decode(@json_encode(simplexml_load_string($xml)),true);
			return $json;
		}
		return false;
	}
	
	
}

?>
