<?php


class Emailchef_Drivers_Forms_ContactForm7 extends Emailchef_Drivers_Forms_Abstract
{
    public $pluginPath = 'contact-form-7/wp-contact-form-7.php';
    public $pluginName = 'Contact Form 7';

    public function getForms()
    {
        global $wpdb;
        $sql = "
    SELECT id, post_title
    FROM $wpdb->posts
    WHERE
      (`post_type` = 'wpcf7_contact_form')
  ";
        $ret = array();
        foreach ($wpdb->get_results($sql) as $key => $row) {
            $ret[] = array(
                'id' => $row->id,
                'title' => sprintf(__('<strong>%s</strong> - page ID %s', 'emailchef'), $row->post_title, $row->id),
            );
        }

        return $ret;
    }

    public function getFormFields($id)
    {
        $content = get_post_field('post_content', $id);

        $shortcodes = WPCF7_ShortcodeManager::get_instance();
        $fields = $shortcodes->scan_shortcode($content);
        $ret = array();

        foreach ($fields as $field) {
            if ($field['type'] == 'submit') {
                continue;
            }
            $ret[] = array(
                'id' => $field['name'],
                'title' => $field['name'],
            );
        }

        return $ret;
    }

    public function intercept()
    {
        add_action('wpcf7_before_send_mail', array(&$this, 'intercepted'), 10, 3);
    }

    public function intercepted()
    {
        if (!isset($_POST['_wpcf7'])) {
            return;
        }
        $id = $_POST['_wpcf7'];
        $data = $_POST;

        $this->sendSubmission($id, $data);
    }
}
