<?php

class component_factfind_menu {

    public static function generate_menu() {
        $plugins_factfind_types =   new plugins_factfind_types();
        $rows                   =   array();
        $rows                   =   $plugins_factfind_types->get_factfind_types();
        $html                   =   array('<h2 id="tab-settings"><a href="#"><span>FACTFINDS</span></a></h2><div id="content-settings" class="menu-content">');
        if(!empty ($rows)) {
            $count              =   count($rows);
            $cnt                =   0;
            foreach($rows as $uid => $data) {
                $cnt++;
                $string         =   "";
                $path           =   $data['slug']."/";
                $class          =   array("sub-empty");
                if($cnt >= $count) {
                    $class[]    =   "last";
                }

                $string         .=  '<div class="'.implode(" ",$class).'"> <a href="'.config::url("admin/factfinds/".$path).'"><span>'.$data['name'].'</span></a></div>';
                $html[]         =   $string;
            }
        }
        $html[]                 =   "</div>";
        return implode("", $html);
    }
}
?>
