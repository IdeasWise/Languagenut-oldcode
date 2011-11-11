<?php

class component_property_type {

    public static function generate_record_list($page_id = '') {

        $mainpanel                = new xhtml('body.admin.library.property.type.list');
        $mainpanel->load();
        $page_rows                = array();
        $property_types           = array();
        $property_type            = new lib_property_type();
        $property_types           = $property_type->get_property_type($page_id);

        if(!empty($property_types)) {
            foreach($property_types as $uid=>$data) {
                $panel = new xhtml('body.admin.library.property.type.list.row');
                $panel->load();
                $panel->assign($data);                
                $page_rows[]    =   $panel->get_content();
            }
        }

        $page_display_title     =   $property_type->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
        $page_navigation        =   $property_type->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$property_type->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$property_type->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

        $mainpanel->assign('page.display.title'  ,   $page_display_title);
        $mainpanel->assign('page.navigation'     ,   $page_navigation);
        $mainpanel->assign('page.rows'           ,   implode('',$page_rows));
        return $mainpanel->get_content();
    }
}
?>