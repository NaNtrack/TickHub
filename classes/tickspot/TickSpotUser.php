<?php

/**
 * TickSpotUser
 *
 * @author jaraya
 */
class TickSpotUser {

	
	const TABLE = 'tickspot_user';
	
	private $id;
	
	private $user_id;
	
	private $first_name;
	
	private $last_name;
	
	private $email;
	
	private $password;
	
	/**
	 *
	 * @var PasswordFilter
	 */
	private $password_filter;
	
	/**
	 * Returns the Id
	 *
	 * @return int
	 */
	public function getID () {
		return $this->id;
	}
	
	/**
	 * Returns the tickspot user Id
	 *
	 * @return int
	 */
	public function getUserID () {
		return $this->user_id;
	}
	
	/**
	 * Returns the first name
	 *
	 * @return string
	 */
	public function getFirstName () {
		return $this->first_name;
	}
	
	/**
	 * Returns the last name
	 *
	 * @return string
	 */
	public function getLastName () {
		return $this->last_name;
	}
	
	/**
	 * Returns the email
	 *
	 * @return string
	 */
	public function getEmail () {
		return $this->email;
	}
	
	
	public function getPassword () {
		if ( $this->password_filter instanceof PasswordFilter && $this->password != null) {
			return $this->password_filter->decode($this->password);
		}
		return null;
	}
	
	public function setPassword ( $password ) {
		if ( $this->password_filter instanceof PasswordFilter ) {
			$this->password = $this->password_filter->encode($password);
		}
	}
	
	
	public function setPasswordFilter ($filter) {
		if ( $filter instanceof PasswordFilter ) {
			$this->password_filter = $filter;
		}
	}
	
	
	
	public function __construct($id = null, $passwordFilter = null) {
		$this->setPasswordFilter($passwordFilter);
		if ($id !== null) {
			$id = Utils::enforcePositiveIntegerValue($id);
			$this->retrieve($id);
		}
	}
	
	private function retrieve($userID){
		$pdo = Database::getInstance()->getConnection();
		if ($pdo) {
			$result = $pdo->query('SELECT * FROM ' . self::TABLE . ' WHERE id = ' . $userID);
			$row = $result->fetch(PDO::FETCH_ASSOC);
			if ($row && count($row) > 0) {
				$this->id = (int) $row['id'];
				$this->email = $row['email'];
				$this->given_name = $row['given_name'];
				$this->tickspot_user_id = $row['tickspot_user_id'];
				$this->github_access_token = $row['github_access_token'];
				$this->github_token_type = $row['github_token_type'];
				$this->password = $row['password'];
			} else {
				throw new Exception("Invalid user Id");
			}
		}
		return $this;
	}
	
	
	public static function getUserIDByEmail ($email) {
		$pdo = Database::getInstance()->getConnection();
		$sql = "SELECT user_id FROM " . self::TABLE . " WHERE email = :email ";
		$stmt = $pdo->prepare($sql);
		$sucess = $stmt->execute(array(':email' => $email));
		if ($sucess) {
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row && count($row) > 0) {
				return $row['user_id'];
			}
		}
		throw new Exception("User not found");
	}
	
	public static function parseUsers ( $data ) {
		$users = array();
		
		if( !is_array($data) || !isset ($data['user'])){
			return $users;
		}
		
		if ( isset($data['user']['id'])) { //just one user
			$u = $data['user'];
			$user = new TickSpotUser();
			$user->user_id = $u['id'];
			$user->first_name = $u['first_name'];
			$user->last_name = $u['last_name'];
			$user->email = $u['email'];
			$user->save();
			array_push($users, $user);
		} else { //2 or more users
			foreach ( $data['user'] as $u ) {
				$user = new TickSpotUser();
				$user->user_id = $u['id'];
				$user->first_name = $u['first_name'];
				$user->last_name = $u['last_name'];
				$user->email = $u['email'];
				$user->save();
				array_push($users, $user);
			}
		}
		return $users;
	}
	
	
	public function save() {
		$pdo = Database::getInstance()->getConnection();
		
		if ( !$this->user_id ) {
			throw new Exception("Invalid tickspot user id");
		}
		
		$this->checkID();
		
		$sql = ($this->id ? 'UPDATE ' : 'INSERT INTO ') . self::TABLE . ' SET ';
		if ($this->getID() == null || ($this->getID() != null && $this->password != null && $this->password_filter != null)) {
			$sql .= "password = :password, ";
		}
		$sql .= 'user_id	= :user_id,
			 first_name	= :first_name,
			 last_name	= :last_name,
			 email		= :email ';
		$params = array(
		    ':user_id'		=> $this->user_id,
		    ':first_name'	=> $this->first_name,
		    ':last_name'	=> $this->last_name,
		    ':email'		=> $this->email
		);
		if ($this->getID() == null || ($this->getID() != null && $this->password != null && $this->password_filter != null)) {
			$params[':password'] = $this->password;
		}
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
		$sql = "SELECT id FROM " . self::TABLE . " WHERE user_id = :user_id ";
		$stmt = $pdo->prepare($sql);
		$sucess = $stmt->execute(array(':user_id' => $this->user_id));
		if ($sucess) {
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row && count($row) > 0) {
				$this->id = $row['id'];
			}
		}
	}
	
	
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
