<?php

namespace Framework;

class SnippetUrl {
	/** @author Playmore 2017 (playmoredevelop@gmail.com) */
	
	private $_get = false;

	public function __construct() {

		// фильтруем при первом вызове
		$this->_get = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
	}

	/**
	 * Вернет готовую строку запроса GET с подменой переменных
	 * - дополнительно можно отфильтровать допустимые ключи, которые могут быть в ссылке (остальные будут отброшены)
	 * - можно убрать конкретные переменные из запроса передав $replace[key => false]
	 * @param type $replace
	 * @param type $allow
	 * @return type
	 */
    public function getQueryStringReplace($replace = [], $allow = [], $withbase = true) {

		$get = $this->_get;

		if(!empty($allow)){
			$get = array_intersect_key($get, array_flip($allow));
		}

		foreach($replace as $key => $val){
			if($val === false){
				unset($get[$key]);
			} else {
				$get[$key] = $val;
			}
		}

		$url = '';
		if($withbase){
			$url = parse_url($_SERVER['REQUEST_URI']);
			$url = $url['path'];
		}
		
		$get = http_build_query($get);
		
		if(mb_strlen($get)){
			return $url.'?'.$get;
		}

		return $url;
	}
	
}

/* END CLASS: SnippetUrl */