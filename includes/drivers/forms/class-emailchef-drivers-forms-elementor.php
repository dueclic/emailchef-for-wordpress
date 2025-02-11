<?php


class Emailchef_Drivers_Forms_Elementor extends Emailchef_Drivers_Forms_Abstract
{
    public $pluginPath = 'elementor/elementor.php';
    public $pluginName = 'Elementor Form';

    private function find_form_by_id( $elements, $form_id ) {
        foreach ( $elements as $element ) {

            if ( isset( $element['id'] ) && $element['id'] === $form_id ) {
                return $element;
            }

            if ( ! empty( $element['elements'] ) ) {
                $result = $this->find_form_by_id( $element['elements'], $form_id );
                if ( $result ) {
                    return $result;
                }
            }
        }

        return null;
    }

    private function extract_form_fields( $form ) {
        $fields = [];

        if ( isset( $form['settings']['form_fields'] ) ) {
            foreach ( $form['settings']['form_fields'] as $field ) {
                $fields[] = [
                    'id'    => $field['custom_id'],
                    'title' => $field['field_label'],
                ];
            }
        }

        return $fields;
    }

    private function find_elementor_forms( $elements ) {
        $forms = [];

        foreach ( $elements as $element ) {
            if ( isset( $element['widgetType'] ) && $element['widgetType'] === 'form' ) {
                $forms[] = [
                    'id' => $element['id'],
                    'name' => $element['settings']['form_name']
                ];
            }

            if ( ! empty( $element['elements'] ) ) {
                $forms = array_merge( $forms, $this->find_elementor_forms( $element['elements'] ) );
            }
        }

        return $forms;
    }

    public function getForms()
    {
        global $wpdb;

        // Query per ottenere sia le pagine pubblicate che i template Elementor
        $query = "
    SELECT ID, post_type 
    FROM {$wpdb->posts} 
    WHERE (post_type = 'page' OR post_type = 'elementor_library')
    AND post_status = 'publish'
    ";
        $posts = $wpdb->get_results($query);

        $forms = [];

        foreach ($posts as $post) {
            $post_id = $post->ID;

            if (\Elementor\Plugin::$instance->db->is_built_with_elementor($post_id)) {
                $document = \Elementor\Plugin::$instance->documents->get($post_id);
                $content = $document->get_elements_data();

                $page_forms = $this->find_elementor_forms($content);

                foreach ($page_forms as $form) {
                    if ($form['id']) {
                        $prefix = $post->post_type === 'page' ? 'page_' : 'template_';
                        $forms[] = [
                            'id' => $prefix . $post_id . "_form_" . $form['id'],
                            'title' => sprintf(
                                __('<strong>%s</strong> - %s ID %s', 'emailchef'),
                                $form['name']." in ".get_the_title($post_id),
                                $post->post_type === 'page' ? 'page' : 'template',
                                $post_id
                            ),
                        ];
                    }
                }
            }
        }

        return $forms;
    }

    public function getFormFields($id)
    {
        if (preg_match('/^(page|template)_(\d+)_form_(.+)$/', $id, $matches)) {
            $post_type = $matches[1]; // Estrae il tipo (page o template)
            $post_id = $matches[2];   // Estrae l'ID del post
            $form_id = $matches[3];   // Estrae l'ID del form

            if (\Elementor\Plugin::$instance->db->is_built_with_elementor($post_id)) {
                $document = \Elementor\Plugin::$instance->documents->get($post_id);
                $content = $document->get_elements_data();

                $form = $this->find_form_by_id($content, $form_id);

                if ($form) {
                    return $this->extract_form_fields($form);
                }
            }
        }
        return [];
    }

    public function intercept()
    {
        add_action('elementor_pro/forms/new_record', array(&$this, 'intercepted'), 10, 2);
    }

    public function intercepted($record, $handler)
    {

        $post_id = $record->get_form_settings( 'form_post_id' );
        $post_type_prefix = get_post_type($post_id) === 'page' ? 'page_' : 'template_';
        $form_id = $record->get_form_settings( 'id' );
        $raw_fields = $record->get( 'fields' );

        $data = array_map(function ($field) {
            return $field['value'];
        }, $raw_fields);

        $this->sendSubmission($post_type_prefix.$post_id."_form_".$form_id, $data);

        /*if (!isset($_POST['_wpcf7'])) {
            return;
        }
        $id = $_POST['_wpcf7'];
        $data = $_POST;

        $this->sendSubmission($id, $data);*/
    }
}
