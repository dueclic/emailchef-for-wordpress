<?php

namespace EMailChef\Command\Api;

use EMailChef\Service\ApiService;

class GetAccountCurrentCommand
{
    protected $apiService;

    public function __construct($apiService = null)
    {
        $this->apiService = $apiService ?: new ApiService();
    }

    public function execute($consumer_key, $consumer_secret)
    {
        $response = $this->apiService->call('get', '/apps/api/v1/accounts/current', [], $consumer_key, $consumer_secret);
        if ($response['code'] == '200') {
            return $response['body'];
        } else if ($response['code'] == '401') {
            throw new \Exception('Unable to login', 'auth_failed');
        } else {
            throw new \Exception('Unable to login', 'server_error');
        }
    }
}
