<?php


/**
 * Description of GithubRepository
 *
 * @author jaraya
 */
class GithubRepository {
	
	const TABLE = 'github_repo';
	
	const TABLE_USER_REPO = 'github_user_repo';
	
	private $id;
	
	private $repo_id;
	
	private $name;
	
	private $description;
	
	private $language;
	
	private $url;
	
	private $clone_url;
	
	private $html_url;
	
	private $private;
	
	private $updated_at;
	
	private $branches;
	
	private $commits;
	

	public function getID () {
		return $this->id;
	}
	
	public function getRepositoryID () {
		return $this->repo_id;
	}
	
	public function getURL () {
		return $this->url;
	}
	
	public function getBranches() {
		return $this->branches;
	}
	
	
	public function __construct( $id = null ) {
		
	}
	
	
	
	public static function getRepositories ($userID) {
		$repos = array();
		$userID = Utils::enforcePositiveIntegerValue($userID);
		$user = new User($userID);
		if ( $user->getGithubAccessToken() == null ) 
			return $repos;
		$httpClient = new HttpClient();
		$httpClient->setHeaders(array("Authorization: token {$user->getGithubAccessToken()}"));
		$response = $httpClient->doGetRequest(GITHUB_API_URL.'/user/repos');
		$repositories = json_decode($response, true);
		foreach ( $repositories as $jsonRepo ) {
			$repo = new GithubRepository();
			$repo->repo_id = $jsonRepo['id'];
			$repo->name = $jsonRepo['name'];
			$repo->description = $jsonRepo['description'];
			$repo->language = $jsonRepo['language'];
			$repo->url = $jsonRepo['url'];
			$repo->clone_url = $jsonRepo['clone_url'];
			$repo->html_url = $jsonRepo['html_url'];
			$repo->private = $jsonRepo['private'] == 1 ? 'YES' : 'NO';
			$repo->updated_at = $jsonRepo['updated_at'];
			$repo->branches = GithubBranch::getBranches($userID, $repo);
			$repo->commits = GithubCommit::getCommits($userID, $repo);
			$repo->save();
			GithubUser::updateUserPermisions($userID, self::TABLE_USER_REPO, 'repo_id', $repo->repo_id);
			array_push($repos, $repo);
		}	
		return $repos;
	}
	
	
	public function save() {
		if ( $this->repo_id == null ) {
			throw new Exception('Invalid repository Id');
		}
		$this->checkID();
		
		$sql = ($this->id ? 'UPDATE ' : 'INSERT INTO ') . self::TABLE . ' SET ';
		
		$sql .= 'repo_id	= :repo_id,
			 name		= :name,
			 description	= :description,
			 language	= :language,
			 url		= :url,
			 clone_url	= :clone_url,
			 html_url	= :html_url,
			 private	= :private,
			 updated_at	= :updated_at
			 ';
		$params = array(
		    ':repo_id'		=> $this->repo_id,
		    ':name'		=> $this->name,
		    ':description'	=> $this->description,
		    ':language'		=> $this->language,
		    ':url'		=> $this->url,
		    ':clone_url'	=> $this->clone_url,
		    ':html_url'		=> $this->html_url,
		    ':private'		=> $this->private,
		    ':updated_at'	=> $this->updated_at
		);
		
		if ( $this->id ) {
			$sql .= ' WHERE id = :id ';
			$params[':id'] = $this->id;
		}
		
		$pdo = Database::getInstance()->getConnection();
		$stmt = $pdo->prepare($sql);
		$stmt->execute($params);
		$rows_affected = $stmt->rowCount();
		if ($this->id == null && $rows_affected == 1) {
			$this->id = $pdo->lastInsertId();
		}
		foreach ($this->branches as $branch) $branch->save();
		foreach ($this->commits as $commit) $commit->save();
		return ($rows_affected == 1 || $rows_affected == 0);
	}
	
	
	
	private function checkID () {
		$pdo = Database::getInstance()->getConnection();
		$sql = "SELECT id FROM " . self::TABLE . " WHERE repo_id = :repo_id ";
		$stmt = $pdo->prepare($sql);
		$sucess = $stmt->execute(array(':repo_id' => $this->repo_id));
		if ($sucess) {
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row && count($row) > 0) {
				$this->id = $row['id'];
			}
		}
	}
	
}

?>
