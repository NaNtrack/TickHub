<?php

/**
 * Description of GithubView
 *
 * @author jaraya
 */
class GithubView {
	
	const TABLE = 'v_commits';
	
	public static function getRepositories ( $userID ) {
		$pdo = Database::getInstance()->getConnection();
		$sql = "SELECT DISTINCT repo_id, repo_name FROM " . self::TABLE . " WHERE user_id = :user_id ";
		$stmt = $pdo->prepare($sql);
		$sucess = $stmt->execute(array(
			':user_id' => $userID	
		));
		if ($sucess) {
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if ($rows && count($rows) > 0) {
				return $rows;
			}
		}
		return array();
	}
	
	public static function getCommits ( $userID, $repoID, $emails = array() ) {
		$pdo = Database::getInstance()->getConnection();
		$sql  = " SELECT DISTINCT v.commit_id, v.message, v.author_name, v.author_email, v.date FROM " . self::TABLE . " v WHERE v.user_id = :user_id and v.repo_id = :repo_id AND v.commit_id NOT IN ";
		$sql .= " (SELECT commit_id FROM ".UserCommit::TABLE." WHERE user_id = v.user_id ) ";
		$params = array(
			':user_id' => $userID,
			':repo_id' => $repoID
		);
		
		if ( count($emails) == 1 ) {
			$sql .= " AND author_email = :author_email ";
			$params[':author_email'] = $emails[0];
		} else if ( count($emails) > 1 ) {
			$i = 0;
			$j = 0;
			$sql .= " AND ( ";
			foreach ( $emails as $email ) {
				$sql .= " author_email = :author_email_$i OR ";
				$params[":author_email_$i"] = $email;
				$i++;
			}
			$sql .= " 1=0 )";
		}
		
		$stmt = $pdo->prepare($sql);
		$sucess = $stmt->execute($params);
		
		if ($sucess) {
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if ($rows && count($rows) > 0) {
				return $rows;
			}
		}
		return array();
	}
	
	public static function getRepositoryEmails ( $userID, $repoID ) {
		$pdo = Database::getInstance()->getConnection();
		$sql = "SELECT DISTINCT author_email, author_name FROM " . self::TABLE . " WHERE user_id = :user_id AND repo_id = :repo_id ";
		$stmt = $pdo->prepare($sql);
		$sucess = $stmt->execute(array(
			':user_id' => $userID,
			':repo_id' => $repoID
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
