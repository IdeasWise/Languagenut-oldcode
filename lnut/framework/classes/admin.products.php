<?php

class admin_products extends Controller {

	private $token		= 'list';
	private $arrTokens	= array(
		'list',
		'add',
		'edit'
	);
	private $arrPaths	= array();
	private $tplSkeleton= '';
	private $locale		= 'en';

	public function __construct() {

		$this->arrPaths = config::get('paths');

		$method = isset($this->arrPaths[2]) ? $this->arrPaths[2] : '';

		if(!in_array($method,$this->arrTokens)) {
			output::redirect(config::url('admin/products/list/'));
		} else {
			$this->tplSkeleton = make::tpl('skeleton.admin');
			$method = 'do'.ucwords($method);
			$this->$method();
		}
	}

	protected function doList() {

		if(isset($this->arrPaths[3])) {
			$this->locale = $this->arrPaths[3];
			$arrProducts = $this->getProductsByLocale($this->locale);

			if(count($arrProducts) < 1) {
				$this->doListNoProducts();
			} else {
				$this->doListProducts($arrProducts);
			}
		} else {
			output::redirect(config::url('admin/products/list/en/'));
		}

	}

	protected function doListNoProducts() {

		$tplBody		= make::tpl($this->locale.'/body.admin.product.list.empty');

		output::as_html($this->tplSkeleton->assign('body',$tplBody->get_content()));

	}

	protected function doListProducts($arrProducts=array()) {

		$tplBody		= make::tpl($this->locale.'/body.admin.product.list');

		$arrTplProducts = array();

		if(count($arrProducts) > 0) {
			foreach($arrProducts as $uid=>$arrProduct) {
				$arrTplProducts[] = make::tpl($this->locale.'/body.admin.product.list.entry')->assign(array(
					'uid'				=> $uid,
					'name'				=> stripslashes($arrProduct['name']),
					'price'				=> ($arrProduct['position']=='before'?$arrProduct['symbol']:'').stripslashes($arrProduct['price']).($arrProduct['position']=='after'?$arrProduct['symbol']:''),
					'available_to_buy'	=> format::to_yesno_graphic($arrProduct['available_to_buy'])
				))->get_content();
			}
		}

		$tplBody->assign(array(
			'list.rows'=>implode("\n",$arrTplProducts)
		));

		$this->tplSkeleton->assign(array(
			'body' => $tplBody->get_content()
		));

		output::as_html($this->tplSkeleton, true);
	}

	protected function doEdit() {

		$productUid = (isset($this->arrPaths[3]) && (int)$this->arrPaths[3] > 0) ? $this->arrPaths[3] : false;

		if(!$productUid) {
			$this->doEditError();
		} else {
			$this->doEditProduct($productUid);
		}

	}

	protected function doEditError () {

		$tplBody		= make::tpl($this->locale.'/body.admin.product.edit.error');

		output::as_html($this->tplSkeleton->assign(array(
			'body' => $tplBody->assign(array(
				'locale'	=> $this->locale
			))->get_content()
		)));
	}

	protected function doEditProduct($productUid=false) {
		if(!$productUid || (false === ($arrProduct=$this->getProductByUid($productUid)))) {
			$this->doEditError();
		} else {

			echo config::get('locale');

			$arrProduct = $this->validateUpdate($arrProduct);

			$tplBody = make::tpl($this->locale.'/body.admin.product.edit');

			$message = $arrProduct['message'];

			if(strlen($message) > 0) {
				$message = '<p class="error">'.$message.'</p>';
				foreach($arrProduct['errors'] as $error) {
					$message.='<p class="error">'.$error.'</p>';
				}
			}

			output::as_html($this->tplSkeleton->assign(array(
				'body' => $tplBody->assign(array(
					'uid'				=> $productUid,
					'locale'			=> $this->locale,
					'message'			=> $message,
					'name'				=> stripslashes($arrProduct['name']),
					'description'		=> stripslashes($arrProduct['description']),
					'price'				=> $arrProduct['price'],
					'available_to_buy0'	=> ($arrProduct['available_to_buy']==0) ? ' checked="checked"' : '',
					'available_to_buy1'	=> ($arrProduct['available_to_buy']==1) ? ' checked="checked"' : '',
					'years_1'			=> $arrProduct['years_1'],
					'years_2'			=> $arrProduct['years_2'],
					'years_3'			=> $arrProduct['years_3'],
					'years_4'			=> $arrProduct['years_4'],
					'years_5'			=> $arrProduct['years_5']
				))->get_content()
			)));
		}
	}

	protected function validateUpdate($arrProduct=array()) {
		$arrData = $_POST;
		$arrErrors = array();

		$error = false;

		if(count($arrData) > 0) {
			$arrData['message'] = '';
			if(isset($arrData['action'])) {
				switch($arrData['action']) {
					case 'update':

						// data capture
						$name				= (isset($arrData['name']) ? trim($arrData['name']) : '');
						$description		= (isset($arrData['description']) ? trim($arrData['description']) : '');
						$price				= (isset($arrData['price']) ? trim($arrData['price']) : '');
						$available_to_buy	= (isset($arrData['available_to_buy']) ? (int)$arrData['available_to_buy'] : 1);

						// validation rules
						if(strlen($name) < 1 || strlen($name) > 32) {
							$error = true;
							$arrData['error.name'] = 'error';
							$arrErrors[] = 'Name must be 1-32 characters in length';
						} else {
							$arrData['name']			= $name;
						}

						$arrData['description']			= $description;
						$arrData['price']				= $price;
						$arrData['available_to_buy']	= $available_to_buy;

						// set defaults
						$arrData['years_1']	= $arrProduct['years_1'];
						$arrData['years_2']	= $arrProduct['years_2'];
						$arrData['years_3']	= $arrProduct['years_3'];
						$arrData['years_4']	= $arrProduct['years_4'];
						$arrData['years_5'] = $arrProduct['years_5'];

					break;
					case 'pricing':

						// data capture
						$years_1			= (isset($arrData['years_1']) ? trim($arrData['years_1']) : '');
						$years_2			= (isset($arrData['years_2']) ? trim($arrData['years_2']) : '');
						$years_3			= (isset($arrData['years_3']) ? trim($arrData['years_3']) : '');
						$years_4			= (isset($arrData['years_4']) ? trim($arrData['years_4']) : '');
						$years_5			= (isset($arrData['years_5']) ? trim($arrData['years_5']) : '');

						// validation rules
						$arrData['years_1']	= $years_1;
						$arrData['years_2']	= $years_2;
						$arrData['years_3']	= $years_3;
						$arrData['years_4']	= $years_4;
						$arrData['years_5']	= $years_5;

						// set defaults
						$arrData['name']			= $arrProduct['name'];
						$arrData['description']		= $arrProduct['description'];
						$arrData['price']			= $arrProduct['price'];
						$arrData['available_to_buy']= $arrProduct['available_to_buy'];

					break;
				}

				if($error) {
					$arrData['message'] = 'Please correct the errors below.';
					$arrData['errors']	= $arrErrors;
				} else {
					$this->updateProduct($arrData);
					output::redirect('/admin/products/'.$this->locale.'/edit/'.$arrData['uid'].'/');
				}
			}
		} else {
			$arrData = $arrProduct;
			$arrData['message'] = '';
		}

		return $arrData;
	}

	protected function getProductsByLocale($locale='en') {
		$arrProducts = array();

		if(false !== ($language_uid = $this->getLanguageUidFromLocale($locale))) {
			$query = "SELECT ";
			$query.= "`product_locale`.`uid`, ";
			$query.= "`product_locale`.`product_uid`, ";
			$query.= "`product_locale`.`name`, ";
			$query.= "`product_locale`.`price`, ";
			$query.= "`product_locale`.`available_to_buy`, ";
			$query.= "`currencies`.`symbol`, ";
			$query.= "`currencies`.`position` ";
			$query.= "FROM ";
			$query.= "`product_locale`, ";
			$query.= "`language`, ";
			$query.= "`currencies` ";
			$query.= "WHERE ";
			$query.= "`product_locale`.`language_uid`=$language_uid ";
			$query.= "AND `product_locale`.`language_uid`=`language`.`uid` ";
			$query.= "AND `currencies`.`uid`=`language`.`currency_uid` ";
			$query.= "ORDER BY ";
			$query.= "`product_locale`.`name` ASC";

			$result = database::query($query);

			if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
				while($row = mysql_fetch_assoc($result)) {
					$arrProducts[$row['uid']] = $row;
				}
			}
		}

		return $arrProducts;
	}

	protected function getLanguageUidFromLocale($locale='en') {
		$query = "SELECT ";
		$query.= "`uid` ";
		$query.= "FROM ";
		$query.= "`language` ";
		$query.= "WHERE ";
		$query.= "`prefix`='".mysql_real_escape_string($locale)."' ";
		$query.= "LIMIT 1";

		$result = database::query($query);

		$uid = false;

		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			$uid = $row['uid'];
		}

		return $uid;
	}

	protected function getProductByUid($productUid=false) {

		$arrProduct = array();

		$query = "SELECT ";
		$query.= "`name`, ";
		$query.= "`description`, ";
		$query.= "`price`, ";
		$query.= "`available_to_buy`, ";
		$query.= "`years_1`, ";
		$query.= "`years_2`, ";
		$query.= "`years_3`, ";
		$query.= "`years_4`, ";
		$query.= "`years_5` ";
		$query.= "FROM ";
		$query.= "`product_locale` ";
		$query.= "WHERE ";
		$query.= "`uid`=$productUid ";
		$query.= "LIMIT 1";

		$result = database::query($query);

		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			$arrProduct = mysql_fetch_assoc($result);
		} else {
			echo mysql_error();
		}

		return $arrProduct;
	}

	protected function updateProduct($arrData=array()) {
		$query = "UPDATE ";
		$query.= "`product_locale` ";
		$query.= "SET ";
		$query.= "`name`='".mysql_real_escape_string($arrData['name'])."', ";
		$query.= "`description`='".mysql_real_escape_string($arrData['description'])."', ";
		$query.= "`price`='".mysql_real_escape_string($arrData['price'])."', ";
		$query.= "`available_to_buy`='".(int)$arrData['available_to_buy']."', ";
		$query.= "`years_1`='".mysql_real_escape_string($arrData['years_1'])."', ";
		$query.= "`years_2`='".mysql_real_escape_string($arrData['years_2'])."', ";
		$query.= "`years_3`='".mysql_real_escape_string($arrData['years_3'])."', ";
		$query.= "`years_4`='".mysql_real_escape_string($arrData['years_4'])."', ";
		$query.= "`years_5`='".mysql_real_escape_string($arrData['years_5'])."' ";
		$query.= "WHERE ";
		$query.= "`uid`=".$arrData['uid']." LIMIT 1";

		database::query($query);
	}
}

?>