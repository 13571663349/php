<?php

class MusicPlayer extends BasePage {
	public function onGetRequest() {
		$this->view->importCss('music.css');
		$this->view->assignVariable('music_list', json_encode($this->getMusicList()));
		$this->showView(-1, 1, true);
	}


	private function getMusicList() {
		$musics = array();
		$opened = opendir(iconv('UTF-8', 'GB2312', "F:\\我的音乐\\"));
		while($file = readdir($opened)) {
			if ($file == '.' || $file == '..' || substr($file, -3, 3) <> 'mp3') {
				continue;
			}
			$musics[] = mb_convert_encoding($file, 'UTF-8', 'GBK');
		}
		closedir($opened);
		return $musics;
	}
}