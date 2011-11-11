<?php

class component_credit_options {

    public static function generate_record_list($page_id = '') {

        $mainpanel                  = new xhtml('body.admin.library.credit.options.list');
        $mainpanel->load();
        $page_rows                  = array();
        $credit_options             = array();
        $credit_option              = new lib_user_credit_options();
        $credit_options             = $credit_option->get_credit_options($page_id);

        if(!empty($credit_options)) {
            foreach($credit_options as $uid=>$data) {
                $panel = new xhtml('body.admin.library.credit.options.list.row');
                $panel->load();
                $panel->assign($data);                
                $page_rows[]    =   $panel->get_content();
            }
        }

        $page_display_title     =   $credit_option->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
        $page_navigation        =   $credit_option->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$credit_option->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$credit_option->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

        $mainpanel->assign('page.display.title'  ,   $page_display_title);
        $mainpanel->assign('page.navigation'     ,   $page_navigation);
        $mainpanel->assign('page.rows'           ,   implode('',$page_rows));
        return $mainpanel->get_content();
    }
}
?>