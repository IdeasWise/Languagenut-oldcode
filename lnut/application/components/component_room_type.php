<?php

class component_room_type {

    public static function generate_record_list($page_id = '') {

        $mainpanel              = new xhtml('body.admin.library.room.type.list');
        $mainpanel->load();
        
        $page_rows              = array();
        $room_types             = array();
        $room_type              = new lib_property_room_type();
        $room_types             = $room_type->get_room_type($page_id);

        if(!empty($room_types)) {
            foreach($room_types as $uid=>$data) {
                $panel = new xhtml('body.admin.library.room.type.list.row');
                $panel->load();
                $panel->assign($data);                
                $page_rows[]    =   $panel->get_content();
            }
        }

        $page_display_title     =   $room_type->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
        $page_navigation        =   $room_type->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$room_type->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$room_type->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

        $mainpanel->assign('page.display.title'  ,   $page_display_title);
        $mainpanel->assign('page.navigation'     ,   $page_navigation);
        $mainpanel->assign('page.rows'           ,   implode('',$page_rows));
        return $mainpanel->get_content();
    }
}
?>