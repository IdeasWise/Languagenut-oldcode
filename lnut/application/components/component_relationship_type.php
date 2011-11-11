<?php

class component_relationship_type {

    public static function generate_record_list($page_id = '') {

        $mainpanel                      = new xhtml('body.admin.library.relationship.type.list');
        $mainpanel->load();
        $page_rows                      = array();
        $relationship_tpyes             = array();
        $relationship_tpye              = new lib_user_relationship_type();
        $relationship_tpyes             = $relationship_tpye->get_relationship_type($page_id);

        if(!empty($relationship_tpyes)) {
            foreach($relationship_tpyes as $uid=>$data) {
                $panel = new xhtml('body.admin.library.relationship.type.list.row');
                $panel->load();
                $panel->assign($data);                
                $page_rows[]    =   $panel->get_content();
            }
        }

        $page_display_title     =   $relationship_tpye->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
        $page_navigation        =   $relationship_tpye->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$relationship_tpye->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$relationship_tpye->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

        $mainpanel->assign('page.display.title'  ,   $page_display_title);
        $mainpanel->assign('page.navigation'     ,   $page_navigation);
        $mainpanel->assign('page.rows'           ,   implode('',$page_rows));
        return $mainpanel->get_content();
    }
}
?>