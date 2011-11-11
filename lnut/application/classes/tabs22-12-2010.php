<?php

/**
 *
 */

class tabs {
	public function __construct () {
	}

        public function get_tabs_and_contents( $index , $tableName, $tabName, $XHTML )
        {
           
            $resArray = array();
            $LangArray = array();
            if(isset($_POST['form_submit_button'])){
                if($tableName == @$_POST['table_name']){

                    $ActionClass = trim($_POST['table_name']) ;
                    if(!empty($ActionClass)){
                        $SaveObject = new $ActionClass();
                        if($SaveObject->doSave()){
                                //$SaveObject->redirectTo($_SERVER['HTTP_REFERER']); // redirect
                                @ob_start();
                                header('location:'.@$_SERVER['REQUEST_URI']);
                                exit;
                        }
                        else{
                            $resArray = $SaveObject->arrForm;
                            // if error occured.
                        }
                    }

                }
            }


            if( isset($_POST['form_submit_language']) ){

                if(!empty($_POST['locale'])){
                    $sql = "INSERT INTO ". $_POST['table_name'] . "
                            SET locale = '".$_POST['locale']."'";
                    database::query($sql);
                    @ob_start();
                    header('location:'.@$_SERVER['REQUEST_URI']);
                    exit;
                }
                else{
                    $LangArray['message_error'] = '<p>Please correct the errors below:</p><ul><li>Please choose one laguage to create page.</li></ul>';
                }
                    
            }

            $body       = new xhtml('body.admin.tabs');
            $body->load();

            $sql = "SELECT TB.*, LG.name as LangName FROM ".$tableName." TB, language LG where LG.prefix  = TB.locale GROUP BY TB.uid ORDER BY LG.name";
            $result = database::query($sql);
            $tabs = array();
            $tabs_li = array();
            $tabs_divs = array();


            if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
                while($row=mysql_fetch_assoc($result)) {
                    $panel = new xhtml($XHTML);
                    $panel->load();

                     if(@$resArray['uid'] == $row['uid']){
                        foreach( $resArray as $idx => $val )
                            $row[$idx] = $val;
                    }

                    foreach( $row as $idx => $val )
                        $row[$idx.'.id'] = $row['locale'].$idx.'.id'.$tabName;

                    $row['cms.id'] = $row['locale'].'.cms'.$tabName;
                    $row['table_name'] = $tableName;
                    $row['tabName'] = $tabName;
                    $row['action'] = $_SERVER['REQUEST_URI'];



                    $panel->assign($row);

                    $tabs_li[] = '<li><a href="#subTab-'.$tabName.'-'.$row['uid'].'"><span>'.$row['LangName'].'</span></a></li>';
                    $tabs_divs[] = '<div id="subTab-'.$tabName.'-'.$row['uid'].'">'.$panel->get_content().'</div>';
                }
            }



            $sql = "SELECT prefix, name FROM language WHERE prefix NOT IN ( SELECT locale FROM ".$tableName.") GROUP BY prefix ORDER BY name";
            $result = database::query($sql);
            $data = array();
            $data[''] = 'Language';
            if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
                while($row=mysql_fetch_assoc($result)) {
                    $data[$row['prefix']] = $row['name'];
                }
            }
            if(count($data) > 1){
                $row = array();
                $panel = new xhtml('body.admin.page.tab.add.language');
                $panel->load();

                $row['table_name'] = $tableName;
                $row['action'] = $_SERVER['REQUEST_URI'];
                $row['locale'] = format::to_select(array("name" => 'locale',"id" => 'locale',"options_only" => false), $data , NULL);
                $panel->assign($row);
                $panel->assign($LangArray);

                $tabs_li[] = '<li><a href="#subTab-'.$tabName.'-language"><span>Add New</span></a></li>';
                $tabs_divs[] = '<div id="subTab-'.$tabName.'-language">'.$panel->get_content().'</div>';
                

            }
            


             $body->assign(
                            array(
                                    'tabs.lis' => implode('',$tabs_li),
                                    'tabs.divs' => implode('',$tabs_divs)
                                )
                        );
             return array( $index => $body->get_content()  );
            //return array( $index => $panel->get_content()  );
        }





        public function get_tabs_and_contents_of_pricing(  )
        {
             $index = 'body';
             $tableName = 'language';
             $tabName = 'lang';
             $XHTML = 'body.admin.pricing.form';
                                                  
            $resArray = array();
            $LangArray = array();
            if(isset($_POST['form_submit_button'])){
                
                if($tableName == @$_POST['table_name']){

                    $ActionClass = trim($_POST['table_name']) ;
                    if(!empty($ActionClass)){
                        $SaveObject = new $ActionClass();
                        if($SaveObject->doSavePricing()){
                                //$SaveObject->redirectTo($_SERVER['HTTP_REFERER']); // redirect
                                @ob_start();
                                header('location:'.@$_SERVER['REQUEST_URI']);
                                exit;
                        }
                        else{
                            $resArray = $SaveObject->arrForm;
                            // if error occured.
                        }
                    }

                }
            }


           

            $body       = new xhtml('body.admin.tabs');
            $body->load();

            $sql = "SELECT TB.uid, TB.currency_uid, TB.home_user_price, TB.school_price, LG.prefix as LangName, LG.prefix as locale, TB.vat FROM ".$tableName." TB, language LG WHERE LG.uid= TB.uid ORDER BY LG.prefix";
            
            $result = database::query($sql);
            $tabs = array();
            $tabs_li = array();
            $tabs_divs = array();


            if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
                while($row=mysql_fetch_assoc($result)) {
                    $panel = new xhtml($XHTML);
                    $panel->load();

                     if(@$resArray['uid'] == $row['uid']){
                        foreach( $resArray as $idx => $val )
                            $row[$idx] = $val;
                    }

                    
                    
                    $row['table_name'] = $tableName;
                    $row['tabName'] = $tabName;
                    $row['action'] = $_SERVER['REQUEST_URI'].'#'.$tabName.'-'.$row['uid'];

                    
                    $currencyObj = new currencies();
                    $row['currency_uid'] = $currencyObj->CurrencySelectBox('currency_uid',  $row['currency_uid'], 'currency_uid-'.$row['locale']);

                    $panel->assign($row);

                    $tabs_li[] = '<li><a href="#'.$tabName.'-'.$row['uid'].'"><span>'.$row['LangName'].'</span></a></li>';
                    $tabs_divs[] = '<div id="'.$tabName.'-'.$row['uid'].'">'.$panel->get_content().'</div>';
                }
            }



          
            
             $body->assign(
                            array(
                                    'tabs.lis' => implode('',$tabs_li),
                                    'tabs.divs' => implode('',$tabs_divs)
                                )
                        );
             return array( $index => $body->get_content()  );
            //return array( $index => $panel->get_content()  );
        }

}
?>