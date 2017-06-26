<?php

namespace Framework;

class SnippetHtml {
	/** @author Playmore 2017 (playmoredevelop@gmail.com) */

	/**
	 * Компилирует атрибуты по массиву в строку
	 * @param array $array массив атрибутов
	 * @return string
	 */
	public function compile_attributes(array $array) {

		if(!empty($array)){
			$return = [];
			foreach($array as $attr => $val){
				if(empty($val)) continue;
				if(is_int($attr)) {
					$return[] = $val; continue;
				}
				$return[] = $attr.'="'.trim($val).'"';
			}
			return implode(' ', $return);
		}

		return '';
	}

	public function make($tag, $attributes = array(), $into = '') {

		$short = true;
		if(in_array($tag, ['a','span','div','label','i','textarea'])) $short = false;

		$attributes = $this->compile_attributes($attributes);
		if($short) {
			$short = '/>';
		} else {
			$short = ">{$into}</{$tag}>";
		}

		return "<{$tag} {$attributes}{$short}";
	}

	public function explodeToLi($value, $separator = ',') {

		return '<li>' . implode('</li><li>', explode($separator, $value)) . '</li>';
	}

	/** Функция очистки верстки описания от грязи */
	public function clear_styles($content, $allow_tags = false, $nl2br = false, $kill_style_atr = true) {

		$preglist = [
			'#<font.+?>|</font>#i' => '',
			'/(width=\".+?\")/' => '', // здесь удаляем стили в тегах table
		];

		$kill_style_atr AND $preglist['/(style=\".+?\")/'] = ''; // здесь удаляем стили в тегах

		if(!empty($allow_tags)){
			$content = strip_tags($content, $allow_tags);
		}

		$nl2br AND $preglist['#[\r\n]{2,}#'] = '<br />';

		$content = preg_replace(array_keys($preglist), array_values($preglist), trim($content));
		$content = preg_replace('#(<\/h\d>)(<br>|<br.+?\/>){1,}#i', '$1', $content); // убираем br после заголовков h1,h2,h3,h4...
		$content = preg_replace('#(<br>|<br.+?\/>){2,}#i', '<br />', $content); // убираем наплодившиеся br
		$content = preg_replace('#\s{2,}#i', '<br /><br />', $content); // заменяем кучу пробелов двумя переводами

		return $content;
	}

	/** удалить все ссылки из текста (преобразуются в span.clearlink) */
	public function clear_a(&$text) {

		$text = preg_replace('#<a[^\>]+>(.+?)<\/a>#i', '<span class="clearlink">$1</span>', $text);

		return $text;
	}

}

/* END CLASS: SnippetFilter */