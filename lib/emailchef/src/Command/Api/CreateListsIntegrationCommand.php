<?php

namespace EMailChef\Command\Api;

use EMailChef\Service\ApiService;

class CreateListsIntegrationCommand
{
    protected $apiService;

    public function __construct($apiService = null)
    {
        $this->apiService = $apiService ?: new ApiService();
    }

    public function execute($consumerKey, $consumerSecret, $listId)
    {
        $data = array(
            'instance_in' => array(
	            "list_id"        => $listId,
	            "integration_id" => 5,
	            "website"        => get_site_url(),
            ),
        );
        $response = $this->apiService->call('post', '/apps/api/v1/integrations', json_encode($data), $consumerKey, $consumerSecret);
        if ($response['code'] != '200') {
            throw new \Exception('Unable to create integration');
        } else {
            return $response['body'];
        }
    }
}
