<?php

echo 'test';exit;

/* * *************************************
 * MyStream Framework
 */
//require_once "Mail.php";
 /*
$from = "Language Nut <andrew@languagenut.com>";
$to = "Workstation <workstation@mystream.co.uk>";
$subject = "Test email using PHP SMTP with SSL\r\n\r\n";
$body = "This is a test email message";

$host = "mail.online-cloud.net";
$port = "25";
$username = "andrew@languagenut.com";
$password = "Stealth1980";

$headers = array ('From' => $from, 'To' => $to, 'Subject' => $subject);
$smtp = Mail::factory('smtp',
  array ('host' => $host,
    'port' => $port,
    'auth' => true,
    'username' => $username,
    'password' => $password));

$mail = $smtp->send($to, $headers, $body);

if (PEAR::isError($mail)) {
  echo("<p>" . $mail->getMessage() . "</p>");
} else {
  echo("<p>Message successfully sent!</p>");
}
//mail('workstation@mystream.co.uk','test','test','From: test@languagenut.com');
*/

error_reporting(E_ALL);

class config {

	private static $server	= 'localhost';
	private static $username= 'mystream';
	private static $password= 'Stealth1980';
	private static $database= 'languagenut';
	public static $data = array();
	
	public static function db($key='') {
		switch ($key) {
			case 'server':	return self::$server;	break;
			case 'username':return self::$username;	break;
			case 'password':return self::$password;	break;
			case 'database':return self::$database;	break;
		}
	}

	public static function getDbName(){
		return self::$database;
	}

	public static function get($key='') {
		$return = '';
		if (array_key_exists($key, self::$data)) {
			$return = self::$data[$key];
		}
		return $return;
	}

	public static function set($key='', $value='') {
		$return = null;
		
		// bk - is this needed?
		//if (array_key_exists($key, self::$data)) {
			self::$data[$key] = $value;
			$return = $value;
		//}
		return $return;
	}

	public static function getSetting($key='') {
		$response = false;
		$sql = "SELECT `value` FROM `settings` WHERE `key`='" . mysql_real_escape_string($key) . "' LIMIT 1";
		$res = database::query($sql);
		if ($res && mysql_error() == '' && mysql_num_rows($res) > 0) {
			$row = mysql_fetch_assoc($res);
			registry::set_entry($key, stripslashes($row['value']));
			$response = registry::get_entry($key);
		}
		return $response;
	}

	/**
	 * Short Paths.
	 * These are mapped in the 'text' object as  {{ path }} => core::$path()
	 */
	public static function base($path = '') {
		return self::$data['protocol'] . self::$data['host'] . '' . $path;
	}

	public static function url($path = '') {
		return self::base(self::$data['locale'] . '/' . $path);
	}

	public static function admin_uri($path = '') {
		if (@$_SESSION['user']['admin'] == 1) {
			return self::base(self::$data['locale'] . '/admin/' . $path);
		} else {
			return self::base(self::$data['locale'] . '/account/' . $path);
		}
	}

	public static function scripts($path = '') {
		return self::base('scripts/' . self::$data['locale'] . '/' . $path);
	}

	public static function scripts_common($path = '') {
		return self::base('scripts/' . $path);
	}

	public static function styles($path = '') {
		return self::base('styles/' . self::$data['locale'] . '/' . $path);
	}

	public static function styles_common($path = '') {
		return self::base('styles/' . $path);
	}

	public static function images($path = '') {
		return self::base('images/' . self::$data['locale'] . '/' . $path);
	}

	public static function images_common($path = '') {
		return self::base('images/' . $path);
	}

	public static function cdn_images($path = '') {
			return (self::$data['cdn_url'] . $path);
	}
	public static function cdn_locale_images($path = '') {
		return (self::$data['cdn_url'] . self::$data['locale'] . '/' . $path);
	}


	public static function flash($path = '') {
		return self::base('flash/' . self::$data['locale'] . '/' . $path);
	}

	public static function flash_common($path = '') {
		return self::base('flash/' . $path);
	}

	public static function documents($path = '') {
		return self::url('documents/' . $path);
	}

	public static function cache_common_uri($path = '') {
		return self::base('cache/' . $path.'/');
	}

	public static function cache_xml_uri($path = '') {
		return self::base('cache/xml/');
	}

	public static function cache_common($path = '') {
		return self::get('root').'/cache/' . $path.'/';
	}

	public static function cache_xml() {
		return self::get('root').'/cache/xml/';
	}

	public static function translate($tag=null) {

		$arrResponse = '';

		$data = '';

		if ($tag != null) {

			$useIn = false;

			if (is_array($tag) && count($tag) > 0) {
				$data = array();
				foreach ($tag as $word) {
					$data[] = "'" . mysql_real_escape_string(trim($word)) . "'";
				}
				$data = implode(', ', $data);
				$useIn = true;
			} else if (is_string($tag)) {
				$data = "'" . mysql_real_escape_string(trim($tag)) . "'";
			}

			$query = "SELECT ";
			$query.= "`page_messages`.`tag`, ";
			$query.= "`page_messages_translations`.`text` ";
			$query.= "FROM ";
			$query.= "`page_messages_translations`, ";
			$query.= "`page_messages` ";
			$query.= "WHERE ";
			$query.= "`page_messages_translations`.`locale`='" . config::get('locale') . "' ";
			$query.= "AND `page_messages`.`tag`" . ($useIn ? " IN ($data)" : "= $data") . " ";
			$query.= "AND `page_messages_translations`.`message_uid`=`page_messages`.`uid` ";
			if (!$useIn) {
				$query.= "LIMIT 1";
			}

			$result = database::query($query);

			if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
				while ($row = mysql_fetch_assoc($result)) {
					if (count($tag) > 1)
						$arrResponse[$row['tag']] = stripslashes($row['text']);
					else
						$arrResponse = stripslashes($row['text']);
				}
			}
		}

		return $arrResponse;
	}

	public function getUserSkeleton() {
		$skeleton = null;
		if (@$_SESSION['user']['admin'] == 1) {
			$skeleton = new xhtml('skeleton.admin');
			$skeleton->load();
		} else {
			$skeleton = new xhtml('skeleton.account');
			$skeleton->load();

			$Menu = new xhtml(@$_SESSION['user']['defaultMenu']);
			$Menu->load();
			$skeleton->assign(array('account.menu.item' => $Menu->get_content()));
		}
		return $skeleton;
	}
	
	public function doMikeError() {
		if($_SERVER['REMOTE_ADDR']=='83.105.41.208') {
			echo '<pre>';
			print_r(debug_backtrace());
			echo '</pre>';
		}
	}

}

/**
 * Load classes dynamically
 */
function __autoload($class_name) {

	//$cls	= strtolower($class_name).'.php';
	$cls = $class_name . '.php';

	$classes = array(
		config::get('framework') . 'classes/' . $cls,
		config::get('application') . 'classes/' . $cls,
		config::get('application') . 'controllers/' . $cls,
		config::get('application') . 'components/' . $cls,
		config::get('application') . 'plugins/' . $cls
	);

	$found = false;
		
	foreach ($classes as $class) {
		if (file_exists($class)) {
			$found = true;
			include($class);
			break;
		}
	}

	if (!$found) {
		notify('class: ' . $class_name . ' not found');
	}
}

function notify($message='') {
	mail('workstation@mystream.co.uk', 'LanguageNut(Live): Notification', $message, 'From: info@languagenut.com');
}

/**
 * Framework Core
 */
class core {

	private static $useGeoRedirect = true;

	private static function set_config_vars() {

		config::set('protocol','http://');
		config::set('host',$_SERVER['HTTP_HOST'].'/');
		config::set('root',$_SERVER['DOCUMENT_ROOT']);
		config::set('uploads',$_SERVER['DOCUMENT_ROOT'].'admin_uploads/');
		config::set('application',preg_replace('/httpdocs\//i','',$_SERVER['DOCUMENT_ROOT']).'application/');
		config::set('framework',preg_replace('/httpdocs\//i','',$_SERVER['DOCUMENT_ROOT']).'framework/');
		config::set('site',$_SERVER['DOCUMENT_ROOT']);
		config::set('pagesize',10);
		config::set('request','');
		config::set('paths',array());
		config::set('controller','index');
		config::set('locale','en');
		config::set('cache_classes',array());
		config::set('mediamanager_base',$_SERVER['DOCUMENT_ROOT']);
		config::set('cdn_url','http://images.languagenut.com/');
	
	}

	private static function security_passed() {
		/**
		 * Determine most appropriate security checks
		 * Options could be IP Bans, Proxy Bans, Bot Bans, etc
		 */
		$passed = true;

		/**
		 * Perform Security Checks Here
		 * set $passed = false; on failure
		 */
		session_start();

		/**
		 * Return whether or not security was passed
		 */
		return $passed;
	}

	private static function controller() {
		$paths = config::get('paths');
		if (count($paths) > 0 && isset($paths[0])) {
			config::set('controller', $paths[0]);
		}
		$controller = config::get('application') . 'controllers/' . config::get('controller') . '.php';
		$default = config::get('application') . 'controllers/index.php';

		$path = '';

		if(isset($_GET['aff'])) {
			$_SESSION['aff'] = $_GET['aff'];
		}

		if (file_exists($controller)) {
			$path = $controller;
		} else {
			$path = $default;
		}
		if ($path != '') { /**
		 * Get the Framework and Application Controller Classes
		 */
			include (config::get('framework') . 'classes/controller.php');
			include ($path);

			/**
			 * Create an instance of the Controller, which is always the last
			 * declared class.
			 */
			$list = get_declared_classes();

			logger::run();

			$instance = new $list[count($list) - 1] ( );
		} else {
			/**
			 * Look up the url to see if we have a matching page/slug/etc
			 */
			$found = false;

			/**
			 * Load a generic page controller
			 */
			if ($found) {

			} else {
				self::stop('no controller found:' . $controller);
			}
		}
	}

	private static function stop($message = '') {
		notify($message);
		output::redirect(config::url());
	}

	public static function start() {

		self::set_config_vars();
				
		database::connect();

		if (self::security_passed()) {
			/**
			 * Set any environment settings
			 */
			//set_magic_quotes_runtime(0);

			/**
			 * Determine the protocol to use
			 */
			$protocol = (isset($_SERVER['SERVER_PROTOCOL']) && 4 < strlen($_SERVER['SERVER_PROTOCOL'])) ? strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, 5)) : '';
			config::set('protocol', 'http' . (('https' === $protocol || 143 === (int) $_SERVER['SERVER_PORT']) ? 's' : '') . '://');

			/**
			 * Capture the Raw Request
			 */
			config::set('request', $_SERVER['REQUEST_URI']);
			$bits = explode('?',config::get('request'));
			if(count($bits) > 1) {
				config::set('request',$bits[0]);
			}

			$paths = array_values(explode('/', str_replace(config::get('host'), '', config::get('request'))));
			foreach ($paths as $index => $path) {
				if (strlen($path) < 1) {
					unset($paths[$index]);
				}
			}
//			array_shift($paths);
			/*
			  if( in_array('en',$paths) ){
			  array_shift($paths);
			  }
			 *
			 */

			$paths = array_values($paths);

			if (isset($paths[0])) {
				config::set('locale', $paths[0]);
				array_shift($paths);
			} else {

				if(isset($paths[0]) && in_array($paths[0],array('loader_support','games','unitsection','printables','gamescores','subscribe','lingualympics'))) {
					self::$useGeoRedirect = false;
				}

				if (true === self::$useGeoRedirect) {
					$geolocale = component_geo::ip_look_up();
					if ($geolocale!=false) {
						header('Location:' . config::base($geolocale) . '/');
						exit();
					}
				}
				config::set('locale', 'en');
			}

			config::set('paths', $paths);
			config::set('controller', 'index');
			$objShibboleth = new shibboleth();
			$objShibboleth->Init();

			/**
			 * Call the appropriate Controller
			 */
			self::controller();
		} else {
			self::stop('failed security');
		}
	}

}
if (!DEFINED('start_core')) {
	core::start();
}

?>