<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GithubBranch
 *
 * @author jaraya
 */
class GithubBranch {
	
	const TABLE = 'github_branch';
	
	
	private $id;
	
	private $repo_id;
	
	private $name;
	
	
	public function getName () {
		return $this->name;
	}
	
	public function save () {
		if ( $this->repo_id == null ) {
			throw new Exception('Invalid repository Id');
		}
		$this->checkID();
		
		$sql = ($this->id ? 'UPDATE ' : 'INSERT INTO ') . self::TABLE . ' SET ';
		
		$sql .= 'repo_id	= :repo_id,
			 name		= :name
			 ';
		$params = array(
		    ':repo_id'	=> $this->repo_id,
		    ':name'	=> $this->name
		);
		
		if ( $this->id ) {
			$sql .= ' WHERE id = :id ';
			$params[':id'] = $this->id;
		}
		
		$pdo = Database::getInstance()->getConnection();
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
		$sql = "SELECT id FROM " . self::TABLE . " WHERE repo_id = :repo_id and name = :name ";
		$stmt = $pdo->prepare($sql);
		$sucess = $stmt->execute(array(
		    ':repo_id'	=> $this->repo_id,
		    ':name'	=> $this->name
		));
		if ($sucess) {
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row && count($row) > 0) {
				$this->id = $row['id'];
			}
		}
	}
	
	
	/**
	 *
	 * @param int $userID
	 * @param GithubRepository $repository
	 * @return array 
	 */
	public static function getBranches ($userID, $repository) {
		$branches = array();
		$userID = Utils::enforcePositiveIntegerValue($userID);
		$user = new User($userID);
		$httpClient = new HttpClient();
		$httpClient->setHeaders(array("Authorization: token {$user->getGithubAccessToken()}"));
		$response = $httpClient->doGetRequest("{$repository->getURL()}/branches");
		$jsonBranches = json_decode($response, true);
		foreach ( $jsonBranches as $jsonBranch ) {
			$branch = new GithubBranch();
			$branch->repo_id = $repository->getRepositoryID();
			$branch->name = $jsonBranch['name'];
			array_push($branches, $branch);
		}
		return $branches;
		
	}
	
}

?>
