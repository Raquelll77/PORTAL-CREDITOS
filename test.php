
<?php

include 'testHeader.php';
// require_once ('include/protect.php');
//require_once ('include/framework.php');

//  $conn = new mysqli(db_ip, db_user, db_pw, db_name);
// // if (mysqli_connect_errno()) {  echo mensaje("Error al Conectar a la Base de Datos [DB:101]","danger");exit;}
// // $conn->set_charset("utf8");



$mysqli = new mysqli("192.168.31.164", "consultas", "s45Agu2oNA", "movesa_garantias");
if ($mysqli->connect_errno) { echo mensaje("Error al Conectar a la Base de Datos [DB:101]","danger");exit;}
else{
   // echo "conectado";
}
 $mysqli->set_charset("utf8"); //Admite acentos, ñ, etc

//  $solProceso = "SELECT prestamo.id,prestamo.numero,prestamo.bodega_nombre, fecha_alta, usuario_alta, nombres, apellidos, identidad, monto_prestamo, monto_financiar, monto_prima, plazo, tasa, estatus, etapa_proceso
//  ,prestamo_estatus.nombre as vestatus
//  ,prestamo_etapa.nombre as vprestamo_etapa
//  ,usuario.nombre as nombreusuario
//  FROM prestamo
//  LEFT OUTER JOIN prestamo_estatus ON (prestamo_estatus.id=prestamo.estatus)
//  LEFT OUTER JOIN prestamo_etapa ON (prestamo_etapa.id=prestamo.etapa_proceso)
//  LEFT OUTER JOIN usuario ON (prestamo.usuario_alta=usuario.usuario)
//  WHERE  1=1 
//  and (prestamo.estatus=1 or (prestamo.estatus=2 and prestamo.cierre_documentos_recibidos is null))
//  and prestamo_etapa.id BETWEEN 1 AND 8 and usuario_alta LIKE 'CD%'";
//  $resProceso = $mysqli->query($solProceso);
//  $cantSolProceso = mysqli_num_rows($resProceso);
// echo $cantSolProceso;
// exit;

  //******* SQL ************************************************************************************
  $consulta="SELECT prestamo.id,prestamo.numero,prestamo.bodega_nombre, fecha_alta, 
        usuario_alta, nombres, apellidos, identidad, monto_prestamo, monto_financiar, monto_prima, plazo, tasa, estatus, etapa_proceso
        ,prestamo_estatus.nombre as vestatus,prestamo_etapa.nombre as vprestamo_etapa,usuario.nombre as nombreusuario
        , prestamo.tipoprima as 'tipoprima'
        , CASE WHEN prestamo.tipoprima = 1 THEN 'Prima Normal' ELSE 'Prima Alta (40%)' END AS 'tipoprimatext'
        FROM prestamo
        LEFT OUTER JOIN prestamo_estatus ON (prestamo_estatus.id=prestamo.estatus)
        LEFT OUTER JOIN prestamo_etapa ON (prestamo_etapa.id=prestamo.etapa_proceso)
        LEFT OUTER JOIN usuario ON (prestamo.usuario_alta=usuario.usuario)
        WHERE  1=1 
        AND (prestamo.estatus=1 or (prestamo.estatus=2 and prestamo.cierre_documentos_recibidos is null))";
// ****** Fin SQL ********************************************************************************
  $filas = $mysqli->query($consulta);  
if ($filas -> num_rows > 0) {


$reg=0;
?>

<div class="container-fluid">
    <div class="row">
    <h4 class="text-center">Solicitudes en Proceso</h4><br>
        <div class="col-sm-12">
        <div class="table-responsive table-responsive-md table-responsive-sm">
        <table id="Solicitudes" class="table table-bordered table-striped">
            <thead>
                <tr>
                <th class="text-center">No.</th>
                <th class="text-center">Fecha</th>
                <th class="text-center">Nombre</th>
                <th class="text-center">Tipo de Prima</th>
                <th class="text-center">Vendedor</th>
                <th class="text-center">Tienda</th>
                <th class="text-center">V. Prestamo</th>
                <th class="text-center">Prima</th>
                <th class="text-center">T. Financiar</th>
                <th class="text-center">Plazo</th>
                <th class="text-center">Tasa</th>
                <th class="text-center">Estatus</th>
                <th class="text-center">Etapa de Proceso</th>
                <th class="text-center">Seguimiento</th>

                </tr>
            </thead>
            
            <tbody>

                <?php
                    while ($row = $filas -> fetch_assoc()) {
                        $colortxt="";
                        if ($row["estatus"]==2) {$colortxt=" bg-success"; }          
                        if ($row["estatus"]==3) {$colortxt=" bg-danger"; } 
                        
                        echo "
                        <tr>
                        <td class='text-center ".($row["tipoprima"]==1 ? "$colortxt" : "bg-danger")."'>".$row["numero"]."</td>
                        <td class='text-center ".($row["tipoprima"]==1 ? "$colortxt" : "bg-danger")."'>".$row["fecha_alta"]."</td>
                        <td class='text-left ".($row["tipoprima"]==1 ? "$colortxt" : "bg-danger")."'>".$row["nombres"]." ".$row["apellidos"]."</td> 
                        <td class='text-center ".($row["tipoprima"]==1 ? "$colortxt" : "bg-danger")."'>".$row["tipoprimatext"]."</td>
                        <td class='text-center ".($row["tipoprima"]==1 ? "$colortxt" : "bg-danger")."'>".$row["nombreusuario"]."</td>
                        <td class='text-center ".($row["tipoprima"]==1 ? "$colortxt" : "bg-danger")."'>".$row["bodega_nombre"]."</td>
                        <td class='text-right ".($row["tipoprima"]==1 ? "$colortxt" : "bg-danger")."'>".$row["monto_prestamo"]."</td>
                        <td class='text-right ".($row["tipoprima"]==1 ? "$colortxt" : "bg-danger")."'>".$row["monto_prima"]."</td>
                        <td class='text-right ".($row["tipoprima"]==1 ? "$colortxt" : "bg-danger")."'>".$row["monto_financiar"]."</td>
                        <td class='text-center ".($row["tipoprima"]==1 ? "$colortxt" : "bg-danger")."'>".$row["plazo"]."</td>
                        <td class='text-center ".($row["tipoprima"]==1 ? "$colortxt" : "bg-danger")."'>".$row["tasa"]."</td>
                        <td class='text-center ".($row["tipoprima"]==1 ? "$colortxt" : "bg-danger")."'>".$row["vestatus"]."</td>
                        <td class='text-center ".($row["tipoprima"]==1 ? "$colortxt" : "bg-danger")."'>".$row["vprestamo_etapa"]."</td>
                        <td></td>
                        </tr>"    ;
                        $reg++;
                    }
                                    } else { echo mensaje( "No se encontraron registros","info"); exit;}  
                ?>
            </tbody>                   
        </table> 
        </div>
        </div>
    </div>


</div>