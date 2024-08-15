<?php

// Register module detail route
// http://atwp.test/module-detail/1/
function module_detail_route() {
    add_rewrite_rule('^module-detail/([^/]+)/?', 'index.php?pagename=module-detail&module_id=$matches[1]', 'top');
}
add_action('init', 'module_detail_route');

function module_detail_query_vars($vars) {
    $vars[] = 'module_id';
    return $vars;
}
add_filter('query_vars', 'module_detail_query_vars');

function module_detail_template($template)
{
    if (get_query_var('pagename') == 'module-detail') {
        return T2S_MODULE_DIR . 'templates/module-detail.php';
    }
    return $template;
}
add_filter('template_include', 'module_detail_template');

// Register module list template
// http://atwp.test/module-list/
function module_list_template($template)
{
    if (get_query_var('pagename') == 'module-list') {
        return T2S_MODULE_DIR . 'templates/module-list.php';
    }
    return $template;
}
add_filter('template_include', 'module_list_template');

// flush rewrite rules
// function t2s_module_flush_rewrite_rules() {
//     global $wp_rewrite;
//     $wp_rewrite->flush_rules();
// }
// add_action( 'init', 't2s_module_flush_rewrite_rules');
