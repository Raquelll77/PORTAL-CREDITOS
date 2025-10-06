<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");


require_once ('include/protect.php');
require_once ('include/framework.php');

$verror = "";

if (!tiene_permiso(14)) { echo mensaje("No tiene privilegios para accesar esta seccion","danger");exit;}
    

$conn = new mysqli(db_ip, db_user, db_pw, db_name);
if (mysqli_connect_errno()) {  echo mensaje("Error al Conectar a la Base de Datos [DB:101]","danger");exit;}
$conn->set_charset("utf8");



    if (!isset($_REQUEST['sub']) ) {
        ?>
    
    
<div id="buscar"> 
    <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">Ingrese la Serie de la Motocicleta</h4>
        </div>
        <div class="panel-body">
            
     <form class="form-horizontal">
       <?php 
       
        echo campo("sb","No. de Serie","text","",'class="form-control" autofocus');
    //  echo campo('sb','No. de Serie','select2ajax','','class="form-control select22"','get.php?a=ax&tp=1',""); 


      // echo campo( "Aceptar","Buscar","boton","","onclick=\"procesarrep(1) ; return false;\" ");
     //  echo campo( "Aceptar2","Mostrar Todos","boton","","onclick=\"procesarrep(2) ; return false;\" ");
       ?> 
     
<div class="form-group"><div class="col-sm-offset-3 col-sm-9">
        <button type="submit" class="btn btn-primary" onclick="procesarrep(1) ; return false;">Buscar</button>
        &nbsp;&nbsp;&nbsp;
        <button type="submit" class="btn btn-default" onclick="procesarrep(2) ; return false;">Mostrar Todos</button>
        &nbsp;
    <?php if($_SESSION['empresa']==1) { ?>      
        &nbsp;
        <button type="submit" class="btn btn-xs btn-info" onclick="procesarrep(5) ; return false;">Unicomer</button>
        &nbsp;
        <button type="submit" class="btn btn-xs btn-info" onclick="procesarrep(6) ; return false;">Molineros</button>
        &nbsp;
        <button type="submit" class="btn btn-xs btn-info" onclick="procesarrep(7) ; return false;">Orell</button>
        &nbsp;
        <button type="submit" class="btn btn-xs btn-info" onclick="procesarrep(8) ; return false;">M&M</button>
        &nbsp;
        <button type="submit" class="btn btn-xs btn-info" onclick="procesarrep(9) ; return false;">LadyLee</button>
     <?php  } ?> 
      
    </div></div>

    
    </form>         
            
        </div>
    </div>
    </div>

     
     
        
        <div id="reportev"> </div>
        
         <script type="text/javascript">
            function procesarrep(todos) {
                var busca=$("#sb").val();
                if (todos>1) {busca='';} 
                var url = "inventario.php?sub=1&sb="+ busca ; 
                 if (todos>2) {url=url+'&ppid='+todos;}            
                actualizarbox('reportev',url) ; 
            
            }   
            
       
     </script>
        
    <?php

    
    }
    
    
    
    
    
    if (isset($_REQUEST['sub']) ) 
    {
            
        if (isset($_REQUEST['sb']) ) 
        {
            
            $buscar="";
            if (isset($_REQUEST['sb'])) {$buscar=$conn->real_escape_string($_REQUEST['sb']);}
            
                        
        //  if ($buscar=="") { echo mensaje( "Debe ingresar el numero de serie","warning"); exit;}
            
            //******* SQL ************************************************************************************
            //SAP
            $sql=  "SELECT t3.Name [Modelo],T1.ItemName [Marca],T0.WhsCode [Cod.Almacen],T2.WhsName [NombreAlmacen],T0.SuppSerial [SerieChasis],T0.IntrSerial [SerieMotor],

CASE WHEN (T2.ZipCode)='CS' THEN 'Centro Sur' ELSE CASE WHEN (T2.ZipCode)='NO' THEN 'Nor Occidente' ELSE 'No Definida' END END [Zona_de_Venta],

(SELECT TOP 1 T6.BaseNum FROM SRI1 T6 WITH(NOLOCK) WHERE T0.SysSerial=T6.SysSerial AND T0.ItemCode=T6.ItemCode  ORDER BY T6.DocDate DESC)[LstTS],

(SELECT TOP 1 CONVERT(char(10), T6.DocDate,126) FROM SRI1 T6 WITH(NOLOCK) WHERE T0.SysSerial=T6.SysSerial AND T0.ItemCode=T6.ItemCode  ORDER BY T6.DocDate DESC)[FechaLstTS],

(SELECT TOP 1 DATEDIFF(DAY,T6.DocDate,GETDATE()) FROM SRI1 T6 WITH(NOLOCK) WHERE T0.SysSerial=T6.SysSerial AND T0.ItemCode=T6.ItemCode  ORDER BY T6.DocDate DESC)[DiasLstTS],

(SELECT TOP 1 DATEDIFF(DAY,T6.DocDate,GETDATE()) FROM SRI1 T6 WITH(NOLOCK) WHERE T0.SysSerial=T6.SysSerial AND T0.ItemCode=T6.ItemCode  ORDER BY T6.DocDate ASC)[DiasIn],

ISNULL((SELECT T.Name FROM [@CRESPONSABLEOWHS] T WHERE T2.U_Categorizacion=T.Code),'')[Responsable],

ISNULL((SELECT T.Name FROM [@CDEPARTAMENTO] T WHERE T2.U_Departamento=T.Code),'')[Depto],

ISNULL((SELECT T.Name FROM [@CCIUDAD] T WHERE T2.U_Ciudad=T.Code),'')[Ciudad],

ISNULL((SELECT T.Name FROM [@CRUTA] T WHERE T2.U_Ruta=T.Code),'')[Ruta]

,(select top 1 LotNumber from OSRN where MnfSerial=T0.SuppSerial)as anio
,(select top 1 [@SCOLOR].Name from [@SCOLOR] where [@SCOLOR].Code=T1.U_ACOLOR)as Color

FROM OSRI T0, OITM T1,OWHS t2,[@AMODELO] t3 WITH(NOLOCK)

WHERE T0.ItemCode=T1.ItemCode and T0.WhsCode=T2.WhsCode and t1.U_AMODELO=t3.Code AND T0.Status=0 AND T1.ItmsGrpCod=154
                      
              ";

          //  if (!tiene_permiso(12)) {
                $sql.=" and T0.[Status]  = '0'";
          //      }
            
            //DISTRIBUIDOR: select CardCode,CardName from OCRD
            //BODEGAS: select WhsCode,WhsName from OWHS
            
            $campo_dist="T2.U_CardCode";
            $campo_bodega="T0.WhsCode";
            
            if ($buscar<>"") { $sql.=  "  and ( SuppSerial LIKE '%$buscar%' )";         }   
    
            if (isset($entorno_desarrollo) ) {
                    $sql=  "SELECT TOP 1000 SerieChasis,SerieMotor,Marca,Modelo ,Color ,anio ,ModeloESpecifico,Almacen,NombreAlmacen
                    ,'' as LstTS
                    ,'' as FechaLstTS
                    ,'' as DiasLstTS
                    ,'' as DiasIn
                    ,'' as Responsable
                    ,'' as Depto
                    ,'' as Ciudad
                    ,'' as Ruta
                    ,'' as Zona_de_Venta
                    FROM serie 
                    WHERE  1=1  ";
                    
                   // if (!tiene_permiso(12)) {
                         $sql.=" and [Status]  = '0'";
                   // }
                    
                    $campo_dist="CodigoCliente";
                    $campo_bodega="Whscode";
                    
                    if ($buscar<>"") { $sql.=  "  and ( SerieChasis LIKE '%$buscar%' )";            }   
            }
        
            
         if (!tiene_permiso(29)) { //usuario taller puede ver todo    
         if (!tiene_permiso(31)) { //INVENTARIO: Ver inventario todas las tiendas 
             if (tiene_permiso(7)){
                 $texto=armar_sql($campo_dist,$_SESSION['grupo_distribuidores'],'or');
                 if ($texto<>"") {$sql.=" and $texto";} 
                 
                 $texto=armar_sql($campo_bodega,$_SESSION['grupo_bodegas'],'or');
                 if ($texto<>"") {$sql.=" and $texto";} 
            } else {
            if ($_SESSION['usuario_bodega']<>"") {$sql.=" and  $campo_bodega='".$_SESSION['usuario_bodega']."'";} // T0.WhsCode
            if ($_SESSION['usuario_distribuidor']<>"") {$sql.=" and $campo_dist='".$_SESSION['usuario_distribuidor']."'";} //T8.U_CardCode              
                
            }   
         }
         }
 
            
            if (isset($_REQUEST['ppid'])) {
                
               if ($_REQUEST['ppid']==3) {$sql.=" and $campo_dist='CL100728'";} 
               if ($_REQUEST['ppid']==4) {$sql.=" and $campo_dist='CL101396'";}
               if ($_REQUEST['ppid']==5) {$sql.=" and ($campo_dist='CL100728'  or $campo_dist='CL101396')";}
               if ($_REQUEST['ppid']==6) {$sql.=" and $campo_dist='CL000156'";}
               if ($_REQUEST['ppid']==7) {$sql.=" and $campo_dist='CL000088'";}
               if ($_REQUEST['ppid']==8) {$sql.=" and $campo_dist='CL000036'";}
               if ($_REQUEST['ppid']==9) {$sql.=" and $campo_dist='CL102215'";}
               
               
                
                
                
            } else {
                  
              //deshabilitado          
             // if (tiene_permiso(7)){
                 // $texto=armar_sql($campo_dist,$_SESSION['grupo_distribuidores'],'or');
                 // if ($texto<>"") {$sql.=" and $texto";} 
//                  
                 // $texto=armar_sql($campo_bodega,$_SESSION['grupo_bodegas'],'or');
                 // if ($texto<>"") {$sql.=" and $texto";} 
            // } else {
            // if ($_SESSION['usuario_bodega']<>"") {$sql.=" and  $campo_bodega='".$_SESSION['usuario_bodega']."'";} // T0.WhsCode
            // if ($_SESSION['usuario_distribuidor']<>"") {$sql.=" and $campo_dist='".$_SESSION['usuario_distribuidor']."'";} //T2.U_CardCode              
//                 
            // }  
                
                
                
            }
        
            
            // ****** Fin SQL ********************************************************************************
            
            $conn2 = sqlsrv_connect( db2_ip, array( "Database"=>db2_dbn, "UID"=>db2_usuario, "PWD"=>db2_clave , "CharacterSet" => "UTF-8") );   if( $conn2 === false ) { echo mensaje("Error al Conectar a la Base de Datos [DB:102]","danger");exit;}
            $stmt2 = sqlsrv_query( $conn2, $sql );  
            if( $stmt2 === false) {   die( print_r( sqlsrv_errors(), true) );}
    
            if (sqlsrv_has_rows($stmt2)===true) 
            {
    //<div class="table-responsive">
    //<table class="table table-striped"> -responsive se quito este apartado
            $reg=0;
            echo '<div class="row">
                    <div class="table">
                      <table class="display nowrap" id="tabla" width="100%" cellspacing="0">
                        <thead>
                          <tr>
                      
                            <th class="text-center">Marca</th>
                            <th class="text-center">Modelo</th>
                            <th class="text-center">Chasis</th>
                            <th class="text-center">Motor</th>
                            <th class="text-center">A&ntilde;o</th>
                            <th class="text-center">Color</th>
                            <th class="text-center">C. Alm.</th>
                            <th class="text-center">Almacen</th>
                            
                            
                            <th class="text-center">DInv</th>
                            <th class="text-center">Responsable</th>
                            <th class="text-center">Depto</th>
                            <th class="text-center">Ciudad</th>
                            <th class="text-center">Ruta</th>
                            <th class="text-center">Zona de Venta</th>
                            
                            <th class="text-center">DTS</th>
                            <th class="text-center">DTR</th>
                            <th class="text-center">FTS</th>

                          </tr>
                        </thead>
                        
                        <tfoot>
                          <tr>
                      
                            <th class="text-center">Marca</th>
                            <th class="text-center">Modelo</th>
                            <th class="text-center">Chasis</th>
                            <th class="text-center">Motor</th>
                            <th class="text-center">A&ntilde;o</th>
                            <th class="text-center">Color</th>
                            
                            <th class="text-center">C. Alm.</th>
                            <th class="text-center">Almacen</th>
                            
                            
                            
                            <th class="text-center">DInv</th>
                            <th class="text-center">Responsable</th>
                            <th class="text-center">Depto</th>
                            <th class="text-center">Ciudad</th>
                            <th class="text-center">Ruta</th>
                            <th class="text-center">Zona de Venta</th>
                            <th class="text-center">DTS</th>
                            <th class="text-center">DTR</th>
                            <th class="text-center">FTS</th>
                            

                          </tr>
                        </tfoot>
                        <tbody>';
  
            while( $row = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC) )
                {
     
                 
                echo "<tr>
                      
                      <td class='text-left'>".trim($row["Marca"])."</td>
                      <td class='text-left'>".trim($row["Modelo"])."</td>                 
                      <td class='text-left'>".trim($row["SerieChasis"])."</td>
                       <td class='text-left'>".trim($row["SerieMotor"])."</td>
                       <td class='text-left'>".trim($row["anio"])."</td>
                      <td class='text-left'>".trim($row["Color"])."</td>
                       
                       <td class='text-left '>".trim($row["Almacen"])."</td>
                       <td class='text-left '>".trim($row["NombreAlmacen"])."</td>
                       
                       
                      
                       <td class='text-left'>".trim($row["DiasIn"])."</td>
                       <td class='text-left'>".trim($row["Responsable"])."</td>
                       <td class='text-left'>".trim($row["Depto"])."</td>
                       <td class='text-left'>".trim($row["Ciudad"])."</td>
                       <td class='text-left'>".trim($row["Ruta"])."</td>
                       <td class='text-left'>".trim($row["Zona_de_Venta"])."</td>
                        <td class='text-left'>".trim($row["DiasLstTS"])."</td>
                       <td class='text-left'>".trim($row["LstTS"])."</td>
                       <td class='text-left'>".fechademysql(trim($row["FechaLstTS"])) ."</td>
                       
  
                      </tr>"    ;
                $reg++;
               
                
            }
            
              
            
            
             echo" </tbody>
                   
                  </table>
                </div>
              </div>
              ";
            
        //  <div class=\"row col-xs-12\">Registros <span class=\"badge\">$reg</span></div>
    
              echo crear_datatable('tabla','false',true,false) ;
             
        
            } else { echo mensaje( "No se encontraron registros","info"); exit;}
    
        } //else { echo mensaje( "Debe ingresar informacion en el campo para buscar","warning"); exit;}
        
        exit;
     
    
    }




?>