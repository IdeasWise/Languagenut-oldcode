<?php

class component_country_type {

    public static function generate_record_list($page_id = '') {

        $mainpanel               = new xhtml('body.admin.library.country.type.list');
        $mainpanel->load();
        $page_rows               = array();
        $country_types           = array();
        $country_type            = new lib_country_type();
        $country_types           = $country_type->get_country_type($page_id);

        if(!empty($country_types)) {
            foreach($country_types as $uid=>$data) {
                $panel = new xhtml('body.admin.library.country.type.list.row');
                $panel->load();
                $panel->assign($data);                
                $page_rows[]    =   $panel->get_content();
            }
        }

        $page_display_title     =   $country_type->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
        $page_navigation        =   $country_type->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$country_type->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$country_type->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

        $mainpanel->assign('page.display.title'  ,   $page_display_title);
        $mainpanel->assign('page.navigation'     ,   $page_navigation);
        $mainpanel->assign('page.rows'           ,   implode('',$page_rows));
        return $mainpanel->get_content();
    }
}
?>