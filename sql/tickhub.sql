USE tickhub;

--------------------------------------------------------------------------------
--------------------------------------------------------------------------------
--------------------------------------------------------------------------------

CREATE TABLE user (
	`id`  INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	`email` VARCHAR(255) NOT NULL,
	`password` VARCHAR(40) NOT NULL,
	`given_name` VARCHAR(255) NULL,
	`tickspot_user_id` INT UNSIGNED NULL,
	`github_access_token` VARCHAR(255) NULL,
	`github_token_type` VARCHAR(10) NULL	
);

CREATE TABLE user_session (
	`id`			INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	`session_id`		CHAR(26) NOT NULL,
	`server_signature`	CHAR(40) NOT NULL,
	`user_id`		INT UNSIGNED NOT NULL REFERENCES user(id),
	`ip_address`		CHAR(15) NOT NULL,
	`last_login_dt`		TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`last_activity_dt`	TIMESTAMP NULL,
	`status`		ENUM('active','deleted') NOT NULL DEFAULT 'active'
) CHARSET=utf8;


CREATE TABLE user_commit (
	`id`		INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	`user_id`	INT UNSIGNED NOT NULL REFERENCES user(id),
	`commit_id`	INT UNSIGNED NOT NULL REFERENCES github_commit(id),
	`status`	ENUM('added','hidden') NOT NULL DEFAULT 'added'
) CHARSET=utf8;

--------------------------------------------------------------------------------
--------------------------------------------------------------------------------
--------------------------------------------------------------------------------


CREATE TABLE tickspot_user (
	`id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	`user_id` INT UNSIGNED NOT NULL UNIQUE,
	`first_name` VARCHAR(255) NULL,
	`last_name` VARCHAR(255) NULL,
	`email` VARCHAR(255) NOT NULL,
	`password` MEDIUMTEXT NULL
) CHARSET=utf8, ENGINE=InnoDB;


CREATE TABLE tickspot_client (
	`id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	`client_id` INT UNSIGNED NOT NULL UNIQUE,
	`name` VARCHAR(255) NOT NULL
) CHARSET=utf8, ENGINE=InnoDB;

CREATE TABLE tickspot_project (
	`id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	`project_id` INT UNSIGNED NOT NULL UNIQUE,
	`name` VARCHAR(255) NOT NULL,
	`budget` DECIMAL(15,2) NOT NULL,
	`client_id` INT UNSIGNED NOT NULL REFERENCES tickspot_client(client_id),
	`opened_on` VARCHAR(19) NOT NULL,
	`closed_on` VARCHAR(19) NULL,
	`created_at` VARCHAR(19) NOT NULL,
	`updated_at` VARCHAR(19) NOT NULL
) CHARSET=utf8, ENGINE=InnoDB;

CREATE TABLE tickspot_task (
	`id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	`task_id` INT UNSIGNED NOT NULL UNIQUE,
	`name` VARCHAR(255) NOT NULL,
	`position` INT UNSIGNED NOT NULL,
	`project_id` INT UNSIGNED NOT NULL REFERENCES tickspot_project(project_id),
	`opened_on` VARCHAR(19) NOT NULL,
	`closed_on` VARCHAR(19) NULL,
	`budget` DECIMAL(15,2) NOT NULL
) CHARSET=utf8, ENGINE=InnoDB;

CREATE TABLE tickspot_user_client (
	`id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	`user_id` INT UNSIGNED NOT NULL REFERENCES tickspot_user(user_id),
	`client_id` INT UNSIGNED NOT NULL REFERENCES tickspot_client(client_id),
	UNIQUE(`user_id`, `client_id`)
) CHARSET=utf8, ENGINE=InnoDB;

CREATE TABLE tickspot_user_project (
	`id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	`user_id` INT UNSIGNED NOT NULL REFERENCES tickspot_user(user_id),
	`project_id` INT UNSIGNED NOT NULL REFERENCES tickspot_project(project_id),
	UNIQUE(`user_id`, `project_id`)
) CHARSET=utf8, ENGINE=InnoDB;

CREATE TABLE tickspot_user_task (
	`id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	`user_id` INT UNSIGNED NOT NULL REFERENCES tickspot_user(user_id),
	`task_id` INT UNSIGNED NOT NULL REFERENCES tickspot_task(task_id),
	UNIQUE(`user_id`, `task_id`)
) CHARSET=utf8, ENGINE=InnoDB;

CREATE VIEW v_clients_projects_tasks AS 
SELECT 
	u.id as user_id,
	tu.id as tickspot_user_id,
	tu.email as tickspot_user_email,
	c.client_id,
	c.name as client_name,
	p.project_id,
	p.name as project_name,
	t.task_id,
	t.name as task_name
FROM 
	user u,
	tickspot_user tu,
	tickspot_client c,
	tickspot_project p,
	tickspot_task t,
	tickspot_user_client uc,
	tickspot_user_project up,
	tickspot_user_task ut
WHERE
	c.client_id = p.client_id AND
	p.project_id = t.project_id AND
	uc.client_id = c.client_id AND
	uc.user_id = tu.user_id AND
	up.project_id = p.project_id AND
	up.user_id = tu.user_id AND
	ut.task_id = t.task_id AND
	ut.user_id = tu.user_id AND
	u.tickspot_user_id = tu.user_id
ORDER BY 
	tu.email ASC,
	c.name ASC,
	p.name ASC,
	t.name ASC;

--------------------------------------------------------------------------------
--------------------------------------------------------------------------------
--------------------------------------------------------------------------------

CREATE TABLE github_repo (
	`id`  INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	`repo_id` INT UNSIGNED NOT NULL,
	`name` VARCHAR(255) NOT NULL,
	`description` MEDIUMTEXT NULL,
	`language` VARCHAR(50) NULL DEFAULT NULL,
	`url` MEDIUMTEXT NOT NULL,
	`clone_url` MEDIUMTEXT NOT NULL,
	`html_url` MEDIUMTEXT NOT NULL,
	`private` ENUM('YES', 'NO') NOT NULL DEFAULT 'NO',
	`updated_at` VARCHAR(20) NOT NULL
) CHARSET=utf8, ENGINE=InnoDB;

CREATE TABLE github_user_repo (
	`id`  INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	`repo_id` INT UNSIGNED NOT NULL REFERENCES github_repo(repo_id),
	`user_id`  INT UNSIGNED NOT NULL REFERENCES user(id),
	UNIQUE(`repo_id`, `user_id`)
) CHARSET=utf8, ENGINE=InnoDB;

CREATE TABLE github_branch (
	`id`  INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	`repo_id` INT UNSIGNED NOT NULL REFERENCES github_repo(repo_id),
	`name` VARCHAR(100) NOT NULL,
	UNIQUE(`repo_id`, `name`)
) CHARSET=utf8, ENGINE=InnoDB;


CREATE TABLE github_commit (
	`id`  INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	`repo_id` INT UNSIGNED NOT NULL REFERENCES github_repo(repo_id),
	`message` TEXT NOT NULL,
	`author_name` VARCHAR(255) NOT NULL,
	`author_email` VARCHAR(255) NOT NULL,
	`date` VARCHAR(20) NOT NULL,
	`sha` VARCHAR(40) NOT NULL,
	`url` MEDIUMTEXT NOT NULL
) CHARSET=utf8, ENGINE=InnoDB;


CREATE VIEW v_commits AS
SELECT 
  u.id as user_id,
  r.repo_id,
  r.name as repo_name,
  c.id as commit_id,
  c.message,
  c.author_name,
  c.author_email, 
  c.date
FROM 
  github_repo r,
  github_user_repo ur,
  github_commit c,
  user u
WHERE
  u.id = ur.user_id AND
  ur.repo_id = r.repo_id AND
  c.repo_id = r.repo_id
ORDER BY 
  repo_id desc, date desc;

--------------------------------------------------------------------------------
--------------------------------------------------------------------------------
--------------------------------------------------------------------------------

CREATE VIEW `tickhub`.`v_tickhub_users` AS
SELECT
  u.id as user_id,
  u.tickspot_company as tickspot_company,
  tu.user_id as tickspot_user_id,
  tu.first_name,
  tu.last_name,
  tu.email,
  tu.password
FROM 
  `tickhub`.`tickspot_user` tu,
  `tickhub`.`user` u
WHERE
  u.tickspot_user_id = tu.user_id AND
  NOT tu.password IS NULL;

