<?php

namespace EMailChef\Command\Api;

use EMailChef\Service\ApiService;

class GetPredefinedFieldsCommand
{
    protected $apiService;

    public function __construct($apiService = null)
    {
        $this->apiService = $apiService ?: new ApiService();
    }

    public function execute($consumerKey, $consumerSecret)
    {
        $response = $this->apiService->call('get', '/apps/api/v1/predefinedfields', null, $consumerKey, $consumerSecret);
        if ($response['code'] != '200') {
            throw new \Exception('Unable to get predefined fields');
        } else {
            return $response['body'];
        }
    }
}
