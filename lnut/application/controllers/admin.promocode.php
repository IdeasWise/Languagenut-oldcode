<?php

class admin_promocode extends Controller {

	private $token		= 'list';

	private $arrTokens	= array (
		'list',
		'edit',
		'add',
		'delete',
		'move'
	);
	private $arrPaths	= array();

	public function __construct () {
		parent::__construct();
		$this->arrPaths = config::get('paths');
		if(isset($this->arrPaths[2]) && in_array($this->arrPaths[2], $this->arrTokens)) {
			$this->token =  $this->arrPaths[2];
		}
		if(in_array($this->token,$this->arrTokens)) {
			$method = 'do' . ucfirst($this->token); 
			$this->$method();
		}
	}

	protected function doAdd() {
		$skeleton	= make::tpl('skeleton.admin');
		$body		= make::tpl('body.admin.promocode.add');

		$objLanguage = new language();
		$locale = (isset($_POST['locale']) && strlen(trim($_POST['locale'])) > 0) ? $_POST['locale'] : '';
		if(count($_POST) > 0) {
			$objPromoCode = new promocode();
			if(($response=$objPromoCode->isValidCreate())===true) {
				output::redirect(config::url('admin/promocode/list/'));
			} else {
				if($objPromoCode->arrForm['override_date'] == 0) {
					$objPromoCode->arrForm['override_date_no'] = 'checked="checked"';
				}
				$objPromoCode->arrForm['locale'] = $objLanguage->LocaleSelectBox('locale', $locale);
				$body->assign($objPromoCode->arrForm);
			}
		}
		$body->assign(
			array(
				'locale' => $objLanguage->LocaleSelectBox('locale', $locale)
			)
		);

		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}

	protected function doEdit() {

		$skeleton		= make::tpl('skeleton.admin');
		$body			= make::tpl('body.admin.promocode.edit');
		$arrBody		= array();
		$objLanguage	= new language();
		$uid			= (isset($this->arrPaths[3]) && (int)$this->arrPaths[3] > 0) ? $this->arrPaths[3] : '';

		if($uid != '') {
			$objPromoCode = new promocode($uid);
			$objPromoCode->load();
			$arrBody['uid'] = $uid;
			$locale			= (isset($_POST['locale']) && strlen(trim($_POST['locale'])) > 0) ? $_POST['locale'] : '';
			if(count($_POST) > 0) {
				if(($response = $objPromoCode->isValidUpdate())===true) {
					output::redirect(config::url('admin/promocode/list/'));
				} else {
					if($objPromoCode->arrForm['override_date'] == 0) {
						$objPromoCode->arrForm['override_date_no'] = 'checked="checked"';
					}
					$objPromoCode->arrForm['locale'] = $objLanguage->LocaleSelectBox('locale', $locale);
					$body->assign($objPromoCode->arrForm);
				}
			} else {
				foreach( $objPromoCode->TableData as $idx => $val ){
					$arrBody[$idx] = $val['Value'];
				}
				$arrBody['locale'] = $objLanguage->LocaleSelectBox('locale', $arrBody['locale']);

				list(
					$arrBody['active_day'],
					$arrBody['active_month'],
					$arrBody['active_year']
					) = explode('-', date('d-m-Y',strtotime($arrBody['active_from'])));

				list(
					$arrBody['avail_day'],
					$arrBody['avail_month'],
					$arrBody['avail_year']
					) = explode('-', date('d-m-Y',strtotime($arrBody['active_until'])));


				list(
					$arrBody['sub_start_day'],
					$arrBody['sub_start_month'],
					$arrBody['sub_start_year']
					) = explode('-', date('d-m-Y',strtotime($arrBody['sub_start_date'])));


				list(
					$arrBody['sub_end_day'],
					$arrBody['sub_end_month'],
					$arrBody['sub_end_year']
					) = explode('-', date('d-m-Y',strtotime($arrBody['sub_end_date'])));

				if($arrBody['override_date'] == 0) {
					$arrBody['override_date_no'] = 'checked="checked"';
				}
				$body->assign($arrBody);
			}
		} else {
			output::redirect(config::url('admin/promocode/list/'));
		}

		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}

	protected function doDelete() {
		if(isset($this->arrPaths[3]) && (int)$this->arrPaths[3] > 0) {
			$objPromoCode = new promocode($this->arrPaths[3]);
			$objPromoCode->delete();
			$objPromoCode->redirectTo('admin/promocode/list/');
		} else {
			output::redirect(config::url('admin/promocode/list/'));
		}
	}

	protected function doList () {
		$skeleton		= make::tpl('skeleton.admin');
		$body			= make::tpl('body.admin.promocode.list');
		$objPromoCode	= new promocode();
		$arrPromoCodes	= $objPromoCode->getList();

		if($arrPromoCodes && count($arrPromoCodes) > 0) {
			$rows = array ();
			foreach($arrPromoCodes as $uid=>$arrData) {
				$arrData['active_from'] 	= date('d/m/Y',strtotime($arrData['active_from']));
				$arrData['active_until'] 	= date('d/m/Y',strtotime($arrData['active_until']));
				$arrData['sub_start_date'] 	= date('d/m/Y',strtotime($arrData['sub_start_date']));
				$arrData['sub_end_date'] 	= date('d/m/Y',strtotime($arrData['sub_end_date']));
				$row = make::tpl('body.admin.promocode.list.row')->assign($arrData);
				$rows[] = $row->get_content();
			}
			$body->assign('rows',implode('',$rows));
		}

		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}
}

?>