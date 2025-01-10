<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://nicolamoretti.com
 * @since      1.0.0
 */

use EMailChef\Command\Api\CreateCustomFieldCommand;
use EMailChef\Command\Api\CreateListsIntegrationCommand;
use EMailChef\Command\Api\UpdateListsIntegrationCommand;
use EMailChef\Command\Api\GetListsIntegrationCommand;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @author     dueclic <info@dueclic.com>
 */
class Emailchef_Admin {
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     *
     * @var string The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     *
     * @var string The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles( $hook ) {
        if ( $hook != 'toplevel_page_emailchef' && $hook != 'emailchef_page_emailchef-options' ) {
            return;
        }
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/emailchef-admin.css', array(), $this->version, 'all' );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts( $hook ) {
        if ( $hook != 'toplevel_page_emailchef' && $hook != 'emailchef_page_emailchef-options' ) {
            return;
        }
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/emailchef-admin.js', array( 'jquery' ), $this->version, false );
    }

    public function emailchef_invalid_credentials_notice(){
        $error = get_option('emailchef_invalid_credentials_notice');
        if ($error) {
            ?>
            <div class="notice notice-error is-dismissible">
                <p>
                    <?php
                    echo sprintf(__('Emailchef API keys are invalid, please reconnect. <a
               target="_blank" href="%s">Go to settings</a> ', 'emailchef'), admin_url("admin.php?page=emailchef"));
                    ?>
            </div>
            <?php
        }
    }

    /**
     * Menu and submenu in admin
     */
    public function menu() {

        $settings = get_option( 'emailchef_settings' );

        add_menu_page( 'emailchef', 'Emailchef', 'manage_options', 'emailchef', array(
            $this,
            (!$settings || !isset($settings['consumer_key']) || !$settings['consumer_key'] || !isset($settings['consumer_secret']) || !$settings['consumer_secret']) ? 'page_options': 'page_forms',
        ), plugin_dir_url( __FILE__ ) . 'img/icon.png', 50 );

    }

    /**
     * Init admin settings page and also ajax calls
     */
    public function pages() {
        add_action( 'admin_init', array( $this, 'page_options_settings' ) );
        // Ajax actions
        add_action( 'wp_ajax_emailchef_check_login', array( $this, 'page_options_ajax_check_login' ) );
        add_action( 'wp_ajax_emailchef_disconnect', array( $this, 'page_options_ajax_disconnect' ) );
        add_action( 'wp_ajax_emailchef_forms_form', array( $this, 'page_forms_ajax_form' ) );
    }

    /**
     * Forms page
     */
    public function page_forms() {
        include_once plugin_dir_path( __FILE__ ) . '../includes/class-emailchef-forms-option.php';
        include_once plugin_dir_path( __FILE__ ) . '../includes/drivers/class-emailchef-drivers-forms.php';
        include_once plugin_dir_path( __FILE__ ) . 'partials/emailchef-page-forms-display.php';
    }

    /**
     * Settings page
     */
    public function page_options() {
        update_option('emailchef_invalid_credentials_notice', false);
        include plugin_dir_path( __FILE__ ) . 'partials/emailchef-page-settings-display.php';
    }

    /**
     * Settings page fields
     */
    public function page_options_settings() {
        register_setting( 'pluginPage', 'emailchef_settings' );

        add_settings_section(
            'emailchef_pluginPage_section',
            __( 'Account details', 'emailchef' ),
            [],
            'pluginPage'
        );

        add_settings_field(
            'emailchef_consumer_key',
            __( 'Consumer Key', 'emailchef' ),
            [],
            'pluginPage',
            'emailchef_pluginPage_section'
        );

        add_settings_field(
            'emailchef_consumer_secret',
            __( 'Consumer Secret', 'emailchef' ),
            [],
            'pluginPage',
            'emailchef_pluginPage_section'
        );
    }

    /**
     * Called by ajax in settings pages to check for right login data.
     */
    public function page_options_ajax_check_login() {

        $consumer_key     = sanitize_text_field($_POST['consumer_key']);
        $consumer_secret = sanitize_text_field($_POST['consumer_secret']);

        try {
            $getAuthenticationTokenCommand = new \EMailChef\Command\Api\GetAuthenticationTokenCommand();
            $getAuthenticationTokenCommand->execute( $consumer_key, $consumer_secret );

            $result = true;
        } catch ( Exception $e ) {
            $result = false;
            emailchef_write_log( 'Unable to login' );
            emailchef_write_log( $e );
        }

        $data = array( 'result' => $result );

        wp_send_json( $data );
        //wp_die(); // this is required to terminate immediately and return a proper response
    }

    public function page_options_ajax_disconnect() {

        delete_option('emailchef_settings');

        $data = array( 'result' => true );

        wp_send_json( $data );
    }

    /**
     * Called by ajax in forms page to get or save form.
     */
    public function page_forms_ajax_form() {
        global $wpdb; // this is how you get access to the database
        include_once plugin_dir_path( __FILE__ ) . '../includes/class-emailchef-forms-option.php';
        include_once plugin_dir_path( __FILE__ ) . '../includes/drivers/class-emailchef-drivers-forms.php';

        $id         = $_POST['id'];
        $driverName = $_POST['driver'];
        $data       = isset( $_POST['data'] ) ? $_POST['data'] : null;
        $create     = isset( $_POST['create'] ) ? $_POST['create'] : null;

        $formsDrivers = Emailchef_Drivers_Forms::getAll();

        $driver = null;
        foreach ( $formsDrivers as $driverT ) {
            if ( $driverT->getSlug() == $driverName ) {
                $driver = $driverT;
                break;
            }
        }
        if ( ! $driver ) {
            throw new \Exception( 'Driver not found' );
        }

        $dataContent = array();
        if ( $data ) {
            // Save form settings
            parse_str( $data, $dataContent );

            Emailchef_Forms_Option::load();
            Emailchef_Forms_Option::setForm( $driver, $id, $dataContent );
            Emailchef_Forms_Option::save();
        }

        $formData = $driver->getForm( $id );

        $settings = get_option( 'emailchef_settings' );
        if (!$settings || !isset($settings['consumer_key']) || !$settings['consumer_key'] || !isset($settings['consumer_secret']) || !$settings['consumer_secret']) {
            throw new \Exception( __( 'Please add authentication details in Settings panel', 'emailchef' ) );
        }

        $consumer_key = $settings['consumer_key'];
        $consumer_secret = $settings['consumer_secret'];

        if ( isset( $formData['listId'] ) && $formData['listId'] !== null ) {

            $idIntegration = null;
            $found         = false;

            $getListsIntegration = new GetListsIntegrationCommand();
            $integrations        = $getListsIntegration->execute( $consumer_key, $consumer_secret, $formData['listId'] );

            foreach ( $integrations as $integration ) {
                if ( $integration->id == 5 && $integration->website == get_site_url() ) {

                    $idIntegration = $integration->row_id;
                    $found         = true;
                }
            }

            if ( $found ) {
                /**
                 * Update integration logo
                 */

                $updateListsIntegration = new UpdateListsIntegrationCommand();
                $updateListsIntegration->execute( $consumer_key, $consumer_secret, $formData['listId'], $idIntegration );

            } else {
                /**
                 * Create integration logo
                 */
                $createListsIntegration = new CreateListsIntegrationCommand();
                $createListsIntegration->execute( $consumer_key, $consumer_secret, $formData['listId'] );
            }
        }

        if ( $create ) {
            // Create and map fields automatically
            $createCustomFieldCommand = new CreateCustomFieldCommand();

            foreach ( $formData['formFields'] as $field ) {
                if ( ! isset( $formData['savedFields'][$field['id']] ) || empty( $formData['savedFields'][$field['id']] ) ) {
                    // Create field in contact list
                    $type        = CreateCustomFieldCommand::DATA_TYPE_TEXT;
                    $name        = $field['title'];

                    $placeHolder =  str_replace( '-', '_', sanitize_title_with_dashes( $field['title'] ) );
                    $createCustomFieldCommand->execute( $consumer_key, $consumer_secret, $formData['listId'], $type, $name, $placeHolder );
                    $dataContent['field'][$field['id']] = $placeHolder;
                }
            }

            Emailchef_Forms_Option::setForm( $driver, $id, $dataContent );
            Emailchef_Forms_Option::save();

            $formData = $driver->getForm( $id );
        }

        include plugin_dir_path( __FILE__ ) . 'partials/emailchef-page-forms-form.php';
        wp_die(); // this is required to terminate immediately and return a proper response
    }
}
