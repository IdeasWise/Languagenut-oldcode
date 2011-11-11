<?php

/**
 * tabs.php
 */

class tabs {
	public function __construct () {
	}

	public function get_tabs_and_contents($index=null,$tableName=null,$tabName=null,$XHTML=null)
	{
		if($index== null || $tableName==null || $tabName==null || $XHTML==null ) {
			return array('none'=>'');
		}

		$arrResult = array();
		$arrLanguage = array();
		if(isset($_POST['form_submit_button'])){
			if(isset($_POST['table_name']) && $tableName == $_POST['table_name']){
				$className = trim($_POST['table_name']) ;
				if(!empty($className)){
					$objClass = new $className();
					if($objClass->doSave()){
						output::redirect($_SERVER['REQUEST_URI']); // redirect
					} else{
						$arrResult= $objClass->arrForm;
						// if error occured.
					}
				}
			}
		}


		if(isset($_POST['form_submit_language'])){
			if(isset($_POST['locale']) && !empty($_POST['locale'])){
				$query ="INSERT INTO ";
				$query.=mysql_real_escape_string($_POST['table_name']). " ";
				$query.="SET ";
				$query.="locale = '".mysql_real_escape_string($_POST['locale'])."'";
				database::query($query);
				output::redirect($_SERVER['REQUEST_URI']); // redirect
			} else{
				$arrLanguage['message_error'] = '<p>Please correct the errors below:</p><ul><li>Please choose one laguage to create page.</li></ul>';
			}
		}



		$body = make::tpl('body.admin.tabs');

		$query  = "SELECT ";
		$query .= "`TB`.*, ";
		$query .= "`LG`.`name` AS `LangName` ";
		$query .= "FROM ";
		$query .= "`".mysql_real_escape_string($tableName)."` AS `TB`, ";
		$query .= "`language` AS `LG` ";
		$query .= "WHERE ";
		$query .= "`LG`.`prefix`  = `TB`.`locale` ";
		$query .= "GROUP BY ";
		$query .= "`TB`.`uid` ";
		$query .= "ORDER BY ";
		$query .= "`LG`.`name` ";

		$result = database::query($query);


		$arrTabs = array();
		$arrTabs_li = array();
		$arrTabsDiv = array();


		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			while($row=mysql_fetch_assoc($result)) {
				$panel = make::tpl($XHTML);
				if(isset($arrResult['uid']) && $arrResult['uid'] == $row['uid']){
					foreach( $arrResult as $idx => $val ) {
						$row[$idx] = $val;
					}
				}

				foreach( $row as $idx => $val ) {
					$row[$idx.'.id'] = $row['locale'].$idx.'.id'.$tabName;
				}

				$row['cms.id']		= $row['locale'].'.cms'.$tabName;
				$row['table_name']	= $tableName;
				$row['tabName']		= $tabName;
				$row['action']		= $_SERVER['REQUEST_URI'];



				$panel->assign($row);

				$arrTabs_li[] = '<li><a href="#subTab-'.$tabName.'-'.$row['uid'].'"><span>'.$row['LangName'].'</span></a></li>';
				$arrTabsDiv[] = '<div id="subTab-'.$tabName.'-'.$row['uid'].'">'.$panel->get_content().'</div>';
			}
		}


		$query ="SELECT ";
		$query.="`prefix`, ";
		$query.="`name` ";
		$query.="FROM ";
		$query.="`language` ";
		$query.="WHERE ";
		$query.="`prefix` NOT IN ( ";
		$query.="SELECT ";
		$query.="`locale` ";
		$query.="FROM ";
		$query.= mysql_real_escape_string($tableName);
		$query.=" ) ";
		$query.="GROUP BY ";
		$query.="`prefix` ";
		$query.="ORDER BY `name`";
		$result = database::query($query);
		$data = array();
		$data[''] = 'Language';
		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			while($row=mysql_fetch_assoc($result)) {
				$data[$row['prefix']] = $row['name'];
			}
		}
		if(count($data) > 1) {
			$row = array();
			$panel = make::tpl('body.admin.page.tab.add.language');

			$row['table_name'] = $tableName;
			$row['action'] = $_SERVER['REQUEST_URI'];
			$row['locale'] = format::to_select(array("name" => 'locale',"id" => 'locale',"options_only" => false), $data , NULL);
			$panel->assign($row);
			$panel->assign($arrLanguage);

			$arrTabs_li[] = '<li><a href="#subTab-'.$tabName.'-language"><span>Add New</span></a></li>';
			$arrTabsDiv[] = '<div id="subTab-'.$tabName.'-language">'.$panel->get_content().'</div>';

		}


		$body->assign(
					array(
						'tabs.lis' => implode('',$arrTabs_li),
						'tabs.divs' => implode('',$arrTabsDiv)
						)
					);
		return array($index => $body->get_content());

	}


	public function get_tabs_and_contents_of_pricing( )
	{
		$index = 'body';
		$tableName = 'language';
		$tabName = 'lang';
		$XHTML = 'body.admin.pricing.form';

		$arrResult= array();
		$arrLanguage = array();
		if(isset($_POST['form_submit_button'])) {
			if(isset($_POST['table_name']) && $tableName == $_POST['table_name']){
				$className = trim($_POST['table_name']) ;
				if(!empty($className)){
					$objClass = new $className();
					if($objClass->doSavePricing()){
							output::redirect($_SERVER['REQUEST_URI']);
					} else {
						$arrResult= $objClass->arrForm;
					}
				}
			}
		}


		$body = make::tpl('body.admin.tabs');

		$query ="SELECT ";
		$query.="`TB`.`uid`, ";
		$query.="`TB`.`currency_uid`, ";
		$query.="`TB`.`home_user_price`, ";
		$query.="`TB`.`school_price`, ";
		$query.="`LG`.`prefix` AS `LangName`, ";
		$query.="`LG`.`prefix` AS `locale`, ";
		$query.="`TB`.`vat` ";
		$query.="FROM ";
		$query.=mysql_real_escape_string($tableName)." AS `TB`, ";
		$query.="`language` AS `LG` ";
		$query.="WHERE ";
		$query.="`LG`.`uid`= `TB`.`uid` ";
		$query.="ORDER BY LG.prefix";
		
		$result = database::query($query);
		$arrTabs = array();
		$arrTabs_li = array();
		$arrTabsDiv = array();


		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			while($row=mysql_fetch_assoc($result)) {
				$panel = make::tpl($XHTML);
				if(isset($arrResult['uid']) && $arrResult['uid'] == $row['uid']){
					foreach( $arrResult as $idx => $val ) {
						$row[$idx] = $val;
					}
				}

				$row['table_name']	= $tableName;
				$row['tabName']		= $tabName;
				$row['action']		= $_SERVER['REQUEST_URI'].'#'.$tabName.'-'.$row['uid'];

				
				$objCurrency  = new currencies();
				$row['currency_uid'] = $objCurrency ->CurrencySelectBox(
																	'currency_uid',
																	$row['currency_uid'],
																	'currency_uid-'.$row['locale']
																	);

				$panel->assign($row);

				$arrTabs_li[] = '<li><a href="#'.$tabName.'-'.$row['uid'].'"><span>'.$row['LangName'].'</span></a></li>';
				$arrTabsDiv[] = '<div id="'.$tabName.'-'.$row['uid'].'">'.$panel->get_content().'</div>';
			}
		}


		$body->assign(
				array(
				'tabs.lis' => implode('',$arrTabs_li),
				'tabs.divs' => implode('',$arrTabsDiv)
					)
			);
		 return array($index => $body->get_content());
	}


	public function get_tabs_and_contents_for_certificate(){
		$arrResult		= array();
		$arrLanguage	= array();
		$body			= make::tpl('body.admin.tabs');

		$query ="SELECT ";
		$query.="`uid`, ";
		$query.="`logo_url`, ";
		$query.="`gold_bg`, ";
		$query.="`silver_bg`, ";
		$query.="`bronze_bg`, ";
		$query.="`prefix`, ";
		$query.="`prefix` AS `locale`, ";
		$query.="`name` ";
		$query.="FROM ";
		$query.="`language` ";
		$query.="ORDER BY `prefix`";

		$result		= database::query($query);
		$arrTabs	= array();
		$arrTabs_li	= array();
		$arrTabsDiv	= array();


		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			while($row=mysql_fetch_assoc($result)) {
				$language =  new language();
				if(isset($_POST['form_language_uploads'])) {
					if( $language->doSaveImages() === false ){
						$row['message'] = $language->arrForm['message_error'];
					}
				}
			
				$xhtml				= make::tpl('body.admin.language.certificate');
				$row['form.action']	= $_SERVER['REQUEST_URI'].'#tab-'.$row['uid'];
				$row['logo_image']	='';
				$row['gold_image']	='';
				$row['silver_image']='';
				$row['bronze_image']='';

				if(!empty($row['logo_url'])) {
					$row['logo_image'] = '(<a href="'.config::images_common('certificate/'.$row['logo_url']).'"  target="_blank">View Image</a>)';
				}
				if(!empty($row['gold_bg'])) {
					$row['gold_image'] = '(<a href="'.config::images_common('certificate/'.$row['gold_bg']).'"  target="_blank">View Image</a>)';
				}
				if(!empty($row['silver_bg'])) {
					$row['silver_image'] = '(<a href="'.config::images_common('certificate/'.$row['silver_bg']).'"  target="_blank">View Image</a>)';
				}
				if(!empty($row['bronze_bg'])) {
					$row['bronze_image'] = '(<a href="'.config::images_common('certificate/'.$row['bronze_bg']).'"  target="_blank">View Image</a>)';
				}
				
				$xhtml->assign($row);

				$arrTabs_li[] = '<li><a href="#tab-'.$row['uid'].'"><span>'.$row['prefix'].'</span></a></li>';
				$arrTabsDiv[] = '<div id="tab-'.$row['uid'].'">'.$xhtml->get_content().'</div>';
			}
		}

		$body->assign(
				array(
					'tabs.lis' => implode('',$arrTabs_li),
					'tabs.divs' => implode('',$arrTabsDiv)
					)
				);
		 return $body->get_content();

	}


	public function get_tabs_and_contents_for_certificate_messages( ) {

		if(isset($_POST['form_cerrtificate_message'])) {
			foreach( $_POST['input'] as $index => $value ) {
				$WHERE =" WHERE ";
				$WHERE.="`locale` = '".mysql_real_escape_string($_POST['locale'])."' ";
				$WHERE.="AND ";
				$WHERE.="`message_uid` = '".mysql_real_escape_string($index)."' ";

				$CheckQuery ="SELECT ";
				$CheckQuery.="`uid` ";
				$CheckQuery.="FROM ";
				$CheckQuery.="`certificate_messages_translations` ";
				$CheckQuery.=$WHERE ;
				$CheckQuery.="LIMIT 1 ";
				
				$check = database::query($CheckQuery);
				if(mysql_num_rows($check) > 0) {
					$query ="UPDATE ";
					$query.="`certificate_messages_translations` ";
					$query.="SET ";
					$query.="`text` = '".addslashes(mysql_real_escape_string($value))."' ";
					$query.=" ".$WHERE;
					$query.=" LIMIT 1 ";
					database::query($query);
				} else {
					$query ="INSERT INTO ";
					$query.="`certificate_messages_translations` ";
					$query.="( ";
						$query.="`message_uid`, ";
						$query.="`locale`, ";
						$query.="`text` ";
					$query.=") VALUES ( ";
						$query.="'".mysql_real_escape_string($index)."', ";
						$query.="'".mysql_real_escape_string($_POST['locale'])."', ";
						$query.="'".addslashes(mysql_real_escape_string($value))."' ";
					$query.=")";
					database::query($query);
				}
				
			}
		}

		$body =make::tpl('body.admin.tabs');

		$query ='SELECT ';
		$query.='`uid`, ';
		$query.='`prefix`, ';
		$query.='`name` ';
		$query.='FROM ';
		$query.='`language` ';
		$query.='ORDER BY `prefix`';
		$result = database::query($query);
		$arrTabs = array();
		$arrTabs_li = array();
		$arrTabsDiv = array();


		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			while($row=mysql_fetch_assoc($result)) {
				$xhtml = make::tpl('body.admin.certificate.messages.form');
				$row['form.action'] = $_SERVER['REQUEST_URI'].'#tab-msg-'.$row['uid'];
			
				$query ="SELECT ";
				$query.="`CM`.*, ";
				$query.="( ";
					$query.="SELECT ";
					$query.="`text` ";
					$query.="FROM ";
					$query.="`certificate_messages_translations` as `CMT` ";
					$query.="WHERE ";
					$query.="`CMT`.`message_uid` = `CM`.`uid` ";
					$query.="AND ";
					$query.="`CMT`.`locale` = '".$row['prefix']."' ";
					$query.="LIMIT 1 ";
				$query.=") AS `text` ";
				$query.="FROM ";
				$query.="`certificate_messages` as `CM` ";
				$arrTranslations = array();
				$arrTranslations = database::arrQuery($query);

				$arrForm = array();

				foreach( $arrTranslations as $index => $data  ) { 
					$field = make::tpl('body.admin.certificate.messages.form.element');
					$data['text']  = stripslashes($data['text']);
					$data['field.name'] = $data['uid'];
					$data['field.id'] = $data['uid'] . '_' . $row['prefix'];
					$field->assign($data);
					$arrForm[] = $field->get_content();
				}
				if(count($arrForm) > 0) {
					$row['form.elements'] = implode('', $arrForm);
				}
				$xhtml->assign($row);

				$arrTabs_li[] = '<li><a href="#tab-msg-'.$row['uid'].'"><span>'.$row['prefix'].'</span></a></li>';
				$arrTabsDiv[] = '<div id="tab-msg-'.$row['uid'].'">'.$xhtml->get_content().'</div>';
			}
		}

		$body->assign(
				array(
					'tabs.lis' => implode('',$arrTabs_li),
					'tabs.divs' => implode('',$arrTabsDiv)
					)
				);
		return $body->get_content();
	}


	public function get_tabs_and_contents_for_certificate_fontsize( ) {
		if( isset($_POST['form_cerrtificate_fontsize']) ) {
			$_POST['gold_size']		= (is_numeric($_POST['gold_size'])	? $_POST['gold_size'] : '0');
			$_POST['silver_size']	= (is_numeric($_POST['silver_size'])? $_POST['silver_size'] : '0');
			$_POST['bronze_size']	= (is_numeric($_POST['bronze_size'])? $_POST['bronze_size'] : '0');

			$WHERE = " WHERE `locale` = '".mysql_real_escape_string($_POST['locale'])."'";
			$query = "SELECT `uid` from `certificate_font_size` ".$WHERE ;
			$check = database::query($query);
			if(mysql_num_rows($check) > 0) {
				$query ="UPDATE ";
				$query.="`certificate_font_size` ";
				$query.="SET ";
				$query.="`gold_size` = '".addslashes(mysql_real_escape_string($_POST['gold_size']))."', ";
				$query.="`silver_size` = '".addslashes(mysql_real_escape_string($_POST['silver_size']))."', ";
				$query.="
				`bronze_size` = '".addslashes(mysql_real_escape_string($_POST['bronze_size']))."'";
				$query.=$WHERE;
				database::query($query);
			} else {
				$query ="INSERT INTO ";
				$query.="`certificate_font_size` ( ";
					$query.="`gold_size`, ";
					$query.="`locale`, ";
					$query.="`silver_size`, ";
					$query.="`bronze_size` ";
				$query.=") VALUES ( ";
					$query.="'".mysql_real_escape_string($_POST['gold_size'])."', ";
					$query.="'".mysql_real_escape_string($_POST['locale'])."', ";
					$query.="'".addslashes(mysql_real_escape_string($_POST['silver_size']))."', ";
					$query.="'".addslashes(mysql_real_escape_string($_POST['bronze_size']))."' ";
				$query.=")";
				database::query($query);
			}
		}

		$body = make::tpl('body.admin.tabs');		
		$query ='SELECT ';
		$query.='`uid`, ';
		$query.='`prefix`, ';
		$query.='( ';
			$query.='SELECT ';
			$query.='concat(`gold_size`,"-",`silver_size`,"-",`bronze_size`) ';
			$query.='FROM ';
			$query.='`certificate_font_size` ';
			$query.='WHERE ';
			$query.='`locale` = `prefix` ';
			$query.='LIMIT 1 ';
		$query.=' ) AS `sizes` ';
		$query.='FROM ';
		$query.='`language` ';
		$query.='ORDER BY `prefix`';
		$result = database::query($query);
		$arrTabs = array();
		$arrTabs_li = array();
		$arrTabsDiv = array();


		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			while($row=mysql_fetch_assoc($result)) {
				$xhtml				= make::tpl('body.admin.certificate.font.size.form');
				$row['form.action']	= $_SERVER['REQUEST_URI'].'#main-3';			
				$row['gold_size']	= '';
				$row['silver_size']	= '';
				$row['bronze_size']	= '';
				if(!is_null($row['sizes'])) {
					list($row['gold_size'], $row['silver_size'], $row['bronze_size']) = explode('-',$row['sizes']);
				}
				$xhtml->assign($row);
				$arrTabs_li[] = '<li><a href="#tab-fonts-size-'.$row['uid'].'"><span>'.$row['prefix'].'</span></a></li>';
				$arrTabsDiv[] = '<div id="tab-fonts-size-'.$row['uid'].'">'.$xhtml->get_content().'</div>';
			}
		}

		$body->assign(
				array(
					'tabs.lis' => implode('',$arrTabs_li),
					'tabs.divs' => implode('',$arrTabsDiv)
					)
			);
		return $body->get_content();
	}

}

?>