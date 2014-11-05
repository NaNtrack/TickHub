<?php

/**
 * This is a sample implementation of the password filter
 *
 * @author jaraya
 */
class TickSpotPasswordFilterSample  implements PasswordFilter{
	
	/**
	 * Returns the decoded string of the password
	 * 
	 * @param string $encodedPassword The encoded password
	 * @return string The password decoded
	 */
	public function decode($encodedPassword) {
		return  null;
	}
	
	/**
	 * Returns an encoded string of the password
	 * 
	 * @param string $password The password to encode
	 * @return string The password encoded
	 */
	public function encode($password) {
		return null;
	}
}

?>
