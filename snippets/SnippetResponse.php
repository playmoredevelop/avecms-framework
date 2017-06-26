<?php

namespace Framework;

class SnippetResponse {
	/** @author Playmore 2017 (playmoredevelop@gmail.com) */

    public function json($data) {

		header('Content-Type: application/json', true);
		if(is_string($data)){
			exit($data);
		}
		exit(json_encode($data));
	}

	public function redirect($uri, $code = 301) {
		
		header('Location: ' . $uri, true, 301);
		exit();
	}
	
	public function cookie($name, $value, $ttlm = 20160, $secure = false, $httponly = true) {
		
		return setcookie($name, $value, (TIME + ($ttlm * 60)), '/', '', $secure, $httponly);
	}
}

/* END CLASS: SnippetResponse */