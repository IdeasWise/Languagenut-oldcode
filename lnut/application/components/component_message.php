<?php

class component_message {

	public static function success ($message='') {
		$xhtml = make::tpl ('body.success.message')->assign('message',$message);
		return $xhtml->get_content();
	}
}

?>