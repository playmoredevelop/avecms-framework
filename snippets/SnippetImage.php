<?php

class SnippetImage {
	/** @author Playmore 2017 (playmoredevelop@gmail.com) */

    public function crop($server_uri, $width = 200, $height = 200) {

		return make_thumbnail([
			'link' => current(explode('|', $server_uri)),
			'size' => implode('', ['c', $width, 'x', $height])
		]);
	}

	public function resize($server_uri, $width = 200, $height = 200) {

		return make_thumbnail([
			'link' => current(explode('|', $server_uri)),
			'size' => implode('', ['r', $width, 'x', $height])
		]);
	}

	public function thumb($server_uri, $width = 200, $height = 200) {

		return make_thumbnail([
			'link' => current(explode('|', $server_uri)),
			'size' => implode('', ['t', $width, 'x', $height])
		]);
	}
}

/* END CLASS: SnippetImage */