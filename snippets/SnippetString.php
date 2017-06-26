<?php

namespace Framework;

class SnippetString {
	/** @author Playmore 2017 (playmoredevelop@gmail.com) */

	/**
	 * ->plural($N, ['булочка','булочки','булочек']) // 1,2,5
	 * @param int $n
	 * @param array $forms
	 * @return string
	 */
	public function plural($n, array $forms) {
		return $n % 10 == 1 && $n % 100 != 11 ? $forms[0] : ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 || $n % 100 >= 20) ? $forms[1] : $forms[2]);
	}
	
	/**
	 * Рандомная строка указанной длинны (для паролей и хэшей)
	 * @param type $limit
	 * @return type
	 */
	public function random($limit = 10) {

		// рандом строка
		$string = sha1(md5(time()));
		$string = str_shuffle($string);
		// делим на 2 сегмента
		$part1 = ceil($limit / 2);
		$part2 = $limit - $part1;
		// половину символов берем с начала строки
		$part1 = substr($string, 0, $part1);
		// заглавные берем с конца строки
		$part2 = mb_strtoupper(substr($string, -$part2));
		// перемешиваем буквы
		return str_shuffle($part1 . $part2);
	}

	/**
	 * Генерация ЧПУ сегмента из русских символов и заголовков
	 * @param type $text
	 * @param type $separator
	 * @return type
	 */
	public function slug($text, $separator = '-') {

		$text = preg_replace('#[^a-zA-Zа-яА-Я0-9\s]+#ui', '', $text);
		$text = mb_strtolower(trim($text));

		$text = strtr($text, array(
			"а" => "a", "б" => "b", "в" => "v", "г" => "g", "д" => "d",
			"е" => "e", "ё" => "e", "ж" => "j", "з" => "z", "и" => "i",
			"й" => "y", "к" => "k", "л" => "l", "м" => "m", "н" => "n",
			"о" => "o", "п" => "p", "р" => "r", "с" => "s", "т" => "t",
			"у" => "u", "ф" => "f", "х" => "h", "ц" => "c", "ч" => "ch",
			"ш" => "sh", "щ" => "sch", "ъ" => "y", "ы" => "i", "ь" => "",
			"э" => "e", "ю" => "ju", "я" => "ja",
		));
		$text = preg_replace('#\s+#', $separator, $text);

		return $text;
	}

	/**
	 * Хэш из любого типа данных
	 * @param type $mixed
	 * @return type
	 */
	public function hash($mixed) {

		return sha1(var_export($mixed, true));
	}

	public function daterus($timestamp, $format = 'm d, Y', $month_form = 0) {
		//Май 24, 2017
		$date = date('d/m/Y/H/i', $timestamp);
		$date = explode('/', $date);
		$assoc = [
			'd' => $date[0],
			'm' => $date[1],
			'Y' => $date[2],
			'H' => $date[3],
			'i' => $date[4],
		];
		$month = [
			'01' => ['Январь', 'Января'],
			'02' => ['Февраль', 'Февраля'],
			'03' => ['Март', 'Марта'],
			'04' => ['Апрель', 'Апреля'],
			'05' => ['Май', 'Мая'],
			'06' => ['Июнь', 'Июня'],
			'07' => ['Июль', 'Июля'],
			'08' => ['Август', 'Августа'],
			'09' => ['Сентябрь', 'Сентября'],
			'10' => ['Октябрь', 'Октября'],
			'11' => ['Ноябрь', 'Ноября'],
			'12' => ['Декабрь', 'Декабря'],
		];
		if($month_form > 1) {
			$month_form = 0;
		}
		$assoc['m'] = $month[$assoc['m']][$month_form];

		return str_replace(array_keys($assoc), array_values($assoc), $format);
	}

	public function br2p($content) {

		$content = preg_split('#\<br\>|\<br \/\>#ui', $content);

		foreach($content as $key => &$one){
			$one = trim($one);
			if(!mb_strlen($one)){
				unset($content[$key]);
			}
		}

		return '<p>'.implode('</p><p>', $content).'</p>';
	}

}

/* END CLASS: SnippetString */