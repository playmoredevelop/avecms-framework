<?php

namespace Framework;

class SnippetGenerator {
	/** @author Playmore 2017 (playmoredevelop@gmail.com) */

    public function keywords($text, $count = 7) {

		$text = preg_replace('#[^а-яА-Я\s]+#ui', '', strip_tags($text));
		$text = preg_replace('#\s[а-яА-Я]{1,3}\s#ui', ' ', mb_strtolower($text));
		$text = preg_replace('#\s[а-яА-Я]{1,4}\s#ui', ' ', $text);
		$array = preg_split('#\s+#i', $text);

		if(count($array) > $count){
			$array = array_count_values($array);
			arsort($array);
			$array = array_slice(array_keys($array), 0, $count);
			shuffle($array);
		}
		
		return implode(', ', $array);
	}
}

/* END CLASS: SnippetGenerator */