<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<!-- saved from url=(0014)about:internet -->
<html xmlns='http://www.w3.org/1999/xhtml' lang='en' xml:lang='en'>	
    <!-- 
    Smart developers always View Source. 
    
    This application was built using Adobe Flex, an open source framework
    for building rich Internet applications that get delivered via the
    Flash Player or to desktops via Adobe AIR. 
    
    Learn more about Flex at http://flex.org 
    // -->
    <head>
        <title></title>         
        <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
		<!-- Include CSS to eliminate any default margins/padding and set the height of the html element and 
		     the body element to 100%, because Firefox, or any Gecko based browser, interprets percentage as 
			 the percentage of the height of its parent container, which has to be set explicitly.  Initially, 
			 don't display flashContent div so it won't show if JavaScript disabled.
		-->
        <style type='text/css' media='screen'> 
			html, body	{ height:100%; }
			body { margin:0; padding:0; overflow:auto; text-align:center; 
			       background-color: #000000; }   
			#flashContent { display:none; }
        </style>
		
		<!-- Enable Browser History by replacing useBrowserHistory tokens with two hyphens -->
        <!-- BEGIN Browser History required section -->
        <link rel='stylesheet' type='text/css' href='history/history.css' />
        <script type='text/javascript' src='history/history.js'></script>
        <!-- END Browser History required section -->  
		    
        <script type='text/javascript' src='swfobject.js'></script>
        <script type='text/javascript'>
            <!-- For version detection, set to min. required Flash Player version, or 0 (or 0.0.0), for no version detection. --> 
            var swfVersionStr = '0.0.0';
            var xiSwfUrlStr = '';
            
            var flashvars = {
                            param : <?php urlencode ( "[{pageId:\"supportSelection\"}]" ) ?>
							};


            var params = {};
            params.quality = 'high';
            params.bgcolor = '#FFFFFF';
            params.allowscriptaccess = 'always';
            params.allowfullscreen = 'true';
            var attributes = {};
            attributes.id = 'website';
            attributes.name = 'PreLoader';
            attributes.align = 'middle';
            swfobject.embedSWF(
                'p.swf', 'flashContent', 
                '550', '400', 
                swfVersionStr, xiSwfUrlStr, 
                flashvars, params, attributes);
                
			swfobject.createCSS('#flashContent', 'display:block;text-align:left;');
        </script>
    </head>
    <body>
        <!-- SWFObject's dynamic embed method replaces this alternative HTML content with Flash content when enough 
			 JavaScript and Flash plug-in support is available. The div is initially hidden so that it doesn't show
			 when JavaScript is disabled.
		-->
        <div id='flashContent'>
        </div>
	   		
   </body>
</html>
