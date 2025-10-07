<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once('clases/MetaDatos.class.php');
require_once('include/protect.php');
require_once('include/framework.php');

$lista_plazos = DocumentosDatos::obtener_datos(DocumentosDatos::LISTA_PLAZOS);
$verror = "";

if (isset($_REQUEST['a'])) {
  $accion = $_REQUEST['a'];
} else {
  $accion = '';
}

if (!tiene_permiso(15)) {
  echo mensaje("No tiene privilegios para accesar esta seccion", "danger");
  exit;
}


$conn = new mysqli(db_ip, db_user, db_pw, db_name);
if (mysqli_connect_errno()) {
  echo mensaje("Error al Conectar a la Base de Datos [DB:101]", "danger");
  exit;
}
$conn->set_charset("utf8");

// === Verificar si el cliente ya tiene crédito ingresado este año por identidad ===
// === Chequeo de solicitudes en el mismo año por identidad ===
if ($accion === 'chkdup') {
  $dni = isset($_REQUEST['identidad']) ? preg_replace('/\D/', '', $_REQUEST['identidad']) : '';
  $out = ['found' => 0, 'items' => []];

  if ($dni !== '') {
    $dni_sql = $conn->real_escape_string($dni);
    $sql = "
      SELECT 
        prestamo.numero,
        prestamo.bodega_nombre,
        DATE_FORMAT(prestamo.fecha_alta,'%d/%m/%Y') AS fecha,
        COALESCE(prestamo_estatus.nombre,'(Sin estatus)') AS estatus
      FROM prestamo
      LEFT JOIN prestamo_estatus ON prestamo_estatus.id = prestamo.estatus
      WHERE REPLACE(REPLACE(prestamo.identidad,'-',''),' ','') = '{$dni_sql}'
        AND YEAR(prestamo.fecha_alta) = YEAR(CURDATE())
      ORDER BY prestamo.fecha_alta DESC
    ";
    if ($rs = $conn->query($sql)) {
      $out['found'] = $rs->num_rows;
      while ($r = $rs->fetch_assoc())
        $out['items'][] = $r;
    }
  }
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($out);
  exit;
}



// TODO Gestiones pendientes detalle
if ($accion == "0b") {
  if (isset($_REQUEST['cid'])) {
    $solicitud = $conn->real_escape_string($_REQUEST['cid']);
  } else {
    echo mensaje("No se encontraron registros", "info");
    exit;
  }

  echo "<h4>GESTIONES PENDIENTES DE SOLICITUD No. " . $_REQUEST['num'] . "</h4><br>";


  //******* SQL ************************************************************************************

  $sql = "  SELECT prestamo_gestion.id, prestamo_gestion.fecha, prestamo_gestion.hora, prestamo_gestion.prestamo_id,
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
  if (tiene_permiso(19)) {
    $sql .= " and gestion_estado='Creditos'";
  } else {
    if (tiene_permiso(22)) { // jefe tienda
      $sql .= "  and gestion_estado='Vendedor'";
    } else { //vendedor y cobrador
      $sql .= " and usuario_dirigido='" . $_SESSION['usuario'] . "' and gestion_estado='Vendedor'";
    }
  }





  // ****** Fin SQL ********************************************************************************

  $result = $conn->query($sql);



  if ($result->num_rows > 0) {


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



    while ($row = $result->fetch_assoc()) {
      //$row["usuario_alta"].' '
      echo "<tr >

     <td class='text-center'><a  href=\"#\" class=\"btn btn-default btn-sm\" onclick=\"actualizarbox('pagina','creditos_gestion.php?a=5b1&cst=" . $row["etapa_id"] . "&cpo=" . $row["campo_id"] . "&geid=" . $row["id"] . "&cid=" . $row["prestamo_id"] . "&num=" . $row["numero"] . "&gest=" . $row["gestion_estado"] . "') ; return false;\" ><span class=\"glyphicon glyphicon-folder-open\" aria-hidden=\"true\"></span></a></td>
                                <td class='text-center'>" . $row["id"] . "</td>
                              <td class='text-center'>" . $row["antiguedad"] . "</td>
                               <td class='text-left'>" . $row["vetapa"] . "</td> 
                           
                               <td class='text-left'>" . $row["descripcion"] . "</td>
                               <td class='text-center'>" . $row["usuario"] . "</td>
                               <td class='text-center'>" . $row["nombreusuario"] . "</td>
                                <td class='text-center'>" . $row["bodega_nombre"] . "</td>
                              
                      
                              </tr>";
    }



    echo " </tbody>                   
                          </table>
                            </div>
                          </div> ";

    echo crear_datatable('tabla', 'false', true, false);
  } else {
    echo mensaje("No se encontraron registros", "info");
    exit;
  }



  // echo "<p><br><br><a href=\"#\" onclick=\"get_gestiones_regresar() ; return false;\"   class=\"btn btn-default\">REGRESAR</a></p>";
  echo "<p><br><br><a href=\"#\" onclick=\"actualizarbox('pagina','creditos.php') ; return false;\"   class=\"btn btn-default\">REGRESAR</a></p>";

  exit;
}


// panel de tareas  // TODO Panel tares
if ($accion <> "3" and $accion <> "42") {
  $sql = "";

  if (!tiene_permiso(19)) { // no es creditos
    if (tiene_permiso(22)) { // jefe tienda
      if (tiene_permiso(7)) {
        $sql .= " and " . armar_sql('prestamo.bodega', $_SESSION['grupo_bodegas'], 'or');
      } else {
        if ($_SESSION['usuario_bodega'] <> "") {
          $sql .= " and  prestamo.bodega='" . $_SESSION['usuario_bodega'] . "'";
        }
      }
    } else { // otros: vendedor y cobrador
      $sql .= " and usuario_alta='" . $_SESSION['usuario'] . "'";
    }
  }

  // Generacion de los botones de acciones a realizar 
  echo '
    <h4>PANEL DE TAREAS</h4><br>
    <div class="row">
      <div class="col-sm-3 text-center">  
        <a href="#" class="btn  btn-default  btn-block"  onclick="actualizarbox(\'pagina\',\'creditos.php?a=0\') ; return false;">Gestiones Pendientes <span class="badge">' . get_dato_sql('prestamo_gestion', 'count(*)', " where 1=1 " . get_gestiones_sql()) . '</span></a>      
        <br>
      </div>
      <div class="col-sm-3 text-center">  
        <a href="#" class="btn  btn-default  btn-block" onclick="actualizarbox(\'pagina\',\'creditos.php?a=1\') ; return false;">Solicitudes en Proceso <span class="badge">' . get_dato_sql('prestamo', 'count(*)', " where 1=1 and (prestamo.estatus=1 or (prestamo.estatus=2 and prestamo.cierre_documentos_recibidos is null)) $sql") . '</span></a>     
        <br>
      </div>
      <div class="col-sm-3 text-center">  
        <a href="#" class="btn   btn-default btn-block " onclick="actualizarbox(\'pagina\',\'creditos.php?a=25\') ; return false;">Solicitudes Finalizadas</a>    
        <br>
      </div>
      <div class="col-sm-3 text-center">  
        <a href="#" class="btn   btn-default btn-block " onclick="actualizarbox(\'pagina\',\'creditos.php?a=2\') ; return false;">Consultar Solicitudes (Todas)</a>
        <br>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-3 text-center">    
        <a href="#" onclick="actualizarbox(\'pagina\',\'creditos.php?a=3\') ; return false;"   class="btn btn-default btn-block"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Nueva Solicitud</a>
      </div>

      <div class="col-sm-3 text-center">  
        <a href="#" class="btn  btn-default btn-block " onclick="actualizarbox(\'pagina\',\'creditos.php?a=4\') ; return false;">Biblioteca de Bur&oacute; </a>    
        <br>
      </div>
    </div>
  ';

  //dashboard
  if ($accion == '') {
    echo '
      <div class="row">
        <div id="rptstat">  </div>
      </div>
      <script>
        actualizarbox(\'rptstat\',\'creditos_stat_1.php\') ;
      </script>';
  }
}


// ***** GESTIONES PENDIENTES *****
if ($accion == "000") {
  //******* SQL ************************************************************************************
  $sql = "
    SELECT  count(prestamo.id) as gestiones ,prestamo.id,prestamo.numero,prestamo.bodega_nombre, fecha_alta, usuario_alta, nombres, apellidos, identidad, monto_prestamo, monto_financiar, monto_prima, plazo, tasa, estatus, etapa_proceso
    ,prestamo_estatus.nombre as vestatus
    ,prestamo_etapa.nombre as vprestamo_etapa
    ,usuario.nombre as nombreusuario
    , prestamo.tipoprima as 'tipoprima'
	,CASE
		WHEN prestamo.tipoprima = 1 THEN 'Prima Normal'
		WHEN prestamo.tipoprima = 2 THEN 'Prima Alta (40%)'
		WHEN prestamo.tipoprima = 3 THEN 'Prima Cero'
		WHEN prestamo.tipoprima = 4 THEN 'Convenio Empresa'
		ELSE '(Prima No Definida)' 
	END AS tipoprimatext
    -- , CASE WHEN prestamo.tipoprima = 1 THEN 'Prima Normal' ELSE 'Prima Alta (40%)' END AS 'tipoprimatext'
    FROM prestamo
      LEFT OUTER JOIN prestamo_estatus ON (prestamo_estatus.id=prestamo.estatus)
      LEFT OUTER JOIN prestamo_etapa ON (prestamo_etapa.id=prestamo.etapa_proceso) 
      LEFT OUTER JOIN prestamo_gestion  ON (prestamo.id=prestamo_gestion.prestamo_id)
      LEFT OUTER JOIN usuario ON (prestamo.usuario_alta=usuario.usuario)
    WHERE 1=1

  ";

  $sql .= get_gestiones_sql();

  $sql .= "  group by prestamo.id ";
  $sql .= " ORDER BY prestamo.tipoprima DESC, prestamo.id ASC";
  // ****** Fin SQL ********************************************************************************
  //echo $sql;exit;
  $result = $conn->query($sql);

  echo "<h4>Gestiones Pendientes</h4><br>";

  if ($result->num_rows > 0) {
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
          <th class="text-center">Tipo de Prima</th>
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
          <th >Tipo de Prima</th>
        </tr>
      </tfoot>
      <tbody>';
    while ($row = $result->fetch_assoc()) { //$row["usuario_alta"].' '
      echo "
          <tr id=\"mnuchk" . $row["id"] . "\">
            <td class='text-center " . ($row["tipoprima"] == 1 ? "" : "bg-danger") . "'>
            <a  href=\"#\" class=\"btn btn-default btn-sm\" onclick=\"get_gestiones(" . $row["id"] . "," . $row["numero"] . "); return false;\" >
            <span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span></a></td>
            <td class='text-center " . ($row["tipoprima"] == 1 ? "" : "bg-danger") . "'>" . $row["numero"] . "</td>
            <td class='text-center " . ($row["tipoprima"] == 1 ? "" : "bg-danger") . "'><span class=\"badge\">" . ($row["gestiones"]) . "</span></td>
            <td class='text-left " . ($row["tipoprima"] == 1 ? "" : "bg-danger") . "'>" . $row["nombres"] . " " . $row["apellidos"] . "</td> 
            <td class='text-center " . ($row["tipoprima"] == 1 ? "" : "bg-danger") . "'>" . $row["tipoprimatext"] . "</td>
            <td class='text-center " . ($row["tipoprima"] == 1 ? "" : "bg-danger") . "'>" . $row["nombreusuario"] . "</td>
            <td class='text-center " . ($row["tipoprima"] == 1 ? "" : "bg-danger") . "'>" . $row["bodega_nombre"] . "</td>
            <td class='text-center " . ($row["tipoprima"] == 1 ? "" : "bg-danger") . "'>" . $row["vestatus"] . "</td>
            <td class='text-center " . ($row["tipoprima"] == 1 ? "" : "bg-danger") . "'>" . $row["vprestamo_etapa"] . "</td>
          </tr>";
    }

    echo " </tbody>                   
        </table>
      </div>
    </div> ";
    echo crear_datatable('tabla', 'false', true, false);
  } else {
    echo mensaje("No se encontraron registros", "info");
    exit;
  }
  exit;
}
// ***** FIN DE LAS GESTIONES PENDIENTES *****

// TODO Solicitudes en proceso           
if ($accion == "1") {
  $buscar = ""; //  if (isset($_REQUEST['sb'])) {$buscar=$conn->real_escape_string($_REQUEST['sb']);} //  if ($buscar=="") { echo mensaje( "Debe ingresar el numero de serie","warning"); exit;}

  //******* SQL ************************************************************************************
  $sql = "SELECT prestamo.id,prestamo.numero,prestamo.bodega_nombre, fecha_alta, usuario_alta, nombres, apellidos, identidad, monto_prestamo, monto_financiar, monto_prima, plazo, tasa, estatus, etapa_proceso
    ,prestamo_estatus.nombre as vestatus
    ,prestamo_etapa.nombre as vprestamo_etapa
    ,usuario.nombre as nombreusuario
    , prestamo.tipoprima as 'tipoprima'
	,CASE
		WHEN prestamo.tipoprima = 1 THEN 'Prima Normal'
		WHEN prestamo.tipoprima = 2 THEN 'Prima Alta (40%)'
		WHEN prestamo.tipoprima = 3 THEN 'Prima Cero'
		WHEN prestamo.tipoprima = 4 THEN 'Convenio Empresa'		
		ELSE '(Prima No Definida)' 
	END AS tipoprimatext
    -- , CASE WHEN prestamo.tipoprima = 1 THEN 'Prima Normal' ELSE 'Prima Alta (40%)' END AS 'tipoprimatext'
  FROM prestamo
    LEFT OUTER JOIN prestamo_estatus ON (prestamo_estatus.id=prestamo.estatus)
    LEFT OUTER JOIN prestamo_etapa ON (prestamo_etapa.id=prestamo.etapa_proceso)
    LEFT OUTER JOIN usuario ON (prestamo.usuario_alta=usuario.usuario)
  WHERE  1=1 
    AND (prestamo.estatus=1 or (prestamo.estatus=2 and prestamo.cierre_documentos_recibidos is null))";

  if (!tiene_permiso(19)) { // no es creditos
    if (tiene_permiso(22)) { // jefe tienda
      if (tiene_permiso(7)) {
        $sql .= " and " . armar_sql('prestamo.bodega', $_SESSION['grupo_bodegas'], 'or');
      } else {
        if ($_SESSION['usuario_bodega'] <> "") {
          $sql .= " and  prestamo.bodega='" . $_SESSION['usuario_bodega'] . "'";
        }
      }
    } else { // otros: vendedor y cobrador
      $sql .= " and usuario_alta='" . $_SESSION['usuario'] . "'";
    }
  }

  if ($buscar <> "") {
    $sql .= "  and ( nombres LIKE '%$buscar%' )";
  }

  $sql .= " ORDER BY prestamo.tipoprima DESC, prestamo.id ASC";
  // ****** Fin SQL ********************************************************************************
  $result = $conn->query($sql);

  echo "<h4>Solicitudes en Proceso</h4><br>";

  if ($result->num_rows > 0) {


    //  <th class="text-center">Identidad</th>
    //<div class="table-responsive">

    $reg = 0;


    echo '<div id="tablasolicitudes" class="row" >
<div class="table-responsive">
<table class="" id="tabla" width="100%" cellspacing="0">
  <thead>
    <tr>
      <th ></th>
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
    </tr>
  </thead>
  <tfoot>
    <tr>
      <th ></th>
      <th >No.</th>
      <th >Fecha</th>
      <th >Nombre</th>
      <th >Tipo de Prima</th>
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


    while ($row = $result->fetch_assoc()) {
      $colortxt = "";
      if ($row["estatus"] == 2) {
        $colortxt = " bg-success";
      }
      if ($row["estatus"] == 3) {
        $colortxt = " bg-danger";
      }

      echo "
    <tr>
      <td class='text-center " . ($row["tipoprima"] == 1 ? "$colortxt" : "bg-danger") . "'><a  href=\"#\" class=\"btn btn-default btn-sm\" onclick=\"actualizarbox('pagina','creditos_gestion.php?a=1&cid=" . $row["id"] . "') ; return false;\" ><span class=\"glyphicon glyphicon-folder-open\" aria-hidden=\"true\"></span></a></td>
      <td class='text-center " . ($row["tipoprima"] == 1 ? "$colortxt" : "bg-danger") . "'>" . $row["numero"] . "</td>
      <td class='text-center " . ($row["tipoprima"] == 1 ? "$colortxt" : "bg-danger") . "'>" . fechademysql($row["fecha_alta"]) . "</td>
      <td class='text-left " . ($row["tipoprima"] == 1 ? "$colortxt" : "bg-danger") . "'>" . $row["nombres"] . " " . $row["apellidos"] . "</td> 
      <td class='text-center " . ($row["tipoprima"] == 1 ? "$colortxt" : "bg-danger") . "'>" . $row["tipoprimatext"] . "</td>
      <td class='text-center " . ($row["tipoprima"] == 1 ? "$colortxt" : "bg-danger") . "'>" . $row["nombreusuario"] . "</td>
      <td class='text-center " . ($row["tipoprima"] == 1 ? "$colortxt" : "bg-danger") . "'>" . $row["bodega_nombre"] . "</td>
      <td class='text-right " . ($row["tipoprima"] == 1 ? "$colortxt" : "bg-danger") . "'>" . $row["monto_prestamo"] . "</td>
      <td class='text-right " . ($row["tipoprima"] == 1 ? "$colortxt" : "bg-danger") . "'>" . $row["monto_prima"] . "</td>
      <td class='text-right " . ($row["tipoprima"] == 1 ? "$colortxt" : "bg-danger") . "'>" . $row["monto_financiar"] . "</td>
      <td class='text-center " . ($row["tipoprima"] == 1 ? "$colortxt" : "bg-danger") . "'>" . $row["plazo"] . "</td>
      <td class='text-center " . ($row["tipoprima"] == 1 ? "$colortxt" : "bg-danger") . "'>" . $row["tasa"] . "</td>
      <td class='text-center " . ($row["tipoprima"] == 1 ? "$colortxt" : "bg-danger") . "'>" . $row["vestatus"] . "</td>
      <td class='text-center " . ($row["tipoprima"] == 1 ? "$colortxt" : "bg-danger") . "'>" . $row["vprestamo_etapa"] . "</td>
    </tr>";
      $reg++;
    }

    //  <td class='text-center'>".$row["identidad"]."</td>

    echo " </tbody>                   
                          </table>
                            </div>
                          </div> ";

    echo crear_datatable('tabla', 'false', true, true);


    // <div class=\"row col-xs-12\">Registros <span class=\"badge\">$reg</span></div>


  } else {
    echo mensaje("No se encontraron registros", "info");
    exit;
  }





  echo "<p><br><br><a href=\"#\" onclick=\"actualizarbox('pagina','creditos.php') ; return false;\"   class=\"btn btn-default\">REGRESAR</a></p>";

  exit;
}



// TODO Consultar Solicitudes
if ($accion == "2" or $accion == "25") {

  $buscar = "";
  //  if (isset($_REQUEST['sb'])) {$buscar=$conn->real_escape_string($_REQUEST['sb']);}


  //  if ($buscar=="") { echo mensaje( "Debe ingresar el numero de serie","warning"); exit;}

  //******* SQL ************************************************************************************
  $sqlextra2 = "";
  $extra2 = " Todas";
  if ($accion == "25") {
    $sqlextra2 = " and prestamo.cierre_documentos_recibidos='SI' ";
    $extra2 = " Finalizadas";
  }

  ///######### 17 enero 2023

  if (isset($_REQUEST['rfdesde'], $_REQUEST['rfhasta'])) {
    $fechadesde = mysqldate($conn->real_escape_string($_REQUEST['rfdesde']));
    $fechahasta = mysqldate($conn->real_escape_string($_REQUEST['rfhasta']));
    if (!checkfecha_mysql($fechadesde)) {
      $fechadesde = date('Y-m-01');
    }
    if (!checkfecha_mysql($fechahasta)) {
      $fechahasta = date('Y-m-t');
    }
  } else {
    $fechadesde = date('Y-m-01'); //Y-m-01
    $fechahasta = date('Y-m-t');
  }

  $sqlextra2 .= " and prestamo.fecha_alta BETWEEN '$fechadesde' AND '$fechahasta' ";

  ///#########

  $sql = "SELECT prestamo.id,prestamo.numero,prestamo.bodega_nombre, fecha_alta, usuario_alta, nombres, apellidos, identidad, monto_prestamo, monto_financiar, costo_rtn, monto_prima, plazo, tasa, estatus, etapa_proceso
          ,prestamo_estatus.nombre as vestatus
          ,prestamo_etapa.nombre as vprestamo_etapa
          ,prestamo.cierre_factura   ,prestamo.cierre_contrato
          ,usuario.nombre as nombreusuario     
          , prestamo.tipoprima as 'tipoprima'
		  ,CASE
				WHEN prestamo.tipoprima = 1 THEN 'Prima Normal'
				WHEN prestamo.tipoprima = 2 THEN 'Prima Alta (40%)'
				WHEN prestamo.tipoprima = 3 THEN 'Prima Cero'
				WHEN prestamo.tipoprima = 4 THEN 'Convenio Empresa'
				ELSE '(Prima No Definida)' 
			END AS tipoprimatext
          -- , CASE WHEN prestamo.tipoprima = 1 THEN 'Prima Normal' ELSE 'Prima Alta (40%)' END AS 'tipoprimatext'
          FROM prestamo
    
          LEFT OUTER JOIN prestamo_estatus ON (prestamo_estatus.id=prestamo.estatus)
          LEFT OUTER JOIN prestamo_etapa ON (prestamo_etapa.id=prestamo.etapa_proceso) 
          LEFT OUTER JOIN usuario ON (prestamo.usuario_alta=usuario.usuario)
                 WHERE  1=1      and fecha_alta > '20190101'
                    $sqlextra2";


  if (!tiene_permiso(19)) { // no es creditos
    if (tiene_permiso(22)) { // jefe tienda

      if (tiene_permiso(7)) {
        $sql .= " and " . armar_sql('prestamo.bodega', $_SESSION['grupo_bodegas'], 'or');
      } else {
        if ($_SESSION['usuario_bodega'] <> "") {
          $sql .= " and  prestamo.bodega='" . $_SESSION['usuario_bodega'] . "'";
        }
      }
    } else { // otros: vendedor y cobrador
      $sql .= " and usuario_alta='" . $_SESSION['usuario'] . "'";
    }
  }



  if ($buscar <> "") {
    $sql .= "  and ( nombres LIKE '%$buscar%' )";
  }
  $sql .= " ORDER BY prestamo.tipoprima DESC, prestamo.id ASC";

  //****** Fin SQL ********************************************************************************

  $result = $conn->query($sql);


  echo "<h4>Consultar Solicitudes$extra2</h4><br>";

  ///######### 17 enero 2023
  echo '  <form class="form form-inline" role="form">
          ' . campo("rfdesde", "Desde", 'date', fechademysql($fechadesde), ' ', ' ') . '
           
          ' . campo("rfhasta", "Hasta", 'date', fechademysql($fechahasta), ' ', ' ') . '
         
          <a id="btn-filtro" href="#" onclick=" cargarporfecha(); return false;" class="btn btn-info ">Actualizar</a>
          
 
      </form>
      <script>
        function cargarporfecha(){
          var url="creditos.php?a=' . $accion . '";
          url=url+"&rfdesde="+$("#rfdesde").val();
          url=url+"&rfhasta="+$("#rfhasta").val();
          actualizarbox(\'pagina\',url) ;
        }
      </script>
      <br>
      ';

  ///######### 


  if ($result->num_rows > 0) {


    //  <th class="text-center">Identidad</th>
    //<div class="table-responsive">

    $reg = 0;
    echo '<div id="tablasolicitudes" class="row">
                    <div class="table-responsive">
                      <table class="" id="tabla" width="100%" cellspacing="0">
                        <thead>
                          <tr>
                            <th ></th>
                            <th class="text-center">No.</th>
                            <th class="text-center">Fecha</th>
                            <th class="text-center">Nombre</th>
                            <th class="text-center">Tipo de Prima</th>
                            <th class="text-center">Vendedor</th>
                            <th class="text-center">Tienda</th>
                            <th class="text-center">V. Prestamo</th>
							<th class="text-center">RTN</th>
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
      <th >Tipo de Prima</th>
      <th >Vendedor</th>
      <th >Tienda</th>
      <th >V. Prestamo</th>
	  <th >RTN</th>
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


    while ($row = $result->fetch_assoc()) {
      $colortxt = "";
      if ($row["estatus"] == 2) {
        $colortxt = " bg-success";
      }
      if ($row["estatus"] == 3) {
        $colortxt = " bg-danger";
      }
      echo "<tr >

    
    <td class='text-center " . ($row["tipoprima"] == 1 ? "$colortxt" : "bg-danger") . "'><a  href=\"#\" class=\"btn btn-default btn-sm\" onclick=\"actualizarbox('pagina','creditos_gestion.php?a=1&cid=" . $row["id"] . "') ; return false;\"><span class=\"glyphicon glyphicon-folder-open\" aria-hidden=\"true\"></span></a></td>
    <td class='text-center " . ($row["tipoprima"] == 1 ? "$colortxt" : "bg-danger") . "'>" . $row["numero"] . "</td>
    <td class='text-center " . ($row["tipoprima"] == 1 ? "$colortxt" : "bg-danger") . "'>" . fechademysql($row["fecha_alta"]) . "</td>
    <td class='text-left " . ($row["tipoprima"] == 1 ? "$colortxt" : "bg-danger") . "'>" . $row["nombres"] . " " . $row["apellidos"] . "</td>
    <td class='text-center " . ($row["tipoprima"] == 1 ? "$colortxt" : "bg-danger") . "'>" . $row["tipoprimatext"] . "</td>
    <td class='text-center " . ($row["tipoprima"] == 1 ? "$colortxt" : "bg-danger") . "'>" . $row["nombreusuario"] . "</td>
    <td class='text-center " . ($row["tipoprima"] == 1 ? "$colortxt" : "bg-danger") . "'>" . $row["bodega_nombre"] . "</td>
    <td class='text-right " . ($row["tipoprima"] == 1 ? "$colortxt" : "bg-danger") . "'>" . $row["monto_prestamo"] . "</td>
	<td class='text-right " . ($row["tipoprima"] == 1 ? "$colortxt" : "bg-danger") . "'>" . $row["costo_rtn"] . "</td>
    <td class='text-right  " . ($row["tipoprima"] == 1 ? "$colortxt" : "bg-danger") . "'>" . $row["monto_prima"] . "</td>
    <td class='text-right  " . ($row["tipoprima"] == 1 ? "$colortxt" : "bg-danger") . "'>" . $row["monto_financiar"] . "</td>
    <td class='text-center  " . ($row["tipoprima"] == 1 ? "$colortxt" : "bg-danger") . "'>" . $row["plazo"] . "</td>
    <td class='text-center  " . ($row["tipoprima"] == 1 ? "$colortxt" : "bg-danger") . "'>" . $row["tasa"] . "</td>
    <td class='text-center  " . ($row["tipoprima"] == 1 ? "$colortxt" : "bg-danger") . "'>" . $row["cierre_factura"] . "</td>
    <td class='text-center  " . ($row["tipoprima"] == 1 ? "$colortxt" : "bg-danger") . "'>" . $row["cierre_contrato"] . "</td>
    <td class='text-center  " . ($row["tipoprima"] == 1 ? "$colortxt" : "bg-danger") . "'>" . $row["vestatus"] . "</td>
    <td class='text-center  " . ($row["tipoprima"] == 1 ? "$colortxt" : "bg-danger") . "'>" . $row["vprestamo_etapa"] . "</td>
                            
               
                      
                              </tr>";
      $reg++;
    }

    //  <td class='text-center'>".$row["identidad"]."</td>

    echo " </tbody>                   
                          </table>
                            </div>
                          </div> ";

    echo crear_datatable('tabla', 'false', true, true);


    // <div class=\"row col-xs-12\">Registros <span class=\"badge\">$reg</span></div>


  } else {
    echo mensaje("No se encontraron registros", "info");
    exit;
  }





  echo "<p><br><br><a href=\"#\" onclick=\"actualizarbox('pagina','creditos.php') ; return false;\"   class=\"btn btn-default\">REGRESAR</a></p>";

  exit;
}



// TODO NUEVA Solicitud
if ($accion == "3") {
  $API_key = "@B1F5E814-4";
  $dni = "0703198600808";
  $url = "http://web.grupomovesa.com/portal/modulo_creditos/services/portalCreditosAPI.services.php";
  $url .= "?request=verificar_resolucion_scoring&dni={$dni}&token={$API_key}";
  $response = file_get_contents($url);
  //echo "Debug: Response from API: " . $response;

  //	$echo $_SESSION['usuario'];

  // session_start();

  // echo "Usuario: " . $_SESSION['usuario'] . "<br>";
// echo "Nombre: " . $_SESSION['usuario_nombre'];

  //	$nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario no definido';
//	echo $nombreUsuario;



  if (isset($_REQUEST['s'])) { // crear Solicitud nueva
    //########## validar datos
    // echo var_dump($_REQUEST["tipo_prima"]);
    $verror = "";
    $verror .= validar("Tienda", $_REQUEST['bodega'], "text", true, null, 1, null);
    $verror .= validar("Nombres", $_REQUEST['nombres'], "text", true, null, 3, null);
    $verror .= validar("Apellidos", $_REQUEST['apellidos'], "text", true, null, 3, null);
    $verror .= validar("Identidad", $_REQUEST['identidad'], "text", true, null, 13, null);

    $verror .= validar("Valor Motocicleta", $_REQUEST['monto_prestamo'], "text", true, null, 3, null);
    //   $verror.=validar("Prima",$_REQUEST['monto_prima'], "text", true,  null,  1,  null);
    $verror .= validar("Total Financiar", $_REQUEST['monto_financiar'], "text", true, null, 3, null);
    $verror .= validar("Plazo", $_REQUEST['plazo'], "text", true, null, 1, null);
    //   $verror.=validar("Tasa",$_REQUEST['tasa'], "text", true,  null,  2,  null);

    if ($_REQUEST["tipo_persona"] == "Persona Juridica" and $_REQUEST["nombre_empresa"] == "") {
      $verror .= "Debe ingresar el nombre de la empresa";
    }

    if ($verror == "") {

      $sqlcampos = "";
      $sqlcampos .= "  nombres =" . GetSQLValue($conn->real_escape_string($_REQUEST["nombres"]), "text");
      $sqlcampos .= " , apellidos =" . GetSQLValue($conn->real_escape_string($_REQUEST["apellidos"]), "text");
      $sqlcampos .= " , identidad =" . GetSQLValue($conn->real_escape_string($_REQUEST["identidad"]), "text");

      $sqlcampos .= " , nombre_empresa_rtn =" . GetSQLValue($conn->real_escape_string($_REQUEST["nombre_empresa_rtn"]), "text");
      $sqlcampos .= " , nombre_empresa =" . GetSQLValue($conn->real_escape_string($_REQUEST["nombre_empresa"]), "text");
      $sqlcampos .= " , tipo_persona =" . GetSQLValue($conn->real_escape_string($_REQUEST["tipo_persona"]), "text");

      $sqlcampos .= " , monto_seguro =" . GetSQLValue($conn->real_escape_string($_REQUEST["monto_seguro"]), "text");
      $sqlcampos .= " , monto_prestamo =" . GetSQLValue($conn->real_escape_string($_REQUEST["monto_prestamo"]), "text");
      $sqlcampos .= " , monto_prima =" . GetSQLValue($conn->real_escape_string($_REQUEST["monto_prima"]), "text");
      $sqlcampos .= " , gastos_administrativos =" . GetSQLValue($conn->real_escape_string($_REQUEST["gastos_administrativos"]), "text");
      $sqlcampos .= " , monto_financiar =" . GetSQLValue($conn->real_escape_string($_REQUEST["monto_financiar"]), "text");
      $sqlcampos .= " , costo_rtn = " . GetSQLValue($conn->real_escape_string($_REQUEST["costo_rtn"]), "text");
      $sqlcampos .= " , plazo =" . GetSQLValue($conn->real_escape_string($_REQUEST["plazo"]), "text");
      $sqlcampos .= " , cliente_nuevo_recompra =" . GetSQLValue($conn->real_escape_string($_REQUEST["cliente_nuevo_recompra"]), "text");
      $sqlcampos .= " , tipo_identificacion =" . GetSQLValue($conn->real_escape_string($_REQUEST["tipo_identificacion"]), "text");
      $sqlcampos .= " , rtn =" . GetSQLValue($conn->real_escape_string($_REQUEST["rtn"]), "text");
      $sqlcampos .= " , codigo_cliente =" . GetSQLValue($conn->real_escape_string($_REQUEST["codigo_cliente"]), "text");
      $sqlcampos .= " , clave_enee =" . GetSQLValue($conn->real_escape_string($_REQUEST["clave_enee"]), "text");
      $sqlcampos .= " , producto_servicio =" . GetSQLValue($conn->real_escape_string($_REQUEST["producto_servicio"]), "text");
      $sqlcampos .= " , otro_cargo_servicio_especificar =" . GetSQLValue($conn->real_escape_string($_REQUEST["otro_cargo_servicio_especificar"]), "text");
      $sqlcampos .= " , compra_productos =" . GetSQLValue($conn->real_escape_string($_REQUEST["compra_productos"]), "text");
      $sqlcampos .= " , uso_unidad =" . GetSQLValue($conn->real_escape_string($_REQUEST["uso_unidad"]), "text");
      $sqlcampos .= " , moto_categoria =" . GetSQLValue($conn->real_escape_string($_REQUEST["moto_categoria"]), "text");
      $sqlcampos .= " , moto_modelo =" . GetSQLValue($conn->real_escape_string($_REQUEST["moto_modelo"]), "text");
      $sqlcampos .= " , moto_marca =" . GetSQLValue($conn->real_escape_string($_REQUEST["moto_marca"]), "text");
      $sqlcampos .= " , cantidad_vehiculos =" . GetSQLValue($conn->real_escape_string($_REQUEST["cantidad_vehiculos"]), "text");

      $sqlcampos .= " , aplica_promocion_octubre = " . GetSQLValue($conn->real_escape_string($_REQUEST["aplica_promocion_octubre"]), "int");


      switch ($_REQUEST["tipo_prima"]) {
        case 1:
          $sqlcampos .= " , tasa =" . GetSQLValue(get_dato_sql('opciones', 'valor', ' where id=1'), "text");
          $sqlcampos .= ", cierre_interes_mensual = 0";

          // $sqlcampos .= " , cierre_interes_mensual =" . GetSQLValue(get_dato_sql('opciones', 'valor', ' where id=5'), "text");
          break;
        case 2:
          $sqlcampos .= " , tasa =" . GetSQLValue(get_dato_sql('opciones', 'valor', ' where id=2'), "text");

          $sqlcampos .= ", cierre_interes_mensual = 0";
          // $sqlcampos .= " , cierre_interes_mensual =" . GetSQLValue(get_dato_sql('opciones', 'valor', ' where id=6'), "text");
          break;
        case 3:
          $sqlcampos .= " , tasa =" . GetSQLValue(get_dato_sql('opciones', 'valor', ' where id=3'), "text");

          $sqlcampos .= ", cierre_interes_mensual = 0";
          //   $sqlcampos .= " , cierre_interes_mensual =" . GetSQLValue(get_dato_sql('opciones', 'valor', ' where id=3'), "text");
          //   break;
          // case 4:
          //   $sqlcampos .= " , tasa =" . GetSQLValue(get_dato_sql('opciones', 'valor', ' where id=4'), "text");
          //   $sqlcampos .= " , cierre_interes_mensual =" . GetSQLValue(get_dato_sql('opciones', 'valor', ' where id=8'), "text");
          break;
        case 4:
          $sqlcampos .= " , tasa =" . GetSQLValue(get_dato_sql('opciones', 'valor', ' where id=4'), "text");
          $sqlcampos .= ", cierre_interes_mensual = 0";
          break;
        default:
          $sqlcampos .= " , tasa =" . GetSQLValue(get_dato_sql('opciones', 'valor', ' where id=1'), "text");
          $sqlcampos .= " , cierre_interes_mensual =" . GetSQLValue(get_dato_sql('opciones', 'valor', ' where id=5'), "text");
          break;
      }

      $sqlcampos .= " , estatus =1";
      $sqlcampos .= " , etapa_proceso =1";
      $sqlcampos .= " , tipoprima = " . $_REQUEST["tipo_prima"];
      $sqlcampos .= " , nombre_vendedor = '" . $_SESSION['usuario_nombre'] . "'"; //agregado
      // $sqlcampos .= " , nombre_vendedor = '{$_SESSION['usuario_nombre']}' "; concatenar mejor de esta manera
      $sqlcampos .= ",usuario_alta= '" . $_SESSION['usuario'] . "' ,fecha_alta=now()";


      if (tiene_permiso(26)) {
        $sqlcampos .= " , canal='CI'";
      }

      $cod_bodega = $conn->real_escape_string($_REQUEST["bodega"]);
      $bodega_nombre = get_dato_sql('bodega', 'nombre', " where codigo='$cod_bodega'");


      $sqlcampos .= ",bodega= '$cod_bodega' ,bodega_nombre='$bodega_nombre'";

      $numero_sol = get_dato_sql('prestamo', '(ifnull(max(numero),0)+1)', "  "); //where bodega='$cod_bodega'
      $sqlcampos .= " , numero =" . $numero_sol;


      $sql = "insert into prestamo set" . $sqlcampos;


      if ($conn->query($sql) === TRUE) {
        $insert_id = mysqli_insert_id($conn);
        // crear registro para aval
        $conn->query("insert into prestamo_aval set id=$insert_id ,numero=$numero_sol");
        $stud_arr[0]["pcode"] = 1;
        $stud_arr[0]["pmsg"] = 'Los datos fueron guardados satisfactoriamente. El numero de Solicitud es: <strong>' . $numero_sol . '</strong>';
        $stud_arr[0]["pcodid"] = $insert_id;
      } else {
        $stud_arr[0]["pcode"] = 0;
        $stud_arr[0]["pmsg"] = 'Se produjo un error al guardar el registro DB101:<br>' . $conn->error;
        $stud_arr[0]["pcodid"] = 0;
      }

      $conn->close();
    } else {
      //mostrar errores validacion
      $stud_arr[0]["pcode"] = 0;
      $stud_arr[0]["pmsg"] = 'Error en los datos:</strong><br>' . $verror;
      $stud_arr[0]["pcodid"] = 0;
    }


    echo salida_json($stud_arr);
    exit;
  }

  echo "<h4>Nueva Solicitud</h4>";
  echo ' <div class="panel panel-default"> <div id="datosgenerales" class="panel-body"> <div class="row"> <div class="col-xs-12"> <form id="solform" class="form-horizontal">';

  $campo_bodega = 'codigo';
  $campo_dist = 'id_distribuidor';
  $sql = "";
  if (tiene_permiso(7)) {
    $texto = armar_sql($campo_dist, $_SESSION['grupo_distribuidores'], 'or');
    if ($texto <> "") {
      $sql .= " and $texto";
    }

    $texto = armar_sql($campo_bodega, $_SESSION['grupo_bodegas'], 'or');
    if ($texto <> "") {
      $sql .= " and $texto";
    }
  } else {
    if ($_SESSION['usuario_bodega'] <> "") {
      $sql .= " and  $campo_bodega='" . $_SESSION['usuario_bodega'] . "'";
    }
    if ($_SESSION['usuario_distribuidor'] <> "") {
      $sql .= " and $campo_dist='" . $_SESSION['usuario_distribuidor'] . "'";
    }
    // echo $sql;exit;   
  }
  //echo var_dump($_SESSION);


  echo campo("bodega", "Tienda", 'select', valores_combobox_db('bodega', '', "nombre as texto", " where 1=1 {$sql} order by nombre", 'texto', false, 'codigo'), 'class="form-control" ', '', '', 3, 7);
  echo "<hr>";


  echo campo("tipo_persona", "Tipo", 'select', valores_combobox_texto('<option value="Persona Natural">Persona Natural</option><option value="Persona Juridica">Persona Juridica</option>', ''), 'class="form-control" onchange="$(\'#pjuridica\').toggle();"', '', '', 3, 5);



  // Add the field: tipo prima
  echo campo("tipo_prima", "Tipo de Prima", 'select', valores_combobox_texto('<option value="1">Prima Normal</option><option value="2">Prima Alta (40%)</option><option value="3">Prima Cero</option><option value="4">Convenio Empresa</option>', ''), 'class="form-control" "', '', '', 3, 5);

  echo campo("cliente_nuevo_recompra", "El cliente es", 'select', valores_combobox_texto('<option value="Nuevo">Cliente Nuevo</option><option value="Recompra">Cliente Recompra</option>', ''), 'class="form-control" "', '', '', 3, 5);

  //<option value="3">Prima Informal</option><option value="4">Prima KTM</option>


  //Tipo credito funcionando con cambio a 24 meses si es 40% de prima
  //echo campo("tipo_prima","Tipo de Prima",'select',valores_combobox_texto('<option value="1">Prima Normal</option><option value="2">Prima Alta (40%)</option>',''),'class="form-control" onchange="selTipCred(); "','','',3,5);





  echo '<div id="pjuridica" style="display: none">';

  echo campo("nombre_empresa", "Nombre Empresa", 'text', '', 'class="form-control" ', '', '', 3, 7);
  echo campo("nombre_empresa_rtn", "RTN Empresa", 'text', '', 'class="form-control" data-mask="99999999999999"', '', '', 3, 7);
  echo '<h4>Datos del representante Legal:</h4>';
  echo '</div>';
  echo campo("nombres", "Nombres", 'text', '', 'class="form-control" ', '', '', 3, 7);
  echo campo("apellidos", "Apellidos", 'text', '', 'class="form-control" ', '', '', 3, 7);
  echo campo("tipo_identificacion", "Tipo de Identificacion", 'select', valores_combobox_texto('
  <option value="DNI">DNI</option>
  <option value="ID Vieja">ID Vieja</option>
  <option value="Carne de Residente">Carné de Residente</option>
  <option value="Licencia de Conducir Vigente">Licencia de Conducir Vigente</option>', ''), 'class="form-control" "', '', '', 3, 5);
  echo campo("identidad", "Identidad", 'text', '', 'class="form-control" data-mask="9999-9999-99999?99" ', '', '', 3, 3);
  echo '<div id="identidad-alert" class="alert alert-warning" role="alert" style="display:none;margin-top:6px;"></div>';

  echo campo("rtn", "No. RTN", 'text', '', 'class="form-control" data-mask="9999-9999-99999?99" ', '', '', 3, 3);
  echo campo("codigo_cliente", "Codigo de Cliente", 'text', '', 'class="form-control" ', '', '', 3, 3);
  echo campo("clave_enee", "Clave Primaria Empresa de Energía Eléctrica", 'text', '', 'class="form-control" ', '', '', 3, 3);
  echo "<hr>";

  // Productos y/o servicios solicitados
  echo campo("producto_servicio", "Productos y/o servicios solicitados", 'select', valores_combobox_texto('
  <option value="Vehiculo Nuevo">Vehículo Nuevo</option>
  <option value="Vehiculo Usado">Vehículo Usado</option>
  <option value="Flota de Vehiculos">Flota de Vehículos</option>
  <option value="Servicio de Taller de Mecanica">Servicio de Taller de Mecánica</option>
  <option value="Servicio de Taller de Pintura y Enderezado">Servicio de Taller de Pintura y Enderezado</option>
  <option value="Productos Automotrices">Productos Automotrices</option>
  <option value="Otros">Otros</option>
  ', ''), 'class="form-control" "', '', '', 3, 5);
  echo campo("otro_cargo_servicio_especificar", "En caso de ser otro servicio solicitado, especificar", 'text', '', 'class="form-control" ', '', '', 3, 5);
  echo campo("compra_productos", "En caso de compra de Productos Automotrices o Servicio Taller (Especificar): ", 'text', '', 'class="form-control" ', '', '', 3, 5);

  echo campo("uso_unidad", "Uso de la unidad", 'select', valores_combobox_texto('<option value="Personal">Personal</option><option value="Comercial">Comercial</option>', ''), 'class="form-control" "', '', '', 3, 5);

  echo campo("moto_categoria", "Categoria", 'select', valores_combobox_texto('<option id="" value="motocicleta">Motocicleta</option><option id="" value="motocargo">Motocargo</option><option id="" value="mototaxi">Mototaxi</option><option id="" value="cuatrimoto">Cuatrimoto</option><option id="" value="vehiculo">Vehiculo</option>', "motocicleta"), 'class=" form-control"', '', '', 3, 5);

  echo campo("moto_marca", "Ingrese la marca de la unidad", 'text', '', 'class="form-control" ', '', '', 3, 4);

  echo campo("moto_modelo", "Ingrese el modelo de la unidad", 'text', '', 'class="form-control" ', '', '', 3, 4);

  echo campo("cantidad_vehiculos", "Cantidad de Vehiculos", 'number', '', 'class="form-control"', '', '', 3, 3);

  echo campo("monto_prestamo", "Valor Motocicleta", 'text', '', 'class="form-control" onchange="$(\'#monto_financiar\').val(
    convertir_num($(\'#monto_prestamo\').val()) +
    convertir_num($(\'#monto_seguro\').val()) +
    convertir_num($(\'#gastos_administrativos\').val()) +
    convertir_num($(\'#costo_rtn\').val()) -
    convertir_num($(\'#monto_prima\').val())
);"', '', '', 3, 3);

  echo campo("monto_seguro", "Valor del Seguro", 'text', '', 'class="form-control" onchange="$(\'#monto_financiar\').val(
    convertir_num($(\'#monto_prestamo\').val()) +
    convertir_num($(\'#monto_seguro\').val()) +
    convertir_num($(\'#gastos_administrativos\').val()) +
    convertir_num($(\'#costo_rtn\').val()) -
    convertir_num($(\'#monto_prima\').val())
);"', '', '', 3, 3);

  echo campo("monto_prima", "Prima", 'text', '', 'class="form-control" onchange="$(\'#monto_financiar\').val(
    convertir_num($(\'#monto_prestamo\').val()) +
    convertir_num($(\'#monto_seguro\').val()) +
    convertir_num($(\'#gastos_administrativos\').val()) +
    convertir_num($(\'#costo_rtn\').val()) -
    convertir_num($(\'#monto_prima\').val())
);"', '', '', 3, 3);

  $gastos = isset($row["gastos_administrativos"]) ? $row["gastos_administrativos"] : 0;

  if (strpos($_SESSION['usuario'], 'Cd') !== false && strpos($_SESSION['usuario'], 'Cd31') === false) {
    // Usuario contiene 'Cd' PERO NO es 'Cd31' → ocultar
    echo campo("gastos_administrativos", "Gastos Administrativos", 'text', $gastos, 'class="form-control" onchange="$(\'#monto_financiar\').val(
        convertir_num($(\'#monto_prestamo\').val()) +
        convertir_num($(\'#monto_seguro\').val()) +
        convertir_num($(\'#gastos_administrativos\').val()) +
        convertir_num($(\'#costo_rtn\').val()) -
        convertir_num($(\'#monto_prima\').val())
    );"', '', '', 3, 3, 'style="display:none;"');
  } else {
    // Usuarios que no contienen 'Cd' o son 'Cd31' → mostrar
    echo campo("gastos_administrativos", "Gastos Administrativos", 'text', $gastos, 'class="form-control" onchange="$(\'#monto_financiar\').val(
        convertir_num($(\'#monto_prestamo\').val()) +
        convertir_num($(\'#monto_seguro\').val()) +
        convertir_num($(\'#gastos_administrativos\').val()) +
        convertir_num($(\'#costo_rtn\').val()) -
        convertir_num($(\'#monto_prima\').val())
    );"', '', '', 3, 3);
  }



  // NUEVO CAMPO: Costo RTN
  echo campo("costo_rtn", "Costo RTN", 'text', '', 'class="form-control" onchange="
    $(\'#monto_financiar\').val(
        convertir_num($(\'#monto_prestamo\').val()) +
        convertir_num($(\'#monto_seguro\').val()) +
        convertir_num($(\'#gastos_administrativos\').val()) +
        convertir_num($(\'#costo_rtn\').val()) -
        convertir_num($(\'#monto_prima\').val())
    );
"', '', '', 3, 3);

  echo campo("monto_financiar", "Total Financiar", 'text', '', 'class="form-control"  readonly', '', '', 3, 3);
  $option_values = convertir_array_dropdown($lista_plazos, $row["plazo"], true, "value", "value");
  echo campo("plazo", "Plazo", 'select', $option_values, 'class="form-control" ', '', '', 3, 2);
  //  echo campo("tasa","Tasa",'text','10','class="form-control" data-mask="9?9"','','',3,2);
  echo campo("tasa", '', 'hidden', '', '', '', '');
  echo campo(
    "aplica_promocion_octubre",
    "¿Aplica promoción de octubre?",
    'select',
    valores_combobox_texto('<option value="0">No</option><option value="1">Sí</option>', ''),
    'class="form-control"',
    '',
    '',
    3,
    5
  );



  ?> <br>
  <div id="botones">
    <a id="btnguardar" href="#" class="btn btn-primary" onclick="procesarforma() ; return false;"><span
        class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar</a>
    <a id="btnimprimir" href="#" style="display: none;" class="btn btn-info"
      onclick="actualizarbox('pagina','creditos_gestion.php?a=1&cid='+$('#ridg').val()) ;  return false;">Continuar</a>

    <input id="ridg" name="" ridg type="hidden" value="" />

    <img id="cargando" style="display: none;" src="images/load.gif" />

    &nbsp;&nbsp;&nbsp;<a id="btnregresar" href="#" onclick="actualizarbox('pagina','creditos.php') ; return false;"
      class="btn btn-default">REGRESAR</a>
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
      $.getJSON(url, $("#solform").serialize(), function (json) {

        i = 1;
        if (json.length > 0) {
          if (json[0].pcode == 0) {

            $('#salida').empty().append('<div class="alert alert-warning" role="alert">' + json[0].pmsg + '</div>');

          }
          if (json[0].pcode == 1) {

            if (json[0].pcodid != 0) {
              $("#ridg").val(json[0].pcodid);
              $('#btnimprimir').show();

            }
            $("#datosgenerales *").attr("disabled", "disabled");
            $('#btnguardar').hide();
            $('#btnregresar').hide();
            $('#salida').empty().append('<div class="alert alert-success" role="alert">' + json[0].pmsg + '</div>');

          }
        } else {
          $('#salida').empty().append('<div class="alert alert-danger" role="alert">Se produjo un error en comunicacion JSON:101</div>');
        }

      }).error(function () {
        $('#salida').empty().append('<div class="alert alert-danger" role="alert">Se produjo un error en comunicacion JSON:102</div>');
      }).complete(function () {

        $('#cargando').hide();
        $("#solform :input").attr('readonly', false);
        $("#botones *").removeAttr("disabled");
      });
    }

    (function () {
      function pintarAlerta(res) {
        var $input = $('#identidad');
        var $grp = $input.closest('.form-group');
        var $alrt = $('#identidad-alert');

        if (res && res.found > 0) {
          var lista = res.items.map(function (x) {
            return '<li>#' + x.numero + ' · ' + x.bodega_nombre + ' · ' + x.estatus + ' · ' + x.fecha + '</li>';
          }).join('');
          $alrt.html('<strong>Atención:</strong> ya existe ' + res.found + ' solicitud(es) este año.<ul style="margin:6px 0 0 18px;">' + lista + '</ul>').show();
          $grp.addClass('has-warning');
        } else {
          $alrt.hide().empty();
          $grp.removeClass('has-warning');
        }
      }

      var t;
      function chequear() {
        var raw = ($('#identidad').val() || '').replace(/\D/g, '');
        if (raw.length < 13) { pintarAlerta(null); return; }
        $.getJSON('creditos.php?a=chkdup&identidad=' + encodeURIComponent(raw))
          .done(pintarAlerta)
          .fail(function () { pintarAlerta(null); });
      }

      // Dispara en tecleo (con debounce), blur y change
      $(document).on('keyup blur change', '#identidad', function (e) {
        if (e.type === 'keyup') { clearTimeout(t); t = setTimeout(chequear, 400); }
        else { chequear(); }
      });

      // Por si viene precargado
      setTimeout(chequear, 0);
    })();

  </script>

  <?php


  echo " </form></div></div></div></div>";


  exit;
}

// TODO Biblioteca de buro
if ($accion == "4") {

  $buscar = "";
  //  if (isset($_REQUEST['sb'])) {$buscar=$conn->real_escape_string($_REQUEST['sb']);}


  //  if ($buscar=="") { echo mensaje( "Debe ingresar el numero de serie","warning"); exit;}

  //******* SQL ************************************************************************************

  $sql = "SELECT id, fecha_alta, usuario_alta, bodega_nombre, nombres, apellidos, identidad,rtn, doc1
    FROM prestamo_biblioteca_buro
                    WHERE  1=1
                    order by nombres ,apellidos, fecha_alta";


  if ($buscar <> "") {
    $sql .= "  and ( nombres LIKE '%$buscar%' )";
  }

  // ****** Fin SQL ********************************************************************************

  $result = $conn->query($sql);

  echo "    <p><a href=\"#\" onclick=\"actualizarbox('pagina','creditos.php?a=42') ; return false;\"   class=\"btn btn-default\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Agregar Nuevo Buró</a></p><br>";


  echo "<h4>Biblioteca de Bur&oacute;</h4><br>";


  if ($result->num_rows > 0) {


    //  <th class="text-center">Identidad</th>
    //<div class="table-responsive">

    $reg = 0;
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


    while ($row = $result->fetch_assoc()) {
      echo "<tr>

     
                              
                            
                              <td class='text-left'>" . $row["nombres"] . " " . $row["apellidos"] . "</td> 
                              <td class='text-center'>" . $row["identidad"] . "</td>
                              <td class='text-center'>" . $row["rtn"] . "</td>
                               <td class='text-center'>" . campo("docv" . $row["id"], "", 'uploadlink', $row["doc1"], 'class="form-control" ', '') . "</td>                  
                              <td class='text-center'>" . fechademysql($row["fecha_alta"]) . "</td>
                              <td class='text-center'>" . $row["usuario_alta"] . "</td>
                       
                              
                      
                              </tr>";
      $reg++;
    }

    //  <td class='text-center'>".$row["identidad"]."</td>

    echo " </tbody>                   
                          </table>
                            </div>
                          </div> ";

    echo crear_datatable('tabla', 'false', true, false);


    // <div class=\"row col-xs-12\">Registros <span class=\"badge\">$reg</span></div>


  } else {
    echo mensaje("No se encontraron registros", "info");
    exit;
  }





  echo "<p><br><br><a href=\"#\" onclick=\"actualizarbox('pagina','creditos.php') ; return false;\"   class=\"btn btn-default\">REGRESAR</a></p>";

  exit;
}

// TODO NUEVA buro
if ($accion == "42") {

  if (isset($_REQUEST['s'])) { // crear  nueva
    //########## validar datos
    $verror = "";
    $verror .= validar("Nombres", $_REQUEST['nombres'], "text", true, null, 3, null);
    $verror .= validar("Apellidos", $_REQUEST['apellidos'], "text", true, null, 3, null);
    // $verror.=validar("Identidad",$_REQUEST['identidad'], "text", true,  null,  13,  null);
    $verror .= validar("Documento Buro", $_REQUEST['doc1'], "text", true, null, 4, null);

    if ($_REQUEST['identidad'] == '' and $_REQUEST['rtn'] == '') {
      $verror .= 'Debe ingresar Identidad o RTN';
    }

    if ($verror == "") {

      $sqlcampos = "";

      $sqlcampos .= "  nombres =" . GetSQLValue($conn->real_escape_string($_REQUEST["nombres"]), "text");
      $sqlcampos .= " , apellidos =" . GetSQLValue($conn->real_escape_string($_REQUEST["apellidos"]), "text");
      $sqlcampos .= " , identidad =" . GetSQLValue($conn->real_escape_string($_REQUEST["identidad"]), "text");
      $sqlcampos .= " , rtn =" . GetSQLValue($conn->real_escape_string($_REQUEST["rtn"]), "text");

      if (isset($_REQUEST["doc1"])) {
        $sqlcampos .= " , doc1 =" . GetSQLValue($conn->real_escape_string($_REQUEST["doc1"]), "text");
      }

      $sqlcampos .= ",usuario_alta= '" . $_SESSION['usuario'] . "' ,fecha_alta=now()";


      //  $cod_bodega=$conn->real_escape_string($_REQUEST["bodega"]); 
      // $bodega_nombre= get_dato_sql('bodega', 'nombre', " where codigo='$cod_bodega'");


      //   $sqlcampos.= ",bodega= '$cod_bodega' ,bodega_nombre='$bodega_nombre'";


      $sql = "insert into prestamo_biblioteca_buro set " . $sqlcampos;

      if ($conn->query($sql) === TRUE) {
        $insert_id = mysqli_insert_id($conn);

        $stud_arr[0]["pcode"] = 1;
        $stud_arr[0]["pmsg"] = 'Los datos fueron guardados satisfactoriamente.';
        $stud_arr[0]["pcodid"] = $insert_id;
      } else {
        $stud_arr[0]["pcode"] = 0;
        $stud_arr[0]["pmsg"] = 'Se produjo un error al guardar el registro DB101:<br>' . $conn->error;
        $stud_arr[0]["pcodid"] = 0;
      }

      $conn->close();
    } else {
      //mostrar errores validacion
      $stud_arr[0]["pcode"] = 0;
      $stud_arr[0]["pmsg"] = 'Error en los datos:</strong><br>' . $verror;
      $stud_arr[0]["pcodid"] = 0;
    }


    echo salida_json($stud_arr);
    exit;
  }

  echo "<h4>Nuevo Documento de Buro</h4>";
  echo ' <div class="panel panel-default"> <div id="datosgenerales" class="panel-body"> <div class="row"> <div class="col-xs-12"> <form id="solform" class="form-horizontal">';


  $sql = "";


  echo campo("nombres", "Nombres", 'text', '', 'class="form-control" ', '', '', 3, 7);
  echo campo("apellidos", "Apellidos", 'text', '', 'class="form-control" ', '', '', 3, 7);
  echo campo("identidad", "Identidad", 'text', '', 'class="form-control" data-mask="9999-9999-99999?99" ', '', '', 3, 3);
  echo campo("rtn", "RTN", 'text', '', 'class="form-control" data-mask="99999999999999" ', '', '', 3, 3);

  echo campo("doc1", "Documento Buro", 'upload', '', 'class="form-control" ');



  ?>
  <br>
  <div id="botones">
    <a id="btnguardar" href="#" class="btn btn-primary" onclick="procesarforma() ; return false;"><span
        class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar</a>

    <input id="ridg" name="" ridg type="hidden" value="" />

    <img id="cargando" style="display: none;" src="images/load.gif" />
    &nbsp;&nbsp;&nbsp;<a id="btnregresar" href="#" onclick="actualizarbox('pagina','creditos.php?a=4') ; return false;"
      class="btn btn-default">REGRESAR</a>
    <div class="row">
      <br>
      <div id="salida"> </div>
    </div>
  </div>

  <script>
    // Script para Guardar el documento/Solicitud
    function procesarforma() {
      $("#botones *").attr("disabled", "disabled");
      $("#solform :input").attr('readonly', true);
      $('#cargando').show();
      var myTable = '';

      var url = "creditos.php?a=42&s=1";
      $.getJSON(url, $("#solform").serialize(), function (json) {
        i = 1;
        if (json.length > 0) {
          if (json[0].pcode == 0) {
            $('#salida').empty().append('<div class="alert alert-warning" role="alert">' + json[0].pmsg + '</div>');
          }
          if (json[0].pcode == 1) {
            if (json[0].pcodid != 0) {
              $("#ridg").val(json[0].pcodid);
            }
            $("#datosgenerales *").attr("disabled", "disabled");
            $('#btnguardar').hide();
            //  $('#btnregresar').hide();
            $('#salida').empty().append('<div class="alert alert-success" role="alert">' + json[0].pmsg + '</div>');
          }
        } else {
          $('#salida').empty().append('<div class="alert alert-danger" role="alert">Se produjo un error en comunicacion JSON:101</div>');
        }
      }).error(function () {
        $('#salida').empty().append('<div class="alert alert-danger" role="alert">Se produjo un error en comunicacion JSON:102</div>');
      }).complete(function () {
        $('#cargando').hide();
        $("#solform :input").attr('readonly', false);
        $("#botones *").removeAttr("disabled");
      });
    }


  </script>

  <?php echo " </form></div></div></div></div>";
  exit;
} ?>

<script type="text/javascript">
  function selTipCred() {
    var optSel = document.getElementById("tipo_prima").value;
    if (optSel == "2") {
      $('#plazo option[value="30"]').remove();
      $('#plazo option[value="36"]').remove();
    } else {
      $('#plazo').append($('<option>', {
        value: 30
      }).text("30"));
      $('#plazo').append($('<option>', {
        value: 36
      }).text("36"));
    }
  }
</script>