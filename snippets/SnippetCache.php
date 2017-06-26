<?php

namespace Framework;

class SnippetCache {
	/** @author Playmore 2017 (playmoredevelop@gmail.com) */

	/** @var boolean ВКЛ\ВЫКЛ Кэша. false - все методы будут возвращать false и перестанут выполняться */
	public $allow = true;
	/** @var boolean Компрессия контента поступившего в кэш */
	public $compression = true;
	/** @var string Путь до папки в которую складываем кэш. Если папки нет - она будет создана */
	public $cachefolder = FPATH.'/cache/framework/';

	private $_errors = [];

	# Кэшируем данные в файл на определенное кол-во минут
	public function save($cachekey, $data, $ttlm = 30) {

		if(false === $this->allow OR !$this->_checkfolder()) return false;

		# если включена компрессия тогда убираем все переводы и табы в контенте (полезно для html)
		if($this->compression){
			is_string($data) AND $data = preg_replace('#[\n\r\s]{2,}|[\t]+#', ' ', $data);
			if(is_array($data)) {
				foreach($data as &$one){
					is_string($one) AND $one = preg_replace('#[\n\r\s]{2,}|[\t]+#', ' ', $one);
				}
			}
		}

		$ttlm = ($ttlm * 60) + TIME; // minutes2sec
		$filename = $this->_getFilename($cachekey);
		$filename = file_put_contents($this->cachefolder.$filename, serialize(array('ttls' => $ttlm, 'data' => $data)));

		return $data;
	}

	/** Пытаемся загрузить данные из кэша приложения */
	public function load($cachekey) {

		if(false === $this->allow OR !$this->_checkfolder()) return false;

		$filename = $this->_getFilename($cachekey);

		if (!file_exists($this->cachefolder.$filename)) return NULL; // file exists?
		$data = file_get_contents($this->cachefolder.$filename);

		if (!$data = @unserialize($data)) return NULL; // is serial?
		//debug($data);
		if (empty($data['ttls']) OR $data['ttls'] <= TIME) {
			unset($data);
			return NULL; // ttls > time()
		}

		return $data['data'];
	}

	/** Пример использования в шаблонах:
		if( !snippets()->cache->start('menu')){
		#code
		snippets()->cache->end('menu', 10);
		}

		if( !snippets()->cache->start('menu2')) :
		 #code
		snippets()->cache->end('menu2', 30); endif;

		#code будет выполняться если кэш устарел или не существует.
	*/
	public function start($cachekey) {

		if(false === $this->allow) return FALSE;

		$cache = $this->load($cachekey);
		if(!is_null($cache)) {
			echo $cache;
			return true;
		} else {
			ob_start();
			return false;
		}
	}

	/**
	 * Сохранит и отобразит кэш попавший в обертку
	 * @param type $cachekey
	 * @param type $ttlm
	 * @return boolean
	 */
	public function end($cachekey, $ttlm = 30) {

		if(false === $this->allow) return FALSE;

		$cache = ob_get_clean();
		$this->save($cachekey, $cache, $ttlm);
		echo $cache;
	}

	/**
	 * Сохранит и вернет кэш попавший в обертку
	 * @param type $cachekey
	 * @param type $ttlm
	 * @return boolean
	 */
	public function get($cachekey, $ttlm = 30) {

		if(false === $this->allow) return FALSE;

		$cache = ob_get_clean();
		$this->save($cachekey, $cache, $ttlm);
		return $cache;
	}

	# drop file.cache by cachekey
	public function drop($cachekey) {

		if(false === $this->allow) return FALSE;

		$filename = $this->_getFilename($cachekey);

		if (!file_exists($this->cachefolder.$filename)) return NULL; // file exists?

		return unlink($this->cachefolder.$filename);
	}

	/**
	 * Уничтожить кэш
	 * @return type
	 */
	public function flush() {

		$flushed = array();
		$error = array();

		function flushcache($dir, &$f, &$error) {

			$path = $dir;
			$dir = dir($dir);
			while($file = $dir->read()){

				if(!in_array($file, array('.', '..'))){
					if(mb_strpos($file, '.cache')){
						if(unlink($path.$file)){
							$f[] = $file;
						} else {
							$error[] = $file;
						}
					}
				}
			}
		}

		if(is_dir($this->cachefolder)) {
			flushcache($this->cachefolder, $flushed, $error);
		} else {
			$error[] = 'Неправильная дериктория: '.$this->cachefolder;
		}

		return array('flushed' => $flushed, 'error' => $error, 'dir' => $this->cachefolder);
	}

	public function hasErrors() {
		
		return !empty($this->_errors);
	}

	public function getErrors() {

		return $this->_errors;
	}

	private function _checkfolder() {

		// обязательный слэш на конце
		$this->cachefolder = rtrim($this->cachefolder, '/').'/';

		if(!is_dir($this->cachefolder)){
			if(mkdir($this->cachefolder, 0755, true)){
				if(!is_writable($this->cachefolder)){
					if(!chmod($this->cachefolder, 0775)){
						$this->_addError('Недостаточно прав.');
						return false;
					}
				}
			} else {
				$this->_addError('Не удалось создать папку кэша');
				return false;
			}
		}

		return true;
	}

	

	// add error message and set has errors
	private function _addError($message) {
		$this->_errors[] = $message;
	}

	// get filename for cache file
	private function _getFilename($cachekey) {
		return md5($this->cachefolder.$cachekey) . '.cache';
	}
}

/* END CLASS: SnippetCache */