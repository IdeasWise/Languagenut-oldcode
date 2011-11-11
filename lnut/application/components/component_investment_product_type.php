<?php

class component_investment_product_type {

    public static function generate_record_list($page_id = '') {

        $mainpanel                      = new xhtml('body.admin.library.investment.product.type.list');
        $mainpanel->load();
        $page_rows                      = array();
        $investment_product_types       = array();
        $investment_product_type        = new plugins_factfind_mortgage_investment_products_types();
        $investment_product_types       = $investment_product_type->get_investment_products_types($page_id);

        if(!empty($investment_product_types)) {
            foreach($investment_product_types as $uid=>$data) {
                $panel = new xhtml('body.admin.library.investment.product.type.list.row');
                $panel->load();
                $panel->assign($data);                
                $page_rows[]    =   $panel->get_content();
            }
        }

        $page_display_title     =   $investment_product_type->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
        $page_navigation        =   $investment_product_type->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$investment_product_type->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$investment_product_type->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

        $mainpanel->assign('page.display.title'  ,   $page_display_title);
        $mainpanel->assign('page.navigation'     ,   $page_navigation);
        $mainpanel->assign('page.rows'           ,   implode('',$page_rows));
        return $mainpanel->get_content();
    }
}
?>