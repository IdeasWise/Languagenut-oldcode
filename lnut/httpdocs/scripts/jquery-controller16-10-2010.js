/**
 * Functions
 */
if ( window.location.host == '127.0.0.1')
var url = window.location.protocol + "//" + window.location.host + "/languagenut/";
else
var url = window.location.protocol + "//" + window.location.host + "/";

//var url = window.location.protocol + "//" + window.location.host + "/leaf_mgmt/SRC/leafs_html/";
var editor = "";
/**
 * jQuery Utility Functions
 */
function inputClear (el) {
    var $i = $('#'+el);
    if($i.attr('value')==$i.attr('title')) {
        $i.attr('value','');
        $i.addClass('focussed');
    }
}
function inputReset (el) {
    var $i = $('#'+el);
    if($i.attr('value')== '') {
        $i.attr('value',$i.attr('title'));
        $i.removeClass('focussed');
    }
}
function growlAlert (title, message, icon, timeout) {
    if(!title) {
        var title = 'Message';
    }
    if(!message) {
        var message = 'An Error Occurred';
    }
    if(!icon) {
        var icon = '';
    }
    if(!timeout) {
        var timeout = false;
    }
    $.Growl.show({
        'title'  : title,
        'message': message,
        'icon'   : icon,
        'timeout': timeout
    });
}

$(document).ready(function(){
						   
						   
    $('.hide-me').hide();

    $('.listrows').bind({
        mouseover: function () {
            $(this).find('.invisible').css({
                'visibility':'visible'
            });
        },
        mouseout: function () {
            $(this).find('.invisible').css({
                'visibility':'hidden'
            });
        }
    });

    $('label.listbox').each(function(){
        $(this).find('.label').hide();
        $label = $(this).find('.label').text();
        $label = $label.replace(":","").trim();
        $input = $(this).find('select');
        if($input.val() == '') {
            $input.prepend($('<option></option>').val("").html($label).attr("selected",true));        // here prob
        }
        
    });

    $('.date-picker-container').each(function(){
        //$(this).find("input").hide();
        $(this).find('label').hide();
        var InputId = $(this).attr("id").replace("date_picker-","");

        var $day    = "";
        var $month  = "";
        var $year   = "";
        var $date   = "";
        $searchArr  = InputId.split("_");
        $searchArr.splice(($searchArr.length - 1),1);
        $search     = $searchArr.join("_") + "_";
		
        $(this).find("input").each(function(){
            switch($(this).attr("name").replace($search,"")) {
                case "day":
                    $day    = $(this).val().replace(/([^0-9].*)/g, "");
                    break;

                case "month":
                    $month  = $(this).val().replace(/([^0-9].*)/g, "");
                    break;

                case "year":
                    $year   = $(this).val().replace(/([^0-9].*)/g, "");
                    break;
            }
        });
        // assign label
        var label   = document.createElement('label');
        $(label).attr("for",$(this).attr("id"));
        $(label).attr("class","textbox");
        $(this).append(label);

        if($day != "" && $month != "" && $year != "") {
            $date = $day + "/" + $month + "/" + $year;
        }

        var $labelText = $(this).find('span').eq(0);
        $(label).append($labelText);

        // assign text
        var save = document.createElement('input');        
        $(save).attr({
            type    : "text",
            id      : InputId,
            name    : InputId,
            value   : $date
        });
        $(save).attr("class","datepicker");
        
        $(label).append(save);        
        bindDatePicker();
    });

    $('form').bind('submit',function(){
        unbindInputElements();
        return bindDatePickerValue();
    });

    bindInputElements();
    $('.RemoveCss').bind({
        click: function(){
        unbindInputElements();
    }});

    $('.framed').fancybox({
        'speedIn': 2500,
        'speedOut': 2500,
        'transitionIn': 'fade',
        'transitionOut': 'fade',
        'overlayShow': true,
        'overlayOpacity':0.8,
        'frameWidth': 1024,
        'frameHeight': 600
    });

    /**
     * Load Map Functions Conditionally
     */
    if('function' == typeof(mapHandler)) {
        mapHandler();
    }

    /**
     *To Save Cache
     */
    $('.class-tick').bind({
        change: function(){
            var page = $(this).attr("name").substr(6);
            var cache = $(this).is(':checked')?1:0;
            var queryString = {
                "responseType":  "xml",
                "action":  "cacheUpdate",
                "cms_uid": page,
                "cache":  cache
            };
            callAjax(queryString,'cacheUpdate','xml');
        }
    });

    /**
     *To Save Cache Time
     */
    $('.class-input').bind({
        blur: function(){
            var page = $(this).attr("name").substr(11);
            var cacheTime = $(this).val();
            var queryString = {
                "responseType":  "xml",
                "action":  "cacheTimeUpdate",
                "cms_uid": page,
                "cache_time":  cacheTime
            };
            callAjax(queryString,'cacheTimeUpdate','xml');
        }
    });
    $('.class-editor-input').each(function() {
        editor = CodeMirror.fromTextArea($(this).attr("id"), {
            height: "300px",
            parserfile: "parsexml.js",
            stylesheet: url + "styles/editor/xmlcolors.css",
            path: url + "scripts/editor/",
            continuousScanning: 500,
            lineNumbers: true
        });
    });    

    $('#template_tags_form').bind({
        submit: function(){
            var templateArray   = window.location.toString().split("/");
            var template_uid    = "";
            if(templateArray[templateArray.length-1] == "") {
                template_uid    = templateArray[templateArray.length-2];
            }
            else {
                template_uid    = templateArray[templateArray.length-1];
            }                        
            var tag_name        = $("input[name=tag_name]").val();
            var tag_description = $("input[name=tag_description]").val();
            var tag_type        = $("select[name=tag_type]").val();

            if(tag_name.trim() == "") {
                alert("Please Enter Tag Name");                
            }
            else if(tag_description.trim() == "") {
                alert("Please Enter Tag Description");                
            }
            else if(tag_type.trim() == "") {
                alert("Please Select Tag Type");
            }
            else {
                var queryString = {
                    "responseType"      :  "html",
                    "action"            :  "templateTagsCreate",
                    "template_uid"      :   template_uid,
                    "tag_name"          :   tag_name,
                    "tag_description"   :   tag_description,
                    "tag_type"          :   tag_type
                };
                callAjax(queryString,'templateTagsCreate','html');
            }
            return false;
        }
    });

    /* cms related javascript start */
    $('#save-draft,#publish,#submit-page').bind({
        click: function(){
            var action              =   $(this).attr("id");            
            var cms_uid             =   $("input[name=cms-uid]").val();
            var cms_title           =   $("input[name=cms-title]").val();
            var cms_slug            =   $("input[name=cms-slug]").val();
            var cms_body            =   editor.getCode();

            // get attributes
            var cms_parent_uid      =   $("select[name=cms-parent-uid]").val();
            var cms_layout_uid      =   $("select[name=cms-layout-uid]").val();
            var cms_template_uid    =   $("select[name=cms-temlpate-uid]").val();
            var cms_keywords        =   $("input[name=cms-keywords]").val();
            var cms_description     =   $("input[name=cms-description]").val();
            var cms_status          =   "";
            if(action == "submit-page") {
                cms_status          =   $("input[name=cms-status]:radio:checked").val();
            }
            if(action == "publish") {
                cms_status          =   "1";
            }
            else {
                cms_status          =   "9";
            }            
            var cms_is_article      =   $("input[name=cms-is-article]:radio:checked").val();
            var cms_cache_enable    =   $("input[name=cms-cache-enable]:radio:checked").val();
            var cms_cache_time      =   $("input[name=cms-cache-time]").val();          

            var queryString = {
                "responseType"          :   "html",
                "action"                :   action,
                "cms_uid"               :   cms_uid,
                "cms_title"             :   cms_title,
                "cms_slug"              :   cms_slug,
                "cms_body"              :   cms_body,
                //get attributes
                "cms_parent_uid"        :   cms_parent_uid,
                "cms_layout_uid"        :   cms_layout_uid,
                "cms_template_uid"      :   cms_template_uid,
                "cms_keywords"          :   cms_keywords,
                "cms_description"       :   cms_description,
                "cms_status"            :   cms_status,
                "cms_is_article"        :   cms_is_article,
                "cms_cache_enable"      :   cms_cache_enable,
                "cms_cache_time"        :   cms_cache_time
            };
            callAjax(queryString,action,'html');
        }
    });
    
    /* cms related javascript finish */
    
    $('#add-cms-tag').bind({
        click: function(){
            var cms_uid     =   $("input[name=cms-uid]").val();
            var cms_tag     =   $("input[name=cms-input-tag]").val();
            if(cms_uid != "") {
                var queryString = {
                    "responseType"          :   "html",
                    "action"                :   "add-cms-tag",
                    "cms_uid"               :   cms_uid,
                    "cms_tag"               :   cms_tag
                };
                callAjax(queryString,"add-cms-tag",'html');
            }
        }
    });

    /* cms tag related script */    
    $('.cms-chk-tag').bind({
        click: function(){
            var cms_uid         =   $("input[name=cms-uid]").val();
            var cms_tag_uid     =   $(this).val();
            var cms_tag_checked =   $(this).attr("checked")?1:0;
            //alert(cms_tag_checked);
            //return false;
            if(cms_uid != "") {
                var queryString = {
                    "responseType"          :   "html",
                    "action"                :   "add-update-cms-tag",
                    "cms_uid"               :   cms_uid,
                    "cms_tag_checked"       :   cms_tag_checked,
                    "cms_tag_uid"           :   cms_tag_uid
                };
                callAjax(queryString,"add-update-cms-tag",'html');
            }
        }
    });

    $('.toggle').click(function(){
        var $this = $(this);
        switch($this.attr("type")) {
            case "radio":
                if($this.hasClass('on')) {
                    $('#'+$this.attr('name')+'-toggle').fadeIn();
                    $('#'+$this.attr('name')+'-toggle-off').fadeOut();
                    if($('#'+$this.attr('name')+'-toggle').attr("class") != "") {
                        $("."+$('#'+$this.attr('name')+'-toggle').attr("class")).fadeIn();
                    }
                } else if($this.hasClass('off')) {
                    $('#'+$this.attr('name')+'-toggle').fadeOut();
                    $('#'+$this.attr('name')+'-toggle-off').fadeIn();
                    if($('#'+$this.attr('name')+'-toggle').attr("class") != "") {
                        $("."+$('#'+$this.attr('name')+'-toggle').attr("class")).fadeOut();
                    }
                }
                break;

            case "checkbox":
                if($this.attr('checked')) {
                    $('#'+$this.attr('name')+'-toggle').fadeIn();
                    $('#'+$this.attr('name')+'-toggle-off').fadeOut();
                    if($('#'+$this.attr('name')+'-toggle').attr("class") != "") {
                        $("."+$('#'+$this.attr('name')+'-toggle').attr("class")).fadeIn();
                    }
                }
                else {
                    $('#'+$this.attr('name')+'-toggle-off').fadeIn();
                    $('#'+$this.attr('name')+'-toggle').fadeOut();
                    if($('#'+$this.attr('name')+'-toggle').attr("class") != "") {
                        $("."+$('#'+$this.attr('name')+'-toggle').attr("class")).fadeOut();
                    }
                }
                break;
        }       
    });

    $('.toggle').each(function(){
        var $this = $(this);
        switch($this.attr("type")) {
            case "radio":
                if($this.hasClass('on') && $this.attr('checked')) {
                    $('#'+$this.attr('name')+'-toggle').fadeIn();
                    $('#'+$this.attr('name')+'-toggle-off').fadeOut();
                    if($('#'+$this.attr('name')+'-toggle').attr("class") != "") {
                        $("."+$('#'+$this.attr('name')+'-toggle').attr("class")).fadeIn();
                    }                    
                }
                else if($this.hasClass('off')  && $this.attr('checked')) {
                    $('#'+$this.attr('name')+'-toggle').fadeOut();
                    $('#'+$this.attr('name')+'-toggle-off').fadeIn();
                    if($('#'+$this.attr('name')+'-toggle').attr("class") != "") {
                        $("."+$('#'+$this.attr('name')+'-toggle').attr("class")).fadeOut();
                    }
                }
                break;

            case "checkbox":
                if($this.attr('checked')) {
                    $('#'+$this.attr('name')+'-toggle').fadeIn();
                    $('#'+$this.attr('name')+'-toggle-off').fadeOut();
                    if($('#'+$this.attr('name')+'-toggle').attr("class") != "") {
                        $("."+$('#'+$this.attr('name')+'-toggle').attr("class")).fadeIn();
                    }
                    
                } else {
                    $('#'+$this.attr('name')+'-toggle-off').fadeIn();
                    $('#'+$this.attr('name')+'-toggle').fadeOut();
                    if($('#'+$this.attr('name')+'-toggle').attr("class") != "") {
                        $("."+$('#'+$this.attr('name')+'-toggle').attr("class")).fadeOut();
                    }
                }
                break;
        }             
    });

    $('.toggle-select').change(function() {
        var $this = $(this);
        if($this.hasClass('toggle-select-on-'+$this.val())) {
            $('#'+$this.attr('name')+'-toggle').fadeIn();
        }
        else {
            $('#'+$this.attr('name')+'-toggle').fadeOut();
        }
    });
    
    $('.toggle-select').each(function() {
        var $this = $(this);
        if($this.hasClass('toggle-select-on-'+$this.val())) {
            $('#'+$this.attr('name')+'-toggle').fadeIn();
        }
        else {
            $('#'+$this.attr('name')+'-toggle').fadeOut();
        }
    });

    // check all check none script
    $('#chk-all').bind({
        click: function(){
            $("input[type=checkbox]").attr("checked",$(this).attr("checked"));
        }
    });
    // check all check none script
	
    // set the tabs    
    if($(".tabs").length > 0) {
               $('.tabs').tabs({
                       select: function(event, ui) {
                               var isValid = true;
                               if($(ui.panel).html().trim() == "") {
                                       isValid = false;
                               }                                
                               return isValid;
                       }
               }); 
       }  
    //
    
    // all the page related script
    bindCmsPageScript();                // CMS Listing
    bindTemplatePageScript();           // Template Page
    bindTemplateTagDeleteScript();      // Delete Template Tags
    bindCmsTemplateTagsScript();        // CMS Template Tags
    bindIpManagerScript();              // IP Manager
    bindTagScript();                    // Tag Manager
    bindFileManagerScript();            // Menu Manager
    bindMenuManagerScript();            // File Manager
    bindUserManagerScript();            // User Manager
    bindFormBuilderScript();            // Form Builder That is deleted
    bindContactNumberTypeScript();      // contact number type
    bindCreditOptionsScript();          // credit options
    bindEmailAddressTypeScript();       // Email address type
    bindLeadSourcesScript();            // Lead Sources
    bindRelationshipTypeScript();       // Relationship Type
    bindInstantMessengerTypeScript();   // Email address type    
    bindResidentialStatusScript();      // Residential Status
    bindPropertyTitleScript();          // Property Title
    bindRepaymentMethodScript();        // Repayment Method
    bindPropertyTypeScript();           // Property Type
    bindGuaranteeScript();              // Guarantee
    bindInterestRateTypeScript();       // Interest Rate Type
    bindBusinessTypeScript();           // Business Type
    bindAddressFormScript();            // Address Form
    bindEndowmentPolicyScript();        // Endowment Policy Form
    bindAdditionalInformationScript();  // textarea for additional information
    bindPropertyAddressScript();        // Property Address
    bindCountryTypeScript();            // Country Type
    bindCountrySubTypeScript();         // Country Sub Type
    bindCountryScript();                // Country
    bindInvestmentProductTypeScript();  // Investment Property Type
    bindProductTypeScript();            // Property Type
    bindRoomTypeScript();               // Room Type
    bindRoofMaterialScript();           // Roof Material
    bindConstructionMaterialScript();   // Construciton Material
    bindRoofConstructionTypeScript();   // Roof Construction Type
// all the page related script
});
function parseResponseXml(action,xml) {
    if($("response",xml).length==1) {
        switch(action) {
            case "cacheUpdate":
            case "cacheTimeUpdate":
                var cms_uid = $("response",xml).attr('cms_uid');
                var status  = $("response",xml).attr('status');
                var title   = "";
                title       = (action == "cacheUpdate")?'Cache Update':'Cache Time Update';
                growlAlert(title,(status==1 ? 'Success' : 'Failed'),url+'images/downarrow.png',1000);
                break;
            case "get-tag-data":
                var error       = $("response",xml).attr('error');
                if(error == 0) {
                    var tag_uid     = $("response",xml).attr('tag_uid');
                    var tag_name    = $("response",xml).attr('tag_name');
                    var tag_slug    = $("response",xml).attr('tag_slug');
                    var tag_descr   = $("response",xml).attr('tag_descr');
                    $("input[name=tag-uid]").val(tag_uid);
                    $("input[name=tag-name]").val(tag_name);
                    $("input[name=tag-slug]").val(tag_slug);
                    $("textarea[name=tag-description]").val(tag_descr);
                }
                else {
                    growlAlert("Error getting data",'Failed',url+'images/downarrow.png',1000);
                }
                break;
            case "get-file-manager-data":
                var error       = $("response",xml).attr('error');
                if(error == 0) {
                    var file_uid        = $("response",xml).attr('file_uid');
                    var filepath        = $("response",xml).attr('filepath');
                    var title           = $("response",xml).attr('title');
                    var description     = $("response",xml).attr('description');
                    var alt             = $("response",xml).attr('alt');
                    $("input[name=file-uid]").val(file_uid);
                    $("input[name=file-title]").val(title);
                    $("textarea[name=file-descr]").val(description);
                    $("input[name=file-alt]").val(alt);
                    var filepatharr     = filepath.split("/");
                    filepath            = filepatharr[filepatharr.length - 1];
                    $("select[name=select-theme]").val(filepath);
                    $("#files-add").fadeIn("1000");
                }
                else {
                    growlAlert("Error getting data",'Failed',url+'images/downarrow.png',1000);
                }
                break;
            case "delete-file-manager-data":
                var file_uid    = $("response",xml).attr('file_uid');
                var status      = $("response",xml).attr('status');
                var title       = "";
                title           = "File Delete";
                growlAlert(title,(status==1 ? 'Success' : 'Failed'),url+'images/downarrow.png',1000);
                break;
            case "get-contact-number-type-data":
            case "get-credit-options-data":
            case "get-email-address-type-data":
            case "get-lead-sources-data":
            case "get-relationship-type-data":
            case "get-instant-messenger-type-data":            
            case "get-residential-status-data":
            case "get-property-title-data":
            case "get-property-type-data":
            case "get-repayment-method-data":
            case "get-guarantee-data":
            case "get-interest-rate-type-data":
            case "get-business-type-data":
            case "get-investment-product-type-data":
            case "get-product-type-data":
            case "get-room-type-data":
            case "get-roof-material-data":
            case "get-construction-material-data":
            case "get-roof-construction-type-data":
                replaceStr  = action.replace("get-","");
                replaceStr  = replaceStr.replace("-data","");
                error       = $("response",xml).attr('error');
                if(error == 0) {
                    var uid             = $("response",xml).attr('uid');
                    var name            = $("response",xml).attr('name');
                    var is_active       = $("response",xml).attr('active');
                    var description     = $("response",xml).attr('description');
                    $("input[name="+replaceStr+"-uid]").val(uid);
                    $("input[name=name]").val(name);                    
                    $("textarea[name=description]").val(description);
                    $.each( $("input[name=is_active]") , function () {
                        if($(this).val() == is_active) {
                            $(this).attr("checked", true);
                        }
                    });
                }
                else {
                    growlAlert("Error getting data",'Failed',url+'images/downarrow.png',1000);
                }
                break;
            case "get-country-type-data":
            case "get-country-sub-type-data":
                replaceStr  = action.replace("get-","");
                replaceStr  = replaceStr.replace("-data","");
                error       = $("response",xml).attr('error');
                if(error == 0) {
                    var uid             = $("response",xml).attr('uid');
                    var name            = $("response",xml).attr('name');
                    var is_active       = $("response",xml).attr('active');                    
                    $("input[name="+replaceStr+"-uid]").val(uid);
                    $("input[name=name]").val(name);                    
                    $.each( $("input[name=is_active]") , function () {
                        if($(this).val() == is_active) {
                            $(this).attr("checked", true);
                        }
                    });
                }
                else {
                    growlAlert("Error getting data",'Failed',url+'images/downarrow.png',1000);
                }
                break;                           
            case "get-country-data":
                error       = $("response",xml).attr('error');
                if(error == 0) {
                    var uid                         = $("response",xml).attr('uid');
                    var common_name                 = $("response",xml).attr('common_name');
                    var formal_name                 = $("response",xml).attr('formal_name');
                    var type_uid                    = $("response",xml).attr('type_uid');
                    var sub_type_uid                = $("response",xml).attr('sub_type_uid');
                    var sovereignty                 = $("response",xml).attr('sovereignty');
                    var capital                     = $("response",xml).attr('capital');
                    var iso_4217_currency_code      = $("response",xml).attr('iso_4217_currency_code');
                    var iso_4217_currency_name      = $("response",xml).attr('iso_4217_currency_name');
                    var itu_t_telephone_code        = $("response",xml).attr('itu_t_telephone_code');
                    var iso_3166_1_2_letter_code    = $("response",xml).attr('iso_3166_1_2_letter_code');
                    var iso_3166_1_3_letter_code    = $("response",xml).attr('iso_3166_1_3_letter_code');
                    var iso_3166_1_number           = $("response",xml).attr('iso_3166_1_number');
                    var iana_country_code_tld       = $("response",xml).attr('iana_country_code_tld');
                    var is_active                   = $("response",xml).attr('active');
                    $("input[name=country-uid]").val(uid);
                    $("input[name=common_name]").val(common_name);
                    $("input[name=formal_name]").val(formal_name);
                    $("select[name=type_uid]").val(type_uid);
                    $("select[name=sub_type_uid]").val(sub_type_uid);
                    $("input[name=sovereignty]").val(sovereignty);
                    $("input[name=capital]").val(capital);
                    $("input[name=iso_4217_currency_code]").val(iso_4217_currency_code);
                    $("input[name=iso_4217_currency_name]").val(iso_4217_currency_name);
                    $("input[name=itu_t_telephone_code]").val(itu_t_telephone_code);
                    $("input[name=iso_3166_1_2_letter_code]").val(iso_3166_1_2_letter_code);
                    $("input[name=iso_3166_1_3_letter_code]").val(iso_3166_1_3_letter_code);
                    $("input[name=iso_3166_1_number]").val(iso_3166_1_number);
                    $("input[name=iana_country_code_tld]").val(iana_country_code_tld);
                    $.each( $("input[name=is_active]") , function () {
                        if($(this).val() == is_active) {
                            $(this).attr("checked", true);
                        }
                    });
                }
                else {
                    growlAlert("Error getting data",'Failed',url+'images/downarrow.png',1000);
                }
                break;
        }      
    }    
}

function parseResponseHtml(action,html) {
    var response, error ,success;
    switch(action) {
        case "positionUpdate":
            $("#content").hide();
            $("#content").html(html);
            $("#content").show(600);
            bindCmsPageScript();
            break;
        case "templateCreate":
            response    = $(html).find("div[id=response_data]").html();
            success     = $(html).find("div[id=response_success]").html();
            error       = $(html).find("div[id=response_error]").html();
            if(error.trim() != "") {
                growlAlert(error.trim(),'Failed',url+'images/downarrow.png',1000);
            }
            else if(success.trim() != "") {
                growlAlert(success.trim(),'Success',url+'images/downarrow.png',1000);
            }
            
            var divId = $(response).live("div[id=template_type-]").attr("id");
            response = $(response).live("#"+divId).html();
            $("#"+divId).css("display","none");
            $("#"+divId).html(html);
            $("#"+divId).fadeIn("1000",function () {
                $("#"+divId).css("display","block");
            });
            bindTemplatePageScript();
            break;
        case "templateTagsCreate":
            response    = $(html).find("div[id=response_data]").html();
            success     = $(html).find("div[id=response_success]").html();
            error       = $(html).find("div[id=response_error]").html();
            if(error.trim() != "") {
                growlAlert(error.trim(),'Failed',url+'images/downarrow.png',1000);
            }
            else if(success.trim() != "") {
                growlAlert(success.trim(),'Success',url+'images/downarrow.png',1000);
            }
            $(response).find("div[id^='tag-container']").attr("style","display:none");
            var hiddenId = $(response).live("div[id^='tag-container']").attr("id");
            $("#template-custom-tag-list").append(response);
            $("#"+hiddenId).css("display","none");
            $("#"+hiddenId).fadeIn("1000",function () {
                $("#"+hiddenId).css("display","block");
            });
            clear_form_elements("#template_tags_form");
            bindTemplateTagDeleteScript();
            break;
        case "templateTagsDelete":
            response    = $(html).find("div[id=response_data]").html();
            success     = $(html).find("div[id=response_success]").html();
            error       = $(html).find("div[id=response_error]").html();
            if(error.trim() != "") {
                growlAlert(error.trim(),'Failed',url+'images/downarrow.png',1000);
            }
            else if(success.trim() != "") {
                growlAlert(success.trim(),'Success',url+'images/downarrow.png',1000);
            }
            $("#tag-container-"+response).fadeOut(1000,function () {
                $("#tag-container-"+response).remove();
            });            
            clear_form_elements("#template_tags_form");
            bindTemplateTagDeleteScript();
            break;
        case "publish":
        case "submit-page":
        case "save-draft":
            response    = $(html).find("div[id=response_data]").html();
            success     = $(html).find("div[id=response_success]").html();
            error       = $(html).find("div[id=response_error]").html();
            if(error.trim() != "") {
                growlAlert(error.trim(),'Failed',url+'images/downarrow.png',1000);
            }
            else if(success.trim() != "") {                
                if(response.indexOf("#####", 0) !== -1) {
                    var respArray   =   response.split("#####");
                    var data        =   respArray[0];
                    var cms_uid     =   respArray[1];
                    $("#form-template-tags").css("display","none");
                    $("#form-template-tags").html(data);
                    $("#form-template-tags").fadeIn("1000",function () {
                        $("#form-template-tags").css("display","block");
                    });
                    bindCmsTemplateTagsScript();
                }
                else {
                    cms_uid         =   response;
                }
                growlAlert(success.trim(),'Success',url+'images/downarrow.png',1000);
                if($("input[name=cms-uid]").val() == "") {
                    window.location.href=   url + "admin/pages/edit/" + cms_uid;
                }
                $("input[name=cms-uid]").val(cms_uid);
            }
            break;
        case "add-cms-tag":
            response    = $(html).find("div[id=response_data]").html();
            success     = $(html).find("div[id=response_success]").html();
            error       = $(html).find("div[id=response_error]").html();
            if(error.trim() != "") {
                growlAlert(error.trim(),'Failed',url+'images/downarrow.png',1000);
            }
            else if(success.trim() != "") {
                growlAlert(success.trim(),'Failed',url+'images/downarrow.png',1000);
                $(response).find("div[id^='tag-container']").attr("style","display:none");
                var hiddenId = $(response).live("div[id^='tag-container']").attr("id");
                if(hiddenId != null) {                    
                    if($("#"+hiddenId).attr("id") == null) {
                        $("#container-tags").append(response);
                        $("#"+hiddenId).css("display","none");
                        $("#"+hiddenId).fadeIn("1000",function () {
                            $("#"+hiddenId).css("display","block");
                        });
                    }
                    else {                        
                        var tagId = $(response).find("input[id^='chk-']").attr("id");                        
                        $("#"+tagId).attr("checked",true);
                    }
                }
                else {
                    $("#chk-"+response).attr("checked",true);
                }                
            }
            $("input[name=cms-input-tag]").val("");            
            break;
        case "add-update-cms-template-tag":
        case "add-update-cms-tag":
            response    = $(html).find("div[id=response_data]").html();
            success     = $(html).find("div[id=response_success]").html();
            error       = $(html).find("div[id=response_error]").html();
            if(error.trim() != "") {
                growlAlert(error.trim(),'Failed',url+'images/downarrow.png',1000);
            }
            else if(success.trim() != "") {
                growlAlert(success.trim(),'Success',url+'images/downarrow.png',1000);
            }
            break;
        case "add-ip-rule":
            response    = $(html).find("div[id=response_data]").html();
            success     = $(html).find("div[id=response_success]").html();
            error       = $(html).find("div[id=response_error]").html();
            if(error.trim() != "") {
                growlAlert(error.trim(),'Failed',url+'images/downarrow.png',1000);
            }
            else if(success.trim() != "") {
                growlAlert(success.trim(),'Success',url+'images/downarrow.png',1000);
                var hiddenId = $(response).live("div[id^='ip-container-']").attr("id");
                $("input[name=ip-rule]").val("");
                $("#list-ip-address").append(response);
                $("#"+hiddenId).css("display","none");
                $("#"+hiddenId).fadeIn("1000",function () {
                    $("#"+hiddenId).css("display","block");
                });
            }
            bindIpManagerScript();
            break;
        case "remove-ip-rule":
            response    = $(html).find("div[id=response_data]").html();
            success     = $(html).find("div[id=response_success]").html();
            error       = $(html).find("div[id=response_error]").html();
            if(error.trim() != "") {
                growlAlert(error.trim(),'Failed',url+'images/downarrow.png',1000);
            }
            else if(success.trim() != "") {
                growlAlert(success.trim(),'Success',url+'images/downarrow.png',1000);
                $("#ip-container-"+response).fadeOut(1000,function () {
                    $("#ip-container-"+response).remove();
                });
            }
            break;
        case "delete-multiple-tags":
        case "add-update-tag":
        case "delete-tag":
            response    = $(html).find("div[id=response_data]").html();
            success     = $(html).find("div[id=response_success]").html();
            error       = $(html).find("div[id=response_error]").html();
            if(error.trim() != "") {
                growlAlert(error.trim(),'Failed',url+'images/downarrow.png',1000);
            }
            else if(success.trim() != "") {
                growlAlert(success.trim(),'Success',url+'images/downarrow.png',1000);
                clear_form_elements("#form-tags-add-update");
                $("#add-update-buttons").fadeOut("1000");
                $("#tags-list").css("display","none");
                $("#tags-list").html(response);
                $("#tags-list").fadeIn("1000");
            }
            break;
        case "update-file-manager":
            response    = $(html).find("div[id=response_data]").html();
            success     = $(html).find("div[id=response_success]").html();
            error       = $(html).find("div[id=response_error]").html();
            if(error.trim() != "") {
                growlAlert(error.trim(),'Failed',url+'images/downarrow.png',1000);
            }
            else if(success.trim() != "") {
                growlAlert(success.trim(),'Success',url+'images/downarrow.png',1000);
                clear_form_elements("#form-submit-file-tags");
                $("#files-add").fadeOut("1000");
            }
            break;
        case "add-menu":
            response    = $(html).find("div[id=response_data]").html();
            success     = $(html).find("div[id=response_success]").html();
            error       = $(html).find("div[id=response_error]").html();
            if(error.trim() != "") {
                growlAlert(error.trim(),'Failed',url+'images/downarrow.png',1000);
            }
            else if(success.trim() != "") {
                growlAlert(success.trim(),'Success',url+'images/downarrow.png',1000);
                clear_form_elements("#menu-form");
                $("#sub-menu-section").append(response);
                bindMenuManagerScript();
            }
            break;
        case "update-menu":
            response    = $(html).find("div[id=response_data]").html();
            success     = $(html).find("div[id=response_success]").html();
            error       = $(html).find("div[id=response_error]").html();
            if(error.trim() != "") {
                growlAlert(error.trim(),'Failed',url+'images/downarrow.png',1000);
            }
            else if(success.trim() != "") {
                growlAlert(success.trim(),'Success',url+'images/downarrow.png',1000);
                $("div[id=menu-header-title-"+response+"]").html($("input[name=menu-title-"+response+"]").val());
            }
            break;
        case "add-update-menu-item":
            response    = $(html).find("div[id=response_data]").html();
            success     = $(html).find("div[id=response_success]").html();
            error       = $(html).find("div[id=response_error]").html();
            if(error.trim() != "") {
                growlAlert(error.trim(),'Failed',url+'images/downarrow.png',1000);
            }
            else if(success.trim() != "") {
                respArray           = response.split("#####");
                var menu_uid        = respArray[2];
                var menu_item_uid   = respArray[1];
                response            = respArray[0];
                growlAlert(success.trim(),'Success',url+'images/downarrow.png',1000);                
                if(response != "update") {
                    clear_form_elements("#form-menu-item-"+menu_item_uid+"-"+menu_uid);
                    $("#menu-items-container-"+menu_uid).css("display","none");
                    $("#menu-items-container-"+menu_uid).append(response);
                    $("#menu-items-container-"+menu_uid).fadeIn(1000);
                    bindMenuManagerScript();
                }
            }
            break;
        case "menuPositionUpdate":
            response    = $(html).find("div[id=response_data]").html();
            success     = $(html).find("div[id=response_success]").html();
            error       = $(html).find("div[id=response_error]").html();
            if(error.trim() != "") {
                growlAlert(error.trim(),'Failed',url+'images/downarrow.png',1000);
            }
            else if(success.trim() != "") {
                growlAlert(success.trim(),'Success',url+'images/downarrow.png',1000);
                var id  = $(response).find("div[id^=drag-drop-]").attr("id");
                var dropIds = id.split("-");
                $("#menu-items-container-"+dropIds[3]).css("display","none");
                $("#menu-items-container-"+dropIds[3]).html(response);
                $("#menu-items-container-"+dropIds[3]).fadeIn(1000);
                bindMenuManagerScript();
            }
            break;
        case "new-user":
        case "update-user":
            response    = $(html).find("div[id=response_data]").html();
            success     = $(html).find("div[id=response_success]").html();
            error       = $(html).find("div[id=response_error]").html();
            if(error.trim() != "") {
                growlAlert(error.trim(),'Failed',url+'images/downarrow.png',1000);
            }
            else if(success.trim() != "") {
                $("input[name=user-uid]").val(response);
                growlAlert(success.trim(),'Success',url+'images/downarrow.png',1000);
            }       
            break;
        case "formCreate":
            response    = $(html).find("div[id=response_data]").html();
            success     = $(html).find("div[id=response_success]").html();
            error       = $(html).find("div[id=response_error]").html();
            if(error.trim() != "") {
                growlAlert(error.trim(),'Failed',url+'images/downarrow.png',1000);
            }
            else if(success.trim() != "") {
                growlAlert(success.trim(),'Success',url+'images/downarrow.png',1000);
                var respArray       = response.split("#####");
                var form_type_uid   = respArray[0];
                $("#forms-list-"+form_type_uid).css("display","none");
                $("#forms-list-"+form_type_uid).html(respArray[1]);
                $("#forms-list-"+form_type_uid).fadeIn("1000");
                clear_form_elements("#form-add-new-"+form_type_uid);
                $("#button-events-" + form_type_uid).show();
                $(".add-form").hide();               
            }            
            break;
        // all the library functions should do the same reload the page after the add / update / delete or multiple delete
        case "delete-multiple-contact-number-type":
        case "contact-number-type-update":
        case "delete-contact-number-type":
        case "delete-multiple-credit-options":
        case "credit-options-update":
        case "delete-credit-options":
        case "delete-multiple-email-address-type":
        case "email-address-type-update":
        case "delete-email-address-type":
        case "delete-multiple-lead-sources":
        case "lead-sources-update":
        case "delete-lead-sources":
        case "delete-multiple-relationship-type":
        case "relationship-type-update":
        case "delete-relationship-type":
        case "delete-multiple-instant-messenger-type":
        case "instant-messenger-type-update":
        case "delete-instant-messenger-type":
        case "delete-multiple-residential-status":
        case "residential-status-update":
        case "delete-residential-status":
        case "delete-multiple-property-title":
        case "property-title-update":
        case "delete-property-title":
        case "delete-multiple-property-type":
        case "property-type-update":
        case "delete-property-type":
        case "delete-multiple-repayment-method":
        case "repayment-method-update":
        case "delete-repayment-method":
        case "delete-multiple-guarantee":
        case "guarantee-update":
        case "delete-guarantee":
        case "delete-multiple-interest-rate-type":
        case "interest-rate-type-update":
        case "delete-interest-rate-type":
        case "delete-multiple-business-type":
        case "business-type-update":
        case "delete-business-type":
        case "delete-multiple-country-type":
        case "country-type-update":
        case "delete-country-type":
        case "delete-multiple-country-sub-type":
        case "country-sub-type-update":
        case "delete-country-sub-type":
        case "delete-multiple-country":
        case "country-update":
        case "delete-country":
        case "delete-multiple-investment-product-type":
        case "investment-product-type-update":
        case "delete-investment-product-type":
        case "delete-multiple-product-type":
        case "product-type-update":
        case "delete-product-type":
        case "delete-multiple-room-type":
        case "room-type-update":
        case "delete-room-type":
        case "delete-multiple-roof-material":
        case "roof-material-update":
        case "delete-roof-material":
        case "delete-multiple-construction-material":
        case "construction-material-update":
        case "delete-construction-material":
        case "delete-multiple-roof-construction-type":
        case "roof-construction-type-update":
        case "delete-roof-construction-type":
            response    = $(html).find("div[id=response_data]").html();
            success     = $(html).find("div[id=response_success]").html();
            error       = $(html).find("div[id=response_error]").html();
            if(error.trim() != "") {
                growlAlert(error.trim(),'Failed',url+'images/downarrow.png',1000);
            }
            else if(success.trim() != "") {
                growlAlert(success.trim(),'Success',url+'images/downarrow.png',1000);
                window.location.reload();
            }
            break;
        case "new-property-address":
        case "update-property-address":
            response    = $(html).find("div[id=response_data]").html();
            success     = $(html).find("div[id=response_success]").html();
            error       = $(html).find("div[id=response_error]").html();
            if(error.trim() != "") {
                growlAlert(error.trim(),'Failed',url+'images/downarrow.png',1000);
            }
            else if(success.trim() != "") {
                $("input[name=property-address-uid]").val(response);
                growlAlert(success.trim(),'Success',url+'images/downarrow.png',1000);
            }
            break;
    }
}

function bindTemplatePageScript() {
    $('.add-new-template').unbind('click');
    $('.add-new-template').bind('click', function() {
        //$('.add-new-template').click(function() {
        var Class = $(this).attr("class");
        var ClassArr = Class.split(" ");
        var template_type_uid = "";
        if($(this).html() == "Add New") {
            template_type_uid = ClassArr[1].toString().replace("add-new-","");
            // this will be removed by u as it hides all the rows
            $(".add-copy-template").hide();
            $(".button-events").show();
            if(template_type_uid != "") {
                $("#add-copy-template-"+template_type_uid).show(400);
                $("#button-events-" + template_type_uid).hide();
            }
        }
        else if($(this).html() == "Add Template") {
            template_type_uid = ClassArr[1].toString().replace("new-","");
            if(template_type_uid != "") {

                var templateType        = "new";
                var newtemplateName     = $("input[name=new_text_template_"+template_type_uid+"]").val();
                var copytemplateName    = $("input[name=copy_text_template_"+template_type_uid+"]").val();
                var copytemplateFrom    = $("select[name=copy_template_"+template_type_uid+"]").val();

                if(newtemplateName == "" && copytemplateName == "") {
                    alert("Select [a] or [b]");
                    return false;
                }
                
                $("#button-events-" + template_type_uid).show();
                $(".add-copy-template").hide();

                if(newtemplateName != "") {
                    templateType        = "new";
                }
                else {
                    templateType        = "copy";
                }

                /* Fire Ajax Here */
                var queryString = {
                    "responseType"              :   "html",
                    "template_type_uid"         :   template_type_uid,
                    "action"                    :   "templateCreate",
                    "template_type"             :   templateType,
                    "new_template_name"         :   newtemplateName,
                    "copy_template_name"        :   copytemplateName,
                    "copy_template_from"        :   copytemplateFrom
                };
                callAjax(queryString,'templateCreate','html');
            }
        /* Fire Ajax Here */
        }
        else if($(this).html() == "Cancel") {
            template_type_uid = ClassArr[1].toString().replace("cancel-","");
            if(template_type_uid != "") {
                $("#button-events-" + template_type_uid).show();
                $(".add-copy-template").hide();
            }
        }
        return false;
    });
    $('.input_text').bind('focus', function() {
        var name = $(this).attr("name");
        var id  = "";
        if(name.indexOf("copy_text_template_", 0) !== -1) {
            id = name.replace("copy_text_template_","");
            $("input[name=new_text_template_"+id+"]").val("");
        }
        else {
            id = name.replace("new_text_template_","");
            $("input[name=copy_text_template_"+id+"]").val("");
        }
    });
}

function bindCmsPageScript() {
    if ($('draggable-class').length) {
        $('.draggable-class').draggable( {
            helper: 'clone'
        });
    }
    if ($('droppable-class').length) {
        $('.droppable-class').droppable({
            drop: function(event, ui){
                var dragId = ui.draggable.attr("id").substr(10);
                var dropId = $(this).attr("id");
                dropIds = $(this).attr("id").split("_");
                dropId = dropIds[1];
                //if(dropId.indexOf("child_", 0))
                var pageIds = window.location.toString().split("list/");
                var pageId = pageIds[1];
                if(dragId != dropId) {
                    var queryString = {
                        "responseType"  :   "html",
                        "pageId"        :   pageId,
                        "action"        :   "positionUpdate",
                        "drag_cms_uid"  :   dragId,
                        "dragType"      :   dropIds[0],
                        "drop_cms_uid"  :   dropId
                    };
                    callAjax(queryString,'positionUpdate','html');
                }
                if (ui.draggable.hasClass('drop')) {
                    $(this).append(ui.draggable);
                    ui.draggable.css('top', ui.position.top);
                    ui.draggable.css('left', ui.position.left);
                    ui.draggable.css('position', 'absolute');
                    ui.draggable.draggable('destroy').draggable(); /* need to reset the draggability */
                }
            }
        });
    }
}

function bindTemplateTagDeleteScript() {
    $('.tag-delete').unbind('click');
    $('.tag-delete').bind('click', function() {
        var template_tag_uid = $(this).attr("href").replace("#delete-tag-","");
        if(template_tag_uid <= 0) {
            return false;
        }
        else {
            var queryString = {
                "responseType"              :   "html",
                "template_tag_uid"          :   template_tag_uid,
                "action"                    :   "templateTagsDelete"
            };
            callAjax(queryString,'templateTagsDelete','html');
        }
        return false;
    });
}

function clear_form_elements(ele) {
    $(ele).find(':input').each(function() {
        switch(this.type) {
            case 'hidden':
            case 'password':
            case 'select-multiple':
            case 'select-one':
            case 'text':
            case 'textarea':
                $(this).val('');
                break;
            case 'checkbox':
            case 'radio':
                this.checked = false;
        }
    });
    bindInputElements();
}

function bindCmsTemplateTagsScript() {
    $('.cms-template-tags-submit').bind({
        click: function(){
            var cms_uid         =   $("input[name=cms-uid]").val();
            var $inputs         =   $('#form-template-tags :input');
            var input_id        =   "";
            var values          =   {};
            var queryString = {
                "responseType"          :   "html",
                "action"                :   "add-update-cms-template-tag",
                "cms_uid"               :   cms_uid
            };
            $inputs.each(function() {
                input_id =  $(this).attr("id").replace("template-input-tag-","");
                switch($(this).attr("type")) {
                    case "text":
                    case "hidden":
                        queryString[input_id] = $(this).val();
                        break;
                }
            });
            callAjax(queryString,"add-update-cms-template-tag",'html');
        }
    });

    // to ajax upload with jquery
    var $inputs         =   $('#form-template-tags :input');
    $inputs.each(function() {
        switch($(this).attr("type")) {
            case "file":
                var fileId  = $(this).attr("id");
                var tagId   = fileId.replace("template-file-tag-","");
                new AjaxUpload(fileId, {
                    action  : url + 'admin/page-update/',
                    name    : 'myfile',
                    data    :   {
                        action : "file-upload",
                        responseType : "html"
                    },
                    onSubmit : function(file , ext){
                        /*if (! (ext && /^(jpg|png|jpeg|gif)$/i.test(ext))){
                            // extension is not allowed
                            alert('Error: invalid file extension');
                            // cancel upload
                            return false;
                        }
                        else {
                            this.disable();
                        }*/
                        this.disable();
                    },
                    onComplete : function(file,html){
                        var response    = $(html).find("div[id=response_data]").html();
                        var success     = $(html).find("div[id=response_success]").html();
                        var error       = $(html).find("div[id=response_error]").html();
                        if(error.trim() != "") {
                            growlAlert(error.trim(),'Failed',url+'images/downarrow.png',1000);
                            this.enable();
                        }
                        else if(success.trim() != "") {
                            growlAlert(success.trim(),'Success',url+'images/downarrow.png',1000);
                            $("input[name=template-input-tag-"+tagId+"]").val(response);
                            $('<li></li>').appendTo($('#template-tag-'+tagId+" .files")).text(file);
                        }
                    }
                });
                break;
        }
    });
}

function bindIpManagerScript() {
    /* ip manager related script */
    $('.submint-ip').unbind('click');
    $('.ip-remove').unbind('click');
    $('.submint-ip').bind({
        click: function(){
            var ip_rule         =   $("input[name=ip-rule]").val();
            if(ip_rule != "") {
                var queryString = {
                    "responseType"          :   "html",
                    "action"                :   "add-ip-rule",
                    "ip_rule"               :   ip_rule
                };
                callAjax(queryString,"add-ip-rule",'html');
            }
        }
    });

    $('.ip-remove').bind({
        click: function(){
            var ip_uid         =   $(this).attr("id").replace("ip-","");
            if(ip_uid != "") {
                var queryString = {
                    "responseType"          :   "html",
                    "action"                :   "remove-ip-rule",
                    "ip_uid"                :   ip_uid
                };
                callAjax(queryString,"remove-ip-rule",'html');
            }
        }
    });
}

function bindTagScript() {

    clear_form_elements("#form-tags-add-update");
    $("#add-update-buttons").fadeIn("1000");

    $('.add-new-tag').bind({
        click: function(){
            clear_form_elements("#form-tags-add-update");
            $("#add-update-buttons").fadeIn("1000");
        }
    });
    $('.add-update-tags').bind({
        click: function(){
            switch($(this).attr("name")) {
                case "submit-form":
                    var tag_page_id_arr = window.location.toString().split("tags/");
                    var page_id         = tag_page_id_arr[1]?tag_page_id_arr[1]:1;
                    var tag_uid         = $("input[name=tag-uid]").val();
                    var tag_name        = $("input[name=tag-name]").val();
                    var tag_slug        = $("input[name=tag-slug]").val();
                    var tag_description = $("textarea[name=tag-description]").val();
                    var queryString = {
                        "responseType"          :   "html",
                        "action"                :   "add-update-tag",
                        "page_id"               :   page_id,
                        "tag_uid"               :   tag_uid,
                        "tag_name"              :   tag_name,
                        "tag_slug"              :   tag_slug,
                        "tag_description"       :   tag_description
                    };
                    callAjax(queryString,"add-update-tag",'html');
                    break;
                case "reset-form":
                    clear_form_elements("#form-tags-add-update");
                    $("#add-update-buttons").fadeOut("1000");
                    break;
            }
        }
    });
    $('.edit-tag').bind({
        click: function(){
            var tag_uid         =   $(this).attr("id").replace("edit-tag-","");
            if(tag_uid != "") {
                clear_form_elements("#form-tags-add-update");
                $("#add-update-buttons").fadeIn("1000");
                var queryString = {
                    "responseType"          :   "xml",
                    "action"                :   "get-tag-data",
                    "tag_uid"               :   tag_uid
                };
                callAjax(queryString,"get-tag-data",'xml');
            }
        }
    });

    $('.tag-delete').bind({
        click: function(){
            var tag_page_id_arr =   window.location.toString().split("tags/");
            var page_id         =   tag_page_id_arr[1]?tag_page_id_arr[1]:1;
            var tag_uid         =   $(this).attr("id").replace("delete-","");
            if(tag_uid != "") {
                var queryString = {
                    "responseType"          :   "html",
                    "action"                :   "delete-tag",
                    "page_id"               :   page_id,
                    "tag_uid"               :   tag_uid
                };
                callAjax(queryString,"delete-tag",'html');
            }
        }
    });

    $('.bulk-actions').bind({
        click: function(){
            var tag_page_id_arr =   window.location.toString().split("tags/");
            var page_id         =   tag_page_id_arr[1]?tag_page_id_arr[1]:1;
            var name            =   $(this).attr("name").replace("bulk-action-","");
            var tag_uids        =   new Array();
            if($("#tag-actions-"+name).val() == "delete") {
                var i           =   0;
                $('.check-tag').each(function() {
                    var checked = $(this).attr("checked");
                    if(checked  == true) {
                        tag_uids[i++] = $(this).attr("id").replace("chk-","");
                    }
                });
                if(tag_uids.length > 0) {
                    if(confirm('Are you sure you wish to delete the tag/s selected?')) {
                        var queryString = {
                            "responseType"          :   "html",
                            "action"                :   "delete-multiple-tags",
                            "page_id"               :   page_id,
                            "tag_uids"              :   tag_uids.join(",")
                        };
                        callAjax(queryString,"delete-multiple-tags",'html');
                    }
                }
            }
        }
    });
}

function bindFileManagerScript() {

    if($('#select-file').length > 0) {
        var button = $('#select-file'), interval;

        new AjaxUpload(button, {
            action  : url + 'admin/page-update/',
            name    : 'myfile',
            data    :   {
                action          : "file-manager-upload",
                responseType    : "xml"
            },
            onSubmit : function(){
                // change button text, when user selects file
                button.text('Uploading');
                this.disable();

                // Uploding -> Uploading. -> Uploading...
                interval = window.setInterval(function(){
                    var text = button.text();
                    if (text.length < 13){
                        button.text(text + '.');
                    } else {
                        button.text('Uploading');
                    }
                }, 200);
            },
            onComplete: function(file, xml){
                button.text('Upload');
                window.clearInterval(interval);
                // enable upload button
                this.enable();
                // add file to the list
                var error       = $("response",xml).attr('error');
                if(error == 0) {
                    var file_uid        = $("response",xml).attr('file_uid');
                    var filepath        = $("response",xml).attr('filepath');
                    var title           = $("response",xml).attr('title');
                    var description     = $("response",xml).attr('description');
                    var alt             = $("response",xml).attr('alt');
                    $("input[name=file-uid]").val(file_uid);
                    $("input[name=file-title]").val(title);
                    $("textarea[name=file-descr]").val(description);
                    $("input[name=file-alt]").val(alt);
                    var filepatharr     = filepath.split("/");
                    filepath            = filepatharr[filepatharr.length - 1];
                    $("select[name=select-theme]").val(filepath);
                    $("#files-add").fadeIn("1000");
                }
                else {
                    growlAlert("Error getting data",'Failed',url+'images/downarrow.png',1000);
                }
            }
        });
    }
    
    $('#submit-file-changes').bind({
        click: function(){
            var file_uid        = $("input[name=file-uid]").val();
            var file_title      = $("input[name=file-title]").val();
            var file_alt        = $("input[name=file-alt]").val();
            var file_path       = $("select[name=select-theme]").val();
            var file_descr      = $("textarea[name=file-descr]").val();
            if(file_uid != "") {
                var queryString = {
                    "responseType"      :   "html",
                    "action"            :   "update-file-manager",
                    "file_uid"          :   file_uid,
                    "file_title"        :   file_title,
                    "file_alt"          :   file_alt,
                    "file_path"         :   file_path,
                    "file_descr"        :   file_descr
                };
                callAjax(queryString,"update-file-manager",'html');
            }
        }
    });

    $('.files').bind({
        click: function(){
            
            var href_link       =   $(this).attr("href");
            href_link           =   href_link.replace("#","");
            var link_array      =   href_link.split("-");
            var file_uid        =   link_array[2];
            if(file_uid != "") {
                switch(link_array[0]) {
                    case "edit":
                        var queryString = {
                            "responseType"      :   "xml",
                            "action"            :   "get-file-manager-data",
                            "file_uid"          :   file_uid
                        };
                        $("#files-add").css("display","none");
                        callAjax(queryString,"get-file-manager-data",'xml');
                        break;
                    case "delete":
                        var queryString = {
                            "responseType"      :   "xml",
                            "action"            :   "delete-file-manager-data",
                            "file_uid"          :   file_uid
                        };
                        $("#files-add").css("display","none");
                        callAjax(queryString,"delete-file-manager-data",'xml');
                        break;
                    case "view":
                        alert(file_uid);
                        break;
                }
            }
        }
    });

    $('#delete-file').bind({
        click: function(){
            var file_uid        =   $("input[name=file-uid]").val();
            if(file_uid != "") {
                var queryString = {
                    "responseType"      :   "xml",
                    "action"            :   "delete-file-manager-data",
                    "file_uid"          :   file_uid
                };
                $("#files-add").css("display","none");
                callAjax(queryString,"delete-file-manager-data",'xml');
            }
        }
    });
}

function bindMenuManagerScript() {
    $('.save-menu').bind({
        click: function(){
            var menu_title          =   "";
            var menu_tag            =   "";
            var menu_uid            =   "";
            var menu_can_be_sub     =   "";
            var menu_snippet        =   0;
            var action              =   ($(this).attr("id")=="save-menu")?"add-menu":"update-menu";
            if(action == "update-menu") {
                menu_uid            =   $(this).attr("id").replace("update-menu-","");
                menu_title          =   $("input[name=menu-title-"+menu_uid+"]").val();
                menu_tag            =   $("input[name=menu-tag-"+menu_uid+"]").val();
                menu_can_be_sub     =   $("select[name=menu-can-be-submenu-"+menu_uid+"]:checked").val();
                menu_snippet        =   $("select[name=menu-snippet-"+menu_uid+"]").val();
            }
            else {
                menu_title          =   $("input[name=menu-title]").val();
                menu_tag            =   $("input[name=menu-tag]").val();
                menu_can_be_sub     =   $("select[name=menu-can-be-submenu]").val();
                menu_snippet        =   $("select[name=menu-snippet]").val();
            }
            var queryString = {
                "responseType"      :   "html",
                "action"            :   action,
                "menu_uid"          :   menu_uid,
                "menu_title"        :   menu_title,
                "menu_snippet"      :   menu_snippet,
                "menu_can_be_sub"   :   menu_can_be_sub,
                "menu_tag"          :   menu_tag
            };
            callAjax(queryString,action,'html');
        }
    });

    $(".menu-item").bind({
        click: function(){
            var classes         =   $(this).attr("class").split(" ");
            var menu_uid        =   classes[3].replace("menu-","");
            var menu_item_uid   =   classes[2].replace("item-","");
            if(classes.indexOf("cancel") !== -1) {
                clear_form_elements("#form-menu-item-"+menu_item_uid+"-"+menu_uid);
            }
            else if(classes.indexOf("save") !== -1) {
                var menu_link_type      =   $("#form-menu-item-"+menu_item_uid+"-"+menu_uid).find("select[name=menu-link-type]").val();
                var menu_item_name      =   $("#form-menu-item-"+menu_item_uid+"-"+menu_uid).find("input[name=menu-item-name]").val();
                var menu_item_snippet   =   $("#form-menu-item-"+menu_item_uid+"-"+menu_uid).find("select[name=menu-item-snippet]").val();
                var menu_link_value     =   0;
                var sub_menu_uid        =   0;
                var has_sub_menu        =   $("#form-menu-item-"+menu_item_uid+"-"+menu_uid).find("input[name=menu-has-submenu]:checked").val();
                switch(menu_link_type) {
                    case "1":
                        menu_link_value = $("#form-menu-item-"+menu_item_uid+"-"+menu_uid).find("select[name=menu-cms-pages]").val();
                        break;
                    case "2":
                        menu_link_value = $("#form-menu-item-"+menu_item_uid+"-"+menu_uid).find("input[name=menu-link-url]").val();
                        break;
                }
                if(has_sub_menu == 1) {
                    sub_menu_uid        =   $("#form-menu-item-"+menu_item_uid+"-"+menu_uid).find("select[name=menu-sub-menu]").val();
                }
                var queryString = {
                    "responseType"      :   "html",
                    "action"            :   "add-update-menu-item",
                    "menu_uid"          :   menu_uid,
                    "menu_item_uid"     :   menu_item_uid,
                    "menu_item_name"    :   menu_item_name,
                    "menu_item_snippet" :   menu_item_snippet,
                    "has_submenu"       :   has_sub_menu,
                    "sub_menu_uid"      :   sub_menu_uid,
                    "menu_link_type"    :   menu_link_type,
                    "menu_link_value"   :   menu_link_value
                };
                callAjax(queryString,"add-update-menu-item",'html');
            }
        }
    });

    if($('.draggable-menu-class').length > 0) {
        $('.draggable-menu-class').draggable( {
            helper: 'clone'
        });
    }

    if($('.droppable-menu-class').length > 0) {
        $('.droppable-menu-class').droppable({
            drop: function(event, ui){
                var dragIds = ui.draggable.attr("id").split("-");
                var dropIds = $(this).attr("id").split("-");
            
                var dragId      =   dragIds[2];
                var dragMenuId  =   dragIds[3];

                var dropId      =   dropIds[2];
                var dropMenuId  =   dropIds[3];
                if(dragMenuId != dropMenuId) {
                    return; // not outside its parent move
                }
                if(dragId != dropId) {
                    var queryString = {
                        "responseType"          :   "html",
                        "action"                :   "menuPositionUpdate",
                        "drag_menu_item_uid"    :   dragId,
                        "drop_menu_item_uid"    :   dropId,
                        "menu_uid"              :   dragMenuId
                    };
                    callAjax(queryString,'menuPositionUpdate','html');
                }
                if (ui.draggable.hasClass('drop')) {
                    $(this).append(ui.draggable);
                    ui.draggable.css('top', ui.position.top);
                    ui.draggable.css('left', ui.position.left);
                    ui.draggable.css('position', 'absolute');
                    ui.draggable.draggable('destroy').draggable(); /* need to reset the draggability */
                }
            }
        });
    }
}

function bindUserManagerScript() {
    $('#submit-user').bind({
        click: function(){
            var user_uid            =   $("input[name=user_uid]").val();
            var email               =   $("input[name=email]").val();
            var password            =   $("input[name=password]").val();
            var conf_password       =   $("input[name=conf_password]").val();
            var allow_access        =   $("input[name=allow_access]").attr("checked")?1:0;
            var is_admin            =   $("input[name=is_admin]").attr("checked")?1:0;
            var deleted             =   $("input[name=deleted]").attr("checked")?1:0;
            var action              =   (user_uid != "")?"update-user":"new-user";
			
			var allow_access_without_sub            =   $("input[name=allow_access_without_sub]").attr("checked")?1:0;
			var optin            =   $("input[name=optin]").attr("checked")?1:0;
			var referral               =   $("#referral").val();
			
            var queryString = {
                "responseType"          :   "html",
                "action"                :   action,
                "user_uid"              :   user_uid,
                "email"                 :   email,
                "password"              :   password,
                "conf_password"         :   conf_password,
                "allow_access"          :   allow_access,
                "is_admin"              :   is_admin,
                "deleted"               :   deleted,
				"allow_access_without_sub"              :   allow_access_without_sub,
				"optin"              :   optin,
				"referral"              :   referral
            };
			queryString = $('#user-form').serialize();
			queryString +='&responseType=html&action='+action;
			/*alert(queryString);
			return false;*/
            callAjaxDynamic("admin/users/user-update/",queryString,action,'html');
        }
    });
}

function bindFormBuilderScript() {
    $('.add-new-form').unbind('click');
    $('.add-new-form').bind('click', function() {
        //$('.add-new-template').click(function() {
        var Class = $(this).attr("class");
        var ClassArr = Class.split(" ");
        var form_type_uid = "";
        if($(this).html() == "Add New") {
            form_type_uid = ClassArr[1].toString().replace("add-new-","");
            // this will be removed by u as it hides all the rows
            $(".add-form").hide();
            $(".button-events").show();
            if(form_type_uid != "") {
                $("#add-form-"+form_type_uid).show(400);
                $("#button-events-" + form_type_uid).hide();
            }
        }
        else if($(this).html() == "Add Form") {
            form_type_uid = ClassArr[1].toString().replace("new-","");
            if(form_type_uid != "") {

                var formName        = $("input[id=form-name-"+form_type_uid+"]").val();
                var formSlug        = $("input[id=form-slug-"+form_type_uid+"]").val();
                var formDescription = $("input[id=form-description-"+form_type_uid+"]").val();
                var formTemplate    = $("select[id=form-template-"+form_type_uid+"]").val();

                /* Fire Ajax Here */
                var queryString = {
                    "responseType"      :   "html",
                    "form_type_uid"     :   form_type_uid,
                    "action"            :   "formCreate",
                    "formName"          :   formName,
                    "formSlug"          :   formSlug,
                    "formDescription"   :   formDescription,
                    "formTemplate"      :   formTemplate
                };
                callAjax(queryString,'formCreate','html');
            }
        /* Fire Ajax Here */
        }
        else if($(this).html() == "Cancel") {
            form_type_uid = ClassArr[1].toString().replace("cancel-","");
            if(form_type_uid != "") {
                clear_form_elements("#form-add-new-"+form_type_uid);
                $("#button-events-" + form_type_uid).show();
                $(".add-form").hide();
            }
        }
        return false;
    });
    $('.input_text').bind('focus', function() {
        var name = $(this).attr("name");
        var id  = "";
        if(name.indexOf("copy_text_template_", 0) !== -1) {
            id = name.replace("copy_text_template_","");
            $("input[name=new_text_template_"+id+"]").val("");
        }
        else {
            id = name.replace("new_text_template_","");
            $("input[name=copy_text_template_"+id+"]").val("");
        }
    });
}

function bindContactNumberTypeScript(){
    clear_form_elements("#form-contact-number-type-add-update");
    $("#add-update-buttons").fadeIn("1000");

    $('.add-new-contact-number-type').bind({
        click: function(){
            clear_form_elements("#form-contact-number-type-add-update");
            $("#add-update-buttons").fadeIn("1000");
        }
    });
    $('.add-update-contact-number-type').bind({
        click: function(){
            switch($(this).attr("name")) {
                case "submit-form":
                    var page_id_arr                 = window.location.toString().split("tags/");
                    var page_id                     = page_id_arr[1]?page_id_arr[1]:1;
                    var contact_number_type_uid     = $("input[name=contact-number-type-uid]").val();
                    var name                        = $("input[name=name]").val();
                    var description                 = $("textarea[name=description]").val();
                    var is_active                   = $("input[name=is_active]:checked").val();
                    var queryString = {
                        "responseType"              :   "html",
                        "action"                    :   "contact-number-type-update",
                        "page_id"                   :   page_id,
                        "contact_number_type_uid"   :   contact_number_type_uid,
                        "name"                      :   name,
                        "is_active"                 :   is_active,
                        "description"               :   description
                    };
                    callAjaxDynamic("admin/library/contact-number-type/contact-number-type-action/",queryString,"contact-number-type-update",'html');
                    break;
                case "reset-form":
                    clear_form_elements("#form-contact-number-type-add-update");
                    $("#add-update-buttons").fadeOut("1000");
                    break;
            }
        }
    });
    $('.contact-number-type').bind({
        click: function(){
            var contact_number_type_uid    =   $(this).attr("id").replace("contact-number-type-","");
            if(contact_number_type_uid != "") {
                clear_form_elements("#form-contact-number-type-add-update");
                $("#add-update-buttons").fadeIn("1000");
                var queryString = {
                    "responseType"              :   "xml",
                    "action"                    :   "get-contact-number-type-data",
                    "contact_number_type_uid"   :   contact_number_type_uid
                };
                callAjaxDynamic("admin/library/contact-number-type/contact-number-type-action/",queryString,"get-contact-number-type-data",'xml');
            }
        }
    });

    $('.contact-number-type-delete').bind({
        click: function(){
            var page_id_arr                 =   window.location.toString().split("tags/");
            var page_id                     =   page_id_arr[1]?page_id_arr[1]:1;
            var contact_number_type_uid    =   $(this).attr("id").replace("delete-","");
            if(contact_number_type_uid != "") {
                var queryString = {
                    "responseType"              :   "html",
                    "action"                    :   "delete-contact-number-type",
                    "page_id"                   :   page_id,
                    "contact_number_type_uid"   :   contact_number_type_uid
                };
                callAjaxDynamic("admin/library/contact-number-type/contact-number-type-action/",queryString,"delete-contact-number-type",'html');
            }
        }
    });

    $('.bulk-actions-contact-number-type').bind({
        click: function(){
            var name            =   $(this).attr("name").replace("bulk-action-","");
            var tag_uids        =   new Array();
            if($("#contact-number-type-actions-"+name+" :selected").val() == "delete") {
                var i           =   0;
                $('.check-contact-number-type').each(function() {
                    var checked = $(this).attr("checked");
                    if(checked  == true) {
                        tag_uids[i++] = $(this).attr("id").replace("chk-","");
                    }
                });
                if(tag_uids.length > 0) {
                    if(confirm('Are you sure you wish to delete the tag/s selected?')) {
                        var queryString = {
                            "responseType"          :   "html",
                            "action"                :   "delete-multiple-contact-number-type",
                            "uids"                  :   tag_uids.join(",")
                        };
                        callAjaxDynamic("admin/library/contact-number-type/contact-number-type-action/",queryString,"delete-multiple-contact-number-type",'html');
                    }
                }
            }
        }
    });
}

function bindCreditOptionsScript(){
    clear_form_elements("#form-credit-options-add-update");
    $("#add-update-buttons").fadeIn("1000");

    $('.add-new-credit-options').bind({
        click: function(){
            clear_form_elements("#form-credit-options-add-update");
            $("#add-update-buttons").fadeIn("1000");
        }
    });
    $('.add-update-credit-options').bind({
        click: function(){
            switch($(this).attr("name")) {
                case "submit-form":
                    var page_id_arr                 = window.location.toString().split("tags/");
                    var page_id                     = page_id_arr[1]?page_id_arr[1]:1;
                    var credit_options_uid          = $("input[name=credit-options-uid]").val();
                    var name                        = $("input[name=name]").val();
                    var description                 = $("textarea[name=description]").val();
                    var is_active                   = $("input[name=is_active]:checked").val();
                    var queryString = {
                        "responseType"              :   "html",
                        "action"                    :   "credit-options-update",
                        "page_id"                   :   page_id,
                        "credit_options_uid"        :   credit_options_uid,
                        "name"                      :   name,
                        "is_active"                 :   is_active,
                        "description"               :   description
                    };
                    callAjaxDynamic("admin/library/credit-options/credit-options-action/",queryString,"credit-options-update",'html');
                    break;
                case "reset-form":
                    clear_form_elements("#form-credit-options-add-update");
                    $("#add-update-buttons").fadeOut("1000");
                    break;
            }
        }
    });
    $('.credit-options').bind({
        click: function(){
            var credit_options_uid    =   $(this).attr("id").replace("credit-options-","");
            if(credit_options_uid != "") {
                clear_form_elements("#form-credit-options-add-update");
                $("#add-update-buttons").fadeIn("1000");
                var queryString = {
                    "responseType"              :   "xml",
                    "action"                    :   "get-credit-options-data",
                    "credit_options_uid"        :   credit_options_uid
                };
                callAjaxDynamic("admin/library/credit-options/credit-options-action/",queryString,"get-credit-options-data",'xml');
            }
        }
    });

    $('.credit-options-delete').bind({
        click: function(){
            var page_id_arr                 =   window.location.toString().split("tags/");
            var page_id                     =   page_id_arr[1]?page_id_arr[1]:1;
            var credit_options_uid          =   $(this).attr("id").replace("delete-","");
            if(credit_options_uid != "") {
                var queryString = {
                    "responseType"              :   "html",
                    "action"                    :   "delete-credit-options",
                    "page_id"                   :   page_id,
                    "credit_options_uid"        :   credit_options_uid
                };
                callAjaxDynamic("admin/library/credit-options/credit-options-action/",queryString,"delete-credit-options",'html');
            }
        }
    });

    $('.bulk-actions-credit-options').bind({
        click: function(){
            var name            =   $(this).attr("name").replace("bulk-action-","");
            var tag_uids        =   new Array();
            if($("#credit-options-actions-"+name+" :selected").val() == "delete") {
                var i           =   0;
                $('.check-credit-options').each(function() {
                    var checked = $(this).attr("checked");
                    if(checked  == true) {
                        tag_uids[i++] = $(this).attr("id").replace("chk-","");
                    }
                });
                if(tag_uids.length > 0) {
                    if(confirm('Are you sure you wish to delete the tag/s selected?')) {
                        var queryString = {
                            "responseType"          :   "html",
                            "action"                :   "delete-multiple-credit-options",
                            "uids"                  :   tag_uids.join(",")
                        };
                        callAjaxDynamic("admin/library/credit-options/credit-options-action/",queryString,"delete-multiple-credit-options",'html');
                    }
                }
            }
        }
    });
}

function bindEmailAddressTypeScript(){
    clear_form_elements("#form-email-address-type-add-update");
    $("#add-update-buttons").fadeIn("1000");

    $('.add-new-email-address-type').bind({
        click: function(){
            clear_form_elements("#form-email-address-type-add-update");
            $("#add-update-buttons").fadeIn("1000");
        }
    });
    $('.add-update-email-address-type').bind({
        click: function(){
            switch($(this).attr("name")) {
                case "submit-form":
                    var page_id_arr                 = window.location.toString().split("tags/");
                    var page_id                     = page_id_arr[1]?page_id_arr[1]:1;
                    var email_address_type_uid      = $("input[name=email-address-type-uid]").val();
                    var name                        = $("input[name=name]").val();
                    var description                 = $("textarea[name=description]").val();
                    var is_active                   = $("input[name=is_active]:checked").val();
                    var queryString = {
                        "responseType"              :   "html",
                        "action"                    :   "email-address-type-update",
                        "page_id"                   :   page_id,
                        "email_address_type_uid"    :   email_address_type_uid,
                        "name"                      :   name,
                        "is_active"                 :   is_active,
                        "description"               :   description
                    };
                    callAjaxDynamic("admin/library/email-address-type/email-address-type-action/",queryString,"email-address-type-update",'html');
                    break;
                case "reset-form":
                    clear_form_elements("#form-email-address-type-add-update");
                    $("#add-update-buttons").fadeOut("1000");
                    break;
            }
        }
    });
    $('.email-address-type').bind({
        click: function(){
            var email_address_type_uid    =   $(this).attr("id").replace("email-address-type-","");
            if(email_address_type_uid != "") {
                clear_form_elements("#form-email-address-type-add-update");
                $("#add-update-buttons").fadeIn("1000");
                var queryString = {
                    "responseType"              :   "xml",
                    "action"                    :   "get-email-address-type-data",
                    "email_address_type_uid"    :   email_address_type_uid
                };
                callAjaxDynamic("admin/library/email-address-type/email-address-type-action/",queryString,"get-email-address-type-data",'xml');
            }
        }
    });

    $('.email-address-type-delete').bind({
        click: function(){
            var page_id_arr                 =   window.location.toString().split("tags/");
            var page_id                     =   page_id_arr[1]?page_id_arr[1]:1;
            var email_address_type_uid      =   $(this).attr("id").replace("delete-","");
            if(email_address_type_uid != "") {
                var queryString = {
                    "responseType"              :   "html",
                    "action"                    :   "delete-email-address-type",
                    "page_id"                   :   page_id,
                    "email_address_type_uid"    :   email_address_type_uid
                };
                callAjaxDynamic("admin/library/email-address-type/email-address-type-action/",queryString,"delete-email-address-type",'html');
            }
        }
    });

    $('.bulk-actions-email-address-type').bind({
        click: function(){
            var name            =   $(this).attr("name").replace("bulk-action-","");
            var tag_uids        =   new Array();
            if($("#email-address-type-actions-"+name+" :selected").val() == "delete") {
                var i           =   0;
                $('.check-email-address-type').each(function() {
                    var checked = $(this).attr("checked");
                    if(checked  == true) {
                        tag_uids[i++] = $(this).attr("id").replace("chk-","");
                    }
                });
                if(tag_uids.length > 0) {
                    if(confirm('Are you sure you wish to delete the email address type/s selected?')) {
                        var queryString = {
                            "responseType"          :   "html",
                            "action"                :   "delete-multiple-email-address-type",
                            "uids"                  :   tag_uids.join(",")
                        };
                        callAjaxDynamic("admin/library/email-address-type/email-address-type-action/",queryString,"delete-multiple-email-address-type",'html');
                    }
                }
            }
        }
    });
}

function bindLeadSourcesScript(){
    clear_form_elements("#form-lead-sources-add-update");
    $("#add-update-buttons").fadeIn("1000");

    $('.add-new-lead-sources').bind({
        click: function(){
            clear_form_elements("#form-lead-sources-add-update");
            $("#add-update-buttons").fadeIn("1000");
        }
    });
    $('.add-update-lead-sources').bind({
        click: function(){
            switch($(this).attr("name")) {
                case "submit-form":
                    var page_id_arr                 = window.location.toString().split("tags/");
                    var page_id                     = page_id_arr[1]?page_id_arr[1]:1;
                    var lead_sources_uid            = $("input[name=lead-sources-uid]").val();
                    var name                        = $("input[name=name]").val();
                    var description                 = $("textarea[name=description]").val();
                    var is_active                   = $("input[name=is_active]:checked").val();
                    var queryString = {
                        "responseType"              :   "html",
                        "action"                    :   "lead-sources-update",
                        "page_id"                   :   page_id,
                        "lead_sources_uid"          :   lead_sources_uid,
                        "name"                      :   name,
                        "is_active"                 :   is_active,
                        "description"               :   description
                    };
                    callAjaxDynamic("admin/library/lead-sources/lead-sources-action/",queryString,"lead-sources-update",'html');
                    break;
                case "reset-form":
                    clear_form_elements("#form-lead-sources-add-update");
                    $("#add-update-buttons").fadeOut("1000");
                    break;
            }
        }
    });
    $('.lead-sources').bind({
        click: function(){
            var lead_sources_uid    =   $(this).attr("id").replace("lead-sources-","");
            if(lead_sources_uid != "") {
                clear_form_elements("#form-lead-sources-add-update");
                $("#add-update-buttons").fadeIn("1000");
                var queryString = {
                    "responseType"              :   "xml",
                    "action"                    :   "get-lead-sources-data",
                    "lead_sources_uid"          :   lead_sources_uid
                };
                callAjaxDynamic("admin/library/lead-sources/lead-sources-action/",queryString,"get-lead-sources-data",'xml');
            }
        }
    });

    $('.lead-sources-delete').bind({
        click: function(){
            var page_id_arr                 =   window.location.toString().split("tags/");
            var page_id                     =   page_id_arr[1]?page_id_arr[1]:1;
            var lead_sources_uid            =   $(this).attr("id").replace("delete-","");
            if(lead_sources_uid != "") {
                var queryString = {
                    "responseType"              :   "html",
                    "action"                    :   "delete-lead-sources",
                    "page_id"                   :   page_id,
                    "lead_sources_uid"          :   lead_sources_uid
                };
                callAjaxDynamic("admin/library/lead-sources/lead-sources-action/",queryString,"delete-lead-sources",'html');
            }
        }
    });

    $('.bulk-actions-lead-sources').bind({
        click: function(){
            var name            =   $(this).attr("name").replace("bulk-action-","");
            var tag_uids        =   new Array();
            if($("#lead-sources-actions-"+name+" :selected").val() == "delete") {
                var i           =   0;
                $('.check-lead-sources').each(function() {
                    var checked = $(this).attr("checked");
                    if(checked  == true) {
                        tag_uids[i++] = $(this).attr("id").replace("chk-","");
                    }
                });
                if(tag_uids.length > 0) {
                    if(confirm('Are you sure you wish to delete the lead source/s selected?')) {
                        var queryString = {
                            "responseType"          :   "html",
                            "action"                :   "delete-multiple-lead-sources",
                            "uids"                  :   tag_uids.join(",")
                        };
                        callAjaxDynamic("admin/library/lead-sources/lead-sources-action/",queryString,"delete-multiple-lead-sources",'html');
                    }
                }
            }
        }
    });
}

function bindRelationshipTypeScript(){
    clear_form_elements("#form-relationship-type-add-update");
    $("#add-update-buttons").fadeIn("1000");

    $('.add-new-relationship-type').bind({
        click: function(){
            clear_form_elements("#form-relationship-type-add-update");
            $("#add-update-buttons").fadeIn("1000");
        }
    });
    $('.add-update-relationship-type').bind({
        click: function(){
            switch($(this).attr("name")) {
                case "submit-form":
                    var page_id_arr                 = window.location.toString().split("tags/");
                    var page_id                     = page_id_arr[1]?page_id_arr[1]:1;
                    var relationship_type_uid       = $("input[name=relationship-type-uid]").val();
                    var name                        = $("input[name=name]").val();
                    var description                 = $("textarea[name=description]").val();
                    var is_active                   = $("input[name=is_active]:checked").val();
                    var queryString = {
                        "responseType"              :   "html",
                        "action"                    :   "relationship-type-update",
                        "page_id"                   :   page_id,
                        "relationship_type_uid"     :   relationship_type_uid,
                        "name"                      :   name,
                        "is_active"                 :   is_active,
                        "description"               :   description
                    };
                    callAjaxDynamic("admin/library/relationship-type/relationship-type-action/",queryString,"relationship-type-update",'html');
                    break;
                case "reset-form":
                    clear_form_elements("#form-relationship-type-add-update");
                    $("#add-update-buttons").fadeOut("1000");
                    break;
            }
        }
    });
    $('.relationship-type').bind({
        click: function(){
            var relationship_type_uid    =   $(this).attr("id").replace("relationship-type-","");
            if(relationship_type_uid != "") {
                clear_form_elements("#form-relationship-type-add-update");
                $("#add-update-buttons").fadeIn("1000");
                var queryString = {
                    "responseType"              :   "xml",
                    "action"                    :   "get-relationship-type-data",
                    "relationship_type_uid"     :   relationship_type_uid
                };
                callAjaxDynamic("admin/library/relationship-type/relationship-type-action/",queryString,"get-relationship-type-data",'xml');
            }
        }
    });

    $('.relationship-type-delete').bind({
        click: function(){
            var page_id_arr                 =   window.location.toString().split("tags/");
            var page_id                     =   page_id_arr[1]?page_id_arr[1]:1;
            var relationship_type_uid    =   $(this).attr("id").replace("delete-","");
            if(relationship_type_uid != "") {
                var queryString = {
                    "responseType"              :   "html",
                    "action"                    :   "delete-relationship-type",
                    "page_id"                   :   page_id,
                    "relationship_type_uid"     :   relationship_type_uid
                };
                callAjaxDynamic("admin/library/relationship-type/relationship-type-action/",queryString,"delete-relationship-type",'html');
            }
        }
    });

    $('.bulk-actions-relationship-type').bind({
        click: function(){
            var name            =   $(this).attr("name").replace("bulk-action-","");
            var tag_uids        =   new Array();
            if($("#relationship-type-actions-"+name+" :selected").val() == "delete") {
                var i           =   0;
                $('.check-relationship-type').each(function() {
                    var checked = $(this).attr("checked");
                    if(checked  == true) {
                        tag_uids[i++] = $(this).attr("id").replace("chk-","");
                    }
                });
                if(tag_uids.length > 0) {
                    if(confirm('Are you sure you wish to delete the relationship type/s selected?')) {
                        var queryString = {
                            "responseType"          :   "html",
                            "action"                :   "delete-multiple-relationship-type",
                            "uids"                  :   tag_uids.join(",")
                        };
                        callAjaxDynamic("admin/library/relationship-type/relationship-type-action/",queryString,"delete-multiple-relationship-type",'html');
                    }
                }
            }
        }
    });
}

function bindInstantMessengerTypeScript(){
    clear_form_elements("#form-instant-messenger-type-add-update");
    $("#add-update-buttons").fadeIn("1000");

    $('.add-new-instant-messenger-type').bind({
        click: function(){
            clear_form_elements("#form-instant-messenger-type-add-update");
            $("#add-update-buttons").fadeIn("1000");
        }
    });
    $('.add-update-instant-messenger-type').bind({
        click: function(){
            switch($(this).attr("name")) {
                case "submit-form":
                    var page_id_arr                 = window.location.toString().split("tags/");
                    var page_id                     = page_id_arr[1]?page_id_arr[1]:1;
                    var instant_messenger_type_uid  = $("input[name=instant-messenger-type-uid]").val();
                    var name                        = $("input[name=name]").val();
                    var description                 = $("textarea[name=description]").val();
                    var is_active                   = $("input[name=is_active]:checked").val();
                    var queryString = {
                        "responseType"              :   "html",
                        "action"                    :   "instant-messenger-type-update",
                        "page_id"                   :   page_id,
                        "instant_messenger_type_uid":   instant_messenger_type_uid,
                        "name"                      :   name,
                        "is_active"                 :   is_active,
                        "description"               :   description
                    };
                    callAjaxDynamic("admin/library/instant-messenger-type/instant-messenger-type-action/",queryString,"instant-messenger-type-update",'html');
                    break;
                case "reset-form":
                    clear_form_elements("#form-instant-messenger-type-add-update");
                    $("#add-update-buttons").fadeOut("1000");
                    break;
            }
        }
    });
    $('.instant-messenger-type').bind({
        click: function(){
            var instant_messenger_type_uid    =   $(this).attr("id").replace("instant-messenger-type-","");
            if(instant_messenger_type_uid != "") {
                clear_form_elements("#form-instant-messenger-type-add-update");
                $("#add-update-buttons").fadeIn("1000");
                var queryString = {
                    "responseType"              :   "xml",
                    "action"                    :   "get-instant-messenger-type-data",
                    "instant_messenger_type_uid":   instant_messenger_type_uid
                };
                callAjaxDynamic("admin/library/instant-messenger-type/instant-messenger-type-action/",queryString,"get-instant-messenger-type-data",'xml');
            }
        }
    });

    $('.instant-messenger-type-delete').bind({
        click: function(){
            var page_id_arr                 =   window.location.toString().split("tags/");
            var page_id                     =   page_id_arr[1]?page_id_arr[1]:1;
            var instant_messenger_type_uid  =   $(this).attr("id").replace("delete-","");
            if(instant_messenger_type_uid != "") {
                var queryString = {
                    "responseType"              :   "html",
                    "action"                    :   "delete-instant-messenger-type",
                    "page_id"                   :   page_id,
                    "instant_messenger_type_uid":   instant_messenger_type_uid
                };
                callAjaxDynamic("admin/library/instant-messenger-type/instant-messenger-type-action/",queryString,"delete-instant-messenger-type",'html');
            }
        }
    });

    $('.bulk-actions-instant-messenger-type').bind({
        click: function(){
            var name            =   $(this).attr("name").replace("bulk-action-","");
            var tag_uids        =   new Array();
            if($("#instant-messenger-type-actions-"+name+" :selected").val() == "delete") {
                var i           =   0;
                $('.check-instant-messenger-type').each(function() {
                    var checked = $(this).attr("checked");
                    if(checked  == true) {
                        tag_uids[i++] = $(this).attr("id").replace("chk-","");
                    }
                });
                if(tag_uids.length > 0) {
                    if(confirm('Are you sure you wish to delete the instant messenger type/s selected?')) {
                        var queryString = {
                            "responseType"          :   "html",
                            "action"                :   "delete-multiple-instant-messenger-type",
                            "uids"                  :   tag_uids.join(",")
                        };
                        callAjaxDynamic("admin/library/instant-messenger-type/instant-messenger-type-action/",queryString,"delete-multiple-instant-messenger-type",'html');
                    }
                }
            }
        }
    });
}

function bindResidentialStatusScript() {
    clear_form_elements("#form-residential-status-add-update");
    $("#add-update-buttons").fadeIn("1000");

    $('.add-new-residential-status').bind({
        click: function(){
            clear_form_elements("#form-residential-status-add-update");
            $("#add-update-buttons").fadeIn("1000");
        }
    });
    $('.add-update-residential-status').bind({
        click: function(){
            switch($(this).attr("name")) {
                case "submit-form":
                    var page_id_arr                 = window.location.toString().split("tags/");
                    var page_id                     = page_id_arr[1]?page_id_arr[1]:1;
                    var residential_status_uid      = $("input[name=residential-status-uid]").val();
                    var name                        = $("input[name=name]").val();
                    var description                 = $("textarea[name=description]").val();
                    var is_active                   = $("input[name=is_active]:checked").val();
                    var queryString = {
                        "responseType"              :   "html",
                        "action"                    :   "residential-status-update",
                        "page_id"                   :   page_id,
                        "residential_status_uid"    :   residential_status_uid,
                        "name"                      :   name,
                        "is_active"                 :   is_active,
                        "description"               :   description
                    };
                    callAjaxDynamic("admin/library/residential-status/residential-status-action/",queryString,"residential-status-update",'html');
                    break;
                case "reset-form":
                    clear_form_elements("#form-residential-status-add-update");
                    $("#add-update-buttons").fadeOut("1000");
                    break;
            }
        }
    });
    $('.residential-status').bind({
        click: function(){
            var residential_status_uid    =   $(this).attr("id").replace("residential-status-","");
            if(residential_status_uid != "") {
                clear_form_elements("#form-residential-status-add-update");
                $("#add-update-buttons").fadeIn("1000");
                var queryString = {
                    "responseType"              :   "xml",
                    "action"                    :   "get-residential-status-data",
                    "residential_status_uid"    :   residential_status_uid
                };
                callAjaxDynamic("admin/library/residential-status/residential-status-action/",queryString,"get-residential-status-data",'xml');
            }
        }
    });

    $('.residential-status-delete').bind({
        click: function(){
            var page_id_arr                 =   window.location.toString().split("tags/");
            var page_id                     =   page_id_arr[1]?page_id_arr[1]:1;
            var residential_status_uid      =   $(this).attr("id").replace("delete-","");
            if(residential_status_uid != "") {
                var queryString = {
                    "responseType"              :   "html",
                    "action"                    :   "delete-residential-status",
                    "page_id"                   :   page_id,
                    "residential_status_uid"    :   residential_status_uid
                };
                callAjaxDynamic("admin/library/residential-status/residential-status-action/",queryString,"delete-residential-status",'html');
            }
        }
    });

    $('.bulk-actions-residential-status').bind({
        click: function(){
            var name            =   $(this).attr("name").replace("bulk-action-","");
            var tag_uids        =   new Array();
            if($("#residential-status-actions-"+name+" :selected").val() == "delete") {
                var i           =   0;
                $('.check-residential-status').each(function() {
                    var checked = $(this).attr("checked");
                    if(checked  == true) {
                        tag_uids[i++] = $(this).attr("id").replace("chk-","");
                    }
                });
                if(tag_uids.length > 0) {
                    if(confirm('Are you sure you wish to delete the occupancy type/s selected?')) {
                        var queryString = {
                            "responseType"          :   "html",
                            "action"                :   "delete-multiple-residential-status",
                            "uids"                  :   tag_uids.join(",")
                        };
                        callAjaxDynamic("admin/library/residential-status/residential-status-action/",queryString,"delete-multiple-residential-status",'html');
                    }
                }
            }
        }
    });
}

function bindPropertyTitleScript() {
    clear_form_elements("#form-property-title-add-update");
    $("#add-update-buttons").fadeIn("1000");

    $('.add-new-property-title').bind({
        click: function(){
            clear_form_elements("#form-property-title-add-update");
            $("#add-update-buttons").fadeIn("1000");
        }
    });
    $('.add-update-property-title').bind({
        click: function(){
            switch($(this).attr("name")) {
                case "submit-form":
                    var page_id_arr                 = window.location.toString().split("tags/");
                    var page_id                     = page_id_arr[1]?page_id_arr[1]:1;
                    var property_title_uid          = $("input[name=property-title-uid]").val();
                    var name                        = $("input[name=name]").val();
                    var description                 = $("textarea[name=description]").val();
                    var is_active                   = $("input[name=is_active]:checked").val();
                    var queryString = {
                        "responseType"              :   "html",
                        "action"                    :   "property-title-update",
                        "page_id"                   :   page_id,
                        "property_title_uid"        :   property_title_uid,
                        "name"                      :   name,
                        "is_active"                 :   is_active,
                        "description"               :   description
                    };
                    callAjaxDynamic("admin/library/property-title/property-title-action/",queryString,"property-title-update",'html');
                    break;
                case "reset-form":
                    clear_form_elements("#form-property-title-add-update");
                    $("#add-update-buttons").fadeOut("1000");
                    break;
            }
        }
    });
    $('.property-title').bind({
        click: function(){
            var property_title_uid    =   $(this).attr("id").replace("property-title-","");
            if(property_title_uid != "") {
                clear_form_elements("#form-property-title-add-update");
                $("#add-update-buttons").fadeIn("1000");
                var queryString = {
                    "responseType"              :   "xml",
                    "action"                    :   "get-property-title-data",
                    "property_title_uid"        :   property_title_uid
                };
                callAjaxDynamic("admin/library/property-title/property-title-action/",queryString,"get-property-title-data",'xml');
            }
        }
    });

    $('.property-title-delete').bind({
        click: function(){
            var page_id_arr                 =   window.location.toString().split("tags/");
            var page_id                     =   page_id_arr[1]?page_id_arr[1]:1;
            var property_title_uid      =   $(this).attr("id").replace("delete-","");
            if(property_title_uid != "") {
                var queryString = {
                    "responseType"              :   "html",
                    "action"                    :   "delete-property-title",
                    "page_id"                   :   page_id,
                    "property_title_uid"        :   property_title_uid
                };
                callAjaxDynamic("admin/library/property-title/property-title-action/",queryString,"delete-property-title",'html');
            }
        }
    });

    $('.bulk-actions-property-title').bind({
        click: function(){
            var name            =   $(this).attr("name").replace("bulk-action-","");
            var tag_uids        =   new Array();
            if($("#property-title-actions-"+name+" :selected").val() == "delete") {
                var i           =   0;
                $('.check-property-title').each(function() {
                    var checked = $(this).attr("checked");
                    if(checked  == true) {
                        tag_uids[i++] = $(this).attr("id").replace("chk-","");
                    }
                });
                if(tag_uids.length > 0) {
                    if(confirm('Are you sure you wish to delete the property title/s selected?')) {
                        var queryString = {
                            "responseType"          :   "html",
                            "action"                :   "delete-multiple-property-title",
                            "uids"                  :   tag_uids.join(",")
                        };
                        callAjaxDynamic("admin/library/property-title/property-title-action/",queryString,"delete-multiple-property-title",'html');
                    }
                }
            }
        }
    });
}

function bindPropertyTypeScript() {
    clear_form_elements("#form-property-type-add-update");
    $("#add-update-buttons").fadeIn("1000");

    $('.add-new-property-type').bind({
        click: function(){
            clear_form_elements("#form-property-type-add-update");
            $("#add-update-buttons").fadeIn("1000");
        }
    });
    $('.add-update-property-type').bind({
        click: function(){
            switch($(this).attr("name")) {
                case "submit-form":
                    var page_id_arr                 = window.location.toString().split("tags/");
                    var page_id                     = page_id_arr[1]?page_id_arr[1]:1;
                    var property_type_uid           = $("input[name=property-type-uid]").val();
                    var name                        = $("input[name=name]").val();
                    var description                 = $("textarea[name=description]").val();
                    var is_active                   = $("input[name=is_active]:checked").val();
                    var queryString = {
                        "responseType"              :   "html",
                        "action"                    :   "property-type-update",
                        "page_id"                   :   page_id,
                        "property_type_uid"         :   property_type_uid,
                        "name"                      :   name,
                        "is_active"                 :   is_active,
                        "description"               :   description
                    };
                    callAjaxDynamic("admin/library/property-type/property-type-action/",queryString,"property-type-update",'html');
                    break;
                case "reset-form":
                    clear_form_elements("#form-property-type-add-update");
                    $("#add-update-buttons").fadeOut("1000");
                    break;
            }
        }
    });
    $('.property-type').bind({
        click: function(){
            var property_type_uid    =   $(this).attr("id").replace("property-type-","");
            if(property_type_uid != "") {
                clear_form_elements("#form-property-type-add-update");
                $("#add-update-buttons").fadeIn("1000");
                var queryString = {
                    "responseType"              :   "xml",
                    "action"                    :   "get-property-type-data",
                    "property_type_uid"         :   property_type_uid
                };
                callAjaxDynamic("admin/library/property-type/property-type-action/",queryString,"get-property-type-data",'xml');
            }
        }
    });

    $('.property-type-delete').bind({
        click: function(){
            var page_id_arr                 =   window.location.toString().split("tags/");
            var page_id                     =   page_id_arr[1]?page_id_arr[1]:1;
            var property_type_uid      =   $(this).attr("id").replace("delete-","");
            if(property_type_uid != "") {
                var queryString = {
                    "responseType"              :   "html",
                    "action"                    :   "delete-property-type",
                    "page_id"                   :   page_id,
                    "property_type_uid"         :   property_type_uid
                };
                callAjaxDynamic("admin/library/property-type/property-type-action/",queryString,"delete-property-type",'html');
            }
        }
    });

    $('.bulk-actions-property-type').bind({
        click: function(){
            var name            =   $(this).attr("name").replace("bulk-action-","");
            var tag_uids        =   new Array();
            if($("#property-type-actions-"+name+" :selected").val() == "delete") {
                var i           =   0;
                $('.check-property-type').each(function() {
                    var checked = $(this).attr("checked");
                    if(checked  == true) {
                        tag_uids[i++] = $(this).attr("id").replace("chk-","");
                    }
                });
                if(tag_uids.length > 0) {
                    if(confirm('Are you sure you wish to delete the property type/s selected?')) {
                        var queryString = {
                            "responseType"          :   "html",
                            "action"                :   "delete-multiple-property-type",
                            "uids"                  :   tag_uids.join(",")
                        };
                        callAjaxDynamic("admin/library/property-type/property-type-action/",queryString,"delete-multiple-property-type",'html');
                    }
                }
            }
        }
    });
}

function bindRepaymentMethodScript() {
    clear_form_elements("#form-repayment-method-add-update");
    $("#add-update-buttons").fadeIn("1000");

    $('.add-new-repayment-method').bind({
        click: function(){
            clear_form_elements("#form-repayment-method-add-update");
            $("#add-update-buttons").fadeIn("1000");
        }
    });
    $('.add-update-repayment-method').bind({
        click: function(){
            switch($(this).attr("name")) {
                case "submit-form":
                    var page_id_arr                 = window.location.toString().split("tags/");
                    var page_id                     = page_id_arr[1]?page_id_arr[1]:1;
                    var repayment_method_uid        = $("input[name=repayment-method-uid]").val();
                    var name                        = $("input[name=name]").val();
                    var description                 = $("textarea[name=description]").val();
                    var is_active                   = $("input[name=is_active]:checked").val();
                    var queryString = {
                        "responseType"              :   "html",
                        "action"                    :   "repayment-method-update",
                        "page_id"                   :   page_id,
                        "repayment_method_uid"      :   repayment_method_uid,
                        "name"                      :   name,
                        "is_active"                 :   is_active,
                        "description"               :   description
                    };
                    callAjaxDynamic("admin/library/repayment-method/repayment-method-action/",queryString,"repayment-method-update",'html');
                    break;
                case "reset-form":
                    clear_form_elements("#form-repayment-method-add-update");
                    $("#add-update-buttons").fadeOut("1000");
                    break;
            }
        }
    });
    $('.repayment-method').bind({
        click: function(){
            var repayment_method_uid    =   $(this).attr("id").replace("repayment-method-","");
            if(repayment_method_uid != "") {
                clear_form_elements("#form-repayment-method-add-update");
                $("#add-update-buttons").fadeIn("1000");
                var queryString = {
                    "responseType"              :   "xml",
                    "action"                    :   "get-repayment-method-data",
                    "repayment_method_uid"      :   repayment_method_uid
                };
                callAjaxDynamic("admin/library/repayment-method/repayment-method-action/",queryString,"get-repayment-method-data",'xml');
            }
        }
    });

    $('.repayment-method-delete').bind({
        click: function(){
            var page_id_arr                 =   window.location.toString().split("tags/");
            var page_id                     =   page_id_arr[1]?page_id_arr[1]:1;
            var repayment_method_uid        =   $(this).attr("id").replace("delete-","");
            if(repayment_method_uid != "") {
                var queryString = {
                    "responseType"              :   "html",
                    "action"                    :   "delete-repayment-method",
                    "page_id"                   :   page_id,
                    "repayment_method_uid"      :   repayment_method_uid
                };
                callAjaxDynamic("admin/library/repayment-method/repayment-method-action/",queryString,"delete-repayment-method",'html');
            }
        }
    });

    $('.bulk-actions-repayment-method').bind({
        click: function(){
            var name            =   $(this).attr("name").replace("bulk-action-","");
            var tag_uids        =   new Array();
            if($("#repayment-method-actions-"+name+" :selected").val() == "delete") {
                var i           =   0;
                $('.check-repayment-method').each(function() {
                    var checked = $(this).attr("checked");
                    if(checked  == true) {
                        tag_uids[i++] = $(this).attr("id").replace("chk-","");
                    }
                });
                if(tag_uids.length > 0) {
                    if(confirm('Are you sure you wish to delete the repayment method/s selected?')) {
                        var queryString = {
                            "responseType"          :   "html",
                            "action"                :   "delete-multiple-repayment-method",
                            "uids"                  :   tag_uids.join(",")
                        };
                        callAjaxDynamic("admin/library/repayment-method/repayment-method-action/",queryString,"delete-multiple-repayment-method",'html');
                    }
                }
            }
        }
    });
}

function bindGuaranteeScript() {
    clear_form_elements("#form-guarantee-add-update");
    $("#add-update-buttons").fadeIn("1000");

    $('.add-new-guarantee').bind({
        click: function(){
            clear_form_elements("#form-guarantee-add-update");
            $("#add-update-buttons").fadeIn("1000");
        }
    });
    $('.add-update-guarantee').bind({
        click: function(){
            switch($(this).attr("name")) {
                case "submit-form":
                    var page_id_arr                 = window.location.toString().split("tags/");
                    var page_id                     = page_id_arr[1]?page_id_arr[1]:1;
                    var guarantee_uid               = $("input[name=guarantee-uid]").val();
                    var name                        = $("input[name=name]").val();
                    var description                 = $("textarea[name=description]").val();
                    var is_active                   = $("input[name=is_active]:checked").val();
                    var queryString = {
                        "responseType"              :   "html",
                        "action"                    :   "guarantee-update",
                        "page_id"                   :   page_id,
                        "guarantee_uid"             :   guarantee_uid,
                        "name"                      :   name,
                        "is_active"                 :   is_active,
                        "description"               :   description
                    };
                    callAjaxDynamic("admin/library/guarantee/guarantee-action/",queryString,"guarantee-update",'html');
                    break;
                case "reset-form":
                    clear_form_elements("#form-guarantee-add-update");
                    $("#add-update-buttons").fadeOut("1000");
                    break;
            }
        }
    });
    $('.guarantee').bind({
        click: function(){
            var guarantee_uid    =   $(this).attr("id").replace("guarantee-","");
            if(guarantee_uid != "") {
                clear_form_elements("#form-guarantee-add-update");
                $("#add-update-buttons").fadeIn("1000");
                var queryString = {
                    "responseType"              :   "xml",
                    "action"                    :   "get-guarantee-data",
                    "guarantee_uid"             :   guarantee_uid
                };
                callAjaxDynamic("admin/library/guarantee/guarantee-action/",queryString,"get-guarantee-data",'xml');
            }
        }
    });

    $('.guarantee-delete').bind({
        click: function(){
            var page_id_arr                 =   window.location.toString().split("tags/");
            var page_id                     =   page_id_arr[1]?page_id_arr[1]:1;
            var guarantee_uid               =   $(this).attr("id").replace("delete-","");
            if(guarantee_uid != "") {
                var queryString = {
                    "responseType"              :   "html",
                    "action"                    :   "delete-guarantee",
                    "page_id"                   :   page_id,
                    "guarantee_uid"             :   guarantee_uid
                };
                callAjaxDynamic("admin/library/guarantee/guarantee-action/",queryString,"delete-guarantee",'html');
            }
        }
    });

    $('.bulk-actions-guarantee').bind({
        click: function(){
            var name            =   $(this).attr("name").replace("bulk-action-","");
            var tag_uids        =   new Array();
            if($("#guarantee-actions-"+name+" :selected").val() == "delete") {
                var i           =   0;
                $('.check-guarantee').each(function() {
                    var checked = $(this).attr("checked");
                    if(checked  == true) {
                        tag_uids[i++] = $(this).attr("id").replace("chk-","");
                    }
                });
                if(tag_uids.length > 0) {
                    if(confirm('Are you sure you wish to delete the occupancy type/s selected?')) {
                        var queryString = {
                            "responseType"          :   "html",
                            "action"                :   "delete-multiple-guarantee",
                            "uids"                  :   tag_uids.join(",")
                        };
                        callAjaxDynamic("admin/library/guarantee/guarantee-action/",queryString,"delete-multiple-guarantee",'html');
                    }
                }
            }
        }
    });
}

function bindInterestRateTypeScript() {
    clear_form_elements("#form-interest-rate-type-add-update");
    $("#add-update-buttons").fadeIn("1000");

    $('.add-new-interest-rate-type').bind({
        click: function(){
            clear_form_elements("#form-interest-rate-type-add-update");
            $("#add-update-buttons").fadeIn("1000");
        }
    });
    $('.add-update-interest-rate-type').bind({
        click: function(){
            switch($(this).attr("name")) {
                case "submit-form":
                    var page_id_arr                 = window.location.toString().split("tags/");
                    var page_id                     = page_id_arr[1]?page_id_arr[1]:1;
                    var interest_rate_type_uid      = $("input[name=interest-rate-type-uid]").val();
                    var name                        = $("input[name=name]").val();
                    var description                 = $("textarea[name=description]").val();
                    var is_active                   = $("input[name=is_active]:checked").val();
                    var queryString = {
                        "responseType"              :   "html",
                        "action"                    :   "interest-rate-type-update",
                        "page_id"                   :   page_id,
                        "interest_rate_type_uid"    :   interest_rate_type_uid,
                        "name"                      :   name,
                        "is_active"                 :   is_active,
                        "description"               :   description
                    };
                    callAjaxDynamic("admin/library/interest-rate-type/interest-rate-type-action/",queryString,"interest-rate-type-update",'html');
                    break;
                case "reset-form":
                    clear_form_elements("#form-interest-rate-type-add-update");
                    $("#add-update-buttons").fadeOut("1000");
                    break;
            }
        }
    });
    $('.interest-rate-type').bind({
        click: function(){
            var interest_rate_type_uid    =   $(this).attr("id").replace("interest-rate-type-","");
            if(interest_rate_type_uid != "") {
                clear_form_elements("#form-interest-rate-type-add-update");
                $("#add-update-buttons").fadeIn("1000");
                var queryString = {
                    "responseType"              :   "xml",
                    "action"                    :   "get-interest-rate-type-data",
                    "interest_rate_type_uid"    :   interest_rate_type_uid
                };
                callAjaxDynamic("admin/library/interest-rate-type/interest-rate-type-action/",queryString,"get-interest-rate-type-data",'xml');
            }
        }
    });

    $('.interest-rate-type-delete').bind({
        click: function(){
            var page_id_arr                 =   window.location.toString().split("tags/");
            var page_id                     =   page_id_arr[1]?page_id_arr[1]:1;
            var interest_rate_type_uid      =   $(this).attr("id").replace("delete-","");
            if(interest_rate_type_uid != "") {
                var queryString = {
                    "responseType"              :   "html",
                    "action"                    :   "delete-interest-rate-type",
                    "page_id"                   :   page_id,
                    "interest_rate_type_uid"    :   interest_rate_type_uid
                };
                callAjaxDynamic("admin/library/interest-rate-type/interest-rate-type-action/",queryString,"delete-interest-rate-type",'html');
            }
        }
    });

    $('.bulk-actions-interest-rate-type').bind({
        click: function(){
            var name            =   $(this).attr("name").replace("bulk-action-","");
            var tag_uids        =   new Array();
            if($("#interest-rate-type-actions-"+name+" :selected").val() == "delete") {
                var i           =   0;
                $('.check-interest-rate-type').each(function() {
                    var checked = $(this).attr("checked");
                    if(checked  == true) {
                        tag_uids[i++] = $(this).attr("id").replace("chk-","");
                    }
                });
                if(tag_uids.length > 0) {
                    if(confirm('Are you sure you wish to delete the occupancy type/s selected?')) {
                        var queryString = {
                            "responseType"          :   "html",
                            "action"                :   "delete-multiple-interest-rate-type",
                            "uids"                  :   tag_uids.join(",")
                        };
                        callAjaxDynamic("admin/library/interest-rate-type/interest-rate-type-action/",queryString,"delete-multiple-interest-rate-type",'html');
                    }
                }
            }
        }
    });
}

function bindBusinessTypeScript() {
    clear_form_elements("#form-business-type-add-update");
    $("#add-update-buttons").fadeIn("1000");

    $('.add-new-business-type').bind({
        click: function(){
            clear_form_elements("#form-business-type-add-update");
            $("#add-update-buttons").fadeIn("1000");
        }
    });
    $('.add-update-business-type').bind({
        click: function(){
            switch($(this).attr("name")) {
                case "submit-form":
                    var page_id_arr                 = window.location.toString().split("tags/");
                    var page_id                     = page_id_arr[1]?page_id_arr[1]:1;
                    var business_type_uid           = $("input[name=business-type-uid]").val();
                    var name                        = $("input[name=name]").val();
                    var description                 = $("textarea[name=description]").val();
                    var is_active                   = $("input[name=is_active]:checked").val();
                    var queryString = {
                        "responseType"              :   "html",
                        "action"                    :   "business-type-update",
                        "page_id"                   :   page_id,
                        "business_type_uid"         :   business_type_uid,
                        "name"                      :   name,
                        "is_active"                 :   is_active,
                        "description"               :   description
                    };
                    callAjaxDynamic("admin/library/business-type/business-type-action/",queryString,"business-type-update",'html');
                    break;
                case "reset-form":
                    clear_form_elements("#form-business-type-add-update");
                    $("#add-update-buttons").fadeOut("1000");
                    break;
            }
        }
    });
    $('.business-type').bind({
        click: function(){
            var business_type_uid    =   $(this).attr("id").replace("business-type-","");
            if(business_type_uid != "") {
                clear_form_elements("#form-business-type-add-update");
                $("#add-update-buttons").fadeIn("1000");
                var queryString = {
                    "responseType"              :   "xml",
                    "action"                    :   "get-business-type-data",
                    "business_type_uid"         :   business_type_uid
                };
                callAjaxDynamic("admin/library/business-type/business-type-action/",queryString,"get-business-type-data",'xml');
            }
        }
    });

    $('.business-type-delete').bind({
        click: function(){
            var page_id_arr                 =   window.location.toString().split("tags/");
            var page_id                     =   page_id_arr[1]?page_id_arr[1]:1;
            var business_type_uid      =   $(this).attr("id").replace("delete-","");
            if(business_type_uid != "") {
                var queryString = {
                    "responseType"              :   "html",
                    "action"                    :   "delete-business-type",
                    "page_id"                   :   page_id,
                    "business_type_uid"         :   business_type_uid
                };
                callAjaxDynamic("admin/library/business-type/business-type-action/",queryString,"delete-business-type",'html');
            }
        }
    });

    $('.bulk-actions-business-type').bind({
        click: function(){
            var name            =   $(this).attr("name").replace("bulk-action-","");
            var tag_uids        =   new Array();
            if($("#business-type-actions-"+name+" :selected").val() == "delete") {
                var i           =   0;
                $('.check-business-type').each(function() {
                    var checked = $(this).attr("checked");
                    if(checked  == true) {
                        tag_uids[i++] = $(this).attr("id").replace("chk-","");
                    }
                });
                if(tag_uids.length > 0) {
                    if(confirm('Are you sure you wish to delete the occupancy type/s selected?')) {
                        var queryString = {
                            "responseType"          :   "html",
                            "action"                :   "delete-multiple-business-type",
                            "uids"                  :   tag_uids.join(",")
                        };
                        callAjaxDynamic("admin/library/business-type/business-type-action/",queryString,"delete-multiple-business-type",'html');
                    }
                }
            }
        }
    });
}

function bindAddressFormScript(){
    $('input[name=address-type]').bind({
        click: function(){
            $(".address-selection").css("display","none");
            $("#address-"+$(this).val()).css("display","block");
        }
    });
}

function bindEndowmentPolicyScript(){
    $('input[name=endowment-policy]').bind({
        click: function(){
            $(".endowment-policy-selection").css("display","none");
            $("#endowment-policy-"+$(this).val()).css("display","block");
        }
    });
}

function bindAdditionalInformationScript(){
    if(jQuery('.wymeditor').length > 0) {
        jQuery(function() {
            jQuery('.wymeditor').wymeditor();
        });
    }
}

function bindPropertyAddressScript() {
    $('#submit-property-address').bind({
        click: function(){
            var property_address_uid            =   $("input[name=property-address-uid]").val();
            var flat_number                     =   $("input[name=flat_number]").val();
            var number                          =   $("input[name=number]").val();
            var name                            =   $("input[name=name]").val();
            var street_name_1                   =   $("input[name=street_name_1]").val();
            var street_name_2                   =   $("input[name=street_name_2]").val();
            var district                        =   $("input[name=district]").val();
            var town                            =   $("input[name=town]").val();
            var city                            =   $("input[name=city]").val();
            var county                          =   $("input[name=county]").val();
            var postcode                        =   $("input[name=postcode]").val();
            var country_uid                     =   $("select[name=country_uid]").val();
            var year_built                      =   $("input[name=year_built]").val();
            var number_of_floors                =   $("input[name=number_of_floors]").val();
            var floor_number                    =   $("input[name=floor_number]").val();
            var is_ex_local_authority_or_mod    =   $("input[name=is_ex_local_authority_or_mod]").attr("checked")?1:0;
            var is_self_built                   =   $("input[name=is_self_built]").attr("checked")?1:0;
            var was_architect_supervised        =   $("input[name=was_architect_supervised]").attr("checked")?1:0;
            var architect_name                  =   $("input[name=architect_name]").val();
            var property_title_uid              =   $("select[name=property_title_uid]").val();
            var property_type_uid               =   $("select[name=property_type_uid]").val();
            var guarantee_uid                   =   $("select[name=guarantee_uid]").val();
            var guarantee_day                   =   $("input[name=guarantee_day]").val();
            var guarantee_month                 =   $("input[name=guarantee_month]").val();
            var guarantee_year                  =   $("input[name=guarantee_year]").val();
            var is_let                          =   $("input[name=is_let]").attr("checked")?1:0;
            var action                          =   (property_address_uid != "")?"update-property-address":"new-property-address";
            var queryString = {
                "responseType"                  :   "html",
                "action"                        :   action,
                "property_address_uid"          :   property_address_uid,
                "flat_number"                   :   flat_number,
                "number"                        :   number,
                "name"                          :   name,
                "street_name_1"                 :   street_name_1,
                "street_name_2"                 :   street_name_2,
                "district"                      :   district,
                "town"                          :   town,
                "city"                          :   city,
                "county"                        :   county,
                "postcode"                      :   postcode,
                "country_uid"                   :   country_uid,
                "year_built"                    :   year_built,
                "number_of_floors"              :   number_of_floors,
                "floor_number"                  :   floor_number,
                "is_ex_local_authority_or_mod"  :   is_ex_local_authority_or_mod,
                "is_self_built"                 :   is_self_built,
                "was_architect_supervised"      :   was_architect_supervised,
                "architect_name"                :   architect_name,
                "property_title_uid"            :   property_title_uid,
                "property_type_uid"             :   property_type_uid,
                "guarantee_uid"                 :   guarantee_uid,
                "guarantee_day"                 :   guarantee_day,
                "guarantee_month"               :   guarantee_month,
                "guarantee_year"                :   guarantee_year,
                "is_let"                        :   is_let
            };
            callAjaxDynamic("admin/library/property-address/property-address-update/",queryString,action,'html');
        }
    });
}

function bindCountryTypeScript(){
    clear_form_elements("#form-country-type-add-update");
    $("#add-update-buttons").fadeIn("1000");

    $('.add-new-country-type').bind({
        click: function(){
            clear_form_elements("#form-country-type-add-update");
            $("#add-update-buttons").fadeIn("1000");
        }
    });
    $('.add-update-country-type').bind({
        click: function(){
            switch($(this).attr("name")) {
                case "submit-form":
                    var page_id_arr                 = window.location.toString().split("tags/");
                    var page_id                     = page_id_arr[1]?page_id_arr[1]:1;
                    var country_type_uid            = $("input[name=country-type-uid]").val();
                    var name                        = $("input[name=name]").val();
                    var description                 = $("textarea[name=description]").val();
                    var is_active                   = $("input[name=is_active]:checked").val();
                    var queryString = {
                        "responseType"              :   "html",
                        "action"                    :   "country-type-update",
                        "page_id"                   :   page_id,
                        "country_type_uid"          :   country_type_uid,
                        "name"                      :   name,
                        "is_active"                 :   is_active,
                        "description"               :   description
                    };
                    callAjaxDynamic("admin/library/country-type/country-type-action/",queryString,"country-type-update",'html');
                    break;
                case "reset-form":
                    clear_form_elements("#form-country-type-add-update");
                    $("#add-update-buttons").fadeOut("1000");
                    break;
            }
        }
    });
    $('.country-type').bind({
        click: function(){
            var country_type_uid    =   $(this).attr("id").replace("country-type-","");
            if(country_type_uid != "") {
                clear_form_elements("#form-country-type-add-update");
                $("#add-update-buttons").fadeIn("1000");
                var queryString = {
                    "responseType"              :   "xml",
                    "action"                    :   "get-country-type-data",
                    "country_type_uid"          :   country_type_uid
                };
                callAjaxDynamic("admin/library/country-type/country-type-action/",queryString,"get-country-type-data",'xml');
            }
        }
    });

    $('.country-type-delete').bind({
        click: function(){
            var page_id_arr                 =   window.location.toString().split("tags/");
            var page_id                     =   page_id_arr[1]?page_id_arr[1]:1;
            var country_type_uid            =   $(this).attr("id").replace("delete-","");
            if(country_type_uid != "") {
                var queryString = {
                    "responseType"        :   "html",
                    "action"              :   "delete-country-type",
                    "page_id"             :   page_id,
                    "country_type_uid"    :   country_type_uid
                };
                callAjaxDynamic("admin/library/country-type/country-type-action/",queryString,"delete-country-type",'html');
            }
        }
    });

    $('.bulk-actions-country-type').bind({
        click: function(){
            var name            =   $(this).attr("name").replace("bulk-action-","");
            var tag_uids        =   new Array();
            if($("#country-type-actions-"+name+" :selected").val() == "delete") {
                var i           =   0;
                $('.check-country-type').each(function() {
                    var checked = $(this).attr("checked");
                    if(checked  == true) {
                        tag_uids[i++] = $(this).attr("id").replace("chk-","");
                    }
                });
                if(tag_uids.length > 0) {
                    if(confirm('Are you sure you wish to delete the country type/s selected?')) {
                        var queryString = {
                            "responseType"          :   "html",
                            "action"                :   "delete-multiple-country-type",
                            "uids"                  :   tag_uids.join(",")
                        };
                        callAjaxDynamic("admin/library/country-type/country-type-action/",queryString,"delete-multiple-country-type",'html');
                    }
                }
            }
        }
    });
}

function bindCountrySubTypeScript(){
    clear_form_elements("#form-country-sub-type-add-update");
    $("#add-update-buttons").fadeIn("1000");

    $('.add-new-country-sub-type').bind({
        click: function(){
            clear_form_elements("#form-country-sub-type-add-update");
            $("#add-update-buttons").fadeIn("1000");
        }
    });
    $('.add-update-country-sub-type').bind({
        click: function(){
            switch($(this).attr("name")) {
                case "submit-form":
                    var page_id_arr                 = window.location.toString().split("tags/");
                    var page_id                     = page_id_arr[1]?page_id_arr[1]:1;
                    var country_sub_type_uid        = $("input[name=country-sub-type-uid]").val();
                    var name                        = $("input[name=name]").val();
                    var description                 = $("textarea[name=description]").val();
                    var is_active                   = $("input[name=is_active]:checked").val();
                    var queryString = {
                        "responseType"              :   "html",
                        "action"                    :   "country-sub-type-update",
                        "page_id"                   :   page_id,
                        "country_sub_type_uid"      :   country_sub_type_uid,
                        "name"                      :   name,
                        "is_active"                 :   is_active,
                        "description"               :   description
                    };
                    callAjaxDynamic("admin/library/country-sub-type/country-sub-type-action/",queryString,"country-sub-type-update",'html');
                    break;
                case "reset-form":
                    clear_form_elements("#form-country-sub-type-add-update");
                    $("#add-update-buttons").fadeOut("1000");
                    break;
            }
        }
    });
    $('.country-sub-type').bind({
        click: function(){
            var country_sub_type_uid    =   $(this).attr("id").replace("country-sub-type-","");
            if(country_sub_type_uid != "") {
                clear_form_elements("#form-country-sub-type-add-update");
                $("#add-update-buttons").fadeIn("1000");
                var queryString = {
                    "responseType"              :   "xml",
                    "action"                    :   "get-country-sub-type-data",
                    "country_sub_type_uid"      :   country_sub_type_uid
                };
                callAjaxDynamic("admin/library/country-sub-type/country-sub-type-action/",queryString,"get-country-sub-type-data",'xml');
            }
        }
    });

    $('.country-sub-type-delete').bind({
        click: function(){
            var page_id_arr                 =   window.location.toString().split("tags/");
            var page_id                     =   page_id_arr[1]?page_id_arr[1]:1;
            var country_sub_type_uid        =   $(this).attr("id").replace("delete-","");
            if(country_sub_type_uid != "") {
                var queryString = {
                    "responseType"          :   "html",
                    "action"                :   "delete-country-sub-type",
                    "page_id"               :   page_id,
                    "country_sub_type_uid"  :   country_sub_type_uid
                };
                callAjaxDynamic("admin/library/country-sub-type/country-sub-type-action/",queryString,"delete-country-sub-type",'html');
            }
        }
    });

    $('.bulk-actions-country-sub-type').bind({
        click: function(){
            var name            =   $(this).attr("name").replace("bulk-action-","");
            var tag_uids        =   new Array();
            if($("#country-sub-type-actions-"+name+" :selected").val() == "delete") {
                var i           =   0;
                $('.check-country-sub-type').each(function() {
                    var checked = $(this).attr("checked");
                    if(checked  == true) {
                        tag_uids[i++] = $(this).attr("id").replace("chk-","");
                    }
                });
                if(tag_uids.length > 0) {
                    if(confirm('Are you sure you wish to delete the country sub type/s selected?')) {
                        var queryString = {
                            "responseType"          :   "html",
                            "action"                :   "delete-multiple-country-sub-type",
                            "uids"                  :   tag_uids.join(",")
                        };
                        callAjaxDynamic("admin/library/country-sub-type/country-sub-type-action/",queryString,"delete-multiple-country-sub-type",'html');
                    }
                }
            }
        }
    });
}

function bindCountryScript(){
    clear_form_elements("#form-country-add-update");
    $("#add-update-buttons").fadeIn("1000");

    $('.add-new-country').bind({
        click: function(){
            clear_form_elements("#form-country-add-update");
            $("#add-update-buttons").fadeIn("1000");
        }
    });
    $('.add-update-country').bind({
        click: function(){
            switch($(this).attr("name")) {
                case "submit-form":
                    var page_id_arr                 = window.location.toString().split("tags/");
                    var page_id                     = page_id_arr[1]?page_id_arr[1]:1;
                    var country_uid                 = $("input[name=country-uid]").val();
                    var common_name                 = $("input[name=common_name]").val();
                    var formal_name                 = $("input[name=formal_name]").val();
                    var type_uid                    = $("select[name=type_uid]").val();
                    var sub_type_uid                = $("select[name=sub_type_uid]").val();
                    var sovereignty                 = $("input[name=sovereignty]").val();
                    var capital                     = $("input[name=capital]").val();
                    var iso_4217_currency_code      = $("input[name=iso_4217_currency_code]").val();
                    var iso_4217_currency_name      = $("input[name=iso_4217_currency_name]").val();
                    var itu_t_telephone_code        = $("input[name=itu_t_telephone_code]").val();
                    var iso_3166_1_2_letter_code    = $("input[name=iso_3166_1_2_letter_code]").val();
                    var iso_3166_1_3_letter_code    = $("input[name=iso_3166_1_3_letter_code]").val();
                    var iso_3166_1_number           = $("input[name=iso_3166_1_number]").val();
                    var iana_country_code_tld       = $("input[name=iana_country_code_tld]").val();
                    var is_active                   = $("input[name=is_active]:checked").val();
                    var queryString = {
                        "responseType"              :   "html",
                        "action"                    :   "country-update",
                        "page_id"                   :   page_id,
                        "country_uid"               :   country_uid,
                        "common_name"               :   common_name,
                        "formal_name"               :   formal_name,
                        "type_uid"                  :   type_uid,
                        "sub_type_uid"              :   sub_type_uid,
                        "sovereignty"               :   sovereignty,
                        "capital"                   :   capital,
                        "iso_4217_currency_code"    :   iso_4217_currency_code,
                        "iso_4217_currency_name"    :   iso_4217_currency_name,
                        "itu_t_telephone_code"      :   itu_t_telephone_code,
                        "iso_3166_1_2_letter_code"  :   iso_3166_1_2_letter_code,
                        "iso_3166_1_3_letter_code"  :   iso_3166_1_3_letter_code,
                        "iso_3166_1_number"         :   iso_3166_1_number,
                        "iana_country_code_tld"     :   iana_country_code_tld,
                        "is_active"                 :   is_active
                    };
                    callAjaxDynamic("admin/library/country/country-action/",queryString,"country-update",'html');
                    break;
                case "reset-form":
                    clear_form_elements("#form-country-add-update");
                    $("#add-update-buttons").fadeOut("1000");
                    break;
            }
        }
    });
    $('.country').bind({
        click: function(){
            var country_uid    =   $(this).attr("id").replace("country-","");
            if(country_uid != "") {
                clear_form_elements("#form-country-add-update");
                $("#add-update-buttons").fadeIn("1000");
                var queryString = {
                    "responseType"     :   "xml",
                    "action"           :   "get-country-data",
                    "country_uid"      :   country_uid
                };
                callAjaxDynamic("admin/library/country/country-action/",queryString,"get-country-data",'xml');
            }
        }
    });

    $('.country-delete').bind({
        click: function(){
            var page_id_arr         =   window.location.toString().split("tags/");
            var page_id             =   page_id_arr[1]?page_id_arr[1]:1;
            var country_uid         =   $(this).attr("id").replace("delete-","");
            if(country_uid != "") {
                var queryString = {
                    "responseType"      :   "html",
                    "action"            :   "delete-country",
                    "page_id"           :   page_id,
                    "country_uid"       :   country_uid
                };
                callAjaxDynamic("admin/library/country/country-action/",queryString,"delete-country",'html');
            }
        }
    });

    $('.bulk-actions-country').bind({
        click: function(){
            var name            =   $(this).attr("name").replace("bulk-action-","");
            var tag_uids        =   new Array();
            if($("#country-actions-"+name+" :selected").val() == "delete") {
                var i           =   0;
                $('.check-country').each(function() {
                    var checked = $(this).attr("checked");
                    if(checked  == true) {
                        tag_uids[i++] = $(this).attr("id").replace("chk-","");
                    }
                });
                if(tag_uids.length > 0) {
                    if(confirm('Are you sure you wish to delete the country/s selected?')) {
                        var queryString = {
                            "responseType"          :   "html",
                            "action"                :   "delete-multiple-country",
                            "uids"                  :   tag_uids.join(",")
                        };
                        callAjaxDynamic("admin/library/country/country-action/",queryString,"delete-multiple-country",'html');
                    }
                }
            }
        }
    });
}

function bindInvestmentProductTypeScript() {
    clear_form_elements("#form-investment-product-type-add-update");
    $("#add-update-buttons").fadeIn("1000");

    $('.add-new-investment-product-type').bind({
        click: function(){
            clear_form_elements("#form-investment-product-type-add-update");
            $("#add-update-buttons").fadeIn("1000");
        }
    });
    $('.add-update-investment-product-type').bind({
        click: function(){
            switch($(this).attr("name")) {
                case "submit-form":
                    var page_id_arr                 = window.location.toString().split("tags/");
                    var page_id                     = page_id_arr[1]?page_id_arr[1]:1;
                    var investment_product_type_uid = $("input[name=investment-product-type-uid]").val();
                    var name                        = $("input[name=name]").val();
                    var description                 = $("textarea[name=description]").val();
                    var is_active                   = $("input[name=is_active]:checked").val();
                    var queryString = {
                        "responseType"                  :   "html",
                        "action"                        :   "investment-product-type-update",
                        "page_id"                       :   page_id,
                        "investment_product_type_uid"   :   investment_product_type_uid,
                        "name"                          :   name,
                        "is_active"                     :   is_active,
                        "description"                   :   description
                    };
                    callAjaxDynamic("admin/library/investment-product-type/investment-product-type-action/",queryString,"investment-product-type-update",'html');
                    break;
                case "reset-form":
                    clear_form_elements("#form-investment-product-type-add-update");
                    $("#add-update-buttons").fadeOut("1000");
                    break;
            }
        }
    });
    $('.investment-product-type').bind({
        click: function(){
            var investment_product_type_uid    =   $(this).attr("id").replace("investment-product-type-","");
            if(investment_product_type_uid != "") {
                clear_form_elements("#form-investment-product-type-add-update");
                $("#add-update-buttons").fadeIn("1000");
                var queryString = {
                    "responseType"                  :   "xml",
                    "action"                        :   "get-investment-product-type-data",
                    "investment_product_type_uid"   :   investment_product_type_uid
                };
                callAjaxDynamic("admin/library/investment-product-type/investment-product-type-action/",queryString,"get-investment-product-type-data",'xml');
            }
        }
    });

    $('.investment-product-type-delete').bind({
        click: function(){
            var page_id_arr                 =   window.location.toString().split("tags/");
            var page_id                     =   page_id_arr[1]?page_id_arr[1]:1;
            var investment_product_type_uid =   $(this).attr("id").replace("delete-","");
            if(investment_product_type_uid != "") {
                var queryString = {
                    "responseType"                  :   "html",
                    "action"                        :   "delete-investment-product-type",
                    "page_id"                       :   page_id,
                    "investment_product_type_uid"   :   investment_product_type_uid
                };
                callAjaxDynamic("admin/library/investment-product-type/investment-product-type-action/",queryString,"delete-investment-product-type",'html');
            }
        }
    });

    $('.bulk-actions-investment-product-type').bind({
        click: function(){
            var name            =   $(this).attr("name").replace("bulk-action-","");
            var tag_uids        =   new Array();
            if($("#investment-product-type-actions-"+name+" :selected").val() == "delete") {
                var i           =   0;
                $('.check-investment-product-type').each(function() {
                    var checked = $(this).attr("checked");
                    if(checked  == true) {
                        tag_uids[i++] = $(this).attr("id").replace("chk-","");
                    }
                });
                if(tag_uids.length > 0) {
                    if(confirm('Are you sure you wish to delete the investment product type/s selected?')) {
                        var queryString = {
                            "responseType"          :   "html",
                            "action"                :   "delete-multiple-investment-product-type",
                            "uids"                  :   tag_uids.join(",")
                        };
                        callAjaxDynamic("admin/library/investment-product-type/investment-product-type-action/",queryString,"delete-multiple-investment-product-type",'html');
                    }
                }
            }
        }
    });
}

function bindProductTypeScript() {
    clear_form_elements("#form-product-type-add-update");
    $("#add-update-buttons").fadeIn("1000");

    $('.add-new-product-type').bind({
        click: function(){
            clear_form_elements("#form-product-type-add-update");
            $("#add-update-buttons").fadeIn("1000");
        }
    });
    $('.add-update-product-type').bind({
        click: function(){
            switch($(this).attr("name")) {
                case "submit-form":
                    var page_id_arr                 = window.location.toString().split("tags/");
                    var page_id                     = page_id_arr[1]?page_id_arr[1]:1;
                    var product_type_uid            = $("input[name=product-type-uid]").val();
                    var name                        = $("input[name=name]").val();
                    var description                 = $("textarea[name=description]").val();
                    var is_active                   = $("input[name=is_active]:checked").val();
                    var queryString = {
                        "responseType"                  :   "html",
                        "action"                        :   "product-type-update",
                        "page_id"                       :   page_id,
                        "product_type_uid"              :   product_type_uid,
                        "name"                          :   name,
                        "is_active"                     :   is_active,
                        "description"                   :   description
                    };
                    callAjaxDynamic("admin/library/product-type/product-type-action/",queryString,"product-type-update",'html');
                    break;
                case "reset-form":
                    clear_form_elements("#form-product-type-add-update");
                    $("#add-update-buttons").fadeOut("1000");
                    break;
            }
        }
    });
    $('.product-type').bind({
        click: function(){
            var product_type_uid    =   $(this).attr("id").replace("product-type-","");
            if(product_type_uid != "") {
                clear_form_elements("#form-product-type-add-update");
                $("#add-update-buttons").fadeIn("1000");
                var queryString = {
                    "responseType"                  :   "xml",
                    "action"                        :   "get-product-type-data",
                    "product_type_uid"              :   product_type_uid
                };
                callAjaxDynamic("admin/library/product-type/product-type-action/",queryString,"get-product-type-data",'xml');
            }
        }
    });

    $('.product-type-delete').bind({
        click: function(){
            var page_id_arr                 =   window.location.toString().split("tags/");
            var page_id                     =   page_id_arr[1]?page_id_arr[1]:1;
            var product_type_uid            =   $(this).attr("id").replace("delete-","");
            if(product_type_uid != "") {
                var queryString = {
                    "responseType"                  :   "html",
                    "action"                        :   "delete-product-type",
                    "page_id"                       :   page_id,
                    "product_type_uid"              :   product_type_uid
                };
                callAjaxDynamic("admin/library/product-type/product-type-action/",queryString,"delete-product-type",'html');
            }
        }
    });

    $('.bulk-actions-product-type').bind({
        click: function(){
            var name            =   $(this).attr("name").replace("bulk-action-","");
            var tag_uids        =   new Array();
            if($("#product-type-actions-"+name+" :selected").val() == "delete") {
                var i           =   0;
                $('.check-product-type').each(function() {
                    var checked = $(this).attr("checked");
                    if(checked  == true) {
                        tag_uids[i++] = $(this).attr("id").replace("chk-","");
                    }
                });
                if(tag_uids.length > 0) {
                    if(confirm('Are you sure you wish to delete the product type/s selected?')) {
                        var queryString = {
                            "responseType"          :   "html",
                            "action"                :   "delete-multiple-product-type",
                            "uids"                  :   tag_uids.join(",")
                        };
                        callAjaxDynamic("admin/library/product-type/product-type-action/",queryString,"delete-multiple-product-type",'html');
                    }
                }
            }
        }
    });
}

function bindRoomTypeScript() {
    clear_form_elements("#form-room-type-add-update");
    $("#add-update-buttons").fadeIn("1000");

    $('.add-new-room-type').bind({
        click: function(){
            clear_form_elements("#form-room-type-add-update");
            $("#add-update-buttons").fadeIn("1000");
        }
    });
    $('.add-update-room-type').bind({
        click: function(){
            switch($(this).attr("name")) {
                case "submit-form":
                    var page_id_arr                 = window.location.toString().split("tags/");
                    var page_id                     = page_id_arr[1]?page_id_arr[1]:1;
                    var room_type_uid               = $("input[name=room-type-uid]").val();
                    var name                        = $("input[name=name]").val();
                    var description                 = $("textarea[name=description]").val();
                    var is_active                   = $("input[name=is_active]:checked").val();
                    var queryString = {
                        "responseType"              :   "html",
                        "action"                    :   "room-type-update",
                        "page_id"                   :   page_id,
                        "room_type_uid"             :   room_type_uid,
                        "name"                      :   name,
                        "is_active"                 :   is_active,
                        "description"               :   description
                    };
                    callAjaxDynamic("admin/library/room-type/room-type-action/",queryString,"room-type-update",'html');
                    break;
                case "reset-form":
                    clear_form_elements("#form-room-type-add-update");
                    $("#add-update-buttons").fadeOut("1000");
                    break;
            }
        }
    });
    $('.room-type').bind({
        click: function(){
            var room_type_uid    =   $(this).attr("id").replace("room-type-","");
            if(room_type_uid != "") {
                clear_form_elements("#form-room-type-add-update");
                $("#add-update-buttons").fadeIn("1000");
                var queryString = {
                    "responseType"          :   "xml",
                    "action"                :   "get-room-type-data",
                    "room_type_uid"         :   room_type_uid
                };
                callAjaxDynamic("admin/library/room-type/room-type-action/",queryString,"get-room-type-data",'xml');
            }
        }
    });

    $('.room-type-delete').bind({
        click: function(){
            var page_id_arr        =   window.location.toString().split("tags/");
            var page_id            =   page_id_arr[1]?page_id_arr[1]:1;
            var room_type_uid      =   $(this).attr("id").replace("delete-","");
            if(room_type_uid != "") {
                var queryString = {
                    "responseType"          :   "html",
                    "action"                :   "delete-room-type",
                    "page_id"               :   page_id,
                    "room_type_uid"         :   room_type_uid
                };
                callAjaxDynamic("admin/library/room-type/room-type-action/",queryString,"delete-room-type",'html');
            }
        }
    });

    $('.bulk-actions-room-type').bind({
        click: function(){
            var name            =   $(this).attr("name").replace("bulk-action-","");
            var tag_uids        =   new Array();
            if($("#room-type-actions-"+name+" :selected").val() == "delete") {
                var i           =   0;
                $('.check-room-type').each(function() {
                    var checked = $(this).attr("checked");
                    if(checked  == true) {
                        tag_uids[i++] = $(this).attr("id").replace("chk-","");
                    }
                });
                if(tag_uids.length > 0) {
                    if(confirm('Are you sure you wish to delete the room type/s selected?')) {
                        var queryString = {
                            "responseType"          :   "html",
                            "action"                :   "delete-multiple-room-type",
                            "uids"                  :   tag_uids.join(",")
                        };
                        callAjaxDynamic("admin/library/room-type/room-type-action/",queryString,"delete-multiple-room-type",'html');
                    }
                }
            }
        }
    });
}

function bindRoofMaterialScript() {
    clear_form_elements("#form-roof-material-add-update");
    $("#add-update-buttons").fadeIn("1000");

    $('.add-new-roof-material').bind({
        click: function(){
            clear_form_elements("#form-roof-material-add-update");
            $("#add-update-buttons").fadeIn("1000");
        }
    });
    $('.add-update-roof-material').bind({
        click: function(){
            switch($(this).attr("name")) {
                case "submit-form":
                    var page_id_arr                 = window.location.toString().split("tags/");
                    var page_id                     = page_id_arr[1]?page_id_arr[1]:1;
                    var roof_material_uid           = $("input[name=roof-material-uid]").val();
                    var name                        = $("input[name=name]").val();
                    var description                 = $("textarea[name=description]").val();
                    var is_active                   = $("input[name=is_active]:checked").val();
                    var queryString = {
                        "responseType"              :   "html",
                        "action"                    :   "roof-material-update",
                        "page_id"                   :   page_id,
                        "roof_material_uid"         :   roof_material_uid,
                        "name"                      :   name,
                        "is_active"                 :   is_active,
                        "description"               :   description
                    };
                    callAjaxDynamic("admin/library/roof-material/roof-material-action/",queryString,"roof-material-update",'html');
                    break;
                case "reset-form":
                    clear_form_elements("#form-roof-material-add-update");
                    $("#add-update-buttons").fadeOut("1000");
                    break;
            }
        }
    });
    $('.roof-material').bind({
        click: function(){
            var roof_material_uid    =   $(this).attr("id").replace("roof-material-","");
            if(roof_material_uid != "") {
                clear_form_elements("#form-roof-material-add-update");
                $("#add-update-buttons").fadeIn("1000");
                var queryString = {
                    "responseType"          :   "xml",
                    "action"                :   "get-roof-material-data",
                    "roof_material_uid"     :   roof_material_uid
                };
                callAjaxDynamic("admin/library/roof-material/roof-material-action/",queryString,"get-roof-material-data",'xml');
            }
        }
    });

    $('.roof-material-delete').bind({
        click: function(){
            var page_id_arr        =   window.location.toString().split("tags/");
            var page_id            =   page_id_arr[1]?page_id_arr[1]:1;
            var roof_material_uid   =   $(this).attr("id").replace("delete-","");
            if(roof_material_uid != "") {
                var queryString = {
                    "responseType"          :   "html",
                    "action"                :   "delete-roof-material",
                    "page_id"               :   page_id,
                    "roof_material_uid"     :   roof_material_uid
                };
                callAjaxDynamic("admin/library/roof-material/roof-material-action/",queryString,"delete-roof-material",'html');
            }
        }
    });

    $('.bulk-actions-roof-material').bind({
        click: function(){
            var name            =   $(this).attr("name").replace("bulk-action-","");
            var tag_uids        =   new Array();
            if($("#roof-material-actions-"+name+" :selected").val() == "delete") {
                var i           =   0;
                $('.check-roof-material').each(function() {
                    var checked = $(this).attr("checked");
                    if(checked  == true) {
                        tag_uids[i++] = $(this).attr("id").replace("chk-","");
                    }
                });
                if(tag_uids.length > 0) {
                    if(confirm('Are you sure you wish to delete the roof material/s selected?')) {
                        var queryString = {
                            "responseType"          :   "html",
                            "action"                :   "delete-multiple-roof-material",
                            "uids"                  :   tag_uids.join(",")
                        };
                        callAjaxDynamic("admin/library/roof-material/roof-material-action/",queryString,"delete-multiple-roof-material",'html');
                    }
                }
            }
        }
    });
}

function bindConstructionMaterialScript() {
    clear_form_elements("#form-construction-material-add-update");
    $("#add-update-buttons").fadeIn("1000");

    $('.add-new-construction-material').bind({
        click: function(){
            clear_form_elements("#form-construction-material-add-update");
            $("#add-update-buttons").fadeIn("1000");
        }
    });
    $('.add-update-construction-material').bind({
        click: function(){
            switch($(this).attr("name")) {
                case "submit-form":
                    var page_id_arr                 = window.location.toString().split("tags/");
                    var page_id                     = page_id_arr[1]?page_id_arr[1]:1;
                    var construction_material_uid   = $("input[name=construction-material-uid]").val();
                    var name                        = $("input[name=name]").val();
                    var description                 = $("textarea[name=description]").val();
                    var is_active                   = $("input[name=is_active]:checked").val();
                    var queryString = {
                        "responseType"              :   "html",
                        "action"                    :   "construction-material-update",
                        "page_id"                   :   page_id,
                        "construction_material_uid" :   construction_material_uid,
                        "name"                      :   name,
                        "is_active"                 :   is_active,
                        "description"               :   description
                    };
                    callAjaxDynamic("admin/library/construction-material/construction-material-action/",queryString,"construction-material-update",'html');
                    break;
                case "reset-form":
                    clear_form_elements("#form-construction-material-add-update");
                    $("#add-update-buttons").fadeOut("1000");
                    break;
            }
        }
    });
    $('.construction-material').bind({
        click: function(){
            var construction_material_uid    =   $(this).attr("id").replace("construction-material-","");
            if(construction_material_uid != "") {
                clear_form_elements("#form-construction-material-add-update");
                $("#add-update-buttons").fadeIn("1000");
                var queryString = {
                    "responseType"              :   "xml",
                    "action"                    :   "get-construction-material-data",
                    "construction_material_uid" :   construction_material_uid
                };
                callAjaxDynamic("admin/library/construction-material/construction-material-action/",queryString,"get-construction-material-data",'xml');
            }
        }
    });

    $('.construction-material-delete').bind({
        click: function(){
            var page_id_arr                 =   window.location.toString().split("tags/");
            var page_id                     =   page_id_arr[1]?page_id_arr[1]:1;
            var construction_material_uid   =   $(this).attr("id").replace("delete-","");
            if(construction_material_uid != "") {
                var queryString = {
                    "responseType"              :   "html",
                    "action"                    :   "delete-construction-material",
                    "page_id"                   :   page_id,
                    "construction_material_uid" :   construction_material_uid
                };
                callAjaxDynamic("admin/library/construction-material/construction-material-action/",queryString,"delete-construction-material",'html');
            }
        }
    });

    $('.bulk-actions-construction-material').bind({
        click: function(){
            var name            =   $(this).attr("name").replace("bulk-action-","");
            var tag_uids        =   new Array();
            if($("#construction-material-actions-"+name+" :selected").val() == "delete") {
                var i           =   0;
                $('.check-construction-material').each(function() {
                    var checked = $(this).attr("checked");
                    if(checked  == true) {
                        tag_uids[i++] = $(this).attr("id").replace("chk-","");
                    }
                });
                if(tag_uids.length > 0) {
                    if(confirm('Are you sure you wish to delete the construction material/s selected?')) {
                        var queryString = {
                            "responseType"          :   "html",
                            "action"                :   "delete-multiple-construction-material",
                            "uids"                  :   tag_uids.join(",")
                        };
                        callAjaxDynamic("admin/library/construction-material/construction-material-action/",queryString,"delete-multiple-construction-material",'html');
                    }
                }
            }
        }
    });
}

function bindRoofConstructionTypeScript() {
    clear_form_elements("#form-roof-construction-type-add-update");
    $("#add-update-buttons").fadeIn("1000");

    $('.add-new-roof-construction-type').bind({
        click: function(){
            clear_form_elements("#form-roof-construction-type-add-update");
            $("#add-update-buttons").fadeIn("1000");
        }
    });
    $('.add-update-roof-construction-type').bind({
        click: function(){
            switch($(this).attr("name")) {
                case "submit-form":
                    var page_id_arr                 = window.location.toString().split("tags/");
                    var page_id                     = page_id_arr[1]?page_id_arr[1]:1;
                    var roof_construction_type_uid  = $("input[name=roof-construction-type-uid]").val();
                    var name                        = $("input[name=name]").val();
                    var description                 = $("textarea[name=description]").val();
                    var is_active                   = $("input[name=is_active]:checked").val();
                    var queryString = {
                        "responseType"              :   "html",
                        "action"                    :   "roof-construction-type-update",
                        "page_id"                   :   page_id,
                        "roof_construction_type_uid":   roof_construction_type_uid,
                        "name"                      :   name,
                        "is_active"                 :   is_active,
                        "description"               :   description
                    };
                    callAjaxDynamic("admin/library/roof-construction-type/roof-construction-type-action/",queryString,"roof-construction-type-update",'html');
                    break;
                case "reset-form":
                    clear_form_elements("#form-roof-construction-type-add-update");
                    $("#add-update-buttons").fadeOut("1000");
                    break;
            }
        }
    });
    $('.roof-construction-type').bind({
        click: function(){
            var roof_construction_type_uid    =   $(this).attr("id").replace("roof-construction-type-","");
            if(roof_construction_type_uid != "") {
                clear_form_elements("#form-roof-construction-type-add-update");
                $("#add-update-buttons").fadeIn("1000");
                var queryString = {
                    "responseType"                  :   "xml",
                    "action"                        :   "get-roof-construction-type-data",
                    "roof_construction_type_uid"    :   roof_construction_type_uid
                };
                callAjaxDynamic("admin/library/roof-construction-type/roof-construction-type-action/",queryString,"get-roof-construction-type-data",'xml');
            }
        }
    });

    $('.roof-construction-type-delete').bind({
        click: function(){
            var page_id_arr                 =   window.location.toString().split("tags/");
            var page_id                     =   page_id_arr[1]?page_id_arr[1]:1;
            var roof_construction_type_uid  =   $(this).attr("id").replace("delete-","");
            if(roof_construction_type_uid != "") {
                var queryString = {
                    "responseType"                  :   "html",
                    "action"                        :   "delete-roof-construction-type",
                    "page_id"                       :   page_id,
                    "roof_construction_type_uid"    :   roof_construction_type_uid
                };
                callAjaxDynamic("admin/library/roof-construction-type/roof-construction-type-action/",queryString,"delete-roof-construction-type",'html');
            }
        }
    });

    $('.bulk-actions-roof-construction-type').bind({
        click: function(){
            var name            =   $(this).attr("name").replace("bulk-action-","");
            var tag_uids        =   new Array();
            if($("#roof-construction-type-actions-"+name+" :selected").val() == "delete") {
                var i           =   0;
                $('.check-roof-construction-type').each(function() {
                    var checked = $(this).attr("checked");
                    if(checked  == true) {
                        tag_uids[i++] = $(this).attr("id").replace("chk-","");
                    }
                });
                if(tag_uids.length > 0) {
                    if(confirm('Are you sure you wish to delete the roof construction type/s selected?')) {
                        var queryString = {
                            "responseType"          :   "html",
                            "action"                :   "delete-multiple-roof-construction-type",
                            "uids"                  :   tag_uids.join(",")
                        };
                        callAjaxDynamic("admin/library/roof-construction-type/roof-construction-type-action/",queryString,"delete-multiple-roof-construction-type",'html');
                    }
                }
            }
        }
    });
}

function bindDatePicker() {
    $(function() {
        $(".datepicker").datepicker({
            dateFormat: 'dd/mm/yy',
            onClose: function(dateText) {
                if(dateText == "")  {
                    $(this).parent().parent().find("span").first().css("visibility","visible");
                }
            }
        });
    });
}

function bindDatePickerValue() {
    $('.date-picker-container').each(function(){
        var InputId = $(this).attr("id").replace("date_picker-","");
        var $day    = "";
        var $month  = "";
        var $year   = "";

        $searchArr  = InputId.split("_");
        $searchArr.splice(($searchArr.length - 1),1);
        $search     = $searchArr.join("_") + "_";

        if($("#"+InputId).val() != "" && $("#"+InputId).val().indexOf("/", 0) !== false) {
            $values = $("#"+InputId).val().split("/");
            $day    = $values[0];
            $month  = $values[1];
            $year   = $values[2];
        }

        $("#"+$search+"day").val($day);
        $("#"+$search+"month").val($month);
        $("#"+$search+"year").val($year);
    });
    return true;
}

function unbindInputElements()
{
    $('label.textbox').each(function(){
        $label = $(this).find('.label').text();
        $input = $(this).find('input[type=text]');
        if($input.val() == $label) {
            $input.attr('value', '');
        }
        $textarea = $(this).find('textarea');
        if($textarea.attr('value') == $label) {
            $textarea.attr('value',"");
        }
        $(this).find('.label').hide();
    });
        
}
function bindInputElements() {
    $('label.textbox').each(function(){
        $label = $(this).find('.label').text();
        $input = $(this).find('input[type=text]');
        if($input.val() == "") {
            $input.attr('value',$label);
        }
        $textarea = $(this).find('textarea');
        if($textarea.attr('value') == "") {
            $textarea.attr('value',$label);
        }
        $(this).find('.label').hide();
    });

    $("input[type='password']").bind({
        click: function () {
            $this = $(this);
            if($this.val() == "") {
                 $this.parent().find("span").css("visibility","hidden");
            }
        },
        focus: function () {
            $this = $(this);
            if($this.val() == "") {
                $this.parent().find("span").css("visibility","hidden");
            }
        },
        blur: function () {
            $this = $(this);
            if($this.val() == '') {
                $this.parent().find("span").css("visibility","visible");
            }
        }
    });

    $("input[type='password']").parent().find("label").addClass("password");
    $(".hide-password").parent().find("span").css("visibility","hidden");
    $(".date-picker-container").each(function(){
        $(this).find("span").first().attr("style","position:absolute;display:block;margin-top:10px;");
    });

    $(".datepicker").bind({
        click: function () {
            $this = $(this);
            if($this.val() == "") {
                $this.parent().parent().find("span").first().css("visibility","hidden");
            }
        },
        focus: function () {
            $this = $(this);
            if($this.val() == "") {
                $this.parent().parent().find("span").first().css("visibility","hidden");
            }
        }
    });

    $("input[type='text'], textarea").bind({
        click: function () {
            $this = $(this);
            if($this.val() == $this.parents('label').find('.label').text()) {
                $this.val('');
            }
        },
        focus: function () {
            $this = $(this);
            if($this.val() == $this.parents('label').find('.label').text()) {
                $this.val('');
            }
        },
        blur: function () {
            $this = $(this);
            if($this.val() == '') {
                $this.val($this.parents('label').find('.label').text());
            }
        }
    });     
}

function callAjax(queryString,callBack,responseType) {
    callAjaxDynamic('admin/page-update/',queryString,callBack,responseType);
}

function callAjaxDynamic(page,queryString,callBack,responseType) {
    $.post(url+page,queryString, function(xml) {
        if(responseType == "xml") {
            if(typeof(parseResponseXml)=='function') {
                parseResponseXml(callBack,xml);
            }
        }
        else if(responseType == "html") {
            if(typeof(parseResponseHtml)=='function') {
                parseResponseHtml(callBack,xml);
            }
        }
    },responseType);
}

function MarkasPaid( PaidUid, Msg )
{
        if(confirm(Msg)){
            $.ajax({
               type: "POST",
               url: url+"admin/invoice/school/paid",
               data: "PaidUid="+PaidUid,
               success: function(msg){
                   if( msg.trim() != '' )
                       $('#button-paid-'+PaidUid).html(msg);
                 //alert( "Data Saved: " + msg );
               }
             });
        }

    //alert(Msg);

}

function GetEditTranslation( inputUid, inputName, InputActive, inputLanguage )
{
	document.frm_translation.uid.value = inputUid;
	document.frm_translation.name.value = inputName;
        document.frm_translation.name.focus();
	if( InputActive == 0 )
		$('#active0').attr("checked",true);

	$('#add_translation').attr("value","Update");
	$('#language_id').val(inputLanguage).attr("selected",true)
	$('#cancel').show();

}

function GetResetAll()
{
	document.frm_translation.uid.value = '';
	document.frm_translation.name.value = '';

		$('#active1').attr("checked",true);

	$('#add_translation').attr("value","Add");
	$('#language_id').val(0).attr("selected",true)
	$('#cancel').hide();
}