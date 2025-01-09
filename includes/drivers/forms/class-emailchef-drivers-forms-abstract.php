<?php

use EMailChef\Command\Api\GetListsCommand;
use EMailChef\Command\Api\GetPredefinedFieldsCommand;
use EMailChef\Command\Api\GetListFieldsCommand;
use EMailChef\Command\Api\CreateContactCommand;
use EMailChef\Command\Api\GetListsIntegrationCommand;
use EMailChef\Command\Api\UpdateListsIntegrationCommand;
use EMailChef\Command\Api\CreateListsIntegrationCommand;

abstract class Emailchef_Drivers_Forms_Abstract
{
    public $pluginPath = '';
    public $pluginName = '';

    public function isActive()
    {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';

        return is_plugin_active($this->pluginPath);
    }

    public function getName()
    {
        return $this->pluginName;
    }

    public function getForms()
    {
        return array();
    }

    public function getForm($id)
    {
        Emailchef_Forms_Option::load();
        $form = Emailchef_Forms_Option::getForm($this, $id);

        $settings = get_option('emailchef_settings');
        if (!$settings || !isset($settings['consumer_key']) || !$settings['consumer_key'] || !isset($settings['consumer_secret']) || !$settings['consumer_secret']) {
            throw new \Exception(__('Please add authentication details in Settings panel', 'emailchef'));
        }
        $consumer_key = $settings['consumer_key'];
        $consumer_secret = $settings['consumer_secret'];

        $lists = (new GetListsCommand())->execute($consumer_key, $consumer_secret, false);

        // single list fields
        $listFields = array();
        if (isset($form['listId'])) {
            $listFound = false;
            // Check if list exists and fields
            foreach ($lists as $listTemp) {
                if ($listTemp->id == $form['listId']) {
                    $listFound = true;
                    break;
                }
            }
            if (!$listFound) {
                $form['listId'] = null;
            } else {
                // Load fields map
                $predefinedFields = (new GetPredefinedFieldsCommand())->execute($consumer_key, $consumer_secret);
                $customFields = (new GetListFieldsCommand())->execute($form['listId'], $consumer_key, $consumer_secret);
                $listFieldsMerge = array_merge($predefinedFields, $customFields);
                foreach ($listFieldsMerge as $listField) {
                    $listFields[] = array(
                        'id' => $listField->place_holder,
                        'title' => $listField->name,
                    );
                }
            }
        }

        // form fields
        $formFields = $this->getFormFields($id);

        // saved fields
        $savedFields = $form['field'];

        return array(
            'lists' => $lists,
            'listId' => isset($form['listId']) ? $form['listId'] : null,
            'listFields' => $listFields,
            'formFields' => $formFields,
            'savedFields' => $savedFields,
        );
    }

    public function getFormFields($id)
    {
        return array();
    }

    public function getSlug()
    {
        return sanitize_title($this->pluginName);
    }

    public function intercept()
    {
    }

    public function sendSubmission($id, $data)
    {
        // Create contact
        Emailchef_Forms_Option::load();
        $form = Emailchef_Forms_Option::getForm($this, $id);

        if (!isset($form['listId']) || !$form['listId']) {
            return;
        }

        $listId = $form['listId'];

        $map = $form['field'];

        $mappingEmail = false;
        $toSend = array();
        foreach ($map as $key => $value) {
            if (!isset($data[$key]) || empty($value)) {
                continue;
            }

            if ($value === "privacy_accepted"){
                $value = "privacy";
            }

            if ($value === "terms_accepted"){
                $value = "terms";
            }

            if ($value === "newsletter_accepted"){
                $value = "newsletter";
            }

            $toSend[$value] = $data[$key];
            if ($value == 'email') {
                $mappingEmail = true;
            }
        }


        if (!$mappingEmail) {
            return; // nothing mapping to email
        }

        try {
            $settings = get_option('emailchef_settings');
            if (!$settings || !isset($settings['consumer_key']) || !$settings['consumer_key'] || !isset($settings['consumer_secret']) || !$settings['consumer_secret']) {
                throw new \Exception(__('Please add authentication details in Settings panel', 'emailchef'));
            }
            $consumer_key = $settings['consumer_key'];
            $consumer_secret = $settings['consumer_secret'];

            $importContactsCommand = new CreateContactCommand();
	        $importContactsCommand->execute($listId, $toSend, $consumer_key, $consumer_secret);
        } catch (\Exception $e) {
            // Ignoring to prevent errors!
        }
    }
}
