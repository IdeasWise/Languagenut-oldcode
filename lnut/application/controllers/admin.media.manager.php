<?php

	class admin_media_manager extends Controller {

		private $token = 'list';
		private $arrTokens = array(
		'list',
		);
		private $arrPaths = array();

		public function __construct() {
			parent::__construct();
			$this->arrPaths = config::get('paths');
			if (isset($this->arrPaths[2]) && in_array($this->arrPaths[2], $this->arrTokens)) {
				$this->token = str_replace(array('-'), array(''), $this->arrPaths[2]);
			}
			if ($this->token != '') {
				$method = 'do' . ucfirst($this->token);
				$this->$method();
			}
		}

		protected function doList() {
			$skeleton = config::getUserSkeleton();
			$body = make::tpl('body.admin.media.manager.list');
			$objMediamanager = new media_manager();

			// media manager


			if (function_exists('date_default_timezone_set')) {
				date_default_timezone_set(date_default_timezone_get());
			}

			# Start running File Thingie #
			// Check if headers has already been sent.
			if (headers_sent ()) {
				$str = $objMediamanager->ft_make_headers_failed();
			} else {

				//			session_start();
				//			header("Content-Type: text/html; charset=UTF-8");
				//			header("Connection: close");
				// Prep settings
				$objMediamanager->ft_settings_load();
				// Load plugins
				$objMediamanager->ft_plugins_load();
				$objMediamanager->ft_invoke_hook('init');
				// Prep language.
				if (file_exists("ft_lang_" . LANG . ".php")) {
					@include_once("ft_lang_" . LANG . ".php");
				}
				// Only calculate total dir size if limit has been set.
				if (LIMIT > 0) {
					define('ROOTDIRSIZE', $objMediamanager->ft_get_dirsize($objMediamanager->ft_get_root()));
				}

				$str = "";
				// Request is a file download.
				if (!empty($_GET['method']) && $_GET['method'] == 'getfile' && !empty($_GET['file'])) {
					if ($objMediamanager->ft_check_login()) {
						$objMediamanager->ft_sanitize_request();
						// Make sure we don't run out of time to send the file.
						@ignore_user_abort();
						@set_time_limit(0);
						@ini_set("zlib.output_compression", "Off");
						@session_write_close();
						// Open file for reading
						if (!$fdl = @fopen($objMediamanager->ft_get_dir() . '/' . $_GET['file'], 'rb')) {
							die("Cannot Open File!");
						} else {
							$objMediamanager->ft_invoke_hook('download', $objMediamanager->ft_get_dir(), $_GET['file']);
							header("Cache-Control: "); // leave blank to avoid IE errors
							header("Pragma: "); // leave blank to avoid IE errors
							header("Content-type: application/octet-stream");
							header("Content-Disposition: attachment; filename=\"" . htmlentities($_GET['file']) . "\"");
							header("Content-length:" . (string) (filesize($objMediamanager->ft_get_dir() . '/' . $_GET['file'])));
							header("Connection: close");
							sleep(1);
							fpassthru($fdl);
						}
					} else {
						// Authentication error.
						$objMediamanager->ft_redirect();
					}
					exit;
				} elseif (!empty($_POST['method']) && $_POST['method'] == "ajax") {
					// Request is an ajax request.
					if (!empty($_POST['act']) && $_POST['act'] == "versioncheck") {
						// Do version check
						if ($objMediamanager->ft_check_login()) {
							$str.= $objMediamanager->ft_check_version();
						} else {
							// Authentication error. Send 403.
							header("HTTP/1.1 403 Forbidden");
							$str.= "<p class='error'>" . $objMediamanager->t('Login error.') . "</p>";
						}
					} else {
						if ($objMediamanager->ft_check_login()) {
							$objMediamanager->ft_sanitize_request();
							// Run the ajax hook for modules implementing ajax.
							$str.= implode('', $objMediamanager->ft_invoke_hook('ajax', $_POST['act']));
						} else {
							// Authentication error. Send 403.
							header("HTTP/1.1 403 Forbidden");
							$str.= "<dt class='error'>" . $objMediamanager->t('Login error.') . "</dt>";
						}
					}
					exit;
				}
				if ($objMediamanager->ft_check_login()) {
					// Run initializing functions.
					$objMediamanager->ft_sanitize_request();
					$objMediamanager->ft_do_action();
					$header_breadcram = $objMediamanager->ft_make_header();
					$str .= '<div id="sidebar">';
					$str .= $objMediamanager->ft_make_sidebar();
					$str .= $objMediamanager->ft_make_body();
					$str .= '</div>';

				} else {
					$str .= $objMediamanager->ft_make_login();
				}
				//			$str .= $objMediamanager->ft_make_footer();
			}
			// media manager

			$tabs_li = array();

			$arrLocales = language::getPrefixes();
			if (count($arrLocales) > 0) {
				foreach ($arrLocales as $uid => $arrData) {
					$dir = (isset($_REQUEST["dir"])) ? $_REQUEST["dir"] : "";
					//                $tabs_li[] = '<li><a href="'.config::admin_uri('media_manager/').'?dir='.$dir.'&locale='.$arrData['prefix'].'"><span>' . $arrData['prefix'] . '</span></a></li>';
					$localeLi = new xhtml('admin.locale.li');
					$localeLi->load();

					$localeLi->assign("tab_id", "");
					$localeLi->assign("uid", config::admin_uri('media_manager/') . '?dir=' . $dir . '&locale=' . $arrData['prefix']);
					$localeLi->assign("prefix", $arrData['prefix']);
					$localeLi->assign("other",'class="locale"');

					$tabs_li[] = $localeLi->get_content();

					//				$tabs_li[] = '<li><a  href=""><span>' . $arrData['prefix'] . '</span></a>&nbsp;</li>';
					//                $tabs_li[] = '<a href=""><span>' . $arrData['prefix'] . '</span></a>&nbsp;';
				}
			}

			$body->assign(
			array(
			'tabs' => implode('', $tabs_li)
			)
			);

			$str.= $objMediamanager->ft_make_scripts_footer();
			$str.= implode("\r\n", $objMediamanager->ft_invoke_hook('destroy'));

			$body->assign("mediamanager_body", $str);
			$body->assign("breadcram", $header_breadcram);
			$body->assign("form_post", config::admin_uri("media_manager/"));
			$req_dir = (isset($_REQUEST["dir"])) ? $_REQUEST["dir"] : "";
			$body->assign("req_dir", $req_dir);

			$skeleton->assign(
			array(
			'body' => $body,
			'media_manager_css' => '<link rel="stylesheet" media="all" href="'.config::styles_common().'filemanager.css" />',
			'media_manager_js' => '<script type="text/javascript" >
			var form_post=\'' . config::admin_uri("media_manager/") . '\';
			var req_dir=\'' . $req_dir . '\';
			</script>
			<script type="text/javascript" src="'.config::scripts_common().'filemanager.js" ></script>
			'
			)
			);

			output::as_html($skeleton, true);
		}

	}

?>