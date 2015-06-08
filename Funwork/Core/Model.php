<?php

@framework();

class Model extends PDO implements IBaseModel, IUserModel {//, IMessageModel {
	private $config = null;
	private $sortBy = null;

	private $pdoObj	= null;
	private $pdoStatementObj = null;

	private $isConnected = false;
	private $tablePrefix = '';
	private $userInfo	 = array();
	private $mysqlInfo	 = array();
	private $sessInfo	 = array();
	private $core		 = null;

	private static $instance = null;
	

	function __construct() {
		$this->core		 	= Core::getInstance();
		$this->pdoObj 	 	= $this;
		$this->config	 	= Config::get('mysql');
		$this->tablePrefix	= $this->config['prefix'];
		$this->mysqlInfo	= $this->config;
	}


	function connect() {
		$dsn	= "mysql:$this->mysqlInfo['host'];dbname=$this->mysqlInfo['table_prefix']$this->mysqlInfo['dbname']";
		$user	= $this->mysqlInfo['user'];
		$pass	= $this->mysqlInfo['pass'];
		$attr	= $this->mysqlInfo['attr'];

		try{
			parent::__construct($dsn, $user, $pass, $attr);
			if ($this->getAttribute(PDO::ATTR_CONNECTION_STATUS) != NULL) {
				$this->isConnected = true;
				return true;
			}
		}catch(PDOException $e) {
			return false;
		}
	}


	function getConnect() {
		return $this;
	}


	function disconnect() {
		if ($this->isConnected) {
			unset($this->pdoObj);
			$this->isConnected = false;
		}
	}

// Implementing from iBaseModel.

	function getResults($table, array $fields, $where = null, array $orderby = null, $limit = null, $top = null) {
		$fileds  = !empty($fields) ? implode(',', $fields) : '*';
		$where   = $where != null ? ' WHRER '. $where : '';
		$orderby = $orderby != null ? ' ORDER BY '. implode(',', $orderby) : '';
		$limit	 = $limit != null ? ' LIMIT '. $limit : '';
		$top	 = $top != null ? ' TOP '. $top. ' ' : '';
		$table	 = $this->tablePrefix. $table;
		$sql	 = "SELECT {$top}$fields FROM $table{$where}{$orderby}{$limit}";

		$this->pdoStatement = $this->query($sql);
		return new ModelIterator($this);
	}


	function getARow($table, array $fields, $where = null) {
		$fileds  = !empty($fields) ? implode(',', $fields) : '*';
		$where   = $where != null ? ' WHRER '. $where : '';
		$table	 = $this->tablePrefix. $table;
		$sql	 = "SELECT {$top}$fields FROM $table{$where}{$orderby}{$limit}";

		if (is_object($c = $this->query($sql)) && $c instanceof PDOStatement){
			return $c->fetch();
		}
		return null;
	}

	function insert($table, array $fields, array $values) {
		$table  = $this->tablePrefix. $table;
		$fileds = empty($fields) ? '' : '('.implode(',', fileds).')';
		$values = implode(',', $values);
		$sql	= "INSERT INTO $table{$fields} VALUES($values)";
		return $this->exec($sql) == 0 ? false : true;
	}


	function update($table, array $fields, $where = null, $orderby = null, $limit = null) {
		$fields  = join(' ,', $fields);

		if (is_array($table)) {
			$pre   = $this->tablePrefix;
			$tabs  = '';
			foreach($table as $tab) {
				$tabs .= $pre. $tab. ', ';
			}

			$table  = substr($tabs, 0, -2);
			$sql   = "UPDATE $table SET $fields{$where}";
		}else{
			$where   = $where != null ? ' WHRER '. $where : '';
			$orderby = is_array($orderby) ? ' ORDER BY '. implode(',', $orderby) : '';
			$limit	 = $limit != null ? ' LIMIT '. $limit : '';
			$sql	 = "UPDATE $table SET $fields{$where}{$orderby}{$limit}";
		}
		return 0 == $this->exec($sql) ? false : true;
	}


	function delete($table, $where = null) {
		$where  = $where != null ? ' WHERE '. $where : '';

		if (is_array($table)) {
			$pre   = $this->tablePrefix;
			$tabs  = '';
			foreach($table as $tab) {
				$tabs .= $pre. $tab. ', ';
			}

			$table  = rtrim($tabs, ', ');
			$sql   = "DELETE FROM $table{$where}";
		}else{
			$sql   = "DELETE FROM $table{$where}";
		}
		return $this->exec($sql) == 0 ? false : true;
	}


	function createDatabase($name) {
		return $this->exec('CREATE DATABASE '. $name);
	}


	function createTable($name, $fields) {
		return $this->exec('CREATE TABLE '. $name. ' '. $fields);
	}


	function deleteDatabase($name) {
		return $this->exec('DROP DATABASE '. $name);
	}


	function deleteTable($name) {
		return $this->exec('DROP TABLE '. $name);
	}


	function renameTable($table, $newName) {
		return $this->exec('RENAME TABLE '. $table. ' TO '. $newName);
	}


	function createIndexOn($indexname, $fieldname) {
		return $this->exec('CREATE INDEX '. $indexname. ' ON '. $fieldname);
	}

// Implementing from IUserModel

	function addUser(array $userInfo) {
		if (empty($userInfo)) {
			return false;
		}

		if ($this->insert('user', array(), $userInfo) && $this->insert('online', array(),
			array("'{$userInfo['uid']}'", 0, '"'.date('Y-m-d G:i:s').'"', '"'.date('Y-m-d G:i:s').'"', 0))) {
			return true;
		}
		return false;
	}


	function deleteUser($uid) {
		$uid = intval($uid);
		return $this->delete('user', 'uid='.$uid) && $this->delete('online', 'uid='.$uid);
	}


	function changeUserName($uid, $newName) {
		$uid = intval($uid);
		return $this->update('user', array('name='.$newName), 'uid='.$uid);
	}


	function isOnline() {
		if (SID) {
			return $_SESSION['user_info']['is_online'];
		}
	}


	function isLogined() {
		if (!empty($_SESSION['user_info'])) {
			return true;
		} 
		return false;
	}


	function getUid() {
		if (SID) {
			if ($row = $this->getARow('user', array('uid'), 'sid="'.SID.'"')) {
				return (int) $row['uid'];
			}
		}else{
			return $_SESSION['user_info']['uid'];
		}
		return null;
	}


	function login(array $loginInfo = null) {
		if (!empty($loginInfo) && IS_POST) {
			if (($u = $this->getARow('user', array(),
				"name='{$loginInfo['name']}' and password='{$loginInfo['pass']}'")) != null) {
				if (SID) {
					session_unset();
					session_destroy();
					session_start();
					session_regenerate_id(true);
				}

				$time = $this->getARow('online_user', array(), 'uid='.$u['uid']);

				$u = array_merge($u, $time);
				$this->userInfo = $u;
				$_SESSION['user_info'] = $u;

				$this->update('user', array('sid="'.session_id().'"'), 'uid='.$u['uid']);
				$this->update('online_user', array('is_online=1', 'last_active=now()',
				'login_time=now()', 'first_online=now()'), 'uid='.$u['uid']);

				return true;
			}
		}
		return false;
	}


	function isGuest() {
		if (!SID || !$this->isLogined())
			return true;
		return false;
	}


	function updateUsersStatus($uid = null) {
		if($uid != null) {
			if (time() - $_SESSION['user_info']['first_online'] > 86400) {
				$this->update('online_user', array('is_online=1', 'first_online=now()', 'last_active=now()'),
						'uid='.$uid);
				$_SESSION['user_info']['first_online'] = time();
				$_SESSION['user_info']['last_active']  = time();
			}else if ($this->isOnline()) {
				if (time() - $_SESSION['user_info']['last_active'] > $this->config['user']['online_update_time']) {
					$this->update('online_user', array('online_time=online_time+(now()-last_active),
								last_active=now()'), 'uid='.$uid);
					$_SESSION['user_info']['last_active']  = time();
				}
			}else{
				$this->update('online_user', array('is_online=1, last_active=now()'), 'uid='.$uid);
				$_SESSION['user_info']['last_active']  = time();
				$_SESSION['user_info']['is_online'] = true;
			}
			return;
		}

		$this->update('online_user', array('is_online=0', 'online_time=online_time+(last_active-now())'),
					'last_active-now()>'.$this->config['user']['max_no_action_time']);
		$_SESSION['user_info']['is_online'] = false;
	}


	function logout($uid) {
		session_unset();
		session_destroy();
		session_start();
		session_regenerate_id(true);

		return $this->update('online_user',
				array('is_online=0', 'online_time=online_time+(last_active-now())'),
				'uid='.$uid);
	}


	function getUserInfo() {
		if (!empty($this->userInfo))
			return $this->userInfo;

		return $this->getARow('user', array(), 'sid="'.SID.'"');
	}


	function getOnlineUsers() {
		return $this->getARow('online_user', array('count(*) as online_users'), 'is_online=1');
	}


	function getUsersTotal() {
		return $this->getARow('user', array('COUNT(*) as users'));
	}


	function postTopic(array $topic) {
		if (empty($topic)) {
			return false;
		}

		return $this->insert('topic', array(), $topic);
	}


	function deleteTopic($tid) {
		return $this->delete('topic', 'tid='.$tid);
	}


	function deleteUserTopics($uid) {
		return $this->delete('topic', 'uid='.$uid);
	}


	function deleteTopics($cid) {
		return $this->delete('topic', 'cid='.$cid);
	}


	function getTopics($cid) {
		return $this->getResults('topic', array(), 'cid='.$cid);
	}


	function setTopTopic($tid) {
		return $this->update('topic', array('is_top=1'), 'tid='.$tid);
	}


	function setGoodTopic($id) {
		return $this->update('topic', array('is_good=1'), 'tid='.$tid);
	}


	function cancelTopTopic($tid) {
		return $this->update('topic', array('is_top=0'), 'tid='.$tid);
	}


	function cancelGoodTopic($tid) {
		return $this->update('topic', array('is_good=0'), 'tid='.$tid);
	}


	function setOldTopic($tid) {
		return $this->update('topic', array('post_time=post_time-1000'), 'tid='.$tid);
	}


	function postReply($tid, array $replyInfo) {
		return $this->insert('reply', array(), $replyInfo);
	}


	function deleteReply($rid) {
		return $this->delete('reply', 'rid='.$rid);
	}


	function countUserTopics($uid) {
		return $this->getARow('topic', array('count(title) as topics'), 'uid='.$uid.' GROUP BY uid');
	}


	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new self();
		}
		return self::$instance;
	}


// Implementing from IMessageModel.

	function __destruct() {
		if (method_exists($this->pdoObj, __METHOD__)) {
			parent::__desctruct();
		}
	}
}

