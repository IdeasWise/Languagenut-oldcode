<?php

class country_type extends Controller {

    private $selected           = '';
    private $selected_action    = "";

    private $pages	= array (
            'country-type-action'
    );
    private $action	= array (
            'country-type-update',
            'get-country-type-data',
            'delete-country-type',
            'delete-multiple-country-type'
    );

    public function __construct () {
        parent::__construct();


        $parts = config::get('paths');
        if(isset($parts[3]) && in_array($parts[3], $this->pages)) {
            $this->selected	 = $parts[3];
        }
        switch($this->selected) {
            case "country-type-action":
                if($_POST > 0 && isset($_POST['action']) && in_array($_POST['action'], $this->action)) {
                    $this->selected_action = $_POST['action'];
                }
                switch($this->selected_action) {
                    case "country-type-update":
                        $this->add_update_country_type();
                        break;
                    case "get-country-type-data":
                        $this->get_country_type();
                        break;
                    case "delete-country-type":
                        $this->delete_country_type();
                        break;
                    case "delete-multiple-country-type":
                        $this->delete_multiple_country_type();
                        break;
                    default:
                        $this->country_type_list();
                        break;
                }
                break;
            default:
                $this->country_type_list();
                break;
        }
    }

    protected function country_type_list() {
        $skeleton                       = new xhtml ('skeleton.admin');
        $skeleton->load();

        $body				= new xhtml('body.admin.library.country.type');
        $body->load();

        $data                           = component_country_type::generate_record_list();
        $body->assign("page.list", $data);

        $skeleton->assign (
                array (
                'body'				=> $body
                )
        );
        output::as_html($skeleton,true);
    }

    protected function add_update_country_type() {
        if(count($_POST) > 0) {
            $country_type_uid   =   (isset($_POST['country_type_uid']) ? format::to_integer($_POST['country_type_uid']) : 0);
            $lib_country_type   =   new lib_country_type();

            if(is_numeric($country_type_uid) && $country_type_uid > 0) {
                $lib_country_type->isUpdateSuccessFul();
            }
            else {
                $lib_country_type->isCreateSuccessFul();
            }
        }
    }

    protected function get_country_type() {
        if(count($_POST) > 0) {
            $error                      =   1;
            $data                       =   array();
            $country_type_uid           =   (isset($_POST['country_type_uid'])? format::to_integer($_POST['country_type_uid']) : '');
            $skeleton                   =   new xhtml ('xml.country.type.get.data');
            $skeleton->load();

            if(is_numeric($country_type_uid) && $country_type_uid > 0) {
                $lib_country_type   =   new lib_country_type($country_type_uid);
                $lib_country_type->load();
                $data = array(
                        "name"          => $lib_country_type->get_name(),
                        "uid"           => $lib_country_type->get_uid(),
                        "active"        => $lib_country_type->get_active(),
                        "error"         => "0"
                );
            }
            else {
                $data = array(
                        "name"          => "",                        
                        "uid"           => "",
                        "active"        => "",
                        "error"         => "1"
                );
            }
            $skeleton->assign($data);
            output::as_xml($skeleton,true);
        }
    }

    protected function delete_country_type() {
        $success                        =   false;
        $error                          =   false;
        $message                        =   array();
        $country_type_uid               =   (isset($_POST['country_type_uid'])? format::to_integer($_POST['country_type_uid']) : 0);
        $page_id                        =   (isset($_POST['page_id'])? format::to_integer($_POST['page_id']) : '');
        $output                         =   "";
        if(is_numeric($country_type_uid) && $country_type_uid > 0) {
            $lib_country_type   =   new lib_country_type($country_type_uid);
            if($lib_country_type->get_valid()) {
                $lib_country_type->delete();
                $message[]  =   "Country Type Deleted Successfully";
            }
        }
        else {
            $error              =   true;
            $message[]          =   "Country Type not found";
        }
        $message_type           =   ($error == true)?"error":"success";
        component_response::htmlSuccessFailureResponse($country_type_uid,$message_type,$message);
    }

    protected function delete_multiple_country_type() {
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
                    $lib_country_type   =   new lib_country_type($uid);
                    if($lib_country_type->get_valid()) {
                        $lib_country_type->delete();
                    }
                }
                $message[]  =   "Country Types Deleted Successfully";
                $output     =   "success";
            }
        }
        else {
            $error              =   true;
            $message[]          =   "Country Type does not found";
        }
        $message_type           =   ($error == true)?"error":"success";
        component_response::htmlSuccessFailureResponse($output,$message_type,$message);
    }
}

?>