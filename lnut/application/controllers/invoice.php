<?php

class admin_invoice extends Controller {

	private $token		= 'list';
	private $arrTokens	= array (
		'list',
		'edit',
		'add',
		'delete',
		'paid',
		'view',
		'expired_due'
	);
	private $arrPaths	= array();

	public function __construct () {
		parent::__construct();
		$this->arrPaths = config::get('paths');
		if(isset($this->arrPaths[3]) && in_array($this->arrPaths[3], $this->arrTokens)) {
			$this->token = $this->arrPaths[3];
		}

		if(in_array($this->token,$this->arrTokens)) {
			$method = 'do' . ucfirst($this->token);
			$this->$method();
		}
	}

	protected function doView() {
		if($this->arrPaths[2] == 'school' && $this->arrPaths[4] > 0 ){
			$objSubscription = new subscriptions($this->arrPaths[4]);
			$objSubscription->load();

			$objSchool = new users_schools();
			$objSchool->load(array(), array('user_uid'=>$objSubscription->get_user_uid() ));

			$objUser = new user($objSubscription->get_user_uid());
			$objUser->load();
			$locale = $objUser->get_locale();

			$objAddress = new lib_property_address_uk( $objSchool->get_address_id() );
			$objAddress->load();

			$amount			= 0;
			$vat			= 0;
			$vat_tax		= 0;
			$amount			= $objSubscription->get_amount();
			$vat			= $objSubscription->get_vat();
			$vat_tax		= ( $amount * ( $vat / 100 ) );
			$objCurrency	= new currencies();
			$arrInvoice		= array(
				'amount'			=> iconv("UTF-8", "cp1252", $objCurrency->getCurrencyFormat( $locale, $amount )),
				'vat'				=> $vat,
				'vat_tax'			=> iconv("UTF-8", "cp1252",$objCurrency->getCurrencyFormat( $locale, $vat_tax )),
				'total'				=> iconv("UTF-8","cp1252",$objCurrency->getCurrencyFormat( $locale,($amount + $vat_tax))),
				'to'				=> $objSchool->get_school(),
				'address'			=> explode(',' , $objAddress->get_street_name_1()),
				'school_postcode'	=> $objAddress->get_postcode(),
				'invoice_number'	=> $objSubscription->get_invoice_number(),
				'date'				=> date('d/m/Y',strtotime($objSubscription->get_start_dts())),
				'due_date'			=> date('d/m/Y', strtotime($objSubscription->get_due_date())),
				'reference'			=> $objSchool->get_name()
			);

			$objInvoice = new invoice();
			$objInvoice->generate($arrInvoice);
		}
	}

	protected function doPaid() {
		if(isset($_POST['PaidUid']) && is_numeric($_POST['PaidUid']) && $_POST['PaidUid'] > 0){
			$objSubscription = new subscriptions();
			echo $objSubscription->doPaid($_POST['PaidUid']);
		}
	}

	protected function doAdd() {
		$skeleton		= make::tpl ('skeleton.admin');
		$body			= make::tpl ('body.admin.invoice.add');
		$arrBody		= array();

		if(isset($this->arrPaths[2]) && !in_array($this->arrPaths[2], array('school','homeuser'))) {
			$this->arrPaths[2] = 'school';
		} else if(!isset($this->arrPaths[2])){
			$this->arrPaths[2] = 'school';
		}

		if($this->arrPaths[2] == 'school'){
			$arrBody['section']		= "School";
			$arrBody['tax_display']	= "display:none;";
		} else {
			$arrBody['section'] = "Home User";
		}
		$arrBody['mode'] = $this->arrPaths[2];

		if(isset($_POST['submit-button'])){
			$objSubscription = new subscriptions();
			if($objSubscription->doSave() ){
				// redirect to invoice list if all does well;
				$objSubscription->redirectTo('admin/invoice/'.$this->arrPaths[2].'/list');
			} else {
				$objSubscription->arrForm['user_uid'] = subscriptions::getOptions($objSubscription->arrForm['user_uid']);
				$body->assign( $objSubscription->arrForm );
			}
		}

		$arrBody['action']		= "Add";
		$arrBody['verified0']	= 'checked="checked"';
		$arrBody['amount']		= config::getSetting('subscription_amount');
		$arrBody['user_uid']	= subscriptions::getOptions();

		$body->assign( $arrBody );

		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}

	protected function doEdit() {
		$skeleton		= make::tpl ('skeleton.admin');
		$body			= make::tpl ('body.admin.invoice.add');
		$arrBody		= array();

		if(isset($this->arrPaths[2]) && !in_array($this->arrPaths[2], array('school','homeuser'))) {
			$this->arrPaths[2] = 'school';
		} else if(!isset($this->arrPaths[2])){
			$this->arrPaths[2] = 'school';
		}

		if($this->arrPaths[2] == 'school'){
			$arrBody['section']		= "School";
			$arrBody['tax_display']	= "display:none;";
		} else {
			$arrBody['section'] = "Home User";
		}
		$arrBody['mode']	= $this->arrPaths[2];
		$arrBody['action']	= "Update";

		if(isset($this->arrPaths[5]) && $this->arrPaths[5] == 'profile') {
			$arrBody['redirect'] = "1";
		}

		if(isset($_POST['submit-button'])) {
			$objSubscription		= new subscriptions();
			if($objSubscription->doSave()){
				if(isset($_POST['redirect']) && $_POST['redirect'] == 1) {
					// redirect to invoice list if all does well;
					$objSubscription->redirectTo('admin/users/profile/school/'.@$_POST['user_uid'].'/');
				} else {
					// redirect to invoice list if all does well;
					$objSubscription->redirectTo('admin/invoice/'.$this->arrPaths[2].'/list');
				}
			} else {
				$arrBody['user_uid_display']	= 'display:none;';
				$arrBody['user_uid_edit']		= subscriptions::getOptions($objSubscription->arrForm['user_uid']);
				$body->assign( $arrBody );
				$body->assign( $objSubscription->arrForm );
			}
		} else {
			if( $this->arrPaths[4] > 0) {
				$arrBody['uid']		= $this->arrPaths[4];
				$objSubscription	= new subscriptions($arrBody['uid']);
				$objSubscription->load();
				foreach($objSubscription->TableData as $idx => $val ) {
					$arrBody[$idx] = $val['Value'];
				}
				if($arrBody['verified'] == 0) {
					$arrBody['verified0'] = 'checked="checked"';
				} else {
					$arrBody['verified1'] = 'checked="checked"';
				}
				if($arrBody['start_dts'] != '0000-00-00 00:00:00' ) {
					list(
						$arrBody['start_day'],
						$arrBody['start_month'],
						$arrBody['start_year']) = explode('-', date('d-m-Y', strtotime($arrBody['start_dts']))
					);
				}

				if($arrBody['expires_dts'] != '0000-00-00 00:00:00' ) {
					list(
						$arrBody['expires_day'],
						$arrBody['expires_month'],
						$arrBody['expires_year']) = explode('-', date('d-m-Y', strtotime($arrBody['expires_dts']))
					);
				}

				if($arrBody['sent_dts'] != '0000-00-00 00:00:00' ) {
					list(
						$arrBody['sent_day'],
						$arrBody['sent_month'],
						$arrBody['sent_year']) = explode('-', date('d-m-Y', strtotime($arrBody['sent_dts']))
					);
				}

				if($arrBody['sent'] == '1' ) {
					$arrBody['sent1'] = 'checked="checked"';
					$arrBody['sent0'] = '';
				} else {
					$arrBody['sent0'] = 'checked="checked"';
					$arrBody['sent1'] = '';
				}

				if($arrBody['date_paid'] != '0000-00-00 00:00:00' ) {
					list(
						$arrBody['date_day'],
						$arrBody['date_month'],
						$arrBody['date_year']) = explode('-', date('d-m-Y', strtotime($arrBody['date_paid']))
					);
					list(
						$arrBody['payverified_day'],
						$arrBody['payverified_month'],
						$arrBody['payverified_year']) = explode('-', date('d-m-Y', strtotime($arrBody['date_paid']))
					);
					$arrBody['payverified1'] = 'checked="checked"';
				} else {
					$arrBody['payverified0'] = 'checked="checked"';
				}

				if($arrBody['verified_dts'] != '0000-00-00 00:00:00' ) {
					list(
						$arrBody['verified_day'],
						$arrBody['verified_month'],
						$arrBody['verified_year']) = explode('-', date('d-m-Y', strtotime($arrBody['verified_dts']))
					);
				}

				if($arrBody['due_date'] != '0000-00-00 00:00:00' ) {
					list(
						$arrBody['due_day'],
						$arrBody['due_month'],
						$arrBody['due_year']) = explode('-', date('d-m-Y', strtotime($arrBody['due_date']))
					);
				}
			}
			//$arrBody['user_uid'] = subscriptions::getOptions($arrBody['user_uid']);
			$arrBody['user_uid_display'] = 'display:none;';
			$arrBody['user_uid_edit'] = subscriptions::getOptions($arrBody['user_uid']);
			$body->assign($arrBody);
		}

		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}

	protected function doDelete() {
		if( !in_array(@$this->arrPaths[2], array('school','homeuser')))
				$this->arrPaths[2] = 'school';
		if( $this->arrPaths[4] > 0){
			$objSubscription = new subscriptions($this->arrPaths[4]);
			$objSubscription->delete();
			// redirect to invoice list if all does well;
			$objSubscription->redirectTo('admin/invoice/'.$this->arrPaths[2].'/list');
		}

	}

	protected function doList () {
		$skeleton   = make::tpl ('skeleton.admin');

		if(isset($this->arrPaths[2]) && !in_array($this->arrPaths[2], array('school','homeuser'))) {
			$this->arrPaths[2] = 'school';
		} else if(!isset($this->arrPaths[2])){
			$this->arrPaths[2] = 'school';
		}

		$body				= make::tpl ('body.admin.invoice.'.$this->arrPaths[2].'.list');
		$arrList			= array();
		$objSubscription	= new subscriptions();
		$arrList			= $objSubscription->getList();

		$query ="SELECT ";
		$query.="DISTINCT ";
		$query.="`locale` ";
		$query.="FROM ";
		$query.="`user` ";
		$query.="WHERE ";
		$query.="FIND_IN_SET('".$this->arrPaths[2]."',`user_type`)";
		$result = database::query($query);
		$locales = array();
		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			while($row=mysql_fetch_assoc($result)) {
				$locales[] = '<a href="'.config::url('admin/invoice/'.$this->arrPaths[2].'/list').'/'.$row['locale'].'/">'.$row['locale'].'</a>';
			}
		}

		$arrRows		= array();
		$objCurrency	= new currencies();
		if(!empty($arrList)) {
			foreach($arrList as $uid=>$data) {
				$data['paid_string']			= '';
				$data['paid_button_display']	= '';
				$panel = make::tpl ('body.admin.invoice.'.$this->arrPaths[2].'.list.row');
				if($data['active'] == 0 && $data['access_allowed'] == 0) {
					$data['subscription_cancelled'] = 'subscription_cancelled';
				}
				if($data['due_date'] != '0000-00-00 00:00:00' ) {
					$data['time_remains'] = ceil( (strtotime( $data['due_date'] ) - time())/(1*24*60*60) ) ;
					if( $data['time_remains'] > 7 ) {
						$data['time_class'] = 'ClassGreen';
					} else if ( $data['time_remains'] < 7 && $data['time_remains'] > 0) {
						$data['time_class'] = 'ClassOrange';
					} else {
						$data['time_class'] = 'ClassRed';
					}
					$data['time_remains'] .= 'Days';
				} else {
					$data['time_remains'] = '___';
				}
				if($data['verified'] == 1) {
					$data['verified'] = 'Yes';
				} else {
					$data['verified'] = 'No';
				}
				$data['type'] = $this->arrPaths[2];

				if($data['date_paid'] != '0000-00-00 00:00:00' ) {
					$data['date_paid']			= date('d/m/Y', strtotime($data['date_paid'])) ;
					$data['paid_string']			= 'Paid';
					$data['paid_button_display']	= 'display:none;';
				} else {
					$data['date_paid'] = '...';
				}
				if($data['due_date'] != '0000-00-00 00:00:00' ) {
					$data['due_date'] =  date('d/m/Y', strtotime($data['due_date'])) ;
				} else {
					$data['due_date'] = '...';
				}
				if(empty($data['language_prefix'])) {
					$data['language_prefix'] = 'en';
				}
				if($this->arrPaths[2] == 'school' ) {
					$data['format_amount'] = $objCurrency->getCurrencyFormat ($data['language_prefix'], $data['amount']);
				}

				$panel->assign($data);
				$arrRows[] = $panel->get_content();
			}
		}

		$page_display_title		= $objSubscription->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
		$page_navigation		= $objSubscription->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$objSubscription->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$objSubscription->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

		$body->assign('page.display.title'	, $page_display_title);
		$body->assign('page.navigation'		, $page_navigation);
		$body->assign('list.rows'			, implode('',$arrRows));
		$body->assign('list.locale'			, implode(' | ',$locales));

		$skeleton->assign (
			array (
				'body'=> $body
			)
		);
		output::as_html($skeleton,true);
	}

	protected function doExpired_due () {
		$skeleton   = make::tpl ('skeleton.admin');

		if(isset($this->arrPaths[2]) && !in_array($this->arrPaths[2], array('school','homeuser'))) {
			$this->arrPaths[2] = 'school';
		} else if(!isset($this->arrPaths[2])){
			$this->arrPaths[2] = 'school';
		}

		$body				= make::tpl ('body.admin.invoice.school.due_date.list');
		$arrList			= array();
		$objSubscription	= new subscriptions();
		$skeleton->assign('expired_count',$objSubscription->getExpiredCount());
		$arrList			= $objSubscription->dueSchoolList();

		$query ="SELECT ";
		$query.="DISTINCT ";
		$query.="`locale` ";
		$query.="FROM ";
		$query.="`user` ";
		$query.="WHERE ";
		$query.="FIND_IN_SET('".$this->arrPaths[2]."',`user_type`)";
		$result = database::query($query);
		$locales = array();
		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			while($row=mysql_fetch_assoc($result)) {
				$locales[] = '<a href="'.config::url('admin/invoice/'.$this->arrPaths[2].'/list').'/'.$row['locale'].'/">'.$row['locale'].'</a>';
			}
		}

		$arrRows		= array();
		$objCurrency	= new currencies();
		if(!empty($arrList)) {
			foreach($arrList as $uid=>$data) {
				$data['paid_string']			= '';
				$data['paid_button_display']	= '';
				$panel = make::tpl ('body.admin.invoice.school.list.row');
				if($data['active'] == 0 && $data['access_allowed'] == 0) {
					$data['subscription_cancelled'] = 'subscription_cancelled';
				}
				if($data['due_date'] != '0000-00-00 00:00:00' ) {
					$data['time_remains'] = ceil( (strtotime( $data['due_date'] ) - time())/(1*24*60*60) ) ;
					if( $data['time_remains'] > 7 ) {
						$data['time_class'] = 'ClassGreen';
					} else if ( $data['time_remains'] < 7 && $data['time_remains'] > 0) {
						$data['time_class'] = 'ClassOrange';
					} else {
						$data['time_class'] = 'ClassRed';
					}
					$data['time_remains'] .= 'Days';
				} else {
					$data['time_remains'] = '___';
				}
				if($data['verified'] == 1) {
					$data['verified'] = 'Yes';
				} else {
					$data['verified'] = 'No';
				}
				$data['type'] = $this->arrPaths[2];

				if($data['date_paid'] != '0000-00-00 00:00:00' ) {
					$data['date_paid']			= date('d/m/Y', strtotime($data['date_paid'])) ;
					$data['paid_string']			= 'Paid';
					$data['paid_button_display']	= 'display:none;';
				} else {
					$data['date_paid'] = '...';
				}
				if($data['due_date'] != '0000-00-00 00:00:00' ) {
					$data['due_date'] =  date('d/m/Y', strtotime($data['due_date'])) ;
				} else {
					$data['due_date'] = '...';
				}
				if(empty($data['language_prefix'])) {
					$data['language_prefix'] = 'en';
				}
				if($this->arrPaths[2] == 'school' ) {
					$data['format_amount'] = $objCurrency->getCurrencyFormat ($data['language_prefix'], $data['amount']);
				}

				$panel->assign($data);
				$arrRows[] = $panel->get_content();
			}
		}

		$page_display_title		= $objSubscription->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
		$page_navigation		= $objSubscription->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$objSubscription->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$objSubscription->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

		$body->assign('page.display.title'	, $page_display_title);
		$body->assign('page.navigation'		, $page_navigation);
		$body->assign('list.rows'			, implode('',$arrRows));
		$body->assign('list.locale'			, implode(' | ',$locales));

		$skeleton->assign (
			array (
				'body'=> $body
			)
		);
		output::as_html($skeleton,true);
	}
}

?>