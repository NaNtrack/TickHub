<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TickSpotClientsProjectsTasks
 *
 * @author jaraya
 */
class TickSpotClientsProjectsTasks {
	
	const TABLE = 'v_clients_projects_tasks';
	
	public static function getClients ( $userID ) {
		$pdo = Database::getInstance()->getConnection();
		$sql = "SELECT client_id, client_name FROM " . self::TABLE . " WHERE user_id = :user_id ";
		$stmt = $pdo->prepare($sql);
		$sucess = $stmt->execute(array(':user_id' => $userID));
		if ($sucess) {
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if ($rows && count($rows) > 0) {
				return $rows;
			}
		}
		return array();
		
	}
	
	public static function getProjects ( $userID, $clientID ) {
		$pdo = Database::getInstance()->getConnection();
		$sql = "SELECT DISTINCT project_id, project_name FROM " . self::TABLE . " WHERE user_id = :user_id and client_id = :client_id";
		$stmt = $pdo->prepare($sql);
		$sucess = $stmt->execute(array(
			':user_id' => $userID,
			':client_id' => $clientID	
		));
		if ($sucess) {
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if ($rows && count($rows) > 0) {
				return $rows;
			}
		}
		return array();
	}
	
	
	public static function getTasks ( $userID, $projectID ) {
		$pdo = Database::getInstance()->getConnection();
		$sql = "SELECT DISTINCT task_id, task_name FROM " . self::TABLE . " WHERE user_id = :user_id and project_id = :project_id";
		$stmt = $pdo->prepare($sql);
		$sucess = $stmt->execute(array(
			':user_id' => $userID,
			':project_id' => $projectID	
		));
		if ($sucess) {
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if ($rows && count($rows) > 0) {
				return $rows;
			}
		}
		return array();
	}
	
	
}

?>
