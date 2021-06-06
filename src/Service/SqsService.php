<?php

namespace App\Service;

use App\Util\Constante;

class SqsService
{
    private $em;

    public function __construct()
    {
        $client = new SqsClient([
            'profile' => 'default',
            'region' => 'us-west-2',
            'version' => '2012-11-05'
        ]);
    }

    public function enqueue($data){
        try{
            $params = [
                'DelaySeconds' => 10,
                'MessageAttributes' => [
                    "Title" => [
                        'DataType' => "String",
                        'StringValue' => "The Hitchhiker's Guide to the Galaxy"
                    ],
                    "Author" => [
                        'DataType' => "String",
                        'StringValue' => "Douglas Adams."
                    ],
                    "WeeksOn" => [
                        'DataType' => "Number",
                        'StringValue' => "6"
                    ]
                ],
                'MessageBody' => "Information about current NY Times fiction bestseller for week of 12/11/2016.",
                'QueueUrl' => 'QUEUE_URL'
            ];
            
            try {
                $result = $client->sendMessage($params);
                var_dump($result);
            } catch (AwsException $e) {
                // output error message if fails
                error_log($e->getMessage());
            }

            /*
            $statuscode = $response->getStatusCode();
            if($statuscode == 200)
                return ['success'=>true,'msg'=>'Exito en el envio de socket'];
            else
                return ['success'=>false,'msg'=>'Error status code ('.$statuscode.').'];

                */


        }catch (\Exception $e){
            return [
                    'success'=>false,
                    'msg'=>$e->getMessage()
                    ];
        }
    }
}