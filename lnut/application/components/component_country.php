<?php

class component_country {

    public static function generate_record_list($page_id = '') {

        $mainpanel              = new xhtml('body.admin.library.country.list');
        $mainpanel->load();
        $page_rows              = array();
        $countries              = array();
        $country                = new lib_country();
        $countries              = $country->get_country($page_id);

        if(!empty($countries)) {
            foreach($countries as $uid=>$data) {
                $panel = new xhtml('body.admin.library.country.list.row');
                $panel->load();
                $panel->assign($data);
                $page_rows[]    =   $panel->get_content();
            }
        }

        $page_display_title     =   $country->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
        $page_navigation        =   $country->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$country->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$country->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

        $mainpanel->assign('page.display.title'  ,   $page_display_title);
        $mainpanel->assign('page.navigation'     ,   $page_navigation);
        $mainpanel->assign('page.rows'           ,   implode('',$page_rows));
        return $mainpanel->get_content();
    }
}
?>