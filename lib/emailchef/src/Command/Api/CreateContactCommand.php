<?php

namespace EMailChef\Command\Api;

use EMailChef\Service\ApiService;

class CreateContactCommand
{
    protected $apiService;

    public function __construct($apiService = null)
    {
        $this->apiService = $apiService ?: new ApiService();
    }

    public function execute($listId, $toSend, $authKey)
    {

	    $email = isset($toSend['email']) ? $toSend['email'] : '';
    	$first_name = isset($toSend['first_name']) ? $toSend['first_name'] : '';
	    $last_name = isset($toSend['last_name']) ? $toSend['last_name'] : '';
        $privacy = isset($toSend['privacy']) ? (int)$toSend['privacy'] : 0;
        $terms = isset($toSend['terms']) ? (int)$toSend['terms'] : 0;
        $newsletter = isset($toSend['newsletter']) ? (int)$toSend['newsletter'] : 0;

	    unset($toSend['email']);
	    unset($toSend['first_name']);
	    unset($toSend['last_name']);
        unset($toSend['privacy']);
        unset($toSend['terms']);
        unset($toSend['newsletter']);

	    $data = array(
            'instance_in' => array(
                'list_id' => $listId,
                'email' => $email,
                'mode' => 'SINGLE_OPT_IN',
                'firstname' => $first_name,
                'lastname' => $last_name,
                'privacy' => $privacy,
                'terms' => $terms,
                'newsletter' => $newsletter,
                'status' => 'ACTIVE',
                'custom_fields' => $toSend,
            ),
        );

        $response = $this->apiService->call('post', 'apps/api/v1/contacts', json_encode($data), $authKey);
        if ($response['code'] != '200') {
            throw new \Exception('Unable to create contact');
        } else {
            return $response['body'];
        }
    }
}
