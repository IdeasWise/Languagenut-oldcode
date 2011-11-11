<?php

class component_residential_status {

    public static function generate_record_list($page_id = '') {

        $mainpanel                  = new xhtml('body.admin.library.residential.status.list');
        $mainpanel->load();
        $page_rows                  = array();
        $residential_statuss        = array();
        $residential_status         = new lib_user_residential_status();
        $residential_statuss        = $residential_status->get_residential_status($page_id);

        if(!empty($residential_statuss)) {
            foreach($residential_statuss as $uid=>$data) {
                $panel = new xhtml('body.admin.library.residential.status.list.row');
                $panel->load();
                $panel->assign($data);                
                $page_rows[]    =   $panel->get_content();
            }
        }

        $page_display_title         =   $residential_status->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
        $page_navigation            =   $residential_status->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$residential_status->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$residential_status->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

        $mainpanel->assign('page.display.title'  ,   $page_display_title);
        $mainpanel->assign('page.navigation'     ,   $page_navigation);
        $mainpanel->assign('page.rows'           ,   implode('',$page_rows));
        return $mainpanel->get_content();
    }
}
?>