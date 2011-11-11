<?php

class plugin_address_details extends plugin {

	private $user_address				=	null;	
	private $lib_property_address_uk	=	null;

	public function get_address_uid() {
		return isset($this->data['address_uid'])?$this->data['address_uid']:0;
	}

	public function get_address_list() {
		return isset($this->data['address_list'])?$this->data['address_list']:"";
	}

	public function get_class_name() {
		return __CLASS__;
	}

	public function __construct($data = array()) {

		$this->user_address			 =	new user_address();
		$this->lib_property_address_uk  =	new lib_property_address_uk();
		$this->data					 =	$data;
		// load skeleton according to form if set
		//$this->body		 =	new xhtml ('body.account.address.details');
		//$this->body->load();
                $this->body		 =	new xhtml ('body.account.factfind.tab.address');
		$this->body->load();
	}

	public function run() {

                // user address list
		//$this->processAddressList();

                // user address
		$this->processAddress();		         
                
		return $this->body;
	}

	protected function processAddress() {

		$user_address_uid				= 0;
		$check_is_submit				= false;
		$check_is_submit				= $this->checkSubmit();
                

                if(isset($_POST['submit-use-address']) && $_POST['uid'] > 0 && $_POST['address_uid'] > 0){
                  $user_address = new user_address();
                    $this->data['address_aid'] = $_POST['address_uid'];
                             $user_address->set_address_id($this->data);
                             @ob_start();
                             header('location:'.$_SERVER['HTTP_REFERER']);
                             exit;
                }
		/*
		if($check_is_submit) {
			$update			 =	false;
			$user_address		=	new user_address();
			if(isset($_POST['user-address-uid']) && is_numeric($_POST['user-address-uid']) && $_POST['user-address-uid'] > 0) {
				$update = true;
			}
			if(($response = $user_address->isValidData($update,$this->data['user_uid'])) === true) {
				if($update && $user_address->isUpdateSuccessful()) {
					$this->data['address_uid'] = $_POST['user-address-uid'];
					if(!isset($this->data['redirect']) || $this->data['redirect'] == false) {
						output::redirect(config::get("request"));
					}
				}
				else if(!$update && ($insert_id = $user_address->isCreateSuccessful()) !== false) {
					$this->data['address_uid'] = $insert_id;
					if(!isset($this->data['redirect']) || $this->data['redirect'] == true) {
						output::redirect(config::get("request"));
					}
				}
			}
			else {
				$this->parseResponse($response);
			}
		}

                 *
                 */

                
                if($check_is_submit) {
                    $update			 =	false;
                    
                    
                    $user_address =	new user_address();
                    if( $user_address->doSave() ){
                        if( trim($_POST['user-address-uid']) == ''){                           
                             $this->data['address_aid'] = $user_address->changed_array['user-address-uid'];
                             $user_address->set_address_id($this->data);
                             @ob_start();
                             header('location:'.$_SERVER['HTTP_REFERER']);
                             exit;
                        }
                    }
                    else{                        
                       
                    }
                    
                        // $body->assign( $profileSave->changed_array );
                }
		else if(isset($_POST['action']) && strtolower($_POST['action']) == "delete") {
			$user_address_uid	=	(isset($_POST['user_address_uid']))?format::to_integer($_POST['user_address_uid']):0;

			if(is_numeric($user_address_uid) && $user_address_uid > 0) {
				$user_address	=	new user_address($user_address_uid);
				if($user_address->get_valid()) {
					$user_address->load();
					$user_address->delete();
					output::redirect(config::get("request"));
				}
			}
		}
		else if( isset($_POST['action']) && strtolower($_POST['action']) == "edit") {
			$user_address_uid									=	(isset($_POST['user_address_uid']))?format::to_integer($_POST['user_address_uid']):0;

			if(is_numeric($user_address_uid) && $user_address_uid > 0) {
				$this->user_address								=	new user_address($user_address_uid);
				if($this->user_address->get_valid()) {
					$this->user_address->load();
					$this->lib_property_address_uk				=	new lib_property_address_uk($this->user_address->get_address_uid());
					$this->lib_property_address_uk->load();
					$this->data['lib_property_address_uk_uid']	=	$this->user_address->get_address_uid();
					$this->data['address_uid']					=	$this->user_address->get_uid();					
				}
			}
		}
                if($this->data['address_id'] > 0){
                    
                            $this->lib_property_address_uk	=	new lib_property_address_uk($this->data['address_id']);
                            $this->lib_property_address_uk->load();                   
                }
		$this->processTabAddress();
		if(count(@$user_address->changed_array) > 0){
                    $this->body->assign($user_address->changed_array);
                }
               // $this->body = $data;
		$this->body->assign(
			array(				  
				  "user_address_uid"	=>	$this->user_address->get_uid(),
                                  "uid"	=>	@$this->data['profile_uid'],
                                  'address-frm-action' =>   $_SERVER['REQUEST_URI'].'#tab-1'
			)
		);
                
              if( isset($_POST['submit-search-address']) && !empty($_POST['keyword']) )
                        $this->doSearch();
              else{
                  $this->body->assign( array("address.div" => 'display:none;')  ) ;
              }


	}

	protected function processTabAddress() {
                
		//$body	=	new xhtml("body.account.factfind.tab.address");
		//$body->load();

		// get country
		$country			=	new lib_country();
		$countries			=	array();
		$countries			=	$country->get_country(0, true);
		$select_countries	=	array();
		$select_countries[0] = 'select address';
		if(!empty ($countries)) {
			foreach($countries as $uid => $data) {
				$select_countries[$data['uid']]  =	$data['common_name'];
			}
		}	
		

		$this->body->assign(
				array(
				// text boxes
                                "uid"                                           =>	@$this->data['profile_uid'],
				"user_address_uid"				=> $this->lib_property_address_uk->get_uid(),
				"flat_number"					=> $this->lib_property_address_uk->get_flat_number(),
				"number"						=> $this->lib_property_address_uk->get_number(),
				"name"							=> $this->lib_property_address_uk->get_name(),
				"street_name_1"					=> $this->lib_property_address_uk->get_street_name_1(),
				"street_name_2"					=> $this->lib_property_address_uk->get_street_name_2(),
				"district"						=> $this->lib_property_address_uk->get_district(),
				"town"							=> $this->lib_property_address_uk->get_town(),
				"city"							=> $this->lib_property_address_uk->get_city(),
				"county"						=> $this->lib_property_address_uk->get_county(),
				"postcode"						=> $this->lib_property_address_uk->get_postcode(),
				//select boxes
				"country_uid"					=> format::to_select(array("name" => "country_uid"			  ,"id" => "country_uid"			  ,"options_only" => false), $select_countries,$this->lib_property_address_uk->get_country_uid()),                                
                                'address-frm-action' =>   $_SERVER['REQUEST_URI'].'#tab-1'                                
				)
		);

		//return $body;
	}	
        protected function doSearch()
        {
            $user_address				=	new user_address();
            $user_addresses			 =	array();
            $user_addresses			 =	$user_address->FindAddresses();
            $rows						=	array();
            if(!empty ($user_addresses)) {
                    foreach($user_addresses as $uid => $data) {
                        /*
                        if(!isset($this->data['post_form'])) { echo 'yes';
                                    $innerPanel		 =	new xhtml ('body.account.edit.profile.user.address');
                                    $innerPanel->load();
                            }
                            else {
                                echo 'false';
                         *
                         */
                                    $innerPanel		 =	new xhtml ('plugin.address.search.list');
                                    $innerPanel->load();
                                   
                                    $checked = true;
                                    $innerPanel->assign("select_box",format::to_radio(array("name" => "address_uid","id" => "address_uid_".$data['uid'],"value" => $data['uid']),$checked));
                                    $innerPanel->assign($data);
                          /*  }
                           * 
                           */


                          
                            $rows[] =	$innerPanel->get_content();
                    }
            }

            if(count($rows) > 0){

            $page_display_title     =   $user_address->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
            $page_navigation        =   $user_address->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$user_address->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$user_address->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

            $this->body->assign('page.display.title'  ,   $page_display_title);
            $this->body->assign('page.navigation'     ,   $page_navigation);
            $array = array(
                            "user.address.list" => implode("",$rows),
                            "result.button" => 'display:block;',
                            "address.div" => 'display:block;'
                            
                          );
            }
            else{
                $array = array(
                            "user.address.list" => 'No Result found',
                            "result.button" => 'display:none;',
                            "address.div" => 'display:block;'
                          );
            }

           // $this->data['address_list'] = implode("",$rows);
            $this->body->assign( $array  ) ;
            
        }
	protected function checkSubmit() {
		return ((isset($this->data['post_form']) && $this->data['post_form'] == true) || (isset($_POST['form']) && $_POST['form'] == "add-update-user-address"));
	}

}
?>