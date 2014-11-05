<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserCommit
 *
 * @author jaraya
 */
class UserCommit {
	
	const TABLE = 'user_commit';
	
	const STATUS_ADDED = 'added';
	const STATUS_HIDDEN = 'hidden';
	
	
	public static function addCommit ( $user_id , $commit_id ) {
		$sql = 'INSERT INTO ' .self::TABLE . ' SET user_id = :user_id, commit_id = :commit_id ';
		$params = array(
		    ':user_id' => $user_id,
		    ':commit_id' => $commit_id
		);
		$pdo = Database::getInstance()->getConnection();
		$stmt = $pdo->prepare($sql);
		$pdo->beginTransaction();
		$stmt->execute($params);
		$rows_affected = $stmt->rowCount();
		$pdo->commit();
		
		return ($rows_affected == 1 || $rows_affected == 0);
	}
	
	public static function hideCommit ( $user_id , $commit_id ) {
		$sql = 'INSERT INTO ' .self::TABLE . ' SET user_id = :user_id, commit_id = :commit_id, status = :status ';
		$params = array(
		    ':user_id' => $user_id,
		    ':commit_id' => $commit_id,
		    ':status' => self::STATUS_HIDDEN
		);
		$pdo = Database::getInstance()->getConnection();
		$stmt = $pdo->prepare($sql);
		$pdo->beginTransaction();
		$stmt->execute($params);
		$rows_affected = $stmt->rowCount();
		$pdo->commit();
		
		return ($rows_affected == 1 || $rows_affected == 0);
	}
	
	
}
