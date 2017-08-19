<?php

function debug($dump, $continue = false) {
	$content = implode('', [
		'<pre style="font-size:8pt; width:100%">',
		var_export($dump, 1),
		'</pre>',
	]);
	$r = [
		'array (' => '<strong style="color:#007934">array (</strong>',
		'=>' => '<span style="color:#b72d00; font-size:10px">=></span>',
		'),' => '<strong style="color:#007934">),</strong>'
	];
	$content = str_replace(array_keys($r), array_values($r), $content);
	echo $content;
	$continue OR exit();
}

function singleton($name, $mixed){

	static $singletones = array();
	if(empty($singletones[$name])) {
		if(is_callable($mixed)) {
			$singletones[$name] = call_user_func($mixed);
		} else {
			$singletones[$name] = $mixed;
		}
	}
	return $singletones[$name];
}

/**
 * snippets()->name->method
 * @return \Framework\SnippetsFactory
 */
function snippets() {

	return singleton('snippets', function(){

		return new \Framework\SnippetsFactory();
	});
}

/**
 * @return \Framework\DB
 */
function db(){

	return singleton('db', function(){
		
		require FPATH.'/classes/class.db.php';
		return new \Framework\DB();
		
	});
}

/**
 * Объект бенчмарка для замеров времени исполнения между секциями
 *
 * benchmarks()->start('section1');
 *		benchmarks()->start('section2');
 *		benchmarks()->stop('section2');
 * benchmarks()->stop('section1');
 *
 * @return \Framework\Benchmarks
 */
function benchmarks() {
	
	return singleton('benchmarks', function(){
		
		require FPATH.'/classes/class.benchmarks.php';
		return new \Framework\Benchmarks();
		
	});
}

/**
 * @return Framework\VarsContainer
 */
function vars() {

	return singleton('vars-container', function(){
		
		require FPATH.'/classes/class.vars.container.php';
		return new Framework\VarsContainer();
		
	});
}

/**
 * 
 * @return \Framework\Modules
 */
function modules() {
	
	return singleton('modules', function(){
		
		require FPATH.'/classes/class.modules.php';
		return new Framework\Modules();
		
	});
}

/**
 * Вызов метода контроллера с передачей массива параметров (все стандартно)
 * @param type $cname
 * @param type $cmethod
 * @param type $params
 * @return type
 */
function call_controller($cname, $cmethod = 'index', $params = []) {
	
	if(in_array($cname, ['admin'])){
		return false;
	}
	
	$controller = singleton('Controller|'.$cname, function() use ($cname){
		
		$classname = implode('', array_map(function($val){
			return ucfirst($val);
		}, explode('.', $cname))).'Controller';
		
		if(class_exists($classname)){
			
			return new $classname();
			
		} else {
			
			$filepath = FPATH.'/controllers/'.$cname.'.php';
			
			if(file_exists($filepath)){
				require $filepath;
				if(class_exists($classname)){
					return new $classname();
				}
			}
		}
		
		return false;
	});
	
	if($controller){ 
		
		$cmethod = str_replace('Action', '', $cmethod).'Action';
		
		if(method_exists($controller, $cmethod)){
			return call_user_func_array([$controller, $cmethod], $params);
		} else {
			$classname = implode('', array_map(function($val){
				return ucfirst($val);
			}, explode('.', $cname))).'Controller';
			exit(sprintf('Method <strong>%s</strong> does not exists in controller <strong>%s</strong>', $cmethod, $classname));
		}
	}
	
	return NULL;
}

/**
 * Автоматический захват вызываемого контроллера (если контроллера нет - продолжаем выполнение системы)
 * - также захватывает роутинг модулей
 */
function hook_route(){
	
	$parse = snippets()->request->uri(true);
	call_controller($parse['controller'], $parse['method'].'Action', $parse['params']);
	
	if($parse['is_module']){
		modules()->execute($parse['controller'], $parse['method'].'Action', $parse['params']);
	}
}