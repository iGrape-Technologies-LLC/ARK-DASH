<?php
namespace App\Helpers;

class CookieHelper {

	public function isset(string $name) {
		return isset($_COOKIE[$name]);
	}

	public function set(string $name, string $value) {
		setcookie($name, $value, time() + (86400 * 30), "/"); //86400 -> 1 dia 
	}

	public function get(string $name) {
		return $_COOKIE[$name];
	}
}
?>