<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'drivers/forms/class-emailchef-drivers-forms-abstract.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'drivers/forms/class-emailchef-drivers-forms-contactform7.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'drivers/forms/class-emailchef-drivers-forms-fastsecurecontactform.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'drivers/forms/class-emailchef-drivers-forms-jetpack.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'drivers/forms/class-emailchef-drivers-forms-wpforms-lite.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'drivers/forms/class-emailchef-drivers-forms-wpforms.php';

class Emailchef_Drivers_Forms
{
    public static function getAll()
    {
        return array(
            new Emailchef_Drivers_Forms_ContactForm7(),
            new Emailchef_Drivers_Forms_FastSecureContactForm(),
            new Emailchef_Drivers_Forms_Jetpack(),
            new Emailchef_Drivers_Forms_WPFormsLite(),
            new Emailchef_Drivers_Forms_WPForms(),
        );
    }
}
