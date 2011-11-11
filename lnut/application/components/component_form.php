<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

/**
 * Description of component_template
 *
 * @author Ritesh
 */
class component_form {
    //put your code here
    public static function generate () {
        $form_type                  =   new plugins_form_builder_forms_type();
        $form_types                 =   array ();
        $form_types                 =   $form_type->get_form_types();
        $page_rows                  =   array();
        $forms_html                 =   array();

        if(!empty($form_types)) {
            // list of all template types
            foreach($form_types as $uid=>$data) {
                $form_list_panel    =   new xhtml('body.admin.forms.list');
                $form_list_panel->load();
                $form_list_panel->assign($data);
                // call component method to generate template files
                list($forms_html) = self::generate_formItem($data['uid']);

                $template           = new template();
                $templates          = array();
                $templates          = $template->get_templates("6");
                $options            = array();

                if(!empty ($templates)) {
                    foreach($templates as $tmpUid => $tempData) {
                        $options[$tempData['uid']] = $tempData['name'];
                    }
                }


                $form_list_panel->assign(
                        array(
                        "form.template" => format::to_select(array("name" => "form-template","id" => "form-template-".$data['uid'],"options_only" => false), $options),
                        "forms.list" => @implode("",$forms_html)
                        )
                );
                $page_rows[] = $form_list_panel->get_content();
            }
        }
        return $page_rows;
    }

    public static function generate_formItem ($form_type_uid = '') {
        $form_type	= new plugins_form_builder_forms_type($form_type_uid);
        if($form_type->get_valid()) {
            $form_type->load();
        }
        $form                   = new plugins_form_builder_forms_core();
        $forms                  = array();
        $forms_html             = array();
        $forms                  = $form->get_forms_core($form_type_uid);

        // list all template files
        foreach($forms as $uid2=>$formData) {
            $form_panel         = new xhtml('body.admin.forms.item');
            $form_panel->load();
            $form_panel->assign(
                    $formData
            );
            $forms_html[] = $form_panel->get_content();
        }
        return array($forms_html);
    }
}
?>
