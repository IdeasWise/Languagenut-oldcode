<?php

class component_roof_construction_type {

    public static function generate_record_list($page_id = '') {

        $mainpanel                          = new xhtml('body.admin.library.roof.construction.type.list');
        $mainpanel->load();
        
        $page_rows                          = array();
        $roof_construction_types            = array();
        $roof_construction_type             = new lib_property_roof_construction_type();
        $roof_construction_types            = $roof_construction_type->get_roof_construction_type($page_id);

        if(!empty($roof_construction_types)) {
            foreach($roof_construction_types as $uid=>$data) {
                $panel = new xhtml('body.admin.library.roof.construction.type.list.row');
                $panel->load();
                $panel->assign($data);                
                $page_rows[]    =   $panel->get_content();
            }
        }

        $page_display_title     =   $roof_construction_type->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
        $page_navigation        =   $roof_construction_type->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$roof_construction_type->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$roof_construction_type->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

        $mainpanel->assign('page.display.title'  ,   $page_display_title);
        $mainpanel->assign('page.navigation'     ,   $page_navigation);
        $mainpanel->assign('page.rows'           ,   implode('',$page_rows));
        return $mainpanel->get_content();
    }
}
?>