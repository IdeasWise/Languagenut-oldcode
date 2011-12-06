<?php

/**
 * static.cache.php
 */
class cache {

	public static $caching = true;
	public static $cache_dir = 'cache/';
	public static $cache_timeout = 3600;
	public static $cache_extension = 'cache';
	public $cache_api_folder = '';

	public static function exists($md5_string = '', $local = false, $controller = '') {
		$application_cache = config::$root . config::$application . self::$cache_dir . $md5_string . '.' . self::$cache_extension;
		$controller_cache = config::$root . config::$application . 'controllers/' . (($controller == '') ? core::$controller : $controller) . '/' . self::$cache_dir . $md5_string . '.' . self::$cache_extension;

		$cache = (($local) ? $controller_cache : $application_cache);

		$state = false;

		if (file_exists($cache)) {
			if (time() - filemtime($cache) < self::$cache_timeout) {
				$state = true;
			} else {
				$state = false;
			}
		} else {
			$state = false;
		}
		return $state;
	}

	public static function store($md5_string = '', $content = '', $local = false, $controller = '') {
		$application_cache = config::$root . config::$application . self::$cache_dir . $md5_string . '.' . self::$cache_extension;
		$controller_cache = config::$root . config::$application . 'controllers/' . (($controller == '') ? core::$controller : $controller) . '/' . self::$cache_dir . $md5_string . '.' . self::$cache_extension;

		$cache = (($local) ? $controller_cache : $application_cache);

		$file_pointer = @fopen($cache, 'w');

		$stored = false;

		if ($file_pointer) {
			if (false === fwrite($file_pointer, $content)) {
				$stored = false; // not writable
			} else {
				$stored = true;
			}
			@fclose($file_pointer);
		} else {
			$stored = false; // cannot open file
		}
		return $stored;
	}

	public static function fetch($md5_string = '', $local = false, $controller = '') {
		if (self::exists($md5_string, $local)) {
			$application_cache = config::$root . config::$application . self::$cache_dir . $md5_string . '.' . self::$cache_extension;
			$controller_cache = config::$root . config::$application . 'controllers/' . (($controller == '') ? core::$controller : $controller) . '/' . self::$cache_dir . $md5_string . '.' . self::$cache_extension;

			$cache = (($local) ? $controller_cache : $application_cache);

			if (file_exists($cache)) {
				touch($cache);
				return file_get_contents($cache);
			} else {
				return null;
			}
		} else {
			return null;
		}
	}

	public static function clear($local = false, $controller = '') {
		$application_cache = config::$root . config::$application . self::$cache_dir;
		$controller_cache = config::$root . config::$application . 'controllers/' . (($controller == '') ? core::$controller : $controller) . '/' . self::$cache_dir;

		$cache_dir = (($local) ? $controller_cache : $application_cache);

		if ($handle = @opendir($cache_dir)) {
			while (false !== ($file = @readdir($handle))) {
				if ($file != '.' && $file != '..') {
					@unlink(self::$cache_dir . $file);
				}
			}
			@closedir($handle);
		}
	}

	public function createOrReplace($stuff="", $content="", $type="xml") {
		if (!empty($stuff)) {
			$stuff = md5($stuff);
			if (!file_exists(config::cache_common($type) . $this->cache_api_folder)) {
				if(mkdir(config::cache_common($type) . $this->cache_api_folder)) {
					chmod(config::cache_common($type) . $this->cache_api_folder,0777);
				}
			}
			$filename = config::cache_common($type) . $this->cache_api_folder . $stuff . ".cache";
			file_put_contents($filename, $content);
			return true;
		}
		return false;
	}

	public function remove($stuff="", $type="xml") {
		if (!empty($stuff)) {
			$stuff = md5($stuff);
			$filename = config::cache_common($type) . $this->cache_api_folder . $stuff . ".cache";
			if (file_exists($filename)) {
				unlink($filename);
				return true;
			}
		}
		return false;
	}

	public function emptyCache($type="sql") {
		
		$filename = config::cache_common($type);
		$this->rrmdir($filename);
		
		return true;
	}

	public function cacheExist($stuff="", $type="xml") {
		if (!empty($stuff)) {
			$stuff = md5($stuff);
			$filename = config::cache_common($type) . $this->cache_api_folder . $stuff . ".cache";
			if (file_exists(trim($filename))) {
				return $stuff . ".cache";
			}
		}
		return false;
	}

	public function getCacheContent($stuff="", $type="xml") {
		if (!empty($stuff)) {
			$stuff = md5($stuff);
			$filename = config::cache_common($type) . $this->cache_api_folder . $stuff . ".cache";
			if (file_exists($filename)) {
				return file_get_contents($filename);
			}
		}
		return false;
	}

	// removes files and non-empty directories
	private function rrmdir($dir) {
		if (is_dir($dir)) {
			$files = scandir($dir);
			foreach ($files as $file)
				if ($file != "." && $file != "..")
					$this->rrmdir("$dir/$file");
//			$this->rrmdir($dir);
		}
		else if (file_exists($dir))
			unlink($dir);
	}
	

}

?>