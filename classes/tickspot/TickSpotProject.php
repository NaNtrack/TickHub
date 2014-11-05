<?php

/**
 * TickSpotProject
 *
 * @author jaraya
 */
class TickSpotProject {
	
	const TABLE = 'tickspot_project';
	const TABLE_USER_PROJECT = 'tickspot_user_project';
	
	/**
	 *
	 * @var int
	 */
	private $id;
	
	/**
	 *
	 * @var int
	 */
	private $project_id;
	
	/**
	 *
	 * @var string
	 */
	private $name;
	
	/**
	 *
	 * @var int
	 */
	private $client_id;
	
	/**
	 *
	 * @var string PHP date format Y-m-d
	 */
	private $opened_on;
	
	/**
	 *
	 * @var string PHP date format Y-m-d
	 */
	private $closed_on;
	
	/**
	 *
	 * @var string PHP date format Y-m-d
	 */
	private $created_at;
	
	/**
	 *
	 * @var string PHP date format Y-m-d
	 */
	private $updated_at;
	
	/**
	 *
	 * @var array
	 */
	private $tasks;
	
	
	public static function parseProjects ( $data, $userID  ) {
		$projects = array();
		
		if ( !is_array($data) || !isset($data['project'])) return $projects;
		
		if ( isset($data['project']['id']) ) {
			$p = $data['project'];
			$project = new TickSpotProject();
			if ( !isset($p['id']) ) continue;
			$project->project_id = $p['id'];
			$project->name = $p['name'];
			$project->client_id = $p['client_id'];
			$project->opened_on = Utils::parseDate($p['opened_on'], 'Y-m-d', 'Y-m-d', false);
			$project->closed_on = Utils::parseDate($p['closed_on'], "D, d M Y H:i:s O");
			$project->created_at = Utils::parseDate($p['created_at'], "D, d M Y H:i:s O");
			$project->updated_at = Utils::parseDate($p['updated_at'], "D, d M Y H:i:s O");
			$project->tasks = TickSpotTask::parseTasks($p['tasks'], $userID);
			TickSpotUser::updateUserPermisions($userID, self::TABLE_USER_PROJECT, 'project_id', $project->project_id);
			array_push($projects, $project);
		} else {
			foreach ($data['project'] as $i => $p ) {
				$project = new TickSpotProject();
				if ( !isset($p['id']) ) continue;
				$project->project_id = $p['id'];
				$project->name = $p['name'];
				$project->client_id = $p['client_id'];
				$project->opened_on = Utils::parseDate($p['opened_on'], 'Y-m-d', 'Y-m-d', false);
				$project->closed_on = Utils::parseDate($p['closed_on'], "D, d M Y H:i:s O");
				$project->created_at = Utils::parseDate($p['created_at'], "D, d M Y H:i:s O");
				$project->updated_at = Utils::parseDate($p['updated_at'], "D, d M Y H:i:s O");
				$project->tasks = TickSpotTask::parseTasks($p['tasks'], $userID);
				TickSpotUser::updateUserPermisions($userID, self::TABLE_USER_PROJECT, 'project_id', $project->project_id);
				array_push($projects, $project);
			}
		}			
		return $projects;
	}
	
	
	public function save () {
		$pdo = Database::getInstance()->getConnection();
		
		if ( !$this->project_id ) {
			throw new Exception("Invalid tickspot project id");
		}
		
		$this->checkID();
		
		$sql = ($this->id ? 'UPDATE ' : 'INSERT INTO ') . self::TABLE . ' SET ';
		$sql .= 'project_id	= :project_id,
			 name		= :name,
			 client_id	= :client_id,
			 opened_on	= :opened_on, 
			 closed_on	= :closed_on, 
			 created_at	= :created_at,
			 updated_at	= :updated_at ';
		$params = array(
		    ':project_id'	=> $this->project_id,
		    ':name'		=> $this->name,
		    ':client_id'	=> $this->client_id,
		    ':opened_on'	=> $this->opened_on,
		    ':closed_on'	=> $this->closed_on,
		    ':created_at'	=> $this->created_at,
		    ':updated_at'	=> $this->updated_at
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
		
		foreach ( $this->tasks as $task ) {
			$task->save();
		}
		
		return ($rows_affected == 1 || $rows_affected == 0);
	}
	
	
	private function checkID () {
		$pdo = Database::getInstance()->getConnection();
		$sql = "SELECT id FROM " . self::TABLE . " WHERE project_id = :project_id ";
		$stmt = $pdo->prepare($sql);
		$sucess = $stmt->execute(array(':project_id' => $this->project_id));
		if ($sucess) {
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row && count($row) > 0) {
				$this->id = $row['id'];
			}
		}
	}
	
}

?>
