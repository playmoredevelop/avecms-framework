<?php

namespace Framework;

class CMS {
	/** @author Playmore 2017 (playmoredevelop@gmail.com) */

    public static function getMediapath() {

		return ABS_PATH . 'templates/' . ((defined('THEME_FOLDER') === false) ? DEFAULT_THEME_FOLDER : THEME_FOLDER) ;
	}
	
	/**
	 * @global AVE_Template $AVE_Template
	 * @return \AVE_Template
	 */
	public static function getAveTemplate() {
		
		global $AVE_Template;
		return $AVE_Template;
	}
}