<?php

@framework();

class Home extends BasePage {
	function onGetRequest() {
		$this->showView(1, 0, true);
	}
}