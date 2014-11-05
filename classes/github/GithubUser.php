<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GithubUser
 *
 * @author jaraya
 */
class GithubUser {
	
	
	
	public static function updateUserPermisions ($userID, $table, $tableField, $tableID ) {
		$pdo = Database::getInstance()->getConnection();
		$sql = " SELECT id FROM $table WHERE user_id = :user_id and $tableField = :$tableField ";
		$params = array(
		    ':user_id' => $userID,
		    ":$tableField" => $tableID
		);
		$stmt = $pdo->prepare($sql);
		$sucess = $stmt->execute($params);
		$id = null;
		if ($sucess) {
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row && count($row) > 0) {
				$id = $row['id'];
			}
		}
		
		$sql = ($id ? 'UPDATE ' : 'INSERT INTO ') . $table . ' SET ';
		$sql .= "user_id	= :user_id,
			 $tableField	= :$tableField";
		$params = array(
		    ':user_id'		=> $userID,
		    ":$tableField"	=> $tableID
		);
		
		if ( $id ) {
			$sql .= ' WHERE id = :id ';
			$params[':id'] = $id;
		}
		
		
		$stmt = $pdo->prepare($sql);
		$stmt->execute($params);
		$rows_affected = $stmt->rowCount();
		if ($id == null && $rows_affected == 1) {
			$id = $pdo->lastInsertId();
		}
		return ($rows_affected == 1 || $rows_affected == 0);
		
	}
	
}

?>
