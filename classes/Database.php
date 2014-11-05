<?php

/**
 * This class handle all database connections
 *
 * @author jaraya
 */
class Database {

	/**
	 * The object instance
	 *
	 * @var Database
	 */
	private static $_instance = null;
	
	/**
	 * Connection
	 *
	 * @var resource
	 */
	private $_conn = null;

	
	private function  __construct() {}

	/**
	 * Get the instance (singleton)
	 *
	 * @return Database The instance of this class
	 */
	public static function getInstance(){
		if ( !isset(self::$_instance) || self::$_instance == null ) {
			$c = __CLASS__;
			self::$_instance = new $c;
		}
		return self::$_instance;
	}


	/**
	 * Get a connection to the desired DB name
	 *
	 * @param string $DBName The Database Name, must be: self::DB_USER_DATA, self::DB_FINANCIAL_DATA or self::DB_ACTIVITY_FEED
	 * @return PDO The PDO Object
	 * @exception RoboinvestException On any error
	 */
	public function getConnection(){
		if ( $this->_conn === null || is_resource($this->_conn) === false ) {
			$this->_conn = new PDO('mysql:host='.DATABASE_HOST.';dbname='.DATABASE_DBNAME.'',DATABASE_USER, DATABASE_PASS,
						array(  
							PDO::ATTR_PERSISTENT => true,
							PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
							PDO::ATTR_EMULATE_PREPARES => true,
							PDO::ATTR_TIMEOUT => 30 
						)
					);
			$this->_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		return $this->_conn;
	}
	
}
