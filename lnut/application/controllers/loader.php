<?php

/**
 * terms.php
 */

class Loader extends Controller {

	public $version	= 1;

	public function __construct () {
		parent::__construct();

		if(isset($_GET['version']) && $_GET['version']==2) {
			$this->version=2;
		}

		$this->content();
	}

	protected function content () {

		$yaerObject = new years(); // initializing year Object
		$years = $yaerObject->getYearArray(); // returns year id and name in array

		$langObject = new language(); // initializing language Object

		if($this->version==1) {
			$languages = $langObject->getLanguageArray(); // returns language id and name in array
		} else if($this->version==2) {
			$languages = $langObject->getLanguages();
		}
		

		echo '<?xml version="1.0" encoding="utf-8" ?>';
		echo '<data>';
		echo '<years>';
		if(count($years) > 0) {
			foreach($years as $id=>$name) {
				echo '<year id="'.$id.'" title="'.$name.'" />';
			}
		}
		echo '</years>';
		echo '<languages>';
		if(count($languages) > 0) {
			foreach($languages as $id=>$arrData) {
				if($this->version==1) {
					echo '<language id="'.$id.'" title="'.$arrData.'" />';
				} else {
					echo '<language id="'.$id.'" title="'.$arrData['name'].'" is_learnable="'.$arrData['is_learnable'].'" is_support="'.$arrData['is_support'].'" />';
				}
			}
		}
		echo '</languages>';
		echo '</data>';

	}
}

?>