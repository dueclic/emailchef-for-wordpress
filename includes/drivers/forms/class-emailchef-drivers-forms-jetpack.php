<?php


class Emailchef_Drivers_Forms_Jetpack extends Emailchef_Drivers_Forms_Abstract
{
    public $pluginPath = 'jetpack/jetpack.php';
    public $pluginName = 'Jetpack';

    public function getForms()
    {
        global $wpdb;
        // Unfortunately we have to parse posts to find them (:()
        $sql = "
  		SELECT id, post_title
  		FROM $wpdb->posts
  		WHERE
  			(`post_type` = 'page' OR `post_type` = 'post')
        AND
        post_content LIKE '%%%[contact-form]%%%' AND post_content LIKE '%%%[/contact-form]%%%'
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
        preg_match('/\[contact-form\](.*?)\[\/contact-form\]/', $content, $matches);
        if (!$matches) {
            return array();
        }
        $innerContent = $matches[1];
        $form = new Grunion_Contact_Form(shortcode_parse_atts($innerContent), $innerContent);
        $fields = $form->fields;
        $ret = array();
        foreach ($fields as $field) {
            $ret[] = array(
                'id' => $field->attributes['id'],
                'title' => $field->attributes['label'],
            );
        }

        return $ret;
    }

    public function intercept()
    {
        add_action('grunion_pre_message_sent', array(&$this, 'intercepted'), 10, 3);
    }

    public function intercepted()
    {
        $id = get_the_ID();
        $data = array();
        foreach ($_POST as $key => $value) {
            $data[str_replace('g' . $id . '-', 'g-', $key)] = $value;
        }
        $this->sendSubmission($id, $data);
    }
}
