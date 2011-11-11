<?php

class component_template_tags {

    public static function generate ($template_uid = 0) {

        $rows           =   array();
        if(is_numeric($template_uid) && $template_uid > 0) {
            $template_tags  =   array();
            $template_tag   =   new template_tag();
            $template_tags  =   $template_tag->get_template_tags($template_uid);
            if(0 < count($template_tags)) {
                foreach($template_tags as $uid=>$data) {
                    $panel = new xhtml('body.admin.templates.edit.tag_list');
                    $panel->load();
                    $panel->assign($data);
                    $rows[] = $panel->get_content();
                }
            }
        }

        $page_rows		= implode('',$rows);
        return $page_rows;
    }
    public static function generate_template_tag ($template_tag_uid = 0) {

        $template_tag   =   new template_tag($template_tag_uid);
        if($template_tag->get_valid()) {
            $template_tag->load();
            $panel = new xhtml('body.admin.templates.edit.tag_list');
            $panel->load();
            $panelData = array (
                    "tag_uid"           =>  $template_tag->get_uid(),
                    "tag_name"          =>  $template_tag->get_tag_name(),
                    "tag_description"   =>  $template_tag->get_tag_description(),
                    "tag_type"          =>  $template_tag->get_tag_type()
            );

            $panel->assign($panelData);
            return $panel->get_content();
        }
        return false;
    }
}

?>