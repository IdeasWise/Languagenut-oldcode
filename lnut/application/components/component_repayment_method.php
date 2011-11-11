<?php

class component_repayment_method {

    public static function generate_record_list($page_id = '') {

        $mainpanel                      = new xhtml('body.admin.library.repayment.method.list');
        $mainpanel->load();
        $page_rows                      = array();
        $repayment_methods              = array();
        $repayment_method               = new lib_repayment_method();
        $repayment_methods              = $repayment_method->get_repayment_method($page_id);

        if(!empty($repayment_methods)) {
            foreach($repayment_methods as $uid=>$data) {
                $panel = new xhtml('body.admin.library.repayment.method.list.row');
                $panel->load();
                $panel->assign($data);                
                $page_rows[]    =   $panel->get_content();
            }
        }

        $page_display_title     =   $repayment_method->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
        $page_navigation        =   $repayment_method->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$repayment_method->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$repayment_method->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

        $mainpanel->assign('page.display.title'  ,   $page_display_title);
        $mainpanel->assign('page.navigation'     ,   $page_navigation);
        $mainpanel->assign('page.rows'           ,   implode('',$page_rows));
        return $mainpanel->get_content();
    }
}
?>