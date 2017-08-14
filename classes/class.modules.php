<?php

namespace Framework;

//modules()->feedback->method();
//modules()->execute('feedback', 'sendAction', $params);

class Modules {
	/** @author Playmore 2017 (playmoredevelop@gmail.com) */
	
	/**
	 * Модуль обратной связи
	 * @return ModuleFeedback
	 */
	public function feedback() {
		
		return $this->_load('feedback');
	}
	
	
	
	public function execute($moduleName, $method, $params = []) {
		
		$module = $this->_load($moduleName);
		
		if($module AND method_exists($module, $method)){
			
			return call_user_func_array([$module, $method], $params);
		}
		
		debug([
			'module.name' => $moduleName,
			'module.method' => $method,
			'error' => 'This module method can not be executed.'
		]);
	}
	
	private function _load($moduleName) {
		
		$mn = mb_strtolower($moduleName);
		
		return singleton('module.'.$mn, function() use ($mn){
		   
		   $ns = __NAMESPACE__;

		   $className = $ns.'\Module'.ucfirst($mn);
		   $file = FPATH.'/modules/'.$mn.'/module.'.$mn.'.php';
		   if(file_exists($file)){
			   require_once $file;
			   return new $className();
		   }
		   return false;
	   });
		
	}
}