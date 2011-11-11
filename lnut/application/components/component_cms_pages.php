<?php

class component_cms_pages {

    public static function generate () {
        $cms	= new cms();
        $pages	= $cms->get_pages();
        $rows	= array();

        if(0 < count($pages)) {

            $count = 0;

            foreach($pages as $uid=>$data) {

                $count++;

                $panel = new xhtml('body.admin.pages.list.row');
                $panel->load();

                // load cms data
                $panel->assign(
                        $data
                );

                // load panel data
                $sequence		= 'recordsArray_'.$count;
                $is_draggable           = ($data['uid'] > 1 ? 'draggable-class' : 'draggable-disable-class');
                $is_droppable           = ($data['uid'] > 1 ? 'droppable-class' : 'droppable-disable-class');

                $panelData = array (
                        'sequence'	=> $sequence,
                        'is_draggable'	=> $is_draggable,
                        'is_droppable'	=> $is_droppable
                );
                $panel->assign($panelData);

                $rows[] = $panel->get_content();
            }
        }

        $page_rows		= implode('',$rows);
        $page_display_title	= $cms->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
        $page_navigation	= $cms->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>');
        $page_navigation	.= $cms->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ');
        $page_navigation	.= $cms->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

        $page_data = array(
                "page.rows"			=> $page_rows,
                "page.display.title"            => $page_display_title,
                "page.navigation"		=> $page_navigation
        );
        return $page_data;

    }
    public static function generate_tags($cms_uid=0) {

        if($cms_uid > 0) {
            $cms    =   new cms($cms_uid);
            $cms->load();
        }

        $panel      =   new xhtml ('body.admin.pages.tags');
        $panel->load();

        $cms_tags           =   "";
        $tags               =   cms_tag::get_tags();
        $cms_tags_uid_array = array();
        if($cms_uid > 0) {
            $cms_tags       = $cms->get_tags();
            if(count($cms_tags) > 0) {
                foreach($cms_tags as $val) {
                    $cms_tags_uid_array[]   = stripslashes($val['tag_uid']);
                }
            }
        }
        $tags_array         =   array();
        if(count($tags) > 0) {
            foreach($tags as $key => $val) {
                $cms_tag        = new xhtml ('body.admin.pages.tags.list');
                $cms_tag->load();
                $checked = (in_array($val['uid'],$cms_tags_uid_array))?'checked="checked"':"";
                $cms_tag->assign(
                        array(
                        "tag.inserted"  =>  $checked,
                        "tag.uid"       =>  $val['uid'],
                        "tag.name"      =>  stripslashes($val['name'])
                        )
                );
                $tags_array[]   =   $cms_tag->get_content();
            }
        }
        $panel->assign("cms.tags.list",implode("",$tags_array));
        return $panel->get_content();
    }
    public static function generate_cms_tags($tag_uid = 0,$tag_name = "") {
        $cms_tag        = new xhtml ('body.admin.pages.tags.list');
        $cms_tag->load();
        $checked = 'checked="checked"';
        $cms_tag->assign(
                array(
                "tag.inserted"  =>  $checked,
                "tag.uid"       =>  $tag_uid,
                "tag.name"      =>  stripslashes($tag_name)
                )
        );
        return $cms_tag->get_content();
    }
    public static function generate_template_tags($template_uid=0,$cms_uid=0) {

        $cms_template_tags          =   array();
        $template_tags              =   array();
        $rows                       =   array();

        if(is_numeric($template_uid) && $template_uid > 0) {
            $template_tag       =   new template_tag($template_uid);
            if($template_tag->get_valid()) {
                $template_tags          =   $template_tag->get_template_tags($template_uid);
                foreach($template_tags as $uid => $data) {
                    $tag_value                  =   "";
                    $cms_template_tag_object    =   "";
                    $panel                      = new xhtml ('body.admin.pages.template.tags');
                    $panel->load();
                    $panel->assign(
                            array(
                            "cms.template.tag.uid"          =>  $data['uid'],
                            "cms.template.tag.description"  =>  stripslashes($data['tag_description'])
                            )
                    );
                    if(is_numeric($cms_uid) && $cms_uid > 0) {
                        $tag_value  =   cms_template_tags::get_template_tag_value($data['uid'],$cms_uid);
                    }

                    switch($data['tag_type']) {
                        case 1:
                            $cms_template_tag_object    =   component_cms_template_tags::generate_input($data['uid'], $tag_value);
                            break;
                        case 2:
                            $cms_template_tag_object    =   component_cms_template_tags::generate_file($data['uid'], $tag_value);
                            break;
                        default:
                            break;

                    }
                    $panel->assign("cms.template.tag.object",$cms_template_tag_object);
                    $rows[]             =   $panel->get_content();
                }
                if(count($rows) > 0) {
                    $rows[]     =   component_cms_template_tags::generate_submit();
                }
                return $rows;
            }
        }
        return false;
    }
}

?>