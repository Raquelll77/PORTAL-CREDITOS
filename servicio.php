<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");


require_once ('include/protect.php');
require_once ('include/framework.php');

$verror = "";

$accion="1";
if (isset($_REQUEST['a'])) { $accion = $_REQUEST['a']; }

if (!tiene_permiso(3)) { echo mensaje("No tiene privilegios para accesar esta seccion","danger");exit;}
    

$conn = new mysqli(db_ip, db_user, db_pw, db_name);
if (mysqli_connect_errno()) {  echo mensaje("Error al Conectar a la Base de Datos [DB:101]","danger");exit;}
$conn->set_charset("utf8");



            
 if ($accion=="1" )  //TODO listar gestiones
        {

            //******* SQL ************************************************************************************
               
            $sql="SELECT id, fecha_alta, hora_alta, usuario_alta, ultima_actividad, distribuidor, bodega, distribuidor_nombre, usuario_nombre,
                    asunto, estado, serie_chasis, nombre_cliente, telefono_cliente, ciudad, departamento , responsable, modelo,distribuidor_nombre2 
                    ,TIMEDIFF(ifnull(hora_cierre,now()),hora_alta ) as antiguedad
                    FROM servicio 
                    WHERE  1=1";
          
            if (!tiene_permiso(16)) {
                 if (!tiene_permiso(30)) {         
                $sql.=" and usuario_alta='".$_SESSION['usuario']."'"; 
                 } else {
                     $campo_dist="distribuidor";
                     $campo_bodega="bodega";
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
            
              
    
            $sql.=" order by estado asc,fecha_alta desc";
        
 
             
            // ****** Fin SQL ********************************************************************************
            
             $result = $conn -> query($sql);

 
            echo "<h4>MODULO DE SERVICIO</h4><br>";
            
           echo "<p><a href=\"#\" onclick=\"actualizarbox('pagina','servicio.php?a=2') ; return false;\"   class=\"btn btn-default\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Nueva Gestion de Servicio</a></p><br>";
 
            if ($result -> num_rows > 0) {
               
              
                 //  <th class="text-center">Identidad</th>
                 //<div class="table-responsive">
                 
                $reg=0;
            echo '<div class="row">
                    <div class="">
                      <table class="display nowrap" id="tabla" width="100%" cellspacing="0">
                        <thead>
                          <tr>
        
                        <th ></th>
                            <th class="text-center">No.</th>
                            <th class="text-center">Gestor</th>
                            <th class="text-center">Responsable</th>
                            <th class="text-center">Distribuidor</th>
                            <th class="text-center">Cliente Final</th>
                            <th class="text-center">Modelo</th>
                            <th class="text-center">Serie</th>
                            <th class="text-center">Asunto</th>
                            <th class="text-center">Fecha</th>
                          
                            <th class="text-center">Estado</th>
                            <th class="text-center">Ant.</th>
                          </tr>
                        </thead>
                        
                        <tfoot>
                         <tr>
                      <th ></th>
                            <th class="text-center">No.</th>
                            <th class="text-center">Gestor</th>
                            <th class="text-center">Responsable</th>
                            <th class="text-center">Distribuidor</th>
                            <th class="text-center">Cliente Final</th>
                            <th class="text-center">Modelo</th>
                            <th class="text-center">Serie</th>
                            
                            <th class="text-center">Asunto</th>
                            <th class="text-center">Fecha</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Ant.</th>
                          </tr>
                        </tfoot>
                        <tbody>';
              
     
                        while ($row = $result -> fetch_assoc()) {
                           echo "<tr>

    
                            <td class='text-center'><a  href=\"#\" class=\"btn btn-default btn-sm\" onclick=\"actualizarbox('pagina','servicio.php?a=4&cid=".$row["id"]."') ; return false;\" ><span class=\"glyphicon glyphicon-folder-open\" aria-hidden=\"true\"></span></a></td>
                              <td class='text-center'>".$row["id"]."</td>
                             
                               <td class='text-left'>".$row["usuario_nombre"]."</td> 
                               <td class='text-left'>".$row["responsable"]."</td> 
                               <td class='text-left'>".$row["distribuidor_nombre2"]."</td> 
                               <td class='text-left'>".$row["nombre_cliente"]."</td> 
                               <td class='text-left'>".$row["modelo"]."</td> 
                             <td class='text-left'>".$row["serie_chasis"]."</td> 
                              <td class='text-left'>".$row["asunto"]."</td> 
                            
                            
                             <td class='text-center'>".fechademysql($row["fecha_alta"])."</td>
                
                              <td class='text-center'>".$row["estado"]."</td> 
                              <td class='text-right'>".substr($row["antiguedad"], 0, -3)."</td> 
                              </tr>"    ;
                        $reg++;
                        }

                    
                 echo" </tbody>                   
                          </table>
                            </div>
                          </div> ";
              
                echo crear_datatable('tabla','false',true,true) ;

                   
                } else { echo mensaje( "No se encontraron registros","info"); exit;}  
  exit;    
} 
        

if ($accion=="2" ) //TODO pantalla de nueva gestion
{
   ?> 
   
       <div id="mydivd"> </div>
   <script>
 function asignar_serie(rid){
    var titulo="Seleccione una Serie";
                var url="servicio.php?a=6";
                
                var NewDialog = $('<div><p><img src="images/load.gif"/></p></div>');
                        NewDialog.load(url).dialog({ modal: true, title: titulo, width: '95%', height: 600, position: { my: "center", at: "center", of: window } , close: function(event, ui) { $(this).dialog('destroy').remove();},
                        buttons: [
                            
                            {text: "Cerrar", click: function() {$(this).dialog("close");}}
                            ]
                        }); 

}
</script>


    
    <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">NUEVA GESTION DE SERVICIO</h4>
        </div>
        <div id="datosgenerales" class="panel-body">
            
            
             <div class="row">
                    <div class="col-xs-12">
           <form class="form-horizontal" id="servicioform">         
                 
       <?php 
       
   $opcionesasunto='<option value="">Seleccione</option>
                    <option value="Revision Motor">Revision Motor</option>
                   <option value="Revision Sistema Electrico">Revision Sistema Electrico</option>
                   <option value="Revision Suspension">Revision Suspension</option>
                   <option value="Revision Frenos">Revision Frenos</option>
                   <option value="Revision Carroceria">Revision Carroceria</option>
                   <option value="Revision Escape">Revision Escape</option>
                   <option value="Revision Traccion">Revision Traccion</option>
                   <option value="Revision Ruedas">Revision Ruedas</option>
                    '; 
                    
                    


echo campo("Usuario","Usuario",'label',$_SESSION['usuario_nombre'] ,'class="form-control" ','','',3,7);
       echo campo( "asunto","Asunto",'select',$opcionesasunto,'class="form-control" ','','',3,7);
       
       echo campo("serie_chasis","Serie Chasis",'text','','class="form-control readonly" readonly','','',3,5); 
       
       echo '<div class="form-group"><label   class="control-label col-sm-3"> </label><div class="col-sm-5">
       <a id="btnserie" href="#" class="btn btn-info btn btn-sm" onclick="asignar_serie(0) ; return false;"> <span class=\"glyphicon glyphicon-folder-open\" aria-hidden=\"true\"></span> Seleccionar Serie</a>
       </div></div>';
       
       echo campo("nombre_cliente","Nombre Cliente",'text','','class="form-control" ','','',3,7);
       echo campo("telefono_cliente","Telefono",'text','','class="form-control" ','','',3,7);
      
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
<option value="Yoro">Yoro</option>',''),'class="form-control" ','','',3,7);
       echo campo("ciudad","Ciudad",'text','','class="form-control" ','','',3,7);  
        
       echo campo("mensaje","Mensaje",'textarea','','class="form-control" rows="5"','','',3,9); 

        echo campo("responsable","",'hidden','','','',''); 
        echo campo("distribuidor_nombre2","",'hidden','','','',''); 
        echo campo("modelo","",'hidden','','','',''); 




       ?>  

                <div class="row">

           <?php 
            echo campo("doc1","Adjunto 1",'upload','','class="form-control" ');
            echo campo("doc2","Adjunto 2",'upload','','class="form-control" ');
            echo campo("doc3","Adjunto 3",'upload','','class="form-control" ');

           ?>  
          </div>
         
                </form>        </div> 
                      </div>
                      
        
        </div>
    </div>
    
    <div id="botones">
    <a id="btnguardar" href="#" class="btn btn-primary" onclick="procesarforma() ; return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar</a>  
     <a id="btnimprimir" href="#" style="display: none;" class="btn btn-info" onclick="actualizarbox('pagina','servicio.php') ; return false;"> Regresar</a>
     <input id="ridg" name=""ridg  type="hidden" value="" />
     
     <img id="cargando" style="display: none;" src="images/load.gif"/>
         <div class="row">
            <br>
     <div id="salida"> </div>
     </div>
    </div>  
    <script>
        
        
        function procesarforma() {
                $("#botones *").attr("disabled", "disabled");
                $("#servicioform :input").attr('readonly', true);
                $('#cargando').show();
                var myTable = '';
                
                
                
                var url = "servicio.php?a=3";
                $.getJSON(url, $("#servicioform").serialize(), function(json) {
                    
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
                                $("#adjuntos *").attr("disabled", "disabled");
                                $('#btnguardar').hide();
                                $('#salida').empty().append('<div class="alert alert-success" role="alert">'+json[0].pmsg+'</div>'); 
                                    
                        }
                    } else {
                            $('#salida').empty().append('<div class="alert alert-danger" role="alert">Se produjo un error en comunicacion JSON:101</div>'); 
                    }

                }).error(function() {
                        $('#salida').empty().append('<div class="alert alert-danger" role="alert">Se produjo un error en comunicacion JSON:102</div>');
                }).complete(function() {
                    
                    $('#cargando').hide();
                    $("#servicioform :input").attr('readonly', false);
                    $("#botones *").removeAttr("disabled");
                });

            }
            
    </script>
    
    <?php
    echo boton_regresar_pag("servicio.php");
    exit;    
    
}


if ($accion=="3" ) //TODO Guarda nueva gestion
{
    //########## validar datos
        $verror="";
        
        $responsable="";
        $modelo="";
        $distribuidor_nombre2="";
        $buscar="";
        
  

         $verror.=validar("Asunto",$_REQUEST['asunto'], "text", true,  null,  3,  null);
         $verror.=validar("Mensaje",$_REQUEST['mensaje'], "text", true,  null,  3,  null);
       
         $verror.=validar("Serie Chasis",$_REQUEST['serie_chasis'], "text", true,  null,  3,  null);
         $verror.=validar("Nombre del Cliente",$_REQUEST['nombre_cliente'], "text", true,  null,  3,  null);
         $verror.=validar("Telefono",$_REQUEST['telefono_cliente'], "text", true,  null,  3,  null);
         $verror.=validar("Mensaje",$_REQUEST['departamento'], "text", true,  null,  3,  null);
         $verror.=validar("Departamento",$_REQUEST['ciudad'], "text", true,  null,  3,  null);
        
        
           // validar serie
           //******* SQL ************************************************************************************
           $buscar=trim($_REQUEST['serie_chasis']);
            //SAP
            if ($buscar<>''){
            $sql=  "SELECT t3.Name [Modelo],T1.ItemName [Marca],T0.WhsCode [Cod.Almacen],T2.WhsName [NombreAlmacen],T0.SuppSerial [SerieChasis],T0.IntrSerial [SerieMotor],
            ISNULL((SELECT T.Name FROM [@CRESPONSABLEOWHS] T WHERE T2.U_Categorizacion=T.Code),'')[Responsable]

            FROM OSRI T0, OITM T1,OWHS t2,[@AMODELO] t3 WITH(NOLOCK)
            
            WHERE T0.ItemCode=T1.ItemCode and T0.WhsCode=T2.WhsCode and t1.U_AMODELO=t3.Code AND T0.Status=1 AND T1.ItmsGrpCod=154
                      
              ";


            if (isset($entorno_desarrollo) )             
            {
                    $sql=  "SELECT TOP 1000 SerieChasis,SerieMotor,Marca,Modelo ,Color ,anio ,ModeloESpecifico,Almacen,NombreAlmacen
                       ,'Juan Carlos' as Responsable      
                    FROM serie 
                    WHERE  1=1  ";
                    $sql.=  "  and ( SerieChasis = '$buscar' )";      
             } else 
            {    $sql.=  "  and ( SuppSerial = '$buscar' )"; }
            
 
             $conn2 = sqlsrv_connect( db2_ip, array( "Database"=>db2_dbn, "UID"=>db2_usuario, "PWD"=>db2_clave , "CharacterSet" => "UTF-8") );   if( $conn2 === false ) { echo mensaje("Error al Conectar a la Base de Datos [DB:102]","danger");exit;}
            $stmt2 = sqlsrv_query( $conn2, $sql );  
            if( $stmt2 === false) {   die( print_r( sqlsrv_errors(), true) );}
    
            if (sqlsrv_has_rows($stmt2)===true) 
            {
   
            
             $row = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC);
             
             $responsable=trim($row["Responsable"]) ;
             $modelo=trim($row["Modelo"] );
             $distribuidor_nombre2=trim($row["NombreAlmacen"]);
                 

        
            } else {  $verror.= "El numero de serie de chasis ingresado NO fue encontrado";}
        
            }
            // ****** Fin SQL ********************************************************************************
        
        

        
        // ######### Guardar 
        if ($verror==""){
            
    
            $sqlcampos="";
               
            $sqlcampos.= "  asunto =".GetSQLValue($conn->real_escape_string($_REQUEST["asunto"]),"text");
            $sqlcampos.= " , serie_chasis =".GetSQLValue($conn->real_escape_string($_REQUEST["serie_chasis"]),"text");
             $sqlcampos.= " , nombre_cliente =".GetSQLValue($conn->real_escape_string($_REQUEST["nombre_cliente"]),"text");
             $sqlcampos.= " , telefono_cliente =".GetSQLValue($conn->real_escape_string($_REQUEST["telefono_cliente"]),"text");
             $sqlcampos.= " , departamento =".GetSQLValue($conn->real_escape_string($_REQUEST["departamento"]),"text");
             $sqlcampos.= " , ciudad =".GetSQLValue($conn->real_escape_string($_REQUEST["ciudad"]),"text");
             
         
          $sqlcampos.= " , responsable =".GetSQLValue( $responsable ,"text");
           $sqlcampos.= " , modelo =".GetSQLValue( $modelo ,"text");
            $sqlcampos.= " , distribuidor_nombre2 =".GetSQLValue($distribuidor_nombre2,"text");
         
         
         
            $sqlcampos.= " , estado = 'Abierto'";

            $sqlcampos.= ",usuario_alta= '" .$_SESSION['usuario'] . "' ,usuario_nombre= '" .$_SESSION['usuario_nombre'] . "' ,fecha_alta=NOW(), hora_alta=NOW(),ultima_actividad=NOW()";
            
            $cod_distribuidor="";
            $distribuidor_nombre="";
           $cod_bodega=$_SESSION['usuario_bodega']; 
           if ($cod_bodega<>''){
           $cod_distribuidor=get_dato_sql('bodega', 'id_distribuidor', " where codigo='$cod_bodega'");        
           $distribuidor_nombre= get_dato_sql('distribuidor', 'nombre', " where codigo='$cod_distribuidor'");
           }
            
            $sqlcampos.= ",distribuidor= '$cod_distribuidor' ,distribuidor_nombre='$distribuidor_nombre'";         
            $sqlcampos.= ",bodega= '$cod_bodega' ";
            
            
            $sql="INSERT INTO servicio set " . $sqlcampos;
           // echo $sql;exit;
            if ($conn->query($sql) === TRUE) {
            $insert_id = mysqli_insert_id($conn);
                     //detalle
                    $sqlcampos="";
                    $sqlcampos.= "  mensaje =".GetSQLValue($conn->real_escape_string($_REQUEST["mensaje"]),"text");
                    $sqlcampos.= ",usuario_alta= '" .$_SESSION['usuario'] . "' ,usuario_nombre= '" .$_SESSION['usuario_nombre'] . "' ,fecha_alta=NOW(), hora_alta=NOW()";
                    $sqlcampos.= ",servicio_id=$insert_id";
                    if (isset($_REQUEST["doc1"])) {$sqlcampos.= " , doc1 =".GetSQLValue($conn->real_escape_string($_REQUEST["doc1"]),"text"); }
                    if (isset($_REQUEST["doc2"])) {$sqlcampos.= " , doc2 =".GetSQLValue($conn->real_escape_string($_REQUEST["doc2"]),"text"); }
                    if (isset($_REQUEST["doc3"])) {$sqlcampos.= " , doc3 =".GetSQLValue($conn->real_escape_string($_REQUEST["doc3"]),"text"); }
                     $sql2="INSERT INTO servicio_detalle set " . $sqlcampos;
                     $conn->query($sql2);
                
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
}

if ($accion=="4" ) //TODO abrir  gestion
{
    if (!isset($_REQUEST['cid'])) { echo mensaje( "No se encontraron registros","info"); exit;} 
    
    $idservicio=$conn->real_escape_string($_REQUEST["cid"]);

 //******* SQL ************************************************************************************
               
            $sql="SELECT id, servicio_id, fecha_alta, hora_alta, usuario_alta, usuario_nombre, mensaje, doc1, doc2, doc3
                      FROM servicio_detalle 
                    WHERE  servicio_id=$idservicio";
                  
            $sql.=" order by hora_alta asc";
        
            
             
            // ****** Fin SQL ********************************************************************************
      
             $result = $conn -> query($sql);


            echo "<h4>GESTION DE SERVICIO # $idservicio</h4><br>";
            
        
            if ($result -> num_rows > 0) {
                
                   
                $result2 = $conn -> query("SELECT * FROM servicio where id=$idservicio ");
               if ($result2 -> num_rows > 0) {
                   $row2 = $result2 -> fetch_assoc();
                   echo "<p>Asunto: <strong>".$row2["asunto"]."</strong></p>";
                  // echo "<p>Asunto: <strong>".$row2["asunto"]."</strong><br>Por: ".$row2["usuario_nombre"]."  &nbsp;&nbsp;<span class=\"pull-right\">".fechademysql($row2["fecha_alta"])." &nbsp;&nbsp;".horademysql($row2["hora_alta"])."</span></p>";
                echo "<p>Serie Chasis: <strong>".$row2["serie_chasis"]."</strong>  Modelo: <strong>".$row2["modelo"]."</strong></p>";
                echo "<p>Cliente: <strong>".$row2["nombre_cliente"]."</strong>  Telefono: <strong>".$row2["telefono_cliente"]."</strong></p>";
                echo "<p>Departamento: <strong>".$row2["departamento"]."</strong>  Ciudad: <strong>".$row2["ciudad"]."</strong></p>";
                echo "<p>Responsable: <strong>".$row2["responsable"]."</strong>  Distribuidor: <strong>".$row2["distribuidor_nombre2"]."</strong></p>";
 
 
 
               }
                 //  <th class="text-center">Identidad</th>
                 //<div class="table-responsive">
             
            
                
                $reg=0;
            echo '<div class="row">
                    <div class="">
                      <table class="table table-striped table-bordered" id="tabla" width="100%" cellspacing="0">
                        <tbody>';
              
     
                        while ($row = $result -> fetch_assoc()) {
                           echo "<tr>
                                <td class='text-left'><strong>".$row["usuario_nombre"]."</strong>  &nbsp;&nbsp;<span class=\"label label-default\">".fechademysql($row["fecha_alta"])." &nbsp;&nbsp;".horademysql($row["hora_alta"])."</span></td>
                           </tr>
                             <tr>
                               <td class='text-left'>".nl2br($row["mensaje"])." ";
                               
                        if ($row["doc1"]<>"") { echo " <br><br>". campo("doc1","",'uploadlink',$row["doc1"],' ');  }  
                        if ($row["doc2"]<>"") { echo " <br><br>".campo("doc2","",'uploadlink',$row["doc2"],' ');  } 
                        if ($row["doc3"]<>"") { echo " <br><br>".campo("doc3","",'uploadlink',$row["doc3"],' ');  }      
                               
                          echo " </td>  </tr>"    ;
                            
                        $reg++;
                        }

                    
                 echo" </tbody>                   
                          </table>
                            </div>
                          </div> ";
             
           

                   
                } else { echo mensaje( "No se encontraron registros","info"); echo boton_regresar_pag("servicio.php"); exit;} 
                
                 if (es_nulo($row2["hora_cierre"])) { 
?>

<div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">NUEVO COMENTARIO</h4>
        </div>
        <div id="datosgenerales" class="panel-body">
            
            
             <div class="row">
                    <div class="col-xs-12">
           <form class="form-horizontal" id="servicioform">         
                 
       <?php 
       
    
       echo campo("mensaje","Mensaje",'textarea','','class="form-control" rows="5"','','',3,9); 


        if (tiene_permiso(17)) {
            echo campo("cerrar","Marcar el estado de la gestion como cerrado",'checkbox','','class="form-control" ','','',8,2); 
        }
       ?>  

                <div class="row">

           <?php 
            echo campo("doc1","Adjunto 1",'upload','','class="form-control" ');
            echo campo("doc2","Adjunto 2",'upload','','class="form-control" ');
            echo campo("doc3","Adjunto 3",'upload','','class="form-control" ');

           ?>  
          </div>
         
                </form>        </div> 
                      </div>
                      
        
        </div>
    </div>
    
    <div id="botones">
    <a id="btnguardar" href="#" class="btn btn-primary" onclick="procesarforma() ; return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar</a>  
   
     <input id="ridg" name=""ridg  type="hidden" value="" />
     
     <img id="cargando" style="display: none;" src="images/load.gif"/>
         <div class="row">
            <br>
     <div id="salida"> </div>
     </div>
    </div>  
    <script>
        
        
        function procesarforma() {
                $("#botones *").attr("disabled", "disabled");
                $("#servicioform :input").attr('readonly', true);
                $('#cargando').show();
                var myTable = '';
                
                
                
                var url = "servicio.php?a=5&cid=<?php echo $idservicio; ?>";
                $.getJSON(url, $("#servicioform").serialize(), function(json) {
                    
                    i = 1;
                    if (json.length > 0) {
                        if (json[0].pcode == 0) {
                                
                                $('#salida').empty().append('<div class="alert alert-warning" role="alert">'+json[0].pmsg+'</div>'); 
                                
                        }
                        if (json[0].pcode == 1) {
                            
                            if (json[0].pcodid != 0) {
                                $("#ridg").val(json[0].pcodid);
                               // $('#btnimprimir').show();
                                
                                }
                                $("#datosgenerales *").attr("disabled", "disabled");
                                $("#adjuntos *").attr("disabled", "disabled");
                                $('#btnguardar').hide();
                                $('#salida').empty().append('<div class="alert alert-success" role="alert">'+json[0].pmsg+'</div>'); 
                                    
                        }
                    } else {
                            $('#salida').empty().append('<div class="alert alert-danger" role="alert">Se produjo un error en comunicacion JSON:101</div>'); 
                    }

                }).error(function() {
                        $('#salida').empty().append('<div class="alert alert-danger" role="alert">Se produjo un error en comunicacion JSON:102</div>');
                }).complete(function() {
                    
                    $('#cargando').hide();
                    $("#servicioform :input").attr('readonly', false);
                    $("#botones *").removeAttr("disabled");
                });

            }
            
    </script>
    
    <?php
    } else {
                 echo  '<div class="row">'. mensaje( "Este documento se encuentra Cerrado ","info").'</div>' ; 
                 
             }

   echo boton_regresar_pag("servicio.php");     
   exit; 
}

if ($accion=="5" ) //TODO guardar ticket
{
      if (!isset($_REQUEST['cid'])) { echo mensaje( "No se encontraron registros","info"); exit;}   
      $idservicio=$conn->real_escape_string($_REQUEST["cid"]);
  //########## validar datos
        $verror="";
        
          $verror.=validar("Mensaje",$_REQUEST['mensaje'], "text", true,  null,  3,  null);
        
        
        // ######### Guardar 
        if ($verror==""){
            
    
            $sqlcampos="";

            $sqlcampos.= "  mensaje =".GetSQLValue($conn->real_escape_string($_REQUEST["mensaje"]),"text");
            $sqlcampos.= ",usuario_alta= '" .$_SESSION['usuario'] . "' ,usuario_nombre= '" .$_SESSION['usuario_nombre'] . "' ,fecha_alta=NOW(), hora_alta=NOW()";
            $sqlcampos.= ",servicio_id=$idservicio";
            if (isset($_REQUEST["doc1"])) {$sqlcampos.= " , doc1 =".GetSQLValue($conn->real_escape_string($_REQUEST["doc1"]),"text"); }
            if (isset($_REQUEST["doc2"])) {$sqlcampos.= " , doc2 =".GetSQLValue($conn->real_escape_string($_REQUEST["doc2"]),"text"); }
            if (isset($_REQUEST["doc3"])) {$sqlcampos.= " , doc3 =".GetSQLValue($conn->real_escape_string($_REQUEST["doc3"]),"text"); }
             $sql="INSERT INTO servicio_detalle set " . $sqlcampos;
                     
           // echo $sql;exit;
            if ($conn->query($sql) === TRUE) {
            $insert_id = mysqli_insert_id($conn);
                $estado="Abierto";
              $sqlcerrar="";  
              if (isset($_REQUEST["cerrar"])) { $estado="Cerrado";  $sqlcerrar=",hora_cierre=now()"; }
                
            ejecutar_sql("UPDATE servicio SET ultima_actividad=NOW(),  estado='$estado' $sqlcerrar where id=$idservicio");        
                
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

if ($accion=="6" ) //TODO Seleccionar Serie
{
    // validar serie
           //******* SQL ************************************************************************************
           //$buscar=trim($_REQUEST['serie_chasis']);
           $buscar="";
            //SAP
           // if ($buscar<>'')
            {
                //,ISNULL((SELECT T.Name FROM [@CRESPONSABLEOWHS] T WHERE T2.U_Categorizacion=T.Code),'')[Responsable]
            $sql=  "SELECT t3.Name [Modelo]
            ,T1.ItemName [Marca]
            ,T0.WhsCode [Cod.Almacen]
            ,T2.WhsName [NombreAlmacen]
            ,T0.SuppSerial [SerieChasis]
            ,T0.IntrSerial [SerieMotor]
            
            FROM OSRI T0, OITM T1,OWHS t2,[@AMODELO] t3 WITH(NOLOCK)
            
            WHERE T0.ItemCode=T1.ItemCode and T0.WhsCode=T2.WhsCode and t1.U_AMODELO=t3.Code 
            AND T0.Status=1 
            AND T1.ItmsGrpCod=154
                      
              ";

            $campo_dist="T2.U_CardCode";
            $campo_bodega="T0.WhsCode";
            
            if (isset($entorno_desarrollo) )             
            {
                    $sql=  "SELECT TOP 1000 SerieChasis,SerieMotor,Marca,Modelo ,Color ,anio ,ModeloESpecifico,Almacen,NombreAlmacen
                       ,'Juan Carlos' as Responsable      
                    FROM serie 
                    WHERE  1=1 AND Status=1  ";
                    //$sql.=  "  and ( SerieChasis = '$buscar' )"; 
                    $campo_dist="CodigoCliente";
                    $campo_bodega="Whscode";     
             } else 
            {   // $sql.=  "  and ( SuppSerial = '$buscar' )"; 
            }
            
            
            if (tiene_permiso(7)){
                 $texto=armar_sql($campo_dist,$_SESSION['grupo_distribuidores'],'or');
                 if ($texto<>"") {$sql.=" and $texto";} 
                 
                 $texto=armar_sql($campo_bodega,$_SESSION['grupo_bodegas'],'or');
                 if ($texto<>"") {$sql.=" and $texto";} 
            } else {
            if ($_SESSION['usuario_bodega']<>"") {$sql.=" and  $campo_bodega='".$_SESSION['usuario_bodega']."'";} // T0.WhsCode
            if ($_SESSION['usuario_distribuidor']<>"") {$sql.=" and $campo_dist='".$_SESSION['usuario_distribuidor']."'";} //T8.U_CardCode              
                
            }
            
 
             $conn2 = sqlsrv_connect( db2_ip, array( "Database"=>db2_dbn, "UID"=>db2_usuario, "PWD"=>db2_clave , "CharacterSet" => "UTF-8") );   if( $conn2 === false ) { echo mensaje("Error al Conectar a la Base de Datos [DB:102]","danger");exit;}
            $stmt2 = sqlsrv_query( $conn2, $sql );  
            if( $stmt2 === false) {   die( print_r( sqlsrv_errors(), true) );}
    
            if (sqlsrv_has_rows($stmt2)===true) 
            {
    //<div class="table-responsive">
    //<table class="table table-striped">
            $reg=0;
            echo '<div class="row">
                    <div >
                      <table class="display nowrap" id="tabla" width="100%" cellspacing="0">
                        <thead>
                          <tr>
                           <th class="text-center"></th>
                            <th class="text-center">Chasis</th>
                            <th class="text-center">Motor</th>
                            <th class="text-center">Modelo</th>                            
                          </tr>
                        </thead>
                        <tbody>';
  
            while( $row = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC) )
                {
                    
                
                         
                echo "<tr>";                                                                                 
                echo " <td class='text-center'><a  href=\"#\" class=\"btn btn-default btn-sm\" onclick=\"$('#serie_chasis').val('".trim($row["SerieChasis"])."');  alert('La serie ".trim($row["SerieChasis"])." fue seleccionada. precione el boton cerrar'); return false;\" ><span class=\"glyphicon glyphicon-folder-open\" aria-hidden=\"true\"></span></a></td>";
                  
                echo "  <td class='text-left'>".trim($row["SerieChasis"])."</td>
                        <td class='text-left'>".trim($row["SerieMotor"])."</td>
                        <td class='text-left'>".trim($row["Modelo"])."</td>
                      </tr>"    ;
                $reg++;
              
                
            }
            
              
            
            
             echo" </tbody>
                   
                  </table>
                </div>
              </div>
              ";
            
        //  <div class=\"row col-xs-12\">Registros <span class=\"badge\">$reg</span></div>
    
             echo crear_datatable('tabla') ;
             
        
            } else { echo mensaje( "No se encontraron registros","info"); exit;}
        
            }
exit;
}


?>