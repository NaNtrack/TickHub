<?php

/**
 * Description of Utils
 *
 * @author jaraya
 */
class Utils {

	
	public static function parseDate ( $value, $format, $outputFormat = 'Y-m-d H:i:s', $useGM = true ) {
		if ( is_array($value) ) return null;
		$date = DateTime::createFromFormat($format, $value);
		if ( $date ) {
			$function = $useGM ? 'gmdate' : 'date';
			return $function($outputFormat, $date->getTimestamp());
		}
		return null;
	}
	
	/**
	 * Get either a Gravatar URL or complete image tag for a specified email address.
	 *
	 * @param string $email The email address
	 * @param string $size Size in pixels, defaults to 80px [ 1 - 512 ]
	 * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
	 * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
	 * @param boole $img True to return a complete IMG tag False for just the URL
	 * @param array $atts Optional, additional key/value attributes to include in the IMG tag
	 * @return String containing either just a URL or a complete image tag
	 * @source http://gravatar.com/site/implement/images/php/
	 */
	public static function getGravatar( $email, $size = 80, $d = '', $r = 'g', $img = false, $atts = array() ) {
		$url = 'https://secure.gravatar.com/avatar/';
		$url .= md5( strtolower( trim( $email ) ) );
		$url .= "?s=$size&d=$d&r=$r";
		if ( $img ) {
			$url = '<img src="' . $url . '"';
			foreach ( $atts as $key => $val )
				$url .= ' ' . $key . '="' . $val . '"';
			$url .= ' />';
		}
	}


	/**
	 * Check if the given value is a positive integer
	 *
	 * @param mixed $val The value to check
	 * @param bool $allowNullValue Allow null value?
	 * @return int A positive integer
	 * @throws Exception If the given value is not a positive integer
	 */
	public static function enforcePositiveIntegerValue ( $val , $allowNullValue = false ) {
		if ( $val === null && $allowNullValue === true ) return $val;
		if ( $val === 0 ) return 0;
		$v = filter_var($val, FILTER_VALIDATE_INT, array('options' => array('min_range' => 0)));
		if ( $v === false) {
			throw new Exception("The value must be a positive integer");
		}
		return $v;
	}


	/**
	 * Check if the given value is an integer
	 *
	 * @param mixed $val The value to check
	 * @param bool $allowNullValue Allow null value?
	 * @return int An integer
	 * @throws Exception If the given value is not an integer
	 */
	public static function enforceIntegerValue ( $val , $allowNullValue = false ) {
		if ( $val === null && $allowNullValue === true ) return $val;
		if ( $val === 0 ) return 0;
		$v = filter_var($val, FILTER_VALIDATE_INT);
		if ( $v === false) {
			throw new Exception("The value must be an integer");
		}
		return $v;
	}


	/**
	 * Check if the given value is a positive float
	 *
	 * @param mixed $val The value to check
	 * @param bool $allowNullValue Allow null value?
	 * @return float A positive float
	 * @throws Exception If the given value is not a positive float
	 */
	public static function enforcePositiveFloatValue ( $val , $allowNullValue = false ) {
		if ( $val === null && $allowNullValue === true ) return $val;
		if ( (float)$val === 0.0 ) return 0.0;
		$v = filter_var($val, FILTER_VALIDATE_FLOAT);
		if ( $v === false || $v < 0.0 ) {
			throw new Exception("The value must be a positive float");
		}
		return $v;
	}


	/**
	 * Check if the given value is a float
	 *
	 * @param mixed $val The value to check
	 * @param bool $allowNullValue Allow null value?
	 * @return float A float
	 * @throws Exception If the given value is not a float
	 */
	public static function enforceFloatValue ( $val, $allowNullValue = false ) {
		if ( $val === null && $allowNullValue === true ) return $val;
		if ( (float)$val === 0.0 ) return 0.0;
		$v = filter_var($val, FILTER_VALIDATE_FLOAT);
		if ( $v === false ) {
			throw new Exception("The value must be a float");
		}
		return $v;
	}

	/**
	 * Sanitize the given value as string
	 *
	 * @param mixed $val The value to sanitize
	 * @param bool $allowNullValue Allow null value?
	 * @return string A string
	 */
	public static function stringValue ( $val , $allowNullValue = false ) {
		if ( $val === null && $allowNullValue === true ) return $val;
		return stripslashes(trim(filter_var($val, FILTER_SANITIZE_STRING)));
	}


	/**
	 * Check if the given value is a valid email address
	 *
	 * @param mixed $val The value to check
	 * @param bool $allowNullValue Allow null value?
	 * @return string A valid email address
	 * @throws Exception If the given value is not a valid email address
	 */
	public static function enforceEmailValue ( $val , $allowNullValue = false ) {
		if ( $val === null && $allowNullValue === true ) return $val;
		$email = filter_var($val, FILTER_VALIDATE_EMAIL);
		if( $email == false ) {
			throw new Exception("The email address is not valid");
		}
		return $email;
	}


	/**
	 * Check if the given value is a valid date (PHP Format: Y-m-d H:i:s)
	 *
	 * @param mixed $val The value to check
	 * @param bool $allowNullValue Allow null value?
	 * @return string The date
	 */
	public static function enforceDateValue ( $val , $allowNullValue = false ) {
		if ( $val === null && $allowNullValue === true ) return $val;
		if ( preg_match("/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $val, $matches) ) {
			if ( checkdate($matches[2], $matches[3], $matches[1]) == false ) {
				throw new Exception("The value must be a valid date. Format: YYYY-MM-DD HH:mm:SS");
			}
		} else {
			throw new Exception("The value must be a valid date. Format: YYYY-MM-DD HH:mm:SS");
		}
		return $val;
	}

	/**
	 * Check if the given value match against one of the accepted values
	 *
	 * @param mixed $val The value to check
	 * @param array $acceptedValues An enumeration with the accepted values
	 * @param string $message The message to throw if there is any exception
	 * @param bool $allowNullValue Allow null value?
	 * @return mixed The given value if this match against any of the accepted values
	 * @throws Exception If the given value doesn't match at all
	 */
	public static function enforceEnumValue ( $val, $acceptedValues, $errorMessage , $allowNullValue = false ) {
		if ( $val === null && $allowNullValue === true ) return $val;
		if ( is_array($acceptedValues) === true ) {
			if ( !in_array( $val, $acceptedValues ) ) {
				throw new Exception($errorMessage);
			}
		} else {
			throw new Exception("The value must be an array");
		}
		return $val;
	}
	
}
