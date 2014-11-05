<?php

/**
 * Description of Github
 *
 * @author jaraya
 */
class Github {
	
	private $user_id;
	private $access_token;
	private $token_type;
	
	public function __construct( $userID ) {
		if ( $userID == null ) {
			throw new Exception("Invalid reference ID");
		}
		$this->user_id = $userID;
	}
	
	
	public function getAccessToken ( $code ) {
		$httpClient = new HttpClient(GITHUB_ACCESS_TOKEN_URL, HttpClient::METHOD_POST, array(
			'client_id' => GITHUB_CLIENT_ID,
			'client_secret' => GITHUB_SECRET,
			'redirect_uri' => GITHUB_CALLBACK_URL,
			'code' => $code
		));
		
		$response = $httpClient->doPostRequest();
		parse_str ($response,$req_token);
		if ( isset($req_token['access_token']) && isset($req_token['token_type']) ) {
			$this->access_token	= $req_token['access_token'];
			$this->token_type 	= $req_token['token_type'];
			$this->save();
			return new OAuthToken($this->access_token, '');
		} 
		return null;
	}
	
	public static function getAuthorizationURL ($userID) {
		$httpClient = new HttpClient(GITHUB_AUTHORIZE_URL, HttpClient::METHOD_GET, array(
			'client_id' => GITHUB_CLIENT_ID,
			'redirect_uri' => str_replace('{ref}', $userID, GITHUB_CALLBACK_URL),
			'scope' => 'user,public_repo,repo,gist'
		));
		return $httpClient->toURL();
	}
	
	
	public function save () {
		$user = new User($this->user_id);
		$user->setGithubAccessToken($this->access_token);
		$user->setGithubTokenType($this->token_type);
		$user->save();
	}
	
	
	public static function cron () {
		$pdo = Database::getInstance()->getConnection();
		$sql = 'SELECT id FROM ' . User::TABLE . ' WHERE NOT github_access_token IS NULL ';
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if ( $rows && count($rows) ) {
			foreach ( $rows as $row ) {
				GithubRepository::getRepositories($row['id']);
			}
		}
	}
	
	
}
