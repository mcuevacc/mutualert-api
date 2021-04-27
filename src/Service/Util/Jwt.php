<?php

namespace App\Service\Util;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use \Firebase\JWT\JWT as firejwt;

class Jwt
{
    private $em;
    private $container;

    public function __construct(EntityManagerInterface $entityManager, ContainerInterface $container)
    {
        $this->em = $entityManager;
        $this->container = $container;
    }

    public function getToken($user)
    {
        $payload = [
            "id" => $user->getId(),
            "username" => $user->getUsername(),
            "iat" => time(),
            "exp" => time() + (7*24*60*60)
        ];
        
		return firejwt::encode($payload, $this->container->getParameter('app.jwt_key'), 'HS256');
	}

    public function decodeToken($token, $object=FALSE)
    {
        try{
            $array = (array) firejwt::decode($token, $this->container->getParameter('app.jwt_key'), array('HS256'));
            if(!$object)
                return ['success'=>TRUE, 'data'=>$array];

            $user = $this->em->getRepository('App:User\Account')->findOneById($array['id']);            
            if( $user ){
                if( $user->getIsActive() )
                    return ['success'=>TRUE, 'data'=>$user];

                return ['success'=>FALSE,'msg'=>'¡Usuario inactivo!'];
            }
            return ['success'=>FALSE,'msg'=>'logout'];
        
        }catch (\Exception $e){
            return ['success'=>FALSE,'msg'=>'logout'];
        }
    }
}