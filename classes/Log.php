<?php

/**
 * Description of Log
 *
 * @author jaraya
 */
final class Log {

	/**
	 * The object instance
	 *
	 * @var Log
	 */
	private static $_instance = null;

	private $_logFile;

	private function  __construct() {
		$this->_logFile = LOG_FILE;
	}

	/**
	 * Get the instance (singleton)
	 *
	 * @return Log The instance of this class
	 */
	public static function getInstance(){
		if ( !isset(self::$_instance) || self::$_instance == null ) {
			$c = __CLASS__;
			self::$_instance = new $c;
		}
		return self::$_instance;
	}


	/**
	 * Log a message into the log file
	 *
	 * @param strign $message The message to log
	 * @param bool $display If true show the message
	 */
	public function log($message, $display = false){
		$current_tz =  date_default_timezone_get();
		if( ini_get('date.timezone') )
			date_default_timezone_set(ini_get('date.timezone'));
		$date = gmdate("Y/m/d H:i:s");

		date_default_timezone_set($current_tz);
		$log = "[$date] - $message\n";
		$fp = @fopen($this->_logFile,'a');
		if ( $display ) echo "$log\n";
		if ( is_resource($fp) ) {
			@fwrite($fp, $log);
			@fclose($fp);
		} else {
			trigger_error("Can't open '{$this->_logFile}' log file",E_USER_WARNING);
		}
	}
	
	public function logException ( Exception $ex ) {
		$this->log("[exception] ".basename($ex->getFile())."@{$ex->getLine()} : {$ex->getMessage()}. Trace: {$ex->getTraceAsString()}");
	}
	

}
