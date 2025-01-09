<?php

namespace EMailChef\Command\Api;

use EMailChef\Service\ApiService;

class ImportContactsInGroupCommand
{
    protected $apiService;

    public function __construct($apiService = null)
    {
        $this->apiService = $apiService ?: new ApiService();
    }

    public function execute($consumerKey, $consumerSecret, $contacts, $listId, $groupId)
    {
        $importContactsCommand = new ImportContactsCommand();
        $importContactsCommand->execute($consumerKey, $consumerSecret, $contacts, $listId);

        $emails = array();
        foreach ($contacts as $contact) {
            $emails[] = $contact['email'];
        }

        $addEmailsToGroupCommand = new AddEmailsToGroupCommand();
        $addEmailsToGroupCommand->execute($consumerKey, $consumerSecret, $emails, $listId, $groupId);

        return true;
    }
}
