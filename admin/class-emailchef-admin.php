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

	/**
	 * Menu and submenu in admin
	 */
	public function menu() {
		add_menu_page( 'emailchef', 'Emailchef', 'manage_options', 'emailchef', array(
			$this,
			'page_forms',
		), 'dashicons-email-alt', 50 );

		add_submenu_page( 'emailchef', 'Emailchef Forms', __( 'Forms', 'emailchef' ), 'manage_options', 'emailchef', array(
			$this,
			'page_forms',
		) );
		add_submenu_page( 'emailchef', 'Emailchef Settings', __( 'Settings', 'emailchef' ), 'manage_options', 'emailchef-options', array(
			$this,
			'page_options',
		) );
	}

	/**
	 * Init admin settings page and also ajax calls
	 */
	public function pages() {
		add_action( 'admin_init', array( $this, 'page_options_settings' ) );

		// Ajax actions
		add_action( 'wp_ajax_emailchef_check_login', array( $this, 'page_options_ajax_check_login' ) );
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
			array( $this, 'page_options_settings_section_callback' ),
			'pluginPage'
		);

		add_settings_field(
			'emailchef_email',
			__( 'Email', 'emailchef' ),
			array( $this, 'page_options_email_render' ),
			'pluginPage',
			'emailchef_pluginPage_section'
		);

		add_settings_field(
			'emailchef_password',
			__( 'Password', 'emailchef' ),
			array( $this, 'page_options_password_render' ),
			'pluginPage',
			'emailchef_pluginPage_section'
		);
	}

	/**
	 * Settings page email field
	 */
	public function page_options_email_render() {
		$options = get_option( 'emailchef_settings' );
		?>
        <input type='email' name='emailchef_settings[emailchef_email]' value='<?php echo $options['emailchef_email'];
		?>'>
		<?php

	}

	/**
	 * Settings page password field
	 */
	public function page_options_password_render() {
		$options = get_option( 'emailchef_settings' );
		?>
        <input type='password' name='emailchef_settings[emailchef_password]'
               value='<?php echo $options['emailchef_password'];
		       ?>'>
		<?php

	}

	/**
	 * Settings page top description
	 */
	public function page_options_settings_section_callback() {
		echo sprintf( __( 'Please provide same login information used to login in <a target="_blank" href="%s">emailchef.com</a> website. Or <a target="_blank" href="%s">click here</a> to try it for free.', 'emailchef' ), 'http://emailchef.com/', 'https://app.emailchef.com/apps/demo/quicksignup' );
	}

	/**
	 * Called by ajax in settings pages to check for right login data.
	 */
	public function page_options_ajax_check_login() {
		global $wpdb; // this is how you get access to the database

		$user     = $_POST['email'];
		$password = $_POST['password'];

		try {
			$getAuthenticationTokenCommand = new \EMailChef\Command\Api\GetAuthenticationTokenCommand();
			$accessKey                     = $getAuthenticationTokenCommand->execute( $user, $password );

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
		if ( ! $settings || ! isset( $settings['emailchef_email'] ) || ! $settings['emailchef_email'] || ! isset( $settings['emailchef_password'] ) || ! $settings['emailchef_password'] ) {
			throw new \Exception( __( 'Please add authentication details in Settings panel', 'emailchef' ) );
		}
		$user     = $settings['emailchef_email'];
		$password = $settings['emailchef_password'];

		$getAuthenticationTokenCommand = new \EMailChef\Command\Api\GetAuthenticationTokenCommand();
		$accessKey                     = $getAuthenticationTokenCommand->execute( $user, $password );

		if ( isset( $formData['listId'] ) && $formData['listId'] !== null ) {

			$idIntegration = null;
			$found         = false;

			$getListsIntegration = new GetListsIntegrationCommand();
			$integrations        = $getListsIntegration->execute( $accessKey, $formData['listId'] );

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
				$updateListsIntegration->execute( $accessKey, $formData['listId'], $idIntegration );

			} else {
				/**
				 * Create integration logo
				 */
				$createListsIntegration = new CreateListsIntegrationCommand();
				$createListsIntegration->execute( $accessKey, $formData['listId'] );
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
					$createCustomFieldCommand->execute( $accessKey, $formData['listId'], $type, $name, $placeHolder );
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
