<?php

namespace App\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\CRUD\Read;
use App\Service\Util\Constante;
use App\Service\Util\Jwt;
use App\Service\EmergencyService;
use App\Service\Api\SocketService;
use App\Model\Object\Point;

class StateController extends AbstractController
{
    public function location(Request $request, Read $read, Jwt $jwt, EmergencyService $emergencyService, SocketService $socketService): Response
    {
        try{
            $decoded = $jwt->decodeToken($request->headers->get('token'), true);
            if(!$decoded['success']) return $this->json($decoded, Constante::HTTP_UNAUTHORIZED);
            $user = $decoded['data'];

            $cadena = 'latitude,longitude,accuracy';
            $sentencia = $read->getData($cadena);
            eval($sentencia);
            if(!$existen){
                return $this->json(['success'=>false,
                                    'msg'=>'No se encontro al parametro ('.$faltante.')'],
                    Constante::HTTP_BAD_REQUEST);
            }
            $latitude = (float) $latitude;
            $longitude = (float) $longitude;
            $accuracy = (float) $accuracy;

            $state = $user->getState();
            $state->setGeoLocation(new Point($latitude, $longitude));
            $state->setAccuracyLocation($accuracy);
            $this->getDoctrine()->getManager()->persist($state);

            if($state->getInAlert()){
                $emergency = $this->getDoctrine()->getRepository('App:Alert\Emergency')->findOneBy([
                    'idUser'=>$user->getId(),
                    'isActive'=>true
                ]);

                $idsUser = $emergency->getAUserAlert();
                if($user->getConfig()->getAlertOther() || !count($user->getContacts())){
                    $newUsers = $emergencyService->getUsersNear($user->getId(), $state->getGeoLocation(), $idsUser);
                    foreach($newUsers as $newUser){
                        $idsUser[] = $newUser['id'];
                    }
                    $emergency->setAUserAlert($idsUser);
                }
                
                $aLocation = $emergency->getALocation();
                $aLocation[] = [
                    'latitude'=>$latitude,
                    'longitude'=>$longitude,
                    'accuracy'=>$accuracy,
                    'date'=>date_create()
                ];
                $emergency->setALocation($aLocation);
                $this->getDoctrine()->getManager()->persist($emergency);

                $location = [
                    'latitude'=>$latitude,
                    'longitude'=>$longitude,
                    'accuracy'=>$accuracy
                ];

                $dataUser = $emergency->asArray(['id', 'apepat', 'apemat', 'nombres', 'avatar', 'phone', 'startedAt']);
                $dataUser['location'] = $location;

                $socketService->send([
                    ['id'=>implode(',', $idsUser), 'event'=>Constante::EVENT_EMEGENCY_UPDATE, 'data'=>$dataUser],
                    ['id'=>$emergency->getId(), 'event'=>Constante::EVENT_EMEGENCY_UPDATE, 'data'=>['location'=>$location], 'users'=>count($idsUser)]
                ]);
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->json(['success' => true]);

        } catch (\Exception $e){
            return $this->json(['success'=>false,
                                'msg'=>$e->getMessage()],
                Constante::HTTP_SERVER_ERROR);
        }
    }
}