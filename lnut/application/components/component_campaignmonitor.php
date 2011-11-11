<?php

/**
* The new CampaignMonitor class that now extends from CMBase. This should be 
* backwards compatible with the original (PHP5) version.
*
* @package CampaignMonitorLib
* @subpackage CampaignMonitor
* @version 1.4.3
* @author Kaiser Shahid <knitcore@yahoo.com> (www.qaiser.net) and 
* Campaign Monitor <support@campaignmonitor.com> 
* @copyright 2007-2009
* @see http://www.campaignmonitor.com/api/
*/
class component_campaignmonitor extends component_cmbase {
	var /*@ protected */
		$url = 'http://api.createsend.com/api/api.asmx',
		$soapAction = 'http://api.createsend.com/api/';
	
	/**
	* @param string $api Your API key.
	* @param string $client The default ClientId you're going to work with.
	* @param string $campaign The default CampaignId you're going to work with.
	* @param string $list The default ListId you're going to work with.
	* @param string $method Determines request type. Values are either get, post, or soap.
	*/
	
	function CampaignMonitor( $api = null, $client = null, $campaign = null, $list = null, $method = 'get' )
	{
		CMBase::CMBase( $api, $client, $campaign, $list, $method );
	}

	/**
	* Wrapper for Subscribers.GetActive. This method triples as Subscribers.GetUnsubscribed 
	* and Subscribers.GetBounced when the very last parameter is overridden.
	*
	* @param mixed $date If a string, should be in the date() format of 'Y-m-d H:i:s', otherwise, a Unix timestamp.
	* @param int $list_id (Optional) A valid List ID to check against. If not given, the default class property is used.
	* @param string $action (Optional) Set the actual API method to call. Defaults to Subscribers.GeActive if no other valid value is given.
	* @return mixed A parsed response from the server, or null if something failed.
	* @see http://www.campaignmonitor.com/api/Subscribers.GetActive.aspx
	*/
	function subscribersGetActive( $date  = 0, $list_id = null, $action = 'Subscribers.GetActive' )
	{
		if ( !$list_id )
			$list_id = $this->list_id;
		
		if ( is_numeric( $date ) )
			$date = date( 'Y-m-d H:i:s', $date );
		
		$valid_actions = array( 'Subscribers.GetActive' => '', 'Subscribers.GetUnsubscribed' => '', 'Subscribers.GetBounced' => '' );
		if ( !isset( $valid_actions[$action] ) )
			$action = 'Subscribers.GetActive';
		
		return $this->makeCall( $action
			, array( 
				'params' => array( 
					'ListID' => $list_id 
					, 'Date' => $date
				)
			)
		);
	}
	
	/**
	* @param mixed $date If a string, should be in the date() format of 'Y-m-d H:i:s', otherwise, a Unix timestamp.
	* @param int $list_id (Optional) A valid List ID to check against. If not given, the default class property is used.
	* @see http://www.campaignmonitor.com/api/Subscribers.GetUnsubscribed.aspx
	*/
	function subscribersGetUnsubscribed( $date  = 0, $list_id = null )
	{
		return $this->subscribersGetActive( $date, $list_id, 'Subscribers.GetUnsubscribed' );
	}
	
	/**
	* @param mixed $date If a string, should be in the date() format of 'Y-m-d H:i:s', otherwise, a Unix timestamp.
	* @param int $list_id (Optional) A valid List ID to check against. If not given, the default class property is used.
	* @see http://www.campaignmonitor.com/api/Subscribers.GetBounced.aspx
	*/
	function subscribersGetBounced( $date  = 0, $list_id = null )
	{
		return $this->subscribersGetActive( $date, $list_id, 'Subscribers.GetBounced' );
	}
	
	/**
	* subscriberAdd()
	* @param string $email Email address.
	* @param string $name User's name.
	* @param int $list_id (Optional) A valid List ID to check against. If not given, the default class property is used.
	* @param boolean $resubscribe If true, does an equivalent 'AndResubscribe' API method.
	* @see http://www.campaignmonitor.com/api/Subscriber.Add.aspx
	*/
	function subscriberAdd( $email, $name, $list_id = null, $resubscribe = false )
	{
		if ( !$list_id )
			$list_id = $this->list_id;
		
		$action = 'Subscriber.Add';
		if ( $resubscribe ) $action = 'Subscriber.AddAndResubscribe';
		
		return $this->makeCall( $action
			, array(
				'params' => array(
					'ListID' => $list_id
					, 'Email' => $email
					, 'Name' => $name
				)
			)
		);
	}
	
	/**
	* This encapsulates the check of whether this particular user unsubscribed once.
	* @param string $email Email address.
	* @param string $name User's name.
	* @param int $list_id (Optional) A valid List ID to check against. If not given, the default class property is used.
	*/
	function subscriberAddRedundant( $email, $name, $list_id = null )
	{
		$added = $this->subscriberAdd( $email, $name, $list_id );        
	        
		if ( $added && $added['Result']['Code'] == '204' )
		{
			$subscribed = $this->subscribersGetIsSubscribed( $email, $list_id );    
	    
			// Must have unsubscribed, so resubscribe
			if ( $subscribed['anyType'] == 'False' )
			{
				// since we're internal, we'll just call the method with full parameters rather
				// than go through a secondary wrapper function.
				$added = $this->subscriberAdd( $email, $name, $list_id, true );
				return $added;
			}
		}
		
		return $added;
	}
	
	/**
	* @param string $email Email address.
	* @param string $name User's name.
	* @param mixed $fields Should be a $key => $value mapping. If there are more than one items for $key, let
	*        $value be a list of scalar values. Example: array( 'Interests' => array( 'xbox', 'wii' ) )
	* @param int $list_id (Optional) A valid List ID to check against. If not given, the default class property is used.
	* @param boolean $resubscribe If true, does an equivalent 'AndResubscribe' API method.
	* @return mixed A parsed response from the server, or null if something failed.
	* @see http://www.campaignmonitor.com/api/Subscriber.AddWithCustomFields.aspx
	*/
	function subscriberAddWithCustomFields( $email, $name, $fields, $list_id = null, $resubscribe = false )
	{
		if ( !$list_id )
			$list_id = $this->list_id;
		
		$action = 'Subscriber.AddWithCustomFields';
		if ( $resubscribe ) $action = 'Subscriber.AddAndResubscribeWithCustomFields';
		
		if ( !is_array( $fields ) )
			$fields = array();
		
		$_fields = array( 'SubscriberCustomField' => array() );
		foreach ( $fields as $k => $v )
		{
			if ( is_array( $v ) )
			{
				foreach ( $v as $nv )
					$_fields['SubscriberCustomField'][] = array( 'Key' => $k, 'Value' => $nv );
			}
			else
				$_fields['SubscriberCustomField'][] = array( 'Key' => $k, 'Value' => $v );
		}
		return $this->makeCall( $action
			, array(
				'params' => array(
					'ListID' => $list_id
					, 'Email' => $email
					, 'Name' => $name
					, 'CustomFields' => $_fields
				)
			)
		);
	}
	
	/**
	* Same as subscriberAddRedundant() except with CustomFields.
	*
	* @param string $email Email address.
	* @param string $name User's name.
	* @param int $list_id (Optional) A valid List ID to check against. If not given, the default class property is used.
	* @return mixed A parsed response from the server, or null if something failed.
	*/
	function subscriberAddWithCustomFieldsRedundant( $email, $name, $fields, $list_id = null )
	{
		$added = $this->subscriberAddWithCustomFields( $email, $name, $fields, $list_id );
		if ( $added && $added['Code'] == '0' )
		{
			$subscribed = $this->subscribersGetIsSubscribed( $email );
			if ( $subscribed == 'False' )
			{
				$added = $this->subscriberAddWithCustomFields( $email, $name, $fields, $list_id, true );
				return $added;
			}
		}
		
		return $added;
	}
	
	/**
	* @param string $email Email address.
	* @param int $list_id (Optional) A valid List ID to check against. If not given, the default class property is used.
	* @param boolean $check_subscribed If true, does the Subscribers.GetIsSubscribed API method instead.
	* @return mixed A parsed response from the server, or null if something failed.
	* @see http://www.campaignmonitor.com/api/Subscriber.Unsubscribe.aspx
	*/
	function subscriberUnsubscribe( $email, $list_id = null, $check_subscribed = false )
	{
		if ( !$list_id )
			$list_id = $this->list_id;
		
		$action = 'Subscriber.Unsubscribe';
		if ( $check_subscribed ) $action = 'Subscribers.GetIsSubscribed';
		
		return $this->makeCall( $action
			, array(
				'params' => array(
					'ListID' => $list_id
					, 'Email' => $email
				)
			)
		);
	}
	
	/**
	* @return string A parsed response from the server, or null if something failed.
	* @see http://www.campaignmonitor.com/api/Subscribers.GetIsSubscribed.aspx
	*/
	function subscribersGetIsSubscribed( $email, $list_id = null )
	{
		return $this->subscriberUnsubscribe( $email, $list_id, true );
	}
	
	/**
	* Given an array of lists, indicate whether the $email is subscribed to each of those lists.
	*
	* @param string $email User's email
	* @param mixed $lists An associative array of lists to check against. Each key should be a List ID
	* @param boolean $no_assoc If true, only returns an array where each value indicates that the user is subscribed
	*        to that particular list. Otherwise, returns a fully associative array of $list_id => true | false.
	* @return mixed An array corresponding to $lists where true means the user is subscribed to that particular list.
	*/
	function checkSubscriptions( $email, $lists, $no_assoc = true )
	{
		$nlist = array();
		foreach ( $lists as $lid => $misc )
		{
			$val = $this->subscribersGetIsSubscribed( $email, $lid );
			$val = $val != 'False';
			if ( $no_assoc && $val ) $nlist[] = $lid;
			elseif ( !$no_assoc ) $nlist[$lid] = $val;
		}
		
		return $nlist;
	}
	
	/**
	* @param string $email Email address.
	* @param string $name User's name.
	* @param int $list_id (Optional) A valid List ID to check against. If not given, the default class property is used.
	* @see http://www.campaignmonitor.com/api/Subscriber.AddAndResubscribe.aspx
	*/
	
	function subscriberAddAndResubscribe( $email, $name, $list_id = null )
	{
		return $this->subscriberAdd( $email, $name, $list_id, true );
	}
	
	/**
	* @param string $email Email address.
	* @param string $name User's name.
	* @param mixed $fields Should only be a single-dimension array of key-value pairs.
	* @param int $list_id (Optional) A valid List ID to check against. If not given, the default class property is used.
	* @param boolean $resubscribe If true, does an equivalent 'AndResubscribe' API method.
	* @return mixed A parsed response from the server, or null if something failed.
	* @see http://www.campaignmonitor.com/api/Subscriber.AddAndResubscribeWithCustomFields.aspx
	*/
	
	function subscriberAddAndResubscribeWithCustomFields( $email, $name, $fields, $list_id = null )
	{
		return $this->subscriberAddWithCustomFields( $email, $name, $fields, $list_id, true );
	}

	/**
	 * Returns the details of a particular subscriber.
	 * @param $list_id The ID of the list to which the subscriber belongs
	 * @param $email The subscriber's email address
	 * @return mixed A parsed response from the server, or null if something failed
	 * @see http://www.campaignmonitor.com/api/method/subscribers-get-single-subscriber/
	 */
	function subscriberGetSingleSubscriber($list_id = null, $email)
    {
        if (!$list_id != null)
            $list_id = $this->list_id;

        return $this->makeCall( 
        	'Subscribers.GetSingleSubscriber',
            array(
                'params' => array(
                    'ListID' => $list_id,
                    'EmailAddress' => $email
                )
            )
        );
    }	

    /*
	* A generic wrapper to feed Client.* calls.
	*
	* @param string $method The API method to call.
	* @param int $client_id (Optional) A valid Client ID to check against. If not given, the default class property is used.
	* @return mixed A parsed response from the server, or null if something failed.
	*/
	
	function clientGeneric( $method, $client_id = null )
	{
		if ( !$client_id )
			$client_id = $this->client_id;
		
		return $this->makeCall( 'Client.' . $method
			, array(
				'params' => array(
					'ClientID' => $client_id
				)
			)
		);
	}
	
	/**
	* @param int $client_id (Optional) A valid Client ID to check against. If not given, the default class property is used.
	* @return mixed A parsed response from the server, or null if something failed.
	* @see http://www.campaignmonitor.com/api/Client.GetLists.aspx
	*/
	
	function clientGetLists( $client_id = null )
	{
		return $this->clientGeneric( 'GetLists', $client_id );
	}
	
	/**
	* Creates an associative array with list_id => List_label pairings.
	*
	* @param int $client_id (Optional) A valid Client ID to check against. If not given, the default class property is used.
	*/
	
	function clientGetListsDropdown( $client_id = null )
	{
		$lists = $this->clientGetLists( $client_id );
		if ( !isset( $lists['List'] ) )
			return null;
		else
			$lists = $lists['List'];
		
		$_lists = array();
		
		if ( isset( $lists[0] ) )
		{
			foreach ( $lists as $list )
				$_lists[$list['ListID']] = $list['Name'];
		}
		else
			$_lists[$lists['ListID']] = $lists['Name'];
		
		return $_lists;
	}
	
	/**
	* Creates an associative array with list_id:List_Label => (list_id) List_label pairings.
	* Remember that you'll need to split the key on ':' only once to get the appropriate ListID
	* and Segment Name.
	*
	* @param int $client_id (Optional) A valid Client ID to check against. If not given, the default class property is used.
	*/
	
	function clientGetSegmentsDropdown( $client_id = null )
	{
		$lists = $this->clientGetSegments( $client_id );
		if ( !isset( $lists['List'] ) )
			return null;
		else
			$lists = $lists['List'];
		
		$_lists = array();
		
		if ( isset( $lists[0] ) )
		{
			foreach ( $lists as $list )
				$_lists[$list['ListID'].':'.$list['Name']] = '(' . $list['ListID'] . ') ' . $list['Name'];
		}
		else
			$_lists[$lists['ListID'].':'.$lists['Name']] = '(' . $lists['ListID'] . ') ' . $lists['Name'];
		
		return $_lists;
	}
	
	/**
	* @param int $client_id (Optional) A valid Client ID to check against. If not given, the default class property is used.
	* @return mixed A parsed response from the server, or null if something failed.
	* @see http://www.campaignmonitor.com/api/Client.GetCampaigns.aspx
	*/
	
	function clientGetCampaigns( $client_id = null )
	{
		return $this->clientGeneric( 'GetCampaigns', $client_id );
	}
	
	/**
	* @param int $client_id (Optional) A valid Client ID to check against. If not given, the default class property is used.
	* @return mixed A parsed response from the server, or null if something failed.
	* @see http://www.campaignmonitor.com/api/Client.GetSegments.aspx
	*/
	
	function clientGetSegments( $client_id = null )
	{
		return $this->clientGeneric( 'GetSegments', $client_id );
	}
	
	/**
	* @param int $client_id (Optional) A valid Client ID to check against. If not given, the default class property is used.
	* @return mixed A parsed response from the server, or null if something failed.
	* @see http://www.campaignmonitor.com/api/method/client-getsuppressionlist/
	*/
	function clientGetSuppressionList( $client_id = null )
	{
		return $this->clientGeneric( 'GetSuppressionList', $client_id );
	}

	/**
	* @param int $client_id (Optional) A valid Client ID to check against. If not given, the default class property is used.
	* @return mixed A parsed response from the server, or null if something failed.
	* @see http://www.campaignmonitor.com/api/method/client-gettemplates/
	*/
	function clientGetTemplates( $client_id = null )
	{
		return $this->clientGeneric( 'GetTemplates', $client_id );
	}
	
	/**
	* @param int $client_id (Optional) A valid Client ID to check against. If not given, the default class property is used.
	* @return mixed A parsed response from the server, or null if something failed.
	* @see http://www.campaignmonitor.com/api/method/client-getdetail/
	*/
	function clientGetDetail( $client_id = null )
	{
		return $this->clientGeneric( 'GetDetail', $client_id );
	}
	
	/**
	* @param string $companyName (CompanyName) Company name of the client to be added
	* @param string $contactName (ContactName) Contact name of the client to be added
	* @param string $emailAddress (EmailAddress) Email Address of the client to be added
	* @param string $country (Country) Country of the client to be added
	* @param string $timezone (Timezone) Timezone of the client to be added
	* @return mixed A parsed response from the server, or null if something failed.
	* @see http://www.campaignmonitor.com/api/method/client-create/
	*/
	function clientCreate( $companyName, $contactName, $emailAddress, $country, $timezone )
	{
		return $this->makeCall( 'Client.Create'
			, array(
				'params' => array(
					'CompanyName' => $companyName
					, 'ContactName' => $contactName
					, 'EmailAddress' => $emailAddress
					, 'Country' => $country
					, 'Timezone' => $timezone
				)
			)
		);
	}
	
	/**
	* @param int $client_id (ClientID) ID of the client to be updated
	* @param string $companyName (CompanyName) Company name of the client to be updated
	* @param string $contactName (ContactName) Contact name of the client to be updated
	* @param string $emailAddress (EmailAddress) Email Address of the client to be updated
	* @param string $country (Country) Country of the client to be updated
	* @param string $timezone (Timezone) Timezone of the client to be updated
	* @return mixed A parsed response from the server, or null if something failed.
	* @see http://www.campaignmonitor.com/api/method/client-create/
	*/
	function clientUpdateBasics( $client_id, $companyName, $contactName, $emailAddress, $country, $timezone )
	{
		return $this->makeCall( 'Client.UpdateBasics'
			, array(
				'params' => array(
					'ClientID' => $client_id
					, 'CompanyName' => $companyName
					, 'ContactName' => $contactName
					, 'EmailAddress' => $emailAddress
					, 'Country' => $country
					, 'Timezone' => $timezone
				)
			)
		);
	}
	
	/**
	* @param int $client_id (ClientID) ID of the client to be updated
	* @param string $accessLevel (AccessLevel) AccessLevel of the client
	* @param string $username (Username) Clients username
	* @param string $password (Password) Password of the client
	* @param string $billingType (BillingType) BillingType that the client will be set as
	* @param string $currency (Currency) Currency that the client will pay in
	* @param string $deliveryFee (DeliveryFee) Per campaign deliivery fee for the campaign
	* @param string $costPerRecipient (CostPerRecipient) Per email fee for the client
	* @param string $designAndSpamTestFee (DesignAndSpamTestFee) Amount the client will
	*				be charged if they have access to send design/spam tests
	* @return mixed A parsed response from the server, or null if something failed.
	* @see http://www.campaignmonitor.com/api/method/client-updateaccessandbilling/
	*/
	function clientUpdateAccessAndBilling( $client_id, $accessLevel, $username, $password, $billingType, $currency, $deliveryFee, $costPerRecipient, $designAndSpamTestFee )
	{
		return $this->makeCall( 'Client.UpdateAccessAndBilling'
			, array(
				'params' => array(
					'ClientID' => $client_id
					, 'AccessLevel' => $accessLevel
					, 'Username' => $username
					, 'Password' => $password
					, 'BillingType' => $billingType
					, 'Currency' => $currency
					, 'DeliveryFee' => $deliveryFee
					, 'CostPerRecipient' => $costPerRecipient
					, 'DesignAndSpamTestFee' => $designAndSpamTestFee
				)
			)
		);
	}
	
	
	
	/**
	* @return mixed A parsed response from the server, or null if something failed.
	* @see http://www.campaignmonitor.com/api/User.GetClients.aspx
	*/
	
	function userGetClients()
	{
		return $this->makeCall( 'User.GetClients' );
	}
	
	/**
	* @return string A parsed response from the server, or null if something failed.
	* @see http://www.campaignmonitor.com/api/User.GetSystemDate.aspx
	*/
	
	function userGetSystemDate()
	{
		return $this->makeCall( 'User.GetSystemDate' );
	}
	
	/**
	* @return string A parsed response from the server, or null if something failed.
	* @see http://www.campaignmonitor.com/api/method/user-gettimezones/
	*/
	
	function userGetTimezones()
	{
		return $this->makeCall( 'User.GetTimezones' );
	}
	
	/**
	* @return string A parsed response from the server, or null if something failed.
	* @see http://www.campaignmonitor.com/api/method/user-getcountries/
	*/
	
	function userGetCountries()
	{
		return $this->makeCall( 'User.GetCountries' );
	}
	
	/**
	 * Gets the API key for a Campaign Monitor user, given site URL, username, 
	 * password. If the user has not already had their API key generated at 
	 * the time this method is called, the user’s API key will be generated 
	 * and returned by this method.
	 * 
	 * @param $site_url The base URL of the site you use to login to 
	 * Campaign Monitor. e.g. http://example.createsend.com/
	 * @param $username The username you use to login to Campaign Monitor.
	 * @param $password The password you use to login to Campaign Monitor.
	 * @return mixed A parsed response from the server, or null if something 
	 * failed.
	 * @see http://www.campaignmonitor.com/api/method/user-getapikey/
	 */
	function userGetApiKey($site_url, $username, $password)
	{
		return $this->makeCall(
			'User.GetApiKey', 
			array(
				'params' => array(
					'SiteUrl' => $site_url,
					'Username' => $username,
					'Password' => $password,
				)
			)
		);
	}
	
	/**
	* A generic wrapper to feed Campaign.* calls.
	*
	* @param string $method The API method to call.
	* @param int $campaign_id (Optional) A valid Campaign ID to check against. If not given, the default class property is used.
	* @return mixed A parsed response from the server, or null if something failed.
	*/
	
	function campaignGeneric( $method, $campaign_id = null )
	{
		if ( !$campaign_id )
			$campaign_id = $this->campaign_id;
		
		return $this->makeCall( 'Campaign.' . $method
			, array(
				'params' => array(
					'CampaignID' => $campaign_id
				)
			)
		);
	}
	
	/**
	* @param int $campaign_id (Optional) A valid Campaign ID to check against. If not given, the default class property is used.
	* @return mixed A parsed response from the server, or null if something failed.
	* @see http://www.campaignmonitor.com/api/Campaign.GetSummary.aspx
	*/
	
	function campaignGetSummary( $campaign_id = null )
	{
		return $this->campaignGeneric( 'GetSummary', $campaign_id );
	}
	
	/**
	* @param int $campaign_id (Optional) A valid Campaign ID to check against. If not given, the default class property is used.
	* @return mixed A parsed response from the server, or null if something failed.
	* @see http://www.campaignmonitor.com/api/Campaign.GetOpens.aspx
	*/
	
	function campaignGetOpens( $campaign_id = null )
	{
		return $this->campaignGeneric( 'GetOpens', $campaign_id );
	}
	
	/**
	* @param int $campaign_id (Optional) A valid Campaign ID to check against. If not given, the default class property is used.
	* @return mixed A parsed response from the server, or null if something failed.
	* @see http://www.campaignmonitor.com/api/Campaign.GetBounces.aspx
	*/
	
	function campaignGetBounces( $campaign_id = null )
	{
		return $this->campaignGeneric( 'GetBounces', $campaign_id );
	}
	
	/**
	* @param int $campaign_id (Optional) A valid Campaign ID to check against. If not given, the default class property is used.
	* @return mixed A parsed response from the server, or null if something failed.
	* @see http://www.campaignmonitor.com/api/Campaign.GetSubscriberClicks.aspx
	*/
	
	function campaignGetSubscriberClicks( $campaign_id = null )
	{
		return $this->campaignGeneric( 'GetSubscriberClicks', $campaign_id );
	}
	
	/**
	* @param int $campaign_id (Optional) A valid Campaign ID to check against. If not given, the default class property is used.
	* @return mixed A parsed response from the server, or null if something failed.
	* @see http://www.campaignmonitor.com/api/Campaign.GetUnsubscribes.aspx
	*/
	
	function campaignGetUnsubscribes( $campaign_id = null )
	{
		return $this->campaignGeneric( 'GetUnsubscribes', $campaign_id );
	}
	
	/**
	* @param int $campaign_id (Optional) A valid Campaign ID to check against. If not given, the default class property is used.
	* @return mixed A parsed response from the server, or null if something failed.
	* @see http://www.campaignmonitor.com/api/Campaign.GetLists.aspx
	*/
	
	function campaignGetLists( $campaign_id = null )
	{
		return $this->campaignGeneric( 'GetLists', $campaign_id );
	}
	
	/**
	* @param int $client_id The ClientID you wish to use; set it to null to use the default class property.
	* @param string $name (CampaignName) Name of campaign
	* @param string $subject (CampaignSubject) Subject of campaign mailing
	* @param string $fromName (FromName) The From name of the sender
	* @param string $fromEmail (FromEmail) The email of the sender
	* @param string $replyTo (ReplyTo) An alternate email to send replies to
	* @param string $htmlUrl (HtmlUrl) Location of HTML body of email
	* @param string $textUrl (TextUrl) Location of plaintext body of email
	* @param array $subscriberListIds (SubscriberListIDs) An array of ListIDs. This will automatically be converted to the right format
	* @param array $listSegments (ListSegments) An array of segment names and their corresponding ListIDs. Each element needs to
	*        be an associative array with keys ListID and Name.
	* @return mixed A parsed response from the server, or null if something failed.
	* @see http://www.campaignmonitor.com/api/Campaign.Create.aspx
	*/
	
	function campaignCreate( $client_id, $name, $subject, $fromName, $fromEmail, $replyTo, $htmlUrl, $textUrl, $subscriberListIds, $listSegments )
	{
		if ( $client_id == null )
			$client_id = $this->client_id;
		
		$_subListIds = '';
		if ($subscriberListIds != "")
		{
			$_subListIds = array( 'string' => array() );
			if ( is_array( $subscriberListIds ) )
			{
				foreach ( $subscriberListIds as $lid )
				{
					$_subListIds['string'][] = $lid;
				}
			}
		}
		
		$_seg = '';
		if ($listSegments != "")
		{
			$_seg = array( 'List' => array() );
			if ( is_array( $listSegments ) )
			{
				foreach ( $listSegments as $seg )
					$_seg['List'][] = $seg;
			}
		}
		
		return $this->makeCall( 'Campaign.Create', array(
			'params' => array(
				'ClientID' => $client_id
				, 'CampaignName' => $name
				, 'CampaignSubject' => $subject
				, 'FromName' => $fromName
				, 'FromEmail' => $fromEmail
				, 'ReplyTo' => $replyTo
				, 'HtmlUrl' => $htmlUrl
				, 'TextUrl' => $textUrl
				, 'SubscriberListIDs' => $_subListIds
				, 'ListSegments' => $_seg
				)
			)
		);
	}
	
	/**
	* @param int $client_id The CampaignID you wish to use; set it to null to use the default class property
	* @param string $confirmEmail (ConfirmationEmail) Email address to send confirmation of campaign send to
	* @param string $sendDate (SendDate) The timestamp to send the campaign. It must be formatted as YYY-MM-DD HH:MM:SS 
	*               and should correspond to user's timezone.
	*/
	
	function campaignSend( $campaign_id, $confirmEmail, $sendDate )
	{
		if ( $campaign_id == null )
			$campaign_id = $this->campaign_id;
		
		return $this->makeCall( 'Campaign.Send', array(
			'params' => array(
				'CampaignID' => $campaign_id
				, 'ConfirmationEmail' => $confirmEmail
				, 'SendDate' => $sendDate
				)
			)
		);
	}

	/**
	 * Delete a campaign.
	 * @param $campaign_id The ID of the campaign to delete.
	 * @return A Status code indicating success or failure.
	 * @see http://www.campaignmonitor.com/api/method/campaign-delete/
	 */
	function campaignDelete($campaign_id)
	{
		return $this->campaignGeneric('Delete', $campaign_id);
	}

	/**
	* @param int $client_id (ClientID) ID of the client the list will be created for
	* @param string $title (Title) Name of the new list
	* @param string $unsubscribePage (UnsubscribePage) URL of the page users will be 
	*				directed to when they unsubscribe from this list.
	* @param string $confirmOptIn (ConfirmOptIn) If true, the user will be sent a confirmation
	*				email before they are added to the list. If they click the link to confirm
	*				their subscription they will be added to the list. If false, they will be
	*				added automatically.
	* @param string $confirmationSuccessPage (ConfirmationSuccessPage) URL of the page that
	*				users will be sent to if they confirm their subscription. Only required when
					$confirmOptIn is true.
	* @see http://www.campaignmonitor.com/api/method/list-create/
	*/
	function listCreate( $client_id, $title, $unsubscribePage, $confirmOptIn, $confirmationSuccessPage )
	{
		if ( $confirmOptIn == 'false' )
			$confirmationSuccessPage = '';
			
		return $this->makeCall( 'List.Create', array(
			'params' => array(
				'ClientID' => $client_id
				, 'Title' => $title
				, 'UnsubscribePage' => $unsubscribePage
				, 'ConfirmOptIn' => $confirmOptIn
				, 'ConfirmationSuccessPage' => $confirmationSuccessPage
				)
			)
		);
	}
	
	/**
	* @param int $list_id (List) ID of the list to be updated
	* @param string $title (Title) Name of the new list
	* @param string $unsubscribePage (UnsubscribePage) URL of the page users will be 
	*				directed to when they unsubscribe from this list.
	* @param string $confirmOptIn (ConfirmOptIn) If true, the user will be sent a confirmation
	*				email before they are added to the list. If they click the link to confirm
	*				their subscription they will be added to the list. If false, they will be
	*				added automatically.
	* @param string $confirmationSuccessPage (ConfirmationSuccessPage) URL of the page that
	*				users will be sent to if they confirm their subscription. Only required when
					$confirmOptIn is true.
	* @see http://www.campaignmonitor.com/api/method/list-update/
	*/
	function listUpdate( $list_id, $title, $unsubscribePage, $confirmOptIn, $confirmationSuccessPage )
	{
		if ( $confirmOptIn == 'false' )
			$confirmationSuccessPage = '';
			
		return $this->makeCall( 'List.Update', array(
			'params' => array(
				'ListID' => $list_id
				, 'Title' => $title
				, 'UnsubscribePage' => $unsubscribePage
				, 'ConfirmOptIn' => $confirmOptIn
				, 'ConfirmationSuccessPage' => $confirmationSuccessPage
				)
			)
		);
	}
	
	/**
	* @param int $list_id (List) ID of the list to be deleted
	* @see http://www.campaignmonitor.com/api/method/list-delete/
	*/
	function listDelete( $list_id )
	{			
		return $this->makeCall( 'List.Delete', array(
			'params' => array(
				'ListID' => $list_id
				)
			)
		);
	}
	
	/**
	* @param int $list_id (List) ID of the list to be deleted
	* @see http://www.campaignmonitor.com/api/method/list-getdetail/
	*/
	function listGetDetail( $list_id )
	{			
		return $this->makeCall( 'List.GetDetail', array(
			'params' => array(
				'ListID' => $list_id
				)
			)
		);
	}

	/**
	 * Gets statistics for a subscriber list
	 * @param $list_id The ID of the list whose statistics will be returned.
	 * @return mixed A parsed response from the server, or null if something 
	 * @see http://www.campaignmonitor.com/api/method/list-getstats/
	 */
	function listGetStats($list_id)
	{
		return $this->makeCall(
			'List.GetStats',
			array(
				'params' => array(
					'ListID' => $list_id
				)
			)
		);
	}
	
	/**
	* @param int $list_id (ListID) A valid list ID to check against. 
	* @param string $fieldName (FieldName) Name of the new custom field
	* @param string $dataType (DataType) Data type of the field. Options are Text, Number, 
	*				MultiSelectOne, or MultiSelectMany
	* @param string $Options (Options) The available options for a multi-valued custom field. 
	*				Options should be separated by a double pipe “||”. This field must be null 
	*				for Text and Number custom fields
	* @return mixed A parsed response from the server, or null if something failed.
	* @see http://www.campaignmonitor.com/api/method/list-createcustomfield/
	*/
	
	function listCreateCustomField( $list_id, $fieldName, $dataType, $options )
	{
		if ( $dataType == 'Text' || $dataType == 'Number' )
			$options = null;
			
		return $this->makeCall( 'List.CreateCustomField', array(
			'params' => array(
				'ListID' => $list_id
				, 'FieldName' => $fieldName
				, 'DataType' => $dataType
				, 'Options' => $options
				)
			)
		);
	}
	
	/**
	* @param int $list_id (ListID) A valid list ID to check against. 
	* @return mixed A parsed response from the server, or null if something failed.
	* @see http://www.campaignmonitor.com/api/method/list-getcustomfields/
	*/
	
	function listGetCustomFields( $list_id )
	{			
		return $this->makeCall( 'List.GetCustomFields', array(
			'params' => array(
				'ListID' => $list_id
				)
			)
		);
	}
	
	/**
	* @param int $list_id (ListID) A valid list ID to check against. 
	* @param int $key (Key) The Key of the field we want to delete. 
	* @return mixed A parsed response from the server, or null if something failed.
	* @see http://www.campaignmonitor.com/api/method/list-deletecustomfield/
	*/
	
	function listDeleteCustomField( $list_id, $key )
	{		
		return $this->makeCall( 'List.DeleteCustomField', array(
			'params' => array(
				'ListID' => $list_id
				, 'Key' => $key
				)
			)
		);
	}
	
	/**
	 * @param int $client_id (ClientID) ID of the client the template will be created for
	 * @param string $template_name (TemplateName) Name of the new template
	 * @param string $html_url (HTMLPageURL) URL of the HTML page you have created for the template
	 * @param string $zip_url (ZipFileURL) URL of a zip file containing any other files required by the template
	 * @param string $screenshot_url (ScreenshotURL) URL of a screenshot of the template
	 * @see http://www.campaignmonitor.com/api/method/template-create/
	 */
	function templateCreate($client_id, $template_name, $html_url, $zip_url, $screenshot_url)
	{
		return $this->makeCall('Template.Create', array(
			'params' => array(
				'ClientID' => $client_id,
				'TemplateName' => $template_name,
				'HTMLPageURL' => $html_url,
				'ZIPFileURL' => $zip_url,
				'ScreenshotURL' => $screenshot_url
			))
		);
	}
	
	/**
	 * @param string $template_id (TemplateID) ID of the template whose details are being requested
	 * @see http://www.campaignmonitor.com/api/method/template-getdetail/
	 */
	function templateGetDetail($template_id)
	{
		return $this->makeCall('Template.GetDetail', array(
			'params' => array(
				'TemplateID' => $template_id
			))
		);
	}

	/**
	 * @param string $template_id (TemplateID) ID of the template to be updated
	 * @param string $template_name (TemplateName) Name of the template
	 * @param string $html_url (HTMLPageURL) URL of the HTML page you have created for the template
	 * @param string $zip_url (ZipFileURL) URL of a zip file containing any other files required by the template
	 * @param string $screenshot_url (ScreenshotURL) URL of a screenshot of the template
	 * @see http://www.campaignmonitor.com/api/method/template-update/
	 */
	function templateUpdate($template_id, $template_name, $html_url, $zip_url, $screenshot_url)
	{
		return $this->makeCall('Template.Update', array(
			'params' => array(
				'TemplateID' => $template_id,
				'TemplateName' => $template_name,
				'HTMLPageURL' => $html_url,
				'ZIPFileURL' => $zip_url,
				'ScreenshotURL' => $screenshot_url
			))
		);
	}

	/**
	 * @param string $template_id (TemplateID) ID of the template to be deleted
	 * @see http://www.campaignmonitor.com/api/method/template-delete/
	 */
	function templateDelete($template_id)
	{
		return $this->makeCall('Template.Delete', array(
			'params' => array(
				'TemplateID' => $template_id
			))
		);
	}
}

?>