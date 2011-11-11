<?php

class component_roof_material {

    public static function generate_record_list($page_id = '') {

        $mainpanel                  = new xhtml('body.admin.library.roof.material.list');
        $mainpanel->load();
        
        $page_rows                  = array();
        $roof_materials             = array();
        $roof_material              = new lib_property_roof_material();
        $roof_materials             = $roof_material->get_roof_material($page_id);

        if(!empty($roof_materials)) {
            foreach($roof_materials as $uid=>$data) {
                $panel = new xhtml('body.admin.library.roof.material.list.row');
                $panel->load();
                $panel->assign($data);                
                $page_rows[]    =   $panel->get_content();
            }
        }

        $page_display_title     =   $roof_material->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
        $page_navigation        =   $roof_material->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$roof_material->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$roof_material->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

        $mainpanel->assign('page.display.title'  ,   $page_display_title);
        $mainpanel->assign('page.navigation'     ,   $page_navigation);
        $mainpanel->assign('page.rows'           ,   implode('',$page_rows));
        return $mainpanel->get_content();
    }
}
?>