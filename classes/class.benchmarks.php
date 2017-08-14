<?php

namespace Framework;

class Benchmarks {

	private $_benchmarks = array();
	private $_counters = array();
	private $_queries = array();

	public function __construct() {

		$this->start('Общий замер');
	}

	public function start($section) {

		$this->_benchmarks[$section] = array(
			'time_start' => microtime(true),
			'time_stop' => null,
			'memory_start' => memory_get_usage(),
			'memory_stop' => null
		);

		return $section;
	}

	public function count($section) {

		if(isset($this->_counters[$section])) {
			$this->_counters[$section]++;
		} else {
			$this->_counters[$section] = 1;
		}

		return $section;
	}

	public function query_start($q) {

		$hash = md5($q);
		$this->_queries[$hash] = array(
			'time_start' => microtime(true),
			'time_stop' => null,
			'query' => $q,
		);

		return $hash;
	}

	public function query_stop($q) {

		$hash = md5($q);
		$this->_queries[$hash]['time_stop'] = microtime(true);

		return $hash;
	}

	public function stop($section) {

		$this->_benchmarks[$section]['time_stop'] = microtime(true);
		$this->_benchmarks[$section]['memory_stop'] = memory_get_usage();

		return $section;
	}

	public function display($with_queries = false) {

		$this->stop('Общий замер');
		$totals = array();
		foreach ($this->_benchmarks as $section => $data) {
			$totals[$section] = '';
			if ($data['time_start'] AND $data['time_stop']) {
				$t = $data['time_stop'] - $data['time_start'];
				$class = 'middle';
				$t > 1 AND $class = 'danger';
				$t < 0.1 AND $class = 'perfect';
				$totals[$section] = '<span class="'.$class.'">'.self::_units('time', $t).'</span>';
			}
			if ($data['memory_start'] AND $data['memory_stop']) {
				$totals[$section] .= ' (Памяти выделено: '.self::_units('bytes', $data['memory_stop'] - $data['memory_start']).')';
			}
		}
		foreach($this->_counters as $section => $count){
			$totals[$section] = '<span class="middle">'.$count.'</span>';
		}

		if($with_queries){
			$replace = array(
				'#(select|from|join|left|\swhere|group by)#i' => '<b style="color:blue">$1</b>',
				'#(\son\s|\sand\s|\sor\s|\sas\s)#i' => '<b style="color:green">$1</b>',
			);
			foreach($this->_queries as $data){

				if ($data['time_start'] AND $data['time_stop']) {
					$t = $data['time_stop'] - $data['time_start'];
					$class = 'middle';
					$t > 0.5 AND $class = 'danger';
					$t < 0.05 AND $class = 'perfect';
					$label = '<span class="'.$class.'">'.self::_units('time', $t).'</span>';
					$data['query'] = preg_replace(array_keys($replace), array_values($replace), $data['query']);
					$totals['Qs'][] = array('label' => $label, 'query' => $data['query']);
				}

			}
		}

		$html = array();
		foreach($totals as $name => $value){
			if($name == 'Qs'){
				foreach($value as $v){
					$html[] = '<li><strong>'.$v['label'].': </strong>'.$v['query'].'</li>';
				}
			} else {
				$html[] = '<li><strong>'.$name.': </strong>'.$value.'</li>';
			}
		}

		array_unshift($html, '<ul class="benchmark-container">');
		array_unshift($html, '<style>
			ul.benchmark-container{
				list-style-type: none;
				padding: 20px;
				margin: 0;
				line-height: 2em;
				font-family: Consolas, Monospace;
				font-size: 8pt;}
			ul.benchmark-container span{
				background-color: #777;
				padding: 4px 6px;
				color: #fff;
				border-radius: 3px;}
			ul.benchmark-container .danger {background-color: #F1665E;}
			ul.benchmark-container .middle {background-color: #FBAD22;}
			ul.benchmark-container .perfect {background-color: #6C3;}
		</style>');
		array_push($html, '</ul>');

		echo implode('', $html);

		//debug($totals, false, true);
	}

	/**
	 * Returns a human readable time (ms, s, m)
	 */
	private  function _units($type, $value) {
		switch ($type) {
			case 'time':
				$value = floatval($value);
				if ($value <= 1) {
					$unit = 'сек';
					$amount = round($value, 3);
				} else if ($value <= 60) {
					$unit = 'сек';
					$amount = round($value, 2);
				} else if ($value <= 3600) {
					$unit = 'мин';
					$amount = round($value / 60, 2);
				}
				return "$amount $unit";
			case 'bytes':
				$sizes = array(' B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
				for ($i = 0; abs($value) > 1024 && isset($sizes[$i + 1]); ++$i)
					$value /= 1024;
				return sprintf("%3.0f %2s", $value, $sizes[$i]);
		}
	}

}