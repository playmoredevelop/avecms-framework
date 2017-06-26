<?php

namespace Framework;

class CMS {
	/** @author Playmore 2017 (playmoredevelop@gmail.com) */

    public static function getMediapath() {

		return ABS_PATH . 'templates/' . ((defined('THEME_FOLDER') === false) ? DEFAULT_THEME_FOLDER : THEME_FOLDER) ;
	}
}