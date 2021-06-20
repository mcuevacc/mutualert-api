<?php

namespace App\Service\Api;

use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\Client;

class SocketService
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function send($data){
        try{
            $client = new Client();
            $resp = $client->request('POST', $this->container->getParameter('app.socket').'socket', [
                'http_errors' => false,
                'json' => $data
            ]);

            $statuscode = $response->getStatusCode();
            if($statuscode == 200)
                return ['success'=>true,'msg'=>'Exito en el envio de socket'];
            else
                return ['success'=>false,'msg'=>'Error status code ('.$statuscode.').'];

        }catch (\Exception $e){
            return [
                    'success'=>false,
                    'msg'=>$e->getMessage()
                    ];
        }
    }
}