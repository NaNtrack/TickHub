<?php

/**
 * Description of User
 *
 * @author jaraya
 */
final class User {
	
	const TABLE			= 'user';
	const VIEW_TICKSPOT_USERS	= 'v_tickhub_users';
	
	/**
	 *
	 * @var int
	 */
	private $id;
	
	/**
	 *
	 * @var string
	 */
	private $email;
	
	/**
	 *
	 * @var string
	 */
	private $password;
	
	/**
	 *
	 * @var string
	 */
	private $given_name;
	
	/**
	 *
	 * @var int
	 */
	private $tickspot_user_id;
	
	/**
	 *
	 * @var string
	 */
	private $tickspot_company;
	
	/**
	 *
	 * @var TickSpotUser
	 */
	private $tickspot_user;
	
	/**
	 *
	 * @var string
	 */
	private $github_access_token;
	
	/**
	 *
	 * @var string
	 */
	private $github_token_type;
	
	
	/**
	 *
	 * @var User
	 */
	private static $_logged_user;
	
	
	public function getID () {
		return $this->id;
	}
	
	public function getEmail () {
		return $this->email;
	}
	
	public function setEmail ( $email ) {
		$this->email = $email;
	}
	
	public function setPassword ( $password ) {
		$this->password = sha1($password);
	}
	
	public function getGivenName () {
		return $this->given_name;
	}
	
	public function setGivenName ( $name ) {
		$this->given_name = $name;
	}
	
	public function getTickSpotUserID () {
		return $this->tickspot_user_id;
	}
	
	public function setTickSpotUserID ($userID) {
		$this->tickspot_user_id = Utils::enforcePositiveIntegerValue($userID);
	}
	
	public function getTickSpotUser () {
		return null;
	}
	
	public function getTickspotCompany () {
		return $this->tickspot_company;
	}
	
	public function setTickSpotCompany ( $company ) {
		$this->tickspot_company = $company;
	} 
	
	public function getGithubAccessToken () {
		return $this->github_access_token;
	}
	
	public function setGithubAccessToken ( $githubAccessToken ) {
		$this->github_access_token = $githubAccessToken;
		return $this;
	}
	
	public function getGithubTokenType () {
		return $this->github_token_type;
	}
	
	public function setGithubTokenType ( $githubTokenType ) {
		$this->github_token_type = $githubTokenType;
		return $this;
	}
	
	
	public function __construct($id = null) {
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
				$this->tickspot_company = $row['tickspot_company'];
				$this->github_access_token = $row['github_access_token'];
				$this->github_token_type = $row['github_token_type'];
			} else {
				throw new Exception("Invalid user Id");
			}
		}
		return $this;
	}
	
	
	public function save() {
		//check mandatory values
		if ($this->email === null) {
			throw new Exception("The email address is not valid");
		} elseif ($this->getID() === null && $this->password == null) {
			throw new RoboinvestException("You must specify a password");
		}

		//check if the username and email are unique
		$this->checkEmail();

		$pdo = Database::getInstance()->getConnection();

		//insert or update?
		$sql = $this->getID() ? 'UPDATE ' : 'INSERT INTO ';
		$sql .= self::TABLE . ' SET ';
		if ($this->getID() == null || ($this->getID() != null && $this->password != null )) {
			$sql .= "password = :password, ";
		}
		$sql .= "email			= :email,
			 given_name		= :given_name,
			 tickspot_user_id	= :tickspot_user_id,
			 tickspot_company	= :tickspot_company,
			 github_access_token	= :github_access_token,
			 github_token_type	= :github_token_type ";
		$sql .= $this->getID() ? ' WHERE id = :id ' : '';
		$stmt = $pdo->prepare($sql);
		$params = array(
			':email'		=> $this->getEmail(),
			':given_name'		=> $this->getGivenName(),
			':tickspot_user_id'	=> $this->getTickSpotUserId(),
			':tickspot_company'	=> $this->getTickspotCompany(),
			':github_access_token'	=> $this->getGithubAccessToken(),
			':github_token_type'	=> $this->getGithubTokenType(),
		);
		if ($this->getID() == null || ($this->getID() != null && $this->password != null )) {
			$params[':password'] = $this->password;
		}
		if ($this->getID()) {
			$params[':id'] = $this->getID();
		}
		$pdo->beginTransaction();
		$stmt->execute($params);
		$rows_affected = $stmt->rowCount();
		if ($this->getID() == null && $rows_affected == 1) {
			$this->id = $pdo->lastInsertId();
			Log::getInstance()->log("[registration] New user, id: " . $this->_id);
		}
		$pdo->commit();
		
		return ($rows_affected == 1 || $rows_affected == 0);
	}
	
	private function checkEmail() {
		$pdo = Database::getInstance()->getConnection();
		$email = $pdo->quote($this->getEmail());
		$sql = "SELECT count(1) FROM " . self::TABLE . " WHERE email = $email ";
		$sql .= $this->getID() ? " AND id <> " . $this->getID() : '';
		$result = $pdo->query($sql);
		$count = $result->fetch(PDO::FETCH_COLUMN);
		if ((int) $count > 0) {
			throw new Exception("The email address is already in use");
		}
	}
	
	
	public static function login ($email, $password, $remember = true ) {
		$email = Utils::enforceEmailValue($email);
		$pdo = Database::getInstance()->getConnection();
		if ($pdo) {
			$sql = "SELECT id FROM " . self::TABLE . " WHERE email = :email AND password = :password ";
			$stmt = $pdo->prepare($sql);
			$params = array(
			    ':email' => $email,
			    ':password' => sha1($password)
			);
			$sucess = $stmt->execute($params);
			if ($sucess) {
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				if ($row && count($row) > 0) {
					Log::getInstance()->log("[login] $email successfully logged in");
					$user = new User($row['id']);
					UserSession::initSession($user->getID(), $remember);
					return $user;
				} else {
					Log::getInstance()->log("[login] Invalid username or password for '$email' ");
				}
			}
		}
		return null;
	}
	
	
	public static function isLogged() {
		return isset($_COOKIE) && isset($_COOKIE['customer']);
	}
	
	
	/**
	 * Get the current logged user data
	 *
	 * @return User
	 */
	public static function getLoggedUser() {
		if ( self::isLogged() ) {
			if ( self::$_logged_user == null ) {
				try {
					$cookie = $_COOKIE['customer'];
					$session = new UserSession($cookie);
					self::$_logged_user =  new User($session->getUserID());
				} catch (Exception $ex) {
					Log::getInstance()->log("[$cookie] Session error... logging out ({$ex->getMessage()})");
					UserSession::endSession();
				}
			}
		}
		return self::$_logged_user;
	}
	
	
}

?>
