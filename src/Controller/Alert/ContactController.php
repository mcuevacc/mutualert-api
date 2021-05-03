<?php

namespace App\Controller\Alert;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\CRUD\Read;
use App\Service\CRUD\Create;
use App\Service\CRUD\Update;
use App\Service\Util\Constante;
use App\Service\Util\Jwt;

class ContactController extends AbstractController
{
    public function list(Request $request, Read $read, Jwt $jwt): Response
    {
        try{
            $decoded = $jwt->decodeToken($request->headers->get('token'));
            if(!$decoded['success']) return $this->json($decoded, Constante::HTTP_UNAUTHORIZED);
            $user = $decoded['data'];

            $condicion['where'] = 'idUser^'.$user['id'];

            $respuesta = ['order'=>'id^ASC',
                        'filtro'=>'id,alias,phone'];
            $resp = $read->findEntitys('Alert\Contact', $condicion, $respuesta);
            if(!$resp['success']){
                return $this->json($resp, Constante::HTTP_SERVER_ERROR);
            }
            return $this->json($resp);

        }catch (\Exception $e){
            return $this->json(['success'=>false,
                                'msg'=>$e->getMessage()],
                Constante::HTTP_SERVER_ERROR);
        }
    }

    public function read(Request $request, Read $read, Jwt $jwt, int $id): Response
    {
        try{
            $decoded = $jwt->decodeToken($request->headers->get('token'));
            if(!$decoded['success']) return $this->json($decoded, Constante::HTTP_UNAUTHORIZED);
            $user = $decoded['data'];

            $condicion['where'] = 'id^'.$id.'|idUser^'.$user['id'];
            $filtro = 'id,alias,phone';
            $resp = $read->findEntitys('Alert\Contact', $condicion,['filtro'=>$filtro]);
            if(!$resp['success']){
                return $this->json($resp, Constante::HTTP_SERVER_ERROR);
            }

            if(!count($resp['data'])){
                return $this->json(['success'=>false,
                                    'msg'=>'Contacto no escontrado'],
                    Constante::HTTP_NOT_FOUND);
            }

            $resp['data'] = $resp['data'][0];
            return $this->json($resp);

        }catch (\Exception $e){
            return $this->json(['success'=>false,
                                'msg'=>$e->getMessage()],
                Constante::HTTP_SERVER_ERROR);
        }
    }
    
    public function create(Request $request, Read $read, Create $create, Jwt $jwt): Response
    {
        try{
            $decoded = $jwt->decodeToken($request->headers->get('token'), true);
            if(!$decoded['success']) return $this->json($decoded, Constante::HTTP_UNAUTHORIZED);
            $user = $decoded['data'];

            $cadena = 'alias,phone';
            $sentencia = $read->getData($cadena);  
            eval($sentencia);
            if(!$existen){
                return $this->json(['success'=>false,
                                    'msg'=>'No se encontro al parametro ('.$faltante.')'],
                    Constante::HTTP_BAD_REQUEST);
            }

            if( count($user->getContacts()) >= Constante::ALERT_CONTACT_MAX ){
                return $this->json(['success'=>false,
                                    'msg'=>'Excedió la cantidad máxima de contactos permitidos'],
                    Constante::HTTP_CONFLICT);
            }

            $contactAlias = $this->getDoctrine()->getRepository('App:Alert\Contact')->findOneBy(['idUser'=>$user->getId(), 'alias'=> $alias]);
            if($contactAlias){
                return $this->json(['success'=>false,
                                    'msg'=>'No puedes tener otro contacto con el mismo alias'],
                    Constante::HTTP_BAD_REQUEST);
            }
            
            $contactPhone = $this->getDoctrine()->getRepository('App:Alert\Contact')->findOneBy(['idUser'=>$user->getId(), 'phone'=> $phone]);
            if($contactPhone){
                return $this->json(['success'=>false,
                                    'msg'=>'No puedes tener otro contacto con el mismo teléfono'],
                    Constante::HTTP_BAD_REQUEST);
            }
            
            $this->getDoctrine()->getConnection()->beginTransaction();

            $cadena = 'idUser/User\Account^'.$user->getId();
            $cadena .= '|alias^'.$alias;
            $cadena .= '|phone^'.$phone;
            $contact = $create->create('Alert\Contact', ['cadena'=>$cadena]);
            if( !$contact['success'] ){
                $this->getDoctrine()->getConnection()->rollBack();
                return $this->json($contact, Constante::HTTP_SERVER_ERROR);
            }
            $contact = $contact['data'];

            $this->getDoctrine()->getConnection()->commit();
            return $this->json(['success' => true,
                                'data'=>$contact->asArray(['id','alias','phone'])]);            
            
        }catch (\Exception $e){
            return $this->json(['success'=>false,
                                'msg'=>$e->getMessage()],
                Constante::HTTP_SERVER_ERROR);
        }
    }

    public function update(Request $request, Read $read, Update $update, Jwt $jwt, int $id): Response
    {
        try{            
            $decoded = $jwt->decodeToken($request->headers->get('token'));
            if(!$decoded['success']) return $this->json($decoded, Constante::HTTP_UNAUTHORIZED);
            $user = $decoded['data'];

            $cadena = 'alias,phone';
            $sentencia = $read->getData($cadena, 'PUT');
            eval($sentencia);
            if(!$existen){
                return $this->json(['success'=>false,
                                    'msg'=>'No se encontro al parametro ('.$faltante.')'],
                    Constante::HTTP_BAD_REQUEST);
            }

            $contact = $this->getDoctrine()->getRepository('App:Alert\Contact')->findOneBy(['id'=>$id, 'idUser'=>$user['id']]);
            if(!$contact){
                return $this->json(['success'=>false,
                                    'msg'=>'Contacto no escontrado'],
                    Constante::HTTP_NOT_FOUND);
            }

            $contactAlias = $this->getDoctrine()->getRepository('App:Alert\Contact')->findOneBy(['idUser'=>$user['id'], 'alias'=> $alias]);
            if($contactAlias && $contactAlias->getId()!=$id){
                return $this->json(['success'=>false,
                                    'msg'=>'No puedes tener otro contacto con el mismo alias'],
                    Constante::HTTP_BAD_REQUEST);
            }
            
            $contactPhone = $this->getDoctrine()->getRepository('App:Alert\Contact')->findOneBy(['idUser'=>$user['id'], 'phone'=> $phone]);
            if($contactPhone  && $contactPhone->getId()!=$id){
                return $this->json(['success'=>false,
                                    'msg'=>'No puedes tener otro contacto con el mismo teléfono'],
                    Constante::HTTP_BAD_REQUEST);
            }
            
            $this->getDoctrine()->getConnection()->beginTransaction();

            $cadena = 'alias^'.$alias;
            $cadena .= '|phone^'.$phone;
            $resp = $update->upEntity('Alert\Contact',['id'=>$id,'cadena'=>$cadena],false);
            if(!$resp['success']){
                $this->getDoctrine()->getConnection()->rollBack();
                return $this->json($resp, Constante::HTTP_SERVER_ERROR);
            }

            $this->getDoctrine()->getConnection()->commit();
            return $this->json($resp);

        }catch (\Exception $e){
            return $this->json(['success'=>false,
                                'msg'=>$e->getMessage()],
                Constante::HTTP_SERVER_ERROR);
        }
    }

    public function delete(Request $request, Jwt $jwt, int $id): Response
    {
        try{
            $decoded = $jwt->decodeToken($request->headers->get('token'));
            if(!$decoded['success']) return $this->json($decoded, Constante::HTTP_UNAUTHORIZED);
            $user = $decoded['data'];

            $contact = $this->getDoctrine()->getRepository('App:Alert\Contact')->findOneBy(['id'=>$id, 'idUser'=>$user['id']]);
            if(!$contact){
                return $this->json(['success'=>false,
                                    'msg'=>'Contacto no escontrado'],
                    Constante::HTTP_NOT_FOUND);
            }

            $this->getDoctrine()->getManager()->remove($contact);
            $this->getDoctrine()->getManager()->flush();
            return $this->json(['success' => true]);

        }catch (\Exception $e){
            return $this->json(['success'=>false,
                                'msg'=>$e->getMessage()],
                Constante::HTTP_SERVER_ERROR);
        }
    }
}