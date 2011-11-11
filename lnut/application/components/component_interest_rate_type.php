<?php

class component_interest_rate_type {

    public static function generate_record_list($page_id = '') {

        $mainpanel                      = new xhtml('body.admin.library.interest.rate.type.list');
        $mainpanel->load();
        $page_rows                      = array();
        $interest_rate_types            = array();
        $interest_rate_type             = new lib_interest_rate_type();
        $interest_rate_types            = $interest_rate_type->get_interest_rate_type($page_id);

        if(!empty($interest_rate_types)) {
            foreach($interest_rate_types as $uid=>$data) {
                $panel = new xhtml('body.admin.library.interest.rate.type.list.row');
                $panel->load();
                $panel->assign($data);                
                $page_rows[]    =   $panel->get_content();
            }
        }

        $page_display_title     =   $interest_rate_type->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
        $page_navigation        =   $interest_rate_type->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$interest_rate_type->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$interest_rate_type->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

        $mainpanel->assign('page.display.title'  ,   $page_display_title);
        $mainpanel->assign('page.navigation'     ,   $page_navigation);
        $mainpanel->assign('page.rows'           ,   implode('',$page_rows));
        return $mainpanel->get_content();
    }
}
?>