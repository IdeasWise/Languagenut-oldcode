<?php

class component_contact_members_type {

    public static function generate_record_list($page_id = '') {

        $mainpanel                      = new xhtml('body.admin.library.contact.member.type.list');
        $mainpanel->load();
        $page_rows                      = array();
        $contact_member_tpyes           = array();
        $contact_member_tpye            = new lib_connect_contact_numbers_type();
        $contact_member_tpyes           = $contact_member_tpye->get_contact_members_type($page_id);

        if(!empty($contact_member_tpyes)) {
            foreach($contact_member_tpyes as $uid=>$data) {
                $panel = new xhtml('body.admin.library.contact.member.type.list.row');
                $panel->load();
                $panel->assign($data);                
                $page_rows[]    =   $panel->get_content();
            }
        }

        $page_display_title     =   $contact_member_tpye->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
        $page_navigation        =   $contact_member_tpye->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$contact_member_tpye->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$contact_member_tpye->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

        $mainpanel->assign('page.display.title'  ,   $page_display_title);
        $mainpanel->assign('page.navigation'     ,   $page_navigation);
        $mainpanel->assign('page.rows'           ,   implode('',$page_rows));
        return $mainpanel->get_content();
    }
}
?>