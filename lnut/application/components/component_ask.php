<?php

class component_ask {

	private static $error		= false;
	private static $processed	= false;
	private static $emailed		= false;

	private static $message		= '';
	private static $success		= 'Thank you. You will be contacted by an advisor within one to three business days.';
	private static $failure		= 'Please correct the identified fields to continue.';

	private static $form		= array (
		'ask-question'	=> array ('error'=>'', 'highlight'=>false, 'value'=>'Ask a question...'),
		'ask-email'		=> array ('error'=>'', 'highlight'=>false, 'value'=>'Email answer to...')
	);

	public static function process ($environment='') {
		/**
		 * Process any form data
		 */
		if(count($_POST) > 0 && (isset($_POST['submit_ask']) || isset($_POST['submit_ask_x']))) {
			/**
			 * Fetch the data that should have been posted
			 */
			self::$form = form::clean(self::$form);

			/**
			 * Indicate the form was processed
			 */
			self::$processed = true;

			/**
			 * If there is nothing to highlight / no errors
			 * then store cleaned values in the database
			 * and ask the user to answer when they would prefer a call
			 */
			foreach(self::$form as $key=>$data) {
				if($data['highlight']) {
					self::$error	= true;
				}
			}
			if(!self::$error) {
				self::email();
			}
		}

		/**
		 * Return either the ajax or the xml response
		 */
		if($environment && $environment=='ajax') {
			return self::xmlresponse();
		} else {
			return self::xhtmlresponse();
		}
	}

	private function email () {
		mail(
			'andrew.whitfield@yahoo.co.uk',
			'LEA: Ask a Question: Success',
			"Question: ".self::$form['question']['value']."\n".
			"E-Mail: ".self::$form['email']['value']."\n",
			'From: contacts@lea-financial-services.co.uk'
		);
		self::$emailed = true;
		config::$redirect = true;
	}

	private function xmlresponse () {
		$eol = "\n";
		$xml[] = '<?xml version="1.0"?>';
		$xml[] = '<response>';
		$xml[] = '<message>'.self::$message.'</message>';

		foreach(self::$form as $key=>$data) {
			/**
			 * Replace the Field Values, Highlights and Errors
			 */
			$node = '<field_'.$key;
			$node.= ' value="'.self::$form[$key]['value'].'"';
			$node.= ' highlight="'.(self::$form[$key]['highlight'] ? 'yes' : 'no').'"';
			$node.= ' error="'.self::$form[$key]['error'].'"';
			$node.= ' />';

			$xml[] = $node;
		}

		$xml[] = '</response>';

		header("Content-Type: text/xml");
		echo implode($eol,$xml);
	}

	private function xhtmlresponse () {
		if(self::$emailed) {
			$_SESSION['plugin.ask.message'] = true;
		} else {
			/**
			 * Load the appropriate template
			 */
			$xhtml = new xhtml ('plugin.ask.question');
			$xhtml->load();

			/**
			 * Prepare error messages, if present
			 */
			if(self::$error) {
				self::$message = '<p class="error">'.self::$failure.'</p>';
			} else if(isset($_SESSION['plugin.ask.message'])) {
				self::$message = '<p class="success">'.self::$success.'</p>';
				unset($_SESSION['plugin.ask.message']);
			}

			/**
			 * Replace the Message Placeholder
			 */
			$xhtml->assign('message',self::$message);

			foreach(self::$form as $key=>$data) {
				if(true==$data['highlight'] && '' != $data['error']) {
					self::$form[$key]['error'] = '<span class="error">'.$data['error'].'</span>';
				}

				/**
				 * Replace the Field Values, Highlights and Errors
				 */
				$xhtml->assign(
					array(
						$key.'.value'		=> $data['value'],
						$key.'.highlight'	=> ($data['highlight'] ? ' class="error"' : ''),
						$key.'.error'		=> self::$form[$key]['error']
					)
				);
			}

			return $xhtml->get_content();
		}
	}
}

?>