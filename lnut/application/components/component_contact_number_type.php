<?php

class component_contact_number_type {

    public static function generate_record_list($page_id = '') {

        $mainpanel                      = new xhtml('body.admin.library.contact.number.type.list');
        $mainpanel->load();
        $page_rows                      = array();
        $contact_number_tpyes           = array();
        $contact_number_tpye            = new lib_connect_contact_numbers_type();
        $contact_number_tpyes           = $contact_number_tpye->get_contact_number_type($page_id);

        if(!empty($contact_number_tpyes)) {
            foreach($contact_number_tpyes as $uid=>$data) {
                $panel = new xhtml('body.admin.library.contact.number.type.list.row');
                $panel->load();
                $panel->assign($data);                
                $page_rows[]    =   $panel->get_content();
            }
        }

        $page_display_title     =   $contact_number_tpye->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
        $page_navigation        =   $contact_number_tpye->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$contact_number_tpye->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$contact_number_tpye->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

        $mainpanel->assign('page.display.title'  ,   $page_display_title);
        $mainpanel->assign('page.navigation'     ,   $page_navigation);
        $mainpanel->assign('page.rows'           ,   implode('',$page_rows));
        return $mainpanel->get_content();
    }
}
?>