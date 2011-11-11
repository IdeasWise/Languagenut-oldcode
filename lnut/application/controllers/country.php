<?php

class country extends Controller {

    private $selected           = '';
    private $selected_action    = "";

    private $pages	= array (
            'country-action'
    );
    private $action	= array (
            'country-update',
            'get-country-data',
            'delete-country',
            'delete-multiple-country'
    );

    public function __construct () {
        parent::__construct();


        $parts = config::get('paths');
        if(isset($parts[3]) && in_array($parts[3], $this->pages)) {
            $this->selected	 = $parts[3];
        }
        switch($this->selected) {
            case "country-action":
                if($_POST > 0 && isset($_POST['action']) && in_array($_POST['action'], $this->action)) {
                    $this->selected_action = $_POST['action'];
                }
                switch($this->selected_action) {
                    case "country-update":
                        $this->add_update_country();
                        break;
                    case "get-country-data":
                        $this->get_country();
                        break;
                    case "delete-country":
                        $this->delete_country();
                        break;
                    case "delete-multiple-country":
                        $this->delete_multiple_country();
                        break;
                    default:
                        $this->country_list();
                        break;
                }
                break;
            default:
                $this->country_list();
                break;
        }
    }

    protected function country_list() {
        $skeleton                       = new xhtml ('skeleton.admin');
        $skeleton->load();

        $body				= new xhtml('body.admin.library.country');
        $body->load();

        $data                           = component_country::generate_record_list();
        $body->assign("page.list", $data);

        // country type dropdown
        $lib_country_type       =   new lib_country_type();
        $options                =   array();
        $country_types          =   array();
        $country_types          =   $lib_country_type->get_country_type('',true);
        if(!empty($country_types)) {
            foreach($country_types as $uid => $data) {
                $options[$data['uid']] = $data['name'];
            }
        }

        $body->assign("type_uid"   ,   format::to_select(array("name" => "type_uid","id" => "type_uid","options_only" => false), $options));
        
        // sub country type dropdown
        $lib_country_sub_type   =   new lib_country_sub_type();
        $options                =   array();
        $country_sub_types      =   array();
        $country_sub_types      =   $lib_country_sub_type->get_country_sub_type('',true);
        if(!empty($country_sub_types)) {
            foreach($country_sub_types as $uid => $data) {
                $options[$data['uid']] = $data['name'];
            }
        }

        $body->assign("sub_type_uid"   ,   format::to_select(array("name" => "sub_type_uid","id" => "sub_type_uid","options_only" => false), $options));

        $skeleton->assign (
                array (
                'body'				=> $body
                )
        );
        output::as_html($skeleton,true);
    }

    protected function add_update_country() {
        if(count($_POST) > 0) {
            $country_uid   =   (isset($_POST['country_uid']) ? format::to_integer($_POST['country_uid']) : 0);
            $lib_country   =   new lib_country();

            if(is_numeric($country_uid) && $country_uid > 0) {
                $lib_country->isUpdateSuccessFul();
            }
            else {
                $lib_country->isCreateSuccessFul();
            }
        }
    }

    protected function get_country() {
        if(count($_POST) > 0) {
            $error                      =   1;
            $data                       =   array();
            $country_uid                =   (isset($_POST['country_uid'])? format::to_integer($_POST['country_uid']) : '');
            $skeleton                   =   new xhtml ('xml.country.get.data');
            $skeleton->load();

            if(is_numeric($country_uid) && $country_uid > 0) {
                $lib_country   =   new lib_country($country_uid);
                $lib_country->load();
                $data = array(
                        "common_name"               => $lib_country->get_common_name(),
                        "formal_name"               => $lib_country->get_formal_name(),
                        "type_uid"                  => $lib_country->get_type_uid(),
                        "sub_type_uid"              => $lib_country->get_sub_type_uid(),
                        "sovereignty"               => $lib_country->get_sovereignty(),
                        "capital"                   => $lib_country->get_capital(),
                        "iso_4217_currency_code"    => $lib_country->get_iso_4217_currency_code(),
                        "iso_4217_currency_name"    => $lib_country->get_iso_4217_currency_name(),
                        "itu_t_telephone_code"      => $lib_country->get_itu_t_telephone_code(),
                        "iso_3166_1_2_letter_code"  => $lib_country->get_iso_3166_1_2_letter_code(),
                        "iso_3166_1_3_letter_code"  => $lib_country->get_iso_3166_1_3_letter_code(),
                        "iso_3166_1_number"         => $lib_country->get_iso_3166_1_number(),
                        "iana_country_code_tld"     => $lib_country->get_iana_country_code_tld(),
                        "uid"                       => $lib_country->get_uid(),
                        "active"                    => $lib_country->get_active(),
                        "error"                     => "0"
                );
            }
            else {
                $data = array(
                        "error"         => "1"
                );
            }
            $skeleton->assign($data);
            output::as_xml($skeleton,true);
        }
    }

    protected function delete_country() {
        $success                   =   false;
        $error                     =   false;
        $message                   =   array();
        $country_uid               =   (isset($_POST['country_uid'])? format::to_integer($_POST['country_uid']) : 0);
        $page_id                   =   (isset($_POST['page_id'])? format::to_integer($_POST['page_id']) : '');
        $output                    =   "";
        if(is_numeric($country_uid) && $country_uid > 0) {
            $lib_country   =   new lib_country($country_uid);
            if($lib_country->get_valid()) {
                $lib_country->delete();
                $message[]  =   "Country Deleted Successfully";
            }
        }
        else {
            $error              =   true;
            $message[]          =   "Country not found";
        }
        $message_type           =   ($error == true)?"error":"success";
        component_response::htmlSuccessFailureResponse($country_uid,$message_type,$message);
    }

    protected function delete_multiple_country() {
        $success		=   false;
        $error                  =   false;
        $message                =   array();
        $uids                   =   (isset($_POST['uids'])? format::to_string($_POST['uids']) : 0);
        $output                 =   "";
        $uid_array              =   array();
        if(strlen($uids) > 0) {
            if(strpos($uids,",") !== false) {
                $uid_array      =   explode(",",$uids);
            }
            else {
                $uid_array[]    =   $uids;
            }
            if(count($uid_array) > 0) {
                foreach($uid_array as $uid) {
                    $lib_country   =   new lib_country($uid);
                    if($lib_country->get_valid()) {
                        $lib_country->delete();
                    }
                }
                $message[]  =   "Country Deleted Successfully";
                $output     =   "success";
            }
        }
        else {
            $error              =   true;
            $message[]          =   "Country does not found";
        }
        $message_type           =   ($error == true)?"error":"success";
        component_response::htmlSuccessFailureResponse($output,$message_type,$message);
    }
}

?>