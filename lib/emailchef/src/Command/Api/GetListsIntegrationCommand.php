<?php

namespace EMailChef\Command\Api;

use EMailChef\Service\ApiService;

class GetListsIntegrationCommand
{
	protected $apiService;

	public function __construct($apiService = null)
	{
		$this->apiService = $apiService ?: new ApiService();
	}

	public function execute($consumerKey, $consumerSecret, $list_id)
	{
		$response = $this->apiService->call('get', '/apps/api/v1/lists/'.$list_id.'/integrations', null, $consumerKey, $consumerSecret);
		if ($response['code'] != '200') {
			throw new \Exception('Unable to login');
		} else {
			$integrations = $response['body'];
			return $integrations;
		}
	}
}
