<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

/**
 * Description of component_response
 *
 * @author Ritesh
 */
class component_response {
    //put your code here
    public static function xmlCMSResponse($cms_uid = 1,$status = 0) {
        $skeleton = new xhtml ('xml.cms_update_cache');
        $skeleton->load();
        $skeleton->assign(
                array(
                'cms_uid'	=> $cms_uid,
                'status'	=> $status // was "1"
                )
        );
        output::as_xml($skeleton,true);
    }

    public static function xmlTagResponse() {
        $tag_uid    =   (isset($_POST['tag_uid'])? format::to_integer($_POST['tag_uid']) : '');
        $error      =   1;        
        $tag_name   =   '';
        $tag_slug   =   '';
        $tag_descr  =   '';

        if(is_numeric($tag_uid) && $tag_uid > 0) {
            $tag                =   new tag($tag_uid);
            if($tag->get_valid()) {
                $tag->load();
                $error      =   0;
                $tag_uid    =   $tag->get_uid();
                $tag_name   =   $tag->get_name();
                $tag_slug   =   $tag->get_slug();
                $tag_descr  =   $tag->get_description();
            }
        }        

        $skeleton = new xhtml ('xml.tag_get_data');
        $skeleton->load();
        $skeleton->assign(
                array(
                'error' => $error,
                'uid'	=> $tag_uid,
                'slug'	=> $tag_slug,
                'name'	=> $tag_name,
                'descr'	=> $tag_descr
                )
        );
        output::as_xml($skeleton,true);
    }

    public static function htmlCMSResponse($page_data) {
        $skeleton = new xhtml('body.admin.pages.list');
        $skeleton->load();
        $skeleton->assign($page_data);
        output::as_html($skeleton,true);
    }

    public static function htmlTemplateResponse($template,$template_type_uid,$template_type_name,$options,$message_type="",$message="") {
        $skeleton = new xhtml('body.admin.templates.list');
        $skeleton->load();
        $skeleton->assign(
                array(
                "template.uid"		=> $template_type_uid,
                'template.name'		=> $template_type_name,
                "template.select"	=> format::to_select(array("name" => "copy_template_".$template_type_uid,"options_only" => false),$options),
                "template.files"	=> @implode("",$template)
                )
        );
        //output::as_html($skeleton,true);
        // common success failure response
        component_response::htmlSuccessFailureResponse($skeleton->get_content(),$message_type,$message);
    }
    public static function htmlSuccessFailureResponse($tag_data,$message_type="",$message="") {
        $skeleton = new xhtml('body.admin.success.error');
        $skeleton->load();
        $skeleton->assign(
                array("data"            => $tag_data)
        );
        if($message_type == "error") {
            $skeleton->assign(
                    array("error"       => implode("<br />",$message))
            );
        }
        else if($message_type == "success") {
            $skeleton->assign(
                    array("success"     => implode("<br />",$message))
            );
        }
        output::as_html($skeleton,true);
    }
}
?>