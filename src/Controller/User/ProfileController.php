<?php

namespace App\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\CRUD\Read;
use App\Service\CRUD\Update;
use App\Service\Util\Constante;
use App\Service\Util\Jwt;

class ProfileController extends AbstractController
{
    public function read(Request $request, Jwt $jwt): Response
    {
        try{
            $decoded = $jwt->decodeToken($request->headers->get('token'), true);
            if(!$decoded['success']) return $this->json($decoded, Constante::HTTP_UNAUTHORIZED);
            $user = $decoded['data'];

            return $this->json(['success' => true,
                                'data'=>$user->getProfile()->asArray(['apepat','apemat','nombres','email','avatar'])]);
        
        }catch (\Exception $e){
            return $this->json(['success'=>false,
                                'msg'=>$e->getMessage()],
                Constante::HTTP_SERVER_ERROR);
        }
    }

    public function edit(Request $request, Read $read, Update $update, Jwt $jwt): Response
    {   
        try{
            $decoded = $jwt->decodeToken($request->headers->get('token'));
            if(!$decoded['success']) return $this->json($decoded, Constante::HTTP_UNAUTHORIZED);
            $user = $decoded['data'];

            $cadena = 'email,apepat,apemat,nombres';
            $sentencia = $read->getData($cadena);  
            eval($sentencia);
            if(!$existen){
                return $this->json(['success'=>false,
                                    'msg'=>'No se encontro al parametro ('.$faltante.')'],
                    Constante::HTTP_BAD_REQUEST);
            }
            
            $oUser = $this->getDoctrine()->getRepository('App:User\Profile')->findOneByEmail($email);
            if( $oUser && $user['id']!=$oUser->getIdUser()->getId()){
                return $this->json(['success'=>false,
                                    'msg'=>'El email ya esta siendo usado.'],
                    Constante::HTTP_CONFLICT);
            }

            $this->getDoctrine()->getConnection()->beginTransaction();

            $cadena = 'apepat^'.$apepat;
            $cadena .= '|apemat^'.$apemat;
            $cadena .= '|nombres^'.$nombres;
            $cadena .= '|email^'.$email;
            $profile = $update->upEntity('User\Profile', ['id'=>$user['id'],'search'=>'idUser','cadena'=>$cadena]);
            if(!$profile['success']){
                $this->getDoctrine()->getConnection()->rollBack();
                return $this->json($resp, Constante::HTTP_SERVER_ERROR);
            }

            $this->getDoctrine()->getConnection()->commit();
            return $this->json(['success'=>true]);

        }catch (\Exception $e){
            return $this->json(['success'=>false,
                                'msg'=>$e->getMessage()],
                Constante::HTTP_SERVER_ERROR);
        }
    }
}