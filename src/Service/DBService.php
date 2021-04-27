<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class DBService
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function removeCode($username){
        $code = $this->em->getRepository('App:User\Code')->findOneByUsername($username);
        if($code){
            $this->em->remove($code);
            $this->em->flush();
        }
    }
}