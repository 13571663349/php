<?php

@framework();

interface ITopicModel {
	function postTopic(array $topic);
	function deleteTopic($tid);
	function deleteUserTopics($uid);
	function deleteTopics($cid);
	function getTopics($cid);
	function setTopTopic($tid);
	function setOldTopic($tid);
	function setGoodTopic($tid);
	function cancelTopTopic($tid);
	function cancelGoodTopic($tid);
	function postReply($tid, array $reply);
	function deleteReply($rid);
	function countUserTopics($uid);
}

interface IUserModel {
	public function addUser(array $userInfo);
	public function deleteUser($uid);
	public function changeUserName($uid, $newname);
	public function isOnline();
	public function login(array $loginInfo = null);
	public function isLogined();
	public function isGuest();
	public function getUid();
	public function logout($uid);
	public function getUserInfo();
	public function getOnlineUsers();
	public function getUsersTotal();
	public function updateUsersStatus();
}

interface IMessageModel {
	const SORTBY_NAME = 0x01;
	const SORTBY_DATE = 0x02;
	const SORTBY_SIZE = 0x03;
	const SORTBY_VIEW = 0x04;

	public function setSortBy($mode);
	public function sendMessage(Message $msg);
	public function getMessages($uid);
	public function getReadedMessages();
	public function getNotReadMessages();
	public function getAllMessages();
	public function deleteMessage($mid);
	public function deleteMessages($uid);
	public function deleteReadedMessages();
	public function deleteNotReadMessages();
}


interface IBaseModel {

	//Predefine methods.
	public function getResults($table, array $fields, $where = null, array $orderby = null, $limit = null, $top = null);
	public function getARow($table, array $fields, $where = null);
	public function insert($table, array $fields, array $values);
	public function update($table, array $fields, $where = null);
	public function delete($table, $where = null);
	public function connect();
	public function disconnect();
	public function getConnect();
	public function createDatabase($name);
	public function deleteDatabase($name);
	public function renameTable($table, $newName);
	public function createTable($table, $fields);
	public function deleteTable($table);
	public function createIndexOn($indexname, $fieldname);
}