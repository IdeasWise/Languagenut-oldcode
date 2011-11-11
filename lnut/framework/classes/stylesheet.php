<?php

class stylesheet extends text {

	public function __construct () {
		$this->path = core::$root.config::$application;
	}

	public function load ($data = array()) {
		if ($data['scope'] && $data['template']) {
			switch($data['scope']) {
				case 'application':
					$this->load_application_xhtml($data['template']);
				break;
				case 'local':
					$this->load_local_xhtml($data['template']);
				break;
				default:
					$this->load_application_xhtml($data['template']);
				break;
			}
		} else {
			$this->load_application($data['template']);
		}
		if(file_exists($this->path)) {
			$this->content = file_get_contents($this->path);
		} else {
			if(debug::$logging) {
				debug::message(
					debug::$log_type['informative'],
					null,
					'Panel Not Found: '.$this->path
				);
			}
		}
		return $this;
	}

	public function load_application_xhtml ($template='') {
		if('' != $template && null != $template && 0 < strlen($template)) {
			$this->path .= 'styles/style.'.$template.'.css';
		}
	}

	public function load_local_xhtml ($template='') {
		if('' != $template && null != $template && 0 < strlen($template)) {
			$this->path .= 'controllers/'.core::$controller.'/styles/style.'.$template.'.css';
		}
	}
}

?>