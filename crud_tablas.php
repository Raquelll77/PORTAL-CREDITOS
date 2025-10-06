<?php
require_once ('include/protect.php');

function crudcombolookup($columnas){
		global $columnas_combo,$columnas_combo2,$columnas_combo3,$tabla;
		$salida= array();
	
	if (isset($columnas_combo)){
   		 $i=0;
   		 foreach ($columnas as $campo) {

			$key = array_search($campo, $columnas_combo);
			if ($key===false) {
				$salida[$i]=$campo;
			} else {
				$campoid="id";
				if ($campo=="bodega" or $campo=="distribuidor") {$campoid="codigo"; }
				
				$latabla1=$columnas_combo2[$key] ;
				$latabla2=$columnas_combo2[$key] ;
				if ($tabla==$columnas_combo2[$key]) {
				$latabla1=$columnas_combo2[$key]. " tabla" ;
				$latabla2=" tabla" ;	
				}
				$salida[$i]="(select ".$columnas_combo3[$key]." from ".$latabla1." where ". $latabla2 .".$campoid=$tabla.$campo limit 1) as $campo";
				
				}		
 
			 $i++;
		}
	} else {$salida=$columnas;}
	
	return $salida;
}

$tabla="";
	
switch ($numtabla) {
    case 1:
        $tabla="perfil_tipo";
		$tabla_etiqueta="Tipos de Perfiles";
		$columnas = array( 'id','nombre','orden' );
		$columnas_etiquetas = array( 'ID','Nombre','Orden' );
		$columnas_tipo = array( 'int','text','int' );
        $columnas_mask = array( '','','9?999' );
        break;
    case 2:
$tabla="usuario_grupo"; 
$tabla_etiqueta="Usuario Grupo";
$columnas = array('id','nombre');
$columnas_etiquetas = array('Id','Nombre' );
$columnas_tipo = array('int','text' );
 $columnas_mask = array( '','' );
break;

    case 3:
$tabla="distribuidor"; 
$tabla_etiqueta="Clientes";
$columnas = array('id','codigo','nombre');
$columnas_etiquetas = array('Id','Codigo','Nombre' );
$columnas_tipo = array('int','text','text' );
 $columnas_mask = array( '','','' );
break;

    case 4:
$tabla="bodega"; 
$tabla_etiqueta="Almacenes";
$columnas = array('id','codigo','nombre','id_distribuidor');
$columnas_etiquetas = array('Id','Codigo','Nombre' ,'Distribuidor');
$columnas_tipo = array('int','text','text' ,'text');
 $columnas_mask = array( '','','' ,'');

$columnas_combo  = array('id_distribuidor');
$columnas_combo2 = array('distribuidor');
$columnas_combo3 = array('nombre');
break;

    case 5:
$tabla="empresa"; 
$tabla_etiqueta="Empresas";
$columnas = array('id','nombre');
$columnas_etiquetas = array('Id','Nombre' );
$columnas_tipo = array('int','text' );
 $columnas_mask = array( '','' );
break;

    case 6:
$tabla="usuario_puesto"; 
$tabla_etiqueta="Puestos";
$columnas = array('id','nombre');
$columnas_etiquetas = array('Id','Nombre' );
$columnas_tipo = array('int','text' );
 $columnas_mask = array( '','' );
break;

    case 7:
$tabla="opciones"; 
$tabla_etiqueta="Opciones";
$columnas = array('id','nombre','valor');
$columnas_etiquetas = array('Id','nombre','Valor' );
$columnas_tipo = array('int','text','text' );
 $columnas_mask = array( '','','' );
break;

   
case 15: 
$tabla="usuario"; 
$tabla_etiqueta="Usuario";
$columnas = array('id','usuario','nombre','clave','grupo_id','distribuidor','bodega','activo','email','telefono_fijo','telefono_ext','telefono_movil','empresa_id','puesto_id','acceso_intentos');
$columnas_etiquetas = array('Id','Usuario','Nombre','Clave','Grupo','Cliente','Almacen','Activo','Email','Telefono Fijo','Extension','Telefono Movil','Empresa','Puesto','Cantidad ingresos invalidos' );
$columnas_tipo = array('int','text','text','text','int','text','text','text','text','text','text','text','int','int','int' ); 
 $columnas_mask = array('','','','','','','aa','','','9999-9999','9?9999','9999-9999','','','9?9' );
 
$columnas_combo  = array('grupo_id','distribuidor','bodega','empresa_id','puesto_id');
$columnas_combo2 = array('usuario_grupo','distribuidor','bodega','empresa','usuario_puesto');
$columnas_combo3 = array('nombre','nombre','nombre','nombre','nombre');

break;


case 28: 
$tabla="config"; 
$tabla_etiqueta="Configuracion Gerencia";
$columnas = array('id','nombre','valor');
$columnas_etiquetas = array('ID','Configuracion','Valor' );
$columnas_tipo = array('int','text','text' );
 $columnas_mask = array( '','','' );
break;

}	


	




?>