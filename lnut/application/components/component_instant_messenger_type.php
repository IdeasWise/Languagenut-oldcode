<?php

class component_instant_messenger_type {

    public static function generate_record_list($page_id = '') {

        $mainpanel                      = new xhtml('body.admin.library.instant.messenger.type.list');
        $mainpanel->load();
        $page_rows                      = array();
        $instant_messenger_types        = array();
        $instant_messenger_type         = new lib_connect_instant_messenger_type();
        $instant_messenger_types        = $instant_messenger_type->get_instant_messenger_type($page_id);

        if(!empty($instant_messenger_types)) {
            foreach($instant_messenger_types as $uid=>$data) {
                $panel = new xhtml('body.admin.library.instant.messenger.type.list.row');
                $panel->load();
                $panel->assign($data);                
                $page_rows[]    =   $panel->get_content();
            }
        }

        $page_display_title     =   $instant_messenger_type->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
        $page_navigation        =   $instant_messenger_type->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$instant_messenger_type->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$instant_messenger_type->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

        $mainpanel->assign('page.display.title'  ,   $page_display_title);
        $mainpanel->assign('page.navigation'     ,   $page_navigation);
        $mainpanel->assign('page.rows'           ,   implode('',$page_rows));
        return $mainpanel->get_content();
    }
}
?>