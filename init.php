<?php

namespace Framework;

define('INIT', true);
define('FPATH', realpath(dirname(__FILE__)));
define('CMSROOT', $_SERVER['DOCUMENT_ROOT']);
define('TIME', time());
define('DOMAIN', $_SERVER['SERVER_NAME']);
define('FW_VERSION', '1.1');

/**
 * @property \Framework\SnippetRequest $request Работа с входным запросом
 * @property \Framework\SnippetResponse $response Работа с ответом сервера
 * @property \Framework\SnippetRender $render Сниппет рендера отображений
 * @property \Framework\SnippetImage $image Сниппеты работы с изображениями
 * @property \Framework\SnippetCache $cache Кэширование блоков контента в файлы
 * @property \Framework\SnippetPagination $pagination Сниппет рендера пагинаций
 * @property \Framework\SnippetRubric $rubric Методы работы с рубриками и выборкой из них
 * @property \Framework\SnippetDocuments $documents Методы работы с документами и выборкой из них
 * @property \Framework\SnippetUsers $users Работа с пользовательскими данными и активностью
 * @property \Framework\SnippetHtml $html Сниппеты генерации и преобразования html
 * @property \Framework\SnippetGenerator $generator Генератор полезных данных
 * @property \Framework\SnippetAssets $assets Удобное подключение скриптов и стилей. Не забудь включить вывод
 * @property \Framework\SnippetString $string Сниппеты генерации строк и полезные функции
 * @property \Framework\SnippetTables $tables Работа с таблицами в БД
 * @property \Framework\SnippetUrl $url Работа с адресной строкой и ее параметрами
 */
class SnippetsFactory {

   public function __get($snippetName) {

	   return singleton('snippet.'.$snippetName, function() use ($snippetName){
		   
		   $ns = __NAMESPACE__;

		   $className = $ns.'\Snippet'.ucfirst($snippetName);
		   $file = FPATH.'/snippets/Snippet'.ucfirst($snippetName).'.php';
		   if(file_exists($file)){
			   require_once $file;
			   return new $className();
		   }
		   return false;
	   });
   }
}

// подрубаем все функции фреймворка
require_once FPATH.'/functions.php';
// подрубаем класс для работы с переменными системы
require_once FPATH.'/classes/class.cms.php';
// подрубаем базовые классы
require_once FPATH.'/classes/class.basecontroller.php';
require_once FPATH.'/classes/class.basemodel.php';

hook_route();