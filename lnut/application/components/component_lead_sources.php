<?php

class component_lead_sources {

    public static function generate_record_list($page_id = '') {

        $mainpanel                  = new xhtml('body.admin.library.lead.sources.list');
        $mainpanel->load();
        $page_rows                  = array();
        $lead_sources               = array();
        $credit_option              = new lib_user_lead_sources();
        $lead_sources               = $credit_option->get_lead_sources($page_id);

        if(!empty($lead_sources)) {
            foreach($lead_sources as $uid=>$data) {
                $panel = new xhtml('body.admin.library.lead.sources.list.row');
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