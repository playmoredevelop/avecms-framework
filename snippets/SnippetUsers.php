<?php

namespace Framework;

class SnippetUsers {
	/** @author Playmore 2017 (playmoredevelop@gmail.com) */

	private $activity_marker = 'citrus.marker';
	private $activity_expire = 15552000; // 6 month

    public function saveActivity($uid, $data = []) {

		$this->setCookieActivity();

		
	}

	public function setCookieActivity() {

		if(empty($_COOKIE[$this->activity_marker])){

			$hash = snippets()->string->random(32);
			setcookie($this->activity_marker, $hash, (TIME + $this->activity_expire), '/', '', false, true);
		}
	}
}

/* END CLASS: SnippetUsers */