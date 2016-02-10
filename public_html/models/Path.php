<?php
/*
	A common class for obtaining paths to files.
*/

class Path {
	static public function externalRoot() {
		return 'jakedawkins.com/';
	}

	static public function base() {
		return '/Users/Jake/Sites/ThrowNote/public_html/';
	}

	static public function css() {
		return Path::base().'css/';
	}

	static public function js() {
		return Path::base().'js/';
	}

	static public function models() {
		return Path::base().'models/';
	}

	static public function partials() {
		return Path::base().'partials/';
	}

	static public function img() {
		return Path::base().'img/';
	}	
	
	static public function tests() {
		return Path::base().'tests/';
	}

	static public function vendor() {
		return Path::base().'vendor/';
	}

	static public function dbSettings() {
		return '/Users/Jake/Sites/ThrowNote/db-settings.php';
	}

}
?>
