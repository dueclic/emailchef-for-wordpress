<?php

/**
 * The file that defines the core plugin class.
 *
 * A class definition that includes attributes and functions used across the admin area.
 *
 * @link       http://nicolamoretti.com
 * @since      1.0.0
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 *
 * @author     dueclic <info@dueclic.com>
 */
class emailchef
{
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     *
     * @var Emailchef_Loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     *
     * @var string The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     *
     * @var string The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        $this->plugin_name = 'emailchef';
        $this->version = '1.0.0';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Emailchef_Loader. Orchestrates the hooks of the plugin.
     * - Emailchef_i18n. Defines internationalization functionality.
     * - Emailchef_Admin. Defines all hooks for the admin area.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-emailchef-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-emailchef-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-emailchef-admin.php';

        $this->loader = new Emailchef_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Emailchef_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {
        $plugin_i18n = new Emailchef_i18n();

        $this->loader->add_action('init', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {
        $plugin_admin = new Emailchef_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'menu');
        $this->loader->add_action('admin_notices', $plugin_admin, 'emailchef_invalid_credentials_notice');
        $plugin_admin->pages();
    }

    public function check_emailchef_credentials(){
        $settings = get_option('emailchef_settings');

        if ($settings && isset($settings['consumer_key']) && $settings['consumer_key'] && isset($settings['consumer_secret']) && $settings['consumer_secret']) {
            try {
                $getAccountCurrentCommand = new \EMailChef\Command\Api\GetAccountCurrentCommand();
                $getAccountCurrentCommand->execute($settings['consumer_key'], $settings['consumer_secret']);

            } catch (Exception $e) {
                if ($e->getCode() === 'auth_failed') {
                    delete_option('emailchef_settings');
                    update_option("emailchef_invalid_credentials_notice", true);
                }
            }
        }
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks()
    {
        // Intercept forms submissions
        include_once plugin_dir_path(__FILE__) . '../includes/class-emailchef-forms-option.php';
        include_once plugin_dir_path(__FILE__) . '../includes/drivers/class-emailchef-drivers-forms.php';
        $formsDrivers = Emailchef_Drivers_Forms::getAll();
        foreach ($formsDrivers as $driver) {
            if (!$driver->isActive()) {
                continue;
            }
            $driver->intercept();
        }
        $this->loader->add_action('check_emailchef_credentials', $this, 'check_emailchef_credentials' );
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     *
     * @return string The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     *
     * @return Emailchef_Loader Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     *
     * @return string The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }
}
