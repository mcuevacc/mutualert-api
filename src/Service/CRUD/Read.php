<?php

namespace App\Service\CRUD;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Knp\Component\Pager\Paginator;

class Read
{
	private $em;
	private $eventDispatcher;

	public function __construct(EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher = null)
	{
		$this->em = $entityManager;
		$this->eventDispatcher = $eventDispatcher;
	}

	public function getData($cadena, $method='POST'): string
	{
		$sentencia = '';
	
		if($method!='GET'){
			$sentencia.= '$_GET = json_decode($request->getContent(), true);';
		}
		$sentencia.= '$existen = TRUE;';

		$cadena = explode(',',$cadena);
		for($i=0;$i<count($cadena);$i++){
			$sentencia.= 'if(!isset($_GET[\''.$cadena[$i].'\'])){';
			$sentencia.= '$faltante = "'.$cadena[$i].'";';
			$sentencia.= '$existen = FALSE;}';
		}

		$sentencia2='if($existen){';
		for($i=0;$i<count($cadena);$i++){
			$nueva = '$'.$cadena[$i].' = $_GET[\''.$cadena[$i].'\'];';
			$nueva=$nueva.'if($'.$cadena[$i].'==\'\'){$'.$cadena[$i].'=NULL;}';
			$sentencia2 .= $nueva;
		}
		$sentencia = $sentencia.$sentencia2.'}';

		return $sentencia;
	}

	// --- VER ENTIDAD ---
	public function findEntitys($entidad,$condicion,$respuesta=[]){
		try{
			////////////////////////////////////////////////////////////////////////////////////////////////////////
			///////////////////////////////// INICIALIZAMOS LAS VARIABLES //////////////////////////////////////////
			////////////////////////////////////////////////////////////////////////////////////////////////////////
			$withWhere = false;
			$withOrder = false;
			////////////////////////////////////////////////////////////////////////////////////////////////////////
			// --- SI TIENE WHERE LO TRANSFORMAMOS
            if( !isset($condicion['where']) ){
                $condicion['where'] = '';
            }
            $condicion['where'] = 'E.'.$condicion['where'];
            $condicion['where'] = str_replace('|','|E.', $condicion['where']);
            ////////////////////////////////////////////////////////////////////////////////////////////////////////
            // --- SI TIENE FECHA LO TRANSFORMAMOS
            if( !isset($condicion['fecha']) ){
                $condicion['fecha'] = [];
            }
            ////////////////////////////////////////////////////////////////////////////////////////////////////////
            if( !isset($condicion['fecha']['desde']) ){
                $condicion['fecha']['desde'] = '';
            }
            $condicion['fecha']['desde'] = 'E.'.$condicion['fecha']['desde'];
            $condicion['fecha']['desde'] = str_replace('|','|E.', $condicion['fecha']['desde']);
            ////////////////////////////////////////////////////////////////////////////////////////////////////////
            if( !isset($condicion['fecha']['hasta']) ){
                $condicion['fecha']['hasta'] = '';
            }
            $condicion['fecha']['hasta'] = 'E.'.$condicion['fecha']['hasta'];
            $condicion['fecha']['hasta'] = str_replace('|','|E.', $condicion['fecha']['hasta']);
            ////////////////////////////////////////////////////////////////////////////////////////////////////////
            if( !isset($respuesta['order']) ){
                $respuesta['order'] = '';
            }
            $respuesta['order'] = 'E.'.$respuesta['order'];
            $respuesta['order'] = str_replace('|','|E.', $respuesta['order']);
            ////////////////////////////////////////////////////////////////////////////////////////////////////////
            ////////////////////////////////////////////////////////////////////////////////////////////////////////
 
			$consulta = 'SELECT E FROM App:'.$entidad.' E';
 
			// --- SI TIENE JOIN o LEFT JOIN
            if( isset($condicion['leftjoin']) || isset($condicion['join']) ){
                if( isset($condicion['leftjoin']) ){
                    foreach($condicion['leftjoin'] as $i=>$data){
                        if( !isset($data['key']) ){
                            $data['key']='E';
                        }
                        $consulta .= ' LEFT JOIN '.$data['key'].'.'.$data['entidad'].' '.$data['entidad'];
                        // --- SI TIENE WHERE 
                        if( isset($data['condicion']['where']) ){
                            $data['condicion']['where'] = '|'.$data['condicion']['where'];
                            $condicion['where'] .= str_replace('|','|'.$data['entidad'].'.',$data['condicion']['where']);
                        }
                        // --- SI TIENE RANGOS DE FECHA LA BUSQUEDA
                        if( isset($data['condicion']['fecha']) ){
                            // --- SI TIENE FECHA DESDE
                            if( isset($data['condicion']['fecha']['desde']) ){
                                $condicion['fecha']['desde'] = '|'.$data['condicion']['fecha']['desde'];
                                $condicion['fecha']['desde'] .= str_replace('|','|'.$data['entidad'].'.',$data['condicion']['fecha']['desde']);
                            }
                            // --- SI TIENE FECHA HASTA
                            if( isset($data['condicion']['fecha']['hasta']) ){
                                $condicion['fecha']['hasta'] = '|'.$data['condicion']['fecha']['hasta'];
                                $condicion['fecha']['hasta'] .= str_replace('|','|'.$data['entidad'].'.',$data['condicion']['fecha']['hasta']);
                            }
                        }
                        // --- SI TIENE ORDER
                        if( isset($data['respuesta']['order']) ){
                            $data['respuesta']['order'] = '|'.$data['respuesta']['order'];
                            $respuesta['order'] .= str_replace('|','|'.$data['entidad'].'.',$data['respuesta']['order']);
                        }
                    }
                }

                if( isset($condicion['join'])){
                    foreach($condicion['join'] as $i=>$data){
                        if( !isset($data['key']) ){
                            $data['key']='E';
                        }
                        $consulta .= ' JOIN '.$data['key'].'.'.$data['entidad'].' '.$data['entidad'];
                        // --- SI TIENE WHERE 
                        if( isset($data['condicion']['where']) ){
                            $data['condicion']['where'] = '|'.$data['condicion']['where'];
                            $condicion['where'] .= str_replace('|','|'.$data['entidad'].'.',$data['condicion']['where']);
                        }
                        // --- SI TIENE RANGOS DE FECHA LA BUSQUEDA
                        if( isset($data['condicion']['fecha']) ){
                            // --- SI TIENE FECHA DESDE
                            if( isset($data['condicion']['fecha']['desde']) ){
                                $condicion['fecha']['desde'] = '|'.$data['condicion']['fecha']['desde'];
                                $condicion['fecha']['desde'] .= str_replace('|','|'.$data['entidad'].'.',$data['condicion']['fecha']['desde']);
                            }
                            // --- SI TIENE FECHA HASTA
                            if( isset($data['condicion']['fecha']['hasta']) ){
                                $condicion['fecha']['hasta'] = '|'.$data['condicion']['fecha']['hasta'];
                                $condicion['fecha']['hasta'] .= str_replace('|','|'.$data['entidad'].'.',$data['condicion']['fecha']['hasta']);
                            }
                        }
                        // --- SI TIENE ORDER
                        if( isset($data['respuesta']['order']) ){
                            $data['respuesta']['order'] = '|'.$data['respuesta']['order'];
                            $respuesta['order'] .= str_replace('|','|'.$data['entidad'].'.',$data['respuesta']['order']);
                        }
                    }
                }
            }
 
			$condiciones = $condicion;
			// --- SI TIENE CONDICIONES WHERE LA BUSQUEDA (=,IS NULL, IS NOT NULL,LIKE)
			$condicionesAux = explode('|',$condiciones['where']);
			for ($i=0; $i<count($condicionesAux); $i++){
                //////// Pasamos al siguiente cuando hay parametros erroneos ////////
                if($condicionesAux[$i]=='') continue;

                $withOr = false;
                //Preguntamos si existe el OR
                if( strpos($condicionesAux[$i], '^^')!== false){            
                    $withOr = true;
                }
                //Variable que si es vacio no se colocará el AND
                $consultaOr = '';
                
                $condicionesOr = explode('^^',$condicionesAux[$i]); 
                foreach($condicionesOr as $condicionOr){
                    $aux = explode('^', $condicionOr);                                      
                    if( $aux[0]=='' || !isset($aux[1]) ) continue;
                    /////////////////////////////////////////////////////////////////////
                    $campo = $aux[0];
                    if ( preg_match("/^[a-zA-Z0-9]{1,}[\.]{1}[a-zA-Z0-9]{1,}[\.]{1}[a-zA-Z0-9]{1,}$/", $campo) ){
                        $campo = substr($campo,2);
                    }else if( preg_match("/^[a-zA-Z0-9]{1,}$/", $campo) ){
                        $campo = 'E.'.$campo;
                    }
                    $valor = $aux[1];
                    /////////////////////////////////////////////////////////////////////
                    if($withOr){
                        $consultaOr .= $consultaOr=='' ? ' (' : ' OR';
                    }
                    /////////////////////////////////////////////////////////////////////
                    //Para valor nulo
                    if( $valor=='NULL' ){
                        $consultaOr .= ' '.$campo.' IS NULL';
                    }
                    //Para valor no nulo
                    else if( $valor=='!NULL' ){
                        $consultaOr .= ' '.$campo.' IS NOT NULL';
                    }                        
                    //Para un like en ambos lados
                    else if( strpos($valor, '%%LIKE%%' )!== false){
                        $valor = substr($valor, 0,-8);
                        $consultaOr .= ' '.$campo.' LIKE \'%'.$valor.'%\'';
                    }
                    //Para un like a la izquierda
                    else if( strpos($valor, '%LIKE%%' )!== false){                            
                        $valor = substr($valor, 0,-7);
                        $consultaOr .= ' '.$campo.' LIKE \'%'.$valor.'\'';
                    }
                    //Para un like a la derecha
                    else if( strpos($valor, '%%LIKE%' )!== false){                            
                        $valor = substr($valor, 0,-7);
                        $consultaOr .= ' '.$campo.' LIKE \''.$valor.'%\'';
                    }
                    //Para un not like en ambos lados
                    else if( strpos($valor, '%%!LIKE%%' )!== false){
                        $valor = substr($valor, 0,-9);
                        $consultaOr .= ' '.$campo.' NOT LIKE \'%'.$valor.'%\'';
                    }
                    //Para un not like a la izquierda
                    else if( strpos($valor, '%!LIKE%%' )!== false){
                        $valor = substr($valor, 0,-8);
                        $consultaOr .= ' '.$campo.' NOT LIKE \'%'.$valor.'\'';
                    }
                    //Para un like a la derecha
                    else if( strpos($valor, '%%!LIKE%')!== false ){
                        $valor = substr($valor, 0,-8);
                        $consultaOr .= ' '.$campo.' NOT LIKE \''.$valor.'%\'';
                    }
                    //Para un IN
                    else if( strpos($valor, '$IN$')!== false ){
                        $valor = substr($valor, 0,-4);
                        $consultaOr .= ' '.$campo.' IN ('.$valor.')';
                    }
                    //Para un NOT IN
                    else if( strpos($valor, '$!IN$')!== false ){
                        $valor = substr($valor, 0,-5);
                        $consultaOr .= ' '.$campo.' NOT IN ('.$valor.')';
                    }
                    //Para un valor diferente al mandado
                    else if( $valor[0]== '!' ){
                        $valor = substr($valor, 1);
                        $consultaOr .= ' '.$campo.' <> \''.$valor.'\'';
                    }
                    else if( strpos($valor, '%>=%' )!== false){
                        $valor = substr($valor, 0,-4);
                        $consultaOr .= ' '.$campo.' >= \''.$valor.'\'';
                    }
                    else if( strpos($valor, '%<=%' )!== false){
                        $valor = substr($valor, 0,-4);
                        $consultaOr .= ' '.$campo.' <= \''.$valor.'\'';
                    }
                    else if( strpos($valor, '%>%' )!== false){
                        $valor = substr($valor, 0,-3);
                        $consultaOr .= ' '.$campo.' > \''.$valor.'\'';
                    }
                    else if( strpos($valor, '%<%' )!== false){
                        $valor = substr($valor, 0,-3);
                        $consultaOr .= ' '. $campo .' < \''. $valor .'\'';
                    }
                    else{
                        $consultaOr .= ' '.$campo.' = \''.$valor.'\'';
                    }
                }
                /////////////////////////////////////////////////////////////////////
                //Verificamos que existan consultas OR
                if($consultaOr!=''){
                    if($withOr) $consultaOr .= ' )';

                    if(!$withWhere){
                        $consulta .= ' WHERE';
                        $withWhere = true;
                    }
                    else{
                        $consulta .= ' AND';
                    }
                    $consulta .= $consultaOr;
                }
            }
 
			// --- SI TIENE RANGOS DE FECHA LA BUSQUEDA
			if( isset($condiciones['fecha']) ){
                if( isset($condiciones['fecha']['desde']) ){
                    $desde = explode('|',$condiciones['fecha']['desde']);
                    for ($i=0; $i<count($desde);$i++){
                        //////// Pasamos al siguiente cuando hay parametros erroneos ////////
                        if($desde[$i]=='') continue;
                        $aux = explode('^',$desde[$i]);
                        if( $aux[0]=='' || !isset($aux[1]) ) continue;
                        /////////////////////////////////////////////////////////////////////
                        $campo = $aux[0];
                        if ( preg_match("/^[a-zA-Z0-9]{1,}[\.]{1}[a-zA-Z0-9]{1,}[\.]{1}[a-zA-Z0-9]{1,}$/", $campo) ){
                            $campo = substr($campo,2);
                        }
                        $valor = $aux[1];
                        /////////////////////////////////////////////////////////////////////
                        if(!$withWhere){
                            $consulta .= ' WHERE';
                            $withWhere = true;
                        }else{
                            $consulta .= ' AND';
                        }
                        /////////////////////////////////////////////////////////////////////
                        $auxhora = explode(' ',$valor);
                        $hora = count($auxhora)>1 ? ' '.$auxhora[1]:'';

                        $aux = explode('/',$auxhora[0]);
                        $fDesde = $aux[2].'-'.$aux[1].'-'.$aux[0];
                        $consulta .= ' '.$campo.' >= \''.$fDesde.$hora.'\'';
                    }
                }

                if( isset($condiciones['fecha']['hasta']) ){
                    $hasta = explode('|',$condiciones['fecha']['hasta']);
                    for ($i=0; $i<count($hasta);$i++){
                        //////// Pasamos al siguiente cuando hay parametros erroneos ////////
                        if($hasta[$i]=='') continue;
                        $aux = explode('^',$hasta[$i]);
                        if( $aux[0]=='' || !isset($aux[1]) ) continue;
                        /////////////////////////////////////////////////////////////////////
                        $campo = $aux[0];
                        if ( preg_match("/^[a-zA-Z0-9]{1,}[\.]{1}[a-zA-Z0-9]{1,}[\.]{1}[a-zA-Z0-9]{1,}$/", $campo) ){
                            $campo = substr($campo,2);
                        }
                        $valor = $aux[1];
                        /////////////////////////////////////////////////////////////////////
                        if(!$withWhere){
                            $consulta .= ' WHERE';
                            $withWhere = true;
                        }else{
                            $consulta .= ' AND';
                        }
                        /////////////////////////////////////////////////////////////////////
                        $auxhora = explode(' ',$valor);
                        $hora = count($auxhora)>1 ? ' '.$auxhora[1]:' 23:59:59';
                        
                        $aux = explode('/',$auxhora[0]);
                        $fHasta = $aux[2].'-'.$aux[1].'-'.$aux[0];
                        $consulta .= ' '.$campo.' <= \''.$fHasta.$hora.'\'';

                    }
                }
            }

            // --- Si se va a ordenar
            if( isset($respuesta['order']) ){
                $order = explode('|',$respuesta['order']);
                for ($i=0; $i<count($order);$i++){
                    //////// Pasamos al siguiente cuando hay parametros erroneos ////////
                    if($order[$i]=='') continue;
                    $aux = explode('^',$order[$i]);
                    if( $aux[0]=='' || !isset($aux[1]) ) continue;
                    /////////////////////////////////////////////////////////////////////
                    $campo = $aux[0];
                    if ( preg_match("/^[a-zA-Z0-9]{1,}[\.]{1}[a-zA-Z0-9]{1,}[\.]{1}[a-zA-Z0-9]{1,}$/", $campo) ){
                        $campo = substr($campo,2);
                    }
                    $valor = $aux[1];
                    /////////////////////////////////////////////////////////////////////
                    if(!$withOrder){
                        $consulta .= ' ORDER BY';
                        $withOrder = true;
                    }else{
                        $consulta .= ',';
                    }
                    /////////////////////////////////////////////////////////////////////
                        $consulta .= ' '.$campo.' '.$valor;
                }
            }

			$query = $this->em->createQuery($consulta);
 
			// --- SI ENVIA PARAMETROS DE PAGINACION
			if( isset($respuesta['paginacion']) && !is_null($respuesta['paginacion']) ){
				$paginacion = $respuesta['paginacion'];
				$paginator = new Paginator($this->eventDispatcher);      
				$page = $paginacion['page'];
				$numItem = $paginacion['numItem'];
				$entidades =  $paginator->paginate($query,$page,$numItem,array('wrap-queries'=>true));
				$totalItem = $entidades->getTotalItemCount();
			}else{
				 $entidades = $query->getResult();
			}      
 
			// --- SI TIENE FILTRO DE PARAMETROS
			if( isset($respuesta['filtro']) && !is_null($respuesta['filtro']) ){
				$filtro = explode(',',$respuesta['filtro']);  
				$codigo='$filtro = array(';
				for($i=0;$i<count($filtro);$i++){
					if($i>0){
						$codigo.=',';
					}  
					$codigo.=('\''.$filtro[$i].'\'');
				}
				$codigo.=');';
				eval($codigo);
			}
 
			// --- Se extrae toda la data
			if( isset($respuesta['objeto']) && $respuesta['objeto'] ){
                $entidad = $entidades;
            }else{
                $entidad = [];
                foreach ($entidades as $ente){
					if( isset($filtro) ){
						$entidad[] = $ente->asArray($filtro);                    
					}
					else{
						$entidad[] = $ente->asArray();
					}
				}
            }

			$resp = [
					'success'=>true,
					'data'=>$entidad
					];

			if( isset($respuesta['paginacion']) && !is_null($respuesta['paginacion']) ){
				$resp['paginacion'] = ['page'=>$page,'numItem'=>$numItem,'totalItem'=>$totalItem];
			}
 
			return $resp;
		
		 }catch (\Exception $e){
			 $error = $e->getMessage();
			 return [
					 'success'=>false,
					 'msg'=>$error
					];
		 }
	}
}