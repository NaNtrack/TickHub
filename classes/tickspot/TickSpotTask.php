<?php

/**
 * TickSpotTask
 *
 * @author jaraya
 */
class TickSpotTask {
	
	const TABLE = 'tickspot_task';
	const TABLE_USER_TASK = 'tickspot_user_task';
	
	/**
	 *
	 * @var int
	 */
	private $id;
	
	/**
	 *
	 * @var int
	 */
	private $task_id;
	
	/**
	 *
	 * @var string
	 */
	private $name;
	
	/**
	 *
	 * @var int
	 */
	private $position;
	
	/**
	 *
	 * @var int
	 */
	private $project_id;
	
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
	 * @var float
	 */
	private $budget;
	
	
	
	public static function parseTasks ( $data, $userID ) {
		$tasks = array();
		
		if ( !is_array($data) || !isset($data['task']) ) return $tasks;
		
		if ( isset($data['task']['id']) ) {
			$t = $data['task'];
			$task = new TickSpotTask();
			$task->task_id = $t['id'];
			$task->name = $t['name'];
			$task->position = $t['position'];
			$task->project_id = $t['project_id'];
			$task->opened_on = Utils::parseDate($t['opened_on'], 'Y-m-d', 'Y-m-d', false);
			$task->closed_on = Utils::parseDate($t['closed_on'], "D, d M Y H:i:s O");
			$task->budget = $t['budget'];
			TickSpotUser::updateUserPermisions($userID, self::TABLE_USER_TASK, 'task_id', $task->task_id);
			array_push($tasks, $task);
		} else {
			foreach( $data['task'] as $i => $t ) {
				$task = new TickSpotTask();
				$task->task_id = $t['id'];
				$task->name = $t['name'];
				$task->position = $t['position'];
				$task->project_id = $t['project_id'];
				$task->opened_on = Utils::parseDate($t['opened_on'], 'Y-m-d', 'Y-m-d', false);
				$task->closed_on = Utils::parseDate($t['closed_on'], "D, d M Y H:i:s O");
				$task->budget = $t['budget'];
				TickSpotUser::updateUserPermisions($userID, self::TABLE_USER_TASK, 'task_id', $task->task_id);
				array_push($tasks, $task);
			}
		}
		return $tasks;
	}
	
	public function save() {
		$pdo = Database::getInstance()->getConnection();
		
		if ( !$this->project_id ) {
			throw new Exception("Invalid tickspot project id");
		}
		
		$this->checkID();
		
		$sql = ($this->id ? 'UPDATE ' : 'INSERT INTO ') . self::TABLE . ' SET ';
		$sql .= 'task_id	= :task_id,
			 name		= :name,
			 position	= :position,
			 project_id	= :project_id,
			 opened_on	= :opened_on, 
			 closed_on	= :closed_on, 
			 budget		= :budget ';
		$params = array(
		    ':task_id'		=> $this->task_id,
		    ':name'		=> $this->name,
		    ':position'		=> $this->position,
		    ':project_id'	=> $this->project_id,
		    ':opened_on'	=> $this->opened_on,
		    ':closed_on'	=> $this->closed_on,
		    ':budget'		=> $this->budget
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
		
		return ($rows_affected == 1 || $rows_affected == 0);
	}
	
	
	private function checkID () {
		$pdo = Database::getInstance()->getConnection();
		$sql = "SELECT id FROM " . self::TABLE . " WHERE task_id = :task_id ";
		$stmt = $pdo->prepare($sql);
		$sucess = $stmt->execute(array(':task_id' => $this->task_id));
		if ($sucess) {
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row && count($row) > 0) {
				$this->id = $row['id'];
			}
		}
	}
	
	
}

?>
