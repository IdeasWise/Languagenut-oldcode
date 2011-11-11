<?php

class xml extends text {

	public function __construct ($file='') {
		$this->path = config::get('application').'tests/'.$file.'.xml';
	}

	public function load ($file='') {
		if(file_exists($this->path)) {
			$this->content = file_get_contents($this->path);
		} else {
			$this->content = '';
		}
		return $this;
	}
}

?>