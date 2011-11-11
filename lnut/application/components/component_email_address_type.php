<?php

class component_email_address_type {

    public static function generate_record_list($page_id = '') {

        $mainpanel                      = new xhtml('body.admin.library.email.address.type.list');
        $mainpanel->load();
        $page_rows                      = array();
        $email_address_tpyes           = array();
        $email_address_tpye            = new lib_connect_email_address_type();
        $email_address_tpyes           = $email_address_tpye->get_email_address_type($page_id);

        if(!empty($email_address_tpyes)) {
            foreach($email_address_tpyes as $uid=>$data) {
                $panel = new xhtml('body.admin.library.email.address.type.list.row');
                $panel->load();
                $panel->assign($data);                
                $page_rows[]    =   $panel->get_content();
            }
        }

        $page_display_title     =   $email_address_tpye->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
        $page_navigation        =   $email_address_tpye->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$email_address_tpye->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$email_address_tpye->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

        $mainpanel->assign('page.display.title'  ,   $page_display_title);
        $mainpanel->assign('page.navigation'     ,   $page_navigation);
        $mainpanel->assign('page.rows'           ,   implode('',$page_rows));
        return $mainpanel->get_content();
    }
}
?>