<?php

class component_construction_material {

    public static function generate_record_list($page_id = '') {

        $mainpanel                      = new xhtml('body.admin.library.construction.material.list');
        $mainpanel->load();
        
        $page_rows                      = array();
        $construction_materials         = array();
        $construction_material          = new lib_property_construction_material();
        $construction_materials         = $construction_material->get_construction_material($page_id);

        if(!empty($construction_materials)) {
            foreach($construction_materials as $uid=>$data) {
                $panel = new xhtml('body.admin.library.construction.material.list.row');
                $panel->load();
                $panel->assign($data);                
                $page_rows[]    =   $panel->get_content();
            }
        }

        $page_display_title     =   $construction_material->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
        $page_navigation        =   $construction_material->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$construction_material->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$construction_material->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

        $mainpanel->assign('page.display.title'  ,   $page_display_title);
        $mainpanel->assign('page.navigation'     ,   $page_navigation);
        $mainpanel->assign('page.rows'           ,   implode('',$page_rows));
        return $mainpanel->get_content();
    }
}
?>