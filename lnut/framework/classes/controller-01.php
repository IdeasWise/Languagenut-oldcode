<?php

/**
 * The controller file sets the base path to use
 * and should list modules, which are 1:1 to pages
 * 
 * This project is using controllers directly
 * making use of the modules logic
 */

class Controller {

	public $controller_path = '';

	public function __construct () {

	}

	public function load_controller ($controller_path='') {

		if($controller_path!='') {
			config::set('controller',$controller_path);
		}

		$controller	= config::get('application').'controllers/'.config::get('controller').'.php';

		if(file_exists($controller)) {
			include($controller);
			$list		= get_declared_classes();
			$instance	= new $list[count($list)-1]();
		} else {
			die("class not found");
		}
	}

}

?>