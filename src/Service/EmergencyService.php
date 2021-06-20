<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Aws\Sqs\SqsClient;
use App\Service\Util\Constante;
use App\Service\Aws\SqsService;

class EmergencyService
{
    private $em;
    private $container;
    private $sqsClient;

    public function __construct(EntityManagerInterface $entityManager, ContainerInterface $container, SqsClient $client) {
        $this->em = $entityManager;
        $this->container = $container;
        $this->sqsClient = $client;
    }

    public function list($uId) {
        $consult = 'SELECT E
                    FROM App:Alert\Emergency E
                    WHERE E.isActive = 1
                    AND E.idUser <> '.$uId.
                    ' AND JSON_CONTAINS(E.aUserAlert, \''.$uId.'\') = 1';
        $query = $this->em->createQuery($consult);
        return $query->getResult();
    }

    public function getIdsUser($uState, $uConfig, $uContacts, $uId, $uPhone, $uProfile, $idEmergency) {
        $idsUsers = []; //Ids de los usuarios que seran notificados por la emergencia

        $phoneContacts = []; //Teléfono de los contactos agregados
        foreach($uContacts as $contact){
            $phoneContacts[] = $contact->getPhone();
        }
        
        if(count($phoneContacts)){
            $sqsService = new SqsService($this->container, $this->sqsClient);
            $message = 'Soy '.$uProfile->getNombres().', necesito tu ayuda: '.$this->container->getParameter('app.socket').'?id='.$idEmergency;
            $key = Constante::TYPE_EMEGENCY.'-'.$idEmergency;
            foreach($phoneContacts as $phoneContact){
                $sqsService->enqueueSms($phoneContact, $message, $key);
            }
        }
        
        $userContacts = []; //Usuarios que son nuestro contacto
        if(count($phoneContacts)){
            $userContacts = $this->getUsersFromPhone($phoneContacts, $uId);
            foreach($userContacts as $userContact){
                $idsUsers[] = $userContact['id'];
            }
        }

        //Si no queremos que otros sepan de la alerta
        //pero ya tengo contactos que saben de esa alerta
        if(!$uConfig->getAlertOther() && count($uContacts)){
            return $idsUsers;
        }
        
        $usersIContact = $this->getUsersIContact($uId, $uPhone, $idsUsers);
        foreach($usersIContact as $userIContact){
            $idsUsers[] = $userIContact['id'];
        }
        
        $usersNear = $this->getUsersNear($uId, $uState->getGeoLocation(), $idsUsers);
        foreach($usersNear as $userNear){
            $idsUsers[] = $userNear['id'];
        }

        return $idsUsers;
    }

    public function getUsersFromPhone($phoneContacts, $uId) {
        $consult = 'SELECT E.id
                    FROM App:User\Account E
                    JOIN App:User\State S
                    JOIN App:User\Config C
                    WHERE E.id = S.idUser
                    AND E.id = C.idUser
                    AND E.isActive = 1
                    AND S.inAlert = 0
                    AND C.notifySelfContact = 1
                    AND E.id<>'.$uId.
                    ' AND E.username IN ('.implode(',', $phoneContacts).')';
        $query = $this->em->createQuery($consult);
        return $query->getResult();
    }

    public function getUsersIContact($uId, $uPhone, $idsUsers) {
        $consult = 'SELECT E.id
                    FROM App:User\Account E
                    JOIN App:User\State S
                    WHERE E.id = S.idUser
                    AND E.isActive = 1
                    AND S.inAlert = 0
                    AND E.id IN (
                        SELECT IDENTITY(A.idUser)
                        FROM App:Alert\Contact A
                        JOIN App:User\Config C
                        WHERE A.idUser = C.idUser
                        AND A.idUser<>'.$uId.
                        ' AND C.notifyMyContact = 1
                        AND A.phone LIKE \''.$uPhone.'\'';
        if(count($idsUsers)){
            $consult .= ' AND A.idUser NOT IN ('.implode(',', $idsUsers).')';
        }
        $consult .= ')';
        $query = $this->em->createQuery($consult);
        return $query->getResult();
    }

    public function getUsersNear($uId, $geoLocation, $idsUsers) {
        $consult = 'SELECT E.id, DISTANCE(S.geoLocation, POINT_STR(\''.$geoLocation.'\')) AS distance_m, C.alertRadio AS radio
                    FROM App:User\Account E
                    JOIN App:User\State S
                    JOIN App:User\Config C
                    WHERE E.id = S.idUser
                    AND E.id = C.idUser
                    AND E.isActive = 1
                    AND S.inAlert = 0
                    AND C.notifyOther = 1
                    AND E.id<>'.$uId;
        if(count($idsUsers)){
            $consult .= ' AND E.id NOT IN ('.implode(',', $idsUsers).')';
        }
        $consult .= ' HAVING distance_m < radio';
        $query = $this->em->createQuery($consult);
        return $query->getResult();
    }
}