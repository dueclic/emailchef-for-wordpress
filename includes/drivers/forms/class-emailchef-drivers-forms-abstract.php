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
        if (!$settings || !isset($settings['emailchef_email']) || !$settings['emailchef_email'] || !isset($settings['emailchef_password']) || !$settings['emailchef_password']) {
            throw new \Exception(__('Please add authentication details in Settings panel', 'emailchef'));
        }
        $user = $settings['emailchef_email'];
        $password = $settings['emailchef_password'];
        $accessKey = null;

        // get lists
        try {
            $getAuthenticationTokenCommand = new \EMailChef\Command\Api\GetAuthenticationTokenCommand();
            $accessKey = $getAuthenticationTokenCommand->execute($user, $password);
        } catch (\Exception $e) {
            throw new \Exception(__('Unable to authenticate', 'emailchef'));
        }
        $lists = (new GetListsCommand())->execute($accessKey, false);

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
                $predefinedFields = (new GetPredefinedFieldsCommand())->execute($accessKey);
                $customFields = (new GetListFieldsCommand())->execute($form['listId'], $accessKey);
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
            if (!$settings || !isset($settings['emailchef_email']) || !$settings['emailchef_email'] || !isset($settings['emailchef_password']) || !$settings['emailchef_password']) {
                throw new \Exception(__('Please add authentication details in Settings panel', 'emailchef'));
            }
            $user = $settings['emailchef_email'];
            $password = $settings['emailchef_password'];

            $getAuthenticationTokenCommand = new \EMailChef\Command\Api\GetAuthenticationTokenCommand();
            $accessKey = $getAuthenticationTokenCommand->execute($user, $password);
            $importContactsCommand = new CreateContactCommand();
	        $importContactsCommand->execute($listId, $toSend, $accessKey);
        } catch (\Exception $e) {
            // Ignoring to prevent errors!
        }
    }
}
