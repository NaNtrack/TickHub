<?php

/**
 * Description of UserSession
 *
 * @author jaraya
 */
class UserSession {
	
	/**
	 * The table where the data is stored
	 */
	const TABLE = 'user_session';
	
	//Session statuses
	const STATUS_ACTIVE  = 'active';
	const STATUS_DELETED = 'deleted';
	
	/**
	 *
	 * @var int
	 */
	private $_id;
	
	/**
	 *
	 * @var string
	 */
	private $_session_id;
	
	/**
	 *
	 * @var string
	 */
	private $_server_signature;
	
	/**
	 *
	 * @var int
	 */
	private $_user_id;
	
	/**
	 *
	 * @var string
	 */
	private $_ip_address;
	
	
	/**
	 *
	 * @var string PHP Date format: Y-m-d H:i:s
	 */
	private $_last_login_dt;
	
	/**
	 *
	 * @var string PHP Date format: Y-m-d H:i:s
	 */
	private $_last_activity_dt;
	
	/**
	 * Returns the Id
	 *
	 * @return int
	 */
	private function getID () {
		return $this->_id;
	}
	
	/**
	 * Returns the user Id
	 *
	 * @return int
	 */
	public function getUserID () {
		return $this->_user_id;
	}
	
	/**
	 * Sets the latest date/time of activity
	 *
	 * @param string $lastActivityDate PHP Date format: Y-m-d H:i:s
	 * @return UserSession 
	 */
	public function setLastActivityDate ( $lastActivityDate ) {
		$this->_last_activity_dt = Utils::enforceDateValue($lastActivityDate, true);
		return $this;
	}
	
	public function __construct( $sessionID = null ) {
		$this->_server_signature = self::getServerSignature();
		if ( $sessionID !== null ) {
			$this->retrieve($sessionID);
		}
	}
	
	/**
	 *
	 * @param string $sessionID 
	 */
	private function retrieve ( $sessionID ) {
		$pdo = Database::getInstance()->getConnection();
		if ($pdo) {
			$sql = 'SELECT * FROM ' . self::TABLE . ' WHERE session_id = :session_id and server_signature = :server_signature AND status = :active_status';
			$params = array(
				':session_id'		=> $sessionID,
				':server_signature'	=> $this->_server_signature,
				':active_status'	=> self::STATUS_ACTIVE
			);
			$stmt = $pdo->prepare($sql);
			$stmt->execute($params);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row && count($row) > 0) {
				$this->_id = $row['id'];
				$this->_session_id	 = $row['session_id'];
				$this->_server_signature = $row['server_signature'];
				$this->_user_id		 = $row['user_id'];
				$this->_ip_address	 = $row['ip_address'];
				$this->_last_login_dt	 = $row['last_login_dt'];
				$this->_last_activity_dt = $row['last_activity_dt'];
				$this->checkSession();
			} else {
				throw new Exception("Invalid Session ID");
			}
		}
	}
	
	
	/**
	 * Returns the server signature
	 *
	 * @return string
	 */
	private static function getServerSignature ( ) {
		return sha1(self::getCookieServerName());
	}
	
	/**
	 * Returns an unique ID
	 *
	 * @return string
	 */
	private static function createUniqueID () {
		return str_replace(".", "-", strtoupper(uniqid('TH-', true)));
	}
	
	
	/**
	 * Check the current IP address for this session. 
	 * If the current IP address is different from the IP address stored 
	 * in the database we initialize another session with another session_id
	 */
	private function checkSession ( ) {
		$clientIP = $_SERVER['REMOTE_ADDR'];
		if ( $this->_ip_address != $clientIP ) {
			$this->_id = null;
			$this->_ip_address = $clientIP;
			$this->_session_id = self::createUniqueID();
			$this->save();
		}
	}
	
	
	/**
	 * Ends and destroy the current session
	 */
	public static function endSession () {
		$cookie = isset($_COOKIE['customer']) ? $_COOKIE['customer'] : null;
		$_COOKIE['customer'] = null;
		setcookie('customer','',time() - 60*60,'/', self::getCookieServerName());
		setcookie('customer','',time() - 60*60,'/', self::_getOldCookieServerName());
		@session_destroy();
		if ( $cookie == null ) return;
		try{
			$pdo = Database::getInstance()->getConnection();

			if ($pdo) {
				$sql = 'UPDATE ' . self::TABLE . ' SET status = :deleted_status WHERE session_id = :session_id ';
				$params = array(
					':deleted_status' => self::STATUS_DELETED,
					':session_id'	  => $cookie
				); 
				$stmt = $pdo->prepare($sql);
				$pdo->beginTransaction();
				$stmt->execute($params);
				$pdo->commit();
			}
		} catch ( Exception $ex) {
			Log::getInstance()->log("Error deactivating user session in the database: {$ex->getMessage()}. Trace: {$ex->getTraceAsString()}");
		}
	}
	
	/**
	 * Initialize a new user session
	 *
	 * @param int $userID
	 * @param bool $remember If true, remember this session for 30 days
	 */
	public static function initSession ( $userID, $remember = true ) {
		$session = new UserSession();
		$session->_user_id = $userID;
		$session->_server_signature = self::getServerSignature();
		$session->_session_id = self::createUniqueID();
		$session->_ip_address = $_SERVER['REMOTE_ADDR'];
		$session->_last_login_dt = gmdate("Y-m-d H:i:s");
		$session->save();
		$time = $remember ? time() + 60 * 60 * 24 * 30 : null; //30 days or at the end of this session
		setcookie('customer', $session->_session_id, $time, '/', self::getCookieServerName());
	}
	
	
	/**
	 * Stores the current user session into the database
	 *
	 * @return bool 
	 */
	public function save() {
		//check mandatory values

		$pdo = Database::getInstance()->getConnection();

		if (	$this->_session_id	 == null ||
			$this->_server_signature == null ||
			$this->_user_id		 == null ||
			$this->_ip_address	 == null ||
			$this->_last_login_dt	 == null    ) {
			throw new Exception("Invalid user session");
		}

		$sql = '';
		
		if ($pdo) {
			//insert or update?
			$sql .= $this->getID() ? 'UPDATE ' : 'INSERT INTO ';
			$sql .= self::TABLE . ' SET ';
			
			$sql .= "
				session_id		= :session_id		,
				server_signature	= :server_signature	,
				user_id			= :user_id		,
				ip_address		= :ip_address		,
				last_login_dt		= :last_login_dt	,
				last_activity_dt	= :last_activity_dt	 ";
				$sql .= $this->getID() ? ' WHERE id = :id ' : '';
			$stmt = $pdo->prepare($sql);
			$params = array(
				':session_id'		=> $this->_session_id,
				':server_signature'	=> $this->_server_signature,
				':user_id'		=> $this->getUserID(),
				':ip_address'		=> $this->_ip_address,
				':last_login_dt'	=> $this->_last_login_dt,
				':last_activity_dt'	=> $this->_last_activity_dt
			);
			if ( $this->getID() ) {
				$params[':id'] = $this->getID();
			}
			$pdo->beginTransaction();
			$stmt->execute($params);
			$rows_affected = $stmt->rowCount();
			if ($this->getID() == null && $rows_affected == 1) {
				$this->_id = $pdo->lastInsertId();
				Log::getInstance()->log("[session] New user sesion, id: " . $this->_id);
			}
			$pdo->commit();
			return ($rows_affected == 1 || $rows_affected == 0);
		}
		return false;
	}
	
	
	private static function getCookieServerName () {
		return $_SERVER['SERVER_NAME'];
	}
	
	private static function _getOldCookieServerName () {
		$s = str_replace('.com', '', $_SERVER['SERVER_NAME']);
		$pointPos = strpos($s, '.');
		$serverName = $pointPos !== -1 ? substr($_SERVER['SERVER_NAME'], $pointPos) : $_SERVER['SERVER_NAME'];
		if ( $serverName[0] != '.' ) $serverName = ".$serverName";
		return $serverName;
	}
	
}
