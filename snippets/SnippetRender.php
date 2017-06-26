<?php

namespace Framework;

class SnippetRender extends SnippetRenderBase {
	/** @author Playmore 2017 (playmoredevelop@gmail.com) */
	
	public function module($modulename, $settings = []) {
		
		$path = (str_replace('.php', '', FPATH.'/views/modules/'.$modulename)).'.php';
		
		if(file_exists($path)){

			ob_start();
			// $settings
			// $this->section(name, cache);
			include $path;
			return ob_get_clean();
		}
		return sprintf('Module file <b>%s</b> not found in modules.', $modulename);
	}

}

class SnippetRenderBase {
	
	public function section($section, $cache_ttlm = false) {

		$path = (str_replace('.php', '', FPATH.'/views/sections/'.$section)).'.php';
		if(file_exists($path)){

			if($cache_ttlm){
				$cache = snippets()->cache->load('section_'.$section);
				if(is_null($cache)) {
					ob_start();
					include $path;
					$cache = snippets()->cache->get('section_'.$section, $cache_ttlm);
				}
				return $cache;
			} else {
				ob_start();
				include $path;
				return ob_get_clean();
			}
		}
		return sprintf('Section <b>%s</b> not found in sections folder.', $section);
	}
	
	public function widget($widgetfile) {
		
		$path = (str_replace('.php', '', FPATH.'/views/widgets/'.$widgetfile)).'.php';
		if(file_exists($path)){
			
			include $path;
		}
		
		return '['.$widgetfile.']';
	}
}

/* END CLASS: SnippetViews */