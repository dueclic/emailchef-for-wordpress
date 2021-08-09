<?php


class Emailchef_Drivers_Forms_FastSecureContactForm extends Emailchef_Drivers_Forms_Abstract
{
    public $pluginPath = 'si-contact-form/si-contact-form.php';
    public $pluginName = 'Fast Secure Contact Form';

    public function getForms()
    {
        global $wpdb;
        $fastSecureContactFormOption = get_option('fs_contact_global');
        $ret = array();
        foreach ($fastSecureContactFormOption['form_list'] as $key => $value) {
            $ret[] = array(
                'id' => $key,
                'title' => sprintf(__('<strong>%s</strong> - form %s', 'emailchef'), $value, $key),
            );
        }

        return $ret;
    }

    public function getFormFields($id)
    {
        $formOption = get_option('fs_contact_form' . $id);
        $ret = array();
        foreach ($formOption['fields'] as $field) {
            $ret[] = array(
                'id' => $field['slug'],
                'title' => $field['label'],
            );
        }

        return $ret;
    }

    public function intercept()
    {
        add_action('fsctf_mail_sent', array(&$this, 'intercepted'), 10, 3);
    }

    public function intercepted()
    {
        if (!isset($_POST['form_id'])) {
            return;
        }
        $id = $_POST['form_id'];
        $data = $_POST;
        $this->sendSubmission($id, $data);
    }
}
