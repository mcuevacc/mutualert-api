<?php

namespace App\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\CRUD\Read;
use App\Service\Util\Constante;
use App\Service\Util\Jwt;
use App\Model\Object\Point;

class StateController extends AbstractController
{
    public function location(Request $request, Read $read, Jwt $jwt): Response
    {
        try{
            $decoded = $jwt->decodeToken($request->headers->get('token'), true);
            if(!$decoded['success']) return $this->json($decoded, Constante::HTTP_UNAUTHORIZED);
            $user = $decoded['data'];

            $cadena = 'latitude,longitude,accuracy';
            $sentencia = $read->getData($cadena, 'PUT');
            eval($sentencia);
            if(!$existen){
                return $this->json(['success'=>false,
                                    'msg'=>'No se encontro al parametro ('.$faltante.')'],
                    Constante::HTTP_BAD_REQUEST);
            }

            $state = $user->getState();
            $state->setGeoLocation(new Point($latitude, $longitude));
            $state->setAccuracyLocation($accuracy);
            $this->getDoctrine()->getManager()->persist($state);
            $this->getDoctrine()->getManager()->flush();

            if($state->getInAlert()){
                //$resp = $this->get('UserAlert')->UpdateAlertLocation($user);
                //return $this->json($resp);
            } 
            return $this->json(['success' => true]);

        }catch (\Exception $e){
            return $this->json(['success'=>false,
                                'msg'=>$e->getMessage()],
                Constante::HTTP_SERVER_ERROR);
        }
    }
}