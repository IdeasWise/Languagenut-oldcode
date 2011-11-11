<?php

class component_country_sub_type {

    public static function generate_record_list($page_id = '') {

        $mainpanel                  = new xhtml('body.admin.library.country.sub.type.list');
        $mainpanel->load();
        $page_rows                  = array();
        $country_sub_types          = array();
        $country_sub_type           = new lib_country_sub_type();
        $country_sub_types          = $country_sub_type->get_country_sub_type($page_id);

        if(!empty($country_sub_types)) {
            foreach($country_sub_types as $uid=>$data) {
                $panel = new xhtml('body.admin.library.country.sub.type.list.row');
                $panel->load();
                $panel->assign($data);                
                $page_rows[]    =   $panel->get_content();
            }
        }

        $page_display_title     =   $country_sub_type->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
        $page_navigation        =   $country_sub_type->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$country_sub_type->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$country_sub_type->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

        $mainpanel->assign('page.display.title'  ,   $page_display_title);
        $mainpanel->assign('page.navigation'     ,   $page_navigation);
        $mainpanel->assign('page.rows'           ,   implode('',$page_rows));
        return $mainpanel->get_content();
    }
}
?>