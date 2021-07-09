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
        }

        $usersIContact = $this->getUsersIContact($uId, $uPhone);

        $idsUsers = [];
        $fcmUsers = [];
        $notifyFromMyContact = [];
        $notifyFromSelfContact = [];
        foreach($userContacts as $userContact){
            $repeat = false;
            $idsUsers[] = $userContact['id'];
            foreach($usersIContact as $key => $userIContact){
                if($userIContact['id'] < $userContact['id']) {
                    continue;
                } else if($userIContact['id'] == $userContact['id']) {
                    if($userIContact['fcm']){
                        $fcmUsers[] = $userIContact['id'];
                        $notifyFromMyContact[] = $userIContact['fcm'];
                    }
                    unset($usersIContact[$key]);
                    $repeat = true;
                }
                break;
            }

            if(!$repeat && $userContact['fcm']){
                $fcmUsers[] = $userContact['id'];
                $notifyFromSelfContact[] = $userContact['fcm'];
            }
        }

        $aUsers = ['idsUsers' => $idsUsers,
                    'fcmUsers' => $fcmUsers,
                    'notifyUsers' =>[
                        Constante::NOTIFY_EMERGENCY_FROM_MYCONTACT => $notifyFromMyContact,
                        Constante::NOTIFY_EMERGENCY_FROM_SELFCONTACT => $notifyFromSelfContact,
                        Constante::NOTIFY_EMERGENCY_FROM_DEFAULT => []
                    ]];
        
        //Si no queremos que otros sepan de la alerta
        //pero ya tengo contactos que saben de esa alerta
        if(!$uConfig->getAlertOther() && count($uContacts)){
            return $aUsers;
        }

        foreach($usersIContact as $userIContact){
            $aUsers['idsUsers'][] = $userIContact['id'];
            if($userIContact['fcm']){
                $aUsers['fcmUsers'][] = $userIContact['id'];
                $aUsers['notifyUsers'][Constante::NOTIFY_EMERGENCY_FROM_MYCONTACT][] = $userIContact['fcm'];
            }
        }
        
        $usersNear = $this->getUsersNear($uId, $uState->getGeoLocation(), $aUsers['idsUsers']);
        foreach($usersNear as $userNear){
            $aUsers['idsUsers'][] = $userNear['id'];
        }

        return $aUsers;
    }

    public function getUsersFromPhone($phoneContacts, $uId) {
        $consult = 'SELECT E.id AS id, Se.fcm
                    FROM App:User\Account E
                    JOIN App:User\State S
                    WITH S.idUser = E.id
                    JOIN App:User\Config C
                    WITH C.idUser = E.id
                    LEFT JOIN App:User\Session Se
                    WITH Se.idUser = E.id
                    WHERE E.isActive = 1
                    AND S.inAlert = 0
                    AND C.notifySelfContact = 1
                    AND E.id<>'.$uId.
                    ' AND E.username IN ('.implode(',', $phoneContacts).')
                    ORDER BY id ASC';
        $query = $this->em->createQuery($consult);
        return $query->getResult();
    }

    public function getUsersIContact($uId, $uPhone) {
        $consult = 'SELECT E.id AS id, Se.fcm
                    FROM App:User\Account E
                    JOIN App:User\State S
                    WITH S.idUser = E.id
                    LEFT JOIN App:User\Session Se
                    WITH Se.idUser = E.id
                    WHERE E.isActive = 1
                    AND S.inAlert = 0
                    AND E.id IN (
                        SELECT IDENTITY(A.idUser)
                        FROM App:Alert\Contact A
                        JOIN App:User\Config C
                        WITH C.idUser = A.idUser
                        WHERE A.idUser<>'.$uId.
                        ' AND C.notifyMyContact = 1
                        AND A.phone LIKE \''.$uPhone.'\')
                    ORDER BY id ASC';
        $query = $this->em->createQuery($consult);
        return $query->getResult();
    }

    public function getUsersNear($uId, $geoLocation, $idsUsers) {
        $consult = 'SELECT E.id, DISTANCE(S.geoLocation, POINT_STR(\''.$geoLocation.'\')) AS distance_m, C.alertRadio AS radio
                    FROM App:User\Account E
                    JOIN App:User\State S
                    WITH S.idUser = E.id
                    JOIN App:User\Config C
                    WITH C.idUser = E.id
                    WHERE E.isActive = 1
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