<?php

class javascript extends text {

	public function __construct () {
		$this->path = core::$root.config::$application;
	}

	public function load ($file = '') {
		if ('' != $file && null != $file && 0 < strlen($file)) {
			$this->path .= 'scripts/script.' . $file . '.js';
			if (file_exists ($this->path)) {
				$this->content = file_get_contents($this->path);
			} else {
				if(core::$logging) {
					debug::message(
						debug::$log_type['informative'],
						null,
						'Script Not Found: '.$this->path
					);
				}
			}
		} else {
			if(core::$logging) {
				echo '1';
				debug::message(
					debug::$log_type['error'],
					null,
					'Cannot Load Blank Script'
				);
			} else {
				echo '2';
			}
		}
	}
}

?>