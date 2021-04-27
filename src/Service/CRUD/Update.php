<?php

namespace App\Service\CRUD;

use Doctrine\ORM\EntityManagerInterface;

class Update
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function upEntity($entidad,$data,$entity=TRUE,$flush=TRUE){

        //try{
            $cadena = explode('|',$data['cadena']);

            $campos = []; // array donde se guardan el nombre de los campos a modificar por el post
            $parametros = []; //array 2d donde se guardan el campo y valor a modificar por el post
            for($i=0;$i<count($cadena);$i++){
                $aux = explode('^',$cadena[$i]);
                if( count(explode('^',$cadena[$i]))>1 ){
                    $campos[] = $aux[0];
                    $parametros[] = $aux;
                }
            }

            if( isset($data['camposNo']) && !is_null($data['camposNo']) ){                
                $camposNo = explode(',',$data['camposNo']);
                //restamos los campos a $campos de $camposNo
                $camposSi = array_diff($campos, $camposNo);  

                foreach ($parametros as $i => $parametro) {
                    $exist = false;
                    foreach ($camposSi as $j => $campoSi) {
                        if( strcmp($parametro[0], $campoSi) == 0) {
                            unset($camposSi[$j]); //si se encontro el campo se eliminara el elemento de $camposSi
                            $exist = true; // si el campo es igual a camposSi se cambiara el flag
                        }
                    }
                    if(!$exist){
                        unset($parametros[$i]); //si no se encontro el campo se eliminara el elemento del array parametros
                    }
                }
            }

            //Validando si se envió entitys
            if( isset($data['entitys']) && !is_null($data['entitys']) ){
                $entitys = explode(',',$data['entitys']);
                $newparametros = []; //array 2d donde se guardan el campo y valor a modificar por el post
                for($i=0;$i<count($entitys);$i++){
                    $newparametros[] = explode('/',$entitys[$i]);
                }
                foreach ($parametros as $i => $parametro) {             
                    foreach ($newparametros as $j => $newparametro) {
                        if( strcmp($parametro[0], $newparametro[0]) == 0) {
                            $parametros[$i][0] .="/".$newparametros[$j][1]; //se concatena el campo con su ruta(entidad)
                            unset($newparametros[$j]); //si no se encontro el campo se eliminara el elemento del array parametros
                        }
                    }
                }
            }
            
            //Obteniendo la variable por cual buscar.
            $search='id';
            if(isset($data['search'])){
                $search = $data['search'];
            }
            $search[0] = ucwords($search[0]);
            eval('$entidad = $this->em->getRepository(\'App:'.$entidad.'\')->findOneBy'.$search.'($data["id"]);');
            if($entidad){
                foreach ($parametros as $i => $parametro) { 
                    $parametros[$i][0] = ucwords($parametro[0]);
                    if($parametro[1] == '' ){
                        $parametros[$i][1] = NULL;
                    }                    
                }

                foreach ($parametros as $parametro) {
                    if(is_null($parametro[1]) ){
                        $aux = explode('/',$parametro[0]);
                        eval('$entidad->set'.$aux[0].'(NULL);');
                    }
                    else if($parametro[0][0]=='F'){
                        eval('$entidad->set'.$parametro[0].'(date_create_from_format( \'Y-m-d h:i:s A\',$parametro[1]));'); 
                    }
                    else{
                        if(strpos($parametro[0], '\\')){
                            $aux = explode('/',$parametro[0]);
                            eval('$objeto=$this->em->getRepository(\'App:'.$aux[1].'\')->findOneById($parametro[1]);');
                            eval('$entidad->set'.$aux[0].'($objeto);');
                        }
                        else{
                            if($parametro[1][0]=='[' || $parametro[1][0]=='{'){
                                $parametro[1] = json_decode($parametro[1], 1);
                            }
                            eval('$entidad->set'.$parametro[0].'($parametro[1]);');
                        }    
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
            }
            else{
                return [
                    'success'=>false,
                    'msg'=>'elemento no encontrado: ' . $data['cadena']
                    ];
            }
        /*
        }catch (\Exception $e){
            return [
                    'success'=>false,
                    'msg'=>$e->getMessage()
                    ];
        }*/
    }
}