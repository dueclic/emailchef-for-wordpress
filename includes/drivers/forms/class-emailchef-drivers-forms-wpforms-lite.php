<?php


class Emailchef_Drivers_Forms_WPFormsLite extends Emailchef_Drivers_Forms_Abstract
{
    public $pluginPath = 'wpforms-lite/wpforms.php';
    public $pluginName = 'WPForms Lite';

    public function getForms()
    {
        $ret   = array();
        $forms = wpforms()->form->get();
        if ($forms) {
            foreach ($forms as $key => $row) {
                $ret[] = array(
                    'id' => $row->ID,
                    'title' => sprintf(
                        __('<strong>%s</strong> - page ID %s', 'emailchef'),
                        $row->post_title, $row->ID
                    ),
                    //'title' => $row->post_title,
                );
            }
        }

        return $ret;
    }

    public function getFormFields($id)
    {
        $form = wpforms()->form->get($id);
        if ( ! $form) {
            return array();
        }
        // Pull and format the form data out of the form object.
        $form_data = ! empty($form->post_content) ? wpforms_decode(
            $form->post_content
        ) : '';

        $form_fields = $form_data['fields'];

        if (empty($form_fields)) {
            return array();
        }

        // Here we define what the types of form fields we do NOT want to include,
        // instead they should be ignored entirely.
        $form_fields_disallow = apply_filters(
            'wpforms_frontend_entries_table_disallow',
            ['divider', 'html', 'pagebreak', 'captcha']
        );

        // Loop through all form fields and remove any field types not allowed.
        foreach ($form_fields as $field_id => $form_field) {
            if (in_array($form_field['type'], $form_fields_disallow, true)) {
                unset($form_fields[$field_id]);
            }
        }

        $ret = array();

        foreach ($form_fields as $field) {
            if ($field['type'] == 'name') {
                $name_values = explode("-", $field['format']);
                foreach ($name_values as $name_value) {
                    $ret[] = array(
                        'id'    => $field['id'].'-'.$name_value,
                        'title' => $field['label'].'-'.$name_value,
                    );
                }
            } else {
                if (!is_null($field['choices']) && count($field['choices']) > 1) {
                    $ret[] = array(
                        'id'    => $field['id'],
                        'title' => $field['label'],
                        'error' => __('Please insert only one choice in checkbox field', 'emailchef'),
                    );
                } else {
                    $ret[] = array(
                        'id'    => $field['id'],
                        'title' => $field['label'],
                    );
                }
            }
        }

        return $ret;
    }

    public function intercept()
    {
        add_action(
            'wpforms_process_complete', array(&$this, 'intercepted'), 10, 3
        );
    }

    public function intercepted()
    {
        $id     = $_POST["wpforms"]['id'];
        $fields = $_POST["wpforms"]["complete"];

        $data = array();
        foreach ($fields as $key => $value) {
            switch ($value['type']) {
            case 'name':
                $data[$value['id'].'-first']  = $value['first'];
                $data[$value['id'].'-middle'] = $value['middle'];
                $data[$value['id'].'-last']   = $value['last'];
                $data[$value['id'].'-simple'] = $value['value'];
                break;

            case 'checkbox':
                if ($value['value'] != "") {
                    $value['value'] = 'yes';
                }
                $data[$value['id']] = $value['value'];
                break;

            default:
                $data[$value['id']] = $value['value'];
                break;
            }
        }

        $this->sendSubmission($id, $data);
    }
}
