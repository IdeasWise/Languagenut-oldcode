<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class cybersource extends SoapClient {

	private $merchantId = '';
	private $transactionKey = '';
	private $error = '';
	private $errorType = '';
	private $reply = null;

	public function __construct($options = array()) {
		parent::__construct(config::get('wsdl'), $options);
		$this->merchantId = config::get('MERCHANT_ID');
		$this->transactionKey = config::get('TRANSACTION_KEY');
	}

// This section inserts the UsernameToken information in the outgoing SOAP message.
	public function __doRequest($request, $location, $action, $version) {

		$user = $this->merchantId;
		$password = $this->transactionKey;

		$soapHeader = "<SOAP-ENV:Header xmlns:SOAP-ENV=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:wsse=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\"><wsse:Security SOAP-ENV:mustUnderstand=\"1\"><wsse:UsernameToken><wsse:Username>$user</wsse:Username><wsse:Password Type=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText\">$password</wsse:Password></wsse:UsernameToken></wsse:Security></SOAP-ENV:Header>";

		$requestDOM = new DOMDocument('1.0');
		$soapHeaderDOM = new DOMDocument('1.0');

		try {

			$requestDOM->loadXML($request);
			$soapHeaderDOM->loadXML($soapHeader);

			$node = $requestDOM->importNode($soapHeaderDOM->firstChild, true);
			$requestDOM->firstChild->insertBefore(
					$node, $requestDOM->firstChild->firstChild);

			$request = $requestDOM->saveXML();

// printf( "Modified Request:\n*$request*\n" );
		} catch (DOMException $e) {
			die('Error adding UsernameToken: ' . $e->code);
		}
		return parent::__doRequest($request, $location, $action, $version);
	}

	public function makeSoapPacket($merchantReferenceCode='1', $cardDetails=array(), $item=array(), $billingAddres=array(),$currency="USD") {
		$productsArray = array();
		
		if (count($cardDetails)==0 || !isset($cardDetails['accountNumber']) || !isset($cardDetails['expirationMonth'])  || !isset($cardDetails['expirationYear']) ) {
			$this->errorType = 'invalid credit card detail';
			$this->error = 'Please Enter Valid Credit card detail';
			return;
		}
		if (count($item) > 0) {

			$counter = 1;
			foreach ($item as $product) {
				$item0 = new stdClass();
				$item0->unitPrice = (isset($product["unitPrice"])) ? $product["unitPrice"] : "0";
				$item0->quantity = (isset($product["quantity"])) ? $product["quantity"] : "1";
				$item0->id = $counter;
				$productsArray[] = $item0;
				$counter++;
			}
		} else {
			$this->errorType = 'empty cart';
			$this->error = 'There should be at least one item in cart';
			return;
		}
		try {

			/*
			  To see the functions and types that the SOAP extension can automatically
			  generate from the WSDL file, uncomment this section:
			  $functions = $this->__getFunctions();
			  print_r($functions);
			  $types = $this->__getTypes();
			  print_r($types);
			 */

			$request = new stdClass();

			$request->merchantID = $this->merchantId;

			// Before using this example, replace the generic value with your own.
			$request->merchantReferenceCode = $merchantReferenceCode;

			// To help us troubleshoot any problems that you may encounter,
			// please include the following information about your PHP application.
			$request->clientLibrary = "PHP";
			$request->clientLibraryVersion = phpversion();
			$request->clientEnvironment = php_uname();

			// This section contains a sample transaction request for the authorization
			// service with complete billing, payment card, and purchase (two items) information.
			$ccAuthService = new stdClass();
			$ccAuthService->run = "true";
			$request->ccAuthService = $ccAuthService;

			$billTo = new stdClass();
			$billTo->firstName = (isset($billingAddres["firstName"])) ? $billingAddres["firstName"] : "";
			$billTo->lastName = (isset($billingAddres["lastName"])) ? $billingAddres["lastName"] : "";
			$billTo->street1 = (isset($billingAddres["street1"])) ? $billingAddres["street1"] : "";
			$billTo->city = (isset($billingAddres["city"])) ? $billingAddres["city"] : "";
			$billTo->state = (isset($billingAddres["state"])) ? $billingAddres["state"] : "";
			$billTo->postalCode = (isset($billingAddres["postalCode"])) ? $billingAddres["postalCode"] : "";
			$billTo->country = (isset($billingAddres["country"])) ? $billingAddres["country"] : "";
			$billTo->email = (isset($billingAddres["email"])) ? $billingAddres["email"] : "";
			$billTo->ipAddress = (isset($billingAddres["ipAddress"])) ? $billingAddres["ipAddress"] : "";
			$billTo->ipAddress = (isset($billingAddres["ipAddress"])) ? $billingAddres["ipAddress"] : "";

			$request->billTo = $billTo;

			$card = new stdClass();
			$card->accountNumber = $cardDetails["accountNumber"];
			$card->expirationMonth = $cardDetails["expirationMonth"];
			$card->expirationYear = $cardDetails["expirationYear"];
			$card->card_cvNumber = '004';
			$request->card = $card;

			$purchaseTotals = new stdClass();
			$purchaseTotals->currency = $currency;
			$request->purchaseTotals = $purchaseTotals;

			$request->item = $productsArray;

			$reply = $this->runTransaction($request);

			// This section will show all the reply fields.
			// var_dump($reply);
			// To retrieve individual reply fields, follow these examples.
			$this->reply = $reply;
//			printf("decision = $reply->decision<br>");
//			printf("reasonCode = $reply->reasonCode<br>");
//			printf("requestID = $reply->requestID<br>");
//			printf("requestToken = $reply->requestToken<br>");
//			printf("ccAuthReply->reasonCode = " . $reply->ccAuthReply->reasonCode . "<br>");
		} catch (SoapFault $exception) {
			$this->errorType = 'run time exception';
			$this->error = $exception->__toString();
		}
	}

	public function get_error() {
		return array('errorType' => $this->errorType, 'error' => $this->error);
	}

	

	public function get_reply() {
		return $this->reply;
	}

}

?>