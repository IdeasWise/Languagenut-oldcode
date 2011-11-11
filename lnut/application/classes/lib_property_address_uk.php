<?php

class lib_property_address_uk extends generic_object {

    public function __construct($uid = 0) {
        parent::__construct($uid, __CLASS__);
    }

    public function get_property_address($pageId = '',$all = false) {
        if($all == false) {
            $query	=	'SELECT COUNT(`uid`) FROM `lib_property_address_uk`';
			
            $this->setPagination($query,$pageId);
			
            $query  = "SELECT * FROM `lib_property_address_uk` ORDER BY `street_name_1` LIMIT ".$this->get_limit();

        }
        else {
            $query  = "SELECT * FROM `lib_property_address_uk` ORDER BY `street_name_1`";
        }

        return database::arrQuery($query);
    }

    public function isCreateSuccessful() {
        $response   =   array();
        $response   =   $this->isValidData();
        if(!empty ($response)) {
            if($response[0] == false) {
                $insert_id      = $this->insert();
                $response[1][]  = "Property Address Added Successfully";
            }
        }
        $message_type           =   ($response[0] == true)?"error":"success";
        component_response::htmlSuccessFailureResponse($insert_id,$message_type,$response[1]);
    }

    public function isUpdateSuccessFul() {
        $response   =   array();
        $response   =   $this->isValidData(true);
        if(!empty ($response)) {
            if($response[0] == false) {
                $this->save();
                $insert_id      = $this->get_uid();
                $this->insertChangeInTransaction($insert_id);
                $response[1][]  = "Property Address Updated Successfully";
            }
        }
        $message_type           =   ($response[0] == true)?"error":"success";
        component_response::htmlSuccessFailureResponse($insert_id,$message_type,$response[1]);
    }

    public function isValidData($update = false,$address_uid = 0) {
        $response = array (
                'fields'=>array (
                        'address_uid'   => array (
                                'default'   => 'Address ID',
                                'message'   => '',
                                'highlight' => false,
                                'error'     => false,
                                'value'     => ''
                        ),
                        'verified'   => array (
                                'default'   => 'Verified',
                                'message'   => '',
                                'highlight' => false,
                                'error'     => false,
                                'value'     => '0'
                        ),
                        'flat_number'      => array (
                                'default'   => 'Flat Number',
                                'message'   => '',
                                'highlight' => false,
                                'error'     => false,
                                'value'     => ''
                        ),
                        'number'      => array (
                                'default'   => 'Building Number',
                                'message'   => '',
                                'highlight' => false,
                                'error'     => false,
                                'value'     => ''
                        ),
                        'name'      => array (
                                'default'   => 'Building Name',
                                'message'   => '',
                                'highlight' => false,
                                'error'     => false,
                                'value'     => ''
                        ),
                        'street_name_1'      => array (
                                'default'   => 'Street Name 1',
                                'message'   => '',
                                'highlight' => false,
                                'error'     => false,
                                'value'     => ''
                        ),
                        'street_name_2'   => array (
                                'default'   => 'Street 2',
                                'message'   => '',
                                'highlight' => false,
                                'error'     => false,
                                'value'     => ''
                        ),
                        'district'   => array (
                                'default'   => 'District',
                                'message'   => '',
                                'highlight' => false,
                                'error'     => false,
                                'value'     => ''
                        ),
                        'town'   => array (
                                'default'   => 'Town',
                                'message'   => '',
                                'highlight' => false,
                                'error'     => false,
                                'value'     => ''
                        ),
                        'city'   => array (
                                'default'   => 'City',
                                'message'   => '',
                                'highlight' => false,
                                'error'     => false,
                                'value'     => ''
                        ),
                        'county'   => array (
                                'default'   => 'County',
                                'message'   => '',
                                'highlight' => false,
                                'error'     => false,
                                'value'     => ''
                        ),
                        'postcode'   => array (
                                'default'   => 'Postcode',
                                'message'   => '',
                                'highlight' => false,
                                'error'     => false,
                                'value'     => ''
                        ),
                        'country_uid'   => array (
                                'default'   => 'Country Name',
                                'message'   => '',
                                'highlight' => false,
                                'error'     => false,
                                'value'     => ''
                        )
                ),
                'message' => ''
        );

        $error  = false;

        // validation starts here
        if($update) {
            if(validation::isValid('integer',$address_uid)) {
                $response['fields']['address_uid']['value']           = $address_uid;
				
				parent::__construct($response['fields']['address_uid']['value'], __CLASS__);
                $this->load();
            } else {
                $response['fields']['address_uid']['message']         = 'Please select a valid ID';
                $response['fields']['address_uid']['error']           = true;
                $response['fields']['address_uid']['highlight']       = true;
            }
        }

        if(validation::isPresent('flat_number',$_POST)) {
            if(validation::isValid('string',$_POST['flat_number'])) {
                $response['fields']['flat_number']['value']    =   $_POST['flat_number'];
            } else {
                $response['fields']['flat_number']['message']         = 'Please Enter a valid flat number';
                $response['fields']['flat_number']['error']           = true;
                $response['fields']['flat_number']['highlight']       = true;
            }
        }

        if(validation::isPresent('number',$_POST)) {
            if(validation::isValid('string',$_POST['number'])) {
                $response['fields']['number']['value']    =   $_POST['number'];
            } else {
                $response['fields']['number']['message']         = 'Please Enter a valid building number';
                $response['fields']['number']['error']           = true;
                $response['fields']['number']['highlight']       = true;
            }
        }

        if(validation::isPresent('name',$_POST)) {
            if(validation::isValid('string',$_POST['name'])) {
                $response['fields']['name']['value']    =   $_POST['name'];
            } else {
                $response['fields']['name']['message']         = 'Please Enter a valid building name';
                $response['fields']['name']['error']           = true;
                $response['fields']['name']['highlight']       = true;
            }
        }

        if(validation::isPresent('street_name_1',$_POST)) {
            if(validation::isValid('string',$_POST['street_name_1'])) {
                $response['fields']['street_name_1']['value']    =   $_POST['street_name_1'];
            } else {
                $response['fields']['street_name_1']['message']         = 'Please Enter a valid street name 1';
                $response['fields']['street_name_1']['error']           = true;
                $response['fields']['street_name_1']['highlight']       = true;
            }
        } else {
            $response['fields']['street_name_1']['message']             = 'Street name 1 is requried';
            $response['fields']['street_name_1']['error']               = true;
            $response['fields']['street_name_1']['highlight']           = true;
        }

        if(validation::isPresent('street_name_2',$_POST)) {
            if(validation::isValid('string',$_POST['street_name_2'])) {
                $response['fields']['street_name_2']['value']    =   $_POST['street_name_2'];
            } else {
                $response['fields']['street_name_2']['message']         = 'Please Enter a valid street name 2';
                $response['fields']['street_name_2']['error']           = true;
                $response['fields']['street_name_2']['highlight']       = true;
            }
        }

        if(validation::isPresent('district',$_POST)) {
            if(validation::isValid('string',$_POST['district'])) {
                $response['fields']['district']['value']            =   $_POST['district'];
            } else {
                $response['fields']['district']['message']          = 'Please Enter a valid Distirct';
                $response['fields']['district']['error']            = true;
                $response['fields']['district']['highlight']        = true;
            }
        }

        if(validation::isPresent('town',$_POST)) {
            if(validation::isValid('string',$_POST['town'])) {
                $response['fields']['town']['value']            =   $_POST['town'];
            } else {
                $response['fields']['town']['message']          = 'Please Enter a valid Town';
                $response['fields']['town']['error']            = true;
                $response['fields']['town']['highlight']        = true;
            }
        }

        if(validation::isPresent('city',$_POST)) {
            if(validation::isValid('string',$_POST['city'])) {
                $response['fields']['city']['value']            =   $_POST['city'];
            } else {
                $response['fields']['city']['message']          = 'Please Enter a valid City';
                $response['fields']['city']['error']            = true;
                $response['fields']['city']['highlight']        = true;
            }
        }

        if(validation::isPresent('county',$_POST)) {
            if(validation::isValid('string',$_POST['county'])) {
                $response['fields']['county']['value']            =   $_POST['county'];
            } else {
                $response['fields']['county']['message']          = 'Please Enter a valid County';
                $response['fields']['county']['error']            = true;
                $response['fields']['county']['highlight']        = true;
            }
        }

        if(validation::isPresent('postcode',$_POST)) {
            if(validation::isValid('string',$_POST['postcode'])) {
                $response['fields']['postcode']['value']            =   $_POST['postcode'];
            } else {
                $response['fields']['postcode']['message']          = 'Please Enter a valid Postcode';
                $response['fields']['postcode']['error']            = true;
                $response['fields']['postcode']['highlight']        = true;
            }
        } else {
            $response['fields']['postcode']['message']              = 'Postcode is requried';
            $response['fields']['postcode']['error']                = true;
            $response['fields']['postcode']['highlight']            = true;
        }

        if(validation::isPresent('country_uid',$_POST)) {
            if(validation::isValid('integer',$_POST['country_uid'])) {
                $response['fields']['country_uid']['value']            =   $_POST['country_uid'];
            } else {
                $response['fields']['country_uid']['message']          = 'Please Select a valid Country';
                $response['fields']['country_uid']['error']            = true;
                $response['fields']['country_uid']['highlight']        = true;
            }
        } else {
            $response['fields']['country_uid']['message']              = 'Country is requried';
            $response['fields']['country_uid']['error']                = true;
            $response['fields']['country_uid']['highlight']            = true;
        }

        if(count($response['fields']) > 0) {
            foreach($response['fields'] as $key => $data) {
                if($data['error'] == true) {
                    $error = true;
                    break;
                }
            }
        }

        if(!$error) {            
            // set the address uk fields
            $this->arrFields['flat_number']['Value']     =   $response['fields']['flat_number']['value'];
            $this->arrFields['number']['Value']          =   $response['fields']['number']['value'];
            $this->arrFields['name']['Value']            =   $response['fields']['name']['value'];
            $this->arrFields['street_name_1']['Value']   =   $response['fields']['street_name_1']['value'];
            $this->arrFields['street_name_2']['Value']   =   $response['fields']['street_name_2']['value'];
            $this->arrFields['district']['Value']        =   $response['fields']['district']['value'];
            $this->arrFields['town']['Value']            =   $response['fields']['town']['value'];
            $this->arrFields['city']['Value']            =   $response['fields']['city']['value'];
            $this->arrFields['county']['Value']          =   $response['fields']['county']['value'];
            $this->arrFields['postcode']['Value']        =   $response['fields']['postcode']['value'];
            $this->arrFields['country_uid']['Value']     =   $response['fields']['country_uid']['value'];
            $this->arrFields['verified']['Value']        =   $response['fields']['verified']['value'];
        }

        if(!$error) {
            return true;
        }
        else {
            return $response;
        }
    }
}
?>