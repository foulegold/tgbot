<?php
namespace app\models;

use VK\Exceptions\VKClientException;
use VK\Exceptions\VKOAuthException;
use VK\OAuth\VKOAuth;
use VK\TransportClient\Curl\CurlHttpClient;

class Rework
{
    /**
     * @param string $username
     * @param string $password
     * @param string $code
     * @return array|mixed|null
     * @throws VKClientException
     * @throws VKOAuthException
     */
    public static function token(string $username, string $password, string $code)
    {
        $client_id = '3140623';
        $client_secret = 'VeWdmVclDCtn6ihuP1nt';
        $fa2_supported = '1';

        $params = array(
            'grant_type' => 'password',
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'username' => $username,
            'password' => $password,
            '2fa_supported' => $fa2_supported,
        );
        if ($code <> "")
        {
            $params['code'] = $code;
        }

        $http_client = new CurlHttpClient(10);
        $response = $http_client->post('https://oauth.vk.com/token', $params);
        return json_decode((string) $response->getBody(), true);
    }

}
