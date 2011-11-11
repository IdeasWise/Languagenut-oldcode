<?php

/**
 * subscribe.php
 */
class Paypal extends Controller {

    private $headers = '';
    private $mailBody = '';

    public function __construct() {
        parent::__construct();

        $paths = config::get('paths');


        foreach ($_REQUEST as $idx => $val)
        $this->mailBody .= ' $data["' . $idx . '"] = ' . $val . ';<BR>';
        $this->headers = 'MIME-Version: 1.0' . "\r\n";
        $this->headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $this->headers .= 'From: errors@languagenut.com' . "\r\n";

        $function = 'index';
        if (isset($paths[1]))
            $function = $paths[1];

        $this->$function();
    }

    public function index() {

        $skeleton = new xhtml('skeleton.subscribe');
        $skeleton->load();

        $body = new xhtml('paypal');
        $body->load();
        $skeleton->assign(
                array(
                    'title' => 'Paypal Test',
                    'keywords' => 'Paypal Test',
                    'description' => 'Paypal Test',
                    'body' => $body,
                    'background_url' => 'registration_bg.en.jpg'
                )
        );
        output::as_html($skeleton, true);
    }

    public function success() {
        echo 'success';

        mail('dev@mystream.co.uk', 'Paypal Success', $this->mailBody, $this->headers);
    }

    public function cancel() {
        echo 'cancel';
        mail('dev@mystream.co.uk', 'Paypal Cancel', $this->mailBody, $this->headers);
    }

    public function ipn() {

        echo 'ipn';

        $req = 'cmd=_notify-validate';
        foreach ($_POST as $key => $value) {
            $value = urlencode(stripslashes($value));
            $req .= "&$key=$value";
        }
// post back to PayPal system to validate
        $header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: " . strlen($req) . "\r\n\r\n";


        $fp = @fsockopen('www.sandbox.paypal.com', 80, $errno, $errstr, 30); // sandbox payment
// $fp = @fsockopen ('www.paypal.com', 80, $errno, $errstr, 30); // Live
// assign posted variables to local variables
        $business = $_POST['business'];
        $mc_gross = $_POST['mc_gross'];
        $custom = $_POST['custom'];
        $payment_status = $_POST['payment_status'];

        if (!$fp) {
            // HTTP ERROR
        } else {
            //mysql_query("insert into ipn(val) values('calling fputs ... ');");
            fputs($fp, $header . $req);
            while (!feof($fp)) {
                $res = fgets($fp, 1024);
                if (strcmp($res, "VERIFIED") == 0) {

                    //mail('workstation@mystream.co.uk', 'VERIFIED IPN', $res.'<br><br>'.$this->mailBody, $this->headers);
                }

                // if the IPN POST was 'INVALID'...do this
                else if (strcmp($res, "INVALID") == 0) {
                    // log for manual investigation                   
                    //mail('workstation@mystream.co.uk', 'INVALID IPN', $this->mailBody, $this->headers);
                }
            }
            fclose($fp);
        }


    }

}

?>