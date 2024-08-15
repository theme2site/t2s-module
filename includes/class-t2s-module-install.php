<?php

/**
 * $t2s_module_db_version - holds current database version
 * and used on plugin update to sync database tables
 */
global $t2s_module_db_version;
$t2s_module_db_version = '1.1'; // version changed from 1.0 to 1.1

/**
 * register_activation_hook implementation
 *
 * will be called when user activates plugin first time
 * must create needed database tables
 */
function t2s_module_install()
{
    global $wpdb;
    global $t2s_module_db_version;

    $table_name = $wpdb->prefix . 't2s_modules'; // do not forget about tables prefix

    // sql to create your table
    // NOTICE that:
    // 1. each field MUST be in separate line
    // 2. There must be two spaces between PRIMARY KEY and its name
    //    Like this: PRIMARY KEY[space][space](id)
    // otherwise dbDelta will not work
    $sql = "CREATE TABLE " . $table_name . " (
        id int(11) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        email VARCHAR(100) NOT NULL,
        age int(11) NULL,
        gender tinyint(4) NOT NULL DEFAULT 0,
        role tinyint(4) NOT NULL DEFAULT 0,
        hobbies VARCHAR(255) NULL,
        introduction text NULL,
        image_url VARCHAR(255) NULL,
        file_url VARCHAR(255) NULL,
        status tinyint(4) NOT NULL DEFAULT 0,
        created_at timestamp NULL DEFAULT NULL,
        updated_at timestamp NULL DEFAULT NULL,
        PRIMARY KEY  (id)
    );";

    // we do not execute sql directly
    // we are calling dbDelta which cant migrate database
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // save current database version for later use (on upgrade)
    add_option('t2s_module_db_version', $t2s_module_db_version);

    /**
     * [OPTIONAL] Example of updating to 1.1 version
     *
     * If you develop new version of plugin
     * just increment $t2s_module_db_version variable
     * and add following block of code
     *
     * must be repeated for each new version
     * in version 1.1 we change email field
     * to contain 200 chars rather 100 in version 1.0
     * and again we are not executing sql
     * we are using dbDelta to migrate table changes
     */
    $installed_ver = get_option('t2s_module_db_version');
    if ($installed_ver != $t2s_module_db_version) {
        $sql = "CREATE TABLE " . $table_name . " (
            id int(11) NOT NULL AUTO_INCREMENT,
            name tinytext NOT NULL,
            email VARCHAR(100) NOT NULL,
            age int(11) NULL,
            gender tinyint(4) NOT NULL DEFAULT 0,
            role tinyint(4) NOT NULL DEFAULT 0,
            hobbies VARCHAR(255) NULL,
            introduction text NULL,
            image_url VARCHAR(255) NULL,
            file_url VARCHAR(255) NULL,
            status tinyint(4) NOT NULL DEFAULT 0,
            created_at timestamp NULL DEFAULT NULL,
            updated_at timestamp NULL DEFAULT NULL,
            PRIMARY KEY  (id)
        );";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // notice that we are updating option, rather than adding it
        update_option('t2s_module_db_version', $t2s_module_db_version);
    }

    // $wpdb->insert($table_name, array(
    //     'name' => 'Alex',
    //     'sex' => '0',
    //     'email' => 'alex@example.com',
    //     'age' => 25,
    //     'created_at'  => date('Y-m-d H:i:s'),
    //     'updated_at'  => date('Y-m-d H:i:s'),
    // ));

    // 创建模块列表页面
    $module_list_page = get_page_by_path('module-list');
    if (!$module_list_page) {
        $module_list_page = array(
            'post_title' => '模块列表',
            'post_name' => 'module-list',
            'post_content' => '',
            'post_status' => 'publish',
            'post_type' => 'page',
            'comment_status' => 'closed',
            'ping_status' => 'closed',
        );
        wp_insert_post($module_list_page);
    }

    // 创建模块详情页面
    $module_detail_page = get_page_by_path('module-detail');
    if (!$module_detail_page) {
        $module_detail_page = array(
            'post_title' => '模块详情',
            'post_name' => 'module-detail',
            'post_content' => '',
            'post_status' => 'publish',
            'post_type' => 'page',
            'comment_status' => 'closed',
            'ping_status' => 'closed',
        );
        wp_insert_post($module_detail_page);
    }

}
register_activation_hook(T2SM_PLUGIN_FILE, 't2s_module_install');

/**
 * Trick to update plugin database, see docs
 */
function t2s_module_update_db_check()
{
    global $t2s_module_db_version;
    if (get_site_option('t2s_module_db_version') != $t2s_module_db_version) {
        t2s_module_install();
    }
}
add_action('plugins_loaded', 't2s_module_update_db_check');
