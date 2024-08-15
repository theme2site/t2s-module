<?php

/**
 * T2S Module
 *
 * Plugin Name: T2S Module
 * Description: Provide custom module example, custom database table, including CRUD operations, export and import data.
 * Author: Theme2Site
 * Author URI: http://www.theme2site.com/
 * Version: 1.5.0
 * Text Domain: t2s-module
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if ( ! defined( 'T2SM_PLUGIN_FILE' ) ) {
	define( 'T2SM_PLUGIN_FILE', __FILE__ );
}

require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

// Include the main T2S_Module class.
if (!class_exists('T2S_Module')) {
    class T2S_Module
    {
        /**
         * T2S_Module Constructor.
         */
        public function __construct()
        {
            $this->define_constants();
            $this->includes();
        }

        /**
         * Define T2S_Module Constants.
         */
        private function define_constants()
        {
            // Define plugin constants
            $this->define('T2S_MODULE_VERSION', '1.5.0');
            $this->define('T2S_MODULE_DIR', plugin_dir_path(__FILE__));
            $this->define('T2S_MODULE_URL', plugin_dir_url(__FILE__));
            $this->define('T2S_MODULE_BASENAME', plugin_basename(__FILE__));
            $this->define('T2S_MODULE_GLOBAL_DATA', require_once(T2S_MODULE_DIR . 'data/global.php'));
        }

        /**
         * Define constant if not already set.
         *
         * @param  string $name
         * @param  string|bool $value
         */
        private function define($name, $value)
        {
            if (!defined($name)) {
                define($name, $value);
            }
        }

        /**
         * Include required core files used in admin and on the frontend.
         */
        public function includes()
        {
            include_once(T2S_MODULE_DIR . 'includes/class-t2s-module-install.php');
            include_once(T2S_MODULE_DIR . 'includes/class-t2s-module.php');

            if (is_admin()) {
                require_once(T2S_MODULE_DIR . 'admin/class-admin.php');
            }
        }
    }

    $GLOBALS['t2s_module'] = new T2S_Module();
}
