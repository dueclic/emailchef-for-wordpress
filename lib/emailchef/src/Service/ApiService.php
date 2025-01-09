<?php

namespace EMailChef\Service;

class ApiService
{
    const ENDPOINT = 'https://app.emailchef.com';

    public function call($method, $path, $data = null, $consumerKey = null, $consumerSecret = null)
    {

        $endpoint_url = apply_filters('emailchef_endpoint_url', self::ENDPOINT);
        $endpoint_path = $endpoint_url . $path;

        switch ($method) {
            case 'post':
                $response = \Httpful\Request::post($endpoint_path, $data);
                break;
            case 'delete':
                $response = \Httpful\Request::delete($endpoint_path, $data);
                break;
            case 'put':
                $response = \Httpful\Request::put($endpoint_path, $data);
                break;
            case 'get':
            default:
                $response = \Httpful\Request::get($endpoint_path);
                break;
        }


        $response = $response->addHeader('Content-Type', 'application/json');

        if (!is_null($consumerKey) && !is_null($consumerSecret)) {
            $response = $response->addHeader('consumerKey', $consumerKey);
            $response = $response->addHeader('consumerSecret', $consumerSecret);
        }

        $response = $response->send();

        return array('body' => $response->body, 'code' => $response->code, 'debug' => $response);
    }
}
