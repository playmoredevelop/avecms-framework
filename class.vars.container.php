<?php

namespace Framework;

class VarsContainer {

	private $container = [];

	public function set($name, $value) {
		$this->container[$name] = $value;
	}

	public function get($name, $default = null) {

		if (array_key_exists($name, $this->container)) {
			return $this->container[$name];
		}
		return $default;
	}

	public function clear() {
		$this->container = [];
	}

}