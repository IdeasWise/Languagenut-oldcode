<?php

class cache extends generic_object {

	public function __construct() {
		
	}

	public  function createOrReplace($stuff="", $content="", $type="xml") {
		if (!empty($stuff)) {
			$stuff = md5($stuff);
			$filename = config::cache_common($type) . $stuff . ".cache";
			file_put_contents($filename, $content);
			return true;
		}
		return false;
	}

	public  function remove($stuff="", $type="xml") {
		if (!empty($stuff)) {
			$stuff = md5($stuff);
			$filename = config::cache_common($type) . $stuff . ".cache";
			if (file_exists($filename)) {
				unlink($filename);
				return true;
			}
		}
		return false;
	}

	public function cacheExist($stuff="",$type="xml") {
		if (!empty($stuff)) {
			$stuff = md5($stuff);
			$filename = config::cache_common($type) . $stuff . ".cache";
			if (file_exists($filename)) {
				return $stuff . ".cache";
			}
		}
		return false;
	}
	
	public  function getCacheContent($stuff="",$type="xml") {
		if (!empty($stuff)) {
			$stuff = md5($stuff);
			$filename = config::cache_common($type) . $stuff . ".cache";
			if (file_exists($filename)) {
				return file_get_contents($filename);
			}
		}
		return false;
	}

}

?>