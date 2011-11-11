<?php

/**
 * subscribe.php
 */
class Sessions extends Controller {

public function __construct() {
		echo '<pre>';
		print_r($_SESSION);
		echo '</pre>';
	}
}

?>