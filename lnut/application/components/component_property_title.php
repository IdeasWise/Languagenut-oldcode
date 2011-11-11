<?php

class component_property_title {

    public static function generate_record_list($page_id = '') {

        $mainpanel                  = new xhtml('body.admin.library.property.title.list');
        $mainpanel->load();
        $page_rows                  = array();
        $property_titles            = array();
        $property_title             = new lib_property_title();
        $property_titles            = $property_title->get_property_title($page_id);

        if(!empty($property_titles)) {
            foreach($property_titles as $uid=>$data) {
                $panel = new xhtml('body.admin.library.property.title.list.row');
                $panel->load();
                $panel->assign($data);                
                $page_rows[]    =   $panel->get_content();
            }
        }

        $page_display_title     =   $property_title->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
        $page_navigation        =   $property_title->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$property_title->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$property_title->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

        $mainpanel->assign('page.display.title'  ,   $page_display_title);
        $mainpanel->assign('page.navigation'     ,   $page_navigation);
        $mainpanel->assign('page.rows'           ,   implode('',$page_rows));
        return $mainpanel->get_content();
    }
}
?>