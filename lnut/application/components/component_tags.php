<?php

class component_tags {
    public static function generate() {

        $body				= new xhtml('body.admin.tags');
        $body->load();

        $data                           = component_tags::generate_tags_list();
        $body->assign("tags.list", $data);
        return $body;
    }

    public static function generate_tags_list($page_id = '') {
        
        $mainpanel                      = new xhtml('body.admin.tags.list');
        $mainpanel->load();
        $page_rows                      = array();
        $tags                           = array();
        $tag                            = new tag();
        $tags                           = $tag->get_tags($page_id);

        if(!empty($tags)) {
            foreach($tags as $uid=>$data) {
                $page_rows[]            =   component_tags::generate_tag_rows($data);
            }
        }

        $page_display_title     =   $tag->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
        $page_navigation        =   $tag->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$tag->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$tag->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

        $mainpanel->assign('page.display.title'  ,   $page_display_title);
        $mainpanel->assign('page.navigation'     ,   $page_navigation);
        $mainpanel->assign('tags.rows'           ,   implode('',$page_rows));
        return $mainpanel->get_content();
    }

    public static function generate_tag_rows($data) {
        $panel = new xhtml('body.admin.tags.row');
        $panel->load();
        $panel->assign($data);
        return $panel->get_content();
    }
}
?>