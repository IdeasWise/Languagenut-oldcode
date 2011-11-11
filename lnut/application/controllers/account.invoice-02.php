<?php

class acccount_invoice extends Controller {

	private $token		= 'list';
	private $arrTokens	= array (
		'list',
		'edit',
		'add',
		'delete',
		'paid',
		'view',
	);
	private $Paths		= array();

	public function __construct () {
		parent::__construct();
		$this->arrPaths = config::get('paths');
		if(isset($this->arrPaths[3]) && in_array($this->arrPaths[3], $this->arrTokens)) {
			$this->token =  $this->arrPaths[3];
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
		if(isset($_POST['PaidUid']) && is_numeric($_POST['PaidUid']) && $_POST['PaidUid'] > 0 ){
			$objSubscription = new subscriptions();
			echo $objSubscription->doPaid($_POST['PaidUid']);
		}
	}

	protected function doAdd() {
		$skeleton	= config::getUserSkeleton();
		$body		= make::tpl('body.admin.invoice.add');

		$arrBody = array();
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
				$objSubscription->redirectTo('account/invoice/'.$this->arrPaths[2].'/list');
			} else{
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

		$skeleton	= config::getUserSkeleton();
		$body		= make::tpl('body.admin.invoice.add');
		$arrBody	= array();

		if(isset($this->arrPaths[5]) && $this->arrPaths[5] == 'profile')
			$arrBody['redirect'] = "1";

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

		if( isset($_POST['submit-button'])){
			$objSubscription	= new subscriptions();
			if( $objSubscription->doSave() ){
				if(isset($_POST['redirect']) && $_POST['redirect'] == 1 && isset($_POST['user_uid'])) {
					// redirect to invoice list if all does well;
					$objSubscription->redirectTo('account/users/profile/school/'.$_POST['user_uid'].'/');
				} else {
					// redirect to invoice list if all does well;
					$objSubscription->redirectTo('account/invoice/'.$this->arrPaths[2].'/list');
				}

			} else{
				$arrBody['user_uid_display']	= 'display:none;';
				$arrBody['user_uid_edit']		= subscriptions::getOptions($objSubscription->arrForm['user_uid']);
				$body->assign( $arrBody );
				$body->assign( $objSubscription->arrForm );
			}
		} else {
			if(isset($this->arrPaths[4]) && $this->arrPaths[4] > 0){
				$arrBody['uid']		= $this->arrPaths[4];
				$objSubscription	= new subscriptions($arrBody['uid']);
				$objSubscription->load();
				foreach($objSubscription->TableData as $idx => $val ){
					$arrBody[$idx] = $val['Value'];
				}
				if($arrBody['verified'] == 0){
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

				if($arrBody['date_paid'] != '0000-00-00 00:00:00' ) {
					list(
						$arrBody['date_day'],
						$arrBody['date_month'],
						$arrBody['date_year']) = explode('-', date('d-m-Y', strtotime($arrBody['date_paid']))
					);
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
			//$arrBody['user_uid'] = $this->getOptions($arrBody['user_uid']);
			$arrBody['user_uid_display'] = 'display:none;';
			$arrBody['user_uid_edit'] = subscriptions::getOptions($arrBody['user_uid']);
			$body->assign( $arrBody );
		}


		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}

	protected function doDelete() {
		if(isset($this->arrPaths[2]) && !in_array($this->arrPaths[2], array('school','homeuser'))) {
			$this->arrPaths[2] = 'school';
		} else if(!isset($this->arrPaths[2])){
			$this->arrPaths[2] = 'school';
		}
		if($this->arrPaths[4] > 0){
			$objSubscription = new subscriptions($this->arrPaths[4]);
			$objSubscription->delete();
			// redirect to invoice list if all does well;
			$objSubscription->redirectTo('account/invoice/'.$this->arrPaths[2].'/list');
		}
	}

	protected function doList () {

		$skeleton = config::getUserSkeleton();
		if(isset($this->arrPaths[2]) && !in_array($this->arrPaths[2], array('school','homeuser','pending'))) {
			$this->arrPaths[2] = 'school';
		} else if(!isset($this->arrPaths[2])){
			$this->arrPaths[2] = 'school';
		}

		$body				= make::tpl('body.reseller.account.invoice.'.$this->arrPaths[2].'.list');
		$arrRows			= array();
		$objSubscription	= new subscriptions();
		if($this->arrPaths[2]=='pending') {
			$page = 1;
			if(isset($this->arrPaths[4]) && is_numeric($this->arrPaths[4])) {
				$page = $this->arrPaths[4];
			}

			$arrRows		= $objSubscription->getListPending($page);
		} else {
			$arrRows		= $objSubscription->getList();
		}

		if($this->arrPaths[2]!=='pending') {
			$query ="SELECT ";
			$query.="DISTINCT `locale` ";
			$query.="FROM ";
			$query.="`user` ";
			$query.="WHERE ";
			$query.="`locale` in (".$_SESSION['user']['localeRights'].") ";
			$query.="AND ";
			$query.="FIND_IN_SET('".$this->arrPaths[2]."',`user_type`)";
		} else {
			$query = "SELECT ";
			$query.= "DISTINCT `locale` ";
			$query.= "FROM `user` ";
			$query.= "`locale` IN (".$_SESSION['user']['localeRights'].") ";
		}

		$result		= database::query( $query );
		$locales	= array();
		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			while($row=mysql_fetch_assoc($result)) {
				$locales[] = '<a href="'.config::url('account/invoice/'.$this->arrPaths[2].'/list').'/'.$row['locale'].'/">'.$row['locale'].'</a>';
			}
		}

		$page_rows = array();
		$objCurrency = new currencies();

		if(!empty($arrRows)) {
			foreach($arrRows as $uid=>$data) {
				$data['paid_string']			= '';
				$data['paid_button_display']	= '';
				$panel = make::tpl('body.reseller.account.invoice.'.$this->arrPaths[2].'.list.row');

				if($data['active'] == 0 && $data['access_allowed'] == 0) {
					$data['subscription_cancelled'] = 'subscription_cancelled';
				}

				if($data['due_date'] != '0000-00-00 00:00:00' ) {
					//$data['time_remains'] =   ceil( (strtotime( $data['due_date'] ) - time())/(1*24*60*60) ) ;
					$data['time_remains'] =   ceil( (strtotime( $data['expires_dts'] ) - time())/(1*24*60*60) ) ;
					if( $data['time_remains'] > 7 ) {
						$data['time_class'] = 'ClassGreen';
					} elseif( $data['time_remains'] < 7 && $data['time_remains'] > 0) {
						$data['time_class'] = 'ClassOrange';
					} else {
						$data['time_class'] = 'ClassRed';
					}
					$data['time_remains'] .= ' Days';
				} else {
					$data['time_remains'] = '___';
				}
				if($data['verified'] == 1) {
					$data['verified'] = 'Yes';
				} else {
					$data['verified'] = 'No';
				}

				$data['type'] = $this->arrPaths[2];

				$data['expires'] = date('d/m/Y',strtotime($data['expires_dts']));

				if($data['date_paid'] != '0000-00-00 00:00:00' ) {
					$data['date_paid'] =  date('d/m/Y', strtotime($data['date_paid'])) ;
					$data['paid_string'] = 'Paid';
					$data['paid_button_display'] = 'display:none;';
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

				if($data['call_status']==0) {
					$data['call_status'] = 'Not Called';
				} else if($data['call_status']==1) {
					$data['call_status'] = 'Call Made: No Contact';
				} else if($data['call_status']==2) {
					$data['call_status'] = 'Call Made: Contact';
				} else {
					$data['call_status'] = 'Other';
				}

				if(isset($data['registered_dts'])) {
					$data['years_subscribing'] = ceil(date('Y')-date('Y',strtotime($data['registered_dts'])));

					$expiry			= strtotime($data['expires_dts']);
					$regd			= strtotime($data['registered_dts']);
					$verified		= ($data['verified']==1 ? true : false);

					$now			= time();
					$two_weeks_ago	= mktime(date('H'),date('i'),date('s'),date('m'),date('d')-14,date('Y'));

					$hasActiveSubscription = true;

					if($hasActiveSubscription) {
						if($data['cancel_dts'] != '0000-00-00 00:00:00') {
							$data['extra_style'] = ' style="background:#FF5347;color:white;"';
						} else if($regd < $two_weeks_ago && !$verified) {
							$data['extra_style'] = ' style="background:#FCBCAE;"';
						} else if($regd < $two_weeks_ago && $verified) {
							$data['extra_style'] = ' style="background:#B8ED9C;"';
						} else if($regd > $two_weeks_ago) {
							$data['extra_style'] = ' style="background:#FCC52F;"';
						}
					}
				}

				$panel->assign($data);
				$page_rows[] = $panel->get_content();
			}
		}


    /*[name] => sabina Common
    [user_uid] => 73
    [email] => sabinacommon@hotmail.com
    [due_date] => 2010-02-03 00:00:00
    [date_paid] => 2010-01-01 00:00:00
    [amount] => 80.00
    [start_dts] => 2010-01-20 00:00:00
    [expires_dts] => 2011-01-20 00:00:00
    [active] => 0
    [access_allowed] => 0
    [verified] => 1*/

		$page_display_title	= $objSubscription->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
		$page_navigation	= $objSubscription->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$objSubscription->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$objSubscription->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

		$body->assign('page.display.title'	, $page_display_title);
		$body->assign('page.navigation'		, $page_navigation);
		$body->assign('list.rows'			, implode('',$page_rows));
		$body->assign('list.locale'			, implode(' | ',$locales));

		$skeleton->assign (
			array (
				'body'	=> $body
			)
		);
		output::as_html($skeleton,true);
	}

}

?>