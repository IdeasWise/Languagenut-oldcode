<?php

class component_menumanager {

    // this is to call all menu with its items and its form
    public static function generate_menu_list($menu_uid = 0) {
        $menu           =   new plugins_menu_manager_menus();
        $rows           =   array();
        $rows           =   $menu->get_menus($menu_uid);
        $page_rows      =   array();
        if(count($rows) > 0) {
            foreach($rows as $key => $data) {
                $main_panel         = new xhtml('body.admin.menumanager.menu.list');
                $main_panel->load();

                // get the menu items
                list($menu_item,$menu_uid)  = component_menumanager::generate_menu_item_list($data['uid']);
                $main_panel->assign("menu.items.list",$menu_item);

                // get the add form for the menu
                $menu_form          = component_menumanager::generate_menu_item_form("0",$data['uid'],"new",array(),$menu_uid);
                $main_panel->assign("menu.item.form",$menu_form);

                // get the menu snippet
                $menu_snippet       =   new template();
                $menu_snippets      =   array();
                $menu_snippets      =   $menu_snippet->get_templates(4);
                $options            =   array();
                if(count($menu_snippets) > 0) {
                    foreach($menu_snippets as $uid => $tempdata) {
                        $options[$tempdata['uid']]  =   $tempdata['name'];
                    }
                }

                $main_panel->assign(
                        array(
                        "menu.snippet" => format::to_select(array("name" => "menu-snippet-".$data['uid'],"options_only" => false,"id" => "menu-snippet-".$data['uid']), $options,$data['menu_snippet_uid'])
                        )
                );

                $main_panel->assign("sub_menu_yes",$data['may_be_submenu']?'checked="checked"':"");
                $main_panel->assign("sub_menu_no",!($data['may_be_submenu'])?'checked="checked"':"");

                $main_panel->assign(
                        $data
                );
                $page_rows[]        =  $main_panel->get_content();
            }
        }
        return implode("",$page_rows);
    }

    // this is to list all the menu items with edit form
    public static function generate_menu_item_list($menu_uid = 0) {
        if(is_numeric($menu_uid) && $menu_uid > 0) {
            $menu_item      =   new plugins_menu_manager_menus_links();
            $menu_items     =   array();
            $menu_items     =   $menu_item->get_menu_links($menu_uid);
            $page_rows      =   array();
            $menu_uid       =   array();
            if(count($menu_items) > 0) {
                foreach($menu_items as $key => $items) {

                    if($items['link_type'] == "3") {
                        $menu_uid[] = $items['link_value'];
                    }
                    $main_panel         = new xhtml('body.admin.menumanager.menu.item');
                    $main_panel->load();
                    $menu_form          = component_menumanager::generate_menu_item_form($items['uid'],$items['menu_uid'],"edit",$items);

                    $main_panel->assign("menu.item.form",$menu_form);
                    $main_panel->assign(
                            $items
                    );
                    $page_rows[]        =  $main_panel->get_content();
                }
            }
            return array(implode("",$page_rows),$menu_uid);
        }
    }

    // this is to create add edit form for menu items
    public static function generate_menu_item_form($menu_item_uid = 0,$menu_uid = 0,$type = "new",$data = array()) {
        $panel                  =   new xhtml('body.admin.menumanager.menu.item.form');
        $panel->load();

        $link_types             =   array();
        $options_link           =   array();
        $menuitem_snippet_uid   =   0;

        $menu_link_type         =   new plugins_menu_manager_link_types();
        $link_types             =   $menu_link_type->get_link_types();

        if(count($link_types) > 0) {
            foreach($link_types as $key => $linkdata) {
                $options_link[$linkdata['uid']]  =   $linkdata['name'];
            }
        }
        $link_type  = 0;
        if(count($data) > 0) {
            $link_type              =   (isset($data['link_type']) && is_numeric($data['link_type']) && $data['link_type'] > 0)?$data['link_type']:0;
            $menuitem_snippet_uid   =   $data['menuitem_snippet_uid'];
        }

        // get the menu item snippet
        $menu_item_snippet       =   new template();
        $menu_item_snippets      =   array();
        $menu_item_snippets      =   $menu_item_snippet->get_templates(5);
        $options_item            =   array();
        if(count($menu_item_snippets) > 0) {
            foreach($menu_item_snippets as $uid => $itemdata) {
                $options_item[$itemdata['uid']]  =   $itemdata['name'];
            }
        }

        // get the parent menu uid
        $options_menu   =   array();
        $menus          =   array();
        $menus          =   plugins_menu_manager_menus::get_menus_exclude_some_menu($menu_uid);
        if(count($menus) > 0) {
            foreach($menus as $uid => $menudata) {
                $options_menu[$menudata['uid']]  =   $menudata['title'];
            }
        }


        // select the cms pages
        $cms            =   new cms();
        $cms_pages      =   array();
        $cms_pages      =   $cms->get_pages('',true);
        $options_pages  =   array();

        if(count($cms_pages) > 0) {
            foreach($cms_pages as $key => $pagedata) {
                $options_pages[$pagedata['uid']]  =   $pagedata['title'];
            }
        }
        $selected_menu  =   (isset($data['link_type']) && is_numeric($data['link_type']) && $data['link_type'] == 3)?$data['link_value']:0;
        $selected_page  =   (isset($data['link_type']) && is_numeric($data['link_type']) && $data['link_type'] == 1)?$data['link_value']:0;
        $link_url       =   (isset($data['link_type']) && is_numeric($data['link_type']) && $data['link_type'] == 2)?$data['link_value']:"";
        $sub_menu_yes   =   (isset($data['submenu_uid']) && is_numeric($data['submenu_uid']) && $data['submenu_uid'] > 0)?'checked="checked"':"";
        $sub_menu_no    =   (strlen($sub_menu_yes) == 0)?'checked="checked"':"";
        $panel->assign(
                array (
                "color"             => ($type == "new")?"grey":"white",
                "item.type.class"   => ($type == "new")?"new_item":"edit_item",
                // this is for link type
                "menu.link.type"    => format::to_select(array("name" => "menu-link-type","options_only"    => false,"id" => "menu-link-$menu_item_uid-$menu_uid"),$options_link,$link_type),
                // this is for cms pages
                "menu.pages"        => format::to_select(array("name" => "menu-cms-pages","options_only"    => false,"id" => "menu-link-pages-$menu_item_uid-$menu_uid"),$options_pages,$selected_page),
                // this is for menu item snippet
                "menuitem.snippet"  => format::to_select(array("name" => "menu-item-snippet","options_only" => false,"id" => "menu-item-snippet-$menu_item_uid-$menu_uid"),$options_item,$menuitem_snippet_uid),
                // this is for the menu snippet
                "menu.submenus"     => format::to_select(array("name" => "menu-sub-menu","options_only"     => false,"id" => "menu-sub-menu-$menu_item_uid-$menu_uid"),$options_menu,$selected_menu),
                "sub_menu_no"       => $sub_menu_no,
                "sub_menu_yes"      => $sub_menu_yes,
                // this is for url
                "menu.link.url"     => '<input type="text" name="menu-link-url" value="'.$link_url.'" id="menu-link-'.$menu_item_uid.'-'.$menu_uid.'" />',
                "uid"               => $menu_item_uid,
                "menu_uid"          => $menu_uid
                )
        );
        $panel->assign($data);
        return $panel->get_content();
    }
}
?>