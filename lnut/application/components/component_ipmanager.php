<?php

class component_ipmanager {
    public static function generate() {

        $list                           = array ();
        $ipmanager                      = new settings_ip_ban();
        $list                           = $ipmanager->get_list();
        $rows                           = array();
        if(!empty($list)) {
            $cnt = 0;
            $main_panel                 = new xhtml('body.admin.ipmanager.list');
            $main_panel->load();
            foreach($list as $uid=>$data) {
                $rows[]                 = component_ipmanager::generate_row($data);
            }
            // assign rows to list so that ajax rows can be updated and deleted
            $main_panel->assign("ip.list.rows",implode('',$rows));
            // update list to main file
            return $main_panel->get_content();
        }
    }
    public static function generate_row($data = array()) {
        $panel              = new xhtml('body.admin.ipmanager.list.row');
        $panel->load();
        $data['start_ip']   =    long2ip($data['start_ip']);
        $data['end_ip']     =    long2ip($data['end_ip']);
        $panel->assign(
                $data
        );
        return $panel->get_content();
    }
}
?>
