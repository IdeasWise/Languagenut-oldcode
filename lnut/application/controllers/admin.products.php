<?php

class admin_products extends Controller {

	private $token		= 'list';
	private $arrTokens	= array(
		'list',
		'add',
		'addmain',
		'edit',
		'editmain',
		'delete',
		'deletemain'
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

		$tplBody	= make::tpl('en/body.admin.product.list.empty');

		$arrLocales	= $this->getLocaleLinks($this->locale);

		$tplBody->assign(array(
			'locales'	=> implode("\n",$arrLocales)
		));

		$this->tplSkeleton->assign(array(
			'body'		=> $tplBody->get_content()
		));

		output::as_html($this->tplSkeleton);
	}

	protected function doListProducts($arrProductsLocalised=array()) {

		$tplBody		= make::tpl('en/body.admin.product.list');

		$arrTplProductsLocalised = array();

		if(count($arrProductsLocalised) > 0) {
			foreach($arrProductsLocalised as $uid=>$arrProduct) {
				$arrTplProductsLocalised[] = make::tpl('en/body.admin.product.list.entry')->assign(array(
					'uid'				=> $uid,
					'name'				=> stripslashes($arrProduct['name']),
					'price'				=> ($arrProduct['position']=='before'?$arrProduct['symbol']:'').stripslashes($arrProduct['price']).($arrProduct['position']=='after'?$arrProduct['symbol']:''),
					'available_to_buy'	=> format::to_yesno_graphic($arrProduct['available_to_buy'])
				))->get_content();
			}
		}

		$arrTplProducts = array();

		$arrProducts = $this->getProducts();

		if(count($arrProducts) > 0) {
			foreach($arrProducts as $uid=>$arrProduct) {
				$arrTplProducts[] = make::tpl('en/body.admin.product.list.main')->assign(array(
					'uid'		=> $uid,
					'name'		=> $arrProduct['name'],
					'token'		=> $arrProduct['token'],
					'description'=>$arrProduct['description']
				))->get_content();
			}
		}

		$arrLocales	= $this->getLocaleLinks($this->locale);

		$tplBody->assign(array(
			'locale.list.rows'	=>implode("\n",$arrTplProductsLocalised),
			'list.rows'			=>implode("\n",$arrTplProducts),
			'locales'			=>implode("\n",$arrLocales)
		));

		$this->tplSkeleton->assign(array(
			'body' => $tplBody->get_content()
		));

		output::as_html($this->tplSkeleton, true);
	}





	protected function doAdd() {

		$arrProduct = $this->validateProductInsert();

		$tplBody = make::tpl($this->locale.'/body.admin.product.add');

		$message = $arrProduct['message'];

		if(strlen($message) > 0) {
			$message = '<p class="error">'.$message.'</p>';
			foreach($arrProduct['errors'] as $error) {
				$message.='<p class="error">'.$error.'</p>';
			}
		} else if(isset($_SESSION['message'])) {
			$message = '<p class="success" style="border-width:2px 0 2px 0;border-color:#009900;background:#bfffcf;font-weight:bold;border-style:solid;text-align:center;">Record Saved</p>';
			unset($_SESSION['message']);
		}

		output::as_html($this->tplSkeleton->assign(array(
			'body' => $tplBody->assign(array(
				'locale'			=> $this->locale,
				'message'			=> $message
			))->get_content()
		)));
	}

	protected function doAddmain() {

		$arrProduct = $this->validateMainProductInsert();

		$tplBody = make::tpl($this->locale.'/body.admin.product.addmain');

		$message = $arrProduct['message'];

		if(strlen($message) > 0) {
			$message = '<p class="error">'.$message.'</p>';
			foreach($arrProduct['errors'] as $error) {
				$message.='<p class="error">'.$error.'</p>';
			}
		} else if(isset($_SESSION['message'])) {
			$message = '<p class="success" style="border-width:2px 0 2px 0;border-color:#009900;background:#bfffcf;font-weight:bold;border-style:solid;text-align:center;">Record Saved</p>';
			unset($_SESSION['message']);
		}

		output::as_html($this->tplSkeleton->assign(array(
			'body'	=> $tplBody->assign(array(
				'locale'	=> $this->locale,
				'message'	=> $message
			))->get_content()
		)));
	}





	protected function doEdit() {

		$productUid = (isset($this->arrPaths[3]) && (int)$this->arrPaths[3] > 0) ? $this->arrPaths[3] : false;

		if(!$productUid) {
			$this->doEditError();
		} else {
			$this->doEditProduct($productUid);
		}
	}

	protected function doEditmain() {

		$productUid = (isset($this->arrPaths[3]) && (int)$this->arrPaths[3] > 0) ? $this->arrPaths[3] : false;

		if(!$productUid) {
			$this->doEditError();
		} else {
			$this->doEditMainProduct($productUid);
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

			if(isset($this->arrPaths[4]) && $this->arrPaths[4]=='packages') {

				$arrPackages = $this->getResellerSubPackages($productUid);
				$arrPackages = $this->validatePackagesUpdate($arrPackages);

				$arrProductPackages = $this->getProductPackages($productUid);

				$arrTplPackages = array();

				if(count($arrPackages) > 0) {
					foreach($arrPackages as $uid=>$arrPackage) {
						$arrTplPackages[] = make::tpl($this->locale.'/body.admin.product.packages.entry')->assign(array(
							'uid'							=> $uid,
							'name'							=> stripslashes($arrPackage['name']),
							'learnable_language'			=> $this->toLanguagesFromUids($arrPackage['learnable_language']),
							'package_type'					=> ucwords($arrPackage['package_type']),
							'is_default_school_package'		=> format::to_yesno_graphic($arrPackage['is_default_school_package']),
							'is_default_homeuser_package'	=> format::to_yesno_graphic($arrPackage['is_default_homeuser_package']),
							'support_language'				=> $this->getLanguageNameFromUid($arrPackage['support_language_uid']),
							'yes_checked'					=> (in_array($uid,$arrProductPackages)?' checked="checked"' : ''),
							'no_checked'					=> (!in_array($uid,$arrProductPackages)?' checked="checked"' : '')
						))->get_content();
					}
				}

				$tplBody = make::tpl($this->locale.'/body.admin.product.packages');

				$message = '';

				if(isset($_SESSION['message'])) {
					$message = '<p class="success" style="border:2px solid #009900;border-width:2px 0 2px 0;background:#bfffcf;font-weight:bold;text-align:center;">Packages Associated</p>';
					unset($_SESSION['message']);
				}

				output::as_html($this->tplSkeleton->assign(array(
					'body'	=> $tplBody->assign(array(
						'uid'			=> $productUid,
						'name'			=> stripslashes($arrProduct['name']),
						'locale'		=> $this->locale,
						'message'		=> $message,
						'rows.list'		=> implode("\n",$arrTplPackages)
					))->get_content()
				)));

			} else {

				$arrProduct = $this->validateProductUpdate($arrProduct);
				$tplBody = make::tpl($this->locale.'/body.admin.product.edit');

				$message = $arrProduct['message'];

				if(strlen($message) > 0) {
					$message = '<p class="error">'.$message.'</p>';
					foreach($arrProduct['errors'] as $error) {
						$message.='<p class="error">'.$error.'</p>';
					}
				} else if(isset($_SESSION['message'])) {
					$message = '<p class="success" style="border-width:2px 0 2px 0;border-color:#009900;background:#bfffcf;font-weight:bold;border-style:solid;text-align:center;">Record Saved</p>';
					unset($_SESSION['message']);
				}

				output::as_html($this->tplSkeleton->assign(array(
					'body' => $tplBody->assign(array(
						'uid'				=> $productUid,
						'locale'			=> $this->locale,
						'message'			=> $message,
						'name'				=> stripslashes($arrProduct['name']),
						'description'		=> stripslashes($arrProduct['description']),
						'price'				=> $arrProduct['price'],
						'years_1'			=> $arrProduct['years_1'],
						'years_2'			=> $arrProduct['years_2'],
						'years_3'			=> $arrProduct['years_3'],
						'years_4'			=> $arrProduct['years_4'],
						'years_5'			=> $arrProduct['years_5'],
						'available_to_buy0'	=> ($arrProduct['available_to_buy']==0)?'checked="checked"':'',
						'available_to_buy1'	=> ($arrProduct['available_to_buy']==1)?'checked="checked"':''
					))->get_content()
				)));
			}
		}
	}

	protected function doEditMainProduct($productUid = false) {

		if(!$productUid || (false === ($arrProduct=$this->getMainProductByUid($productUid)))) {
			$this->doEditError();
		} else {
			$arrProduct = $this->validateMainProductUpdate($arrProduct);

			$tplBody = make::tpl($this->locale.'/body.admin.product.editmain');

			$message = $arrProduct['message'];

			if(strlen($message) > 0) {
				$message = '<p class="error">'.$message.'</p>';
				foreach($arrProduct['errors'] as $error) {
					$message.='<p class="error">'.$error.'</p>';
				}
			} else if(isset($_SESSION['message'])) {
				$message.='<p class="success" style="border-width:2px 0 2px 0;border-colour:#009900;background:#bfffcf;font-weight:bold;border-style:solid;text-align:center;">Record Saved</p>';
				unset($_SESSION['message']);
			}

			output::as_html($this->tplSkeleton->assign(array(
				'body'	=> $tplBody->assign(array(
					'uid'					=> $productUid,
					'locale'				=> $this->locale,
					'message'				=> $message,
					'name'					=> stripslashes($arrProduct['name']),
					'description'			=> stripslashes($arrProduct['description']),
					'site_map'				=> str_replace(array('{','}'),array('&#123;','&#125;'),stripslashes($arrProduct['site_map'])),
					'product_type_homeuser'	=> ($arrProduct['product_type']=='homeuser')?'checked="checked"':'',
					'product_type_school'	=> ($arrProduct['product_type']=='school')?'checked="checked"':''
				))->get_content()
			)));
		}
	}





	protected function doDelete($redirect=true) {
		if(isset($this->arrPaths[3]) && (int)$this->arrPaths[3] > 0) {

			$package_uid = (int)$this->arrPaths[3];

			$query = "DELETE FROM `product_locale` WHERE `package_uid`=$package_uid";
			database::query($query);

			if($redirect) {
				output::redirect(config::admin_uri('products/list/'.$this->locale.'/'));
			}
		}
	}

	protected function doDeletemain() {
		if(isset($this->arrPaths[3]) && (int)$this->arrPaths[3] > 0) {

			$this->doDelete(false);

			$package_uid = (int)$this->arrPaths[3];

			$query = "DELETE FROM `product` WHERE `uid`=$package_uid";
			database::query($query);

			output::redirect(config::admin_uri('products/list/'.$this->locale.'/'));
		}
	}





	/*[ UTILITY FUNCTIONS TO BE MOVED TO OBJECT METHODS ]*/

	protected function getLocaleLinks($selected='') {

		$arrLocaleLinks = array();

		$query = "SELECT ";
		$query.= "`prefix` ";
		$query.= "FROM ";
		$query.= "`language` ";
		if($selected != '') {
			$query.= "WHERE ";
			$query.= "`uid` NOT IN('$selected') ";
		}
		$query.= "ORDER BY ";
		$query.= "`prefix` ASC";

		$result= database::query($query);

		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			while($row = mysql_fetch_assoc($result)) {
				$arrLocaleLinks[] = '<span style="padding:0 3px;">[<a href="'.config::admin_uri('products/list/'.$row['prefix'].'/').'"'.($row['prefix']==$this->locale?' class="selected"' : '').'>'.$row['prefix'].'</a>]</span>';
			}
		}

		return $arrLocaleLinks;
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

	protected function getProducts() {
		$arrProducts = array();

		$query = "SELECT ";
		$query.= "`uid`, ";
		$query.= "`token`, ";
		$query.= "`name`, ";
		$query.= "`site_map`, ";
		$query.= "`description` ";
		$query.= "FROM ";
		$query.= "`product` ";
		$query.= "ORDER BY ";
		$query.= "`name` ASC";

		$result = database::query($query);

		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			while($row = mysql_fetch_assoc($result)) {
				$arrProducts[$row['uid']] = $row;
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

	protected function getLanguageNameFromUid($language_uid=null) {
		$query = "SELECT ";
		$query.= "`name` ";
		$query.= "FROM ";
		$query.= "`language` ";
		$query.= "WHERE ";
		$query.= "`uid`='".(int)$language_uid."' ";
		$query.= "LIMIT 1";

		$result = database::query($query);

		$name = false;

		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			$name = stripslashes($row['name']);
		}

		return $name;
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

	protected function getMainProductByUid($productUid=false) {

		$arrProduct = array();

		$query = "SELECT ";
		$query.= "`name`, ";
		$query.= "`description`, ";
		$query.= "`site_map`, ";
		$query.= "`product_type` ";
		$query.= "FROM ";
		$query.= "`product` ";
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

	protected function getResellerSubPackages($productUid=false) {

		$arrPackages = false;

		$query = "SELECT ";
		$query.= "`reseller_sub_package`.`uid`, ";
		$query.= "`reseller_sub_package`.`name`, ";
		$query.= "`reseller_sub_package`.`learnable_language`, ";
		$query.= "`reseller_sub_package`.`package_type`, ";
		$query.= "`reseller_sub_package`.`is_default_school_package`, ";
		$query.= "`reseller_sub_package`.`is_default_homeuser_package`, ";
		$query.= "`reseller_sub_package`.`support_language_uid` ";
		$query.= "FROM ";
		$query.= "`reseller_sub_package`, ";
		$query.= "`product_locale`, ";
		$query.= "`language`, ";
		$query.= "`user` ";
		$query.= "WHERE ";
		$query.= "`product_locale`.`uid`=$productUid ";
		$query.= "AND `reseller_sub_package`.`reseller_uid`=`user`.`uid` ";
		$query.= "AND `user`.`locale`=`language`.`prefix` ";
		$query.= "AND `language`.`uid`=`product_locale`.`language_uid` ";
		$query.= "ORDER BY ";
		$query.= "`reseller_sub_package`.`package_type`,`reseller_sub_package`.`name`";

		$result = database::query($query);

		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			while($row = mysql_fetch_assoc($result)) {
				$arrPackages[$row['uid']] = $row;
			}
		}

		return $arrPackages;
	}

	protected function getProductPackages($productUid=false) {

		$arrPackages = array();

		if(false !== $productUid) {
			$query = "SELECT ";
			$query.= "`sub_package_uid` ";
			$query.= "FROM ";
			$query.= "`product_package` ";
			$query.= "WHERE ";
			$query.= "`product_uid`=$productUid ";

			$result = database::query($query);

			if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
				while($row = mysql_fetch_assoc($result)) {
					$arrPackages[] = $row['sub_package_uid'];
				}
			}
		}

		return $arrPackages;
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

	protected function updateMainProduct($arrData=array()) {
		$query = "UPDATE ";
		$query.= "`product` ";
		$query.= "SET ";
		$query.= "`name`='".mysql_real_escape_string($arrData['name'])."', ";
		$query.= "`product_type`='".mysql_real_escape_string($arrData['product_type'])."', ";
		$query.= "`token`='".mysql_real_escape_string(format::to_friendly_url($arrData['name']))."', ";
		$query.= "`description`='".mysql_real_escape_string($arrData['description'])."', ";
		$query.= "`site_map`='".mysql_real_escape_string(str_replace(array('&#123;','&#125;'),array('{','}'),$arrData['site_map']))."' ";
		$query.= "WHERE ";
		$query.= "`uid`=".$arrData['uid']." LIMIT 1";

		database::query($query);
	}

	protected function getLanguageUids() {

		$arrLanguages = array();

		$query = "SELECT ";
		$query.= "`uid` ";
		$query.= "FROM ";
		$query.= "`language` ";

		$result = database::query($query);

		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			while($row = mysql_fetch_assoc($result)) {
				$arrLanguages[] = $row['uid'];
			}
		}

		return $arrLanguages;
	}

	protected function addProduct($arrData=array()) {
		$query = "INSERT INTO ";
		$query.= "`product_locale` (";
		$query.= "`name`, ";
		$query.= "`product_uid` ";
		$query.= ") VALUES (";
		$query.= "'".mysql_real_escape_string($arrData['name'])."', ";
		$query.= "'".(int)$arrData['product_uid']."'";
		$query.= ")";

		$insert_id = database::insert($query);

		return $insert_id;
	}

	protected function addMainProduct($arrData=array()) {
		$query = "INSERT INTO ";
		$query.= "`product` (";
		$query.= "`name`, ";
		$query.= "`token`, ";
		$query.= "`product_type` ";
		$query.= ") VALUES (";
		$query.= "'".mysql_real_escape_string($arrData['name'])."', ";
		$query.= "'".mysql_real_escape_string(format::to_friendly_url($arrData['name']))."',";
		$query.= "'".mysql_real_escape_string($arrData['product_type'])."'";
		$query.= ")";

		$insert_id = database::insert($query);

		$this->addProductsPerLocale($insert_id,$arrData);

		return $insert_id;
	}

	protected function addProductsPerLocale($insert_id=false,$arrData=array()) {
		$arrLanguages = $this->getLanguageUids();

		if(count($arrLanguages) > 0) {

			$name = mysql_real_escape_string($arrData['name']);

			$query = "INSERT INTO `product_locale` (";
			$query.= "`product_uid`, ";
			$query.= "`language_uid`, ";
			$query.= "`name` ";
			$query.= ") VALUES ";
			$parts=array();
			foreach($arrLanguages as $language_uid) {
				$parts[] = "(".$insert_id.",".$language_uid.",'".$name."')";
			}
			$query.= implode(',',$parts);

			database::query($query);
		}
	}

	protected function toLanguagesFromUids($jsonLanguages=null) {
		$strLanguages = '';

		if(null !== $jsonLanguages) {
			$obj = json_decode($jsonLanguages);

			if(isset($obj->language_uids) && count($obj->language_uids) > 0) {
				$query = "SELECT ";
				$query.= "`name` ";
				$query.= "FROM ";
				$query.= "`language` ";
				$query.= "WHERE ";
				$query.= "`uid` IN (".implode(',',$obj->language_uids).") ";
				$query.= "ORDER BY ";
				$query.= "`name` ASC";

				$result = database::query($query);

				if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
					while($row = mysql_fetch_assoc($result)) {
						$strLanguages.=stripslashes($row['name']).'<br />';
					}
				}
			}
		}

		return $strLanguages;
	}

	protected function validateProductUpdate($arrProduct=array()) {
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
					$_SESSION['message'] = 'success';
					output::redirect(config::admin_uri('products/edit/'.$arrData['uid'].'/'));
				}
			}
		} else {
			$arrData = $arrProduct;
			$arrData['message'] = '';
		}

		return $arrData;
	}

	protected function validateMainProductUpdate($arrProduct=array()) {
		$arrData = $_POST;
		$arrErrors = array();

		$error = false;

		if(count($arrData) > 0) {
			$arrData['message'] = '';

			// data capture
			$name				= (isset($arrData['name']) ? trim($arrData['name']) : '');
			$description		= (isset($arrData['description']) ? trim($arrData['description']) : '');
			$site_map			= (isset($arrData['site_map']) ? trim($arrData['site_map']) : '');

			// validation rules
			if(strlen($name) < 1 || strlen($name) > 32) {
				$error = true;
				$arrData['error.name'] = 'error';
				$arrErrors[] = 'Name must be 1-32 characters in length';
			} else {
				$arrData['name']			= $name;
			}

			$arrData['description']			= $description;
			$arrData['site_map']			= $site_map;

			if($error) {
				$arrData['message'] = 'Please correct the errors below.';
				$arrData['errors']	= $arrErrors;
			} else {
				$this->updateMainProduct($arrData);
				$_SESSION['message'] = 'success';
				output::redirect(config::admin_uri('products/editmain/'.$arrData['uid'].'/'));
			}

		} else {
			$arrData = $arrProduct;
			$arrData['message'] = '';
		}

		return $arrData;
	}

	protected function validateProductInsert() {
		$arrData = $_POST;
		$arrErrors = array();

		$error = false;

		if(count($arrData) > 0) {
			$arrData['message'] = '';

			// data capture
			$name				= (isset($arrData['name']) ? trim($arrData['name']) : '');

			// validation rules
			if(strlen($name) < 1 || strlen($name) > 32) {
				$error = true;
				$arrData['error.name'] = 'error';
				$arrErrors[] = 'Name must be 1-32 characters in length';
			} else {
				$arrData['name']			= $name;
			}

			if($error) {
				$arrData['message'] = 'Please correct the errors below.';
				$arrData['errors']	= $arrErrors;
			} else {
				$uid = $this->addProduct($arrData);
				$_SESSION['message'] = 'success';
				output::redirect(config::admin_uri('products/edit/'.$uid.'/'));
			}
		} else {
			$arrData = array();
			$arrData['message'] = '';
		}

		return $arrData;
	}

	protected function validateMainProductInsert() {
		$arrData = $_POST;
		$arrErrors = array();

		$error = false;

		if(count($arrData) > 0) {
			$arrData['message'] = '';

			// data capture
			$name				= (isset($arrData['name']) ? trim($arrData['name']) : '');

			// validation rules
			if(strlen($name) < 1 || strlen($name) > 32) {
				$error = true;
				$arrData['error.name'] = 'error';
				$arrErrors[] = 'Name must be 1-32 characters in length';
			} else {
				$arrData['name']			= $name;
			}

			if($error) {
				$arrData['message'] = 'Please correct the errors below.';
				$arrData['errors']	= $arrErrors;
			} else {
				$uid = $this->addMainProduct($arrData);
				$_SESSION['message'] = 'success';
				output::redirect(config::admin_uri('products/editmain/'.$uid.'/'));
			}
		} else {
			$arrData = array();
			$arrData['message'] = '';
		}

		return $arrData;
	}

	protected function validatePackagesUpdate($arrPackages=array()) {
		$arrData = $_POST;
		$arrErrors = array();

		$error = false;

		if(count($arrData) > 0) {
			$options['yes'] = array();
			$options['no'] = array();
			foreach($arrData as $key=>$val) {
				if($key !== 'product_uid' && $key !== 'submit') {
					list($field,$uid) = explode('_',$key);
					$val = ($val==1 ? 'yes' : 'no');
					$options[$val][] = $uid;
				}
			}

			$query = "DELETE FROM `product_package` WHERE `product_uid` = ".$arrData['product_uid'];

			$result = database::query($query);

			if(count($options['yes']) > 0) {
				$query = "INSERT INTO `product_package` (`product_uid`, `sub_package_uid`) VALUES ";
				$parts = array();
				foreach($options['yes'] as $package_uid) {
					$parts[] = "(".$arrData['product_uid'].", ".$package_uid.")";
				}
				$query.= implode(', ',$parts);

				$result = database::query($query);
			}

			$_SESSION['message'] = 'Packages Updated';
			output::redirect(config::admin_uri('products/edit/'.$arrData['product_uid'].'/packages/'));

		} else {
			$arrData = $arrPackages;
		}

		return $arrData;
	}
}

?>