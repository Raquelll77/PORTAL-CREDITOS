<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");


require_once ('include/protect.php');
require_once ('include/framework.php');

$verror = "";

if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } else   {$accion='' ;}

if (!tiene_permiso(15)) { echo mensaje("No tiene privilegios para accesar esta seccion","danger");exit;}
    

$conn = new mysqli(db_ip, db_user, db_pw, db_name);
if (mysqli_connect_errno()) {  echo mensaje("Error al Conectar a la Base de Datos [DB:101]","danger");exit;}
$conn->set_charset("utf8");


// TODO Gestiones pendientes detalle
if ($accion=="0b")  {
  if (isset($_REQUEST['cid'])) { $solicitud = $conn->real_escape_string($_REQUEST['cid']); } else   {echo mensaje( "No se encontraron registros","info"); exit;}  
  
  echo "<h4>GESTIONES PENDIENTES DE SOLICITUD No. ".$_REQUEST['num']."</h4><br>";
  
  
         //******* SQL ************************************************************************************
               
            $sql="  SELECT prestamo_gestion.id, prestamo_gestion.fecha, prestamo_gestion.hora, prestamo_gestion.prestamo_id,
                (CONCAT(
                   FLOOR(HOUR(TIMEDIFF(now(), prestamo_gestion.hora)) / 24), ' D, ',
                   MOD(HOUR(TIMEDIFF(now(), prestamo_gestion.hora)), 24), ' H, ',
                   MINUTE(TIMEDIFF(now(), prestamo_gestion.hora)), ' M')
                    ) as antiguedad,
                 prestamo_gestion.usuario, prestamo_gestion.etapa_id, prestamo_gestion.descripcion , prestamo_etapa.nombre as vetapa 
                 ,prestamo_gestion.seccion, prestamo_gestion.campo_id, prestamo_gestion.gestion_estado
                 ,prestamo.numero,prestamo.bodega_nombre , prestamo.usuario_alta
                 ,usuario.nombre as nombreusuario
                FROM prestamo_gestion LEFT OUTER JOIN prestamo_etapa ON (prestamo_etapa.id=prestamo_gestion.etapa_id) 
                LEFT OUTER JOIN prestamo ON (prestamo.id=prestamo_gestion.prestamo_id)
                LEFT OUTER JOIN usuario ON (prestamo.usuario_alta=usuario.usuario)
                    where 1=1
                   
                    and prestamo_gestion.prestamo_id=$solicitud
                    and gestion_estado is not null
                    and gestion_estado<>'Confirmado'
";
if (tiene_permiso(19)) {$sql.=" and gestion_estado='Creditos'";
} else {
         if (tiene_permiso(22)) {// jefe tienda
                $sql.="  and gestion_estado='Vendedor'";
         } else { //vendedor y cobrador
             $sql.=" and usuario_dirigido='".$_SESSION['usuario']."' and gestion_estado='Vendedor'";}
         }
        
    



            // ****** Fin SQL ********************************************************************************
 
             $result = $conn -> query($sql);
          
          

          if ($result -> num_rows > 0) {
               
                 
            echo '<div class="row">
                    <div class="table-responsive">
                      <table class=" " id="tabla" width="100%" cellspacing="0">
                        <thead>
                          <tr>
                           <th class="text-center"></th> 
                            <th class="text-center"># Gestion</th>
                            <th class="text-center">Antiguedad</th>
                            <th class="text-center">Tipo Gestion</th>
                  
                            <th class="text-center">Gestion Solicitada</th>
                            <th class="text-center">Usuario</th>
                            <th class="text-center">Vendedor</th>
                            <th class="text-center">Tienda</th>

                          </tr>
                        </thead>
                        
                        <tfoot>
                         <tr>
                 <th class="text-center"></th>
                           <th class="text-center"># Gestion</th>
                            <th class="text-center">Antiguedad</th>
                            <th class="text-center">Tipo Gestion</th>
               
                            <th class="text-center">Gestion Solicitada</th>
                            <th class="text-center">Usuario</th>
                            <th class="text-center">Vendedor</th>
                            <th class="text-center">Tienda</th>
                           
                          </tr>
                        </tfoot>
                        <tbody>';
              


                        while ($row = $result -> fetch_assoc()) {
                            //$row["usuario_alta"].' '
                           echo "<tr >

     <td class='text-center'><a  href=\"#\" class=\"btn btn-default btn-sm\" onclick=\"actualizarbox('pagina','creditos_gestion.php?a=5b1&cst=".$row["etapa_id"]."&cpo=".$row["campo_id"]."&geid=".$row["id"]."&cid=".$row["prestamo_id"]."&num=".$row["numero"]."&gest=".$row["gestion_estado"]."') ; return false;\" ><span class=\"glyphicon glyphicon-folder-open\" aria-hidden=\"true\"></span></a></td>
                                <td class='text-center'>".$row["id"]."</td>
                              <td class='text-center'>".$row["antiguedad"]."</td>
                               <td class='text-left'>".$row["vetapa"]."</td> 
                           
                               <td class='text-left'>".$row["descripcion"]."</td>
                               <td class='text-center'>".$row["usuario"]."</td>
                               <td class='text-center'>".$row["nombreusuario"]."</td>
                                <td class='text-center'>".$row["bodega_nombre"]."</td>
                              
                      
                              </tr>"    ;
                      
                        }


                        
                 echo" </tbody>                   
                          </table>
                            </div>
                          </div> ";
              
                echo crear_datatable('tabla','false',true,false) ;

              
              
                 
                 
                } else { echo mensaje( "No se encontraron registros","info"); exit;}  
                
                
  
  // echo "<p><br><br><a href=\"#\" onclick=\"get_gestiones_regresar() ; return false;\"   class=\"btn btn-default\">REGRESAR</a></p>";
  echo "<p><br><br><a href=\"#\" onclick=\"actualizarbox('pagina','creditos.php') ; return false;\"   class=\"btn btn-default\">REGRESAR</a></p>";
  
  exit;  
}


// panel de tareas  // TODO Panel tares
 if ($accion<>"3" and $accion<>"42"){
      
      
  $sql="";   
  if (!tiene_permiso(19)) {// no es creditos
    if (tiene_permiso(22)) {// jefe tienda
    
     if (tiene_permiso(7)){
                     $sql.=" and ".armar_sql('prestamo.bodega',$_SESSION['grupo_bodegas'],'or');
                 
                    } else {
                    if ($_SESSION['usuario_bodega']<>"") {$sql.=" and  prestamo.bodega='".$_SESSION['usuario_bodega']."'";}                
                    }
      
    } else { // otros: vendedor y cobrador
        $sql.=" and usuario_alta='".$_SESSION['usuario']."'";}
 } 
 

 echo "<h4>PANEL DE TAREAS</h4><br>";
 
 echo '
  <div class="row">
      <div class="col-sm-3 text-center">  
        <a href="#" class="btn  btn-default  btn-block"  onclick="actualizarbox(\'pagina\',\'creditos.php?a=0\') ; return false;">Gestiones Pendientes <span class="badge">'.get_dato_sql('prestamo_gestion','count(*)'," where 1=1 ".get_gestiones_sql()  ).'</span></a>      
      <br></div>
      <div class="col-sm-3 text-center">  
         <a href="#" class="btn  btn-default  btn-block" onclick="actualizarbox(\'pagina\',\'creditos.php?a=1\') ; return false;">Solicitudes en Proceso <span class="badge">'.get_dato_sql('prestamo','count(*)'," where 1=1 and (prestamo.estatus=1 or (prestamo.estatus=2 and prestamo.cierre_documentos_recibidos is null)) $sql").'</span></a>     
      <br></div>
      <div class="col-sm-3 text-center">  
          <a href="#" class="btn   btn-default btn-block " onclick="actualizarbox(\'pagina\',\'creditos.php?a=25\') ; return false;">Solicitudes Finalizadas</a>    
      <br></div>
      <div class="col-sm-3 text-center">  
          <a href="#" class="btn   btn-default btn-block " onclick="actualizarbox(\'pagina\',\'creditos.php?a=2\') ; return false;">Consultar Solicitudes (Todas)</a>     
      <br></div>
 
 </div>
 
 
    ';
    
    
   echo '
  <div class="row">
    ';

   if ($accion=='') { echo "   
      <div class=\"col-sm-3 text-center\">    
       
        <a href=\"#\" onclick=\"actualizarbox('pagina','creditos.php?a=3') ; return false;\"   class=\"btn btn-default btn-block\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Nueva Solicitud</a>
        </div>
   "; }    
    
 
 echo ' <div class="col-sm-3 text-center">  
          <a href="#" class="btn  btn-default btn-block " onclick="actualizarbox(\'pagina\',\'creditos.php?a=4\') ; return false;">Biblioteca de Bur&oacute; </a>    
      <br></div>
 
 </div>
 
 
    ';  
  
    

 
     //dashboard
     if ($accion=='') {
         
         echo '<div class="row">
         <div id="rptstat">  </div>
          </div>
            <script>
            actualizarbox(\'rptstat\',\'creditos_stat.php\') ;
            </script>';
            
    }    
    
    
    // exit;
 
}
            
  
// TODO Gestiones pendientes
if ($accion=="0")  {
    
           //******* SQL ************************************************************************************
               
            $sql="SELECT count(prestamo.id) as gestiones ,prestamo.id,prestamo.numero,prestamo.bodega_nombre, fecha_alta, usuario_alta, nombres, apellidos, identidad, monto_prestamo, monto_financiar, monto_prima, plazo, tasa, estatus, etapa_proceso
    ,prestamo_estatus.nombre as vestatus
    ,prestamo_etapa.nombre as vprestamo_etapa
   ,usuario.nombre as nombreusuario
    FROM prestamo
    
    LEFT OUTER JOIN prestamo_estatus ON (prestamo_estatus.id=prestamo.estatus)
    LEFT OUTER JOIN prestamo_etapa ON (prestamo_etapa.id=prestamo.etapa_proceso) 
    LEFT OUTER JOIN prestamo_gestion  ON (prestamo.id=prestamo_gestion.prestamo_id)
    LEFT OUTER JOIN usuario ON (prestamo.usuario_alta=usuario.usuario)
                 
                    
                    where  1=1
                     
        ";
        
        

$sql.=get_gestiones_sql();

$sql.="  group by prestamo.id ";

            // ****** Fin SQL ********************************************************************************
 //echo $sql;exit;
             $result = $conn -> query($sql);


            echo "<h4>Gestiones Pendientes</h4><br>";
            
          

            if ($result -> num_rows > 0) {
               
              echo '<div id="tablagestiones" class="row" style="display: none;"> </div>';
                   
            echo '<div id="tablasolicitudes" class="row">
                    <div class="table-responsive">
                      <table class=" " id="tabla" width="100%" cellspacing="0">
                        <thead>
                          <tr>
                            <th ></th>
                            <th class="text-center">No.</th>
                            <th class="text-center">Gestiones</th>
                            <th class="text-center">Nombre</th>
                          <th class="text-center">Vendedor</th>
                            <th class="text-center">Tienda</th>
                  
                            <th class="text-center">Estatus</th>
                            <th class="text-center">Etapa de Proceso</th>
                            
                        
                           
                          </tr>
                        </thead>
                        
                        <tfoot>
                         <tr>
                      <th ></th>
                            <th >No.</th>
                            <th >Gestiones</th>
                            <th >Nombre</th>
                     <th >Vendedor</th>
                            <th >Tienda</th>
                      
                            <th >Estatus</th>
                            <th >Etapa de Proceso</th>
                            
                           
                          </tr>
                        </tfoot>
                        <tbody>';
              
     
                        while ($row = $result -> fetch_assoc()) {
                            //$row["usuario_alta"].' '
                           echo "<tr id=\"mnuchk".$row["id"]."\">

     <td class='text-center'>
     <a  href=\"#\" class=\"btn btn-default btn-sm\" onclick=\"get_gestiones(".$row["id"].",".$row["numero"]."); return false;\" >
     
     <span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span></a></td>
                            
                            <td class='text-center'>".$row["numero"]."</td>
                              <td class='text-center'><span class=\"badge\">".($row["gestiones"])."</span></td>
                              <td class='text-left'>".$row["nombres"]." ".$row["apellidos"]."</td> 
                            <td class='text-center'>".$row["nombreusuario"]."</td>
                               <td class='text-center'>".$row["bodega_nombre"]."</td>
                       
                               <td class='text-center'>".$row["vestatus"]."</td>
                               <td class='text-center'>".$row["vprestamo_etapa"]."</td>
                               
                              
                      
                              </tr>"    ;
                      
                        }


                        
                 echo" </tbody>                   
                          </table>
                            </div>
                          </div> ";
              
                echo crear_datatable('tabla','false',true,false) ;

              
              
                 
                 
                } else { echo mensaje( "No se encontraron registros","info"); exit;}  
    
    
    exit;     
}            


            
// TODO Solicitudes en proceso           
if ($accion=="1")  { 
            
            $buscar="";
          //  if (isset($_REQUEST['sb'])) {$buscar=$conn->real_escape_string($_REQUEST['sb']);}
            
                        
        //  if ($buscar=="") { echo mensaje( "Debe ingresar el numero de serie","warning"); exit;}
            
            //******* SQL ************************************************************************************
               
            $sql="SELECT prestamo.id,prestamo.numero,prestamo.bodega_nombre, fecha_alta, usuario_alta, nombres, apellidos, identidad, monto_prestamo, monto_financiar, monto_prima, plazo, tasa, estatus, etapa_proceso
    ,prestamo_estatus.nombre as vestatus
    ,prestamo_etapa.nombre as vprestamo_etapa
    ,usuario.nombre as nombreusuario
    FROM prestamo
    
    LEFT OUTER JOIN prestamo_estatus ON (prestamo_estatus.id=prestamo.estatus)
    LEFT OUTER JOIN prestamo_etapa ON (prestamo_etapa.id=prestamo.etapa_proceso)
    LEFT OUTER JOIN usuario ON (prestamo.usuario_alta=usuario.usuario)
     
                    WHERE  1=1 
                    and (prestamo.estatus=1 or (prestamo.estatus=2 and prestamo.cierre_documentos_recibidos is null))";
                    
 if (!tiene_permiso(19)) {// no es creditos
    if (tiene_permiso(22)) {// jefe tienda
    
     if (tiene_permiso(7)){
                     $sql.=" and ".armar_sql('prestamo.bodega',$_SESSION['grupo_bodegas'],'or');
                 
                    } else {
                    if ($_SESSION['usuario_bodega']<>"") {$sql.=" and  prestamo.bodega='".$_SESSION['usuario_bodega']."'";}                
                    }
      
    } else { // otros: vendedor y cobrador
        $sql.=" and usuario_alta='".$_SESSION['usuario']."'";}
 } 
                    
    
        
            if ($buscar<>"") { $sql.=  "  and ( nombres LIKE '%$buscar%' )";   
            }       
             
            // ****** Fin SQL ********************************************************************************
         
             $result = $conn -> query($sql);


            echo "<h4>Solicitudes en Proceso</h4><br>";
            
         
            if ($result -> num_rows > 0) {
               
              
                 //  <th class="text-center">Identidad</th>
                 //<div class="table-responsive">
                 
                $reg=0;
              
                
            echo '<div id="tablasolicitudes" class="row" >
                    <div class="table-responsive">
                      <table class="" id="tabla" width="100%" cellspacing="0">
                        <thead>
                          <tr>
        
                        <th ></th>
                            <th class="text-center">No.</th>
                            <th class="text-center">Fecha</th>
                            <th class="text-center">Nombre</th>
                          <th class="text-center">Vendedor</th>
                            <th class="text-center">Tienda</th>
                            <th class="text-center">V. Prestamo</th>
                            <th class="text-center">Prima</th>
                            <th class="text-center">T. Financiar</th>
                            <th class="text-center">Plazo</th>
                            <th class="text-center">Tasa</th>
                            <th class="text-center">Estatus</th>
                            <th class="text-center">Etapa de Proceso</th>
                            
                        
                           
                          </tr>
                        </thead>
                        
                        <tfoot>
                         <tr>
                      <th ></th>
                            <th >No.</th>
                            <th >Fecha</th>
                            <th >Nombre</th>
                      <th >Vendedor</th>
                            <th >Tienda</th>
                            <th >V. Prestamo</th>
                            <th >Prima</th>
                            <th >T. Financiar</th>
                            <th >Plazo</th>
                            <th >Tasa</th>
                            <th >Estatus</th>
                            <th >Etapa de Proceso</th>
                           
                           
                          </tr>
                        </tfoot>
                        <tbody>';
              
     
                        while ($row = $result -> fetch_assoc()) {
                             $colortxt="";
                            if ($row["estatus"]==2) {$colortxt=" bg-success"; }          
                            if ($row["estatus"]==3) {$colortxt=" bg-danger"; } 
                           echo "<tr>

    
                            <td class='text-center $colortxt'><a  href=\"#\" class=\"btn btn-default btn-sm\" onclick=\"actualizarbox('pagina','creditos_gestion.php?a=1&cid=".$row["id"]."') ; return false;\" ><span class=\"glyphicon glyphicon-folder-open\" aria-hidden=\"true\"></span></a></td>
                              <td class='text-center $colortxt'>".$row["numero"]."</td>
                              <td class='text-center $colortxt'>".fechademysql($row["fecha_alta"])."</td>
                              <td class='text-left $colortxt'>".$row["nombres"]." ".$row["apellidos"]."</td> 
                             <td class='text-center $colortxt'>".$row["nombreusuario"]."</td>
                               <td class='text-center $colortxt'>".$row["bodega_nombre"]."</td>
                               <td class='text-right $colortxt'>".$row["monto_prestamo"]."</td>
                               <td class='text-right $colortxt'>".$row["monto_prima"]."</td>
                               <td class='text-right $colortxt'>".$row["monto_financiar"]."</td>
                               <td class='text-center $colortxt'>".$row["plazo"]."</td>
                               <td class='text-center $colortxt'>".$row["tasa"]."</td>
                               <td class='text-center $colortxt'>".$row["vestatus"]."</td>
                               <td class='text-center $colortxt'>".$row["vprestamo_etapa"]."</td>
                              
                              
                      
                              </tr>"    ;
                        $reg++;
                        }

//  <td class='text-center'>".$row["identidad"]."</td>
                        
                 echo" </tbody>                   
                          </table>
                            </div>
                          </div> ";
              
                echo crear_datatable('tabla','false',true,true) ;

              
              // <div class=\"row col-xs-12\">Registros <span class=\"badge\">$reg</span></div>
                 
                 
                } else { echo mensaje( "No se encontraron registros","info"); exit;}  
    
    
    
    
    
 echo "<p><br><br><a href=\"#\" onclick=\"actualizarbox('pagina','creditos.php') ; return false;\"   class=\"btn btn-default\">REGRESAR</a></p>";
    
    exit;
        } 



// TODO Consultar Solicitudes
if ($accion=="2" or $accion=="25")  {
            
            $buscar="";
          //  if (isset($_REQUEST['sb'])) {$buscar=$conn->real_escape_string($_REQUEST['sb']);}
            
                        
        //  if ($buscar=="") { echo mensaje( "Debe ingresar el numero de serie","warning"); exit;}
            
            //******* SQL ************************************************************************************
            $sqlextra2="";
            $extra2=" Todas";
            if ($accion=="25")  {$sqlextra2=" and prestamo.cierre_documentos_recibidos='SI' "; $extra2=" Finalizadas";}
               
            $sql="SELECT prestamo.id,prestamo.numero,prestamo.bodega_nombre, fecha_alta, usuario_alta, nombres, apellidos, identidad, monto_prestamo, monto_financiar, monto_prima, plazo, tasa, estatus, etapa_proceso
    ,prestamo_estatus.nombre as vestatus
    ,prestamo_etapa.nombre as vprestamo_etapa
     ,prestamo.cierre_factura   ,prestamo.cierre_contrato
     ,usuario.nombre as nombreusuario
    FROM prestamo
    
    LEFT OUTER JOIN prestamo_estatus ON (prestamo_estatus.id=prestamo.estatus)
    LEFT OUTER JOIN prestamo_etapa ON (prestamo_etapa.id=prestamo.etapa_proceso) 
    LEFT OUTER JOIN usuario ON (prestamo.usuario_alta=usuario.usuario)
                    WHERE  1=1 
                    $sqlextra2";
         
                    
          if (!tiene_permiso(19)) {// no es creditos
    if (tiene_permiso(22)) {// jefe tienda
    
     if (tiene_permiso(7)){
                     $sql.=" and ".armar_sql('prestamo.bodega',$_SESSION['grupo_bodegas'],'or');
                 
                    } else {
                    if ($_SESSION['usuario_bodega']<>"") {$sql.=" and  prestamo.bodega='".$_SESSION['usuario_bodega']."'";}                
                    }
      
    } else { // otros: vendedor y cobrador
        $sql.=" and usuario_alta='".$_SESSION['usuario']."'";}
 } 
 
        
        
            if ($buscar<>"") { $sql.=  "  and ( nombres LIKE '%$buscar%' )";   
            }       
             
            // ****** Fin SQL ********************************************************************************
            
             $result = $conn -> query($sql);


            echo "<h4>Consultar Solicitudes$extra2</h4><br>";
            
         
            if ($result -> num_rows > 0) {
               
              
                 //  <th class="text-center">Identidad</th>
                 //<div class="table-responsive">
                 
                $reg=0;
            echo '<div class="row">
                    <div class="table-responsive">
                      <table class="" id="tabla" width="100%" cellspacing="0">
                        <thead>
                          <tr>
        
                        <th ></th>
                            <th class="text-center">No.</th>
                            <th class="text-center">Fecha</th>
                            <th class="text-center">Nombre</th>
                           <th class="text-center">Vendedor</th>
                            <th class="text-center">Tienda</th>
                            <th class="text-center">V. Prestamo</th>
                            <th class="text-center">Prima</th>
                            <th class="text-center">T. Financiar</th>
                            <th class="text-center">Plazo</th>
                            <th class="text-center">Tasa</th>
                            <th class="text-center"># Factura</th>
                            <th class="text-center"># Contrato</th>
                            <th class="text-center">Estatus</th>
                            <th class="text-center">Etapa de Proceso</th>
                           
                        
                           
                          </tr>
                        </thead>
                        
                        <tfoot>
                         <tr>
                      <th ></th>
                            <th >No.</th>
                            <th >Fecha</th>
                            <th >Nombre</th>
                         <th >Vendedor</th>
                            <th >Tienda</th>
                            <th >V. Prestamo</th>
                            <th >Prima</th>
                            <th >T. Financiar</th>
                            <th >Plazo</th>
                            <th >Tasa</th>
                            <th ># Factura</th>
                            <th ># Contrato</th>
                            <th >Estatus</th>
                            <th >Etapa de Proceso</th>
                        
                           
                          </tr>
                        </tfoot>
                        <tbody>';
              
     
                        while ($row = $result -> fetch_assoc()) {
                            $colortxt="";
                            if ($row["estatus"]==2) {$colortxt=" bg-success"; }          
                            if ($row["estatus"]==3) {$colortxt=" bg-danger"; }
                           echo "<tr >

    
                            <td class='text-center $colortxt'><a  href=\"#\" class=\"btn btn-default btn-sm\" onclick=\"actualizarbox('pagina','creditos_gestion.php?a=1&cid=".$row["id"]."') ; return false;\" ><span class=\"glyphicon glyphicon-folder-open\" aria-hidden=\"true\"></span></a></td>
                              <td class='text-center $colortxt'>".$row["numero"]."</td>
                              <td class='text-center $colortxt'>".fechademysql($row["fecha_alta"])."</td>
                              <td class='text-left $colortxt'>".$row["nombres"]." ".$row["apellidos"]."</td> 
                                <td class='text-center $colortxt'>".$row["nombreusuario"]."</td>
                               <td class='text-center $colortxt'>".$row["bodega_nombre"]."</td>
                               <td class='text-right $colortxt'>".$row["monto_prestamo"]."</td>
                               <td class='text-right  $colortxt'>".$row["monto_prima"]."</td>
                               <td class='text-right  $colortxt'>".$row["monto_financiar"]."</td>
                               <td class='text-center  $colortxt'>".$row["plazo"]."</td>
                               <td class='text-center  $colortxt'>".$row["tasa"]."</td>
                               <td class='text-center  $colortxt'>".$row["cierre_factura"]."</td>
                               <td class='text-center  $colortxt'>".$row["cierre_contrato"]."</td>
                               <td class='text-center  $colortxt'>".$row["vestatus"]."</td>
                               <td class='text-center  $colortxt'>".$row["vprestamo_etapa"]."</td>
                            
               
                      
                              </tr>"    ;
                        $reg++;
                        }

//  <td class='text-center'>".$row["identidad"]."</td>
                        
                 echo" </tbody>                   
                          </table>
                            </div>
                          </div> ";
              
                echo crear_datatable('tabla','false',true,true) ;

              
              // <div class=\"row col-xs-12\">Registros <span class=\"badge\">$reg</span></div>
                 
                 
                } else { echo mensaje( "No se encontraron registros","info"); exit;}  
    
    
    
    
    
 echo "<p><br><br><a href=\"#\" onclick=\"actualizarbox('pagina','creditos.php') ; return false;\"   class=\"btn btn-default\">REGRESAR</a></p>";
   
    
    exit;
        } 



// TODO NUEVA Solicitud
if ($accion=="3")  {
    
    if (isset($_REQUEST['s'])) { // crear Solicitud nueva
      //########## validar datos
        $verror="";
        $verror.=validar("Tienda",$_REQUEST['bodega'], "text", true,  null,  1,  null);
        $verror.=validar("Nombres",$_REQUEST['nombres'], "text", true,  null,  3,  null);
        $verror.=validar("Apellidos",$_REQUEST['apellidos'], "text", true,  null,  3,  null);
        $verror.=validar("Identidad",$_REQUEST['identidad'], "text", true,  null,  13,  null);
        
        $verror.=validar("Valor Motocicleta",$_REQUEST['monto_prestamo'], "text", true,  null,  3,  null);
     //   $verror.=validar("Prima",$_REQUEST['monto_prima'], "text", true,  null,  1,  null);
        $verror.=validar("Total Financiar",$_REQUEST['monto_financiar'], "text", true,  null,  3,  null);
        $verror.=validar("Plazo",$_REQUEST['plazo'], "text", true,  null,  1,  null);
     //   $verror.=validar("Tasa",$_REQUEST['tasa'], "text", true,  null,  2,  null);
     
     if ($_REQUEST["tipo_persona"]=="Persona Juridica" and $_REQUEST["nombre_empresa"]=="") {$verror.="Debe ingresar el nombre de la empresa"; }
        
        if ($verror==""){
                   
            $sqlcampos="";
            $sqlcampos.= "  nombres =".GetSQLValue($conn->real_escape_string($_REQUEST["nombres"]),"text");
            $sqlcampos.= " , apellidos =".GetSQLValue($conn->real_escape_string($_REQUEST["apellidos"]),"text");
            $sqlcampos.= " , identidad =".GetSQLValue($conn->real_escape_string($_REQUEST["identidad"]),"text");
           
            $sqlcampos.= " , nombre_empresa_rtn =".GetSQLValue($conn->real_escape_string($_REQUEST["nombre_empresa_rtn"]),"text");
            $sqlcampos.= " , nombre_empresa =".GetSQLValue($conn->real_escape_string($_REQUEST["nombre_empresa"]),"text");
            $sqlcampos.= " , tipo_persona =".GetSQLValue($conn->real_escape_string($_REQUEST["tipo_persona"]),"text");
            
            $sqlcampos.= " , monto_seguro =".GetSQLValue($conn->real_escape_string($_REQUEST["monto_seguro"]),"text");
            $sqlcampos.= " , monto_prestamo =".GetSQLValue($conn->real_escape_string($_REQUEST["monto_prestamo"]),"text");
            $sqlcampos.= " , monto_prima =".GetSQLValue($conn->real_escape_string($_REQUEST["monto_prima"]),"text");
            $sqlcampos.= " , monto_financiar =".GetSQLValue($conn->real_escape_string($_REQUEST["monto_financiar"]),"text");
            $sqlcampos.= " , plazo =".GetSQLValue($conn->real_escape_string($_REQUEST["plazo"]),"text");
          
            $sqlcampos.= " , tasa =".GetSQLValue(get_dato_sql('opciones','valor',' where id=1') ,"text");
            $sqlcampos.= " , cierre_interes_mensual =".GetSQLValue(get_dato_sql('opciones','valor',' where id=2') ,"text");
            
            $sqlcampos.= " , estatus =1";
            $sqlcampos.= " , etapa_proceso =1";
            $sqlcampos.= ",usuario_alta= '" .$_SESSION['usuario'] . "' ,fecha_alta=now()";
            
             if (tiene_permiso(26)) {$sqlcampos.= " , canal='CI'"; }
            
           $cod_bodega=$conn->real_escape_string($_REQUEST["bodega"]); 
           $bodega_nombre= get_dato_sql('bodega', 'nombre', " where codigo='$cod_bodega'");
            
       
            $sqlcampos.= ",bodega= '$cod_bodega' ,bodega_nombre='$bodega_nombre'";
            
            $numero_sol=get_dato_sql('prestamo', '(ifnull(max(numero),0)+1)', "  "); //where bodega='$cod_bodega'
            $sqlcampos.= " , numero =".$numero_sol;
            
            
            $sql="insert into prestamo set " . $sqlcampos;
            
            if ($conn->query($sql) === TRUE) {
            $insert_id = mysqli_insert_id($conn);
            // crear registro para aval
            $conn->query("insert into prestamo_aval set id=$insert_id ,numero=$numero_sol");
            $stud_arr[0]["pcode"] = 1;
            $stud_arr[0]["pmsg"] ='Los datos fueron guardados satisfactoriamente. El numero de Solicitud es: <strong>'.$numero_sol.'</strong>';    
            $stud_arr[0]["pcodid"] = $insert_id;
            
            
            } else {
            $stud_arr[0]["pcode"] = 0;
            $stud_arr[0]["pmsg"] ='Se produjo un error al guardar el registro DB101:<br>'.$conn->error;
            $stud_arr[0]["pcodid"] =0;
            }
        
            $conn->close();
            
            
        } else {
            //mostrar errores validacion
            $stud_arr[0]["pcode"] = 0;
            $stud_arr[0]["pmsg"] ='Error en los datos:</strong><br>'.$verror;
            $stud_arr[0]["pcodid"] =0;  
        }           
                
        
        echo salida_json($stud_arr);
        exit;  
    }
    
    echo "<h4>Nueva Solicitud</h4>";
        echo ' <div class="panel panel-default"> <div id="datosgenerales" class="panel-body"> <div class="row"> <div class="col-xs-12"> <form id="solform" class="form-horizontal">';
    
    $campo_bodega='codigo';
    $campo_dist='id_distribuidor';
    $sql="";
    if (tiene_permiso(7)){
                 $texto=armar_sql($campo_dist,$_SESSION['grupo_distribuidores'],'or');
                 if ($texto<>"") {$sql.=" and $texto";} 
                 
                 $texto=armar_sql($campo_bodega,$_SESSION['grupo_bodegas'],'or');
                 if ($texto<>"") {$sql.=" and $texto";} 
            } else {
            if ($_SESSION['usuario_bodega']<>"") {$sql.=" and  $campo_bodega='".$_SESSION['usuario_bodega']."'";}
            if ($_SESSION['usuario_distribuidor']<>"") {$sql.=" and $campo_dist='".$_SESSION['usuario_distribuidor']."'";}           
            // echo $sql;exit;   
            }
                    
    echo campo("bodega","Tienda",'select',valores_combobox_db('bodega','',"nombre as texto"," where 1=1 $sql order by nombre",'texto',false,'codigo'),'class="form-control" ','','',3,7);
    echo "<hr>";
    

     echo campo("tipo_persona","Tipo",'select',valores_combobox_texto('<option value="Persona Natural">Persona Natural</option><option value="Persona Juridica">Persona Juridica</option>',''),'class="form-control" onchange="$(\'#pjuridica\').toggle();"','','',3,5);
  echo '<div id="pjuridica" style="display: none">';
    echo campo("nombre_empresa","Nombre Empresa",'text','','class="form-control" ','','',3,7);
    echo campo("nombre_empresa_rtn","RTN Empresa",'text','','class="form-control" data-mask="99999999999999"','','',3,7);
    echo '<h4>Datos del representante legal:</h4>';
   echo '</div>';
     echo campo("nombres","Nombres",'text','','class="form-control" ','','',3,7);
    
     echo campo("apellidos","Apellidos",'text','','class="form-control" ','','',3,7);
     echo campo("identidad","Identidad",'text','','class="form-control" data-mask="9999-9999-99999?99" ','','',3,3);  
      echo "<hr>";
      echo campo("monto_prestamo","Valor Motocicleta",'text','','class="form-control"  onchange="$(\'#monto_financiar\').val(convertir_num($(\'#monto_prestamo\').val())+convertir_num($(\'#monto_seguro\').val())-convertir_num($(\'#monto_prima\').val())); " ','','',3,3);  
       echo campo("monto_seguro","Valor del Seguro",'text','','class="form-control"  onchange="$(\'#monto_financiar\').val(convertir_num($(\'#monto_prestamo\').val())+convertir_num($(\'#monto_seguro\').val())-convertir_num($(\'#monto_prima\').val())); " ','','',3,3);     
      echo campo("monto_prima","Prima",'text','','class="form-control"  onchange="$(\'#monto_financiar\').val(convertir_num($(\'#monto_prestamo\').val())+convertir_num($(\'#monto_seguro\').val())-convertir_num($(\'#monto_prima\').val())); "','','',3,3);
      
      echo campo("monto_financiar","Total Financiar",'text','','class="form-control"  readonly','','',3,3);
      echo campo("plazo","Plazo",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="6">6</option><option value="12">12</option><option value="18">18</option><option value="24">24</option><option value="30">30</option><option value="36">36</option>',''),'class="form-control" ','','',3,2);
    //  echo campo("tasa","Tasa",'text','10','class="form-control" data-mask="9?9"','','',3,2);
echo campo("tasa",'','hidden','','','','');
     
  
   
        ?>  <br>
        <div id="botones">
        <a id="btnguardar" href="#" class="btn btn-primary" onclick="procesarforma() ; return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar</a>  
         <a id="btnimprimir" href="#" style="display: none;" class="btn btn-info" onclick="actualizarbox('pagina','creditos_gestion.php?a=1&cid='+$('#ridg').val()) ;  return false;">Continuar</a>
        
          <input id="ridg" name=""ridg  type="hidden" value="" />
         
         <img id="cargando" style="display: none;" src="images/load.gif"/>
         
          &nbsp;&nbsp;&nbsp;<a id="btnregresar" href="#" onclick="actualizarbox('pagina','creditos.php') ; return false;"   class="btn btn-default">REGRESAR</a>
             <div class="row">
                <br>
         <div id="salida"> </div>
         </div>
         
       
        </div>  
        
    <script>
            function procesarforma() {
                $("#botones *").attr("disabled", "disabled");
                $("#solform :input").attr('readonly', true);
                $('#cargando').show();
                var myTable = '';
                
                
                
                var url = "creditos.php?a=3&s=1";
                $.getJSON(url, $("#solform").serialize(), function(json) {
                    
                    i = 1;
                    if (json.length > 0) {
                        if (json[0].pcode == 0) {
                                
                                $('#salida').empty().append('<div class="alert alert-warning" role="alert">'+json[0].pmsg+'</div>'); 
                                
                        }
                        if (json[0].pcode == 1) {
                            
                            if (json[0].pcodid != 0) {
                                $("#ridg").val(json[0].pcodid);
                                $('#btnimprimir').show();
                                
                                }
                                $("#datosgenerales *").attr("disabled", "disabled");
                                $('#btnguardar').hide();
                                $('#btnregresar').hide();
                                $('#salida').empty().append('<div class="alert alert-success" role="alert">'+json[0].pmsg+'</div>'); 
                                    
                        }
                    } else {
                            $('#salida').empty().append('<div class="alert alert-danger" role="alert">Se produjo un error en comunicacion JSON:101</div>'); 
                    }

                }).error(function() {
                        $('#salida').empty().append('<div class="alert alert-danger" role="alert">Se produjo un error en comunicacion JSON:102</div>');
                }).complete(function() {
                    
                    $('#cargando').hide();
                    $("#solform :input").attr('readonly', false);
                    $("#botones *").removeAttr("disabled");
                });

            }
            
    </script>

    <?php  
        
        
           echo " </form></div></div></div></div>";
    

 exit;
        }

// TODO Biblioteca de buro
if ($accion=="4")  {
             
            $buscar="";
          //  if (isset($_REQUEST['sb'])) {$buscar=$conn->real_escape_string($_REQUEST['sb']);}
            
                        
        //  if ($buscar=="") { echo mensaje( "Debe ingresar el numero de serie","warning"); exit;}
            
            //******* SQL ************************************************************************************
               
            $sql="SELECT id, fecha_alta, usuario_alta, bodega_nombre, nombres, apellidos, identidad,rtn, doc1
    FROM prestamo_biblioteca_buro
                    WHERE  1=1
                    order by nombres ,apellidos, fecha_alta";
        
        
            if ($buscar<>"") { $sql.=  "  and ( nombres LIKE '%$buscar%' )";   
            }       
             
            // ****** Fin SQL ********************************************************************************
            
             $result = $conn -> query($sql);

   echo "    <p><a href=\"#\" onclick=\"actualizarbox('pagina','creditos.php?a=42') ; return false;\"   class=\"btn btn-default\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Agregar Nuevo</a></p><br>";
 

            echo "<h4>Biblioteca de Bur&oacute;</h4><br>";
            
         
            if ($result -> num_rows > 0) {
               
              
                 //  <th class="text-center">Identidad</th>
                 //<div class="table-responsive">
                 
                $reg=0;
            echo '<div class="row">
                    <div class="table-responsive">
                      <table class="" id="tabla" width="100%" cellspacing="0">
                        <thead>
                          <tr>
        
                  
                            <th class="text-center">Nombre</th>       
                            <th class="text-center">Identidad</th>
                            <th class="text-center">RTN</th>
                            <th class="text-center">Documento</th>
                            <th class="text-center">Fecha</th>
                            <th class="text-center">Usuario</th>
                        
                           
                          </tr>
                        </thead>
                        
                        <tfoot>
                         <tr>
                  
                            <th class="text-center">Nombre</th>       
                            <th class="text-center">Identidad</th>
                             <th class="text-center">RTN</th>
                            <th class="text-center">Documento</th>
                            <th class="text-center">Fecha</th>
                            <th class="text-center">Usuario</th>
                           
                          </tr>
                        </tfoot>
                        <tbody>';
              
     
                        while ($row = $result -> fetch_assoc()) {
                           echo "<tr>

     
                              
                            
                              <td class='text-left'>".$row["nombres"]." ".$row["apellidos"]."</td> 
                              <td class='text-center'>".$row["identidad"]."</td>
                              <td class='text-center'>".$row["rtn"]."</td>
                               <td class='text-center'>".campo("docv".$row["id"],"",'uploadlink',$row["doc1"],'class="form-control" ','')."</td>                  
                              <td class='text-center'>".fechademysql($row["fecha_alta"])."</td>
                              <td class='text-center'>".$row["usuario_alta"]."</td>
                       
                              
                      
                              </tr>"    ;
                        $reg++;
                        }

//  <td class='text-center'>".$row["identidad"]."</td>
                        
                 echo" </tbody>                   
                          </table>
                            </div>
                          </div> ";
              
                echo crear_datatable('tabla','false',true,false) ;

              
              // <div class=\"row col-xs-12\">Registros <span class=\"badge\">$reg</span></div>
                 
                 
                } else { echo mensaje( "No se encontraron registros","info"); exit;}  
    
    
    
   
    
 echo "<p><br><br><a href=\"#\" onclick=\"actualizarbox('pagina','creditos.php') ; return false;\"   class=\"btn btn-default\">REGRESAR</a></p>";
    
    exit;   
    

        }   

// TODO NUEVA buro
if ($accion=="42")  {
    
    if (isset($_REQUEST['s'])) { // crear  nueva
      //########## validar datos
        $verror="";
        $verror.=validar("Nombres",$_REQUEST['nombres'], "text", true,  null,  3,  null);
        $verror.=validar("Apellidos",$_REQUEST['apellidos'], "text", true,  null,  3,  null);
       // $verror.=validar("Identidad",$_REQUEST['identidad'], "text", true,  null,  13,  null);
        $verror.=validar("Documento Buro",$_REQUEST['doc1'], "text", true,  null,  4,  null);
        
if ($_REQUEST['identidad']=='' and $_REQUEST['rtn']=='') {  $verror.='Debe ingresar Identidad o RTN';}

        if ($verror==""){
                   
            $sqlcampos="";
  
             $sqlcampos.= "  nombres =".GetSQLValue($conn->real_escape_string($_REQUEST["nombres"]),"text");
            $sqlcampos.= " , apellidos =".GetSQLValue($conn->real_escape_string($_REQUEST["apellidos"]),"text");
            $sqlcampos.= " , identidad =".GetSQLValue($conn->real_escape_string($_REQUEST["identidad"]),"text");
             $sqlcampos.= " , rtn =".GetSQLValue($conn->real_escape_string($_REQUEST["rtn"]),"text");
            
            if (isset($_REQUEST["doc1"])) {$sqlcampos.= " , doc1 =".GetSQLValue($conn->real_escape_string($_REQUEST["doc1"]),"text"); }
            
            $sqlcampos.= ",usuario_alta= '" .$_SESSION['usuario'] . "' ,fecha_alta=now()";
            
            
         //  $cod_bodega=$conn->real_escape_string($_REQUEST["bodega"]); 
         // $bodega_nombre= get_dato_sql('bodega', 'nombre', " where codigo='$cod_bodega'");
            
       
         //   $sqlcampos.= ",bodega= '$cod_bodega' ,bodega_nombre='$bodega_nombre'";
            
       
            $sql="insert into prestamo_biblioteca_buro set " . $sqlcampos;
            
            if ($conn->query($sql) === TRUE) {
            $insert_id = mysqli_insert_id($conn);
           
            $stud_arr[0]["pcode"] = 1;
            $stud_arr[0]["pmsg"] ='Los datos fueron guardados satisfactoriamente.';    
            $stud_arr[0]["pcodid"] = $insert_id;
            
            
            } else {
            $stud_arr[0]["pcode"] = 0;
            $stud_arr[0]["pmsg"] ='Se produjo un error al guardar el registro DB101:<br>'.$conn->error;
            $stud_arr[0]["pcodid"] =0;
            }
        
            $conn->close();
            
            
        } else {
            //mostrar errores validacion
            $stud_arr[0]["pcode"] = 0;
            $stud_arr[0]["pmsg"] ='Error en los datos:</strong><br>'.$verror;
            $stud_arr[0]["pcodid"] =0;  
        }           
                
        
        echo salida_json($stud_arr);
        exit;  
    }
    
    echo "<h4>Nuevo Documento de Buro</h4>";
        echo ' <div class="panel panel-default"> <div id="datosgenerales" class="panel-body"> <div class="row"> <div class="col-xs-12"> <form id="solform" class="form-horizontal">';
    
 
    $sql="";
                    

     echo campo("nombres","Nombres",'text','','class="form-control" ','','',3,7);
     echo campo("apellidos","Apellidos",'text','','class="form-control" ','','',3,7);
     echo campo("identidad","Identidad",'text','','class="form-control" data-mask="9999-9999-99999?99" ','','',3,3);  
      echo campo("rtn","RTN",'text','','class="form-control" data-mask="99999999999999" ','','',3,3); 

        echo campo("doc1","Documento Buro",'upload','','class="form-control" ');
     
  
   
        ?>  <br>
        <div id="botones">
        <a id="btnguardar" href="#" class="btn btn-primary" onclick="procesarforma() ; return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar</a>  
         
          <input id="ridg" name=""ridg  type="hidden" value="" />
         
         <img id="cargando" style="display: none;" src="images/load.gif"/>
         
          &nbsp;&nbsp;&nbsp;<a id="btnregresar" href="#" onclick="actualizarbox('pagina','creditos.php?a=4') ; return false;"   class="btn btn-default">REGRESAR</a>
             <div class="row">
                <br>
         <div id="salida"> </div>
         </div>
         
       
        </div>  
        
    <script>
            function procesarforma() {
                $("#botones *").attr("disabled", "disabled");
                $("#solform :input").attr('readonly', true);
                $('#cargando').show();
                var myTable = '';
                
                
                
                var url = "creditos.php?a=42&s=1";
                $.getJSON(url, $("#solform").serialize(), function(json) {
                    
                    i = 1;
                    if (json.length > 0) {
                        if (json[0].pcode == 0) {
                                
                                $('#salida').empty().append('<div class="alert alert-warning" role="alert">'+json[0].pmsg+'</div>'); 
                                
                        }
                        if (json[0].pcode == 1) {
                            
                            if (json[0].pcodid != 0) {
                                $("#ridg").val(json[0].pcodid);
                              
                                
                                }
                                $("#datosgenerales *").attr("disabled", "disabled");
                                $('#btnguardar').hide();
                              //  $('#btnregresar').hide();
                                $('#salida').empty().append('<div class="alert alert-success" role="alert">'+json[0].pmsg+'</div>'); 
                                    
                        }
                    } else {
                            $('#salida').empty().append('<div class="alert alert-danger" role="alert">Se produjo un error en comunicacion JSON:101</div>'); 
                    }

                }).error(function() {
                        $('#salida').empty().append('<div class="alert alert-danger" role="alert">Se produjo un error en comunicacion JSON:102</div>');
                }).complete(function() {
                    
                    $('#cargando').hide();
                    $("#solform :input").attr('readonly', false);
                    $("#botones *").removeAttr("disabled");
                });

            }
            
    </script>

    <?php  
        
        
           echo " </form></div></div></div></div>";
    

 exit;
        } 

?>