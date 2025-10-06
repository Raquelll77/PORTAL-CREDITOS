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
                
              $sql="SELECT 
                 ($sqlperiodo) as periodo
                    , count(id) as ingresadas 
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

                
    echo '<div class="row "> ';
    echo "<p class=\"text-muted pull-right\"><small>Mostrando estadisticas para el periodo: $reporte_fecha</small></p>";
      echo ' </div>';               
              
                    echo '<div class="row"> ';
              
                    echo '<div class="col-xs-6 col-md-2">'.crear_pie('ppAprobacion','Aprobadas','#026125',round(($row["aprobadas"]/$row["ingresadas"])*100,2)).'</div>';
                    echo '<div class="col-xs-6 col-md-2">'.crear_pie('ppEntrega','Entregadas','#30a5ff',round(($row["entregadas"]/$row["ingresadas"])*100,2)).'</div>';
                    echo '<div class="col-xs-6 col-md-2">'.crear_pie('pprechazados','Rechazadas','#f9243f',round(($row["rechazadas"]/$row["ingresadas"])*100,2)).'</div>';
                    
                 //  echo '<div class="col-xs-6 col-md-2">'.crear_pie('pphgeneral','Resolucion General','#004d00',$horaspromedio1,'').'</div>';
                 //  echo '<div class="col-xs-6 col-md-2">'.crear_pie('pphaprobado','Creditos Aprobados','#000080',$horaspromedio2,'').'</div>';
                 //  echo '<div class="col-xs-6 col-md-2">'.crear_pie('pphventas','Gestion Ventas','#cc6600',$horaspromedio3,'').'</div>';
                  
                   echo '<div class="col-xs-6 col-md-2"><br>'.'Resolucion General<div class="alert alert-info" role="alert">'.$horaspromedio1.' h</div></div>';
                   echo '<div class="col-xs-6 col-md-2"><br>'.'Creditos Aprobados<div class="alert alert-success" role="alert">'.$horaspromedio2.' h</div></div>';
                   echo '<div class="col-xs-6 col-md-2"><br>'.'Gestion Ventas<div class="alert alert-warning" role="alert">'.$horaspromedio3.' h</div></div>';
                  
                  
                  
                    echo ' </div>';
                    
                    
                
                     $primapromedio=($row["mprestamos_entregado"]/$row["entregadas"]);
                     $prestamopromedio=($row["mprima_entregado"]/$row["entregadas"]);
         
                      
                  
                   echo '<div class="row"> ';
                   
                     echo '<div class="col-xs-12 col-md-5">';
                     echo '                       
                      <table class="table table-striped"    >
                        <thead>
                          <tr>
                            <th class="text-left">Estadisticas Generales </th>
                            <th class="text-right"> </th>
                          </tr>
                        </thead> 
                         <tbody>                    
                        ';
                         echo "  <tr>
                              <td class='text-left'>Solicitudes Ingresadas</td>
                               <td class='text-right'>".$row["ingresadas"]."</td>
                     
                              </tr>";
                       echo "  <tr>
                              <td class='text-left'>Solicitudes Aprobadas</td>
                               <td class='text-right'>".$row["aprobadas"]."</td>
                     
                              </tr>";
                        echo "  <tr>
                              <td class='text-left'>Solicitudes Entregadas</td>
                               <td class='text-right'>".$row["entregadas"]."</td>
                     
                              </tr>";
                        echo "  <tr>
                              <td class='text-left'>Solicitudes Rechazadas</td>
                               <td class='text-right'>".$row["rechazadas"]."</td>
                     
                              </tr>";
                        echo "  <tr>
                              <td class='text-left'>Solicitudes Pendientes</td>
                               <td class='text-right'>".$row["enproceso"]."</td>
                     
                              </tr>";
                         echo "  <tr>
                              <td class='text-left'>Prestamo promedio</td>
                               <td class='text-right'> Lps.".number_format($primapromedio, 2)."</td>
                     
                              </tr>";
                              
                         echo "  <tr>
                              <td class='text-left'>Prima promedio</td>
                               <td class='text-right'> Lps.".number_format($prestamopromedio, 2)."</td>
                     
                              </tr>";
                          echo "  <tr>
                              <td class='text-left'>% Prima Promedio</td>
                               <td class='text-right'>".number_format( (($prestamopromedio/$primapromedio)*100), 2)."%"."</td>
                     
                              </tr>";
                                
                     echo" </tbody>                   
                                  </table>
                                     ";
                                  
                      echo ' </div>';
                       echo '<div class="col-xs-12 col-md-1"></div>';
                echo '<div class="col-xs-12 col-md-5">';
                  echo '                       
                      <table class="table table-striped"    >
                        <thead>
                          <tr>
                            <th class="text-left">Tiempo Promedio</th>
                            <th class="text-right">Horas</th>
                          </tr>
                        </thead> 
                         <tbody>                    
                        ';
                       echo "  <tr>
                              <td class='text-left'>Resolucion General</td>
                               <td class='text-right'>".$horaspromedio1."</td>
                     
                              </tr>";
                        echo "  <tr>
                              <td class='text-left'>Resolucion Creditos Aprobados</td>
                               <td class='text-right'>".$horaspromedio2."</td>
                     
                              </tr>";
                         echo "  <tr>
                              <td class='text-left'>Respuesta Gestion Ventas</td>
                               <td class='text-right'>".$horaspromedio3."</td>
                     
                              </tr>";
                         echo "  <tr>
                              <td class='text-left'>Respuesta Gestion Oficial Creditos</td>
                               <td class='text-right'>".$horaspromedio4."</td>
                     
                              </tr>";
                              
                         echo "  <tr>
                              <td class='text-left'>Recepcion Credito</td>
                               <td class='text-right'>".$horaspromedio5."</td>
                     
                              </tr>";
                          echo "  <tr>
                              <td class='text-left'>Verificacion de campo</td>
                               <td class='text-right'>".$horaspromedio6."</td>
                     
                              </tr>";
                                
                     echo" </tbody>                   
                                  </table>
                                     ";
                                     echo ' </div>';
                                     echo ' </div>';
                    
                    //###### plazos
                       echo '<BR><BR>
                       <h4>Distribucion de Plazos Ingresados </h4>
                      <table class="table table-striped"    >
                        <thead>
                           <tr>
                            <th > </th>
                            <th >   </th>
                            <th >   </th>
                            <th >   </th>
                             <th class="text-center tglobales">   </th>
                            <th  class="text-center tglobales" >Globales</th>
                             <th class="text-center tglobales">   </th> 

                          </tr>
                          <tr>
                            <th class="text-center">Plazo Solicitados</th>
                            <th class="text-center">CTD Ingresos</th>
                            <th class="text-center">CTD Aprobados</th>
                            <th class="text-center">CTD Rechazados</th>
                            <th class="text-center tglobales">Promedio Ingresos</th>
                            <th class="text-center tglobales">Promedio Aprobados</th>
                            <th class="text-center tglobales">Promedio Rechazados</th>

                          </tr>
                        </thead>
                        ';
                        
                  echo "     <tfoot>
                               <tr>
                              <td class='text-center'><strong>Total</strong></td>
                               <td class='text-center'>".$row["ingresadas"]."</td>
                                <td class='text-center'>".$row["aprobadas"]." (".round(($row["aprobadas"]/$row["ingresadas"])*100,2)."%)</td>
                                <td class='text-center'>".$row["rechazadas"]." (".round(($row["rechazadas"]/$row["ingresadas"])*100,2)."%)</td>
                               <td class='text-center'> </td>
                               <td class='text-center'> </td>
                               <td class='text-center'> </td>
                              </tr>
                              </tfoot>
                        <tbody>
                        ";
                          echo "<tr>
                              <td class='text-center'>12</td>
                               <td class='text-center'>".$row["ingreso12"]."</td>
                                <td class='text-center'>".$row["aprobadas12"]." (".round(($row["aprobadas12"]/$row["ingreso12"])*100,2)."%)</td>
                                <td class='text-center'>".$row["rechazadas12"]." (".round(($row["rechazadas12"]/$row["ingreso12"])*100,2)."%)</td>
                               <td class='text-center'>".round(($row["ingreso12"]/$row["ingresadas"])*100,2)."%</td>
                               <td class='text-center'>".round(($row["aprobadas12"]/$row["aprobadas"])*100,2)."%</td>
                               <td class='text-center'>".round(($row["rechazadas12"]/$row["rechazadas"])*100,2)."%</td>
                              </tr>
                              
                              <tr>
                              <td class='text-center'>18</td>
                               <td class='text-center'>".$row["ingreso18"]."</td>
                                <td class='text-center'>".$row["aprobadas18"]." (".round(($row["aprobadas18"]/$row["ingreso18"])*100,2)."%)</td>
                                <td class='text-center'>".$row["rechazadas18"]." (".round(($row["rechazadas18"]/$row["ingreso18"])*100,2)."%)</td>
                               <td class='text-center'>".round(($row["ingreso18"]/$row["ingresadas"])*100,2)."%</td>
                               <td class='text-center'>".round(($row["aprobadas18"]/$row["aprobadas"])*100,2)."%</td>
                               <td class='text-center'>".round(($row["rechazadas18"]/$row["rechazadas"])*100,2)."%</td>
                              </tr>
                              
                              <tr>
                              <td class='text-center'>24</td>
                               <td class='text-center'>".$row["ingreso24"]."</td>
                                <td class='text-center'>".$row["aprobadas24"]." (".round(($row["aprobadas24"]/$row["ingreso24"])*100,2)."%)</td>
                                <td class='text-center'>".$row["rechazadas24"]." (".round(($row["rechazadas24"]/$row["ingreso24"])*100,2)."%)</td>
                               <td class='text-center'>".round(($row["ingreso24"]/$row["ingresadas"])*100,2)."%</td>
                               <td class='text-center'>".round(($row["aprobadas24"]/$row["aprobadas"])*100,2)."%</td>
                               <td class='text-center'>".round(($row["rechazadas24"]/$row["rechazadas"])*100,2)."%</td>
                              </tr>
                              
                              <tr>
                              <td class='text-center'>30</td>
                               <td class='text-center'>".$row["ingreso30"]."</td>
                                <td class='text-center'>".$row["aprobadas30"]." (".round(($row["aprobadas30"]/$row["ingreso30"])*100,2)."%)</td>
                                <td class='text-center'>".$row["rechazadas30"]." (".round(($row["rechazadas30"]/$row["ingreso30"])*100,2)."%)</td>
                               <td class='text-center'>".round(($row["ingreso30"]/$row["ingresadas"])*100,2)."%</td>
                               <td class='text-center'>".round(($row["aprobadas30"]/$row["aprobadas"])*100,2)."%</td>
                               <td class='text-center'>".round(($row["rechazadas30"]/$row["rechazadas"])*100,2)."%</td>
                              </tr>
                              
                              <tr>
                              <td class='text-center'>36</td>
                               <td class='text-center'>".$row["ingreso36"]."</td>
                                <td class='text-center'>".$row["aprobadas36"]." (".round(($row["aprobadas36"]/$row["ingreso36"])*100,2)."%)</td>
                                <td class='text-center'>".$row["rechazadas36"]." (".round(($row["rechazadas36"]/$row["ingreso36"])*100,2)."%)</td>
                               <td class='text-center'>".round(($row["ingreso36"]/$row["ingresadas"])*100,2)."%</td>
                               <td class='text-center'>".round(($row["aprobadas36"]/$row["aprobadas"])*100,2)."%</td>
                               <td class='text-center'>".round(($row["rechazadas36"]/$row["rechazadas"])*100,2)."%</td>
                              </tr>
                              
                               "    ;
                      
                      
                        
                         echo" </tbody>                   
                                  </table>
                                    <br>  ";
                                    
                        //##### Grafica
                   echo '     
                       <div class="row">
                        <div class="col-lg-12">
                            <div class="panel panel-default">
                                <div class="panel-heading">Distribucion de Plazos Ingresados</div>
                                <div class="panel-body">
                                    <div class="canvas-wrapper">
                                        <canvas class="main-chart" id="line-chart" height="200" width="600"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!--/.row-->   
                    
                    
                    
                    <script>
                    
                    var randomScalingFactor = function(){ return Math.round(Math.random()*1000)};
    
                    var lineChartData = {
                            labels : ["","12 Meses","18 Meses","24 Meses","30 Meses","36 Meses",""],
                            datasets : [
                                {
                                    label: "Ingresados",
                                    fillColor : "rgba(48,164,255,0.2)",
                                    strokeColor : "rgba(48,164,255,1)",
                                    pointColor : "rgba(48,164,255,1)",
                                    pointStrokeColor : "#fff",
                                    pointHighlightFill : "#fff",
                                    pointHighlightStroke : "rgba(48,164,255,1)",
                                    data : [0,'.$row["ingreso12"].','.$row["ingreso18"].','.$row["ingreso24"].','.$row["ingreso30"].','.$row["ingreso36"].',0]
                                },
                                {  
                                    label: "Aprobados",
                                    fillColor : "rgba(0, 143, 55, 0.2)",
                                    strokeColor : "rgba(0, 143, 55, 1)",
                                    pointColor : "rgba(0, 143, 55, 1)",
                                    pointStrokeColor : "#fff",
                                    pointHighlightFill : "#fff",
                                    pointHighlightStroke : "rgba(0, 143, 55, 1)",
                                    data : [0,'.$row["aprobadas12"].','.$row["aprobadas18"].','.$row["aprobadas24"].','.$row["aprobadas30"].','.$row["aprobadas36"].',0]
                                },
                                {
                                    label: "Rechazados",
                                    fillColor : "rgba(255, 112, 102, 0.2)",
                                    strokeColor : "rgba(255, 112, 102, 1)",
                                    pointColor : "rgba(255, 112, 102, 1)",
                                    pointStrokeColor : "#fff",
                                    pointHighlightFill : "#fff",
                                    pointHighlightStroke : "rgba(255, 112, 102, 1)",
                                    data : [0,'.$row["rechazadas12"].','.$row["rechazadas18"].','.$row["rechazadas24"].','.$row["rechazadas30"].','.$row["rechazadas36"].',0]
                                }
                            ]
                
                        }     
        

                
                
         
                    var chart1 = document.getElementById("line-chart").getContext("2d");
                    window.myLine = new Chart(chart1).Line(lineChartData, {
                        responsive: true
                    });
                   
                    
           
                    
                    </script>                                  
                      ';
                                    
                                    
                       //### Rechazados
                      $extrasql=""; 
                      $extraencabezado="";
                      $extradetalle=""; 
                      $extrapie="";
                      $i=1;
                       foreach ($tiendas as &$vt) {
                            $extraencabezado.='<th class="text-center">'.$vt.'</th>';
                            $extraencabezado.='<th class="text-center">'.$vt.' %</th>';
                            $extrasql.=", sum(if(tabla.bodega='$vt',tpt,0)) as tienda".$i;
                            $extrasql.=", (select count(*) from prestamo where bodega='$vt' and estatus=3 and fecha_alta>='$fechadesde' and fecha_alta<='$fechahasta' ) as tiendatot".$i;
                            $i++;
                        } 
                       unset($vt);
                      
                        echo '<BR><BR>
                       <h4>Estadistica de Rechazo</h4>
                      <table class="table table-striped" >
                        <thead>
                          <tr>
                            <th class="text-center">Razon de Rechazo</th>
                            <th class="text-center">General</th>
                            <th class="text-center">General %</th>
                            '.$extraencabezado.' 
                          </tr>
                        </thead>
                        ';
                        
                
                       
                       $sqlrechazadas="SELECT prestamo_cierre.nombre , sum(tpt) as general  $extrasql  
                                    from prestamo_cierre
                                    left OUTER JOIN    (
                                    select cierre_razon, bodega, count(*) as tpt
                                    from prestamo
                                    where estatus=3
                                    and fecha_alta>='$fechadesde' and fecha_alta<='$fechahasta'
                                    $sqlbodega
                                    $sqlcanal
                                    group by cierre_razon, bodega
                                    ) as tabla ON (prestamo_cierre.id=tabla.cierre_razon)
                                    
                                    where prestamo_cierre.nombre like '%Rechaza%'
                                    group by prestamo_cierre.nombre ";
                                    
                       // echo $sqlrechazadas;exit;            
                        $lasrechazadas = $conn -> query($sqlrechazadas);
                    if ($lasrechazadas -> num_rows > 0) {
                        
                         while ($rowrech = $lasrechazadas -> fetch_assoc()){
                                echo " <tr>
                              <td class='text-left'>".$rowrech["nombre"]."</td>
                              <td class='text-center'>".$rowrech["general"]."</td>
                              <td class='text-center'>".round(($rowrech["general"]/$row["rechazadas"])*100,2)."%</td>
                              ";
                              $i=1;
                               foreach ($tiendas as &$vt) {
                                   if ($rowrech["tienda$i"]>0) {$promedio=round(($rowrech["tienda$i"]/$rowrech["tiendatot$i"])*100,2);} else {$promedio="0";}
                                   echo "<td class='text-center'>".$rowrech["tienda$i"]."</td>
                                          <td class='text-center'>".$promedio."%"."</td>
                                          ";
                                    $i++;
                                } 
                               unset($vt);
                               
                             echo "</tr>";
                             
                           
                         } 
                          
                        }    
                        
                       
                
                       
                        echo" </tbody>                   
                                  </table>
                                    <br>  ";
                                    
                                    
                                    
                                    
                     //### Horas Por etapa
                      $extrasql=""; 
                      $extraencabezado="";
                      $extradetalle=""; 
                      $extrapie="";
                      $i=1;
                      $totaleshoras=array();
                      $totaleshoras[0]=0;
                       // foreach ($tiendas as &$vt) {
                            // $extraencabezado.='<th class="text-right">'.$vt.'</th>';                      
                            // $extrasql.=", sum(if(bodega='$vt',horas,0)) as sumt".$i.", sum(if(bodega='$vt' and horas>0,1,0)) as ct".$i;
                            // $totaleshoras[$i]=0;
                            // $i++;
                        // } 
                       // unset($vt);
                      
                        echo '<BR><BR>
                       <h4>Estadistica de Tiempos Promedio por Etapa</h4>
                      <table class="table table-striped" >
                        <thead>
                          <tr>
                            <th class="text-center"></th>
                            <th class="text-right">SOLICITUD NUEVA</th>
                            <th class="text-right">VER. DOCTOS</th>
                            <th class="text-right">VER. BURO</th>
                            <th class="text-right">VER. TELEF.</th>
                            <th class="text-right">VER. LABORAL</th>
                            <th class="text-right">VER. CAMPO</th>
                            <th class="text-right">CALCULO FINANCIERO</th>
                            <th class="text-right">APROBACION</th>
                            <th class="text-right">IMPRESION LEGALES</th>
                            <th class="text-right">FIRMA</th>
                            <th class="text-right">CIERRE</th>
                            <th class="text-right">DOC. FISICA</th>
                             
                          </tr>
                        </thead>
                        ';
                        
                
                       
                       // $sqlrhorass="select etapa_id,prestamo_etapa.nombre, sum(horas) as tot_horas, avg(horas) as avg_horas , count(horas)  as cnd_horas
                                            // $extrasql  
                                            // from prestamo_etapa
                                            // left OUTER JOIN(
                                            // SELECT prestamo_gestion.bodega, prestamo_gestion.prestamo_id, prestamo_gestion.etapa_id
                                            // , TIMESTAMPDIFF(HOUR, min(prestamo_gestion.hora),if (max(prestamo_gestion.hora)>max(ifnull(prestamo_gestion.hora_confirma,0)),max(prestamo_gestion.hora),max(prestamo_gestion.hora_confirma))) as horas
//                                             
                                                // FROM prestamo_gestion 
                                                // where etapa_id >1 and fecha>='$fechadesde' and fecha<='$fechahasta'
//                                                     
                                                // $sqlbodega $sqlcanal
                                                // group by prestamo_gestion.prestamo_id, prestamo_gestion.etapa_id
                                                // order by prestamo_gestion.prestamo_id, prestamo_gestion.etapa_id
                                                // ) as tabla1 ON (prestamo_etapa.id=tabla1.etapa_id)
                                                // where etapa_id>1 and etapa_id is not null
                                                // group by etapa_id
                                        // ";
                        
                        
                        $sqlrhorass=" select bodega
                                ,avg(ifnull(TIMESTAMPDIFF(MINUTE,inicio,etapa1),0)) as e1 
                                ,avg(ifnull(TIMESTAMPDIFF(MINUTE,etapa1,etapa2),0)) as e2 
                                ,avg(ifnull(TIMESTAMPDIFF(MINUTE,etapa2,etapa3),0)) as e3 
                                ,avg(ifnull(TIMESTAMPDIFF(MINUTE,etapa3,etapa4),0)) as e4 
                                ,avg(ifnull(TIMESTAMPDIFF(MINUTE,etapa4,etapa5),0)) as e5 
                                ,avg(ifnull(TIMESTAMPDIFF(MINUTE,etapa5,etapa6),0)) as e6 
                                ,avg(ifnull(TIMESTAMPDIFF(MINUTE,etapa6,etapa7),0)) as e7 
                                ,avg(ifnull(TIMESTAMPDIFF(MINUTE,etapa7,etapa8),0)) as e8 
                                ,avg(ifnull(TIMESTAMPDIFF(MINUTE,etapa8,etapa9),0)) as e9 
                                ,avg(ifnull(TIMESTAMPDIFF(MINUTE,etapa9,etapa10),0)) as e10 
                                ,avg(ifnull(TIMESTAMPDIFF(MINUTE,etapa10,etapa11),0)) as e11 
                                ,avg(ifnull(TIMESTAMPDIFF(MINUTE,etapa11,etapa12),0)) as e12 
                                
                                From (
                                select prestamo.id ,prestamo.bodega
                                ,fecha_enviar_creditos as inicio
                                ,fecha_recibe_creditos as etapa1
                                ,(select min(prestamo_gestion.hora) from prestamo_gestion where prestamo_gestion.estatus_id=2 and prestamo_gestion.prestamo_id=prestamo.id and prestamo_gestion.etapa_id=2) as etapa2
                                ,(select min(prestamo_gestion.hora) from prestamo_gestion where prestamo_gestion.estatus_id=2 and prestamo_gestion.prestamo_id=prestamo.id and prestamo_gestion.etapa_id=3) as etapa3
                                ,(select min(prestamo_gestion.hora) from prestamo_gestion where prestamo_gestion.estatus_id=2 and prestamo_gestion.prestamo_id=prestamo.id and prestamo_gestion.etapa_id=4) as etapa4
                                ,(select min(prestamo_gestion.hora) from prestamo_gestion where prestamo_gestion.estatus_id=2 and prestamo_gestion.prestamo_id=prestamo.id and prestamo_gestion.etapa_id=5) as etapa5
                                ,(select min(prestamo_gestion.hora) from prestamo_gestion where prestamo_gestion.estatus_id=2 and prestamo_gestion.prestamo_id=prestamo.id and prestamo_gestion.etapa_id=6) as etapa6
                                ,(select min(prestamo_gestion.hora) from prestamo_gestion where prestamo_gestion.estatus_id=2 and prestamo_gestion.prestamo_id=prestamo.id and prestamo_gestion.etapa_id=7) as etapa7
                                ,(select min(prestamo_gestion.hora) from prestamo_gestion where prestamo_gestion.estatus_id=2 and prestamo_gestion.prestamo_id=prestamo.id and prestamo_gestion.etapa_id=8) as etapa8
                                ,(select min(prestamo_gestion.hora) from prestamo_gestion where prestamo_gestion.estatus_id=2 and prestamo_gestion.prestamo_id=prestamo.id and prestamo_gestion.etapa_id=9) as etapa9
                                ,(select min(prestamo_gestion.hora) from prestamo_gestion where prestamo_gestion.estatus_id=2 and prestamo_gestion.prestamo_id=prestamo.id and prestamo_gestion.etapa_id=10) as etapa10
                                ,(select min(prestamo_gestion.hora) from prestamo_gestion where prestamo_gestion.estatus_id=2 and prestamo_gestion.prestamo_id=prestamo.id and prestamo_gestion.etapa_id=11) as etapa11
                                ,(select min(prestamo_gestion.hora) from prestamo_gestion where prestamo_gestion.estatus_id=2 and prestamo_gestion.prestamo_id=prestamo.id and prestamo_gestion.etapa_id=12) as etapa12
                                from prestamo 
                                where fecha_alta>='$fechadesde' and fecha_alta<='$fechahasta' 
                                $sqlbodega
                                $sqlcanal
                                ) as tabla1
                                 
                                group by bodega 
                        ";   
                        
                                    
                        $lashorass = $conn -> query($sqlrhorass);
                    if ($lashorass -> num_rows > 0) {
                            $gc=0;
                           
                            $t1=0;$t2=0;$t3=0;$t4=0;$t5=0;$t6=0;$t7=0;$t8=0;$t9=0;$t10=0;$t11=0;$t12=0;
                            $MINXHORA=60; //$MINXHORA=1; PARA VER EN MINUTOD
                            
                         while ($rowhoras = $lashorass -> fetch_assoc()){
                                 
                            $e1=0;$e2=0;$e3=0;$e4=0;$e5=0;$e6=0;$e7=0;$e8=0;$e9=0;$e10=0;$e11=0;$e12=0;
                            if ($rowhoras["e1"]>0) { $e1=number_format($rowhoras["e1"]/$MINXHORA,2);}
                            if ($rowhoras["e2"]>0) { $e2=number_format($rowhoras["e2"]/$MINXHORA,2);}
                            if ($rowhoras["e3"]>0) { $e3=number_format($rowhoras["e3"]/$MINXHORA,2);}
                            if ($rowhoras["e4"]>0) { $e4=number_format($rowhoras["e4"]/$MINXHORA,2);}
                            if ($rowhoras["e5"]>0) { $e5=number_format($rowhoras["e5"]/$MINXHORA,2);}
                            if ($rowhoras["e6"]>0) { $e6=number_format($rowhoras["e6"]/$MINXHORA,2);}
                            if ($rowhoras["e7"]>0) {  $e7=number_format($rowhoras["e7"]/$MINXHORA,2);}
                            if ($rowhoras["e8"]>0) { $e8=number_format($rowhoras["e8"]/$MINXHORA,2);}
                            if ($rowhoras["e9"]>0) { $e9=number_format($rowhoras["e9"]/$MINXHORA,2);}
                            if ($rowhoras["e10"]>0) { $e10=number_format($rowhoras["e10"]/$MINXHORA,2);}
                            if ($rowhoras["e11"]>0) { $e11=number_format($rowhoras["e11"]/$MINXHORA,2);}
                            if ($rowhoras["e12"]>0) {  $e12=number_format($rowhoras["e12"]/$MINXHORA,2);}
                             
                             
                             
                                echo " <tr>
                              <td class='text-left'>".$rowhoras["bodega"]."</td>
                              <td class='text-right'>".$e1."</td>   
                              <td class='text-right'>".$e2."</td> 
                              <td class='text-right'>".$e3."</td> 
                              <td class='text-right'>".$e4."</td> 
                              <td class='text-right'>".$e5."</td> 
                              <td class='text-right'>".$e6."</td> 
                              <td class='text-right'>".$e7."</td> 
                              <td class='text-right'>".$e8."</td> 
                              <td class='text-right'>".$e9."</td> 
                              <td class='text-right'>".$e10."</td> 
                              <td class='text-right'>".$e11."</td> 
                              <td class='text-right'>".$e12."</td> ";  
                              
                              $gc++;
                              $t1+=$e1;
                              $t2+=$e2;
                              $t3+=$e3;
                              $t4+=$e4;
                              $t5+=$e5;
                              $t6+=$e6;
                              $t7+=$e7;
                              $t8+=$e8;
                              $t9+=$e9;
                              $t10+=$e10;
                              $t11+=$e11;
                              $t12+=$e12;
                              
                               
                             echo "</tr>";
                             
                             
                             
                           
                         } 
                          
                        }    
                        
                       
                  echo "     <tfoot>
                               <tr>
                              <td class='text-left'><strong>GLOBAL</strong></td>
                              
                   
                              <td class='text-right'><strong>".number_format($t1/$gc,2)."</strong></td>   
                              <td class='text-right'><strong>".number_format($t2/$gc,2)."</strong></td> 
                              <td class='text-right'><strong>".number_format($t3/$gc,2)."</strong></td> 
                              <td class='text-right'><strong>".number_format($t4/$gc,2)."</strong></td> 
                              <td class='text-right'><strong>".number_format($t5/$gc,2)."</strong></td> 
                              <td class='text-right'><strong>".number_format($t6/$gc,2)."</strong></td> 
                              <td class='text-right'><strong>".number_format($t7/$gc,2)."</strong></td> 
                              <td class='text-right'><strong>".number_format($t8/$gc,2)."</strong></td> 
                              <td class='text-right'><strong>".number_format($t9/$gc,2)."</strong></td> 
                              <td class='text-right'><strong>".number_format($t10/$gc,2)."</strong></td> 
                              <td class='text-right'><strong>".number_format($t11/$gc,2)."</strong></td> 
                              <td class='text-right'><strong>".number_format($t12/$gc,2)."</strong></td>  "; 
                                 
                                    
                                
                            
                      echo "       </tr>
                              </tfoot>
                       
                        ";
                       
                        echo" </tbody>                   
                                  </table>
                                    <br>  ";     
                                    
                                    
                                    
                      
                      
                               
                                    
                                    
                                    
            }



  
 if (tiene_permiso(19)) {     //  es creditos

    //TODO
     echo '<br><hr><div class="row"> ';
   echo '<div class="col-xs-12 col-md-12">';
   echo '<h4>Filtros</h4>';
   echo '<form id="formarptstat" class="form-horizontal" >';

   echo campo("fd","Fecha Desde:","date","",'class="form-control" ','','',3,3);
   echo campo("fh","Fecha Hasta:","date","",'class="form-control" ','','',3,3);
   echo campo("tp","Tipo",'select','<option value="1" selected>Reporte</option><option value="2">Comparativo Mensual</option><option value="3">Comparativo Anual</option>','class="form-control" ','','',3,3);
   echo campo("ca","Canal",'select','<option value="">Todos</option><option value="CD">Canal Directo</option><option value="CI">Canal Indirecto</option>','class="form-control" ','','',3,3);          
   echo campo('td','Tienda','select2ajax','','class="form-control select22"','creditos_stat.php?a=ax&tp=1',"",3,4); 
   
    echo " <a href=\"#\" onclick=\"actualizarbox('rptstat','creditos_stat.php?a=1&'+$('#formarptstat').serialize()) ; return false;\"   class=\"btn btn-primary \"> Actualizar</a>";
    
    echo " </form> "; 
    echo ' </div>';
      echo ' </div>';
     }




}

 
?>