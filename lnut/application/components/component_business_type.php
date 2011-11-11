<?php

class component_business_type {

    public static function generate_record_list($page_id = '') {

        $mainpanel                 = new xhtml('body.admin.library.business.type.list');
        $mainpanel->load();
        $page_rows                 = array();
        $business_types            = array();
        $business_type             = new lib_business_type();
        $business_types            = $business_type->get_business_type($page_id);

        if(!empty($business_types)) {
            foreach($business_types as $uid=>$data) {
                $panel = new xhtml('body.admin.library.business.type.list.row');
                $panel->load();
                $panel->assign($data);                
                $page_rows[]    =   $panel->get_content();
            }
        }

        $page_display_title     =   $business_type->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
        $page_navigation        =   $business_type->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$business_type->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$business_type->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

        $mainpanel->assign('page.display.title'  ,   $page_display_title);
        $mainpanel->assign('page.navigation'     ,   $page_navigation);
        $mainpanel->assign('page.rows'           ,   implode('',$page_rows));
        return $mainpanel->get_content();
    }
}
?>