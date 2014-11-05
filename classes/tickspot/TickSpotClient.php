<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TickSpotClient
 *
 * @author jaraya
 */
class TickSpotClient {
	
	const TABLE = 'tickspot_client';
	const TABLE_USER_CLIENT = 'tickspot_user_client';
	
	/**
	 *
	 * @var int
	 */
	private $id;
	
	/**
	 *
	 * @var int
	 */
	private $client_id;
	
	/**
	 *
	 * @var string
	 */
	private $name;
	
	/**
	 *
	 * @var array
	 */
	private $projects;
	
	
	/**
	 * Returns the clients found in the xml
	 *
	 * @param string $data
	 * @param string $userID The user ID
	 * @return array A list of TickSpotClient objects
	 */
	public static function parseClients ($data, $userID)  {
		$clients = array();
		
		if ( !is_array($data) || !isset ($data['client']) ) {
			return $clients;
		}
		
		if ( isset($data['client']['id']) ) {//just one client
			$c = $data['client'];
			$client = new TickSpotClient();
			$client->name = $c['name'];
			$client->client_id = $c['id'];
			$client->projects = TickSpotProject::parseProjects($c['projects']);
			$client->save();
			TickSpotUser::updateUserPermisions($userID, self::TABLE_USER_CLIENT, 'client_id', $client->client_id);
			array_push($clients, $client);
		} else {//2 or more clients
			foreach ( $data['client'] as $i => $c ) {
				$client = new TickSpotClient();
				$client->name = $c['name'];
				$client->client_id = $c['id'];
				$client->projects = TickSpotProject::parseProjects($c['projects'], $userID);
				$client->save();
				TickSpotUser::updateUserPermisions($userID, self::TABLE_USER_CLIENT, 'client_id', $client->client_id);
				array_push($clients, $client);
			}
		}
		return $clients;
	}
	
	
	/**
	 * Returns the projects associated with this client
	 *
	 * @return array A list of TickSpotProject objects associated with this client
	 */
	public function getProjects () {
		return $this->projects;
	}
	
	
	public function save () {
		$pdo = Database::getInstance()->getConnection();
		
		if ( !$this->client_id ) {
			throw new Exception("Invalid tickspot client id");
		}
		
		$this->checkID();
		
		$sql = ($this->id ? 'UPDATE ' : 'INSERT INTO ') . self::TABLE . ' SET ';
		$sql .= 'client_id	= :client_id,
			 name		= :name ';
		$params = array(
		    ':client_id' => $this->client_id,
		    ':name'	 => $this->name
		);
		
		if ( $this->id ) {
			$sql .= ' WHERE id = :id ';
			$params[':id'] = $this->id;
		}
		
		
		$stmt = $pdo->prepare($sql);
		$stmt->execute($params);
		$rows_affected = $stmt->rowCount();
		if ($this->id == null && $rows_affected == 1) {
			$this->id = $pdo->lastInsertId();
		}
		
		foreach ( $this->projects as $project ) {
			$project->save();
		}
		
		return ($rows_affected == 1 || $rows_affected == 0);
	}
	
	
	private function checkID () {
		$pdo = Database::getInstance()->getConnection();
		$sql = "SELECT id FROM " . self::TABLE . " WHERE client_id = :client_id ";
		$stmt = $pdo->prepare($sql);
		$sucess = $stmt->execute(array(':client_id' => $this->client_id));
		if ($sucess) {
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row && count($row) > 0) {
				$this->id = $row['id'];
			}
		}
	}
	
}

?>
