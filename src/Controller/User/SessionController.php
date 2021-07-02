<?php

namespace App\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\CRUD\Read;
use App\Service\Util\Constante;
use App\Service\Util\Jwt;
use App\Service\Api\FirebaseService;
use App\Entity\User\Session;

class SessionController extends AbstractController
{
    public function fcm(Request $request, Read $read, Jwt $jwt, FirebaseService $firebaseService): Response
    {
        try{
            $decoded = $jwt->decodeToken($request->headers->get('token'), true);
            if(!$decoded['success']) return $this->json($decoded, Constante::HTTP_UNAUTHORIZED);
            $user = $decoded['data'];

            $cadena = 'registrationId';
            $sentencia = $read->getData($cadena);
            eval($sentencia);
            if(!$existen){
                return $this->json(['success'=>false,
                                    'msg'=>'No se encontro al parametro ('.$faltante.')'],
                    Constante::HTTP_BAD_REQUEST);
            }
            
            $session = $this->getDoctrine()->getRepository('App:User\Session')->findOneByIdUser($user->getId());
            if(!$session){
                $session = new Session();
                $session->setIdUser($user);
            }

            $notificationKeyName = 'user-'.$user->getId();
            $notificationKey = $firebaseService->getGroupCM($notificationKeyName);
            if(!$notificationKey){
                $notificationKey = $firebaseService->createGroupCM($notificationKeyName, $registrationId);
            } else {
                $notificationKey = $firebaseService->addGroupCM($notificationKeyName, $registrationId, $notificationKey);
            }

            if(!$notificationKey){
                return $this->json(['success'=>false,
                                'msg'=>"No se puedo vincular dispositivo para notificaciones"],
                Constante::HTTP_CONFLICT);
            }

            $session->setFcm($notificationKey);
            $this->getDoctrine()->getManager()->persist($session);
            $this->getDoctrine()->getManager()->flush();
            return $this->json(['success' => true]);

        } catch (\Exception $e){
            return $this->json(['success'=>false,
                                'msg'=>$e->getMessage()],
                Constante::HTTP_SERVER_ERROR);
        }
    }
}