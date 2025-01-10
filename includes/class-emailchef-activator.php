<?php

/**
 * Fired during plugin activation.
 *
 * @link       http://nicolamoretti.com
 * @since      1.0.0
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 *
 * @author     dueclic <info@dueclic.com>
 */
class Emailchef_Activator
{
    /**
     * Short Description. (use period).
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate()
    {

        if (!wp_next_scheduled('check_emailchef_credentials')) {
            wp_schedule_event(time(), 'daily', 'check_emailchef_credentials');
        }
    }
}
