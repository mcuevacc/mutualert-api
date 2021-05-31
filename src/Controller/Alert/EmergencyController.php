<?php

namespace App\Controller\Alert;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\CRUD\Read;
use App\Service\Util\Constante;
use App\Service\Util\Jwt;

class EmergencyController extends AbstractController
{
    public function start(Request $request, Read $read, Jwt $jwt): Response
    {
        try{
            $decoded = $jwt->decodeToken($request->headers->get('token'), true);
            if(!$decoded['success']) return $this->json($decoded, Constante::HTTP_UNAUTHORIZED);
            $user = $decoded['data'];
            
            $state = $user->getState();
            if( $state->getInAlert() ){
                return $this->json(['success'=>false,
                                    'msg'=>'El modo emergencia ya está activado'],
                    Constante::HTTP_CONFLICT);
            }

            $state->setInAlert(true);
            $this->getDoctrine()->getManager()->persist($state);
            $this->getDoctrine()->getManager()->flush();

            return $this->json(['success'=>true]);

        }catch (\Exception $e){
            return $this->json(['success'=>false,
                                'msg'=>$e->getMessage()],
                Constante::HTTP_SERVER_ERROR);
        }
    }

    public function stop(Request $request, Read $read, Jwt $jwt): Response
    {
        try{
            $decoded = $jwt->decodeToken($request->headers->get('token'), true);
            if(!$decoded['success']) return $this->json($decoded, Constante::HTTP_UNAUTHORIZED);
            $user = $decoded['data'];

            $state = $user->getState();
            if( !$state->getInAlert() ){
                return $this->json(['success'=>false,
                                    'msg'=>'El modo emergencia ya está desactivado'],
                    Constante::HTTP_CONFLICT);
            }

            $state->setInAlert(false);
            $this->getDoctrine()->getManager()->persist($state);
            $this->getDoctrine()->getManager()->flush();

            return $this->json(['success'=>true]);

        }catch (\Exception $e){
            return $this->json(['success'=>false,
                                'msg'=>$e->getMessage()],
                Constante::HTTP_SERVER_ERROR);
        }
    }
}