<?php

namespace App\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\DBService;
use App\Service\CRUD\Read;
use App\Service\CRUD\Create;
use App\Service\Util\Constante;
use App\Service\Util\Jwt;
use App\Service\Util\Util;


class AuthController extends AbstractController
{
    public function login(Request $request, Read $read, Jwt $jwt): Response
    {
        try{
            $cadena = 'username,password';
            $sentencia = $read->getData($cadena);  
            eval($sentencia);
            if(!$existen){
                return $this->json(['success'=>false,
                                    'msg'=>'No se encontro al parametro ('.$faltante.')'],
                    Constante::HTTP_BAD_REQUEST);
            }

            $user = $this->getDoctrine()->getRepository('App:User\Account')->findOneByUsername($username);
            if($user && password_verify($password, $user->getPassword())){
                if(!$user->getIsActive()){
                    return $this->json(['success'=>FALSE,'msg'=>'Usario inactivo'], Constante::HTTP_CONFLICT);
                }

                return $this->json([
                    'success' => true,
                    'data'=>[
                        'token' => $jwt->getToken($user),
                        'profile' => $user->getProfile()->asArray(['apepat','apemat','nombres','email','avatar'])
                    ]]);
            }

            return $this->json(['success'=>false, 'msg'=>'Teléfono o contraseña incorrecta'], Constante::HTTP_NOT_FOUND);
        }catch (\Exception $e){
            return $this->json(['success'=>false,
                                'msg'=>$e->getMessage()],
                Constante::HTTP_SERVER_ERROR);
        }
    }

    public function token(Request $request, Jwt $jwt): Response
    {   
        try{        
            $decoded = $jwt->decodeToken($request->headers->get('token'), true);
            if(!$decoded['success']) return $this->json($decoded, Constante::HTTP_UNAUTHORIZED);
            $user = $decoded['data'];

            return $this->json([
                'success' => true,
                'data'=>[
                    'token' => $jwt->getToken($user),
                    'profile' => $user->getProfile()->asArray(['apepat','apemat','nombres','email','avatar'])
                ]
            ]);

        }catch (\Exception $e){
            return $this->json(['success'=>false,
                                'msg'=>$e->getMessage()],
                Constante::HTTP_SERVER_ERROR);
        }
    }

    public function signUp(Request $request, Read $read, Create $create, Jwt $jwt, Util $util, DBService $dBService): Response
    {
        try{
            $cadena = 'username,code,password,apepat,apemat,nombres';
            $sentencia = $read->getData($cadena);  
            eval($sentencia);
            if(!$existen){
                return $this->json(['success'=>false,
                                    'msg'=>'No se encontro al parametro ('.$faltante.')'],
                    Constante::HTTP_BAD_REQUEST);
            }

            $oUser = $this->getDoctrine()->getRepository('App:User\Account')->findOneByUsername($username);
            if( $oUser ){
                return $this->json(['success'=>false,
                                    'msg'=>'El numero de teléfono ya esta siendo usado'],
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
            $account = $create->create('User\Account', ['cadena'=>$cadena]);
            if( !$account['success'] ){
                $this->getDoctrine()->getConnection()->rollBack(); 
                return $this->json($account, Constante::HTTP_SERVER_ERROR);
            }
            $account = $account['data'];
  
            $cadena = 'idUser/User\Account^'.$account->getId();
            $cadena.= '|apepat^'.$apepat;
            $cadena.= '|apemat^'.$apemat;
            $cadena.= '|nombres^'.$nombres;
            $profile = $create->create('User\Profile', ['cadena'=>$cadena]);
            if( !$profile['success'] ){
                $this->getDoctrine()->getConnection()->rollBack();
                return $this->json($profile, Constante::HTTP_SERVER_ERROR);
            }
            $profile = $profile['data'];

            $this->getDoctrine()->getConnection()->commit();
            return $this->json([
                'success' => true,
                'data'=>[
                    'token' => $jwt->getToken($account),
                    'profile' => $profile->asArray(['apepat','apemat','nombres','email','avatar'])
                ]]);
        
        }catch (\Exception $e){
            return $this->json(['success'=>false,
                                'msg'=>$e->getMessage()],
                Constante::HTTP_SERVER_ERROR);
        }
    }
}