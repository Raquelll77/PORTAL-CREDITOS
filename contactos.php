<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");


require_once ('include/protect.php');
require_once ('include/framework.php');

$verror = "";

if (!tiene_permiso(13)) { echo mensaje("No tiene privilegios para accesar esta seccion","danger");exit;}
    

$conn = new mysqli(db_ip, db_user, db_pw, db_name);
if (mysqli_connect_errno()) {  echo mensaje("Error al Conectar a la Base de Datos [DB:101]","danger");exit;}
$conn->set_charset("utf8");



    if (!isset($_REQUEST['sub']) ) {
        ?>
    
    
<div id="buscar"> 
    <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">Agenda de Contactos</h4>
        </div>
        <div class="panel-body">
            
     <form class="form-horizontal">
       <?php 
       
        echo campo("sb","Nombre Contacto","text","",' class="form-control" autofocus');
   
    //   echo campo( "Aceptar","Buscar","boton","","onclick=\"procesarrep(1) ; return false;\" ");
    
   ?>    
      <div class="form-group"><div class="col-sm-offset-3 col-sm-9">
        <button type="submit" class="btn btn-primary" onclick="procesarrep(1) ; return false;">Buscar</button>
        &nbsp;&nbsp;&nbsp;
        <button type="submit" class="btn btn-default" onclick="procesarrep(2) ; return false;">Mostrar Todos</button>
    </div></div>
       
    </form>         
            
        </div>
    </div>
    </div>

     
     
        
        <div id="reportev"> </div>
        
         <script type="text/javascript">
            function procesarrep(todos) {
                var busca=$("#sb").val();
                if (todos==2) {busca='';} 
                var url = "contactos.php?sub=1&sb="+busca ;             
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
               
            $sql="SELECT usuario.nombre, email, distribuidor, distribuidor_nombre, bodega, bodega_nombre, telefono_fijo,telefono_ext, telefono_movil
            , empresa.nombre as empresa, usuario_puesto.nombre as puesto, bodega.nombre as sucursal
                    FROM usuario 
                    LEFT OUTER JOIN empresa ON (usuario.empresa_id=empresa.id)
                    LEFT OUTER JOIN usuario_puesto ON (usuario.puesto_id=usuario_puesto.id)
                    LEFT OUTER JOIN bodega ON (usuario.bodega=bodega.codigo)
                    WHERE  activo='SI' ";
   
        
            if ($buscar<>"") { $sql.=  "  and ( usuario.nombre LIKE '%$buscar%' )";            }   
            
              if ( $_SESSION['empresa']<>1) {
                  $addsql="";
                   if ( $_SESSION['empresa']<>"") { $addsql="or usuario.empresa_id =".$_SESSION['empresa'];}
                   $sql.=  "  and ( usuario.empresa_id =1 $addsql )";       
                       }      
             
            // ****** Fin SQL ********************************************************************************
      
             $result = $conn -> query($sql);

            if ($result -> num_rows > 0) {
                
                $reg=0;
            echo '<div class="row">
                    <div >
                      <table class="display nowrap" id="tabla" width="100%" cellspacing="0">
                        <thead>
                          <tr>
                       
                            <th class="text-center">Nombre</th>
                            <th class="text-center">Empresa</th>
                            <th class="text-center">Sucursal</th>
                            <th class="text-center">Puesto</th>
                            <th class="text-center">Telefono</th>
                            <th class="text-center">Celular</th>
                            <th class="text-center">Email</th>
                           
                          </tr>
                        </thead>
                        
                        <tfoot>
                         <tr>
                      
                            <th>Nombre</th>
                            <th>Empresa</th>
                            <th>Sucursal</th>
                            <th>Puesto</th>
                            <th>Telefono</th>
                            <th>Celular</th>
                            <th>Email</th>
                           
                          </tr>
                        </tfoot>
                        <tbody>';
              
     
                        while ($row = $result -> fetch_assoc()) {
                            $ext="";
                            if ($row["telefono_ext"]<>"") {  $ext="Ext. ".$row["telefono_ext"]; }
                           echo "<tr>
                         
                              <td class='text-left'>".$row["nombre"]."</td>
                              <td class='text-left'>".$row["empresa"]."</td>
                              <td class='text-left'>".$row["sucursal"]."</td>
                              <td class='text-left'>".$row["puesto"]."</td> 
                              <td class='text-left'>".$row["telefono_fijo"]." $ext </td>
                               <td class='text-left'>".$row["telefono_movil"]."</td>
                               <td class='text-left'>".$row["email"]."</td>
                      
                              </tr>"    ;
                        $reg++;
                        }


                        
                 echo" </tbody>                   
                          </table>
                            </div>
                          </div> ";
              
                echo crear_datatable('tabla','false',true,false) ;

              
              // <div class=\"row col-xs-12\">Registros <span class=\"badge\">$reg</span></div>
                 
                 
                } else { echo mensaje( "No se encontraron registros","info"); exit;}  
    
    
    
    
    
 
    
        } //else { echo mensaje( "Debe ingresar informacion en el campo para buscar","warning"); exit;}
        
        exit;
     
    
    }







?>