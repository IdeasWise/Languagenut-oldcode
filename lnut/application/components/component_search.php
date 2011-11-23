<?php

class component_search {

	public static function form($data = array()) {

		$paths = config::get('paths');

		if(isset($_POST['search_button']) || isset($_POST['ResetSearch'])) {
			if(isset($_POST['find']) && trim($_POST['find']) != '' && isset($data['section'])) {
				$locale = '';
				if(	isset($paths[3]) && language::CheckLocale($paths[3], false) != false) {
					$locale = $paths[3].'/';
				}
				switch($data['section']) {
					case 'list':
						component_search::Redirect('/users/list/'.$locale.'?find='.trim($_POST['find']));
					break;
					case 'school':
						component_search::Redirect('/users/school/'.$locale.'?find='.trim($_POST['find']));
					break;
					case 'schooladmin':
						component_search::Redirect('/users/schooladmin/'.$locale.'?find='.trim($_POST['find']));
					break;
					case 'schoolteacher':
						component_search::Redirect('/users/schoolteacher/'.$locale.'?find='.trim($_POST['find']));
					break;
					case 'student':
						component_search::Redirect('/users/student/'.$locale.'?find='.trim($_POST['find']));
					break;
					case 'homeuser':
						component_search::Redirect('/users/homeuser/'.$locale.'?find='.trim($_POST['find']));
					break;
					case 'login_history':
						if( isset($data['school_uid']) && is_numeric($data['school_uid']) ) {
							component_search::Redirect('/login-history/school/'.$data['school_uid'].'/'.$locale.'?find='.trim($_POST['find']));
						} else {
							component_search::Redirect('/login-history/'.$locale.'?find='.trim($_POST['find']));
						}

					break;
				}
			}		
		}
		if(isset($_POST['date_search']) && isset($_POST['search_from'])  && isset($_POST['search_to']) && $data['section']=='school') {
			$from	= date('Y-m-d');
			$to		= $from;
			$explode = explode('/',$_POST['search_from']);
			if(count($explode)==3) {
				$from = $explode[2].'-'.$explode[0].'-'.$explode[1];
			}
			$explode = explode('/',$_POST['search_to']);
			if(count($explode)==3) {
				$to = $explode[2].'-'.$explode[0].'-'.$explode[1];
			}
			component_search::Redirect('/users/school/'.$locale.'?from='.$from.'&to='.$to);
		}

		if(isset($_POST['ResetSearch'])) {
			$locale = '';
			if(	isset($paths[3]) && language::CheckLocale($paths[3], false) != false) {
				$locale = $paths[3].'/';
			}
			switch($data['section']) {
				case 'list':
					component_search::Redirect('/users/list/'.$locale);
				break;
				case 'school':
					component_search::Redirect('/users/school/'.$locale);
				break;
				case 'schooladmin':
					component_search::Redirect('/users/schooladmin/'.$locale);
				break;
				case 'schoolteacher':
					component_search::Redirect('/users/schoolteacher/'.$locale);
				break;
				case 'student':
					component_search::Redirect('/users/student/'.$locale);
				break;
				case 'homeuser':
					component_search::Redirect('/users/homeuser/'.$locale);
				break;
				case 'login_history':
					if( isset($data['school_uid']) && is_numeric($data['school_uid']) ) {
						component_search::Redirect('/login-history/school/'.$data['school_uid'].'/'.$locale);
					} else {
						component_search::Redirect('/login-history/'.$locale);
					}

				break;
			}
		}
		if(isset($data['section']) && $data['section'] == 'school') {
			$panel = new xhtml('body.component.search.school.form');
		} else {
			$panel = new xhtml('body.component.search.form');
		}
		$panel->load();
		return $panel->get_content();
	}

	public static function Redirect( $url ) {
		if(@$_SESSION['user']['admin'] == 1) {
			$url = 'admin'.$url;
		} else {
			$url = 'account'.$url;
		}

		if (!headers_sent($filename, $linenum)) {
			header('Location: ' . config::url($url));
			exit();
		} else {
			echo "Headers already sent in $filename on line $linenum\n";
		}
	}
}
?>
