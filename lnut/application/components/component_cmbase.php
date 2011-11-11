<?php
/**
* LICENSE
* -------------------
* Copyright (c) 2007-2009, Kaiser Shahid <knitcore@yahoo.com> and
* Campaign Monitor <support@campaignmonitor.com>
* All rights reserved.
*
* This software is licensed under the BSD License:
*
* Redistribution and use in source and binary forms, with or without
* modification, are permitted provided that the following conditions are met:
*     * Redistributions of source code must retain the above copyright
*       notice, this list of conditions and the following disclaimer.
*     * Redistributions in binary form must reproduce the above copyright
*       notice, this list of conditions and the following disclaimer in the
*       documentation and/or other materials provided with the distribution.
*     * Neither the name of Kaiser Shahid or Campaign Monitor nor the
*       names of its contributors may be used to endorse or promote products
*       derived from this software without specific prior written permission.
*
* THIS SOFTWARE IS PROVIDED BY Kaiser Shahid "AS IS" AND ANY
* EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
* WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
* DISCLAIMED. IN NO EVENT SHALL Kaiser Shahid BE LIABLE FOR ANY
* DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
* (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
* LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
* ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
* (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
* SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*
* @package CampaignMonitorLib
*/

/**
* This is an all-inclusive package for interfacing with Campaign Monitor's services. It
* supports SOAP, GET, and POST seamlessly (just set the $method property to 'soap', 'get', 
* or 'post' before making a call) and always returns the same view of data regardless of
* the method used to call the service.
*
* See README for more information on usage and details.
*
* CHANGES: 2008-04-28
* -------------------
* - Now compatible with PHP4. Biggest changes include removing reliance on
*   enhanced OOP syntax, and using XML Parser functions instead of SimpleXML.
* - Base class (CMBase) branches into CampaignMonitor and MailBuild
* - CMBase contains all the shared API calls (and extended functionality 
*   related to those) between both classes.
*
* @package CampaignMonitorLib
* @subpackage CMBase
* @version 1.4.3
* @author Kaiser Shahid <knitcore@yahoo.com> (www.qaiser.net)
* @copyright 2007-2009
* @see http://www.campaignmonitor.com/api/
*/

define( 'PHPVER', phpversion() );

// WARNING: this is needed to keep the socket from apparently hanging (even when it should be done reading)
// NOTE: using a timeout (SOCKET_TIMEOUT) that's passed when calling fsockopen. safer thing to do.
//ini_set( 'default_socket_timeout', 1 );
define( 'SOCKET_TIMEOUT', 1 );

class component_cmbase
{
	var /*@ protected */
		$api = ''
		, $campaign_id = 0
		, $client_id = 0
		, $list_id = 0
	;
	
	var /*@ public */
		$method = 'get'
		, $url = ''
		, $soapAction = ''
		, $curl = true
		, $curlExists = true
	;
	
	// debugging options
	var /*@ public */
		$debug_level = 0
		, $debug_request = ''
		, $debug_response = ''
		, $debug_url = ''
		, $debug_info = array()
		, $show_response_headers = 0
	;
	
	/**
	* @param string $api Your API key.
	* @param string $client The default ClientId you're going to work with.
	* @param string $campaign The default CampaignId you're going to work with.
	* @param string $list The default ListId you're going to work with.
	* @param string $method Determines request type. Values are either get, post, or soap.
	*/
	function component_cmbase( $api = null, $client = null, $campaign = null, $list = null, $method = 'get' )
	{
		$this->api = $api;
		$this->client_id = $client;
		$this->campaign_id = $campaign;
		$this->list_id = $list;
		$this->method = $method;
		$this->curlExists = function_exists( 'curl_init' ) && function_exists( 'curl_setopt' );
	}
	
	/**
	* The direct way to make an API call. This allows developers to include new API
	* methods that might not yet have a wrapper method as part of the package.
	*
	* @param string $action The API call.
	* @param array $options An associative array of values to send as part of the request.
	* @return array The parsed XML of the request.
	*/
	function makeCall( $action = '', $options = array() )
	{
		// NEW [2008-06-24]: switch to soap automatically for these calls
		$old_method = $this->method;
		if ( $action == 'Subscriber.AddWithCustomFields' || $action == 'Subscriber.AddAndResubscribeWithCustomFields' || $action == 'Campaign.Create')
			$this->method = 'soap';
		
		if ( !$action ) return null;
		$url = $this->url;
		
		// DONE: like facebook's client, allow for get/post through the file wrappers
		// if curl isn't available. (or maybe have curl-emulating functions defined 
		// at the bottom of this script.)
		
		//$ch = curl_init();
		if ( !isset( $options['header'] ) )
			$options['header'] = array();
		
		$options['header'][] = 'User-Agent: CMBase URL Handler 1.5';
		
		$postdata = '';
		$method = 'GET';
		
		if ( $this->method == 'soap' )
		{
			$options['header'][] = 'Content-Type: text/xml; charset=utf-8';
			$options['header'][] = 'SOAPAction: "' . $this->soapAction . $action . '"';
			
			$postdata = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
			$postdata .= "<soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"";
			$postdata .= " xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\"";
			$postdata .= " xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\">\n";
			$postdata .= "<soap:Body>\n";
			$postdata .= "	<{$action} xmlns=\"{$this->soapAction}\">\n";
			$postdata .= "		<ApiKey>{$this->api}</ApiKey>\n";
			
			if ( isset( $options['params'] ) )
				$postdata .= $this->array2xml( $options['params'], "\t\t" );
			
			$postdata .= "	</{$action}>\n";
			$postdata .= "</soap:Body>\n";
			$postdata .= "</soap:Envelope>";
			
			$method = 'POST';
			
			//curl_setopt( $ch, CURLOPT_POST, 1 );
			//curl_setopt( $ch, CURLOPT_POSTFIELDS, $postdata );
		}
		else
		{
			$postdata = "ApiKey={$this->api}";
			$url .= "/{$action}";
			
			// NOTE: since this is GET, the assumption is that params is a set of simple key-value pairs.
			if ( isset( $options['params'] ) )
			{
				foreach ( $options['params'] as $k => $v )
					$postdata .= '&' . $k . '=' .rawurlencode(utf8_encode($v));
			}
			
			if ( $this->method == 'get' )
			{
				$url .= '?' . $postdata;
				$postdata = '';
			}
			else
			{
 				$options['header'][] = 'Content-Type: application/x-www-form-urlencoded';
				$method = 'POST';
				//curl_setopt( $ch, CURLOPT_POST, 1 );
				//curl_setopt( $ch, CURLOPT_POSTFIELDS, $postdata );
			}
		}
					
		$res = '';
		
		// WARNING: using fopen() does not recognize stream contexts in PHP 4.x, so
		// my guess is using fopen() in PHP 4.x implies that POST is not supported
		// (otherwise, how do you tell fopen() to use POST?). tried fsockopen(), but
		// response time was terrible. if someone has more experience with working
		// directly with streams, please troubleshoot that.
		// NOTE: fsockopen() needs a small timeout to force the socket to close.
		// it's defined in SOCKET_TIMEOUT. 
		
		// preferred method is curl, only if it exists and $this->curl is true.
		if ( $this->curl && $this->curlExists )
		{
			$ch = curl_init();
			if ( $this->method != 'get' )
			{
				curl_setopt( $ch, CURLOPT_POST, 1 );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $postdata );
			}
			
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, $options['header'] );
			curl_setopt( $ch, CURLOPT_HEADER, $this->show_response_headers );
			
			// except for the response, all other information will be stored when debugging is on.
			$res = curl_exec( $ch );
			if ( $this->debug_level )
			{
				$this->debug_url = $url;
				$this->debug_request = $postdata;
				$this->debug_info = curl_getinfo( $ch );
				$this->debug_info['headers_sent'] = $options['header'];
			}
			$this->debug_response = $res;
			curl_close( $ch );
		}
		else
		{
			// 'header' is actually the entire HTTP payload. as such, you need
			// Content-Length header, otherwise you'll get errors returned/emitted.
			
			$postLen = strlen( $postdata );
			$ctx = array(
				'method' => $method
				, 'header' => implode( "\n", $options['header'] ) 
					. "\nContent-Length: " . $postLen
					. "\n\n" . $postdata
			);
			
			if ( $this->debug_level )
			{
				$this->debug_url = $url;
				$this->debug_request = $postdata;
				$this->debug_info['overview'] = 'Used stream_context_create()/fopen() to make request. Content length=' . $postLen;
				$this->debug_info['headers_sent'] = $options['header'];
				//$this->debug_info['complete_content'] = $ctx;
			}
			
			$pv = PHPVER;
			
			// the preferred non-cURL way if user is using PHP 5.x
			if ( $pv{0} == '5' )
			{
				$context = stream_context_create( array( 'http' => $ctx ) );
				$fp = fopen( $url, 'r', false, $context );
				ob_start();
				fpassthru( $fp );
				fclose( $fp );
				$res = ob_get_clean();
			}
			else
			{
				// this should work with PHP 4, but it seems to take forever to get data back this way
				// NOTE: setting the default_socket_timeout seems to alleviate this issue [finally].
				list( $protocol, $url ) = explode( '//', $url, 2 );
				list( $domain, $path ) = explode( '/', $url, 2 );
				$fp = fsockopen( $domain, 80, $tvar, $tvar2, SOCKET_TIMEOUT );
			
				if ( $fp )
				{
					$payload = "$method /$path HTTP/1.1\n"
					 	. "Host: $domain\n"
						. $ctx['header']
					;
					fwrite( $fp, $payload );
				
					// even with the socket timeout set, using fgets() isn't playing nice, but
					// fpassthru() seems to be doing the right thing.
				
					ob_start();
					fpassthru( $fp );
					list( $headers, $res ) = explode( "\r\n\r\n", ob_get_clean(), 2 );
				
					if ( $this->debug_level )
						$this->debug_info['headers_received'] = $headers;
				
					fclose( $fp );
				}
				elseif ( $this->debug_level )
					$this->debug_info['overview'] .= "\nOpening $domain/$path failed!";
			}
		}
		
		if ( $res )
		{
			if ( $this->method == 'soap' )
			{
				$tmp = $this->xml2array( $res, '/soap:Envelope/soap:Body' );
				if ( !is_array( $tmp ) )
					return $tmp;
				else
					return $tmp[$action.'Response'][$action.'Result'];
			}
			else
				return $this->xml2array($res);
		}
		else
			return null;
	}

	/**
	 * Convert the given XML $contents into a PHP array. Based on code from:
	 * http://www.bin-co.com/php/scripts/xml2array/
	 * @param $contents The XML to be converted.
	 * @param $root The path of the root element within the XML at which 
	 * conversion should occur.
	 * @param $charset The character set to use.
	 * @param $get_attributes 0 or 1. If this is 1 the function will get the 
	 * attributes as well as the tag values - this results in a different array 
	 * structure in the return value.
	 * @param $priority Can be 'tag' or 'attribute'. This will change the structure
	 * of the resulting array. For 'tag', the tags are given more importance.
	 * @return A PHP array representing the XML $contents passed in
	 */
	function xml2array(
		$contents, 
		$root = '/',
		$charset = 'utf-8',
		$get_attributes = 0, 
		$priority = 'tag') {
	
		if(!$contents)
			return array();
	
	    if(!function_exists('xml_parser_create'))
	        return array();
	
	    // Get the PHP XML parser
	    $parser = xml_parser_create($charset);
	
	    // Attempt to find the last tag in the $root path and use this as the 
	    // start/end tag for the process of extracting the xml
		// Example input: '/soap:Envelope/soap:Body'
	
	    // Toggles whether the extraction of xml into the array actually occurs
	    $extract_on = TRUE;
	    $start_and_end_element_name = '';
		$root_elements = explode('/', $root);
		if ($root_elements != FALSE && 
			!empty($root_elements)) {
			$start_and_end_element_name = trim(end($root_elements));
			if (!empty($start_and_end_element_name))
				$extract_on = FALSE;
		}
	
	    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
	    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	    xml_parse_into_struct($parser, trim($contents), $xml_values);
	    xml_parser_free($parser);
	
	    if(!$xml_values) 
	    	return;
	
	    $xml_array = array();
	    $parents = array();
	    $opened_tags = array();
	    $arr = array();
	
	    $current = &$xml_array; // Reference
	
	    // Go through the tags.
	    $repeated_tag_index = array(); // Multiple tags with same name will be turned into an array
	    foreach($xml_values as $data) {
	        unset($attributes,$value); // Remove existing values, or there will be trouble
	
	        // This command will extract these variables into the foreach scope
	        // tag(string), type(string), level(int), attributes(array).
	        extract($data);
	
	        if (!empty($start_and_end_element_name) && 
	        	$tag == $start_and_end_element_name) {
	        	// Start at the next element (if looking at the opening tag), 
	        	// or don't process any more elements (if looking at the closing tag)...
	        	$extract_on = !$extract_on;
	        	continue;
	        }
	
	        if (!$extract_on)
	        	continue;
	        
	        $result = array();
	        $attributes_data = array();
	        
	        if(isset($value)) {
	            if($priority == 'tag') $result = $value;
	            else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
	        }
	
	        // Set the attributes too.
	        if(isset($attributes) and $get_attributes) {
	            foreach($attributes as $attr => $val) {
	                if($priority == 'tag') $attributes_data[$attr] = $val;
	                else $result['attr'][$attr] = $val; // Set all the attributes in a array called 'attr'
	            }
	        }
	
	        // See tag status and do the needed.
	        if($type == "open") {// The starting of the tag '<tag>'
	            $parent[$level-1] = &$current;
	            if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
	                $current[$tag] = $result;
	                if($attributes_data) $current[$tag. '_attr'] = $attributes_data;
	                $repeated_tag_index[$tag.'_'.$level] = 1;
	                $current = &$current[$tag];
	            } else { // There was another element with the same tag name
	                if(isset($current[$tag][0])) { // If there is a 0th element it is already an array
	                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
	                    $repeated_tag_index[$tag.'_'.$level]++;
	                } else { // This section will make the value an array if multiple tags with the same name appear together
	                    $current[$tag] = array($current[$tag],$result); // This will combine the existing item and the new item together to make an array
	                    $repeated_tag_index[$tag.'_'.$level] = 2;
	                    
	                    if(isset($current[$tag.'_attr'])) { // The attribute of the last(0th) tag must be moved as well
	                        $current[$tag]['0_attr'] = $current[$tag.'_attr'];
	                        unset($current[$tag.'_attr']);
	                    }
	                }
	                $last_item_index = $repeated_tag_index[$tag.'_'.$level]-1;
	                $current = &$current[$tag][$last_item_index];
	            }
	        } elseif($type == "complete") { // Tags that ends in 1 line '<tag />'
	            // See if the key is already taken.
	            if(!isset($current[$tag])) { //New Key
	            	// Don't insert an empty array - we don't want it!
	                if (!(is_array($result) && empty($result)))
	                	$current[$tag] = $result;
	                $repeated_tag_index[$tag.'_'.$level] = 1;
	                if($priority == 'tag' and $attributes_data) $current[$tag. '_attr'] = $attributes_data;
	
	            } else { // If taken, put all things inside a list(array)
	                if(isset($current[$tag][0]) and is_array($current[$tag])) { // If it is already an array...
	
	                    // ...push the new element into that array.
	                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
	                    
	                    if($priority == 'tag' and $get_attributes and $attributes_data) {
	                        $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
	                    }
	                    $repeated_tag_index[$tag.'_'.$level]++;
	
	                } else { // If it is not an array...
	                    $current[$tag] = array($current[$tag],$result); // ...Make it an array using using the existing value and the new value
	                    $repeated_tag_index[$tag.'_'.$level] = 1;
	                    if($priority == 'tag' and $get_attributes) {
	                        if(isset($current[$tag.'_attr'])) { // The attribute of the last(0th) tag must be moved as well
	                            
	                            $current[$tag]['0_attr'] = $current[$tag.'_attr'];
	                            unset($current[$tag.'_attr']);
	                        }
	                        
	                        if($attributes_data) {
	                            $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
	                        }
	                    }
	                    $repeated_tag_index[$tag.'_'.$level]++; // 0 and 1 index is already taken
	                }
	            }
	        } elseif($type == 'close') { // End of tag '</tag>'
	            $current = &$parent[$level-1];
	        }
	    }
	    return($xml_array);
	}  
	
	/**
	* Converts an array to XML. This is the inverse to xml2array(). Values
	* are automatically escaped with htmlentities(), so you don't need to escape 
	* values ahead of time. If you have, just set the third parameter to false.
	* This is an all-or-nothing deal.
	*
	* @param mixed $arr The associative to convert to an XML fragment
	* @param string $indent (Optional) Starting identation of each element
	* @param string $escape (Optional) Determines whether or not to escape a text node.
	* @return string An XML fragment.
	*/
	function array2xml( $arr, $indent = '', $escape = true )
	{
		$buff = '';
		
		foreach ( $arr as $k => $v )
		{
			if ( !is_array( $v ) )
				$buff .= "$indent<$k>" . ($escape ? utf8_encode( $v ) : $v ) . "</$k>\n";
			else
			{
				/*
				Encountered a list. The primary difference between the two branches is that
				in the 'if' branch, a $k element is generated for each item in $v, whereas
				in the 'else' branch, a single $k element encapsulates $v.
				*/
				
				if ( isset( $v[0] ) )
				{
					foreach ( $v as $_k => $_v )
					{
						if ( is_array( $_v ) )
					 		$buff .= "$indent<$k>\n" . $this->array2xml( $_v, $indent . "\t", $escape ) . "$indent</$k>\n";
						else
							$buff .= "$indent<$k>" . ($escape ? utf8_encode( $_v ) : $_v ) . "</$k>\n";
					}
				}
				else
					$buff .= "$indent<$k>\n" . $this->array2xml( $v, $indent . "\t", $escape ) . "$indent</$k>\n";
			}
		}
		
		return $buff;
	}
}

?>