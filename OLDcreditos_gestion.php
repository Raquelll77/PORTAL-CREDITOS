<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; } else   {exit ;}


require_once ('include/protect.php');
require_once ('include/framework.php');

$forma=time();

$verror = "";

if (!tiene_permiso(15)) { echo mensaje("No tiene privilegios para accesar esta seccion","danger");exit;}
    

$conn = new mysqli(db_ip, db_user, db_pw, db_name);
if (mysqli_connect_errno()) {  echo mensaje("Error al Conectar a la Base de Datos [DB:101]","danger");exit;}
$conn->set_charset("utf8");


if (isset($_REQUEST['cid'])) { $solicitud_id=$conn->real_escape_string($_REQUEST['cid']) ;} else { echo mensaje("Debe seleccionar una solicitud","danger");exit;}
 


//******************************************************************************

if ($accion=="1") { //TODO Principal

  $solicitud_num=get_dato_sql('prestamo',"concat('<span class=\"label label-primary\">',numero, '</span>  - TIENDA: ',bodega_nombre)",' where id='.$solicitud_id); 
   echo "<h4>SOLICITUD No. $solicitud_num</h4><br>";
   
    ?>
<ul class="nav nav-tabs">
  <li role="presentation" ><a href="#a1" onclick="actualizarbox('a1','creditos_gestion.php?a=2&cid=<?php echo $solicitud_id; ?>') ; return false;"  data-toggle="tab">Datos Solicitud</a></li>
  <li role="presentation"><a href="#a2"  onclick="actualizarbox('a1','creditos_gestion.php?a=3&b=1&cid=<?php echo $solicitud_id; ?>') ; return false;" data-toggle="tab">Datos del Cliente</a></li>
  <li role="presentation"><a href="#a3"  onclick="actualizarbox('a1','creditos_gestion.php?a=4&cid=<?php echo $solicitud_id; ?>') ; return false;" data-toggle="tab">Documentos Adjuntos</a></li>
  <li role="presentation"><a href="#a4"  onclick="actualizarbox('a1','creditos_gestion.php?a=3&b=2&cid=<?php echo $solicitud_id; ?>') ; return false;" data-toggle="tab">Datos del Aval</a></li>
  <li role="presentation"><a href="#a5"  onclick="actualizarbox('a1','creditos_gestion.php?a=5&cid=<?php echo $solicitud_id; ?>') ; return false;" data-toggle="tab">Gestiones</a></li>
 <?php // <li role="presentation"><a href="#a6"  onclick="actualizarbox('a1','creditos_gestion.php?a=xx&cid=<?php echo $solicitud_id; ?>
   <?php //   ') ; return false;" data-toggle="tab">Plan de Pagos</a></li> ?>

  <li role="presentation"><a href="#a7"  onclick="actualizarbox('a1','creditos_gestion.php?a=6&cid=<?php echo $solicitud_id; ?>') ; return false;" data-toggle="tab">Historial Gestiones</a></li>
</ul>
<br>
<div class="tab-content" id="tabs">
    <div class="tab-pane" id="a1"></div>

</div>

<script>
    function activaTab(tab,url){
    actualizarbox(tab,url) ;
    $('.nav-tabs a[href="#' + tab + '"]').tab('show');

};
   
    <?php  $urladd=""; if (isset($_REQUEST['b'])) { ?>
       $urladd="&b=<?php echo real_escape_string($_REQUEST['b']) ?>";
    <?php } ?>
    activaTab('a1','creditos_gestion.php?a=2&cid=<?php echo $solicitud_id.$urladd; ?>');

</script>
    <br><p><br><br><a href="#" onclick="actualizarbox('pagina','creditos.php') ; return false;"   class="btn btn-default">REGRESAR</a></p>
  <?php  
  
  exit;
}

//******************************************************************************
            
if ($accion=="2") //TODO Datos Generales
{
         if (isset($_REQUEST['s2'])) { // guardar remitir a creditos
             //########## validar datos
            $verror="";
            
              if ($verror==""){
                   
      
            
            $sql="update prestamo set fecha_enviar_creditos=now() where id=$solicitud_id and fecha_enviar_creditos is null" ;
            
            if ($conn->query($sql) === TRUE) {
                    $sqlcampos="";
                    $sqlcampos.= "  prestamo_id =$solicitud_id";
                    $sqlcampos.= " , gestion_estado='Creditos' ";
                    $sqlcampos.= " , campo_id=0 ";
                    $sqlcampos.= " , etapa_id=1 ";
                    $sqlcampos.= " , estatus_id=1 ";
                    $sqlcampos.= " , descripcion='Nueva Solicitud' ";
                    $sqlcampos.= ",usuario= '" .$_SESSION['usuario'] . "',fecha=curdate() ,hora=now()";
                    $sqlcampos.= ",usuario_responde= '" .$_SESSION['usuario'] . "' ,hora_responde=now()";
                    
                    if (tiene_permiso(26)) {$sqlcampos.= " , canal='CI'"; }
                    
                    $sqlcampos.= " , bodega=(select prestamo.bodega from prestamo where prestamo.id=$solicitud_id limit 1) ";
        if (tiene_permiso(26)) {$sqlcampos.= " , canal='CI'"; }
                    $sql="insert into prestamo_gestion set " . $sqlcampos;
                    $conn->query($sql);
                    $gestion_id_new = mysqli_insert_id($conn);
                    enviar_notificacion_gestion($gestion_id_new,'','','Nueva Solicitud');
                    
                    echo '<div class="alert alert-success" role="alert">La solicitud fue remitida a creditos</div>'; 

            } else {  echo '<div class="alert alert-danger" role="alert">Se produjo un error al guardar el registro DB101:<br>'.$conn->error.'</div>';    }
        

        } else {
            //mostrar errores validacion
            echo '<div class="alert alert-warning" role="alert">Error en los datos:</strong><br>'.$verror.'</div>'; 
        }           
                
        
            exit;
        }
         
         
            
        if (isset($_REQUEST['s'])) { // guardar
             //########## validar datos
            $verror="";
            
            $verror.=validar("Valor Motocicleta",$_REQUEST['monto_prestamo'], "text", true,  null,  3,  null);
     //       $verror.=validar("Prima",$_REQUEST['monto_prima'], "text", true,  null,  1,  null);
            $verror.=validar("Total Financiar",$_REQUEST['monto_financiar'], "text", true,  null,  3,  null);
            $verror.=validar("Plazo",$_REQUEST['plazo'], "text", true,  null,  1,  null);
            $verror.=validar("Tasa",$_REQUEST['tasa'], "text", true,  null,  1,  null);
            
            
              if ($verror==""){
                   
            $sqlcampos="";
         
            $sqlcampos.= "  monto_prestamo =".GetSQLValue($conn->real_escape_string($_REQUEST["monto_prestamo"]),"text");
            $sqlcampos.= "  ,monto_seguro =".GetSQLValue($conn->real_escape_string($_REQUEST["monto_seguro"]),"text");
            $sqlcampos.= " , monto_prima =".GetSQLValue($conn->real_escape_string($_REQUEST["monto_prima"]),"text");
            $sqlcampos.= " , monto_financiar =".GetSQLValue($conn->real_escape_string($_REQUEST["monto_financiar"]),"text");
            $sqlcampos.= " , plazo =".GetSQLValue($conn->real_escape_string($_REQUEST["plazo"]),"text");
            $sqlcampos.= " , tasa =".GetSQLValue($conn->real_escape_string($_REQUEST["tasa"]),"text");
            
            $sqlcampos.= " , estatus=".GetSQLValue($conn->real_escape_string($_REQUEST["estatus"]),"int");
            $sqlcampos.= " , etapa_proceso =".GetSQLValue($conn->real_escape_string($_REQUEST["etapa_proceso"]),"int");
       
            
            $sql="update prestamo set $sqlcampos where id=$solicitud_id" ;
     
            if ($conn->query($sql) === TRUE) {
                    echo '<div class="alert alert-success" role="alert">Los datos fueron guardados</div>'; 

            } else {  echo '<div class="alert alert-danger" role="alert">Se produjo un error al guardar el registro DB101:<br>'.$conn->error.'</div>';    }
        

        } else {
            //mostrar errores validacion
            echo '<div class="alert alert-warning" role="alert">Error en los datos:</strong><br>'.$verror.'</div>'; 
        }           
                
        
            
            exit;
        }





            
            //******* SQL ************************************************************************************
               
            $sql="SELECT prestamo.id,prestamo.numero,prestamo.bodega,prestamo.bodega_nombre, fecha_alta, usuario_alta, nombres, apellidos, identidad, monto_prestamo, monto_financiar, monto_prima, plazo, tasa, estatus, etapa_proceso
    ,prestamo_estatus.nombre as vestatus
    ,prestamo_etapa.nombre as vprestamo_etapa
    ,prestamo.fecha_enviar_creditos  ,prestamo.fecha_recibe_creditos
    ,monto_seguro
    ,usuario.nombre as nombreusuario
    FROM prestamo
    
    LEFT OUTER JOIN prestamo_estatus ON (prestamo_estatus.id=prestamo.estatus)
    LEFT OUTER JOIN prestamo_etapa ON (prestamo_etapa.id=prestamo.etapa_proceso) 
    LEFT OUTER JOIN usuario ON (prestamo.usuario_alta=usuario.usuario)
    
                    WHERE  prestamo.id=$solicitud_id 
                    ";
        
        
            
 // echo $sql;exit;
             
            // ****** Fin SQL ********************************************************************************
            
             $result = $conn -> query($sql);


          
 
            if ($result -> num_rows > 0) {
               
     $row = $result -> fetch_assoc();
                
   if (es_nulo($row["fecha_enviar_creditos"])) {
       echo ' <div class="panel panel-default"> <div id="remitircreditos" class="panel-body"> <div class="row"><div class="col-xs-12"> <form id="forma'.$forma.'" class="form-horizontal">';
     
       echo 'Cuando termine de ingresar la solicitud, precione el boton para remitir la solicitud al departamento de Creditos.';
       echo ' &nbsp;&nbsp;<a id="Guardar'.$forma.'" href="#" class="btn btn-primary" onclick="procesar_datos_remitirgestion(\'creditos_gestion.php?a=2&s2=1&cid='.$solicitud_id.'\','.$forma.'); return false;"><span class="glyphicon glyphicon-transfer" aria-hidden="true"></span> Remitir a Creditos</a>';
  
       echo '<div id="respuesta'.$forma.'"> </div>';
        
       echo " </form></div></div></div></div>";
  
       $forma=$forma+10;
   }        
                
      echo ' <div class="panel panel-default"> <div id="datosgenerales" class="panel-body"> <div class="row"><div class="col-xs-12"> <form id="forma'.$forma.'" class="form-horizontal">';
     
                 
     echo campo("numero","No. Solicitud",'label',$row["numero"],'class="form-control" ','','',3,3);               
     echo campo("fecha_alta","Fecha",'label',fechademysql($row["fecha_alta"]),'class="form-control" ','','',3,3);
     echo campo("usuario_alta","Vendedor",'label',$row["nombreusuario"],'class="form-control" ','','',3,4); //$row["usuario_alta"]
     echo campo("nombres","Nombres",'label',$row["nombres"],'class="form-control" ','','',3,7);
     echo campo("apellidos","Apellidos",'label',$row["apellidos"],'class="form-control" ','','',3,7);
     echo campo("identidad","Identidad",'label',$row["identidad"],'class="form-control" ','','',3,3);  
    
     echo campo("monto_prestamo","Valor Motocicleta",'text',$row["monto_prestamo"],'class="form-control" onchange="$(\'#monto_financiar\').val(convertir_num($(\'#monto_prestamo\').val())+convertir_num($(\'#monto_seguro\').val())-convertir_num($(\'#monto_prima\').val())); " ','','',3,3);  
     echo campo("monto_seguro","Valor del Seguro",'text',$row["monto_seguro"],'class="form-control"  onchange="$(\'#monto_financiar\').val(convertir_num($(\'#monto_prestamo\').val())+convertir_num($(\'#monto_seguro\').val())-convertir_num($(\'#monto_prima\').val())); " ','','',3,3);     
     echo campo("monto_prima","Prima",'text',$row["monto_prima"],'class="form-control"  onchange="$(\'#monto_financiar\').val(convertir_num($(\'#monto_prestamo\').val())+convertir_num($(\'#monto_seguro\').val())-convertir_num($(\'#monto_prima\').val())); "','','',3,3);
      
      echo campo("monto_financiar","Total Financiar",'text',$row["monto_financiar"],'class="form-control"  readonly','','',3,3);
      echo campo("plazo","Plazo",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="6">6</option><option value="12">12</option><option value="18">18</option><option value="24">24</option><option value="30">30</option><option value="36">36</option>',$row["plazo"]),'class="form-control" ','','',3,2);
      echo campo("tasa","Tasa",'text',$row["tasa"],'class="form-control"  ','','',3,2);


echo campo("estatus","Estatus",'select',valores_combobox_db('prestamo_estatus',$row["estatus"],'nombre','','nombre') ,'class="form-control" ','','',3,4);
echo campo("etapa_proceso","Etapa Proceso",'select',valores_combobox_db('prestamo_etapa',$row["etapa_proceso"],'nombre','','nombre') ,'class="form-control" ','','',3,8);
    
    
  if (tiene_permiso(19)) {
        echo '<br><a id="Guardar'.$forma.'" href="#" class="btn btn-primary" onclick="procesar_datos(\'creditos_gestion.php?a=2&s=1&cid='.$solicitud_id.'\','.$forma.'); return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar</a>';
  }
    echo '<div id="respuesta'.$forma.'"> </div>';
        
   echo " </form></div></div></div></div>";
                 
         
     
                    
 
                 
                } else { echo mensaje( "No se encontraron registros","info"); exit;}  
    
    
              exit;
 } 
 
 if ($accion=="3") //TODO Datos Cliente o aval
        {
            $tabla="prestamo";
            $b=1;
            if (isset($_REQUEST['b'])) { if($_REQUEST['b']=='2'){ $tabla="prestamo_aval"; $b=2;} }
            
            
             if (isset($_REQUEST['s'])) { // guardar
             //########## validar datos
            $verror="";
            
            $verror.=validar("Nombres",$_REQUEST['nombres'], "text", true,  null,  3,  null);
            $verror.=validar("Apellidos",$_REQUEST['apellidos'], "text", true,  null,  3,  null);
            $verror.=validar("Identidad",$_REQUEST['identidad'], "text", true,  null,  13,  null);
            
            
              if ($verror==""){
                   
            $sqlcampos="";
                  
            $sqlcampos.= "  identidad =".GetSQLValue($conn->real_escape_string($_REQUEST["identidad"]),"text");
            $sqlcampos.= " , apellidos =".GetSQLValue($conn->real_escape_string($_REQUEST["apellidos"]),"text");
            $sqlcampos.= " , nombres =".GetSQLValue($conn->real_escape_string($_REQUEST["nombres"]),"text");
            
             $sqlcampos.= " , nombre_empresa_rtn =".GetSQLValue($conn->real_escape_string($_REQUEST["nombre_empresa_rtn"]),"text");
            $sqlcampos.= " , nombre_empresa =".GetSQLValue($conn->real_escape_string($_REQUEST["nombre_empresa"]),"text");
            $sqlcampos.= " , tipo_persona =".GetSQLValue($conn->real_escape_string($_REQUEST["tipo_persona"]),"text");
         
            if ($_REQUEST["fecha_nacimiento"]<>"") $sqlcampos.= " , fecha_nacimiento =".GetSQLValue(mysqldate($conn->real_escape_string($_REQUEST["fecha_nacimiento"])),"text");
            if ($_REQUEST["empresa_fecha_ingreso"]<>"") $sqlcampos.= " , empresa_fecha_ingreso =".GetSQLValue(mysqldate($conn->real_escape_string($_REQUEST["empresa_fecha_ingreso"])),"text");
            $sqlcampos.= " , empresa_salario =".GetSQLValue($conn->real_escape_string($_REQUEST["empresa_salario"]),"double");

            $sqlcampos.= " , empresa_salario_otro =".GetSQLValue($conn->real_escape_string($_REQUEST["empresa_salario_otro"]),"double");

          
            $sqlcampos.= " , no_dependientes =".GetSQLValue($conn->real_escape_string($_REQUEST["no_dependientes"]),"int");
            
            
         //   $sqlcampos.= " , comentario =".GetSQLValue($conn->real_escape_string($_REQUEST["comentario"]),"text");
            $sqlcampos.= " , empresa_telefono =".GetSQLValue($conn->real_escape_string($_REQUEST["empresa_telefono"]),"text");
            $sqlcampos.= " , vecino_telefono2 =".GetSQLValue($conn->real_escape_string($_REQUEST["vecino_telefono2"]),"text");
         //   $sqlcampos.= " , requiere_aval =".GetSQLValue($conn->real_escape_string($_REQUEST["requiere_aval"]),"text");
            $sqlcampos.= " , tipo_vivienda =".GetSQLValue($conn->real_escape_string($_REQUEST["tipo_vivienda"]),"text");
            $sqlcampos.= " , antiguedad_vivienda =".GetSQLValue($conn->real_escape_string($_REQUEST["antiguedad_vivienda"]),"text");
        //    $sqlcampos.= " , requiere_verificacion_campo_laboral =".GetSQLValue($conn->real_escape_string($_REQUEST["requiere_verificacion_campo_laboral"]),"text");
            $sqlcampos.= " , empresa =".GetSQLValue($conn->real_escape_string($_REQUEST["empresa"]),"text");
            $sqlcampos.= " , empresa_puesto =".GetSQLValue($conn->real_escape_string($_REQUEST["empresa_puesto"]),"text");
            $sqlcampos.= " , empresa_direccion =".GetSQLValue($conn->real_escape_string($_REQUEST["empresa_direccion"]),"text");
            $sqlcampos.= " , empresa_salario_otro_tipo =".GetSQLValue($conn->real_escape_string($_REQUEST["empresa_salario_otro_tipo"]),"text");
            $sqlcampos.= " , empresa_tipo_empleo =".GetSQLValue($conn->real_escape_string($_REQUEST["empresa_tipo_empleo"]),"text");
            $sqlcampos.= " , empresa_tipo_condicion =".GetSQLValue($conn->real_escape_string($_REQUEST["empresa_tipo_condicion"]),"text");
            $sqlcampos.= " , empresa_telefono2 =".GetSQLValue($conn->real_escape_string($_REQUEST["empresa_telefono2"]),"text");
            $sqlcampos.= " , empresa_extension =".GetSQLValue($conn->real_escape_string($_REQUEST["empresa_extension"]),"text");
            $sqlcampos.= " , ref1_telefono_celular =".GetSQLValue($conn->real_escape_string($_REQUEST["ref1_telefono_celular"]),"text");
            $sqlcampos.= " , ref3_telefono_celular =".GetSQLValue($conn->real_escape_string($_REQUEST["ref3_telefono_celular"]),"text");
            $sqlcampos.= " , ref4_nombre =".GetSQLValue($conn->real_escape_string($_REQUEST["ref4_nombre"]),"text");
            $sqlcampos.= " , ref4_telefono_casa =".GetSQLValue($conn->real_escape_string($_REQUEST["ref4_telefono_casa"]),"text");
            $sqlcampos.= " , ref4_telefono_trabajo =".GetSQLValue($conn->real_escape_string($_REQUEST["ref4_telefono_trabajo"]),"text");
            $sqlcampos.= " , ref4_telefono_celular =".GetSQLValue($conn->real_escape_string($_REQUEST["ref4_telefono_celular"]),"text");
            $sqlcampos.= " , ref1_relacion =".GetSQLValue($conn->real_escape_string($_REQUEST["ref1_relacion"]),"text");
            $sqlcampos.= " , ref2_relacion =".GetSQLValue($conn->real_escape_string($_REQUEST["ref2_relacion"]),"text");
            $sqlcampos.= " , ref3_relacion =".GetSQLValue($conn->real_escape_string($_REQUEST["ref3_relacion"]),"text");
            $sqlcampos.= " , ref3_telefono_trabajo =".GetSQLValue($conn->real_escape_string($_REQUEST["ref3_telefono_trabajo"]),"text");
            $sqlcampos.= " , ref3_telefono_casa =".GetSQLValue($conn->real_escape_string($_REQUEST["ref3_telefono_casa"]),"text");
            $sqlcampos.= " , ref1_nombre =".GetSQLValue($conn->real_escape_string($_REQUEST["ref1_nombre"]),"text");
            $sqlcampos.= " , ref1_telefono_casa =".GetSQLValue($conn->real_escape_string($_REQUEST["ref1_telefono_casa"]),"text");
            $sqlcampos.= " , ref1_telefono_trabajo =".GetSQLValue($conn->real_escape_string($_REQUEST["ref1_telefono_trabajo"]),"text");
            $sqlcampos.= " , ref2_nombre =".GetSQLValue($conn->real_escape_string($_REQUEST["ref2_nombre"]),"text");
            $sqlcampos.= " , ref2_telefono_casa =".GetSQLValue($conn->real_escape_string($_REQUEST["ref2_telefono_casa"]),"text");
            $sqlcampos.= " , ref2_telefono_trabajo =".GetSQLValue($conn->real_escape_string($_REQUEST["ref2_telefono_trabajo"]),"text");
            $sqlcampos.= " , ref2_telefono_celular =".GetSQLValue($conn->real_escape_string($_REQUEST["ref2_telefono_celular"]),"text");
            $sqlcampos.= " , ref3_nombre =".GetSQLValue($conn->real_escape_string($_REQUEST["ref3_nombre"]),"text");
            $sqlcampos.= " , ref4_relacion =".GetSQLValue($conn->real_escape_string($_REQUEST["ref4_relacion"]),"text");
            $sqlcampos.= " , vecino_telefono =".GetSQLValue($conn->real_escape_string($_REQUEST["vecino_telefono"]),"text");
            $sqlcampos.= " , vecino =".GetSQLValue($conn->real_escape_string($_REQUEST["vecino"]),"text");
            $sqlcampos.= " , email =".GetSQLValue($conn->real_escape_string($_REQUEST["email"]),"text");
            $sqlcampos.= " , sexo =".GetSQLValue($conn->real_escape_string($_REQUEST["sexo"]),"text");
            $sqlcampos.= " , celular =".GetSQLValue($conn->real_escape_string($_REQUEST["celular"]),"text");
            $sqlcampos.= " , telefono3 =".GetSQLValue($conn->real_escape_string($_REQUEST["telefono3"]),"text");
            $sqlcampos.= " , telefono2 =".GetSQLValue($conn->real_escape_string($_REQUEST["telefono2"]),"text");
            $sqlcampos.= " , telefono =".GetSQLValue($conn->real_escape_string($_REQUEST["telefono"]),"text");
            $sqlcampos.= " , departamento =".GetSQLValue($conn->real_escape_string($_REQUEST["departamento"]),"text");
            $sqlcampos.= " , ciudad =".GetSQLValue($conn->real_escape_string($_REQUEST["ciudad"]),"text");
          //  $sqlcampos.= " , direccion_gps =".GetSQLValue($conn->real_escape_string($_REQUEST["direccion_gps"]),"text");
            $sqlcampos.= " , direccion_referencia =".GetSQLValue($conn->real_escape_string($_REQUEST["direccion_referencia"]),"text");
            $sqlcampos.= " , direccion =".GetSQLValue($conn->real_escape_string($_REQUEST["direccion"]),"text");
   
        //    $sqlcampos.= " , bodega_nombre =".GetSQLValue($conn->real_escape_string($_REQUEST["bodega_nombre"]),"text");
        //    $sqlcampos.= " , bodega =".GetSQLValue($conn->real_escape_string($_REQUEST["bodega"]),"text");

            $sqlcampos.= " , escolaridad =".GetSQLValue($conn->real_escape_string($_REQUEST["escolaridad"]),"text");
            $sqlcampos.= " , profesion =".GetSQLValue($conn->real_escape_string($_REQUEST["profesion"]),"text");
            $sqlcampos.= " , nombre_conyuge =".GetSQLValue($conn->real_escape_string($_REQUEST["nombre_conyuge"]),"text");
            $sqlcampos.= " , estado_civil =".GetSQLValue($conn->real_escape_string($_REQUEST["estado_civil"]),"text");
         
    
            
            $sql="update $tabla set $sqlcampos where id=$solicitud_id" ;
            
            if ($conn->query($sql) === TRUE) {
                    echo '<div class="alert alert-success" role="alert">Los datos fueron guardados</div>'; 

            } else {  echo '<div class="alert alert-danger" role="alert">Se produjo un error al guardar el registro DB101:<br>'.$conn->error.'</div>';    }
        

        } else {
            //mostrar errores validacion
            echo '<div class="alert alert-warning" role="alert">Error en los datos:</strong><br>'.$verror.'</div>'; 
        }           
                
        
            
            exit;
        }
        
            
            //******* SQL ************************************************************************************
               
            $sql="SELECT *
                    FROM $tabla
                    WHERE  id=$solicitud_id 
                    ";
        

            
 
             
            // ****** Fin SQL ********************************************************************************
            
             $result = $conn -> query($sql);


          
 
            if ($result -> num_rows > 0) {
                       
                    $forma=time();
                    
                    $row = $result -> fetch_assoc();
  if ($b==1) {             
    if (es_nulo($row["fecha_enviar_creditos"])) {
       echo ' <div class="panel panel-default"> <div id="remitircreditos" class="panel-body"> <div class="row"><div class="col-xs-12"> <form id="forma'.$forma.'" class="form-horizontal">';
     
       echo 'Cuando termine de ingresar la solicitud, precione el boton para remitir la solicitud al departamento de Creditos.';
       echo ' &nbsp;&nbsp;<a id="Guardar'.$forma.'" href="#" class="btn btn-primary" onclick="procesar_datos_remitirgestion(\'creditos_gestion.php?a=2&s2=1&cid='.$solicitud_id.'\','.$forma.'); return false;"><span class="glyphicon glyphicon-transfer" aria-hidden="true"></span> Remitir a Creditos</a>';
  
       echo '<div id="respuesta'.$forma.'"> </div>';
        
       echo " </form></div></div></div></div>";
  
       $forma=$forma+10;
   }  
  }                
               
     
                
      echo ' <div class="panel panel-default"> <div id="datosgenerales" class="panel-body"> <div class="row"> <div class="col-xs-12"> <form id="forma'.$forma.'" class="form-horizontal">';
      
     echo campo("tipo_persona","Tipo",'select',valores_combobox_texto('<option value="Persona Natural">Persona Natural</option><option value="Persona Juridica">Persona Juridica</option>',$row["tipo_persona"]),'class="form-control" onchange="$(\'#pjuridica\').toggle();"','','',3,5);
  if ($row["tipo_persona"]=="Persona Juridica"){$pjestilo='';} else {$pjestilo='style="display: none"';}
  echo '<div id="pjuridica" '.$pjestilo.'>';
    echo campo("nombre_empresa","Nombre Empresa",'text',$row["nombre_empresa"],'class="form-control" ','','',3,7);
    echo campo("nombre_empresa_rtn","RTN Empresa",'text',$row["nombre_empresa_rtn"],'class="form-control" data-mask="99999999999999"','','',3,7);
    echo '<h4>Datos del representante legal:</h4>';
   echo '</div>';  
     
       
     echo campo("nombres","Nombres",'text',$row["nombres"],'class="form-control" ','','',3,7);
     echo campo("apellidos","Apellidos",'text',$row["apellidos"],'class="form-control" ','','',3,7);
     echo campo("identidad","Identidad",'text',$row["identidad"],'class="form-control" data-mask="9999-9999-99999?99"','','',3,3);  
    
     echo campo("direccion","Direccion",'text',$row["direccion"],'class="form-control" ','','',3,7);
     echo campo("direccion_referencia","Direccion Referencia",'text',$row["direccion_referencia"],'class="form-control" ','','',3,5);
   //  echo campo("direccion_gps","Direccion GPS",'text',$row["direccion_gps"],'class="form-control" ','','',3,3);
     echo campo("ciudad","Ciudad",'text',$row["ciudad"],'class="form-control" ','','',3,3);
     echo campo("departamento","Departamento",'select',valores_combobox_texto('<option value="">Seleccione</option>
<option value="Atlantida">Atlantida</option>
<option value="Choluteca">Choluteca</option>
<option value="Colon">Colon</option>
<option value="Comayagua">Comayagua</option>
<option value="Copan">Copan</option>
<option value="Cortes">Cortes</option>
<option value="El Paraiso">El Paraiso</option>
<option value="Francisco Morazan">Francisco Morazan</option>
<option value="Gracias a Dios">Gracias a Dios</option>
<option value="Intibuca">Intibuca</option>
<option value="Islas de la Bahia">Islas de la Bahia</option>
<option value="La Paz">La Paz</option>
<option value="Lempira">Lempira</option>
<option value="Ocotepeque">Ocotepeque</option>
<option value="Olancho">Olancho</option>
<option value="Santa Barbara">Santa Barbara</option>
<option value="Valle">Valle</option>
<option value="Yoro">Yoro</option>',$row["departamento"]),'class="form-control" ','','',3,3); 
     echo campo("telefono","Telefono",'text',$row["telefono"],'class="form-control" data-mask="9999-9999"','','',3,3); 
     echo campo("telefono2","Telefono 2",'text',$row["telefono2"],'class="form-control" data-mask="9999-9999"','','',3,3); 
     echo campo("celular","Celular",'text',$row["celular"],'class="form-control" data-mask="9999-9999"','','',3,3); 
     echo campo("telefono3","Celular 2",'text',$row["telefono3"],'class="form-control" data-mask="9999-9999"','','',3,3); 
     
     
     echo campo("email","Email",'text',$row["email"],'class="form-control" ','','',3,4);
    echo campo("sexo","Sexo",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="MASCULINO">MASCULINO</option><option value="FEMENINO">FEMENINO</option>',$row["sexo"]),'class="form-control" ','','',3,3);
       
     echo campo("profesion","Profesion",'text',$row["profesion"],'class="form-control" ','','',3,5);
     echo campo("fecha_nacimiento","Fecha Nacimiento",'date',fechademysql($row["fecha_nacimiento"]),'class="form-control" ','','',3,3);
     echo campo("escolaridad","Escolaridad",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="CICLO BASICO">CICLO BASICO</option><option value="SECUNDARIA">SECUNDARIA</option><option value="UNIVERSIDAD">UNIVERSIDAD</option>',$row["escolaridad"]),'class="form-control" ','','',3,4);

 echo campo("estado_civil","Estado Civil",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="SOLTERO">SOLTERO</option><option value="CASADO">CASADO</option><option value="DIVORCIADO">DIVORCIADO</option><option value="VIUDO">VIUDO</option><option value="UNION LIBRE">UNION LIBRE</option>',$row["estado_civil"]),'class="form-control" ','','',3,3);
    echo campo("nombre_conyuge","Nombre Conyuge",'text',$row["nombre_conyuge"],'class="form-control" ','','',3,5);

     echo campo("no_dependientes","No. Dependientes",'text',$row["no_dependientes"],'class="form-control" data-mask="9?9"','','',3,2);
     echo campo("vecino","Pariente / Vecino",'text',$row["vecino"],'class="form-control" ','','',3,4);
     echo campo("vecino_telefono","Vecino Telefono",'text',$row["vecino_telefono"],'class="form-control" data-mask="9999-9999"','','',3,4);
     echo campo("vecino_telefono2","Vecino Telefono 2",'text',$row["vecino_telefono2"],'class="form-control" data-mask="9999-9999"','','',3,4);
     
     echo campo("tipo_vivienda","Tipo Vivienda",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="ALQUILA">ALQUILA</option><option value="PROPIA">PROPIA</option><option value="HIPOTECADA">HIPOTECADA</option><option value="FAMILIAR">FAMILIAR</option><option value="CEDIDA POR EMPRESA">CEDIDA POR EMPRESA</option>',$row["tipo_vivienda"]),'class="form-control" ','','',3,3);
     echo campo("antiguedad_vivienda","Tiempo de residir",'text',$row["antiguedad_vivienda"],'class="form-control" ','','',3,2);
    
    echo "<hr>";
    echo"<br><h4>DATOS LABORALES</h4><br>";
    
    echo campo("empresa","Empresa donde Labora",'text',$row["empresa"],'class="form-control" ','','',3,7);
    echo campo("empresa_direccion","Direccion",'text',$row["empresa_direccion"],'class="form-control" ','','',3,9);
    echo campo("empresa_tipo_empleo","Condicion Laboral",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="ASALARIADO">ASALARIADO</option><option value="INDEPENDIENTE">INDEPENDIENTE</option>',$row["empresa_tipo_empleo"]),'class="form-control" ','','',3,3);
    echo campo("empresa_tipo_condicion","Tipo de Condicion",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="FIJO">FIJO</option><option value="TEMPORAL">TEMPORAL</option><option value="INTERINO">INTERINO</option>',$row["empresa_tipo_condicion"]),'class="form-control" ','','',3,3);
    echo campo("empresa_telefono","Telefono",'text',$row["empresa_telefono"],'class="form-control" data-mask="9999-9999"','','',3,4);
    echo campo("empresa_telefono2","Telefono 2",'text',$row["empresa_telefono2"],'class="form-control" data-mask="9999-9999"','','',3,4);
    echo campo("empresa_extension","Extension",'text',$row["empresa_extension"],'class="form-control" ','','',3,1);
    echo campo("empresa_salario","Salario",'text',$row["empresa_salario"],'class="form-control" ','','',3,4);
    echo campo("empresa_salario_otro","Ortos ingresos",'text',$row["empresa_salario_otro"],'class="form-control" ','','',3,4);  
      
    echo campo("empresa_salario_otro_tipo","Por concepto",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="REMESAS">REMESAS</option><option value="ALQUILERES">ALQUILERES</option><option value="OTROS">OTROS</option>',$row["empresa_salario_otro_tipo"]),'class="form-control" ','','',3,3);
    echo campo("empresa_puesto","Ocupacion o Cargo",'text',$row["empresa_puesto"],'class="form-control" ','','',3,4);
    echo campo("empresa_fecha_ingreso","Fecha Ingreso",'date',fechademysql($row["empresa_fecha_ingreso"]),'class="form-control" ','','',3,3);
    
echo "<hr>";
    echo"<br><h4>DATOS DE REFERENCIAS PERSONALES</h4><br>";
    
    echo campo("ref1_nombre","Nombre",'text',$row["ref1_nombre"],'class="form-control" ','','',3,6);
    echo campo("ref1_telefono_casa","Telefono Casa",'text',$row["ref1_telefono_casa"],'class="form-control" data-mask="9999-9999"','','',3,3);
    echo campo("ref1_telefono_trabajo","Telefono Trabajo",'text',$row["ref1_telefono_trabajo"],'class="form-control" data-mask="9999-9999"','','',3,3);
    echo campo("ref1_telefono_celular","Telefono Celular",'text',$row["ref1_telefono_celular"],'class="form-control" data-mask="9999-9999"','','',3,3); 
    echo campo("ref1_relacion","Relacion",'text',$row["ref1_relacion"],'class="form-control" ','','',3,4); 
    echo "<hr>";  echo"<br>";
    echo campo("ref2_nombre","Nombre",'text',$row["ref2_nombre"],'class="form-control" ','','',3,6);
    echo campo("ref2_telefono_casa","Telefono Casa",'text',$row["ref2_telefono_casa"],'class="form-control" data-mask="9999-9999"','','',3,3);
    echo campo("ref2_telefono_trabajo","Telefono Trabajo",'text',$row["ref2_telefono_trabajo"],'class="form-control" data-mask="9999-9999"','','',3,3);
    echo campo("ref2_telefono_celular","Telefono Celular",'text',$row["ref2_telefono_celular"],'class="form-control" data-mask="9999-9999"','','',3,3);
    echo campo("ref2_relacion","Relacion",'text',$row["ref2_relacion"],'class="form-control" ','','',3,4);
    echo "<hr>";   echo"<br>";
    echo campo("ref3_nombre","Nombre",'text',$row["ref3_nombre"],'class="form-control" ','','',3,6);
    echo campo("ref3_telefono_casa","Telefono Casa",'text',$row["ref3_telefono_casa"],'class="form-control"data-mask="9999-9999" ','','',3,3);
    echo campo("ref3_telefono_trabajo","Telefono Trabajo",'text',$row["ref3_telefono_trabajo"],'class="form-control" data-mask="9999-9999"','','',3,3);
    echo campo("ref3_telefono_celular","Telefono Celular",'text',$row["ref3_telefono_celular"],'class="form-control" data-mask="9999-9999"','','',3,3);
    echo campo("ref3_relacion","Relacion",'text',$row["ref3_relacion"],'class="form-control" ','','',3,4);
     echo "<hr>";  echo"<br>";
    echo campo("ref4_nombre","Nombre",'text',$row["ref4_nombre"],'class="form-control" ','','',3,6);
    echo campo("ref4_telefono_casa","Telefono Casa",'text',$row["ref4_telefono_casa"],'class="form-control" data-mask="9999-9999"','','',3,3);
    echo campo("ref4_telefono_trabajo","Telefono Trabajo",'text',$row["ref4_telefono_trabajo"],'class="form-control" data-mask="9999-9999"','','',3,3);
    echo campo("ref4_telefono_celular","Telefono Celular",'text',$row["ref4_telefono_celular"],'class="form-control" data-mask="9999-9999"','','',3,3);
    echo campo("ref4_relacion","Relacion",'text',$row["ref4_relacion"],'class="form-control" ','','',3,4);
    
      echo '<br><a id="Guardar'.$forma.'" href="#" class="btn btn-primary" onclick="procesar_datos(\'creditos_gestion.php?a=3&b='.$b.'&s=1&cid='.$solicitud_id.'\','.$forma.'); return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar</a>';
    echo '<div id="respuesta'.$forma.'"> </div>';
      
   echo " </form></div></div></div></div>";
                 
         
     
                    
 
                 
                } else { echo mensaje( "No se encontraron registros","info"); exit;}  
    
    
              exit;
 } 
        
//******************************************************************************
if ($accion=="4") //TODO documentos adjuntos
{
          
         //******* SQL ************************************************************************************
               
            $sql="SELECT doc1, doc2, doc3, doc4, doc5, doc6, doc7, doc8, doc9, doc10, doc11, doc12
            ,fecha_enviar_creditos
                    FROM prestamo
                    WHERE  id=$solicitud_id 
                    ";
        
         
            // ****** Fin SQL ********************************************************************************
            
             $result = $conn -> query($sql);


            if ($result -> num_rows > 0) {
                
                $row = $result -> fetch_assoc();
                
 if (es_nulo($row["fecha_enviar_creditos"])) {
       echo ' <div class="panel panel-default"> <div id="remitircreditos" class="panel-body"> <div class="row"><div class="col-xs-12"> <form id="forma'.$forma.'" class="form-horizontal">';
     
       echo 'Cuando termine de ingresar la solicitud, precione el boton para remitir la solicitud al departamento de Creditos.';
       echo ' &nbsp;&nbsp;<a id="Guardar'.$forma.'" href="#" class="btn btn-primary" onclick="procesar_datos_remitirgestion(\'creditos_gestion.php?a=2&s2=1&cid='.$solicitud_id.'\','.$forma.'); return false;"><span class="glyphicon glyphicon-transfer" aria-hidden="true"></span> Remitir a Creditos</a>';
  
       echo '<div id="respuesta'.$forma.'"> </div>';
        
       echo " </form></div></div></div></div>";
  
       $forma=$forma+10;
   } 
 
               
      echo ' <div class="panel panel-default"> <div id="datosgenerales" class="panel-body"> <div class="row"> <div class="col-xs-12"> <form class="form-horizontal">';
        
        echo "<h4>Documentos Adjuntos</h4><br>";
     
                
                
           $doc1="upload";
           $doc2="upload";
           $doc3="upload";
           $doc4="upload";
           $doc5="upload";
           $doc6="upload";
           $doc7="upload";
           $doc8="upload";
           $doc9="upload";
           $doc10="upload";
           $doc11="upload";
           $doc12="upload";
           

            if ($row["doc1"]<>"") {$doc1="uploadlink";  }
            if ($row["doc2"]<>"") {$doc2="uploadlink";  }
            if ($row["doc3"]<>"") {$doc3="uploadlink";  }
            if ($row["doc4"]<>"") {$doc4="uploadlink";  }
            if ($row["doc5"]<>"") {$doc5="uploadlink";  }
            if ($row["doc6"]<>"") {$doc6="uploadlink";  }
            if ($row["doc7"]<>"") {$doc7="uploadlink";  }
            if ($row["doc8"]<>"") {$doc8="uploadlink";  }
            if ($row["doc9"]<>"") {$doc9="uploadlink";  }
            if ($row["doc10"]<>"") {$doc10="uploadlink";  }
            if ($row["doc11"]<>"") {$doc11="uploadlink";  }
            if ($row["doc12"]<>"") {$doc12="uploadlink";  }

                    
            echo "<hr>";
            echo campo_upload("doc1","SOLICITUD PORTADA",$doc1,$row["doc1"],'class="form-control" ',$solicitud_id,3,9,"SI");
            echo "<hr>";
            echo campo_upload("doc2","SOLICITUD CONTRAPORTADA",$doc2,$row["doc2"],'class="form-control" ',$solicitud_id,3,9,"SI");
            echo "<hr>";
            echo campo_upload("doc3","IDENTIDAD",$doc3,$row["doc3"],'class="form-control" ',$solicitud_id,3,9,"SI");
            echo "<hr>";
            echo campo_upload("doc4","LICENCIA",$doc4,$row["doc4"],'class="form-control" ',$solicitud_id,3,9,"SI");
            echo "<hr>";
            echo campo_upload("doc5","RTN",$doc5,$row["doc5"],'class="form-control" ',$solicitud_id,3,9,"SI");
            echo "<hr>";
            echo campo_upload("doc6","CONSTANCIA DE TRABAJO",$doc6,$row["doc6"],'class="form-control" ',$solicitud_id,3,9,"SI");
            echo "<hr>";
            echo campo_upload("doc7","RECIBOS PUBLICOS",$doc7,$row["doc7"],'class="form-control" ',$solicitud_id,3,9,"SI");
            echo "<hr>";
            echo campo_upload("doc8","CROQUIS",$doc8,$row["doc8"],'class="form-control" ',$solicitud_id,3,9,"SI");
         //   echo "<hr>";
        //    echo campo_upload("doc9","COMPROBANTE PAGO (PRIMA)",$doc9,$row["doc9"],'class="form-control" ',$solicitud_id,3,9,"SI");
        //    echo "<hr>";
        //    echo campo_upload("doc10","COPIA FACTURA",$doc10,$row["doc10"],'class="form-control" ',$solicitud_id,3,9,"SI");
        
            
            
            } 
       
   echo " </form></div></div></div></div>";
}
//******************************************************************************


if ($accion=="41") { // TODO   guardar link de docto subido

    
     if (!isset($_REQUEST['nn'],$_REQUEST['dd'])) {echo mensaje( "Debe seleccionar un archivo","warning"); exit;}
     $archivo_nombre=$conn->real_escape_string($_REQUEST['nn']);
     $dd=$conn->real_escape_string($_REQUEST['dd']);
     if ($archivo_nombre=="") {echo mensaje( "Debe seleccionar un registro","warning"); exit;}
     
     
            //******* SQL ************************************************************************************
           
            
            
            $sql=  "UPDATE prestamo set $dd='$archivo_nombre'
                    WHERE     id=$solicitud_id                         
            "; 
            
            
            // ****** Fin SQL ********************************************************************************
            // echo $sql;exit;
            
           
            if ($conn->query($sql) === TRUE) {

            
            echo "OK";
            
            } else { echo mensaje( "Error al guardar documento","warning");exit;}

   
  
     
     
    exit;
    
} // fin pagina 


//******************************************************************************
 
 
if ($accion=="5") //TODO gestiones
{
     //******* SQL ************************************************************************************
               
            $sql="SELECT id, nombre , 
            
        (SELECT prestamo_gestion.estatus_id FROM prestamo_gestion 
        where prestamo_gestion.prestamo_id=$solicitud_id and prestamo_gestion.etapa_id=prestamo_etapa.id
        and prestamo_gestion.gestion_estado is null
        order by prestamo_gestion.id desc limit 1) as estado_actual
        
        ,(select count(prestamo_gestion.id) from prestamo_gestion 
        where prestamo_gestion.prestamo_id=$solicitud_id and prestamo_gestion.etapa_id=prestamo_etapa.id
        and prestamo_gestion.gestion_estado is not null
        and prestamo_gestion.gestion_estado<>'Confirmado') as pendientes
                    
    FROM prestamo_etapa
    
    where incluir_pasos=1
    order by orden,id";

    // ****** Fin SQL ********************************************************************************
       
             $result = $conn -> query($sql);

            if ($result -> num_rows > 0) {
                   
               
               
         
    echo '
    <div class="panel panel-default"> <div id="datosgenerales" class="panel-body"> <div class="row">  <div class="col-lg-12"> 
    
    <div class="col-lg-3" > 
    <ul class="nav nav-pills nav-stacked  " style="margin-right: 10px;">
    ';
    
    
  while ($row = $result -> fetch_assoc()) {
      
      $clase="";
      $icono='<i class="glyphicon glyphicon-question-sign"></i> '; 
      
      if ($row["estado_actual"]==1 or $row["estado_actual"]=="1") {
         $clase='class="alert-info"';
         $icono='<i class="glyphicon glyphicon-info-sign"></i> '; 
      }
      if ($row["estado_actual"]==2 or $row["estado_actual"]=="2") {
         $clase='class="alert-success"';
         $icono='<i class="glyphicon glyphicon-ok-sign"></i> '; 
      }
      if ($row["estado_actual"]==3 or $row["estado_actual"]=="3") {
         $clase='class="alert-danger"';
         $icono='<i class="glyphicon glyphicon-ban-circle"></i> '; 
      }
    
    $gpendientes="";
    
    if ($row["pendientes"]>0) {$gpendientes='  <span class="label label-warning">'.$row["pendientes"].'</span>';}
    
    echo '
     <li '.$clase.'><a href="#b1" onclick=" actualizarbox(\'b1\',\'creditos_gestion.php?a=5b1&cst='.$row["id"].'&cid='. $solicitud_id .'\') ; return false;" id="titulo'.$row["id"].'" data-titulo="'.$row["nombre"].'" class="text-muted">'.$icono.$row["nombre"].$gpendientes.'</a></li>
  
    ';
    
     // <li class="alert-success"><a href="#b1"   onclick="actualizarbox(\'b1\',\'creditos_gestion.php?a=5b1&cst=1&cid='. $solicitud_id .'\') ; return false;"  class="text-muted"><i class="glyphicon glyphicon-ok-sign"></i> Personal Info</a></li>
  // <li class="alert-success"><a href="#b2"   onclick="actualizarbox(\'b1\',\'creditos_gestion.php?a=5b1&cst=2&cid='. $solicitud_id .'\') ; return false;"  class="text-muted"><i class="glyphicon glyphicon-ok-sign"></i> Address</a></li>
  // <li class="alert-warning"><a href="#b3" onclick="actualizarbox(\'b1\',\'creditos_gestion.php?a=5b1&cst=3&cid='. $solicitud_id .'\') ; return false;" ><i class="glyphicon glyphicon-ban-circle"></i> Employment</a></li>
  // <li><a href="#" class="text-muted" onclick="actualizarbox(\'b1\',\'creditos_gestion.php?a=5b1&cst=4&cid='. $solicitud_id .'\') ; return false;"><i class="glyphicon glyphicon-question-sign"></i> Signatures</a></li>
  // <li><a href="#" class="text-muted" onclick="actualizarbox(\'b1\',\'creditos_gestion.php?a=5b1&cst=5&cid='. $solicitud_id .'\') ; return false;"><i class="glyphicon glyphicon-question-sign"></i> Status</a></li>
//     
    }
        echo '
         </ul>
         <hr>
  </div>
  
     <div class="col-lg-9"> 
     
       <h4 id="eltitulogestion"></h4><br>
       
       <div class="tab-pane" id="b1"></div>
    </div>
    ';
    
        ?>

 
  <?php  
    
       echo "  </div></div></div></div>";
       }

if (isset($_REQUEST['b'])) { ?>
<script>

    actualizarbox('b1','creditos_gestion.php?a=5b1&cst=<?php echo $_REQUEST['b'] ?>&cid=<?php echo $solicitud_id ?>' ) ;

</script>
<?php }

       exit;
} 



if ($accion=="5x") //TODO gestiones marcar verificado
{
    $salida="";
    //########## validar datos
        $verror="";
        
      //  $verror.=validar("obs",$_REQUEST['obs'], "text", true,  null,  1,  null);

       
        
    // ######### Guardar 
        if ($verror==""){
            
        
            $sqlcampos="";
            $sqlcampos.= "  prestamo_id =".GetSQLValue($conn->real_escape_string($_REQUEST["cid"]),"int");
            $sqlcampos.= " , campo_id =".GetSQLValue($conn->real_escape_string($_REQUEST["n"]),"int");
            $sqlcampos.= " , gestion_estado='Confirmado' ";
             $sqlcampos.= " , etapa_id =".GetSQLValue($conn->real_escape_string($_REQUEST["eid"]),"int");          
             $sqlcampos.= " , estatus_id =2";
           if (isset($_REQUEST['obs'])) {
              $sqlcampos.= " , descripcion =".GetSQLValue($conn->real_escape_string($_REQUEST["obs"]),"text"); 
           }
           
           //$sqlcampos.= " , usuario_dirigido =".GetSQLValue($conn->real_escape_string($_REQUEST["usr"]),"text");  
            
     
            $sqlcampos.= ",usuario= '" .$_SESSION['usuario'] . "',fecha=curdate() ,hora=now()";
            $sqlcampos.= ",usuario_confirma= '" .$_SESSION['usuario'] . "' ,hora_confirma=now()";
            
            $sqlcampos.= " , bodega=(select prestamo.bodega from prestamo where prestamo.id=".$conn->real_escape_string($_REQUEST["cid"])." limit 1) ";
if (tiene_permiso(26)) {$sqlcampos.= " , canal='CI'"; }
            $sql="insert into prestamo_gestion set " . $sqlcampos;
            
            if ($conn->query($sql) === TRUE) {
                //$gestion_id_new = mysqli_insert_id($conn);
               

              $salida="OK";   
                    
            } else {
      
                $salida ='Se produjo un error al guardar el registro DB101: <br>'.$conn->error;

            }
        
            
            
            
        } else {
            //mostrar errores validacion
            $salida=$verror;
        }           
                
        
     
     echo $salida;
    exit;
}


if ($accion=="5x2c") //TODO gestiones marcar verificado, confirmacion de creditos
{
    $salida="";
    //########## validar datos
        $verror="";
        
        
    // ######### Guardar 
        if ($verror==""){
            
        
            $sqlcampos="";
            $sqlcampos.= "  gestion_estado='Confirmado' ";
   
             $sqlcampos.= " , estatus_id =2";
             
             $sqlcampos.= ",usuario_confirma= '" .$_SESSION['usuario'] . "' ,hora_confirma=now()";
             
             if (isset($_REQUEST['obs'])) {
              $sqlcampos.= " , texto_confirma =".GetSQLValue($conn->real_escape_string($_REQUEST["obs"]),"text"); 
           }


            $sql="update prestamo_gestion set " . $sqlcampos . " where prestamo_id=".$conn->real_escape_string($_REQUEST['cid'])." and campo_id=".$conn->real_escape_string($_REQUEST['n'])." and usuario_responde is not null";
           
            if ($conn->query($sql) === TRUE) {
                
                if ($_REQUEST['n']==0) { //si es nueva solicitud
                   $conn->query("update prestamo set fecha_recibe_creditos=now() where id=$solicitud_id") ;
                }
                
              $salida="OK";   
                    
            } else {
      
                $salida ='Se produjo un error al guardar el registro DB101 <br>'.$conn->error;

            }
        
            
            
            
        } else {
            //mostrar errores validacion
            $salida=$verror;
        }           
                
        
     
     echo $salida;
    exit;
}


// CREATE TABLE `prestamo_gestion` (
    // `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    // `prestamo_id` INT(11) NULL DEFAULT NULL,
    // `fecha` DATE NULL DEFAULT NULL,
    // `hora` DATETIME NULL DEFAULT NULL,
    // `usuario` VARCHAR(45) NULL DEFAULT NULL,
    // `etapa_id` INT(11) NULL DEFAULT NULL,
    // `campo_id` INT(11) NULL DEFAULT NULL,
    // `gestion_estado` VARCHAR(15) NULL DEFAULT NULL COMMENT 'Vendedor, Creditos y Confirmado',
    // `descripcion` TEXT NULL,
    // `estatus_id` INT(11) NULL DEFAULT '1',
    // `seccion` VARCHAR(50) NULL DEFAULT NULL,
    // `usuario_dirigido` VARCHAR(45) NULL DEFAULT NULL,
    // `hora_responde` DATETIME NULL DEFAULT NULL,
    // `texto_responde` VARCHAR(150) NULL DEFAULT NULL,
    
    
if ($accion=="5y") //TODO gestiones nueva
{
    $salida="";
    //########## validar datos
        $verror="";
        
        $verror.=validar("Dirigido a",$_REQUEST['usr'], "text", true,  null,  2,  null);

       
        
    // ######### Guardar 
        if ($verror==""){
            
        
            $sqlcampos="";
            $sqlcampos.= "  prestamo_id =".GetSQLValue($conn->real_escape_string($_REQUEST["cid"]),"int");
            $sqlcampos.= " , campo_id =".GetSQLValue($conn->real_escape_string($_REQUEST["n"]),"int");
             if ($conn->real_escape_string($_REQUEST["usr"])==$_SESSION['usuario']) {
                 $sqlcampos.= " , gestion_estado='Creditos' ";
                 $sqlcampos.= " , usuario_responde='".$_SESSION['usuario']."' ";
             } else {
                 $sqlcampos.= " , gestion_estado='Vendedor' "; 
             }
           
            $sqlcampos.= " , etapa_id =".GetSQLValue($conn->real_escape_string($_REQUEST["eid"]),"int");          
             $sqlcampos.= " , estatus_id =".GetSQLValue($conn->real_escape_string($_REQUEST["est"]),"int");
           if (isset($_REQUEST['obs'])) {
              $sqlcampos.= " , descripcion =".GetSQLValue($conn->real_escape_string($_REQUEST["obs"]),"text"); 
           }
           
           $sqlcampos.= " , usuario_dirigido =".GetSQLValue($conn->real_escape_string($_REQUEST["usr"]),"text");  
            
    
            $sqlcampos.= ",usuario= '" .$_SESSION['usuario'] . "',fecha=curdate() ,hora=now()";
            $sqlcampos.= " , bodega=(select prestamo.bodega from prestamo where prestamo.id=".$conn->real_escape_string($_REQUEST["cid"])." limit 1) ";
           if (tiene_permiso(26)) {$sqlcampos.= " , canal='CI'"; } 
            $sql="insert into prestamo_gestion set " . $sqlcampos;
            
            if ($conn->query($sql) === TRUE) {
                $gestion_id_new = mysqli_insert_id($conn);
                enviar_notificacion_gestion($gestion_id_new,'',$conn->real_escape_string($_REQUEST["usr"]),$conn->real_escape_string($_REQUEST["obs"]));

              $salida="OK";   
                    
            } else {
      
                $salida ='Se produjo un error al guardar el registro DB101: <br>'.$conn->error;

            }
        
            
            
            
        } else {
            //mostrar errores validacion
            $salida=$verror;
        }           
                
        
     
     echo $salida;
    exit;
}

if ($accion=="5z") //TODO Responder a una gestion
{
    $salida="";
    //########## validar datos
        $verror="";
        $sqlcampos2="";
        
        if (isset($_REQUEST['geid'])) { $gestion_id=$conn->real_escape_string($_REQUEST['geid']) ;} else { $verror= mensaje("Debe seleccionar una solicitud","danger");exit;}
 
        
        $verror.=validar("Respuesta",$_REQUEST['texto_responde'], "text", true,  null,  2,  null);

        if (isset($_POST)) {
            $sepa="";
            while(list($key, $val) = each($_POST)){
            $key = stripslashes($key);
            $val = stripslashes($val);
            if ($key<>"doc1" and $key<>"doc2"and $key<>"doc3"and $key<>"doc4"and $key<>"doc5"and $key<>"doc6"and $key<>"doc7" and $key<>"doc8" and $key<>"doc9") {
            $sqlcampos2.= $sepa.$conn->real_escape_string($key)."=".GetSQLValue($conn->real_escape_string($val),"text");
            $sepa=" , ";
            }
            }
         // echo "update prestamo set $sqlcampos2 where id=$solicitud_id"; exit;  
          if ($sqlcampos2<>"") { $conn->query("update prestamo set $sqlcampos2 where id=$solicitud_id"); }
          
        }
  
        
    // ######### Guardar 
        if ($verror==""){
            
        
            $sqlcampos="";
            $sqlcampos.= "  texto_responde =".GetSQLValue($conn->real_escape_string($_REQUEST["texto_responde"]),"text");
           
            $sqlcampos.= ",gestion_estado='Creditos'";     
            $sqlcampos.= ",usuario_responde= '" .$_SESSION['usuario'] . "' ,hora_responde=now()";

            $sql="update prestamo_gestion set " . $sqlcampos ." where id=$gestion_id";
            
            if ($conn->query($sql) === TRUE) {
   
              $salida=mensaje("Guardado Satisfactoriamente","success");   
                    
            } else {   
                $salida =mensaje('Se produjo un error al guardar el registro DB101: <br>'.$conn->error,"danger");//<br>'.$conn->error;
            }
        
            
            
            
        } else {
            //mostrar errores validacion
            $salida=mensaje($verror,"danger");
        }           
                
        
     
     echo $salida;
    exit;
}

if ($accion=="5g6") //TODO guardar verificacion de campo
{
    $salida="";
        $sqlcampos2="";
        
             
     
        if (isset($_POST)) {
            $sepa="";
            while(list($key, $val) = each($_POST)){
            $key = stripslashes($key);
            $val = stripslashes($val);
            if ($key<>"doc1" and $key<>"doc2"and $key<>"doc3"and $key<>"doc4"and $key<>"doc5"and $key<>"doc6"and $key<>"doc7" and $key<>"doc8" and $key<>"doc9") {
            $sqlcampos2.= $sepa.$conn->real_escape_string($key)."=".GetSQLValue($conn->real_escape_string($val),"text");
            $sepa=" , ";
            }
            }
         // echo "update prestamo set $sqlcampos2 where id=$solicitud_id"; exit;  
          if ($sqlcampos2<>"") {
              
          
           if ($conn->query("update prestamo set $sqlcampos2 where id=$solicitud_id") === TRUE) {
   
              $salida=mensaje("Guardado Satisfactoriamente","success");   
                    
            } else {   
                $salida =mensaje('Se produjo un error al guardar el registro DB101: <br>'.$conn->error,"danger");//<br>'.$conn->error;
            }
          }
          
        }
  
              
                
        
     
     echo $salida;
    exit;
}

if ($accion=="5g7") //TODO calculo finncero aaprobacion
{
    $salida="";
    
    
                if ($_REQUEST['s']=="2") //TODO Responder a una gestion
                {
            
                    //########## validar datos
                        $verror="";
                        $sqlcampos2="";
                        
                        if (isset($_REQUEST['geid'])) { $gestion_id=$conn->real_escape_string($_REQUEST['geid']) ;} else { $verror= mensaje("Debe seleccionar una solicitud","danger");exit;}
                 
                        
                        $verror.=validar("Respuesta",$_REQUEST['texto_responde'], "text", true,  null,  2,  null);
                               
                        
                    // ######### Guardar 
                        if ($verror==""){
                            
                        
                            $sqlcampos="";
                            $sqlcampos.= "  texto_responde =".GetSQLValue($conn->real_escape_string($_REQUEST["texto_responde"]),"text");
                           
                            $sqlcampos.= ",gestion_estado='Confirmado'";     
                            $sqlcampos.= ",usuario_responde= '" .$_SESSION['usuario'] . "' ,hora_responde=now()";
                
                            $sql="update prestamo_gestion set " . $sqlcampos ." where id=$gestion_id";
                            
                            if ($conn->query($sql) === TRUE) {
                                    $conn->query("update prestamo set aprobado_gerencia_usuario='".$_SESSION['usuario']."',aprobado_gerencia_fecha=now() where id=$solicitud_id  limit 1");
                              $salida=mensaje("Guardado Satisfactoriamente","success");   
                                    
                            } else {   
                                $salida =mensaje('Se produjo un error al guardar el registro DB101: <br>'.$conn->error,"danger");//<br>'.$conn->error;
                            }
                        
                            
                            
                            
                        } else {
                            //mostrar errores validacion
                            $salida=mensaje($verror,"danger");
                        }           
                                
                        
                     
                     echo $salida;
                    exit;
                }
    
    
    
    
    //########## validar datos
        $sql="";
        $verror="";
        $sqlcampos2="";
        
  
    // ######### Guardar 
        if ($verror==""){
         $usuario_dirigido="" ;      
           $sql="select usuario.usuario,usuario.nombre from usuario
                LEFT OUTER JOIN usuario_nivelxgrupo ON (usuario.grupo_id=usuario_nivelxgrupo.grupo_id) 
                where usuario.activo='SI' and usuario_nivelxgrupo.nivel_id=25
                group by usuario.usuario,usuario.nombre";
    
      $result2 = $conn -> query($sql);


      if ($result2 -> num_rows > 0) {                  
                $row2 = $result2 -> fetch_assoc();
                $usuario_dirigido=$row2["usuario"];   
      }
            
                      

                // crear gestion avisar de condiciones aprobacion
                    $sqlcampos="";
                    $sqlcampos.= "  prestamo_id =$solicitud_id";
                    $sqlcampos.= " , gestion_estado='Creditos' ";
                    $sqlcampos.= " , campo_id=701 "; //*****
                    $sqlcampos.= " , etapa_id=7 ";
                    $sqlcampos.= " , estatus_id=1 ";
                    $sqlcampos.= " , descripcion='Solicitud de Aprobacion' ";
                    $sqlcampos.= ",usuario= '" .$_SESSION['usuario'] . "',fecha=curdate() ,hora=now()";
                    $sqlcampos.= ",usuario_dirigido= '$usuario_dirigido'"   ;
                   // $sqlcampos.= ",usuario_confirma= '" .$_SESSION['usuario'] . "' ,hora_confirma=now()";
                    
                    
                    $sqlcampos.= " , bodega=(select prestamo.bodega from prestamo where prestamo.id=$solicitud_id limit 1) ";
        if (tiene_permiso(26)) {$sqlcampos.= " , canal='CI'"; }
                    $sql="insert into prestamo_gestion set " . $sqlcampos;
 
            
            if ($conn->query($sql) === TRUE) {
                
           
                
                    $gestion_id_new = mysqli_insert_id($conn);
                    enviar_notificacion_gestion($gestion_id_new,'',$usuario_dirigido,'Condiciones de Aprobacion');
               
   
              $salida=mensaje("Guardado Satisfactoriamente","success");   
                    
            } else {   
                $salida =mensaje('Se produjo un error al guardar el registro DB101: <br>'.$conn->error,"danger");//<br>'.$conn->error;
            }
           
                  
        } else {
            //mostrar errores validacion
            $salida=mensaje($verror,"danger");
        }           
                
        
     
     echo $salida;
    exit;
}


if ($accion=="5g8") //TODO condiciones aprobacion
{
    $salida="";
    //########## validar datos
        $sql="";
        $verror="";
        $sqlcampos2="";
        
  
    // ######### Guardar 
        if ($verror==""){
         $usuario_dirigido="" ;      
            $sql="SELECT usuario_alta
                    FROM prestamo
                    where id=$solicitud_id";
    
      $result2 = $conn -> query($sql);


      if ($result2 -> num_rows > 0) {                  
                $row2 = $result2 -> fetch_assoc();
                $usuario_dirigido=$row2["usuario_alta"];   
      }
            
            if (isset($_POST)) {
            $sepa="";
            while(list($key, $val) = each($_POST)){
            $key = stripslashes($key);
            $val = stripslashes($val);
            if ($key<>"doc1" and $key<>"doc2"and $key<>"doc3"and $key<>"doc4"and $key<>"doc5"and $key<>"doc6"and $key<>"doc7" and $key<>"doc8" and $key<>"doc9") {
            $sqlcampos2.= $sepa.$conn->real_escape_string($key)."=".GetSQLValue($conn->real_escape_string($val),"text");
            $sepa=" , ";
            }
            }
         // echo "update prestamo set $sqlcampos2 where id=$solicitud_id"; exit;  
          if ($sqlcampos2<>"") { $sql="update prestamo set $sqlcampos2 where id=$solicitud_id"; }
          
        }
            
        if ($sql<>"") {
            
            if ($conn->query($sql) === TRUE) {
                
           
                    // crear gestion avisar de condiciones aprobacion
                    $sqlcampos="";
                    $sqlcampos.= "  prestamo_id =$solicitud_id";
                    $sqlcampos.= " , gestion_estado='Vendedor' ";
                    $sqlcampos.= " , campo_id=801 "; //*****
                    $sqlcampos.= " , etapa_id=8 ";
                    $sqlcampos.= " , estatus_id=1 ";
                    $sqlcampos.= " , descripcion='Condiciones de Aprobacion' ";
                    $sqlcampos.= ",usuario= '" .$_SESSION['usuario'] . "',fecha=curdate() ,hora=now()";
                    $sqlcampos.= ",usuario_dirigido= '$usuario_dirigido'"   ;
                   // $sqlcampos.= ",usuario_confirma= '" .$_SESSION['usuario'] . "' ,hora_confirma=now()";
                    
                    
                    $sqlcampos.= " , bodega=(select prestamo.bodega from prestamo where prestamo.id=$solicitud_id limit 1) ";
        if (tiene_permiso(26)) {$sqlcampos.= " , canal='CI'"; }
                    $sql="insert into prestamo_gestion set " . $sqlcampos;
                    $conn->query($sql); 
                    $gestion_id_new = mysqli_insert_id($conn);
                    enviar_notificacion_gestion($gestion_id_new,'',$usuario_dirigido,'Condiciones de Aprobacion');
               
   
              $salida=mensaje("Guardado Satisfactoriamente","success");   
                    
            } else {   
                $salida =mensaje('Se produjo un error al guardar el registro DB101: <br>'.$conn->error,"danger");//<br>'.$conn->error;
            }
           
          }  else {  $salida =mensaje('No se guardo ningun campo',"danger"); }         
            
        } else {
            //mostrar errores validacion
            $salida=mensaje($verror,"danger");
        }           
                
        
     
     echo $salida;
    exit;
}

if ($accion=="5g9") //TODO Generar Contrato
{
    $salida="";
    $nuevo=true;
    //########## validar datos
        $verror="";
        
        $verror.=validar("Fecha de Primera Cuota",$_REQUEST['cierre_cuota_primera'], "date", true,  null,  null,  null); 
        $verror.=validar("Fecha de Ultima Cuota",$_REQUEST['cierre_cuota_final'], "date", true,  null,  null,  null); 
        $verror.=validar("Fecha de Firma Contrato",$_REQUEST['cierre_firma_fecha'], "date", true,  null,  null,  null); 
        
 
        
        $sqlcampos2="";
        $usuario_dirigido="";
     
    // ######### Guardar 
        if ($verror==""){
            
             $sql="SELECT cierre_contrato,usuario_alta
                    FROM prestamo
                    where id=$solicitud_id";
    
      $result2 = $conn -> query($sql);


      if ($result2 -> num_rows > 0) {                  
                $row2 = $result2 -> fetch_assoc();
                $usuario_dirigido=$row2["usuario_alta"];
                if ($row2["cierre_contrato"]>0){
                    $nuevo=false;
                }     
      }
            
              
   

    
            $sqlcampos="";
            // $sqlcampos.= "  cierre_plazo =".GetSQLValue($conn->real_escape_string($_REQUEST["cierre_plazo"]),"double");
            // $sqlcampos.= " , cierre_total_usd =".GetSQLValue($conn->real_escape_string($_REQUEST["cierre_total_usd"]),"double");
            // $sqlcampos.= " , cierre_interes_mensual =".GetSQLValue($conn->real_escape_string($_REQUEST["cierre_interes_mensual"]),"double");
            // $sqlcampos.= " , cierre_total_usd_contado =".GetSQLValue($conn->real_escape_string($_REQUEST["cierre_total_usd_contado"]),"double");
            // $sqlcampos.= " , cierre_total_seguro_usd =".GetSQLValue($conn->real_escape_string($_REQUEST["cierre_total_seguro_usd"]),"double");
            // $sqlcampos.= " , cierre_total_prima_usd =".GetSQLValue($conn->real_escape_string($_REQUEST["cierre_total_prima_usd"]),"double");
            // $sqlcampos.= " , cierre_cuota_cantidad =".GetSQLValue($conn->real_escape_string($_REQUEST["cierre_cuota_cantidad"]),"double");
            // $sqlcampos.= " , cierre_cuota_total_usd =".GetSQLValue($conn->real_escape_string($_REQUEST["cierre_cuota_total_usd"]),"double");
            $sqlcampos.= "  cierre_cuota_dia_pago =".GetSQLValue($conn->real_escape_string($_REQUEST["cierre_cuota_dia_pago"]),"double");
            
            $sqlcampos.= " , cierre_cuota_primera =".GetSQLValue(mysqldate($conn->real_escape_string($_REQUEST["cierre_cuota_primera"])),"text");
            $sqlcampos.= " , cierre_cuota_final =".GetSQLValue(mysqldate($conn->real_escape_string($_REQUEST["cierre_cuota_final"])),"text");
            $sqlcampos.= " , cierre_firma_fecha =".GetSQLValue(mysqldate($conn->real_escape_string($_REQUEST["cierre_firma_fecha"])),"text");
            
            if ($nuevo==true) {
                $contrato_num=get_dato_sql('prestamo',"(max(cierre_contrato)+1)",'');
                $sqlcampos.= " , cierre_contrato =".GetSQLValue($contrato_num,"int");
            }
            
            
            $sqlcampos.= " , moto_serie =".GetSQLValue($conn->real_escape_string($_REQUEST["moto_serie"]),"text");
          //  $sqlcampos.= " , moto_tipo =".GetSQLValue($conn->real_escape_string($_REQUEST["moto_tipo"]),"text");
            $sqlcampos.= " , moto_marca =".GetSQLValue($conn->real_escape_string($_REQUEST["moto_marca"]),"text");
            $sqlcampos.= " , moto_modelo =".GetSQLValue($conn->real_escape_string($_REQUEST["moto_modelo"]),"text");
            $sqlcampos.= " , moto_motor =".GetSQLValue($conn->real_escape_string($_REQUEST["moto_motor"]),"text");
            $sqlcampos.= " , moto_color =".GetSQLValue($conn->real_escape_string($_REQUEST["moto_color"]),"text");
            $sqlcampos.= " , moto_ano =".GetSQLValue($conn->real_escape_string($_REQUEST["moto_ano"]),"text");
            $sqlcampos.= " , moto_cilindraje =".GetSQLValue($conn->real_escape_string($_REQUEST["moto_cilindraje"]),"text");
            $sqlcampos.= " , moto_valor =".GetSQLValue($conn->real_escape_string(filter_var($_REQUEST["moto_valor"], FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION)),"double");
           


            $sql="update prestamo set " . $sqlcampos ." where id=$solicitud_id";
            
            if ($conn->query($sql) === TRUE) {
                  $salida="OK";
                
                if ($nuevo==true) {
                    // crear gestion avisar que fue creado contrato
                    $sqlcampos="";
                    $sqlcampos.= "  prestamo_id =$solicitud_id";
                    $sqlcampos.= " , gestion_estado='Vendedor' ";
                    $sqlcampos.= " , campo_id=901 "; //*****
                    $sqlcampos.= " , etapa_id=9 ";
                    $sqlcampos.= " , estatus_id=1 ";
                    $sqlcampos.= " , descripcion='Documentos legales listos para imprimir' ";
                    $sqlcampos.= ",usuario= '" .$_SESSION['usuario'] . "',fecha=curdate() ,hora=now()";
                    $sqlcampos.= ",usuario_dirigido= '$usuario_dirigido'"   ;
                    
                    $sqlcampos.= " , bodega=(select prestamo.bodega from prestamo where prestamo.id=$solicitud_id limit 1) ";
        if (tiene_permiso(26)) {$sqlcampos.= " , canal='CI'"; }
                    $sql="insert into prestamo_gestion set " . $sqlcampos;
                    $conn->query($sql); 
                    $gestion_id_new = mysqli_insert_id($conn);
                    enviar_notificacion_gestion($gestion_id_new,'',$usuario_dirigido,'Documentos legales listos para imprimir');
                    }
               
                    
            } else {   
                $salida =mensaje('Se produjo un error al guardar el registro DB101: <br>'.$conn->error,"danger");//<br>'.$conn->error;
            }
        
            
            
            
        } else {
            //mostrar errores validacion
            $salida=mensaje($verror,"danger");
        }           
                
        
     
     echo $salida;
    exit;
}


if ($accion=="5g10") //TODO Gestion crear envio de documentos
{
    $salida="";
    $nuevo=true;
    //########## validar datos
        $verror="";
        
          
        $sqlcampos2="";
        $usuario_dirigido="";
     
    // ######### Guardar 
        if ($verror==""){
            
             $sql="SELECT cierre_documentos_enviados_gestion,usuario_alta
                    FROM prestamo
                    where id=$solicitud_id";
    
      $result2 = $conn -> query($sql);


      if ($result2 -> num_rows > 0) {                  
                $row2 = $result2 -> fetch_assoc();
                $usuario_dirigido=$row2["usuario_alta"];
                if ($row2["cierre_documentos_enviados_gestion"]=="SI"){
                    $nuevo=false;
                }     
      }
            
              
   
            if ($nuevo==true) {
                $sql="update prestamo set cierre_documentos_enviados_gestion='SI' where id=$solicitud_id";
                
                if ($conn->query($sql) === TRUE) {
                  $salida="OK";
                
       
                    // crear gestion avisar que envio documentos
                    $sqlcampos="";
                    $sqlcampos.= "  prestamo_id =$solicitud_id";
                    $sqlcampos.= " , gestion_estado='Vendedor' ";
                    $sqlcampos.= " , campo_id=1001 "; //*****
                    $sqlcampos.= " , etapa_id=10 ";
                    $sqlcampos.= " , estatus_id=1 ";
                    $sqlcampos.= " , descripcion='Enviar documentos legales' ";
                    $sqlcampos.= ",usuario= '" .$_SESSION['usuario'] . "',fecha=curdate() ,hora=now()";
                    $sqlcampos.= ",usuario_dirigido= '$usuario_dirigido'"   ;
                    
                    $sqlcampos.= " , bodega=(select prestamo.bodega from prestamo where prestamo.id=$solicitud_id limit 1) ";
        if (tiene_permiso(26)) {$sqlcampos.= " , canal='CI'"; }
                    $sql="insert into prestamo_gestion set " . $sqlcampos;
                    $conn->query($sql); 
                    $gestion_id_new = mysqli_insert_id($conn);
                    enviar_notificacion_gestion($gestion_id_new,'',$usuario_dirigido,'Enviar documentos legales');
           
               
                    
            } else {   
                $salida =mensaje('Se produjo un error al guardar el registro DB101: <br>'.$conn->error,"danger");//<br>'.$conn->error;
            }
                
     
            }
            
       
        } else {
            //mostrar errores validacion
            $salida=mensaje($verror,"danger");
        }           
                
        
     
     echo $salida;
    exit;
}

if ($accion=="5g11") //TODO en cierre de credito
{
    $salida="";
    //########## validar datos
        $sql="";
        $verror="";
        $sqlcampos2="";
        
        $codcierre=$_REQUEST['cierre_razon'];
        
        if ($codcierre=="0" or $codcierre=="" ) {
            $verror="Debe ingresar la razon del cierre";
        }
     
  
    // ######### Guardar 
        if ($verror==""){
            
            if (isset($_POST)) {
            $sepa="";
            while(list($key, $val) = each($_POST)){
            $key = stripslashes($key);
            $val = stripslashes($val);
            if ($key<>"doc1" and $key<>"doc2"and $key<>"doc3"and $key<>"doc4"and $key<>"doc5"and $key<>"doc6"and $key<>"doc7" and $key<>"doc8" and $key<>"doc9") {
            $sqlcampos2.= $sepa.$conn->real_escape_string($key)."=".GetSQLValue($conn->real_escape_string($val),"text");
            $sepa=" , ";
            }
            }
         // echo "update prestamo set $sqlcampos2 where id=$solicitud_id"; exit;  
         
         if ($codcierre=="1" or $codcierre==="2") {$sqlcampos2.=" , estatus=2";} else {$sqlcampos2.=" , estatus=3";}
          if ($sqlcampos2<>"") { $sql="update prestamo set $sqlcampos2 where id=$solicitud_id"; }
          
        }
            
        if ($sql<>"") {
            
            if ($conn->query($sql) === TRUE) {
   
              $salida=mensaje("Guardado Satisfactoriamente","success");   
                    
            } else {   
                $salida =mensaje('Se produjo un error al guardar el registro DB101: <br>'.$conn->error,"danger");//<br>'.$conn->error;
            }
           
          }  else {  $salida =mensaje('No se guardo ningun campo',"danger"); }         
            
        } else {
            //mostrar errores validacion
            $salida=mensaje($verror,"danger");
        }           
                
        
     
     echo $salida;
    exit;
}


if ($accion=="5b1ss") //TODO en gestiones solo guardar
{
    $salida="";
    //########## validar datos
        $sql="";
        $verror="";
        $sqlcampos2="";
        
  
    // ######### Guardar 
        if ($verror==""){
            
            if (isset($_POST)) {
            $sepa="";
            while(list($key, $val) = each($_POST)){
            $key = stripslashes($key);
            $val = stripslashes($val);
            if ($key<>"doc1" and $key<>"doc2"and $key<>"doc3"and $key<>"doc4"and $key<>"doc5"and $key<>"doc6"and $key<>"doc7" and $key<>"doc8" and $key<>"doc9") {
            $sqlcampos2.= $sepa.$conn->real_escape_string($key)."=".GetSQLValue($conn->real_escape_string($val),"text");
            $sepa=" , ";
            }
            }
         // echo "update prestamo set $sqlcampos2 where id=$solicitud_id"; exit;  
          if ($sqlcampos2<>"") { $sql="update prestamo set $sqlcampos2 where id=$solicitud_id"; }
          
        }
            
        if ($sql<>"") {
            
            if ($conn->query($sql) === TRUE) {
   
              $salida=mensaje("Guardado Satisfactoriamente","success");   
                    
            } else {   
                $salida =mensaje('Se produjo un error al guardar el registro DB101: <br>'.$conn->error,"danger");//<br>'.$conn->error;
            }
           
          }  else {  $salida =mensaje('No se guardo ningun campo',"danger"); }         
            
        } else {
            //mostrar errores validacion
            $salida=mensaje($verror,"danger");
        }           
                
        
     
     echo $salida;
    exit;
}




if ($accion=="5b1") //TODO gestiones
{
    

    
    //guardar 
    if (isset($_REQUEST['s'])) {
            
        $laetapa=GetSQLValue($conn->real_escape_string($_REQUEST["etapa_id"]),"int");
        
                    
        //########## validar datos
        $verror="";
        
        $verror.=validar("Descripcion",$_REQUEST['descripcion'], "text", true,  null,  2,  null);
        $verror.=validar("Estado",$_REQUEST['estado'], "int", true,  null,  null,  null);
       
     //  if ($_REQUEST['estado']=="0") {$verror.= 'Debe seleccionar el Estado de la gestion';}
     
     // Validar que no tenga gestiones abiertas para aprobar
     if ($_REQUEST['estado']==2 or $_REQUEST['estado']=="2") {
          $sqlver="select count(prestamo_gestion.id) as pendientes from prestamo_gestion 
            where prestamo_gestion.prestamo_id=$solicitud_id 
            and prestamo_gestion.etapa_id =$laetapa
            and prestamo_gestion.gestion_estado is not null
            and prestamo_gestion.gestion_estado<>'Confirmado'";
          $result = $conn -> query($sqlver);
          if ($result -> num_rows > 0) {                  
            $row = $result -> fetch_assoc();
            if ($row["pendientes"]>0) { $verror='No se puede aprobar la etapa porque hay '.$row["pendientes"].' gestiones pendientes';
                
            }
          }  
     }
     
     
    // ######### Guardar 
        if ($verror==""){
            $elprestamo=GetSQLValue($conn->real_escape_string($_REQUEST["cid"]),"int");
            $laetapa=$laetapa;
        
            $sqlcampos="";
            $sqlcampos.= "  etapa_id =".$laetapa ;
            $sqlcampos.= " , prestamo_id =".$elprestamo;
            $sqlcampos.= " , estatus_id =".GetSQLValue($conn->real_escape_string($_REQUEST["estado"]),"int");
            $sqlcampos.= " , descripcion =".GetSQLValue($conn->real_escape_string($_REQUEST["descripcion"]),"text");
           
           $sqlcampos.= " , bodega=(select prestamo.bodega from prestamo where prestamo.id=".$conn->real_escape_string($_REQUEST["cid"])." limit 1) ";
          
            $sqlcampos.= ",usuario= '" .$_SESSION['usuario'] . "',fecha=curdate() ,hora=now()";
if (tiene_permiso(26)) {$sqlcampos.= " , canal='CI'"; }
            $sql="insert into prestamo_gestion set " . $sqlcampos;
            
            if ($conn->query($sql) === TRUE) {
            $insert_id = mysqli_insert_id($conn);
                
                $conn->query("update prestamo set etapa_proceso=ifnull((select max(etapa_id) from prestamo_gestion where prestamo_id=$elprestamo limit 1),$laetapa) where id=$elprestamo");
            $stud_arr[0]["pcode"] = 1;
            $stud_arr[0]["pmsg"] ='Los datos fueron guardados satisfactoriamente. El numero de gestion es: <strong>'.$insert_id.'</strong>';    
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
    } // fin guardar 
    
    
    if (isset($_REQUEST['cst'])) { $cod_status = $conn->real_escape_string($_REQUEST['cst']); } else   {exit ;}     
    
    $campo_unico="";
    if (isset($_REQUEST['cpo'])) { $campo_unico = $conn->real_escape_string($_REQUEST['cpo']); }
       

     
     if ($campo_unico=="") {echo '<script> $(\'#eltitulogestion\').text($(titulo'.$cod_status.').data(\'titulo\')) ; </script>';}  
     
     $mostrar_todo=false;
     if ($campo_unico=="701" and tiene_permiso(19)) { $mostrar_todo=true;   }
     
     //----------------------------------
     
     $asignados=leer_verificaciones_asignados($solicitud_id);
     
     echo campo('ccetapa','','hidden',$cod_status,'');
     
     echo '<form id="forma2'.$forma.'" class="form-horizontal ">';
     
     
     if ($cod_status=="1") { //Nueva solicitud
     
      if (tiene_permiso(19)) {
          
            
            $tmpasignado[0]='Creditos';  
            $tmpasignado["desc"][0]="";           
            $tmpasignado["desc2"][0]="";
            $tmpasignado["desc3"][0]="";
            $tmpasignado["desc4"][0]="";
                     
            echo ' <div class="row">';
            echo "<hr>";
            echo "<strong>Solicitud Nueva</strong>, presione el boton para confirmar la recepcion de la misma:<br><br>";
            echo boton_verificar(0,$solicitud_id,false,'',$tmpasignado,$campo_unico); 
         //   echo "<hr>";   
         //   echo "<a  href=\"#\" class=\"btn btn-default btn-sm\" onclick=\"actualizarbox('pagina','creditos_gestion.php?a=1&cid=".$solicitud_id."') ; return false;\" ><span class=\"glyphicon glyphicon-folder-open\" aria-hidden=\"true\"></span>  &nbsp;Abrir Solicitud</a>";
            echo "<hr>";   
             echo '</div>';
              
      }
     
     }
     
     if ($cod_status=="2" or $mostrar_todo) { //VERIFICAR DOCUMENTOS
     
  
     $sql="SELECT id,doc1, doc2, doc3, doc4, doc5, doc6, doc7, doc8, doc9, doc10, doc11, doc12,
    usuario_alta
    FROM prestamo
    where id=$solicitud_id";
    
      $result = $conn -> query($sql);


      if ($result -> num_rows > 0) {                  
                $row = $result -> fetch_assoc();
          
      if ($mostrar_todo==true) {
          //desplegar documentos adjuntos
          
          echo incrustar_objeto("SOLICITUD PORTADA",$row["doc1"]);

          echo incrustar_objeto("SOLICITUD CONTRAPORTADA",$row["doc2"]);

          echo incrustar_objeto("IDENTIDAD",$row["doc3"]);

          echo incrustar_objeto("LICENCIA",$row["doc4"]);

          echo incrustar_objeto("RTN",$row["doc5"]);

          echo incrustar_objeto("CONSTANCIA DE TRABAJO",$row["doc6"]);

          echo incrustar_objeto("RECIBOS PUBLICOS",$row["doc7"]);

          echo incrustar_objeto("CROQUIS",$row["doc8"]);

          echo incrustar_objeto("COMPROBANTE PAGO (PRIMA)",$row["doc9"]);
          
      } else {   
          
      if ($campo_unico=="" or $campo_unico==1 or $mostrar_todo) {      
            echo "<hr><div class=\"row\"><div class=\"col-xs-6\">"."SOLICITUD PORTADA"."</div><div class=\"col-xs-6\">".boton_verificar(1,$solicitud_id,false,$row["usuario_alta"],$asignados,$campo_unico)."</div></div>";
            echo "<div class=\"row\"><div class=\"col-xs-12\">".campo_upload("doc1","",'uploadlink',$row["doc1"],'class="form-control" ',$row["id"],0,12,"SI")."</div></div><br><br>";
      }                                                   
      if ($campo_unico=="" or $campo_unico==2 or $mostrar_todo) {     
            echo "<hr><div class=\"row\"><div class=\"col-xs-6\">"."SOLICITUD CONTRAPORTADA"."</div><div class=\"col-xs-6\">".boton_verificar(2,$solicitud_id,false,$row["usuario_alta"],$asignados,$campo_unico)."</div></div>";
            echo "<div class=\"row\"><div class=\"col-xs-12\">".campo_upload("doc2","",'uploadlink',$row["doc2"],'class="form-control" ',$row["id"],0,12,"SI")."</div></div><br><br>";
      } 
      if ($campo_unico=="" or $campo_unico==3 or $mostrar_todo) {      
            echo "<hr><div class=\"row\"><div class=\"col-xs-6\">"."IDENTIDAD"."</div><div class=\"col-xs-6\">".boton_verificar(3,$solicitud_id,false,$row["usuario_alta"],$asignados,$campo_unico)."</div></div>";
            echo "<div class=\"row\"><div class=\"col-xs-12\">".campo_upload("doc3","",'uploadlink',$row["doc3"],'class="form-control" ',$row["id"],0,12,"SI")."</div></div><br><br>";
      } 
      if ($campo_unico=="" or $campo_unico==4 or $mostrar_todo) {      
            echo "<hr><div class=\"row\"><div class=\"col-xs-6\">"."LICENCIA"."</div><div class=\"col-xs-6\">".boton_verificar(4,$solicitud_id,false,$row["usuario_alta"],$asignados,$campo_unico)."</div></div>";
            echo "<div class=\"row\"><div class=\"col-xs-12\">".campo_upload("doc4","",'uploadlink',$row["doc4"],'class="form-control" ',$row["id"],0,12,"SI")."</div></div><br><br>";
      } 
      if ($campo_unico=="" or $campo_unico==5 or $mostrar_todo) {      
            echo "<hr><div class=\"row\"><div class=\"col-xs-6\">"."RTN"."</div><div class=\"col-xs-6\">".boton_verificar(5,$solicitud_id,false,$row["usuario_alta"],$asignados,$campo_unico)."</div></div>";
            echo "<div class=\"row\"><div class=\"col-xs-12\">".campo_upload("doc5","",'uploadlink',$row["doc5"],'class="form-control" ',$row["id"],0,12,"SI")."</div></div><br><br>";
      } 
      if ($campo_unico=="" or $campo_unico==6 or $mostrar_todo) {      
            echo "<hr><div class=\"row\"><div class=\"col-xs-6\">"."CONSTANCIA DE TRABAJO"."</div><div class=\"col-xs-6\">".boton_verificar(6,$solicitud_id,false,$row["usuario_alta"],$asignados,$campo_unico)."</div></div>";
            echo "<div class=\"row\"><div class=\"col-xs-12\">".campo_upload("doc6","",'uploadlink',$row["doc6"],'class="form-control" ',$row["id"],0,12,"SI")."</div></div><br><br>";
       } 
      if ($campo_unico=="" or $campo_unico==7 or $mostrar_todo) {     
            echo "<hr><div class=\"row\"><div class=\"col-xs-6\">"."RECIBOS PUBLICOS"."</div><div class=\"col-xs-6\">".boton_verificar(7,$solicitud_id,false,$row["usuario_alta"],$asignados,$campo_unico)."</div></div>";
            echo "<div class=\"row\"><div class=\"col-xs-12\">".campo_upload("doc7","",'uploadlink',$row["doc7"],'class="form-control" ',$row["id"],0,12,"SI")."</div></div><br><br>";
      } 
      if ($campo_unico=="" or $campo_unico==8 or $mostrar_todo) {      
            echo "<hr><div class=\"row\"><div class=\"col-xs-6\">"."CROQUIS"."</div><div class=\"col-xs-6\">".boton_verificar(8,$solicitud_id,false,$row["usuario_alta"],$asignados,$campo_unico)."</div></div>";
            echo "<div class=\"row\"><div class=\"col-xs-12\">".campo_upload("doc8","",'uploadlink',$row["doc8"],'class="form-control" ',$row["id"],0,12,"SI")."</div></div><br><br>";
       } 
      if ($campo_unico=="" or $campo_unico==9 or $mostrar_todo) {     
            echo "<hr><div class=\"row\"><div class=\"col-xs-6\">"."COMPROBANTE PAGO (PRIMA)"."</div><div class=\"col-xs-6\">".boton_verificar(9,$solicitud_id,false,$row["usuario_alta"],$asignados,$campo_unico)."</div></div>";
            echo "<div class=\"row\"><div class=\"col-xs-12\">".campo_upload("doc9","",'uploadlink',$row["doc9"],'class="form-control" ',$row["id"],0,12,"SI")."</div></div><br><br>";
            echo "<hr>";
      }
      
      }//else mostrar todo fin
       }
            else { echo mensaje( "No se encontraron registros","info"); exit;}

    }
     
 
      if ($cod_status=="3" or $mostrar_todo) { //VERIFICAR BURO
        
     $sql="SELECT id,doc1, doc2, doc3, doc4, doc5, doc6, doc7, doc8, doc9, doc10, doc11, doc12,
     usuario_alta
    FROM prestamo
    
    where id=$solicitud_id";
    
      $result = $conn -> query($sql);


      if ($result -> num_rows > 0) {                  
                $row = $result -> fetch_assoc();
          
          if ($mostrar_todo==true) {
          //desplegar documentos adjuntos
          echo incrustar_objeto("IDENTIDAD",$row["doc3"]);
          echo incrustar_objeto("DOCUMENTO DEL BURO",$row["doc11"]);
          
      } else {
            echo ' <div class="row">';
            echo "<hr>";
            echo campo("doc3","IDENTIDAD",'uploadlink',$row["doc3"],'class="form-control" ',$row["id"]);
            echo "<hr>";
            echo campo_upload("doc11","DOCUMENTO DEL BURO",'uploadlink',$row["doc11"],'class="form-control" ',$row["id"],0,12,"SI");
            echo "<hr>";
             echo '</div>';
             }
        }
            else { echo mensaje( "No se encontraron registros","info"); exit;}

    }    

if ($cod_status=="4" or $mostrar_todo) { //VERIFICAR telefonica
 
 
 $sql="SELECT id, nombres, apellidos, identidad, direccion, ciudad, departamento, telefono, celular, sexo, email, direccion_gps, estado_civil, nombre_conyuge, profesion, fecha_nacimiento, escolaridad, no_dependientes, vecino, vecino_telefono, tipo_vivienda, antiguedad_vivienda, empresa, empresa_direccion, empresa_tipo_empleo, empresa_tipo_condicion, empresa_telefono, empresa_extension, empresa_salario, empresa_salario_otro, empresa_salario_otro_tipo, empresa_puesto, empresa_fecha_ingreso, 
            ref1_nombre, ref1_telefono_casa, ref1_telefono_trabajo, ref1_telefono_celular, ref2_nombre, ref2_telefono_casa, ref2_telefono_trabajo, ref2_telefono_celular, ref3_nombre, ref3_telefono_casa, ref3_telefono_trabajo, ref3_telefono_celular, ref4_nombre, ref4_telefono_casa, ref4_telefono_trabajo, ref4_telefono_celular,
            ref1_relacion,ref2_relacion,ref3_relacion,ref4_relacion
            , telefono2,telefono3,vecino_telefono2,empresa_telefono2
            ,usuario_alta
                    FROM prestamo
                    WHERE  id=$solicitud_id 
                    ";
    
      $result = $conn -> query($sql);


      if ($result -> num_rows > 0) {                  
                $row = $result -> fetch_assoc();

      echo '  <div class="row">';
      
      
       echo "<hr><div class=\"row\"><div class=\"col-xs-8\">"."<h4>DATOS </h4>"."</div><div class=\"col-xs-4\"></div></div>";
          echo "<div class=\"row\"><div class=\"col-xs-8\">";
          echo "Nombre: ".$row["nombres"]." ".$row["apellidos"]."<br>" ;
          echo "Direccion: ".$row["direccion"]."<br>" ;
          echo "Ciudad: ".$row["ciudad"]."<br>" ;
         echo "</div></div>";
   
 
      if ($campo_unico=="" or $campo_unico==11 or $mostrar_todo) {  
   echo "<hr><div class=\"row\"><div class=\"col-xs-8\">"."Telefono"."</div></div>";
            echo "<div class=\"row\"><div class=\"col-xs-8\">". campo("telefono","",'text',$row["telefono"],'class="form-control" ','','',3,3)."</div><div class=\"col-xs-4\">".boton_verificar(11,$solicitud_id,true,$row["usuario_alta"],$asignados,$campo_unico)."</div></div>";
 } 
      if ($campo_unico=="" or $campo_unico==12 or $mostrar_todo) {    
   echo "<hr><div class=\"row\"><div class=\"col-xs-8\">"."Telefono 2"."</div></div>";
            echo "<div class=\"row\"><div class=\"col-xs-8\">". campo("telefono2","",'text',$row["telefono2"],'class="form-control" ','','',3,3)."</div><div class=\"col-xs-4\">".boton_verificar(12,$solicitud_id,true,$row["usuario_alta"],$asignados,$campo_unico)."</div></div>";
  } 
      if ($campo_unico=="" or $campo_unico==13 or $mostrar_todo) {   
   echo "<hr><div class=\"row\"><div class=\"col-xs-8\">"."Celular"."</div></div>";
            echo "<div class=\"row\"><div class=\"col-xs-8\">". campo("celular","",'text',$row["celular"],'class="form-control" ','','',3,3)."</div><div class=\"col-xs-4\">".boton_verificar(13,$solicitud_id,true,$row["usuario_alta"],$asignados,$campo_unico)."</div></div>";
  } 
      if ($campo_unico=="" or $campo_unico==14 or $mostrar_todo) {   
   echo "<hr><div class=\"row\"><div class=\"col-xs-8\">"."Celular 2"."</div></div>";
            echo "<div class=\"row\"><div class=\"col-xs-8\">". campo("telefono3","",'text',$row["telefono3"],'class="form-control" ','','',3,3)."</div><div class=\"col-xs-4\">".boton_verificar(14,$solicitud_id,true,$row["usuario_alta"],$asignados,$campo_unico)."</div></div>";
      } 

     
    
 
      if ($campo_unico=="" or $campo_unico==15 or $mostrar_todo) { 
      echo "<hr><div class=\"row\"><div class=\"col-xs-8\">"."<h4>DATOS DE PARIENTE / VECINO</h4>"."</div></div>";
          echo "<div class=\"row\"><div class=\"col-xs-8\">";
          echo "Pariente / Vecino: ".$row["vecino"]."<br>" ;
          echo "Vecino Telefono: ".$row["vecino_telefono"]."<br>" ;
          echo "Vecino Telefono2: ".$row["vecino_telefono2"]."<br>" ;
         echo "</div><div class=\"col-xs-4\">".boton_verificar(15,$solicitud_id,true,$row["usuario_alta"],$asignados,$campo_unico)."</div></div>";
 
  } 
      if ($campo_unico=="" or $campo_unico==16 or $mostrar_todo) {     
       echo "<hr><div class=\"row\"><div class=\"col-xs-8\">"."<h4>DATOS DE REFERENCIAS PERSONALES</h4>"."</div></div>";
          echo "<div class=\"row\"><div class=\"col-xs-8\">";
          echo "Nombre: ".$row["ref1_nombre"]."<br>" ;
          echo "Telefono Casa: ".$row["ref1_telefono_casa"]."<br>" ;
          echo "Telefono Trabajo: ".$row["ref1_telefono_trabajo"]."<br>" ;
          echo "Telefono Celular: ".$row["ref1_telefono_celular"]."<br>" ;
          echo "Relacion: ".$row["ref1_relacion"]."<br>" ;      
       echo "</div><div class=\"col-xs-4\">".boton_verificar(16,$solicitud_id,true,$row["usuario_alta"],$asignados,$campo_unico)."</div></div>";
  } 
      if ($campo_unico=="" or $campo_unico==17 or $mostrar_todo) {       
       echo "<hr><div class=\"row\"><div class=\"col-xs-8\">"."<h4>DATOS DE REFERENCIAS PERSONALES</h4>"."</div></div>";
          echo "<div class=\"row\"><div class=\"col-xs-8\">";
          echo "Nombre: ".$row["ref2_nombre"]."<br>" ;
          echo "Telefono Casa: ".$row["ref2_telefono_casa"]."<br>" ;
          echo "Telefono Trabajo: ".$row["ref2_telefono_trabajo"]."<br>" ;
          echo "Telefono Celular: ".$row["ref2_telefono_celular"]."<br>" ;
          echo "Relacion: ".$row["ref2_relacion"]."<br>" ;      
       echo "</div><div class=\"col-xs-4\">".boton_verificar(17,$solicitud_id,true,$row["usuario_alta"],$asignados,$campo_unico)."</div></div>";
 } 
      if ($campo_unico=="" or $campo_unico==18 or $mostrar_todo) {        
       echo "<hr><div class=\"row\"><div class=\"col-xs-8\">"."<h4>DATOS DE REFERENCIAS PERSONALES</h4>"."</div></div>";
          echo "<div class=\"row\"><div class=\"col-xs-8\">";
          echo "Nombre: ".$row["ref3_nombre"]."<br>" ;
          echo "Telefono Casa: ".$row["ref3_telefono_casa"]."<br>" ;
          echo "Telefono Trabajo: ".$row["ref3_telefono_trabajo"]."<br>" ;
          echo "Telefono Celular: ".$row["ref3_telefono_celular"]."<br>" ;
          echo "Relacion: ".$row["ref3_relacion"]."<br>" ;      
       echo "</div><div class=\"col-xs-4\">".boton_verificar(18,$solicitud_id,true,$row["usuario_alta"],$asignados,$campo_unico)."</div></div>";
 } 
      if ($campo_unico=="" or $campo_unico==19 or $mostrar_todo) {        
       echo "<hr><div class=\"row\"><div class=\"col-xs-8\">"."<h4>DATOS DE REFERENCIAS PERSONALES</h4>"."</div></div>";
          echo "<div class=\"row\"><div class=\"col-xs-8\">";
          echo "Nombre: ".$row["ref4_nombre"]."<br>" ;
          echo "Telefono Casa: ".$row["ref4_telefono_casa"]."<br>" ;
          echo "Telefono Trabajo: ".$row["ref4_telefono_trabajo"]."<br>" ;
          echo "Telefono Celular: ".$row["ref4_telefono_celular"]."<br>" ;
          echo "Relacion: ".$row["ref4_relacion"]."<br>" ;      
       echo "</div><div class=\"col-xs-4\">".boton_verificar(19,$solicitud_id,true,$row["usuario_alta"],$asignados,$campo_unico)."</div></div>";
      }     
     
   
   
       echo '</div>';   
         echo "<hr>";
        }
        
   
    }    


 if ($cod_status=="5" or $mostrar_todo) { //VERIFICAR de laboral
 
 
 $sql="SELECT id, nombres, apellidos, identidad, direccion, ciudad, departamento, telefono, celular, sexo, email, direccion_gps, estado_civil, nombre_conyuge, profesion, fecha_nacimiento, escolaridad, no_dependientes, vecino, vecino_telefono, tipo_vivienda, antiguedad_vivienda, empresa, empresa_direccion, empresa_tipo_empleo, empresa_tipo_condicion, empresa_telefono, empresa_extension, empresa_salario, empresa_salario_otro, empresa_salario_otro_tipo, empresa_puesto, empresa_fecha_ingreso, 
            ref1_nombre, ref1_telefono_casa, ref1_telefono_trabajo, ref1_telefono_celular, ref2_nombre, ref2_telefono_casa, ref2_telefono_trabajo, ref2_telefono_celular, ref3_nombre, ref3_telefono_casa, ref3_telefono_trabajo, ref3_telefono_celular, ref4_nombre, ref4_telefono_casa, ref4_telefono_trabajo, ref4_telefono_celular,
            ref1_relacion,ref2_relacion,ref3_relacion,ref4_relacion
            , telefono2,telefono3,vecino_telefono2,empresa_telefono2,requiere_verificacion_campo_laboral
            ,usuario_alta
                    FROM prestamo
                    WHERE  id=$solicitud_id 
                    ";
    
      $result = $conn -> query($sql);


      if ($result -> num_rows > 0) {                  
                $row = $result -> fetch_assoc();
  echo"<br><h4>DATOS LABORALES</h4><br>";
     } 
      if ($campo_unico=="" or $campo_unico==20 or $mostrar_todo) {
             echo ' <div class="row">';
    } 
      if ($campo_unico=="" or $campo_unico==21 or $mostrar_todo) {
    echo "<hr><div class=\"row\"><div class=\"col-xs-8\">"."Empresa donde Labora"."</div></div>";
            echo "<div class=\"row\"><div class=\"col-xs-8\">". campo("empresa","",'text',$row["empresa"],'class="form-control" ','','',3,7)."</div><div class=\"col-xs-4\">".boton_verificar(20,$solicitud_id,false,$row["usuario_alta"],$asignados,$campo_unico)."</div></div>";
    } 
      if ($campo_unico=="" or $campo_unico==21 or $mostrar_todo) {
    echo "<hr><div class=\"row\"><div class=\"col-xs-8\">"."Direccion"."</div></div>";
            echo "<div class=\"row\"><div class=\"col-xs-8\">". campo("empresa_direccion","",'text',$row["empresa_direccion"],'class="form-control" ','','',3,9)."</div><div class=\"col-xs-4\">".boton_verificar(21,$solicitud_id,false,$row["usuario_alta"],$asignados,$campo_unico)."</div></div>";
    } 
      if ($campo_unico=="" or $campo_unico==22 or $mostrar_todo) {
    echo "<hr><div class=\"row\"><div class=\"col-xs-8\">"."Condicion Laboral"."</div></div>";
            echo "<div class=\"row\"><div class=\"col-xs-8\">". campo("empresa_tipo_empleo","",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="ASALARIADO">ASALARIADO</option><option value="INDEPENDIENTE">INDEPENDIENTE</option>',$row["empresa_tipo_empleo"]),'class="form-control" ','','',3,3)."</div><div class=\"col-xs-4\">".boton_verificar(22,$solicitud_id,false,$row["usuario_alta"],$asignados,$campo_unico)."</div></div>";
    } 
      if ($campo_unico=="" or $campo_unico==23 or $mostrar_todo) {
    echo "<hr><div class=\"row\"><div class=\"col-xs-8\">"."Tipo de Condicion"."</div></div>";
            echo "<div class=\"row\"><div class=\"col-xs-8\">". campo("empresa_tipo_condicion","",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="FIJO">FIJO</option><option value="TEMPORAL">TEMPORAL</option><option value="INTERINO">INTERINO</option>',$row["empresa_tipo_condicion"]),'class="form-control" ','','',3,3)."</div><div class=\"col-xs-4\">".boton_verificar(23,$solicitud_id,false,$row["usuario_alta"],$asignados,$campo_unico)."</div></div>";
    } 
      if ($campo_unico=="" or $campo_unico==24 or $mostrar_todo) {
    echo "<hr><div class=\"row\"><div class=\"col-xs-8\">"."Telefono"."</div></div>";
            echo "<div class=\"row\"><div class=\"col-xs-8\">". campo("empresa_telefono","",'text',$row["empresa_telefono"],'class="form-control" ','','',3,4)."</div><div class=\"col-xs-4\">".boton_verificar(24,$solicitud_id,true,$row["usuario_alta"],$asignados,$campo_unico)."</div></div>";
    } 
      if ($campo_unico=="" or $campo_unico==25 or $mostrar_todo) {
    echo "<hr><div class=\"row\"><div class=\"col-xs-8\">"."Telefono 2"."</div></div>";
            echo "<div class=\"row\"><div class=\"col-xs-8\">". campo("empresa_telefono2","",'text',$row["empresa_telefono2"],'class="form-control" ','','',3,4)."</div><div class=\"col-xs-4\">".boton_verificar(25,$solicitud_id,true,$row["usuario_alta"],$asignados,$campo_unico)."</div></div>";
    } 
      if ($campo_unico==""  or $mostrar_todo) {
    echo "<hr><div class=\"row\"><div class=\"col-xs-8\">"."Extension"."</div><div class=\"col-xs-4\"></div></div>";
            echo "<div class=\"row\"><div class=\"col-xs-8\">". campo("empresa_extension","",'text',$row["empresa_extension"],'class="form-control" ','','',3,1)."</div></div>";
    } 
      if ($campo_unico=="" or $campo_unico==26 or $mostrar_todo) {
    echo "<hr><div class=\"row\"><div class=\"col-xs-8\">"."Salario"."</div></div>";
            echo "<div class=\"row\"><div class=\"col-xs-8\">". campo("empresa_salario","",'text',$row["empresa_salario"],'class="form-control" ','','',3,4)."</div><div class=\"col-xs-4\">".boton_verificar(26,$solicitud_id,false,$row["usuario_alta"],$asignados,$campo_unico)."</div></div>";
    } 
      if ($campo_unico=="" or $campo_unico==27 or $mostrar_todo) {
    echo "<hr><div class=\"row\"><div class=\"col-xs-8\">"."Ortos ingresos"."</div></div>";
            echo "<div class=\"row\"><div class=\"col-xs-8\">". campo("empresa_salario_otro","",'text',$row["empresa_salario_otro"],'class="form-control" ','','',3,4)."</div><div class=\"col-xs-4\">".boton_verificar(27,$solicitud_id,false,$row["usuario_alta"],$asignados,$campo_unico)."</div></div>" ;
      
    } 
      if ($campo_unico=="" or $campo_unico==28 or $mostrar_todo) {
    echo "<hr><div class=\"row\"><div class=\"col-xs-8\">"."Por concepto"."</div></div>";
            echo "<div class=\"row\"><div class=\"col-xs-8\">". campo("empresa_salario_otro_tipo","",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="REMESAS">REMESAS</option><option value="ALQUILERES">ALQUILERES</option><option value="OTROS">OTROS</option>',$row["empresa_salario_otro_tipo"]),'class="form-control" ','','',3,3)."</div><div class=\"col-xs-4\">".boton_verificar(28,$solicitud_id,false,$row["usuario_alta"],$asignados,$campo_unico)."</div></div>";
    } 
      if ($campo_unico=="" or $campo_unico==29 or $mostrar_todo) {
    echo "<hr><div class=\"row\"><div class=\"col-xs-8\">"."Ocupacion o Cargo"."</div></div>";
            echo "<div class=\"row\"><div class=\"col-xs-8\">". campo("empresa_puesto","",'text',$row["empresa_puesto"],'class="form-control" ','','',3,4)."</div><div class=\"col-xs-4\">".boton_verificar(29,$solicitud_id,false,$row["usuario_alta"],$asignados,$campo_unico)."</div></div>";
    } 
      if ($campo_unico=="" or $campo_unico==30 or $mostrar_todo) {
    echo "<hr><div class=\"row\"><div class=\"col-xs-8\">"."Fecha Ingreso"."</div></div>";
            echo "<div class=\"row\"><div class=\"col-xs-8\">". campo("empresa_fecha_ingreso","",'date',$row["empresa_fecha_ingreso"],'class="form-control" ','','',3,3)."</div><div class=\"col-xs-4\">".boton_verificar(30,$solicitud_id,false,$row["usuario_alta"],$asignados,$campo_unico)."</div></div>";
  
   } 
      if ($campo_unico==""  or $mostrar_todo) { 
       echo "<hr>";
            echo  campo("requiere_verificacion_campo_laboral","Requiere verificacion de Campo",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="SI">SI</option><option value="NO">NO</option>',$row["requiere_verificacion_campo_laboral"]),'class="form-control" ','','',3,3)."</div></div>";
      }
     
      echo '</div>';   
         echo "<hr>";
        
   
    
      if ($campo_unico==""  or $mostrar_todo) {
        
     $sql="SELECT id,doc1, doc2, doc3, doc4, doc5, doc6, doc7, doc8, doc9, doc10, doc11, doc12
    FROM prestamo
    where id=$solicitud_id";
    
      $result = $conn -> query($sql);


      if ($result -> num_rows > 0) {                  
                $row = $result -> fetch_assoc();
            
            if ($mostrar_todo==true) {
          //desplegar documentos adjuntos
          echo incrustar_objeto("CONSTANCIA DE TRABAJO",$row["doc6"]);
          
      } else {    
            echo '  <div class="row">';
            echo "<hr>";
            echo campo("doc6","CONSTANCIA DE TRABAJO",'uploadlink',$row["doc6"],'class="form-control" ',$row["id"]);
            echo "<hr>";

           
             echo '</div>';
             }
        }
            else { echo mensaje( "No se encontraron registros","info"); exit;}
            }
    }    


   if ($cod_status=="6" or $mostrar_todo) { //VERIFICAR de campo
        
     $sql="SELECT *
    FROM prestamo
    where id=$solicitud_id";
    


      $result = $conn -> query($sql);


      if ($result -> num_rows > 0) {                  
                $row = $result -> fetch_assoc();
              // error_reporting(0);
              
              if (tiene_permiso(19)) {   echo boton_verificar(31,$solicitud_id,false,$row["usuario_alta"],$asignados,$campo_unico); 
               echo '  <hr>';
              }
      if ($campo_unico=="" or $campo_unico==31 or $mostrar_todo) {
    
            echo '  <div class="row">';
            echo "<H4>Verificacion Domiciciaria</H4>";
            echo "<hr>";
            echo "".$row["nombres"]." ".$row["apellidos"]."<br>";
            echo "".$row["direccion"]." ".$row["ciudad"]." ".$row["departamento"]."<br>";
            echo "".$row["direccion_referencia"]."<br>";
             echo "".$row["telefono"]." ".$row["telefono2"]." ".$row["celular"]."<br>";
             echo "<hr>";
             if ($mostrar_todo==true) {
          //desplegar documentos adjuntos
          echo incrustar_objeto("CROQUIS",$row["doc8"]);
          
      } else {
            echo campo("doc8","CROQUIS",'uploadlink',$row["doc8"],'class="form-control" ',$row["id"]);
      }
             echo "<hr>";
       //     echo campo("direccion","Direccion",'text',$row["direccion"],'class="form-control" ','','',3,7);
       //     echo campo("direccion_referencia","Puntos de Referencia",'text',$row["direccion_referencia"],'class="form-control" ','','',3,7);
       //     echo campo("direccion_poste","Poste",'text',$row["direccion_poste"],'class="form-control" ','','',3,4);
            
           
            echo  campo("vivienda","Vivienda",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="Propia">Propia</option><option value="Alquilada">Alquilada</option><option value="Familiar">Familiar</option><option value="Empresa">Empresa</option>',$row["vivienda"]),'class="form-control" ','','',3,3);
  
            echo  campo("vivienda_tiempo","Tiempo de residir en a&ntilde;os",'text',$row["vivienda_tiempo"],'class="form-control" ','','',3,3);
            
            echo  campo("vivienda_condicion","Condiciones de Construccion",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="Excelente">Excelente</option><option value="Buena">Buena</option><option value="Regular">Regular</option><option value="Malas">Malas</option>',$row["vivienda_condicion"]),'class="form-control" ','','',3,3);
           
                    
           echo  campo("vivienda_tipo","Tipo de Domicilio",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="Casa">Casa</option><option value="Apartamentos">Apartamentos</option><option value="Cuartearia">Cuartearia</option><option value="Otros">Otros</option>',$row["vivienda_tipo"]),'class="form-control" ','','',3,3);
            echo  campo("vivienda_construccion","Tipo Construccion",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="Bloque">Bloque</option><option value="Ladrillo">Ladrillo</option><option value="Madera">Madera</option><option value="Adobe">Adobe</option><option value="Lamina">Lamina</option><option value="Carton">Carton</option><option value="Otros">Otros</option>',$row["vivienda_construccion"]),'class="form-control" ','','',3,3);
            echo campo("vivienda_construccion_obs","Comentarios",'textarea',$row["vivienda_construccion_obs"],'class="form-control" ','','',3,7);
 
  
   echo  campo("vivienda_vecino1","Referencias Vecino 1",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="Excelentes">Excelentes</option><option value="Buenas">Buenas</option><option value="Regulares">Regulares</option><option value="Malas">Malas</option><option value="No los conocen">No los conocen</option>',$row["vivienda_vecino1"]),'class="form-control" ','','',3,4);
   echo campo("vivienda_vecino1_obs","Comentarios Vecino 1",'textarea',$row["vivienda_vecino1_obs"],'class="form-control" ','','',3,7);
 
    echo  campo("vivienda_vecino2","Referencias Vecino 2",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="Excelentes">Excelentes</option><option value="Buenas">Buenas</option><option value="Regulares">Regulares</option><option value="Malas">Malas</option><option value="No los conocen">No los conocen</option>',$row["vivienda_vecino2"]),'class="form-control" ','','',3,4);
   echo campo("vivienda_vecino2_obs","Comentarios Vecino 2",'textarea',$row["vivienda_vecino2_obs"],'class="form-control" ','','',3,7);
   echo "<hr>";
  
   
    echo campo("vivienda_comentarios","Comentarios Adicionales",'textarea',$row["vivienda_vecino1_obs"],'class="form-control" ','','',3,7);
     echo  campo("vivienda_recomienda","Recomendacion",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="Aprobar">Aprobar</option><option value="Condicionar">Condicionar</option><option value="Rechazar">Rechazar</option>',$row["vivienda_recomienda"]),'class="form-control" ','','',3,4);
     echo "<hr>";
  
   echo campo("direccion_gps","<a href=\"#\" onclick=\"ubicacion_gps() ; return false;\"   class=\"btn btn-info\"><span class=\"glyphicon glyphicon-map-marker\" aria-hidden=\"true\"></span> Ubicacion GPS</a> <br><br> <a href=\"#\" onclick=\"abrir_mapa($('#direccion_gps').val()) ; return false;\"   class=\"btn btn-default\"><span class=\"glyphicon glyphicon-globe\" aria-hidden=\"true\"></span> Ver Mapa</a>",'text',$row["direccion_gps"],'class="form-control" ','','',3,7);
       
         echo "<div id=\"GeoAPI\" style=\"display: none;\"></div>" ;
       
       
       if ($mostrar_todo==true) {
          //desplegar documentos adjuntos
          echo incrustar_objeto("Foto",$row["doc_foto1"]);
          echo incrustar_objeto("Foto",$row["doc_foto2"]);
          echo incrustar_objeto("Foto",$row["doc_foto3"]);
          echo incrustar_objeto("Foto",$row["doc_foto4"]);
          echo incrustar_objeto("Foto",$row["doc_foto5"]);
          echo incrustar_objeto("Foto",$row["doc_foto6"]);
          
      } else {                                                                                                                
            echo "<hr>";
            echo campo_upload("doc_foto1","FOTO 1",'uploadlink',$row["doc_foto1"],'class="form-control" ',$row["id"],0,12,"SI");
            echo "<hr>";
             echo campo_upload("doc_foto2","FOTO 2",'uploadlink',$row["doc_foto2"],'class="form-control" ',$row["id"],0,12,"SI");
            echo "<hr>";
             echo campo_upload("doc_foto3","FOTO 3",'uploadlink',$row["doc_foto3"],'class="form-control" ',$row["id"],0,12,"SI");
            echo "<hr>";
             echo campo_upload("doc_foto4","FOTO 4",'uploadlink',$row["doc_foto4"],'class="form-control" ',$row["id"],0,12,"SI");
            echo "<hr>";
            echo campo_upload("doc_foto5","FOTO 5",'uploadlink',$row["doc_foto5"],'class="form-control" ',$row["id"],0,12,"SI");
            echo "<hr>";
             echo campo_upload("doc_foto6","FOTO 6",'uploadlink',$row["doc_foto6"],'class="form-control" ',$row["id"],0,12,"SI");
            echo "<hr>";
            }  
             echo "<H4>Verificacion Laboral</H4>";
              echo "<hr>";
            echo "".$row["nombres"]." ".$row["apellidos"]."<br>";
             
            echo "".$row["empresa"]."<br>"; 
            echo "".$row["empresa_direccion"]."<br>"; 
            echo "".$row["empresa_telefono"]." ".$row["empresa_telefono2"]."<br>";
             echo "<hr>";
       //     echo campo("direccion","Direccion",'text',$row["direccion"],'class="form-control" ','','',3,7);
      //   echo campo("direccion","Nombre del Trabajo o Negocio",'text',$row["direccion"],'class="form-control" ','','',3,7);
         echo  campo("negocio_rotulo","Tiene Rotulo",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="Si">Si</option><option value="No">No</option>',$row["negocio_rotulo"]),'class="form-control" ','','',3,3);
        
       // echo  campo("rotulo","Relacion del Trabajo",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="Propio">Propio</option><option value="Empleado">Empleado</option>',$row["rotulo"]),'class="form-control" ','','',3,3);
        if ($row["empresa_tipo_empleo"] =='ASALARIADO') {
             echo "<hr>";
             echo "<strong>Empleado</strong><br>" ;
             echo campo("empleado_patrono","Patrono",'text',$row["empleado_patrono"],'class="form-control" ','','',3,7);
             echo campo("empleado_antiguedad","Antiguedad",'text',$row["empleado_antiguedad"],'class="form-control" ','','',3,3); 
            echo  campo("empleado_ingreso","Salario Mensual L.",'text',$row["empleado_ingreso"],'class="form-control" ','','',3,3);
            // echo  campo("empleado_ingreso","Salario Quincenal L.",'text',$row["empleado_ingreso"],'class="form-control" ','','',3,3);
            // echo  campo("empleado_ingreso","Salario Semanal L.",'text',$row["empleado_ingreso"],'class="form-control" ','','',3,3);
            // echo  campo("empleado_ingreso","Otros Ingresos L.",'text',$row["empleado_ingreso"],'class="form-control" ','','',3,3);
//             
            
             echo  campo("empleado_tipo","Tipo Empleo",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="Permanente">Permanente</option><option value="Temporal">Temporal</option><option value="Contrato">Contrato</option><option value="Otro">Otro</option>',$row["empleado_tipo"]),'class="form-control" ','','',3,3);
             echo  campo("empleado_tipo_empresa","Tipo de empresa",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="Servicios">Servicios</option><option value="Industria">Industria</option><option value="Comercio">Comercio</option><option value="Gobierno">Gobierno</option>',$row["empleado_tipo_empresa"]),'class="form-control" ','','',3,3);
            echo  campo("empleado_prestamo","Prestamos con la Empresa L.",'text',$row["empleado_prestamo"],'class="form-control" ','','',3,3);
            echo  campo("empleado_prestamo_telefono","Telefono",'text',$row["empleado_prestamo_telefono"],'class="form-control" ','','',3,3);
            
              echo  campo("empleado_referencia","Referencia Compa&ntilde;eros o vecinos",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="Excelentes">Excelentes</option><option value="Buenas">Buenas</option><option value="Regulares">Regulares</option><option value="Malas">Malas</option><option value="No los conocen">No los conocen</option>',$row["empleado_referencia"]),'class="form-control" ','','',3,4);
            echo campo("empleado_obs","Comentarios",'textarea',$row["empleado_obs"],'class="form-control" ','','',3,7);
           
            
        }  ELSE {
            
        
            echo "<hr>";
             echo "<strong>Negocio Propio</strong><br>";  
            echo  campo("negocio_tipo","Negocio Propio",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="Propietario">Propietario</option><option value="Socio">Socio</option><option value="Familiar Informal">Familiar Informal</option>',$row["negocio_tipo"]),'class="form-control" ','','',3,3);
            echo  campo("negocio_permiso","Permiso de Operacion",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="Si">Si</option><option value="No">No</option>',$row["negocio_permiso"]),'class="form-control" ','','',3,3);
          echo  campo("negocio_tamano","Tama&ntilde;o",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="Grande">Grande</option><option value="Mediano">Mediano</option><option value="Peque&ntilde;o">Peque&ntilde;o</option>',$row["negocio_tamano"]),'class="form-control" ','','',3,3);
           echo  campo("negocio_tiempo","Tiempo de Operar",'text',$row["negocio_tiempo"],'class="form-control" ','','',3,3);
            
            echo  campo("negocio_condicion","Condiciones de Construccion",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="Excelente">Excelente</option><option value="Buena">Buena</option><option value="Regular">Regular</option><option value="Malas">Malas</option>',$row["negocio_condicion"]),'class="form-control" ','','',3,3);
            echo  campo("negocio_afluencia","Afluencia de clientes",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="Alta">Alta</option><option value="Media">Media</option><option value="Baja">Baja</option><option value="Otros">Otros</option>',$row["negocio_afluencia"]),'class="form-control" ','','',3,3);
            echo  campo("negocio_empleados","Numero de empleados",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="1-2">1-2</option><option value="3-5">3-5</option><option value="5-10">5-10</option><option value="mas de 10">mas de 10</option>',$row["negocio_empleados"]),'class="form-control" ','','',3,3);
            echo  campo("negocio_ubicacion","Zona de ubicacion",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="En Domicilio">En Domicilio</option><option value="Mercado">Mercado</option><option value="Centro Comercial">Centro Comercial</option><option value="Ambulante">Ambulante</option><option value="Industria">Industria</option><option value="Indistinta">Indistinta</option><option value="Especifique">Especifique</option>',$row["negocio_ubicacion"]),'class="form-control" ','','',3,3);
            
            // echo  campo("negocio_ingreso","Ingreso Diario L.",'text',$row["negocio_ingreso"],'class="form-control" ','','',3,3);
            // echo  campo("negocio_ingreso","Ingreso Semanal L.",'text',$row["negocio_ingreso"],'class="form-control" ','','',3,3);
            // echo  campo("negocio_ingreso","Ingreso Quincenal L.",'text',$row["negocio_ingreso"],'class="form-control" ','','',3,3);
            echo  campo("negocio_ingreso","Ingreso Mensual L.",'text',$row["negocio_ingreso"],'class="form-control" ','','',3,3);
            
              
            echo  campo("negocio_referencia","Referencias Vecino 2",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="Excelentes">Excelentes</option><option value="Buenas">Buenas</option><option value="Regulares">Regulares</option><option value="Malas">Malas</option><option value="No los conocen">No los conocen</option>',$row["negocio_referencia"]),'class="form-control" ','','',3,4);
            echo campo("negocio_obs","Comentarios",'textarea',$row["negocio_obs"],'class="form-control" ','','',3,7);
            }  
            
         if ($mostrar_todo==true) {
          //desplegar documentos adjuntos
          echo incrustar_objeto("Foto",$row["doc_foto7"]);
          echo incrustar_objeto("Foto",$row["doc_foto8"]);
          echo incrustar_objeto("Foto",$row["doc_foto9"]);
          echo incrustar_objeto("Foto",$row["doc_foto10"]);
          echo incrustar_objeto("Foto",$row["doc_foto11"]);
          echo incrustar_objeto("Foto",$row["doc_foto12"]);
          
      } else {    
            echo "<hr>";
            echo campo_upload("doc_foto7","FOTO 1",'uploadlink',$row["doc_foto7"],'class="form-control" ',$row["id"],0,12,"SI");
            echo "<hr>";
            echo campo_upload("doc_foto8","FOTO 2",'uploadlink',$row["doc_foto8"],'class="form-control" ',$row["id"],0,12,"SI");
            echo "<hr>";
            echo campo_upload("doc_foto9","FOTO 3",'uploadlink',$row["doc_foto9"],'class="form-control" ',$row["id"],0,12,"SI");
            echo "<hr>";
            echo campo_upload("doc_foto10","FOTO 4",'uploadlink',$row["doc_foto10"],'class="form-control" ',$row["id"],0,12,"SI");
            echo "<hr>";
            echo campo_upload("doc_foto11","FOTO 5",'uploadlink',$row["doc_foto11"],'class="form-control" ',$row["id"],0,12,"SI");
            echo "<hr>";
            echo campo_upload("doc_foto12","FOTO 6",'uploadlink',$row["doc_foto12"],'class="form-control" ',$row["id"],0,12,"SI");
          if ($campo_unico<>"" ) {
              //  echo "<hr>";
            // echo '<div id="botones"><a id="btnguardar" href="#" class="btn btn-primary" onclick="procesar_datos_gestion(\'creditos_gestion.php?a=5z&s=1&geid='.$_REQUEST['geid'].'&cid='.$solicitud_id.'\','.$forma.'); return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar</a></div>';
             }
          echo "<hr>";
          
          } 
          
             echo '</div>';
               if ($campo_unico<>""  and $mostrar_todo==false)  //que el vendedor tambien la pueda modificar despues
       {
            echo "<hr>";
            echo '<a id="Guardar'.$forma.'" href="#" class="btn btn-primary" onclick="procesar_datos_gestion(\'creditos_gestion.php?a=5g6&s=1&cid='.$solicitud_id.'\','.$forma.'); return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar (Parcial)</a>';
            echo '<div id="respuesta'.$forma.'"> </div>';
            } 
             
        }

        if (tiene_permiso(19) and $mostrar_todo==false)  //que el vendedor tambien la pueda modificar
       {
            echo "<hr>";
            echo '<a id="Guardar'.$forma.'" href="#" class="btn btn-primary" onclick="procesar_datos_gestion(\'creditos_gestion.php?a=5g6&s=1&cid='.$solicitud_id.'\','.$forma.'); return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar</a>';
            echo '<div id="respuesta'.$forma.'"> </div>';
            } 
        
        }
            else { echo mensaje( "No se encontraron registros","info"); exit;}

    }   




  if ($cod_status=="7") { //CALCULO FINANCIERO
  
    $sql="SELECT *
    FROM prestamo
    where id=$solicitud_id";
    
      $result = $conn -> query($sql);

      if ($result -> num_rows > 0) {                  
                $row = $result -> fetch_assoc();


 if ($campo_unico==""  or $mostrar_todo) {
      
           if ($row["aprobado_gerencia_usuario"]<>""){
            echo boton_verificar(701,$solicitud_id,false,'',$asignados,$campo_unico);
           
           }
           
      
            echo '  <div class="row">';
             echo '  <div ><div > <div class="form">';
            
            echo "<hr>";
               echo campo("monto_prestamo","Valor Motocicleta",'text',$row["monto_prestamo"],'class="form-control" onchange="calculos_financieros4(); " ','','',3,3);  
     echo campo("monto_seguro","Valor del Seguro",'text',$row["monto_seguro"],'class="form-control"  onchange="calculos_financieros4(); " ','','',3,3);     
     echo campo("monto_prima","Prima",'text',$row["monto_prima"],'class="form-control"  onchange="calculos_financieros4();"','','',3,3);
     
     echo '<div id="amort" >' ;
      echo campo("monto_financiar","Total Financiar",'text',$row["monto_financiar"],'class=" form-control"  readonly','','',3,3);   
      echo campo("plazo","Plazo",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="6">6</option><option value="12">12</option><option value="18">18</option><option value="24">24</option><option value="30">30</option><option value="36">36</option>',$row["plazo"]),'class=" form-control" onchange="calculos_financieros4(); "','','',3,2);
      echo campo("tasa","Tasa",'text',$row["tasa"],'class=" form-control"  onchange="calculos_financieros4(); "','','',3,2);
     echo '</div>' ;       
            
            echo "<hr>";
             echo "<h4>CALCULO RELACION CUOTA INGRESO</h4>";
             echo campo("cuota","Cuota Mensual",'text',$row["cuota"],'class="form-control" onchange="calculos_financieros2();" readonly','','',3,2);
              echo campo("endeuda_sueldo_requerido","Sueldo Requerido",'text',$row["endeuda_sueldo_requerido"],'class="form-control "  readonly','','',3,3);
            
            echo "<hr>";
             echo "<h4>NIVEL DE ENDEUDAMIENTO</h4>";

 
            echo campo("endeuda_sueldo","Sueldo Neto",'text',$row["endeuda_sueldo"],'class="form-control"  onchange="calculos_financieros1();  " ','','',3,3);
            echo campo("endeuda_tarjeta","Tarjeta de Credito",'text',$row["endeuda_tarjeta"],'class="form-control" onchange="calculos_financieros1();  " ','','',3,3);
            echo campo("endeuda_prestamo","Prestamo",'text',$row["endeuda_prestamo"],'class="form-control" onchange="calculos_financieros1();  " ','','',3,3);
            echo campo("endeuda_cooperativa","Cooperativa",'text',$row["endeuda_cooperativa"],'class="form-control" onchange="calculos_financieros1();  " ','','',3,3);
            echo campo("endeuda_movesa","Prestamo Movesa",'text',$row["cuota"],'class="form-control"  readonly','','',3,3);
            
            echo campo("endeuda_otros","Otros",'text',$row["endeuda_otros"],'class="form-control" onchange="calculos_financieros1();  " ','','',3,3);
            
            
            echo campo("endeuda_total","Total Obligaciones",'text',$row["endeuda_total"],'class="form-control"  readonly','','',3,3);          
            echo campo("endeuda_nivel","Nivel de Endeudamiento",'text',$row["endeuda_nivel"],'class="form-control"  readonly','','',3,3);
            
           
            
            if (tiene_permiso(19) and !$mostrar_todo) {
            echo "<hr>";
            echo '<a id="Guardar'.$forma.'" href="#" class="btn btn-primary" onclick="procesar_datos_gestion(\'creditos_gestion.php?a=5b1ss&s=1&cid='.$solicitud_id.'\','.$forma.'); return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar</a>';
            if (es_nulo($row["aprobado_gerencia_usuario"])){echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a id="AutorizarGes'.$forma.'" href="#" class="btn btn-info" onclick="procesar_datos_gestion(\'creditos_gestion.php?a=5g7&s=1&cid='.$solicitud_id.'\','.$forma.'); $(this).hide(); return false;"> Solicitar Aprobacion de Gerente</a>';}
            
            
            echo '<div id="respuesta'.$forma.'"> </div>';
            }  
            
            echo "<hr>";
            echo "<h4>CALCULO CUOTA NIVELADA Y PLAN DE AMORTIZACION</h4>";
             echo '                  
                     <div id="amSchedule" >    </div>
                <div class="clear"></div>
           ';
            
               echo "<hr>";
             echo ' </div></div></div>';
             echo '</div>';
             
             
 
            
       echo '       <script>
            $(document).ready(function(){

      

    $(function () {
        showValues();
       // $("#amort").change(function () {
       //     showValues();
       // });
    })
            
            });
            </script> 
           
  ';
            
            } // if  if ($campo_unico=="" ) {
                    
      if ( $mostrar_todo and tiene_permiso(25)) {
      
          // if (es_nulo($row["aprobado_gerencia_usuario"])){
              // echo '<a id="AutorizarGes'.$forma.'" href="#" class="btn btn-success" onclick="procesar_datos_gestion(\'creditos_gestion.php?a=5g7&s=2&cid='.$solicitud_id.'\','.$forma.'); $(this).hide(); return false;"> Comentarios de Gerente</a>';
//             
            // }
          
        //  if (es_nulo($row["aprobado_gerencia_usuario"])){echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a id="AutorizarGes2'.$forma.'" href="#" class="btn btn-danger" onclick="procesar_datos_gestion(\'creditos_gestion.php?a=5g7&s=3&cid='.$solicitud_id.'\','.$forma.'); $(this).hide(); return false;"> Marcar como rechazado por Gerente</a>';}
         // echo '<div id="respuesta'.$forma.'"> </div>';  
         
          echo '<br><div class="well">';
    echo '<div class="row"><div class="col-xs-12"> <form id="forma'.$forma.'" class="form-horizontal" >';

   echo campo("texto_responde","Respuesta",'textarea','','class="form-control" rows="5"','','',2,9);
    echo '<a id="Guardar'.$forma.'" href="#" class="btn btn-primary" onclick="procesar_datos_gestion(\'creditos_gestion.php?a=5g7&s=2&geid='.$_REQUEST['geid'].'&cid='.$solicitud_id.'\','.$forma.'); return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar</a>';
 
    echo '<div id="respuesta'.$forma.'"> </div>';
  
   echo " </form></div></div></div><hr>";
   
      }          
        
        }
            else { echo mensaje( "No se encontraron registros","info"); exit;}
  
  
  
  
  }
  


  if ($cod_status=="8" ) { //CONDICIONES DE APROBACION
        
     $sql="SELECT *
    FROM prestamo
    where id=$solicitud_id";
    
      $result = $conn -> query($sql);

      if ($result -> num_rows > 0) {                  
                $row = $result -> fetch_assoc();

            echo '  <div class="row">';
            
            if (isset($asignados[801])) {
            echo boton_verificar(801,$solicitud_id,false,$row["usuario_alta"],$asignados,$campo_unico) ;
           // echo "<hr>";
          }
            
        if ($campo_unico=="801"  or $mostrar_todo) {
            echo "<h4>Notificacion de Condiciones de aprobacion</h4>";
            echo boton_verificar(801,$solicitud_id,false,'',$asignados,$campo_unico);
            
            echo "<hr>";
            
               echo campo("monto_prestamo","Valor Motocicleta",'label',$row["monto_prestamo"],'class="form-control" onchange="$(\'#monto_financiar\').val(convertir_num($(\'#monto_prestamo\').val())+convertir_num($(\'#monto_seguro\').val())-convertir_num($(\'#monto_prima\').val())); " ','','',3,3);  
     echo campo("monto_seguro","Valor del Seguro",'label',$row["monto_seguro"],'class="form-control"  onchange="$(\'#monto_financiar\').val(convertir_num($(\'#monto_prestamo\').val())+convertir_num($(\'#monto_seguro\').val())-convertir_num($(\'#monto_prima\').val())); " ','','',3,3);     
     echo campo("monto_prima","Prima",'label',$row["monto_prima"],'class="form-control"  onchange="$(\'#monto_financiar\').val(convertir_num($(\'#monto_prestamo\').val())+convertir_num($(\'#monto_seguro\').val())-convertir_num($(\'#monto_prima\').val())); "','','',3,3);
      
      echo campo("monto_financiar","Total Financiar",'label',$row["monto_financiar"],'class="form-control"  readonly','','',3,3);

           
            echo campo("plazo","Plazo",'label',$row["plazo"],'class="form-control" ','','',3,2);
            echo campo("tasa","Tasa",'label',$row["tasa"],'class="form-control"  ','','',3,2);
            echo campo("cuota","Letra",'label',$row["cuota"],'class="form-control"  ','','',3,2);
        }     
            
       if ($campo_unico=="" ) {      
            echo "<hr>";
            // echo campo("monto_prestamo","Valor Motocicleta",'text',$row["monto_prestamo"],'class="form-control" onchange="$(\'#monto_financiar\').val(convertir_num($(\'#monto_prestamo\').val())+convertir_num($(\'#monto_seguro\').val())-convertir_num($(\'#monto_prima\').val())); " ','','',3,3);  
            // echo campo("monto_seguro","Valor del Seguro",'text',$row["monto_seguro"],'class="form-control"  onchange="$(\'#monto_financiar\').val(convertir_num($(\'#monto_prestamo\').val())+convertir_num($(\'#monto_seguro\').val())-convertir_num($(\'#monto_prima\').val())); " ','','',3,3);     
            // echo campo("monto_prima","Prima",'text',$row["monto_prima"],'class="form-control"  onchange="$(\'#monto_financiar\').val(convertir_num($(\'#monto_prestamo\').val())+convertir_num($(\'#monto_seguro\').val())-convertir_num($(\'#monto_prima\').val())); "','','',3,3);
            // echo campo("monto_financiar","Total Financiar",'text',$row["monto_financiar"],'class="form-control"  readonly','','',3,3);
            // echo campo("plazo","Plazo",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="6">6</option><option value="12">12</option><option value="18">18</option><option value="24">24</option><option value="30">30</option><option value="36">36</option>',$row["plazo"]),'class="form-control" ','','',3,2);
            // echo campo("tasa","Tasa",'text',$row["tasa"],'class="form-control"  ','','',3,2);
            // echo campo("cuota","Letra",'text',$row["cuota"],'class="form-control"  ','','',3,2);
            
               echo campo("monto_prestamo","Valor Motocicleta",'text',$row["monto_prestamo"],'class="form-control" onchange="calculos_financieros4(); " ','','',3,3);  
     echo campo("monto_seguro","Valor del Seguro",'text',$row["monto_seguro"],'class="form-control"  onchange="calculos_financieros4(); " ','','',3,3);     
     echo campo("monto_prima","Prima",'text',$row["monto_prima"],'class="form-control"  onchange="calculos_financieros4();"','','',3,3);
     
     echo '<div id="amort" >' ;
      echo campo("monto_financiar","Total Financiar",'text',$row["monto_financiar"],'class=" form-control"  readonly','','',3,3);   
      echo campo("plazo","Plazo",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="6">6</option><option value="12">12</option><option value="18">18</option><option value="24">24</option><option value="30">30</option><option value="36">36</option>',$row["plazo"]),'class=" form-control" onchange="calculos_financieros4(); "','','',3,2);
      echo campo("tasa","Tasa",'text',$row["tasa"],'class=" form-control"  onchange="calculos_financieros4(); "','','',3,2);
     echo '</div>' ;       
            

     echo campo("cuota","Cuota Mensual",'text',$row["cuota"],'class="form-control" onchange="calculos_financieros2();" readonly','','',3,2);
              
        echo '<div id="endeuda2" style="display:none">' ;     
            echo campo("endeuda_sueldo_requerido","Sueldo Requerido",'text',$row["endeuda_sueldo_requerido"],'class="form-control "  readonly','','',3,3);
            echo campo("endeuda_sueldo","Sueldo Neto",'text',$row["endeuda_sueldo"],'class="form-control"  onchange="calculos_financieros1();  " ','','',3,3);
            echo campo("endeuda_tarjeta","Tarjeta de Credito",'text',$row["endeuda_tarjeta"],'class="form-control" onchange="calculos_financieros1();  " ','','',3,3);
            echo campo("endeuda_prestamo","Prestamo",'text',$row["endeuda_prestamo"],'class="form-control" onchange="calculos_financieros1();  " ','','',3,3);
            echo campo("endeuda_cooperativa","Cooperativa",'text',$row["endeuda_cooperativa"],'class="form-control" onchange="calculos_financieros1();  " ','','',3,3);
            echo campo("endeuda_movesa","Prestamo Movesa",'text',$row["cuota"],'class="form-control"  readonly','','',3,3);
            
            echo campo("endeuda_otros","Otros",'text',$row["endeuda_otros"],'class="form-control" onchange="calculos_financieros1();  " ','','',3,3);
            
            
            echo campo("endeuda_total","Total Obligaciones",'text',$row["endeuda_total"],'class="form-control"  readonly','','',3,3);          
            echo campo("endeuda_nivel","Nivel de Endeudamiento",'text',$row["endeuda_nivel"],'class="form-control"  readonly','','',3,3);

             echo '                  
                     <div id="amSchedule" >    </div>
                <div class="clear"></div>
           ';
            
         echo '</div>' ;  
          
       echo '       <script>
            $(document).ready(function(){

    $(function () {
        showValues();
       // $("#amort").change(function () {
       //     showValues();
       // });
    })
            
            });
            </script> 
           
  '; 
            
            
            
  if (tiene_permiso(19)) {
            echo "<hr>";
            echo '<a id="Guardar'.$forma.'" href="#" class="btn btn-primary" onclick="procesar_datos_gestion(\'creditos_gestion.php?a=5g8&s=1&cid='.$solicitud_id.'\','.$forma.'); return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar y Notificar a Vendedor</a>';
            echo '<div id="respuesta'.$forma.'"> </div>';
            } 

}      
            echo "<hr>";
             echo '</div>';
      
        
        }
            else { echo mensaje( "No se encontraron registros","info"); exit;}

 } 



if ($cod_status=="9") { //IMPRESION DE DOCUMENTOS LEGALES
  
    $sql="SELECT *
    FROM prestamo
    where id=$solicitud_id";
    
      $result = $conn -> query($sql);

      if ($result -> num_rows > 0) {                  
                $row = $result -> fetch_assoc();
          
          
  
   echo '  <div class="row">';
            
            echo "<hr>";
            // imprimir contrato

             if ($row["cierre_contrato"]<>0) {
             echo campo("cierre_contrato","No. Contrato",'label',$row["cierre_contrato"],'class="form-control"  ','','',3,4);
            
           if (tiene_permiso(24)) {  
            echo '<a id="btnimp_1'.$forma.'" href="#" class="btn btn-default" onclick="imprimir_contratos('.$solicitud_id.'); return false;"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Imprimir Documentos</a>';
           } else {         echo Mensaje("No tiene permisos para Imprimir Contrato","info");        }
           echo "<hr>";
           }
           
         if (isset($asignados[901])) {
            echo boton_verificar(901,$solicitud_id,false,$row["usuario_alta"],$asignados,$campo_unico) ;
            echo "<hr>";
          }
         
         
          if ($campo_unico=="" or $campo_unico==902) {
              
                echo '<div class=\"row\"><div id="motospanel"></div>';
                echo '<a href="#" class="btn btn-default" onclick="actualizarbox(\'motospanel\',\'get.php?a=11&sub=1&sb=&creditos=1\'); return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Seleccionar Serie</a><br><br>';
                echo "</div>" ;
               
               echo "<div class=\"row\"><div class=\"col-xs-8\">"."<h4>Datos de la Moto</h4>"."</div></div><hr>";
          echo "<div class=\"row\"><div class=\"col-xs-8\">";
          
            
           echo campo("moto_serie","Serie",'text',$row["moto_serie"],'class="form-control"  ','','',3,8);
        //   echo campo("moto_tipo","Tipo",'text',$row["moto_tipo"],'class="form-control"  ','','',3,8);
           echo campo("moto_marca","Marca",'text',$row["moto_marca"],'class="form-control"  ','','',3,8);
           echo campo("moto_modelo","Modelo",'text',$row["moto_modelo"],'class="form-control"  ','','',3,8);
           echo campo("moto_motor","Motor",'text',$row["moto_motor"],'class="form-control"  ','','',3,8);
           echo campo("moto_color","Color",'text',$row["moto_color"],'class="form-control"  ','','',3,5);
           echo campo("moto_ano","A&ntilde;o",'text',$row["moto_ano"],'class="form-control"  ','','',3,5);
           echo campo("moto_cilindraje","Cilindraje",'text',$row["moto_cilindraje"],'class="form-control"  ','','',3,5);
           echo campo("moto_valor","Valor en LPS",'text',$row["moto_valor"],'class="form-control"  ','','',3,8);
          
          if ((tiene_permiso(18) or tiene_permiso(22)) and $row["moto_serie"]=="") { //vendedor o jefe de tienda
    
            echo '<a id="Guardar'.$forma.'" href="#" class="btn btn-primary" onclick="procesar_datos_gestion(\'creditos_gestion.php?a=5b1ss&s=1&cid='.$solicitud_id.'\','.$forma.'); return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar</a>';
            echo '<div id="respuesta'.$forma.'"> </div>'; 
            //echo "<hr>"; 
          }
          
         echo "</div><div class=\"col-xs-4\">".boton_verificar(902,$solicitud_id,true,$row["usuario_alta"],$asignados,$campo_unico)."</div></div>";
 
  } 
         
         
                    

            // generar contrato
            
                    if (tiene_permiso(23)) {
                        
                       
                        
                   //       echo '<div class="row"><div class="col-xs-12"> <form id="forma2'.$forma.'" class="form-horizontal" >';
                          
echo "<h4>Datos del contrato</h4><hr>";


                            
                          // echo campo("cierre_plazo","Plazo",'text',$v_plazo,'class="form-control"  ','','',3,3);
                        //  echo campo("cierre_interes_mensual","Interes Mensual",'text',$row["cierre_interes_mensual"],'class="form-control"  ','','',3,3);
                          // echo campo("cierre_total_usd","Total ",'text',$row["cierre_total_usd"],'class="form-control"  ','','',3,3);
                          // echo campo("cierre_total_usd_contado","Total Contado",'text',$row["cierre_total_usd_contado"],'class="form-control"  ','','',3,3);
                          // echo campo("cierre_total_seguro_usd","Seguro",'text',$row["cierre_total_seguro_usd"],'class="form-control"  ','','',3,3);
                          // echo campo("cierre_total_prima_usd","Prima",'text',$row["cierre_total_prima_usd"],'class="form-control"  ','','',3,3);
                          // echo campo("cierre_cuota_cantidad","# Cuotas",'text',$row["cierre_cuota_cantidad"],'class="form-control"  ','','',3,3);
                          // echo campo("cierre_cuota_total_usd","Cuota",'text',$row["cierre_cuota_total_usd"],'class="form-control"  ','','',3,3);
                         
                          echo campo("cierre_cuota_dia_pago","Dia Pago Cuota",'text',$row["cierre_cuota_dia_pago"],'class="form-control"  ','','',3,3);
                          
                          echo campo("cierre_cuota_primera","Fecha de Primera Cuota",'date',fechademysql($row["cierre_cuota_primera"]),'class="form-control" ','','',3,3);
                          echo campo("cierre_cuota_final","Fecha de Ultima Cuota",'date',fechademysql($row["cierre_cuota_final"]),'class="form-control" ','','',3,3);
                          echo campo("cierre_firma_fecha","Fecha de Firma Contrato",'date',fechademysql($row["cierre_firma_fecha"]),'class="form-control" ','','',3,3);
 
                          
                          echo '<a id="Guardar'.$forma.'" href="#" class="btn btn-primary" onclick="procesar_datos_contrato(\'creditos_gestion.php?a=5g9&cid='.$solicitud_id.'\','.$forma.'); return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar Contrato</a>';
                          echo '<div id="respuesta'.$forma.'"> </div>';
                           if (tiene_permiso(24)) {  
                            echo '<div style="display: none;" id="respboton'.$forma.'"><a id="btnimp_1'.$forma.'" href="#" class="btn btn-default" onclick="imprimir_contratos('.$solicitud_id.'); return false;"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Imprimir Documentos</a></div>';
                           }
                  //        echo " </form></div></div>";
                        
                    }      
           
            
      
           
            echo "<hr>";
             echo '</div>';
  
   }
            else { echo mensaje( "No se encontraron registros","info"); exit;}
  }

if ($cod_status=="10") { //FIRMA DE CONTRATO

     $sql="SELECT *
    FROM prestamo
    where id=$solicitud_id";
    
      $result = $conn -> query($sql);

      if ($result -> num_rows > 0) {                  
                $row = $result -> fetch_assoc();

    echo ' <div class="row">';

    
            if (es_nulo($row["cierre_documentos_enviados_gestion"])) {
                if (tiene_permiso(22)) {  
                 echo '<a id="gestionbtn'.$forma.'" href="#" class="btn btn-primary" onclick="procesar_gestion10(\'creditos_gestion.php?a=5g10&cid='.$solicitud_id.'\','.$forma.'); return false;"><span class="glyphicon glyphicon-road" aria-hidden="true"></span> Crear gestion para enviar documentos</a>';
                echo '<div id="gestionrespuesta'.$forma.'"> </div>';
                }
            } else {
                 if ($campo_unico=="" or $campo_unico==1001) {     
                    echo "<hr><div class=\"row\"><div class=\"col-xs-8\">"."Numero Guia de Envio<br>".campo("cierre_documentos_tracking","",'text',$row["cierre_documentos_tracking"],'class="form-control"  ','','',3,8)."</div><div class=\"col-xs-4\">".boton_verificar(1001,$solicitud_id,false,$row["usuario_alta"],$asignados,$campo_unico)."</div></div>";
                    //echo "<div class=\"row\"><div class=\"col-xs-8\">".campo("cierre_documentos_tracking","",'text',$row["cierre_documentos_tracking"],'class="form-control"  ','','',3,8)."</div></div><br><br>";
                     echo "<hr>";           
                   //  echo campo("doc_contrato","Contrato Firmado",'upload',$row["doc_contrato"],'class="form-control" ',$row["id"]);
                  echo  campo_upload("doc_contrato","Contrato Firmado",'upload',$row["doc_contrato"],'class="form-control" ',$row["id"],0,12,"SI");
                  
                  echo  campo_upload("doc_foto_persona","Foto de la Persona",'upload',$row["doc_foto_persona"],'class="form-control" ',$row["id"],0, 12,"SI");
              }
                
            }
            
           
            
            echo "<hr>";
             echo '</div>';
             
              }
            else { echo mensaje( "No se encontraron registros","info"); exit;}
  
  }

if ($cod_status=="11") { //CIERRE

  $sql="SELECT *
    FROM prestamo
    where id=$solicitud_id";
    
      $result = $conn -> query($sql);

      if ($result -> num_rows > 0) {                  
                $row = $result -> fetch_assoc();

    echo ' <div class="row">';
            echo "<hr>";
           echo  campo("cierre_razon","Razon de Cierre",'select',valores_combobox_db( 'prestamo_cierre',$row["cierre_razon"],"nombre as texto"," order by nombre",'texto','Seleccione'),'class="form-control" ','','',3,6); 
           echo campo("cierre_factura","Numero de Factura",'text',$row["cierre_factura"],'class="form-control"  ','','',3,5); 
  if (tiene_permiso(19)) {
            echo "<hr>";
            echo '<a id="Guardar'.$forma.'" href="#" class="btn btn-primary" onclick="procesar_datos_gestion(\'creditos_gestion.php?a=5g11&s=1&cid='.$solicitud_id.'\','.$forma.'); return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar</a>';
            echo '<div id="respuesta'.$forma.'"> </div>';
            }  
            echo "<hr>";
             echo '</div>';
             
              }
            else { echo mensaje( "No se encontraron registros","info"); exit;}
  
  
  }

if ($cod_status=="12") { //RECIBIR DOCUMENTACION FISICA

 
  $sql="SELECT *
    FROM prestamo
    where id=$solicitud_id";
    
      $result = $conn -> query($sql);

      if ($result -> num_rows > 0) {                  
                $row = $result -> fetch_assoc();

    echo ' <div class="row">';
            echo "<hr>";
           
           echo  campo("cierre_documentos_recibidos","Documentos Recibidos",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="SI">SI</option><option value="NO">NO</option>',$row["cierre_documentos_recibidos"]),'class="form-control" ','','',3,3); 
           echo campo("cierre_documentos_recibidos_fecha","Fecha Recibido",'date',($row["cierre_documentos_recibidos_fecha"]),'class="form-control" ','','',3,3);
      if (tiene_permiso(19)) {
            echo "<hr>";
            echo '<a id="Guardar'.$forma.'" href="#" class="btn btn-primary" onclick="procesar_datos_gestion(\'creditos_gestion.php?a=5b1ss&s=1&cid='.$solicitud_id.'\','.$forma.'); return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar</a>';
            echo '<div id="respuesta'.$forma.'"> </div>';
            }  
            echo "<hr>";
            echo '</div>';
             
              }
            else { echo mensaje( "No se encontraron registros","info"); exit;}
   
  
  }




 
     echo '</form>'; 
      //---------------------------------- 
      //----------------------------------  
 if ($campo_unico<>"") {
     
     
     
if ($_REQUEST['gest']=='Vendedor') {     
      echo '<br><div class="well">';
    echo '<div class="row"><div class="col-xs-12"> <form id="forma'.$forma.'" class="form-horizontal" >';

   echo campo("texto_responde","Respuesta",'textarea','','class="form-control" rows="5"','','',2,9);
    echo '<a id="Guardar'.$forma.'" href="#" class="btn btn-primary" onclick="procesar_datos_gestion(\'creditos_gestion.php?a=5z&s=1&geid='.$_REQUEST['geid'].'&cid='.$solicitud_id.'\','.$forma.'); return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar y Notificar a Creditos</a>';
 
    echo '<div id="respuesta'.$forma.'"> </div>';
  
   echo " </form></div></div></div><hr>";
  }   


    
     
  

 }        
        
        
        
 if ($campo_unico=="") {        
        
   // ************* ver  historial gestiones
     //******* SQL ************************************************************************************
               
            $sql="SELECT prestamo_gestion.id, fecha, hora, usuario, etapa_id, descripcion 
            , prestamo_etapa.nombre as vetapa
            , prestamo_estatus.nombre as vestatus
                    FROM prestamo_gestion
                    LEFT OUTER JOIN prestamo_etapa ON (prestamo_etapa.id=prestamo_gestion.etapa_id) 
                    LEFT OUTER JOIN prestamo_estatus ON (prestamo_estatus.id=prestamo_gestion.estatus_id) 
                    where prestamo_id=$solicitud_id and etapa_id=$cod_status
                    and gestion_estado is null
                    order by prestamo_gestion.id";

            // ****** Fin SQL ********************************************************************************
            
             $result = $conn -> query($sql);


            if ($result -> num_rows > 0) {
               
                 
            echo '<BR><BR><HR><BR>
                      <table class="table table-striped"    >
                        <thead>
                          <tr>
                            <th class="text-center">Fecha</th>
                            <th class="text-center">Tipo Gestion</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Descripcion</th>
                            <th class="text-center">Usuario</th>

                          </tr>
                        </thead>
                        
                      
                        <tbody>';
              
     
                        while ($row = $result -> fetch_assoc()) {
                           echo "<tr>

    
                  
                              <td class='text-center'>".fechademysql($row["fecha"])." ".horademysql($row["hora"])."</td>
                               <td class='text-left'>".$row["vetapa"]."</td>
                                <td class='text-center'>".$row["vestatus"]."</td>
                               <td class='text-left'>".$row["descripcion"]."</td>
                               <td class='text-center'>".$row["usuario"]."</td>
                              
                      
                              </tr>"    ;
                      
                        }


                        
                 echo" </tbody>                   
                          </table>
                            <br>  ";
                    
                } 
    
   
   //******** nueava gestion ********************************************************
  if (tiene_permiso(19)) {  
    echo '<br><div class="well">';
    echo '<div id="nuevagestion" class="row"><div class="col-xs-12"> <form id="formagestion" class="form-horizontal" >';
        
        echo "<h4>Gestion</h4><br>";
                    
   


   echo campo("etapa_id","",'hidden',$cod_status,'');
   echo campo("descripcion","Descripcion",'textarea','','class="form-control" rows="6"','','',3,9);
   echo campo("estado","Estado",'select',valores_combobox_db( 'prestamo_estatus','','nombre','','',$texto_primera='Seleccione...' ),'class="form-control" ','','',3,4);
     
     
   
   echo '<div id="botones"><a id="btnguardar" href="#" class="btn btn-primary" onclick="procesarforma() ; return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar</a></div>
   <img id="cargando" style="display: none;" src="images/load.gif"/> ';
  
   echo " </form></div></div>";
 
    
    $siguienteb= intval($cod_status) +1;
    
     echo '<div id="salidagestion" class="row"><div class="col-xs-12"> 
     <br>  
     <div id="salida"> </div>
     <div id="siguientegestion" style="display: none;"><a href="#a4"  onclick="actualizarbox(\'a1\',\'creditos_gestion.php?a=5&cid='. $solicitud_id.'&b='.$siguienteb.'\') ; return false;" data-toggle="tab" class="btn btn-primary">Siguiente</a> </div>
     </div></div>' ; 
     
       echo " </div>";  
    ?>
    
     
<script>
        
        
        function procesarforma() {
                $("#botones *").attr("disabled", "disabled");
                $("#formagestion :input").attr('readonly', true);
                $('#cargando').show();
                var myTable = '';

                var url = "creditos_gestion.php?a=5b1&s=1&cid=<?php echo $solicitud_id; ?>";
                $.getJSON(url, $("#formagestion").serialize(), function(json) {
                    
                    i = 1;
                    if (json.length > 0) {
                        if (json[0].pcode == 0) {
                                
                                $('#salida').empty().append('<div class="alert alert-warning" role="alert">'+json[0].pmsg+'</div>'); 
                                
                        }
                        if (json[0].pcode == 1) {
                            

                                $('#nuevagestion').hide();
                                $('#siguientegestion').show();
                                $('#salida').empty().append('<div class="alert alert-info" role="alert">'+json[0].pmsg+'</div>'); 
                                    
                        }
                    } else {
                            $('#salida').empty().append('<div class="alert alert-danger" role="alert">Se produjo un error en comunicacion JSON:101</div>'); 
                    }

                }).error(function() {
                        $('#salida').empty().append('<div class="alert alert-danger" role="alert">Se produjo un error en comunicacion JSON:102</div>');
                }).complete(function() {
                    
                    $('#cargando').hide();
                    $("#formagestion :input").attr('readonly', false);
                    $("#botones *").removeAttr("disabled");
                });

            }
            
    </script>
    
    
    

    <?php 

        }//nueva gestion 
                
        
    } else {
         $solicitud_num='';
        if (isset($_REQUEST['num'])) {$solicitud_num=$_REQUEST['num'];}
         echo "<br><br><a href=\"#\" onclick=\"actualizarbox('pagina','creditos.php?a=0b&cid=$solicitud_id&num=$solicitud_num') ; return false;\"   class=\"btn btn-default\">REGRESAR</a>";
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a  href=\"#\" class=\"btn btn-default btn-sm\" onclick=\"actualizarbox('pagina','creditos_gestion.php?a=1&cid=".$solicitud_id."') ; return false;\" ><span class=\"glyphicon glyphicon-folder-open\" aria-hidden=\"true\"></span>&nbsp; Abrir Solicitud</a>";
    }

     exit;
}
            
if ($accion=="6") //TODO Historial gestiones
{
  
             //******* SQL ************************************************************************************
               
            $sql="SELECT prestamo_gestion.id, fecha, hora, usuario, etapa_id, descripcion 
            ,usuario_responde, usuario_confirma, hora_responde, hora_confirma
            , prestamo_etapa.nombre as vetapa
            , prestamo_estatus.nombre as vestatus
                    FROM prestamo_gestion
                    LEFT OUTER JOIN prestamo_etapa ON (prestamo_etapa.id=prestamo_gestion.etapa_id) 
                    LEFT OUTER JOIN prestamo_estatus ON (prestamo_estatus.id=prestamo_gestion.estatus_id) 
                    where prestamo_id=$solicitud_id 
                    ";

            // ****** Fin SQL ********************************************************************************
            
             $result = $conn -> query($sql);


         //   echo "<h4>Gestiones</h4><br>";
            
          

            if ($result -> num_rows > 0) {
               
                 
            echo '<div class="row">
                    <div class="">
                      <table class="display nowrap" id="tabla" width="100%" cellspacing="0">
                        <thead>
                          <tr>
        
                            <th class="text-center"># Gestion</th>
                            <th class="text-center">Usuario</th>
                            <th class="text-center">Usuario Responde</th>
                            <th class="text-center">Usuario Confirma</th>
                            <th class="text-center">Tipo Gestion</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Descripcion</th>
                            

                          </tr>
                        </thead>
                        
                        <tfoot>
                         <tr>
                
                           <th class="text-center"># Gestion</th>
                            <th class="text-center">Usuario</th>
                            <th class="text-center">Usuario Responde</th>
                            <th class="text-center">Usuario Confirma</th>
                                      <th class="text-center">Tipo Gestion</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Descripcion</th>
                           
                          </tr>
                        </tfoot>
                        <tbody>';
              
     
                        while ($row = $result -> fetch_assoc()) {
                            $fecha_recibe="";
                            $fecha_confirma="";
                            if (!is_null($row["hora_responde"])) {$fecha_recibe=date("d/m/y h:i a",strtotime($row["hora_responde"]));}
                            if (!is_null($row["hora_confirma"])) {$fecha_confirma=date("d/m/y h:i a",strtotime($row["hora_confirma"]));}
                            
                           echo "<tr>
                              <td class='text-center'>".$row["id"]."</td>               
                              <td class='text-center'>".$row["usuario"]."<br>".fechademysql($row["fecha"]).' '.horademysql($row["hora"])."</td>
                              <td class='text-center'>".$row["usuario_responde"].'<br>'.$fecha_recibe."</td>
                              <td class='text-center'>".$row["usuario_confirma"].'<br>'.$fecha_confirma."</td>
                              <td class='text-left'>".$row["vetapa"]."</td>
                              <td class='text-center'>".$row["vestatus"]."</td>
                              <td class='text-left'>".$row["descripcion"]."</td>
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

if ($accion=="52") //TODO gestion Nueva
{
        echo ' <div class="panel panel-default"> <div id="datosgenerales" class="panel-body"> <div class="row">                  <div class="col-xs-12"> <form class="form-horizontal">';
        
        echo "<h4>Nueva Gestion</h4><br>";
                    
   
 echo campo("etapa_id","Tipo de Gestion",'select',valores_combobox_db('prestamo_etapa','','nombre','','nombre') ,'class="form-control" ','','',3,8);
   echo campo("descripcion","Descripcion",'textarea','','class="form-control" rows="6"','','',3,9);  
   
   echo '<a id="btnguardar" href="#" class="btn btn-primary" onclick="procesarforma() ; return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar</a>';
          
   echo " </form></div></div></div></div>";
}
            
            




?>