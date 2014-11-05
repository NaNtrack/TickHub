<?php

/**
 * This interface defines the method to encode/decode the password to before 
 * save/retrieve the value to/from the database
 *
 * @author jaraya
 */
interface PasswordFilter {
	
	
	/**
	 * Returns an encoded string of the password
	 * 
	 * @param string $password The password to encode
	 * @return string The password encoded
	 */
	public function encode ( $password );
	
	
	/**
	 * Returns the decoded string of the password
	 * 
	 * @param string $password The encoded password
	 * @return string The password decoded
	 */
	public function decode ( $password );
	
	
}

?>
