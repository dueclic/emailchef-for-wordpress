<?php

/**
 * @link              https://emailchef.com/
 * @since             1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       Emailchef
 * Plugin URI:        https://emailchef.com/
 * Description:       Emailchef: the easiest way to create great newsletters. Sync form submissions automatically from Elementor, Contact Form 7, FSCF, and Jetpack.
 * Version:           3.5.0
 * Author:            dueclic
 * Author URI:        https://www.dueclic.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       emailchef
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

define('EMAILCHEF_PLUGIN_FILE_PATH', __FILE__);
define('EMAILCHEF_PLUGIN_PATH', dirname(EMAILCHEF_PLUGIN_FILE_PATH));

// Load Emailchef library
include plugin_dir_path(__FILE__) . 'lib/emailchef/vendor/autoload.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-emailchef-activator.php.
 */
function activate_emailchef()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-emailchef-activator.php';
	Emailchef_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-emailchef-deactivator.php.
 */
function deactivate_emailchef()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-emailchef-deactivator.php';
	Emailchef_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_emailchef');
register_deactivation_hook(__FILE__, 'deactivate_emailchef');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-emailchef.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_emailchef()
{
	$plugin = new Emailchef();
	$plugin->run();
}

run_emailchef();
