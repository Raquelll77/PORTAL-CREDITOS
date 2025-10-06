<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
error_reporting(0);

require_once ('include/protect.php');
require_once ('include/framework.php');

if (!tiene_permiso(15)) { echo mensaje("No tiene privilegios para accesar esta seccion","danger");exit;}

$verror = "";
if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } else   {$accion='1' ;}



$conn = new mysqli(db_ip, db_user, db_pw, db_name);
if (mysqli_connect_errno()) {  echo mensaje("Error al Conectar a la Base de Datos [DB:101]","danger");exit;}
$conn->set_charset("utf8");

//TODO 
if ($accion=="ax") {
    
    $row = array();
    $return_arr = array();
    $row_array = array();
    $tp="";
    
    if((isset($_GET['term']) && strlen($_GET['term']) > 0) || (isset($_GET['id']) && is_numeric($_GET['id'])))
    {
     if(isset($_GET['tp'])) { $tp=$conn->real_escape_string($_GET['tp']);}
        if(isset($_GET['term']))
        {
            $getVar = $conn->real_escape_string($_GET['term']);
            $whereClause =  " and ( codigo LIKE '%" . $getVar ."%' or nombre LIKE '%" . $getVar ."%'  ) ";

        
        }
        elseif(isset($_GET['id']))
        {
            $whereClause =  " codigo = '".$conn->real_escape_string($_GET['id'])."'" ;
        }

        if (isset($_GET['page_limit'])) {$limit = intval($_GET['page_limit']);} else {$limit=20;}
 
        $sql = "SELECT  codigo,nombre, concat(codigo ,' ',nombre) AS text FROM bodega WHERE 1=1 $whereClause limit $limit";
        
    // echo $sql;exit;
            $result = $conn -> query($sql);
          
             if ($result -> num_rows > 0)
            {
                while ($row = $result -> fetch_assoc())
                {
                    $row_array['id'] = $row['codigo'];
                    $row_array['text'] = utf8_encode($row['codigo']." - ". $row["nombre"]);
                    array_push($return_arr,$row_array);
                }
    
            }
            

            


    }
    else
    {
        $row_array['id'] = 0;
        $row_array['text'] = utf8_encode('Escriba....');
        array_push($return_arr,$row_array);
    
    }
    
    $ret = array();

    if(isset($_GET['id']))
    {
        $ret = $row_array;
    }

    else
    {
        $ret['results'] = $return_arr;
    }
    
$conn -> close();
echo salida_json($ret);
exit;   
    
}







if (isset($_REQUEST['fd'],$_REQUEST['fh'])) {  
 
 
        $fechadesde = mysqldate($conn->real_escape_string($_REQUEST['fd'])); 
        $fechahasta = mysqldate($conn->real_escape_string($_REQUEST['fh'])); 
       
$botonregresar='<br><a href="#" onclick="actualizarbox(\'pagina\',\'creditos.php\') ; return false;" class="btn   btn-default   "> Regresar</a>  ';
if (!checkfecha_mysql($fechadesde)) {echo mensaje("Error en la fecha Desde. Debe ingresar una fecha valida","warning");echo $botonregresar;exit;}
if (!checkfecha_mysql($fechahasta)) {echo mensaje("Error en la fecha Hasta. Debe ingresar una fecha valida","warning");echo $botonregresar;exit;}
  } else {
     $fechadesde= date('Y-m-01'); //Y-m-01
     $fechahasta= date('Y-m-t');
     
    
  }
  
  $reporte_fecha= fechademysql($fechadesde)." al ".fechademysql($fechahasta); 
 
// restringir tienda 
$sqlbodega=""; 
$tiendabodega=""; 
$sqlcanal="";  
$tiporeporte=1;

if (isset($_REQUEST['td'])) {$tiendabodega=trim($conn->real_escape_string($_REQUEST['td']));}
if (isset($_REQUEST['tp'])) {$tiporeporte=trim($conn->real_escape_string($_REQUEST['tp']));}

if (isset($_REQUEST['ca'])) {if ($_REQUEST['ca']<>'') {$sqlcanal=" and canal='".trim($conn->real_escape_string($_REQUEST['ca']))."'";}}
 
if ($tiendabodega<>'') {
	
    $sqlbodega.=" and  bodega='$tiendabodega'";
} else {


 if (!tiene_permiso(19)) {// no es creditos
   // if (tiene_permiso(22)) {// jefe tienda
    
     if (tiene_permiso(7)){
                     $sqlbodega.=" and ".armar_sql('bodega',$_SESSION['grupo_bodegas'],'or');
                 
                    } else {
                    if ($_SESSION['usuario_bodega']<>"") {$sqlbodega.=" and  bodega='".$_SESSION['usuario_bodega']."'";}                
                    }
      
   // } 
 } 
}

 
 function crear_pie($nombre,$etiqueta,$color,$porcentaje,$simbolo='%') {
   
   $salida= '  
                
                     <div class="panel-body easypiechart-panel">
                        <h4>'.$etiqueta.'</h4>
                        <div class="easypiechart" id="'.$nombre.'"   data-percent="'.$porcentaje.'" ><span class="percent">'.$porcentaje.$simbolo.'</span>
                        </div>
                    </div> 
                 
                         
                    ';
    $salida.="<script>
             $(function() {
                $('#$nombre').easyPieChart({
                    scaleColor: false,
                    barColor: '$color',
                     size: 70
                        });
              });
                </script>"  ;              
   
   return $salida;
 }



 // TODO estadisticas
if ($accion=="1")  {
    
           
//******* COMPARATIVO ************************************************************************************ 
if ($tiporeporte<>1 and $tiporeporte<>"1") {
  $sqlperiodo="EXTRACT(YEAR_MONTH FROM fecha_alta)";
  $sqlperiodo2="EXTRACT(YEAR_MONTH FROM fecha)";
  if ($tiporeporte==3 or $tiporeporte=="3") {$sqlperiodo="EXTRACT(YEAR FROM fecha_alta)"; $sqlperiodo2="EXTRACT(YEAR FROM fecha)";}

  $sql="
    SELECT 
      ($sqlperiodo) as periodo
      , count(id) as ingresadas 
      ,sum(if(estatus=1,1,0)) as enproceso   ,sum(if(estatus=2,1,0)) as aprobadas 
      ,sum(if(estatus=3,1,0)) as rechazadas  ,sum(if(cierre_contrato>0,1,0)) as entregadas 
      ,sum(if(plazo=12,1,0)) as ingreso12  ,sum(if(plazo=18,1,0)) as ingreso18 
      ,sum(if(plazo=24,1,0)) as ingreso24  ,sum(if(plazo=30,1,0)) as ingreso30 
      ,sum(if(plazo=36,1,0)) as ingreso36 
      ,sum(if(estatus=2 and plazo=12,1,0)) as aprobadas12  ,sum(if(estatus=2 and plazo=18,1,0)) as aprobadas18 
      ,sum(if(estatus=2 and plazo=24,1,0)) as aprobadas24  ,sum(if(estatus=2 and plazo=30,1,0)) as aprobadas30 
      ,sum(if(estatus=2 and plazo=36,1,0)) as aprobadas36 
      ,sum(if(estatus=3 and plazo=12,1,0)) as rechazadas12 ,sum(if(estatus=3 and plazo=18,1,0)) as rechazadas18 
      ,sum(if(estatus=3 and plazo=24,1,0)) as rechazadas24 ,sum(if(estatus=3 and plazo=30,1,0)) as rechazadas30 
      ,sum(if(estatus=3 and plazo=36,1,0)) as rechazadas36
      ,sum(if(cierre_contrato>0,monto_financiar,0)) as mprestamos_entregado
      ,sum(if(cierre_contrato>0,monto_prima,0)) as mprima_entregado 
    FROM prestamo
    where fecha_alta>='$fechadesde' and fecha_alta<='$fechahasta'
      $sqlbodega
      $sqlcanal
    group by $sqlperiodo
    ";

$result = $conn -> query($sql);

if ($result -> num_rows > 0) {
        
              
               
              
                       echo '<div class="row">
                    <div class="table-responsive" >
                      <table class="display nowrap" id="tabla" width="100%" cellspacing="0">
                        <thead>
                          <tr>
                            <th class="text-center">Periodo</th>
                            <th class="text-center">Ingresadas</th>
                            <th class="text-center">Aprobadas</th>
                            <th class="text-center">Entregadas</th>
                            <th class="text-center">Rechazadas</th>
                            <th class="text-center">Pendientes</th>
                            
                            <th class="text-center">% Aprobadas</th>
                            <th class="text-center">% Entregadas</th>
                            <th class="text-center">% Rechazadas</th>
                            <th class="text-center">% Pendientes</th>

                            
                            <th class="text-center">Prestamo promedio</th>
                            <th class="text-center">Prima promedio</th>
                            <th class="text-center">% Prima Promedio</th>                            
                            
                            <th class="text-center">Resolucion General</th>
                            <th class="text-center">Resolucion Creditos Aprobados</th>
                            <th class="text-center">Respuesta Gestion Ventas</th>
                            <th class="text-center">Respuesta Gestion Oficial Creditos</th>
                            <th class="text-center">Recepcion Credito</th>
                            <th class="text-center">Verificacion de campo</th>

                            
                            <th class="text-center">Ingresadas 12 Meses</th>
                            <th class="text-center">Ingresadas 18 Meses</th>
                            <th class="text-center">Ingresadas 24 Meses</th>
                            <th class="text-center">Ingresadas 30 Meses</th>
                            <th class="text-center">Ingresadas 36 Meses</th>
                            
                            <th class="text-center">Aprobadas 12 Meses</th>
                            <th class="text-center">Aprobadas 18 Meses</th>
                            <th class="text-center">Aprobadas 24 Meses</th>
                            <th class="text-center">Aprobadas 30 Meses</th>
                            <th class="text-center">Aprobadas 36 Meses</th>
                            
                            <th class="text-center">Rechazadas 12 Meses</th>
                            <th class="text-center">Rechazadas 18 Meses</th>
                            <th class="text-center">Rechazadas 24 Meses</th>
                            <th class="text-center">Rechazadas 30 Meses</th>
                            <th class="text-center">Rechazadas 36 Meses</th>
                      
 


                          </tr>
                        </thead>
                        <tbody>';
                        
                 
                  
                  
                  
            
 
                        while ($row = $result -> fetch_assoc()) {
                            
                              //### Promedios horas
                        $horaspromedio1=0;
                        $horaspromedio2=0;
                        $horaspromedio3=0;
                        $horaspromedio4=0;
                        $horaspromedio5=0;
                        $horaspromedio6=0;
       
                    
                       $sqlpsql="SELECT  avg(TIMESTAMPDIFF(HOUR, prestamo.fecha_enviar_creditos,(select max(prestamo_gestion.hora) from prestamo_gestion where prestamo_gestion.prestamo_id=prestamo.id limit 1))) as salida
                              FROM prestamo 
                              where $sqlperiodo=".$row["periodo"]." 
                              and estatus<>1  and fecha_enviar_creditos is not null
                              $sqlbodega
                              $sqlcanal
                                   ";
                        $sqlppc = $conn -> query($sqlpsql);
                    if ($sqlppc -> num_rows > 0) {  $rowpp = $sqlppc -> fetch_assoc();
                        $horaspromedio1=round($rowpp["salida"],2) ;               
                        }                        unset ($sqlppc,$rowpp);
                        
                        
                        $sqlpsql=" SELECT  avg(TIMESTAMPDIFF(HOUR, prestamo.fecha_enviar_creditos,aprobado_gerencia_fecha)) as salida
                                  FROM prestamo
                                  where $sqlperiodo=".$row["periodo"]."  
                                  and   fecha_enviar_creditos is not null and aprobado_gerencia_fecha is not null
                                  $sqlbodega
                                  $sqlcanal
                                   ";
                        $sqlppc = $conn -> query($sqlpsql);
                    if ($sqlppc -> num_rows > 0) {  $rowpp = $sqlppc -> fetch_assoc();
                        $horaspromedio2=round($rowpp["salida"],2) ;               
                        }                        unset ($sqlppc,$rowpp);
                       
                        $sqlpsql="SELECT  avg(TIMESTAMPDIFF(HOUR, prestamo_gestion.hora,prestamo_gestion.hora_responde)) as salida
                                  FROM prestamo_gestion
                                  LEFT OUTER JOIN prestamo ON (prestamo.id=prestamo_gestion.prestamo_id)
                                  where  
                                   prestamo_gestion.hora_responde is not null
                                 and $sqlperiodo=".$row["periodo"]."
                                 and $sqlperiodo2=".$row["periodo"]."  
                                 $sqlbodega
                                 $sqlcanal
                                   ";
                        $sqlppc = $conn -> query($sqlpsql);
                    if ($sqlppc -> num_rows > 0) {  $rowpp = $sqlppc -> fetch_assoc();
                        $horaspromedio3=round($rowpp["salida"],2) ;               
                        } 
                    
                        $sqlpsql="SELECT  avg(TIMESTAMPDIFF(HOUR, hora_responde,hora_confirma)) as salida
                                  FROM prestamo_gestion
                                  LEFT OUTER JOIN prestamo ON (prestamo.id=prestamo_gestion.prestamo_id)
                                  where  
                                  hora_responde is not null  and hora_responde is not null 
                                  and $sqlperiodo=".$row["periodo"]."
                                  and $sqlperiodo2=".$row["periodo"]." 
                                  $sqlbodega   
                                  $sqlcanal
                                   ";
                        $sqlppc = $conn -> query($sqlpsql);
                    if ($sqlppc -> num_rows > 0) {  $rowpp = $sqlppc -> fetch_assoc();
                        $horaspromedio4=round($rowpp["salida"],2) ;               
                        }
                    
                    
                        $sqlpsql="SELECT  avg(TIMESTAMPDIFF(HOUR, fecha_enviar_creditos,fecha_recibe_creditos)) as salida
                                  FROM prestamo
                                  where $sqlperiodo=".$row["periodo"]."  
                                  and   fecha_enviar_creditos is not null and fecha_recibe_creditos is not null 
                                  $sqlbodega
                                  $sqlcanal
                                   ";
                        $sqlppc = $conn -> query($sqlpsql);
                    if ($sqlppc -> num_rows > 0) {  $rowpp = $sqlppc -> fetch_assoc();
                        $horaspromedio5=round($rowpp["salida"],2) ;               
                        }
                        
                        $sqlpsql="SELECT  avg(TIMESTAMPDIFF(HOUR, hora,hora_responde)) as salida
                                  FROM prestamo_gestion
                                  LEFT OUTER JOIN prestamo ON (prestamo.id=prestamo_gestion.prestamo_id)
                                  where  
                                  prestamo_gestion.campo_id=31 and hora_responde is not null
                                  and $sqlperiodo=".$row["periodo"]."
                                    and $sqlperiodo2=".$row["periodo"]."
                                    $sqlbodega
                                    $sqlcanal
                                   ";
                        $sqlppc = $conn -> query($sqlpsql);
                    if ($sqlppc -> num_rows > 0) {  $rowpp = $sqlppc -> fetch_assoc();
                        $horaspromedio6=round($rowpp["salida"],2) ;               
                        } 
                    
                            
                                   
                           echo "<tr>
                              <td class='text-center'>".$row["periodo"]."</td>  
                              <td class='text-right'>".$row["ingresadas"]."</td>
                              <td class='text-right'>".$row["aprobadas"]."</td>
                              <td class='text-right'>".$row["entregadas"]."</td>
                              <td class='text-right'>".$row["rechazadas"]."</td>
                              <td class='text-right'>".$row["enproceso"]."</td>
                              <td class='text-right'>".round(($row["aprobadas"]/$row["ingresadas"])*100,2)."</td>
                              <td class='text-right'>".round(($row["entregadas"]/$row["ingresadas"])*100,2)."</td>
                              <td class='text-right'>".round(($row["rechazadas"]/$row["ingresadas"])*100,2)."</td>
                              <td class='text-right'>".round(($row["enproceso"]/$row["ingresadas"])*100,2)."</td>
                              
                              
                              <td class='text-right'>".formato_numero($row["mprestamos_entregado"]/$row["entregadas"],2,'')."</td>
                              <td class='text-right'>".formato_numero($row["mprima_entregado"]/$row["entregadas"],2,'')."</td>
                              <td class='text-right'>".round((($row["mprima_entregado"]/$row["entregadas"])/($row["mprestamos_entregado"]/$row["entregadas"])*100),2)."</td>
        
                        
                                <td class='text-right'>".$horaspromedio1."</td>
                                <td class='text-right'>".$horaspromedio2."</td>
                                <td class='text-right'>".$horaspromedio3."</td>
                                <td class='text-right'>".$horaspromedio4."</td>
                                <td class='text-right'>".$horaspromedio5."</td>
                                <td class='text-right'>".$horaspromedio6."</td>
                       
                              
                                 
                              <td class='text-right'>".$row["ingreso12"]."</td>
                              <td class='text-right'>".$row["ingreso18"]."</td>
                              <td class='text-right'>".$row["ingreso24"]."</td>
                              <td class='text-right'>".$row["ingreso30"]."</td>
                              <td class='text-right'>".$row["ingreso36"]."</td>
                              
                              <td class='text-right'>".$row["aprobadas12"]."</td>
                              <td class='text-right'>".$row["aprobadas18"]."</td>
                              <td class='text-right'>".$row["aprobadas24"]."</td>
                              <td class='text-right'>".$row["aprobadas30"]."</td>
                              <td class='text-right'>".$row["aprobadas36"]."</td>
                              
                              <td class='text-right'>".$row["rechazadas12"]."</td>
                              <td class='text-right'>".$row["rechazadas18"]."</td>
                              <td class='text-right'>".$row["rechazadas24"]."</td>
                              <td class='text-right'>".$row["rechazadas30"]."</td>
                              <td class='text-right'>".$row["rechazadas36"]."</td>
                              
                              </tr>" ; 
                              
                        $reg++;
                        }
                 echo" </tbody>                   
                          </table>
                            </div>
                          </div> ";
              
                echo crear_datatable('tabla','false') ;  
                
                echo $botonregresar;               
                       
                 
                           
                  
              
             } else {echo mensaje("No se encontraron datos para las fechas indicadas","warning");echo $botonregresar;exit;}
                
                exit;   
            }
          //******* COMPARATIVO ************************************************************************************
  
  
  
        //TODO REPORTE NORMAL  
  
         //******* SQL ************************************************************************************
               
            $sql="SELECT 
                count(id) as ingresadas 
                ,sum(if(estatus=1,1,0)) as enproceso 
                ,sum(if(estatus=2,1,0)) as aprobadas 
                ,sum(if(estatus=3,1,0)) as rechazadas 
                 ,sum(if(cierre_contrato>0,1,0)) as entregadas 
                 
                  ,sum(if(plazo=12,1,0)) as ingreso12
                  ,sum(if(plazo=18,1,0)) as ingreso18 
                  ,sum(if(plazo=24,1,0)) as ingreso24 
                  ,sum(if(plazo=30,1,0)) as ingreso30 
                  ,sum(if(plazo=36,1,0)) as ingreso36 
             
                  ,sum(if(estatus=2 and plazo=12,1,0)) as aprobadas12
                  ,sum(if(estatus=2 and plazo=18,1,0)) as aprobadas18 
                  ,sum(if(estatus=2 and plazo=24,1,0)) as aprobadas24 
                  ,sum(if(estatus=2 and plazo=30,1,0)) as aprobadas30 
                  ,sum(if(estatus=2 and plazo=36,1,0)) as aprobadas36 
                  
                  ,sum(if(estatus=3 and plazo=12,1,0)) as rechazadas12
                  ,sum(if(estatus=3 and plazo=18,1,0)) as rechazadas18 
                  ,sum(if(estatus=3 and plazo=24,1,0)) as rechazadas24 
                  ,sum(if(estatus=3 and plazo=30,1,0)) as rechazadas30 
                  ,sum(if(estatus=3 and plazo=36,1,0)) as rechazadas36
                  
                  ,sum(if(cierre_contrato>0,monto_financiar,0)) as mprestamos_entregado
                  ,sum(if(cierre_contrato>0,monto_prima,0)) as mprima_entregado 
                      
                 FROM prestamo
                 
                 where fecha_alta>='$fechadesde' and fecha_alta<='$fechahasta'
                    $sqlbodega
                    $sqlcanal
                ";
                
              $sqltiendas="SELECT bodega FROM prestamo  
              where fecha_alta>='$fechadesde' and fecha_alta<='$fechahasta' 
              $sqlbodega
              $sqlcanal
              group by bodega";
                

        
    

// SELECT prestamo_cierre.nombre , count(tpt) as general, sum(if(tabla.bodega='CUR-02',1,0)) as tienda1    , sum(if(tabla.bodega='TRO-02',1,0)) as tienda2     
// from prestamo_cierre
// left OUTER JOIN    (
// select cierre_razon, bodega, count(*) as tpt
// from prestamo
// where estatus=3
// and fecha_alta>='2011-02-01' and fecha_alta<='2016-02-29' 
// group by cierre_razon, bodega
// ) as tabla ON (prestamo_cierre.id=tabla.cierre_razon)
// 
// where prestamo_cierre.nombre like '%Rechaza%'
// group by prestamo_cierre.nombre 

            // ****** Fin SQL ********************************************************************************
 
             $result = $conn -> query($sql);
          
           
            $tiendas=array();
            $optiontiendas="";
          if ($result -> num_rows > 0) {
                  
                    //tiendas
                    $lastiendas = $conn -> query($sqltiendas);
                    if ($lastiendas -> num_rows > 0) {
                        $i=0;
                         while ($rowtienda = $lastiendas -> fetch_assoc()){
                           $i++; 
                           $tiendas[$i]=$rowtienda['bodega'] ;
                         //  $optiontiendas.='<option value="'.$rowtienda['bodega'].'">'.$rowtienda['bodega'].'</option>';
                         } 
                          
                        }                   
                    //fin tiendas
                  
                  
                  
                  
              
                     $row = $result -> fetch_assoc();
                    
                    
                    
                    
                     //### Promedios horas
                        $horaspromedio1=0;
                        $horaspromedio2=0;
                        $horaspromedio3=0;
                        $horaspromedio4=0;
                        $horaspromedio5=0;
                        $horaspromedio6=0;
       
                    
                       $sqlpsql="SELECT  avg(TIMESTAMPDIFF(HOUR, prestamo.fecha_enviar_creditos,(select max(prestamo_gestion.hora) from prestamo_gestion where prestamo_gestion.prestamo_id=prestamo.id limit 1))) as salida
                              FROM prestamo 
                              where fecha_alta>='$fechadesde' and fecha_alta<='$fechahasta' 
                              and estatus<>1  and fecha_enviar_creditos is not null
                              $sqlbodega
                              $sqlcanal
                                   ";
                        $sqlppc = $conn -> query($sqlpsql);
                    if ($sqlppc -> num_rows > 0) {  $rowpp = $sqlppc -> fetch_assoc();
                        $horaspromedio1=round($rowpp["salida"],2) ;               
                        }                        unset ($sqlppc,$rowpp);
                        
                        
                        $sqlpsql=" SELECT  avg(TIMESTAMPDIFF(HOUR, prestamo.fecha_enviar_creditos,aprobado_gerencia_fecha)) as salida
                                  FROM prestamo
                                  where fecha_alta>='$fechadesde' and fecha_alta<='$fechahasta'  
                                  and   fecha_enviar_creditos is not null and aprobado_gerencia_fecha is not null
                                  $sqlbodega
                                  $sqlcanal
                                   ";
                        $sqlppc = $conn -> query($sqlpsql);
                    if ($sqlppc -> num_rows > 0) {  $rowpp = $sqlppc -> fetch_assoc();
                        $horaspromedio2=round($rowpp["salida"],2) ;               
                        }                        unset ($sqlppc,$rowpp);
                       
                        $sqlpsql="SELECT  avg(TIMESTAMPDIFF(HOUR, prestamo_gestion.hora,prestamo_gestion.hora_responde)) as salida
                                  FROM prestamo_gestion
                                  LEFT OUTER JOIN prestamo ON (prestamo.id=prestamo_gestion.prestamo_id)
                                  where  
                                   prestamo_gestion.hora_responde is not null
                                 and prestamo.fecha_alta>='$fechadesde' and prestamo.fecha_alta<='$fechahasta'
                                 and prestamo_gestion.fecha>='$fechadesde' and prestamo_gestion.fecha<='$fechahasta'  
                                 $sqlbodega
                                 $sqlcanal
                                   ";
                        $sqlppc = $conn -> query($sqlpsql);
                    if ($sqlppc -> num_rows > 0) {  $rowpp = $sqlppc -> fetch_assoc();
                        $horaspromedio3=round($rowpp["salida"],2) ;               
                        } 
                    
                        $sqlpsql="SELECT  avg(TIMESTAMPDIFF(HOUR, hora_responde,hora_confirma)) as salida
                                  FROM prestamo_gestion
                                  LEFT OUTER JOIN prestamo ON (prestamo.id=prestamo_gestion.prestamo_id)
                                  where  
                                  hora_responde is not null  and hora_responde is not null 
                                  and prestamo.fecha_alta>='$fechadesde' and prestamo.fecha_alta<='$fechahasta'
                                  and fecha>='$fechadesde' and fecha<='$fechahasta' 
                                  $sqlbodega   
                                  $sqlcanal
                                   ";
                        $sqlppc = $conn -> query($sqlpsql);
                    if ($sqlppc -> num_rows > 0) {  $rowpp = $sqlppc -> fetch_assoc();
                        $horaspromedio4=round($rowpp["salida"],2) ;               
                        }
                    
                    
                        $sqlpsql="SELECT  avg(TIMESTAMPDIFF(HOUR, fecha_enviar_creditos,fecha_recibe_creditos)) as salida
                                  FROM prestamo
                                  where fecha_alta>='$fechadesde' and fecha_alta<='$fechahasta'  
                                  and   fecha_enviar_creditos is not null and fecha_recibe_creditos is not null 
                                  $sqlbodega
                                  $sqlcanal
                                   ";
                        $sqlppc = $conn -> query($sqlpsql);
                    if ($sqlppc -> num_rows > 0) {  $rowpp = $sqlppc -> fetch_assoc();
                        $horaspromedio5=round($rowpp["salida"],2) ;               
                        }
                        
                        $sqlpsql="SELECT  avg(TIMESTAMPDIFF(HOUR, hora,hora_responde)) as salida
                                  FROM prestamo_gestion
                                  LEFT OUTER JOIN prestamo ON (prestamo.id=prestamo_gestion.prestamo_id)
                                  where  
                                  prestamo_gestion.campo_id=31 and hora_responde is not null
                                  and prestamo.fecha_alta>='$fechadesde' and prestamo.fecha_alta<='$fechahasta'
                                    and fecha>='$fechadesde' and fecha<='$fechahasta'  
                                    $sqlbodega
                                    $sqlcanal
                                   ";
                        $sqlppc = $conn -> query($sqlpsql);
                    if ($sqlppc -> num_rows > 0) {  $rowpp = $sqlppc -> fetch_assoc();
                        $horaspromedio6=round($rowpp["salida"],2) ;               
                        } 
                    
                    
                    //filtro de busqueda
                    
                      
                       foreach ($tiendas as &$vt) {
                          //  $vt;  
                        
                        } 
                       unset($vt); 

                
echo '
  <div class="row ">
    <p class="text-muted pull-right"><small>Mostrando estadisticas para el periodo: '.$reporte_fecha.'</small></p>
  </div>
  <div class="row">
    <div class="col-xs-6 col-md-2">'.crear_pie('ppAprobacion','Aprobadas','#026125',round(($row["aprobadas"]/$row["ingresadas"])*100,2)).'</div>
    <div class="col-xs-6 col-md-2">'.crear_pie('ppEntrega','Entregadas','#30a5ff',round(($row["entregadas"]/$row["ingresadas"])*100,2)).'</div>
    <div class="col-xs-6 col-md-2">'.crear_pie('pprechazados','Rechazadas','#f9243f',round(($row["rechazadas"]/$row["ingresadas"])*100,2)).'</div>
    <div class="col-xs-6 col-md-2"><br>'.'Resolucion General<div class="alert alert-info" role="alert">'.$horaspromedio1.' h</div></div>
    <div class="col-xs-6 col-md-2"><br>'.'Creditos Aprobados<div class="alert alert-success" role="alert">'.$horaspromedio2.' h</div></div>
    <div class="col-xs-6 col-md-2"><br>'.'Gestion Ventas<div class="alert alert-warning" role="alert">'.$horaspromedio3.' h</div></div>
  </div>';

//  echo '<div class="col-xs-6 col-md-2">'.crear_pie('pphgeneral','Resolucion General','#004d00',$horaspromedio1,'').'</div>';
//  echo '<div class="col-xs-6 col-md-2">'.crear_pie('pphaprobado','Creditos Aprobados','#000080',$horaspromedio2,'').'</div>';
//  echo '<div class="col-xs-6 col-md-2">'.crear_pie('pphventas','Gestion Ventas','#cc6600',$horaspromedio3,'').'</div>';

$primapromedio=($row["mprestamos_entregado"]/$row["entregadas"]);
$prestamopromedio=($row["mprima_entregado"]/$row["entregadas"]);
echo '
<div class="row"> 
  <div class="col-xs-12 col-md-5">
    <table class="table table-striped"    >
      <thead>
        <tr>
          <th class="text-left">Estadisticas Generales </th>
          <th class="text-right"> </th>
        </tr>
      </thead> 
      <tbody>
        <tr><td class="text-left">Solicitudes Ingresadas</td><td class="text-right">'.$row["ingresadas"].'</td></tr>
        <tr><td class="text-left">Solicitudes Aprobadas</td><td class="text-right">'.$row["aprobadas"].'</td></tr>
        <tr><td class="text-left">Solicitudes Entregadas</td><td class="text-right">'.$row["entregadas"].'</td></tr>
        <tr><td class="text-left">Solicitudes Rechazadas</td><td class="text-right">'.$row["rechazadas"].'</td></tr>
        <tr><td class="text-left">Solicitudes Pendientes</td><td class="text-right">'.$row["enproceso"].'</td></tr>
        <tr><td class="text-left">Prestamo promedio</td><td class="text-right"> Lps.'.number_format($primapromedio, 2).'</td></tr>
        <tr><td class="text-left">Prima promedio</td><td class="text-right"> Lps.'.number_format($prestamopromedio, 2).'</td></tr>
        <tr><td class="text-left">% Prima Promedio</td><td class="text-right">'.number_format( (($prestamopromedio/$primapromedio)*100), 2).'%</td></tr>
      </tbody>                   
    </table>
  </div>
  <div class="col-xs-12 col-md-1"></div><div class="col-xs-12 col-md-5">
    <table class="table table-striped"    >
      <thead>
        <tr>
          <th class="text-left">Tiempo Promedio</th>
          <th class="text-right">Horas</th>
        </tr>
      </thead> 
      <tbody> 
        <tr><td class="text-left">Resolucion General</td><td class="text-right">'.$horaspromedio1.'</td></tr>
        <tr><td class="text-left">Resolucion Creditos Aprobados</td><td class="text-right">'.$horaspromedio2.'</td></tr>
        <tr><td class="text-left">Respuesta Gestion Ventas</td><td class="text-right">'.$horaspromedio3.'</td></tr>
        <tr><td class="text-left">Respuesta Gestion Oficial Creditos</td><td class="text-right">'.$horaspromedio4.'</td></tr>
        <tr><td class="text-left">Recepcion Credito</td><td class="text-right">'.$horaspromedio5.'</td></tr>
        <tr><td class="text-left">Verificacion de campo</td><td class="text-right">'.$horaspromedio6.'</td></tr>
      </tbody>
    </table>
  </div>
</div>';

/*================================================
  Distribucion de Plazos Ingresados 
================================================*/
echo '
  <div class="row">
    <div class="col-sm-4 text-center">  
      <a class="btn btn-default btn-block" onclick="actualizarbox(\'plazosIngresados\',\'ajax_plazosingresados.php?sqlperiodo='.$sqlperiodo.'\')" ; return false;">Distribucion de Plazos Ingresados</a>
      <br>
      <div id="plazosIngresados" name="plazosIngresados"></div>
    </div>
  </div>
';

/*================================================
  Estadistica de rechazo
================================================*/
echo '
  <div class="row">
    <div class="col-sm-4 text-center">  
      <a href="#" class="btn  btn-default  btn-block"  onclick="actualizarbox(\'pagina\',\'creditos.php?a=0\') ; return false;">Estadistica de Rechazo</a>
      <br>
    </div>
  </div>
';

/*================================================
  Estadistica de rechazo
================================================*/
echo '
  <div class="row">
    <div class="col-sm-4 text-center">  
      <a href="#" class="btn  btn-default  btn-block"  onclick="actualizarbox(\'pagina\',\'creditos.php?a=0\') ; return false;">Estadistica de Rechazo</a>
      <br>
    </div>
  </div>
';
}

  if (tiene_permiso(19)) {     //  es creditos
    /*================================================
      Filtros
    ================================================*/
    echo '
      <div class="row">
        <div class="col-sm-4 text-center">  
          <a href="#" class="btn  btn-default  btn-block"  onclick="actualizarbox(\'pagina\',\'creditos.php?a=0\') ; return false;">Filtros</a>
          <br>
        </div>
      </div>
    ';
  }
}
?>
