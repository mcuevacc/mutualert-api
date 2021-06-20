<?php

namespace App\Service\Aws;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Aws\Sqs\SqsClient;

class SqsService
{
    private $container;
    private $client;

    public function __construct(ContainerInterface $container, SqsClient $client){
        $this->container = $container;
        $this->client = $client;
    }

    public function enqueueSms($phone, $message, $key){
        try {
            $params = [
                'MessageAttributes' => [
                    "phone" => [
                        'DataType' => "String",
                        'StringValue' => $phone
                    ],
                    "key" => [
                        'DataType' => "String",
                        'StringValue' => $key
                    ]
                ],
                'MessageBody' => $message,
                'QueueUrl' => $this->container->getParameter('aws.sqs_url')
            ];
            $result = $this->client->sendMessage($params);
        } catch (\Exception $e){
            error_log($e->getMessage());
        }
    }
}