<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Aws\Sqs\SqsClient;
use App\Service\Util\Constante;
use App\Service\Aws\SqsService;

class SessionService
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->em = $entityManager;
    }

    public function getFcmTokens($idsUsers) {
        $consult = 'SELECT S.fcm
                    FROM App:User\Session S
                    WHERE S.idUser IN ('.implode(',', $idsUsers).')
                     AND S.fcm IS NOT NULL AND S.fcm <> \'\'';
        $query = $this->em->createQuery($consult);
        return $query->getResult();
    }
}