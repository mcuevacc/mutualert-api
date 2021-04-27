<?php

namespace App\Service\CRUD;

use Doctrine\ORM\EntityManagerInterface;

class Create
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function create($entidad,$data,$entity=TRUE,$flush=TRUE){

        //try{
            eval('$entidad = new App\Entity\\'.$entidad.'();'); // instancia de la entidad

            $cadena = explode('|',$data['cadena']);

            //Funcion auxiliar
            if( isset($data['entitys']) && !is_null($data['entitys']) ){
                $entitys = explode(',',$data['entitys']);
                foreach($entitys as $i => $entidades){
                    $entidads = (explode('/',$entidades))[0];

                    foreach($cadena as $j => $parametro){
                        $campo = (explode('^',$parametro))[0];
                        if( strcmp($entidads, $campo) == 0) {
                            $cadena[$j] = $entidades.'^'.(explode('^',$parametro))[1];
                        }
                    }
                }
            }
            /********************* Fin de Funcion Auxiliar ************************/
            for($i=0;$i<count($cadena);$i++){
                
                $aux = $cadena[$i];
                $aux = explode('^',$aux);
                $campo = ucwords($aux[0]);
                
                if($aux[1]==''){$aux[1]=NULL;}
                
                if($campo[0]=='F' && !is_null($aux[1]) && strlen($aux[1])>10){
                    eval('$entidad->set'.$campo.'(date_create_from_format( \'Y-m-d h:i:s A\',$aux[1]));'); 
                } else if( strpos($campo, '\\') ){
                    eval('$objeto=$this->em->getRepository(\'App:'.explode('/',$campo)[1].'\')->findOneById($aux[1]);');
                    eval('$entidad->set'.explode('/',$campo)[0].'($objeto);');   
                } else{
                        if( !is_null($aux[1]) && ($aux[1][0]=='[' || $aux[1][0]=='{') )
                            $aux[1] = json_decode($aux[1], 1);
                    eval('$entidad->set'.$campo.'($aux[1]);');                    
                }
            }

            $this->em->persist($entidad);
            if($flush){
                $this->em->flush();
            }

            $resp = [
                    'success'=>true,
                    ];
            if( $entity ){
                $resp['data'] = $entidad;
            }
            return $resp;
        /*
        }
        catch (\Exception $e) {
            return [
                    'success'=>false,
                    'msg'=>$e->getMessage()
                    ];
        }
        */
    }
}