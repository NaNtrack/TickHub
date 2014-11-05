<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GithubCommits
 *
 * @author jaraya
 */
class GithubCommit {
	
	
	const TABLE = 'github_commit';
	
	
	
	private $id;
	
	private $repo_id;
	
	private $message;
	
	private $author_name;
	
	private $author_email;
	
	private $date;
	
	private $sha;
	
	private $url;
	
	
	/**
	 *
	 * @param int $userID
	 * @param GithubRepository $repository
	 * @return array 
	 */
	public static function getCommits ($userID, $repository) {
		$commits = array();
		$userID = Utils::enforcePositiveIntegerValue($userID);
		$user = new User($userID);
		$httpClient = new HttpClient();
		$httpClient->setHeaders(array("Authorization: token {$user->getGithubAccessToken()}"));
		foreach ( $repository->getBranches() as $branch ) {
			$response = $httpClient->doGetRequest("{$repository->getURL()}/commits/{$branch->getName()}");
			$jsonCommits = json_decode($response, true);
			foreach ( $jsonCommits as $jsonCommit ) {
				if ( is_array($jsonCommit) && isset($jsonCommit['author']) ) {
					//Log::getInstance()->log(print_r($jsonCommit['author'], true));
					$commit = new GithubCommit();
					$commit->repo_id	= $repository->getRepositoryID();
					$commit->message	= $jsonCommit['message'];
					$commit->author_name	= $jsonCommit['author']['name'];
					$commit->author_email	= $jsonCommit['author']['email'];
					$commit->date		= $jsonCommit['author']['date'];
					$commit->sha		= $jsonCommit['tree']['sha'];
					$commit->url		= $jsonCommit['tree']['url'];
					array_push($commits, $commit);
				}
			}
		}
		return $commits;
	}
	
	
	
	public function save() {
		if ( $this->repo_id == null ) {
			throw new Exception('Invalid repository Id');
		}
		$this->checkID();
		
		$sql = ($this->id ? 'UPDATE ' : 'INSERT INTO ') . self::TABLE . ' SET ';
		
		$sql .= 'repo_id	= :repo_id,
			 message	= :message,
			 author_name	= :author_name,
			 author_email	= :author_email,
			 date		= :date,
			 sha		= :sha,
			 url		= :url
			 ';
		$params = array(
		    ':repo_id'		=> $this->repo_id,
		    ':message'		=> $this->message,
		    ':author_name'	=> $this->author_name,
		    ':author_email'	=> $this->author_email,
		    ':date'		=> Utils::parseDate($this->date, DateTime::RFC3339),
		    ':sha'		=> $this->sha,
		    ':url'		=> $this->url
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
		$sql = "SELECT id FROM " . self::TABLE . " WHERE sha = :sha ";
		$stmt = $pdo->prepare($sql);
		$sucess = $stmt->execute(array(':sha' => $this->sha));
		if ($sucess) {
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row && count($row) > 0) {
				$this->id = $row['id'];
			}
		}
	}
	
	
}
