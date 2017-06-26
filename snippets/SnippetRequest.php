<?php

namespace Framework;

class SnippetRequest {

	public function isAjax() {

		return boolval(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
	}

	public function get($name, $default = false, $filter = FILTER_SANITIZE_STRING) {

		if(array_key_exists($name, $_REQUEST)){

			if(is_array($_REQUEST[$name])){
				return filter_var_array($_REQUEST[$name], $filter);
			} else {
				return filter_var($_REQUEST[$name], $filter);
			}
		}

		return $default;
	}
	
	public function cookie($name, $default = false) {

		if(array_key_exists($name, $_COOKIE)){

			return filter_var($_COOKIE[$name], FILTER_SANITIZE_STRING);
		}

		return $default;
	}

	public function getAll() {

		$post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
		$get = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);

		return [
			'GET' => $get,
			'POST' => $post
		];
	}
	
	public function uri($parse = false) {
		
		$uri = $_SERVER['REQUEST_URI'];
		
		if($parse){
			
			$exp = parse_url($uri)['path'];
			$exp = explode('/', trim($exp, '/'));
			$uri = [
				'controller' => !(empty($exp[0])) ? $exp[0] : 'index',
				'method' => !empty($exp[1]) ? $exp[1] : 'index',
				'params' => 1
			];
			array_shift($exp);
			array_shift($exp);
			$uri['params'] = $exp;
			
		}
		
		return $uri;
	}
	
}