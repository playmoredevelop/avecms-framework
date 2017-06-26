<?php

namespace Framework;

class SnippetAssets {
	/** @author Playmore 2017 (playmoredevelop@gmail.com) */

	private $js = [];
	private $css = [];

	public function addJs($src) {

		$this->js[] = $src;
	}

	public function addCss($href) {

		$this->css[] = $href;
	}

	public function displayJs() {

		$display = ['<!-- js assets -->'];
		foreach($this->js as $src) {
			$display[] = sprintf('<script type="text/javascript" src="%s"></script>', CMS::getMediapath().$src);
		}

		return PHP_EOL.implode(PHP_EOL, $display).PHP_EOL;
	}

	public function displayCss() {

		$display = ['<!-- css assets -->'];
		foreach($this->css as $href) {
			$display[] = sprintf('<link rel="stylesheet" href="%s" />', CMS::getMediapath().$href);
		}

		return PHP_EOL.implode(PHP_EOL, $display).PHP_EOL;
	}

}

/* END CLASS: SnippetAssets */