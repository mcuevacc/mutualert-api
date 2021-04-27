<?php

namespace App\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\DBService;
use App\Service\CRUD\Read;
use App\Service\CRUD\Create;
use App\Service\CRUD\Update;
use App\Service\Util\Constante;
use App\Service\Util\Jwt;
use App\Service\Util\Util;
use App\Service\Api\FirebaseService;

class AccountController extends AbstractController
{
    public function exist(Request $request, Read $read): Response
    {
        try{
            $cadena = 'username';
            $sentencia = $read->getData($cadena);  
            eval($sentencia);
            if(!$existen){
                return $this->json(['success'=>false,
                                    'msg'=>'No se encontro al parametro ('.$faltante.')'],
                    Constante::HTTP_BAD_REQUEST);
            }

            $user = $this->getDoctrine()->getRepository('App:User\Account')->findOneByUsername($username);
            return  $this->json(['success'=>true, 'data'=>$user ? true : false]);

        }catch (\Exception $e){
            return $this->json(['success'=>false,
                                'msg'=>$e->getMessage()],
                Constante::HTTP_SERVER_ERROR);
        }
    }

    public function checkCode(Request $request, Read $read, Create $create, Update $update, FirebaseService $firebaseService, Util $util): Response
    {
        try{
            $cadena = 'sessionInfo,code,username';
            $sentencia = $read->getData($cadena);  
            eval($sentencia);
            if(!$existen){
                return $this->json(['success'=>false,
                                    'msg'=>'No se encontro al parametro ('.$faltante.')'],
                    Constante::HTTP_BAD_REQUEST);
            }

            if( !$firebaseService->signInWithPhoneNumber($sessionInfo, $code, $username) ){
                return $this->json(['success'=>false,
                                    'msg'=>'Codigo incorrecto'],
                    Constante::HTTP_BAD_REQUEST);
            }

            $uCode = $this->getDoctrine()->getRepository('App:User\Code')->findOneByUsername($username);
            if($uCode){
                $id=$uCode->getId();
            }

            $this->getDoctrine()->getConnection()->beginTransaction();
            
            $cadena = 'code^'.$code;
            $cadena.= '|f^'.date('Y-m-d h:i:s A');

            if( isset($id) ){
                $uCode = $update->upEntity('User\Code',['id'=>$id,'cadena'=>$cadena]);
            }else{
                $cadena.= '|username^'.$username;
                $uCode = $create->create('User\Code',['cadena'=>$cadena]);
            }

            if( !$uCode['success'] ){
                $this->getDoctrine()->getConnection()->rollBack();
                return $this->json($uCode, Constante::HTTP_SERVER_ERROR);
            }

            $this->getDoctrine()->getConnection()->commit();
            return $this->json(['success'=>true]);
            
        }catch (\Exception $e){
            return $this->json(['success'=>false,
                                'msg'=>$e->getMessage()],
                Constante::HTTP_SERVER_ERROR);
        }
    }

    public function newPassword(Request $request, Read $read, Update $update, Util $util, DBService $dBService): Response
    {
        try{
            $cadena = 'username,code,password';
            $sentencia = $read->getData($cadena);  
            eval($sentencia);
            if(!$existen){
                return $this->json(['success'=>false,
                                    'msg'=>'No se encontro al parametro ('.$faltante.')'],
                    Constante::HTTP_BAD_REQUEST);
            }

            $user = $this->getDoctrine()->getRepository('App:User\Account')->findOneByUsername($username); 
            if( !$user ){
                return $this->json(['success'=>false,
                                    'msg'=>'El numero de telefono no esta registrado'],
                    Constante::HTTP_CONFLICT);
            }
            
            $uCode = $this->getDoctrine()->getRepository('App:User\Code')->findOneByUsername($username);
            if(!$uCode || !$util->isExpiredDate($uCode->getF(), Constante::USER_CODE_TIME)){
                return  $this->json (['success'=>false,
                                      'msg'=>'Codigo no valido o caducado'],
                    Constante::HTTP_NOT_FOUND);
            }

            if( $code!=$uCode->getCode() ){
                return  $this->json (['success'=>false,
                                      'msg'=>'Codigo incorrecto'],
                    Constante::HTTP_BAD_REQUEST);
            }

            $this->getDoctrine()->getConnection()->beginTransaction();

            $dBService->removeCode($username);

            $cadena = 'username^'.$username;
            $cadena.= '|password^'.password_hash($password, PASSWORD_DEFAULT);
            $account = $update->upEntity('User\Account',['id'=>$user->getId(), 'cadena'=>$cadena]);
            if( !$account['success'] ){
                $this->getDoctrine()->getConnection()->rollBack(); 
                return $this->json($account, Constante::HTTP_SERVER_ERROR);
            }
            
            $this->getDoctrine()->getConnection()->commit();
            return  $this->json(['success'=>true]);
        
        }catch (\Exception $e){
            return $this->json(['success'=>false,
                                'msg'=>$e->getMessage()],
                Constante::HTTP_SERVER_ERROR);
        }
    }

    public function updatePassword(Request $request, Read $read, Update $update, Jwt $jwt): Response
    {   
        try{
            $decoded = $jwt->decodeToken($request->headers->get('token'), true);
            if(!$decoded['success']) return $this->json($decoded, Constante::HTTP_UNAUTHORIZED);
            $user = $decoded['data'];

            $cadena = 'oldpwd,newpwd,rnewpwd';
            $sentencia = $read->getData($cadena);  
            eval($sentencia);
            if(!$existen){
                return $this->json(['success'=>false,
                                    'msg'=>'No se encontro al parametro ('.$faltante.')'],
                    Constante::HTTP_BAD_REQUEST);
            }

            if( $newpwd!=$rnewpwd ){
                return $this->json(['success'=>false,
                                    'msg'=>'La nueva clave no coincide con la confirmacion.'],
                    Constante::HTTP_BAD_REQUEST);
            }

            if( !password_verify($oldpwd,$user->getPassword()) ){
                return $this->json(['success'=>false,
                                    'msg'=>'Su contraseña no coincide con la actual.'],
                    Constante::HTTP_CONFLICT);
            }

            if( $oldpwd==$newpwd ){
                return $this->json(['success'=>false,
                                    'msg'=>'La nueva contraseña no puede ser igual a la actual.'],
                    Constante::HTTP_CONFLICT);
            }

            $this->getDoctrine()->getConnection()->beginTransaction();

            $cadena = 'password^'.password_hash($newpwd,PASSWORD_DEFAULT);
            $profile = $update->upEntity('User\Account',['id'=>$user->getId(),'cadena'=>$cadena]);
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