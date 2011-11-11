<?php

class api extends Controller {

	public function __construct () {
		parent::__construct();
		$arrPaths = config::get('paths');
		if(isset($arrPaths[1]) && $arrPaths[1] == 'templates' ) {
			$this->load_controller('api.templates');
		} else if (isset($arrPaths[1]) && $arrPaths[1] == 'articles' ) {
			$this->load_controller('api.articles');
		} else if (isset($arrPaths[1]) && $arrPaths[1] == 'tasks' ) {
			$this->load_controller('api.tasks');
		} else if (isset($arrPaths[1]) && $arrPaths[1] == 'medals' ) {
			$this->load_controller('api.medals');
		} else if (isset($arrPaths[1]) && $arrPaths[1] == 'qae' ) {
			$this->load_controller('api.qae');
		} else if (isset($arrPaths[1]) && $arrPaths[1] == 'games' ) {
			$this->load_controller('api.games');
		} else if (isset($arrPaths[1]) && $arrPaths[1] == 'writing' ) {
			$this->load_controller('api.writing');
		} else if (isset($arrPaths[1]) && $arrPaths[1] == 'flash' ) {
			$this->load_controller('api.flash');
		}
	}

}

?>