<?php

class component_guarantee {

    public static function generate_record_list($page_id = '') {

        $mainpanel                      = new xhtml('body.admin.library.guarantee.list');
        $mainpanel->load();
        $page_rows                      = array();
        $guarantees                     = array();
        $guarantee                      = new lib_property_built_guarantee();
        $guarantees                     = $guarantee->get_built_guarantee($page_id);

        if(!empty($guarantees)) {
            foreach($guarantees as $uid=>$data) {
                $panel = new xhtml('body.admin.library.guarantee.list.row');
                $panel->load();
                $panel->assign($data);                
                $page_rows[]    =   $panel->get_content();
            }
        }

        $page_display_title     =   $guarantee->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
        $page_navigation        =   $guarantee->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$guarantee->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$guarantee->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

        $mainpanel->assign('page.display.title'  ,   $page_display_title);
        $mainpanel->assign('page.navigation'     ,   $page_navigation);
        $mainpanel->assign('page.rows'           ,   implode('',$page_rows));
        return $mainpanel->get_content();
    }
}
?>