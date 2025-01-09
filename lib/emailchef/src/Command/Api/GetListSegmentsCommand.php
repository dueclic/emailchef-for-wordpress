<?php

namespace EMailChef\Command\Api;

use EMailChef\Service\ApiService;

class GetListSegmentsCommand
{
    protected $apiService;

    public function __construct($apiService = null)
    {
        $this->apiService = $apiService ?: new ApiService();
    }

    public function execute($listId, $consumerKey, $consumerSecret)
    {
        $response = $this->apiService->call('get', '/apps/api/v1/lists/' . $listId . '/segments?limit=1000', null, $consumerKey, $consumerSecret);
        if ($response['code'] != '200') {
            throw new \Exception('Something wrong');
        } else {
            return $response['body'];
        }
    }
}
