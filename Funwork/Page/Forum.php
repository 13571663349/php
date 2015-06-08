<?php

/*
 * Forum page.
 */


class Forum extends BasePage {
	public $topicID = -1;
	public $forumID = -1;
	public $topicInfo = array();


	public function getTopic($id) {
		if (!is_int($id))
			$this->exception("The id must be int!");
	}
}