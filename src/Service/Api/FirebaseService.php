<?php

namespace App\Service\Api;

use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\Client;

class FirebaseService
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function signInWithPhoneNumber($sessionInfo, $code, $phone){

        $client = new Client();
        $resp = $client->request('POST', 'https://identitytoolkit.googleapis.com/v1/accounts:signInWithPhoneNumber', [
            'http_errors' => false,
            'query' => ['key' => $this->container->getParameter('firebase.web_api_key')],
            'json' => ['sessionInfo' => $sessionInfo, 'code' => $code]
        ]);

        if($resp->getStatusCode()==200){
            $resp = json_decode($resp->getBody(), true);
            return !strcmp($phone, substr($resp['phoneNumber'], -strlen($phone)));
        }
        
        return false;
    }
}