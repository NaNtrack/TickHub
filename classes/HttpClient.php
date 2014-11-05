<?php

/**
 * HttpClient
 *
 * @author jaraya
 */
class HttpClient {
	
	const METHOD_POST = 'POST';
	const METHOD_GET = 'GET';
	
	/**
	 *
	 * @var string
	 */
	public $url;
	
	/**
	 *
	 * @var type 
	 */
	private $method;
	
	/**
	 *
	 * @var array
	 */
	private $params;
	
	/**
	 *
	 * @var array
	 */
	private $headers;
	
	/**
	 *
	 * @var OAuthConsumer
	 */
	private $consumer;
	
	/**
	 *
	 * @var string
	 */
	private $response;
	
	
	public $response_header;
	
	
	public $response_body;
	
	
	public $response_code;
	
	
	public function __construct($url = null, $method = self::METHOD_GET, $params = null, $consumer = null) {
		$this->url = $url;
		$this->method = $method;
		$this->params = $params;
		$this->consumer = null;
		if ( $params != null ) {
			$this->setParams($params);
		}
	}
	
	
	
	/**
	 * Sets the parameters for the http request
	 *
	 * @method setParams
	 * @param array $params
	 */
	public function setParams($params) {
		if( is_array($params) ){
			$this->params = http_build_query($params, '', '&');
		} else {
			$this->params = null;
		}
	}
	
	/**
	 * Sets the headers for the http request
	 *
	 * @method setHeaders
	 * @param array $headers
	 */
	public function setHeaders($headers) {
		if( is_array($headers) ){
			$this->headers = $headers;
		}
	}
	
	
	/**
	 * Performs a GET HTTP Request
	 *
	 * @param string $url The url
	 * @param array $params The request parameters
	 * @return string 
	 */
	public function doGetRequest ($url = null, $params = null) {
		if ( $url != null ) {
			$this->url = $url;
		}
		if ( $params != null ) {
			$this->setParams($params);
		}		
		$this->method = HttpClient::METHOD_GET;
		return $this->doRequest();
	}
	
	
	/**
	 * Performs a POST HTTP Request
	 *
	 * @param string $url The url
	 * @param array $params The request parameters
	 * @return string 
	 */
	public function doPostRequest ($url = null, $params = null ) {
		if ( $url != null ) {
			$this->url = $url;
		}
		if ( $params != null ) {
			$this->setParams($params);
		}
		$this->method = HttpClient::METHOD_GET;
		return $this->doRequest();
	}
	
	
	
	public function toURL () {
		return $this->url.'?'.$this->params;
	}
	
	
	private function doRequest () {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url );
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		if($this->method == HttpClient::METHOD_POST ) {
			curl_setopt($ch, CURLOPT_POST, 		true);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$this->params);
		} elseif ( $this->method == HttpClient::METHOD_GET ) {
			curl_setopt($ch, CURLOPT_URL,$this->url.'?'.$this->params);
		}
		
		if($this->headers) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
		}
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);


		//--------------------------------
		$this->response = curl_exec($ch);
		//--------------------------------

		if(curl_errno($ch))
		{
			throw new Exception("Error no : " . curl_errno($ch) . "\nError : " . curl_error($ch));
		}
		else
		{
			$curl_info 		= curl_getinfo($ch);
			$this->response_header	= substr($this->response, 0,$curl_info['header_size']);
			$this->response_body	= substr($this->response, $curl_info['header_size']);
			$this->response_code 	= $curl_info['http_code'];
			
		}

		curl_close($ch);

		// Service returned XML error message with http status code 400.
		
		if( $this->response_code == 400 ) {
			$msg_str  = $this->response_body ;
			throw new Exception($msg_str,$this->response_code);
		} elseif( $this->response_code != 200 ) {
			$msg_str  = 	$this->response_header ;
			throw new Exception($msg_str,$this->response_code);
		}
		
		return $this->response_body;
		
	}
	
	
	
}
