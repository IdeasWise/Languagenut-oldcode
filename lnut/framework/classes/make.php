<?php

class make {

	public static function tpl ($file='', $content=false) {

		$tpl = new template_parser();
		$tpl->load($file,$content);

		return $tpl;

	}

}

?>