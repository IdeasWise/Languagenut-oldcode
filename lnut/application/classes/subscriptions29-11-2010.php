<?php

class subscriptions extends generic_object {

    public $arrForm = array();

    public function __construct($uid = 0) {
        parent::__construct($uid, __CLASS__);
    }

    public function getList($data = array(), $OrderBy = "date_paid DESC", $all = false) {
        $parts = config::get('paths');

        if (!in_array(@$parts[2], array('school', 'homeuser')))
            $parts[2] = 'school';

        $asField = ' school as name';
        if ($parts[2] == 'school') {
            $where = ', users_schools S, `user` as `U` where S.user_uid = SB.user_uid and `invoice_for` = "school" and `U`.`deleted` != "1" and U.uid = S.user_uid ';
            if (@$parts[4] != '' && !is_numeric($parts[4]))
                $where .= " and `language_prefix` = '" . $parts[4] . "'";
        }
        if ($parts[2] == 'homeuser') {
            $asField = " CONCAT(vfirstname, ' ', vlastname) as name, vemail, vphone";
            $where = ', profile_homeuser P, `user` as `U` where P.iuser_uid = SB.user_uid and `invoice_for` = "homeuser" and `U`.`deleted` != "1" and U.uid = P.iuser_uid';
        }


        foreach ($data as $idx => $val) {
            $where .= " AND " . $idx . "='" . $val . "'";
        }
        if ($all == false) {
            $result = database::query('SELECT COUNT(SB.uid) FROM subscriptions SB' . $where);
            $max = 0;
            if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
                $row = mysql_fetch_array($result);
                $max = $row[0];
            }
            $pageId = '';
            if ($pageId == '') {
                 $n = count($parts) - 1;

                if (isset($parts[$n]) && is_numeric($parts[$n]) && $parts[$n] > 0) {
                    $pageId = $parts[$n];
                } else {
                    $pageId = 1;
                }
            }
           
            $this->pager(
                    $max, //see above
                    config::get("pagesize"), //how many records to display at one time
                    $pageId,
                    array("php_self" => "")
            );

            $this->set_range(10);   
			
            $result = database::query("SELECT SB.*, " . $asField . " FROM subscriptions SB " . $where . " ORDER BY " . $OrderBy . "  LIMIT " . $this->get_limit());
        } else {
            $result = database::query("SELECT SB.*, " . $asField . " FROM subscriptions SB " . $where . " ORDER BY " . $OrderBy);
        }
        $this->data = array();
        if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                $this->data[] = $row;
            }
        }
        return $this->data;
    }

    public function doSave() {
	
		
		
        $response = $this->isValidate();
        if (count($response) == 0) {
			$due_date = 0;
            $fields = array();
            if (@$_POST['user_uid'] > 0)
                $fields[] = "user_uid = '" . @$_POST['user_uid'] . "'";
            if (!empty($_POST['date_paid']))
                $fields[] = "date_paid = '" . $_POST['date_year'] . '-' . $_POST['date_month'] . '-' . $_POST['date_day'] . "'";
            if (!empty($_POST['start_dts'])) {
                $fields[] = "start_dts = '" . $_POST['start_year'] . '-' . $_POST['start_month'] . '-' . $_POST['start_day'] . "'";
				$due_date = strtotime(str_replace('/','-',$_POST['start_dts']). " +14 day");
            }
            if (!empty($_POST['expires_dts']))
                $fields[] = "expires_dts = '" . $_POST['expires_year'] . '-' . $_POST['expires_month'] . '-' . $_POST['expires_day'] . "'";
            if (!empty($_POST['verified_dts']))
                $fields[] = "verified_dts  = '" . $_POST['verified_year'] . '-' . $_POST['verified_month'] . '-' . $_POST['verified_day'] . "'";
            if (!empty($_POST['due_date'])) {
				if( $due_date > strtotime(str_replace('/','-',$_POST['due_date'])) ){
					$fields[] = "due_date  = '" . date('Y-m-d',$due_date) . "'";
				}else {
					$fields[] = "due_date  = '" . $_POST['due_year'] . '-' . $_POST['due_month'] . '-' . $_POST['due_day'] . "'";
				}            }
			if (empty($_POST['due_date'])){
					$fields[] = "due_date  = '" . date('Y-m-d',$due_date) . "'";
			}

			if(isset($_POST['invoice_number']) && $_POST['invoice_number'] != '')
				$fields[] = "invoice_number = '" . $_POST['invoice_number'] . "'";
			if(isset($_POST['vat']) && $_POST['vat'] != '')
				$fields[] = "vat = '" . $_POST['vat'] . "'";
            $fields[] = "verified = '" . $_POST['verified'] . "'";
            $fields[] = "amount = '" . $_POST['amount'] . "'";
            $fields[] = "invoice_for = '" . $_POST['mode'] . "'";
            $fields[] = "paypal_txn_id = '" . @$_POST['paypal_txn_id'] . "'";

            if ($_POST['uid'] > 0) {
                $update = "UPDATE subscriptions SET " . implode(', ', $fields);
                $update .= " WHERE uid = '" . $_POST['uid'] . "'"; 
                database::query($update);
                //$this->save ();
            } else {
                $sql = "INSERT INTO subscriptions SET " . implode(', ', $fields);
                database::query($sql);
                // $insert = $this->insert();
                @$this->arrForm['uid'] = mysql_insert_id();
            }
        } else {
            $msg = NULL;
            foreach ($response as $idx => $val) {
                $this->arrForm[$idx] = 'label_error';
                $msg .= '<li>' . $val . '</li>';
            }
            if ($msg != NULL)
                $this->arrForm['message_error'] = '<p>Please correct the errors below:</p><ul>' . $msg . '</ul>';
        }
        if (count($response) > 0)
            return false;
        else
            return true;
    }

    public function isValidate() {
        $message = array();

        if (trim(@$_POST['user_uid']) == '' && trim($_POST['uid']) == '') {
            $message['error_user_uid'] = 'Please choose school name';
            if ($_POST['mode'] == 'homeuser')
                $message['error_user_uid'] = 'Please choose home user';
        }
        if ($_POST['start_dts'] == '')
            $message['error_start_dts'] = 'Please enter start date';
        if ($_POST['expires_dts'] == '')
            $message['error_expires_dts'] = 'Please enter expire date';
        if ($_POST['amount'] == '')
            $message['error_amount'] = 'Please enter amount';
        if (!is_numeric($_POST['amount']))
            $message['error_amount'] = 'Please enter valid amount';
		if ($_POST['invoice_number'] == '')
            $message['error_invoice_number'] = 'Please enter invoice number';
        if (!is_numeric($_POST['invoice_number']))
            $message['error_invoice_number'] = 'invoice number number should be number.';
		if ($_POST['vat'] == '' || $_POST['vat'] == 0)
            $message['error_vat'] = 'Please enter VAT %';
        if (!is_numeric($_POST['vat']))
            $message['error_vat'] = 'Please enter valid VAT %';

        foreach ($_POST as $idx => $val)
            $this->arrForm[$idx] = $val;

        return $message;
    }

    public function doPaid($uid) {
        if ($uid > 0) {
            $query = "UPDATE  subscriptions SET date_paid = NOW(), verified_dts = NOW(), verified = '1' WHERE uid = '" . $uid . "' ";
            $res = database::query($query);
            if (mysql_affected_rows ()) {
                echo '<div class="ClassGreen">Paid</div>';
            }
        }
    }

    public function CreateSchoolSbscription($user_uid, $price) {
        $now = date('Y-m-d H:i:s');
        list($date, $time) = explode(' ', $now);
        list($y, $m, $d) = explode('-', $date);
        list($h, $i, $s) = explode(':', $time);

        $start = $now;
        $expires = date('Y-m-d H:i:s', mktime($h, $i, $s, $m, ($d + 14), ($y + 1)));
        $due_date = date('Y-m-d H:i:s', mktime($h, $i, $s, $m, ($d + 14), ($y)));

		$priceArray = array();
        $Pricingobject = new currencies();
        $priceArray = $Pricingobject->getPriceAndCurrency('school');

        $this->arrFields['user_uid']['Value'] = $user_uid;
		$this->arrFields['invoice_number']['Value'] = 1600+$user_uid;
        $this->arrFields['date_paid']['Value'] = '0000-00-00 00:00:00';
        $this->arrFields['due_date']['Value'] = $due_date;
        $this->arrFields['amount']['Value'] = $price;
        $this->arrFields['start_dts']['Value'] = $start;
        $this->arrFields['expires_dts']['Value'] = $expires;
        $this->arrFields['verified']['Value'] = 0;
        $this->arrFields['verified_dts']['Value'] = '0000-00-00 00:00:00';
        $this->arrFields['invoice_for']['Value'] = 'school';
		$this->arrFields['vat']['Value'] = $priceArray['vat'];			

        return $this->insert();
    }

    public function CreateHomeUserSbscription($user_uid, $price) {
        $now = date('Y-m-d H:i:s');
        list($date, $time) = explode(' ', $now);
        list($y, $m, $d) = explode('-', $date);
        list($h, $i, $s) = explode(':', $time);

        $start = $now;
        $expires = date('Y-m-d H:i:s', mktime($h, $i, $s, $m, ($d), ($y + 1)));

		$priceArray = array();
        $Pricingobject = new currencies();
        $priceArray = $Pricingobject->getPriceAndCurrency('homeuser');

        $this->arrFields['user_uid']['Value'] = $user_uid;
		$this->arrFields['invoice_number']['Value'] = 1600+$user_uid;
        $this->arrFields['date_paid']['Value'] = '0000-00-00 00:00:00';
        $this->arrFields['due_date']['Value'] = '0000-00-00 00:00:00';
        $this->arrFields['amount']['Value'] = $price;
        $this->arrFields['start_dts']['Value'] = $start;
        $this->arrFields['expires_dts']['Value'] = $expires;
        $this->arrFields['verified']['Value'] = 0;
        $this->arrFields['verified_dts']['Value'] = '0000-00-00 00:00:00';
        $this->arrFields['invoice_for']['Value'] = 'homeuser';
		$this->arrFields['vat']['Value'] = $priceArray['vat'];




        return $this->insert();
    }

    /* start of paypal operations handling functions ( this is used with homeuser subscription ) */

    public function Paypalcancel() {
        if (isset($_SESSION['form1'])) {
            $form1 = $_SESSION['form1'];

            unset($_SESSION['form1']);
            unset($_SESSION['stage']);

            $user_uid = $form1['user_uid'];

            $userObject = new user($user_uid);
            $userObject->load();


            $homeuserObject = new profile_homeuser();
            $huwhere = array('iuser_uid' => $user_uid);
            $homeuserObject->load(array(), $huwhere);


            $where = array('user_uid' => $homeuserObject->TableData['uid']['Value'], 'invoice_for' => 'homeuser');
            $this->load(array(), $where);


            // remove the subscription's record from the database
            $this->where_delete($where);
            // remove the home user's profile record from the database
            $homeuserObject->where_delete($huwhere);
            // remove the user's record from the database
            $userObject->delete();
        }
    }

    public function PaypalSuccess() {
        if (isset($_SESSION['form1'])) {
            $form1 = $_SESSION['form1'];

            unset($_SESSION['form1']);
            if (isset($_SESSION['stage'])) {
                unset($_SESSION['stage']);
            }
            // move the user to the login page
            $_SESSION['login_email'] = $form1['username'];
            $_SESSION['login_password'] = $form1['password'];
        }
    }

    public function PaypalIPN() {
        
        mail('dev@mystream.co.uk', 'languagenut paypal - pre', print_r($_POST, true), 'From: payments@languagenut.com');
        // paypal ipn here
        // read the post from PayPal system and add 'cmd'
        $req = 'cmd=_notify-validate';

        foreach ($_POST as $key => $value) {
            $value = urlencode(stripslashes($value));
            $req .= "&$key=$value";
        }
        $header = '';

        // post back to PayPal system to validate
        $header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

        // open socket to paypal
        $fp = fsockopen('www.paypal.com', 80, $errno, $errstr, 30);
        // $fp = @fsockopen('www.sandbox.paypal.com', 80, $errno, $errstr, 30);
        // assign posted variables to local variables
        $item_name = $_POST['item_name'];
        $item_number = $_POST['item_number'];
        $payment_status = $_POST['payment_status'];
        $payment_amount = $_POST['mc_gross'];
        $payment_currency = $_POST['mc_currency'];
        $txn_id = $_POST['txn_id'];
        $receiver_email = $_POST['receiver_email'];
        $payer_email = $_POST['payer_email'];

        if (!$fp) {
            // HTTP ERROR
            mail('dev@mystream.co.uk', 'languagenut paypal fp error', $errno . $errstr, 'From: errors@languagenut.com');
        } else {
            mail('dev@mystream.co.uk', 'languagenut paypal fp success', '', 'From: errors@languagenut.com');
            fputs($fp, $header . $req);
            while (!feof($fp)) {
                $res = fgets($fp, 1024);
                print $res;                


                $res = fgets ($fp, 1024);
		if (strcmp ($res, "VERIFIED") == 0){


                     $user_uid = $item_number;

                if ($payment_status == 'Completed') {
                    // first we check that this transaction has not already been processed
                    $this->load(array('paypal_txn_id' => 'paypal_txn_id'), array('paypal_txn_id'=> mysql_real_escape_string($txn_id)));

                    if ( empty( $this->TableData['paypal_txn_id']['Value'] ) ) {
                        // no records for this transaction id - continue
                        // update the subscription to say it has been validated and set the validation date



                        // load home user profile
                        $homeuserObject = new profile_homeuser();
                        $huwhere = array('iuser_uid' => $item_number);
                        $homeuserObject->load(array(), $huwhere);

                        // load subscriptions
                        $where = array('user_uid' => $homeuserObject->TableData['uid']['Value'], 'invoice_for' => 'homeuser');
                        $this->load(array(), $where);

                        $query = "SELECT `uid` FROM `subscriptions` WHERE
                                  `user_uid`='" . $item_number . "'
                                   and `invoice_for` = 'homeuser'  ORDER BY `uid` DESC LIMIT 1";
                        $result = database::query($query);
                        if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
                            $row = mysql_fetch_assoc($result);
                            $sub_uid = $row['uid'];

                            $query = "UPDATE `subscriptions` SET `verified`=1, `verified_dts`='" . date('Y-m-d H:i:s') . "', `paypal_txn_id`='" . mysql_real_escape_string($txn_id) . "' WHERE `uid`=$sub_uid LIMIT 1";
                            $result = database::query($query);
                            
                            $query = "UPDATE `user` SET `access_allowed`='1' WHERE `uid`='".$item_number."' LIMIT 1";
                            $result = database::query($query);

                            if ($result && mysql_error() == '') {
                                // finally... send an email to the admins that a payment has been completed
                                 if ( !empty($homeuserObject->TableData['vfirstname']['Value'])) {
									$user_vemail = '';
                                    $username = $homeuserObject->TableData['vfirstname']['Value'];
									$user_vemail = $homeuserObject->TableData['vemail']['Value'];
                                    $headers = '';
                                    $headers .= "Content-Transfer-Encoding: 8bit" . "\n";
                                    $headers .= 'Content-type: text/html; charset=iso-8859-15' . "\n";
                                    $headers .= 'From: payments@languagenut.com' . "\n";
                                    $email = 'subs@languagenut.com, jamie@languagenut.com, dev@mystream.co.uk';
                                    $subject = 'Home User account paid via PayPal.';
                                    $html = '';
                                    $html .= 'Username:' . $username . '<br />';
                                    $html .= 'Amount:' . $payment_amount . ' ' . $payment_currency . '<br />';

                                    mail($email, $subject, $html, $headers);                                    

                                    // send email to the client
                                    $email = $username;
                                    $query = "SELECT `subject`,`body` FROM `page_subscribe_homeuser_stage_4_translations` WHERE `locale`='".config::get('locale')."' LIMIT 1";
                                    $result = database::query($query);
                                    if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
                                        $row = mysql_fetch_assoc($result);
                                        $subject = stripslashes($row['subject']);
                                        $body = stripslashes(str_replace('{terms}', config::url('/terms/'), $row['body']));
                                        mail($user_vemail, $subject, $body, $headers);
                                    } else {
                                        mail('dev@mystream.co.uk', 'languagenut: cannot find subject/body for client paypal update', mysql_error() . $query, 'From: errors@languagenut.com');
                                    }
                                } else {
                                    mail('dev@mystream.co.uk', 'languagenut: cannot find user for ' . $item_number, mysql_error() . $query, 'From: errors@languagenut.com');
                                }
                            } else {
                                mail('dev@mystream.co.uk', 'languagenut: subscription [' . $sub_uid . '] not updated after payment', mysql_error() . $query, 'From: errors@languagenut.com');
                            }
                        } else {
                            mail('dev@mystream.co.uk', 'languagenut: no matching subscription for user [' . $item_number . ']', mysql_error() . $query, 'From: errors@languagenut.com');
                        }
                    } else {
                        // payment already been processed - do nothing?
                        mail('dev@mystream.co.uk', 'languagenut paypal double callback', '', 'From: errors@languagenut.com');
                    }
                } else {
                    // payment is anything other than Completed (i.e. accepted + balance added to paypal account)
                    mail('dev@mystream.co.uk', 'languagenut paypal not completed', $payment_status, 'From: errors@languagenut.com');
                }


		}

		// if the IPN POST was 'INVALID'...do this
		else if (strcmp ($res, "INVALID") == 0){
			 mail('dev@mystream.co.uk', 'languagenut paypal INVALID IPN', $errno . $errstr, 'From: errors@languagenut.com');
		}

               


            }
            fclose($fp);
        }
        exit();
    }
    /* End of paypal operations handling functions ( this is used with homeuser subscription ) */
}

?>