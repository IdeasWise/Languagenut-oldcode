<?php

class page_subscribe_school_stage_1_translations extends generic_object {

    public $arrForm = array();

    public function __construct($uid = 0) {
        parent::__construct($uid, __CLASS__);
    }

    
    public function doSave ()
    {
        $response = true;
        $response = $this->isValidate();
        if( count( $response ) == 0 ){
            $this->save();
        }
        else{
            $msg  = NULL;
            foreach( $response as $idx => $val ){
                $this->arrForm[$idx] = 'label_error';
                $msg .= '<li>'.$val.'</li>';
            }
            if($msg != NULL)
                $this->arrForm['message_error'] = '<p>Please correct the errors below:</p><ul>'.$msg.'</ul>';
        }
        if( count( $response ) > 0 )
            return false;
        else
            return true;

    }

    public function isValidate()
    {

        if(is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
            parent::__construct($_POST['uid'],__CLASS__);
            $this->load();
        }
        $message            =   array();
        if( trim($_POST['background_url']) == '' ) {
            $message['error.background_url']      =   "Please enter background url.";
        }

        if( trim($_POST['title_url']) == '' ) {
            $message['error.title_url']      =   "Please enter title url.";
        }

        if( trim($_POST['title_alt']) == '' ) {
            $message['error.title_alt']      =   "Please enter title alt text.";
        }

        if( trim($_POST['intro_text']) == '' ) {
            $message['error.intro_text']      =   "Please enter introduction text.";
        }


        if( trim($_POST['recaptcha_text']) == '' ) {
            $message['error.recaptcha_text']      =   "Please enter recaptcha text.";
        }

        if( trim($_POST['promo_text']) == '' ) {
            $message['error.promo_text']      =   "Please enter promo text.";
        }

        if( trim($_POST['accept_terms']) == '' ) {
            $message['error.accept_terms']      =   "Please enter accept terms text.";
        }

        if( trim($_POST['send_updates']) == '' ) {
            $message['error.send_updates']      =   "Please enter send update text.";
        }

        if( trim($_POST['emails_not_to_3rd_party']) == '' ) {
            $message['error.emails_not_to_3rd_party']      =   "Please enter email security  text.";
        }

        if( trim($_POST['sub_price']) == ''  ) {
            $message['error.sub_price']      =   "Please enter subscription price.";
        }

         if( $_POST['sub_price'] <= 0 || !is_numeric($_POST['sub_price']) ) {
            $message['error.sub_price']      =   "Please enter valid subscription price.";
        }
        

        $IgnoreArray = array('uid', 'locale','table_name','form_submit_button');
        foreach( $_POST as $idx => $val )   {
            $this->arrForm[$idx] = $val;
            if( in_array($idx, $IgnoreArray ) ) continue;
            $this->arrFields[$idx]['Value'] = $val;
        }
        return $message;
    }
 

    public function subscribeValidation()
    {
                $recaptcha		= new component_recaptchalib();

		$message = '';
		$errors = array ();

		/**
		 * Set up Defaults for the Stage 1 Data Capture Form
		 */
		$form1 = array (
			'name'				=> array ('value'=>'', 'error'=>false),
			'phone_number'		=> array ('value'=>'', 'error'=>false),
			'email'				=> array ('value'=>'', 'error'=>false),
			'password1'			=> array ('value'=>'', 'error'=>false),
			'password2'			=> array ('value'=>'', 'error'=>false),
			'recaptcha'			=> array ('value'=>'', 'error'=>false),
			'terms'				=> array ('value'=>'', 'error'=>false),
			'optin'				=> array ('value'=>'', 'error'=>false),
			'promo_code'		=> array ('value'=>'')
		);

		if(count($_POST) > 0) {
			/**
			 * Capture
			 */
			$name			= (isset($_POST['name']) && strlen(trim($_POST['name'])) > 0)
								? trim($_POST['name'])
								: '';

			$phone_number	= (isset($_POST['phone_number']) && strlen(preg_replace('/[^\d]/','',$_POST['phone_number'])) > 0)
								? preg_replace('/[^\d]/','',$_POST['phone_number'])
								: '';

			$email			= (isset($_POST['email']) && strlen(trim($_POST['email'])) > 0)
								? trim($_POST['email'])
								: '';

			$password1		= (isset($_POST['password1']) && strlen(trim($_POST['password1'])) > 0)
								? $_POST['password1']
								: '';

			$password2		= (isset($_POST['password2']) && strlen(trim($_POST['password2'])) > 0)
								? $_POST['password2']
								: '';

			$terms			= (isset($_POST['accept_terms']) && $_POST['accept_terms']=='yes')
								? true
								: false;

			$optin			= (isset($_POST['optin']) && $_POST['optin']=='yes')
								? true
								: false;

			$promo_code		= (isset($_POST['promo_code']) && strlen(trim($_POST['promo_code'])) > 0)
								? strtoupper(trim($_POST['promo_code']))
								: '';

			/**
			 * Validate
			 */
			if(strlen($name) < 5 || strlen($name) > 255) {
				$errors[] = config::translate('field.name.error.5-255');
				$form1['name']['error'] = true;
			} else {
				$form1['name']['value'] = $name;
			}
			if(strlen($phone_number) < 9 || strlen($phone_number) > 20) {
				$errors[] = config::translate('field.phone_number.error.9-20');
				$form1['phone_number']['error'] = true;
			} else {
				$form1['phone_number']['value'] = $phone_number;
			}

			if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$errors[] = config::translate('field.email.error.invalid');
				$form1['email']['error'] = true;
			} else {

                                $userObject = new user();

                                if($userObject->email_exist($email)) {
                                        $errors[] = config::translate('field.username.error.taken');
                                        $form1['email']['error'] = true;
                                } else {
                                        $form1['email']['value'] = $email;
                                }

                                /*
                                else {
					$errors[] = config::translate('field.email.error.unknown');
					$form1['email']['error'] = true;
				}
                                 *
                                 */
			}

			if($password1=='' || $password1 != $password2) {
				$errors[] = config::translate('field.password.error.missing-or-mismatch');
				$form1['password1']['error'] = true;
				$form1['password2']['error'] = true;
			}
			if(!$terms) {
				$errors[] = config::translate('field.terms.error.not-selected');
				$form1['terms']['error'] = true;
			} else {
				$form1['terms']['value'] = true;
			}
			$form1['optin']['value'] = $optin;
			if(strlen($promo_code) > 0) {
				if(strlen($promo_code) < 3 || strlen($promo_code) > 32) {
					$errors[] = config::translate('field.promo_code.error.invalid-format');
					$form1['promo_code']['error'] = true;
				} else {
					$form1['promo_code']['value'] = $promo_code;
				}
			}

			$recaptcha_response = $recaptcha->recaptcha_check_answer (
				null,
				$_SERVER["REMOTE_ADDR"],
				$_POST["recaptcha_challenge_field"],
				$_POST["recaptcha_response_field"]
			);

			if (!$recaptcha_response['is_valid']) {
				$errors[] = str_replace('{error}',$recaptcha_response['error'],config::translate('field.recaptcha.error.invalid'));
			}
			mail('workstation@mystream.co.uk','dump',print_r($form1,true),'From: developer@languagenut.com');

			// process
			if(count($errors) > 0) {

				$message = '<div class="errors">';
				$message.= '<p><img src="'.config::images('problem.png').'" alt="'.config::translate('form.invalid').'" /></p>';
				$message.= '<p>'.config::translate('form.correct-errors').'</p>';
				$message.= '<ul>';
				$message.= '<li>'.implode('</li><li>',$errors).'</li>';
				$message.= '</ul>';
				$message.= '<p><a href="#" class="errorClose"><img src="'.config::images('back_to_form.png').'" alt="'.config::translate('form.back').'" /></a></p>';
				$message.= '</div>';

				$_SESSION['stage'] = 1;
				$_SESSION['message'] = $message;
			} else {
				$form1['password']['value'] = $password1;
				$_SESSION['stage'] = 2;
			}
			$_SESSION['form1'] = $form1;
			output::redirect(config::url('subscribe/school/'));
		} else {
			if(isset($_SESSION['form1'])) {
				$form1 = $_SESSION['form1'];
				unset($_SESSION['form1']);
			}
		}

		/**
		 * Get any error message
		 */
		if(
			isset($_SESSION['message'])
			&& strlen($_SESSION['message']) > 0
		) {
			$message = $_SESSION['message'];
			unset($_SESSION['message']);
		}

                return array(
                    $errors,
                    $message,
                    $form1
                );
    }

}
?>