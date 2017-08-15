<?php

namespace Framework\Controllers;

class BaseController {
	/** @author Playmore 2017 (playmoredevelop@gmail.com) */
	
	public function __call($modelname, $args = []) {
		
		return singleton($modelname.'Model', function() use ($modelname){
			
			$cls = ucfirst($modelname).'Model';
			$file = FPATH.'/models/'.mb_strtolower($modelname.'.model.php');
			$ns = '\Framework\Models\\';

			if( !class_exists($ns.$cls) AND file_exists($file)){
				require $file;
				$model = $ns.$cls;
				return new $model();
			}
			
			debug(sprintf('Модель <b>%s</b> не найдена по пути %s (%s)', $cls, $file, $ns));
		});
	}
	
}