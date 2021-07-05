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

    public function getGroupCM($notificationKeyName){
        $notificationKey = null;

        $client = new Client();
        $resp = $client->request('GET', 'https://fcm.googleapis.com/fcm/notification', [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'project_id' => $this->container->getParameter('firebase.project_id'),
                'Authorization' => 'key='.$this->container->getParameter('firebase.cm_key')
            ],
            'query' => ['notification_key_name' => $notificationKeyName]
        ]);

        

        if($resp->getStatusCode()==200){
            $resp = json_decode($resp->getBody(), true);
            $notificationKey = $resp['notification_key'];
        }
        return $notificationKey;
    }

    public function createGroupCM($notificationKeyName, $registrationId){
        $notificationKey = null;

        $client = new Client();
        $resp = $client->request('POST', 'https://fcm.googleapis.com/fcm/notification', [
            'http_errors' => false,
            'headers' => [
                'project_id' => $this->container->getParameter('firebase.project_id'),
                'Authorization' => 'key='.$this->container->getParameter('firebase.cm_key')
            ],
            'json' => [
                'operation' => 'create',
                'notification_key_name'=>$notificationKeyName,
                'registration_ids'=>[$registrationId]
            ]
        ]);

        if($resp->getStatusCode()==200){
            $resp = json_decode($resp->getBody(), true);
            $notificationKey = $resp['notification_key'];
        }
        return $notificationKey;
    }

    public function addGroupCM($notificationKeyName, $registrationId, $notificationKey){
        $respNotificationKey = null;

        $client = new Client();
        $resp = $client->request('POST', 'https://fcm.googleapis.com/fcm/notification', [
            'http_errors' => false,
            'headers' => [
                'project_id' => $this->container->getParameter('firebase.project_id'),
                'Authorization' => 'key='.$this->container->getParameter('firebase.cm_key')
            ],
            'json' => [
                'operation' => 'add',
                'notification_key_name'=>$notificationKeyName,
                'notification_key'=>$notificationKey,
                'registration_ids'=>[$registrationId]
            ]
        ]);

        if($resp->getStatusCode()==200){
            $resp = json_decode($resp->getBody(), true);
            $respNotificationKey = $resp['notification_key'];
        }
        return $respNotificationKey;
    }

    public function sendCM($notificationKey, $data){
        $respNotificationKey = null;

        $client = new Client();
        $resp = $client->request('POST', 'https://fcm.googleapis.com/fcm/send', [
            'http_errors' => false,
            'headers' => [
                'Authorization' => 'key='.$this->container->getParameter('firebase.cm_key')
            ],
            'json' => [
                'to' => $notificationKey,
                'data'=>$data
            ]
        ]);
    }
}