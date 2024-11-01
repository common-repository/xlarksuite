<?php

/**
 * @link              https://codetay.com
 * @since             1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       XLarksuite
 * Plugin URI:        https://larksuites.com
 * Description:       WordPress for Larksuite
 * Version:           1.0.5
 * Author:            CODETAY
 * Author URI:        https://codetay.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       xlarksuite
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('XLARKSUITE_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-xlarksuite-activator.php
 */
function activate_xlarksuite()
{
    require_once plugin_dir_path(__FILE__).'includes/class-xlarksuite-activator.php';
    Xlarksuite_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-xlarksuite-deactivator.php
 */
function deactivate_xlarksuite()
{
    require_once plugin_dir_path(__FILE__).'includes/class-xlarksuite-deactivator.php';
    Xlarksuite_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_xlarksuite');
register_deactivation_hook(__FILE__, 'deactivate_xlarksuite');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__).'includes/class-xlarksuite.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_xlarksuite()
{
    $plugin = new Xlarksuite();
    $plugin->run();
}
run_xlarksuite();
