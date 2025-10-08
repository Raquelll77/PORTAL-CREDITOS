<?php

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once('clases/MetaDatos.class.php');
require_once('include/protect.php');
require_once('include/framework.php');

$lista_plazos = DocumentosDatos::obtener_datos(DocumentosDatos::LISTA_PLAZOS);
$lista_perfiles = DocumentosDatos::obtener_datos(DocumentosDatos::LISTA_PERFILES);
$lista_tipos_productos = DocumentosDatos::obtener_datos(DocumentosDatos::TIPOS_PRODUCTOS);
$lista_tipos_identificacion = DocumentosDatos::obtener_datos(DocumentosDatos::TIPOS_IDENTIFICACION);

$lista_tipos_personas = DocumentosDatos::obtener_datos(DocumentosDatos::TIPOS_PERSONAS);
$lista_tipos_clientes = DocumentosDatos::obtener_datos(DocumentosDatos::TIPOS_CLIENTES);
$lista_departamentos = DocumentosDatos::obtener_datos(DocumentosDatos::DEPARTAMENTOS);
$lista_tipos_trabajos = DocumentosDatos::obtener_datos(DocumentosDatos::TIPOS_TRABAJO);
$lista_tipos_salarios = DocumentosDatos::obtener_datos(DocumentosDatos::TIPOS_SALARIOS);

$lista_tipos_escolaridad = DocumentosDatos::obtener_datos(DocumentosDatos::TIPOS_ESCOLARIDAD);
$lista_tipos_estados_civil = DocumentosDatos::obtener_datos(DocumentosDatos::TIPOS_ESTADO_CIVIL);
$lista_tipos_vivienda = DocumentosDatos::obtener_datos(DocumentosDatos::TIPOS_VIVIENDA);

$lista_SI_NO = [
	["value" => "SI", "label" => "SI"],
	["value" => "NO", "label" => "NO"],
];

class Modulos
{
	const MODULO_MOSTRAR_GESTION = "5b1";
	const MODULO_GUARDAR_CONTRATO_ADMIN = "5g9";
	const MODULO_GUARDAR_DATOS_GESTION = "5b1ss";
	const MODULO_HISTORIAL_GESTIONES = "6";

	const MODULO_CREDITOS_MAIN_MENU = "1";

	const MODULO_DATOS_SOLICITUD = "2";
	const MODULO_DATOS_CLIENTE = "3";
	const MODULO_DATOS_ADJUNTOS = "4";
	const MODULO_GESTIONES = "5";

	//const MODULO_ = "";
}


if (isset($_REQUEST[RequestParams::ACCION])) {
	/**
	 * @var string Indica una clave string del modulo a mostrar
	 */
	$accion = $_REQUEST[RequestParams::ACCION];
} else {
	exit;
}


$forma = time();

$verror = "";

if (!tiene_permiso(PermisosModulos::PERMISOS_MODULO_CREDITOS)) {
	echo mensaje("No tiene privilegios para accesar esta seccion", "danger");
	exit;
}


$conn = new mysqli(db_ip, db_user, db_pw, db_name);
if (mysqli_connect_errno()) {
	echo mensaje("Error al Conectar a la Base de Datos [DB:101]", "danger");
	exit;
}
$conn->set_charset("utf8");


if (isset($_REQUEST[RequestParams::CREDITO_ID])) {
	$solicitud_id = $conn->real_escape_string($_REQUEST[RequestParams::CREDITO_ID]);
} else {
	echo mensaje("Debe seleccionar una solicitud", "danger");
	exit;
}



if ($accion == Modulos::MODULO_CREDITOS_MAIN_MENU) { //TODO Principal

	$solicitud_num = get_dato_sql('prestamo', "concat('<span class=\"label label-primary\">',numero, '</span>  - TIENDA: ',bodega_nombre)", ' where id=' . $solicitud_id);
	echo "<h4>SOLICITUD No. $solicitud_num</h4><br>";

	?>
	<ul class="nav nav-tabs">
		<li role="presentation"><a href="#a1"
				onclick="actualizarbox('a1','creditos_gestion.php?a=2&cid=<?php echo $solicitud_id; ?>') ; return false;"
				data-toggle="tab">Datos Solicitud</a></li>
		<li role="presentation"><a href="#a2"
				onclick="actualizarbox('a1','creditos_gestion.php?a=3&b=1&cid=<?php echo $solicitud_id; ?>') ; return false;"
				data-toggle="tab">Datos del Cliente</a></li>
		<li role="presentation"><a href="#a3"
				onclick="actualizarbox('a1','creditos_gestion.php?a=4&cid=<?php echo $solicitud_id; ?>') ; return false;"
				data-toggle="tab">Documentos Adjuntos</a></li>
		<li role="presentation"><a href="#a4"
				onclick="actualizarbox('a1','creditos_gestion.php?a=3&b=2&cid=<?php echo $solicitud_id; ?>') ; return false;"
				data-toggle="tab">Datos del Aval</a></li>
		<li role="presentation"><a href="#a5"
				onclick="actualizarbox('a1','creditos_gestion.php?a=5&cid=<?php echo $solicitud_id; ?>') ; return false;"
				data-toggle="tab">Gestiones</a></li>
		<li role="presentation"><a href="#a7"
				onclick="actualizarbox('a1','creditos_gestion.php?a=6&cid=<?php echo $solicitud_id; ?>') ; return false;"
				data-toggle="tab">Historial Gestiones</a></li>
	</ul>
	<br>
	<div class="tab-content" id="tabs">
		<div class="tab-pane" id="a1"></div>

	</div>

	<script>
		function activaTab(tab, url) {
			actualizarbox(tab, url);
			$('.nav-tabs a[href="#' + tab + '"]').tab('show');

		};

		<?php $urladd = "";
		if (isset($_REQUEST['b'])) { ?>
			$urladd = "&b=<?php echo real_escape_string($_REQUEST['b']) ?>";
		<?php } ?>
		activaTab('a1', 'creditos_gestion.php?a=2&cid=<?php echo $solicitud_id . $urladd; ?>');
	</script>
	<br>
	<p><br><br><a href="#" onclick="actualizarbox('pagina','creditos.php') ; return false;"
			class="btn btn-default">REGRESAR</a></p>
	<?php

	exit;
}


if ($accion == Modulos::MODULO_DATOS_SOLICITUD) //TODO Datos Generales
{
	if (isset($_REQUEST['s2'])) { // guardar remitir a creditos
		//########## validar datos
		$verror = "";

		if ($verror == "") {



			$sql = "update prestamo set fecha_enviar_creditos=now() where id=$solicitud_id and fecha_enviar_creditos is null";

			if ($conn->query($sql) === TRUE) {
				$sqlcampos = "";
				$sqlcampos .= "  prestamo_id =$solicitud_id";
				$sqlcampos .= " , gestion_estado='Creditos' ";
				$sqlcampos .= " , campo_id=0 ";
				$sqlcampos .= " , etapa_id=1 ";
				$sqlcampos .= " , estatus_id=1 ";
				$sqlcampos .= " , descripcion='Nueva Solicitud' ";
				$sqlcampos .= ",usuario= '" . $_SESSION['usuario'] . "',fecha=curdate() ,hora=now()";
				$sqlcampos .= ",usuario_responde= '" . $_SESSION['usuario'] . "' ,hora_responde=now()";

				if (tiene_permiso(PermisosModulos::PERMISOS_CANAL_INDIRECTO)) {
					$sqlcampos .= " , canal='CI'";
				}

				$sqlcampos .= " , bodega=(select prestamo.bodega from prestamo where prestamo.id=$solicitud_id limit 1) ";
				if (tiene_permiso(PermisosModulos::PERMISOS_CANAL_INDIRECTO)) {
					$sqlcampos .= " , canal='CI'";
				}
				$sql = "insert into prestamo_gestion set " . $sqlcampos;
				$conn->query($sql);
				$gestion_id_new = mysqli_insert_id($conn);
				enviar_notificacion_gestion($gestion_id_new, '', '', 'Nueva Solicitud');

				echo '<div class="alert alert-success" role="alert">La solicitud fue remitida a creditos</div>';
			} else {
				echo '<div class="alert alert-danger" role="alert">Se produjo un error al guardar el registro DB101:<br>' . $conn->error . '</div>';
			}
		} else {
			//mostrar errores validacion
			echo '<div class="alert alert-warning" role="alert">Error en los datos:</strong><br>' . $verror . '</div>';
		}


		exit;
	}



	if (isset($_REQUEST[RequestParams::GUARDAR])) { // guardar
		//########## validar datos
		$verror = "";

		$verror .= validar("Valor Motocicleta", $_REQUEST['monto_prestamo'], "text", true, null, 3, null);
		//       $verror.=validar("Prima",$_REQUEST['monto_prima'], "text", true,  null,  1,  null);
		$verror .= validar("Total Financiar", $_REQUEST['monto_financiar'], "text", true, null, 3, null);
		$verror .= validar("Plazo", $_REQUEST['plazo'], "text", true, null, 1, null);
		$verror .= validar("Tasa", $_REQUEST['tasa'], "text", true, null, 1, null);


		if ($verror == "") {

			$sqlcampos = "";

			$sqlcampos .= "  monto_prestamo =" . GetSQLValue($conn->real_escape_string($_REQUEST["monto_prestamo"]), "text");
			$sqlcampos .= "  ,monto_seguro =" . GetSQLValue($conn->real_escape_string($_REQUEST["monto_seguro"]), "text");
			$sqlcampos .= " , monto_prima =" . GetSQLValue($conn->real_escape_string($_REQUEST["monto_prima"]), "text");
			$sqlcampos .= " , gastos_administrativos =" . GetSQLValue($conn->real_escape_string($_REQUEST["gastos_administrativos"]), "text");
			$sqlcampos .= " , costo_rtn = " . GetSQLValue($conn->real_escape_string($_REQUEST["costo_rtn"]), "text");
			$sqlcampos .= " , aplica_promocion_octubre = " . GetSQLValue($conn->real_escape_string($_REQUEST["aplica_promocion_octubre"]), "int");
			$sqlcampos .= " , moto_marca =" . GetSQLValue($conn->real_escape_string($_REQUEST["moto_marca"]), "text");
			$sqlcampos .= " , moto_modelo =" . GetSQLValue($conn->real_escape_string($_REQUEST["moto_modelo"]), "text");
			$sqlcampos .= " , monto_financiar =" . GetSQLValue($conn->real_escape_string($_REQUEST["monto_financiar"]), "text");
			$sqlcampos .= " , plazo =" . GetSQLValue($conn->real_escape_string($_REQUEST["plazo"]), "text");
			$sqlcampos .= " , tasa =" . GetSQLValue($conn->real_escape_string($_REQUEST["tasa"]), "text");
			$sqlcampos .= " , estatus=" . GetSQLValue($conn->real_escape_string($_REQUEST["estatus"]), "int");
			$sqlcampos .= " , etapa_proceso =" . GetSQLValue($conn->real_escape_string($_REQUEST["etapa_proceso"]), "int");
			$sqlcampos .= " , producto_servicio =" . GetSQLValue($conn->real_escape_string($_REQUEST["producto_servicio"]), "text");
			$sqlcampos .= " , moto_categoria =" . GetSQLValue($conn->real_escape_string($_REQUEST["moto_categoria"]), "text");
			$sqlcampos .= " , rtn =" . GetSQLValue($conn->real_escape_string($_REQUEST["rtn"]), "text");
			$sqlcampos .= " , codigo_cliente =" . GetSQLValue($conn->real_escape_string($_REQUEST["codigo_cliente"]), "text");
			$sqlcampos .= " , clave_enee =" . GetSQLValue($conn->real_escape_string($_REQUEST["clave_enee"]), "text");
			$sqlcampos .= " , otro_cargo_servicio_especificar =" . GetSQLValue($conn->real_escape_string($_REQUEST["otro_cargo_servicio_especificar"]), "text");
			$sqlcampos .= " , compra_productos =" . GetSQLValue($conn->real_escape_string($_REQUEST["compra_productos"]), "text");
			$sqlcampos .= " , uso_unidad =" . GetSQLValue($conn->real_escape_string($_REQUEST["uso_unidad"]), "text");
			$sqlcampos .= " , cantidad_vehiculos =" . GetSQLValue($conn->real_escape_string($_REQUEST["cantidad_vehiculos"]), "int");


			$sql = "update prestamo set $sqlcampos where id=$solicitud_id";

			if ($conn->query($sql) === TRUE) {
				echo '<div class="alert alert-success" role="alert">Los datos fueron guardados</div>';
			} else {
				echo '<div class="alert alert-danger" role="alert">Se produjo un error al guardar el registro DB101:<br>' . $conn->error . '</div>';
			}
		} else {
			//mostrar errores validacion
			echo '<div class="alert alert-warning" role="alert">Error en los datos:</strong><br>' . $verror . '</div>';
		}



		exit;
	}

	//******* SQL ************************************************************************************

	$sql =
		"SELECT 
			prestamo.id,prestamo.numero,prestamo.bodega,prestamo.bodega_nombre
			,fecha_alta, usuario_alta, nombres, apellidos, identidad, monto_prestamo
			,monto_financiar, monto_prima, plazo, tasa, estatus, etapa_proceso
			,prestamo_estatus.nombre as vestatus
			,prestamo_etapa.nombre as vprestamo_etapa
			,prestamo.fecha_enviar_creditos  ,prestamo.fecha_recibe_creditos
			,monto_seguro
			,usuario.nombre as nombreusuario
			,prestamo.tipoprima as tipoprima
			,CASE
				WHEN prestamo.tipoprima = 1 THEN 'Prima Normal'
				WHEN prestamo.tipoprima = 2 THEN 'Prima Alta (40%)'
				WHEN prestamo.tipoprima = 3 THEN 'Prima Cero'
				WHEN prestamo.tipoprima = 4 THEN 'Convenio Empresa'
				ELSE '(Prima No Definida)' 
			END AS tipoprimatext
			,codigo_cliente
			,producto_servicio
			,moto_categoria
			,cantidad_vehiculos
			,compra_productos
			,tipo_identificacion
			,rtn
			,clave_enee
			,otro_cargo_servicio_especificar
			,uso_unidad
			,gastos_administrativos
			,costo_rtn
			,aplica_promocion_octubre
			,moto_modelo
			,moto_marca
		FROM prestamo
		LEFT OUTER JOIN prestamo_estatus ON (prestamo_estatus.id=prestamo.estatus)
		LEFT OUTER JOIN prestamo_etapa ON (prestamo_etapa.id=prestamo.etapa_proceso) 
		LEFT OUTER JOIN usuario ON (prestamo.usuario_alta=usuario.usuario)
		WHERE prestamo.id= {$solicitud_id}";



	// echo $sql;exit;

	// ****** Fin SQL ********************************************************************************

	$result = $conn->query($sql);




	if ($result->num_rows > 0) {

		$row = $result->fetch_assoc();

		if (es_nulo($row["fecha_enviar_creditos"])) {
			echo ' <div class="panel panel-default"> <div id="remitircreditos" class="panel-body"> <div class="row"><div class="col-xs-12"> <form id="forma' . $forma . '" class="form-horizontal" autocomplete="off">';

			echo 'Cuando termine de ingresar la solicitud, precione el boton para remitir la solicitud al departamento de Creditos.';
			echo ' &nbsp;&nbsp;<a id="Guardar' . $forma . '" href="#" class="btn btn-primary" onclick="procesar_datos_remitirgestion(\'creditos_gestion.php?a=2&s2=1&cid=' . $solicitud_id . '\',' . $forma . '); return false;"><span class="glyphicon glyphicon-transfer" aria-hidden="true"></span> Remitir a Creditos</a>';

			echo '<div id="respuesta' . $forma . '"> </div>';

			echo " </form></div></div></div></div>";

			$forma = $forma + 10;
		}

		echo ' <div class="panel panel-default"> <div id="datosgenerales" class="panel-body"> <div class="row"><div class="col-xs-12"> <form id="forma' . $forma . '" class="form-horizontal" autocomplete="off">';


		echo campo("numero", "No. Solicitud", 'label', $row["numero"], 'class="form-control" ', '', '', 3, 3);

		echo campo("tipo_prima", "Tipo de Prima", 'text', $row["tipoprimatext"], 'class="form-control"  readonly', '', '', 3, 3);

		echo campo("fecha_alta", "Fecha", 'label', fechademysql($row["fecha_alta"]), 'class="form-control" ', '', '', 3, 3);
		echo campo("usuario_alta", "Vendedor", 'label', $row["nombreusuario"], 'class="form-control" ', '', '', 3, 4); //$row["usuario_alta"]
		echo campo("nombres", "Nombres", 'label', $row["nombres"], 'class="form-control" ', '', '', 3, 7);
		echo campo("apellidos", "Apellidos", 'label', $row["apellidos"], 'class="form-control" ', '', '', 3, 7);
		echo campo("identidad", "Identidad", 'label', $row["identidad"], 'class="form-control" ', '', '', 3, 3);

		$default_value = $row["tipo_identificacion"] ? $row["tipo_identificacion"] : "DNI";
		$option_values = convertir_array_dropdown($lista_tipos_identificacion, $default_value);
		echo campo("tipo_identificacion", "Tipo de Identificacion", 'select', $option_values, 'class="form-control" "', '', '', 3, 5);
		echo campo("rtn", "RTN", 'text', $row["rtn"] ? $row["rtn"] : "", 'class="form-control" ', '', '', 3, 3);

		// CODIGO DEL CLIENTE
		echo campo("codigo_cliente", "Codigo de Cliente", 'text', $row["codigo_cliente"] ? $row["codigo_cliente"] : "", 'class="form-control" ', '', '', 3, 3);
		echo campo("clave_enee", "Clave Primaria Empresa de Energía Eléctrica", 'text', $row["clave_enee"] ? $row["clave_enee"] : "", 'class="form-control" ', '', '', 3, 3);

		$default_value = $row["producto_servicio"] ? $row["producto_servicio"] : "Vehiculo Nuevo";
		$option_values = convertir_array_dropdown($lista_tipos_productos, $default_value);
		echo campo("producto_servicio", "Productos y/o servicios solicitados", 'select', $option_values, 'class="form-control" "', '', '', 3, 5);

		echo campo("otro_cargo_servicio_especificar", "En caso de ser otro servicio solicitado, especificar", 'text', $row["otro_cargo_servicio_especificar"] ? $row["otro_cargo_servicio_especificar"] : "", 'class="form-control" ', '', '', 3, 5);

		echo campo("compra_productos", "Compra de Productos Automotrices o Servicio Taller (Especificar): ", 'text', $row["compra_productos"] ? $row["compra_productos"] : "", 'class="form-control" ', '', '', 3, 5);

		echo campo("uso_unidad", "Uso de la unidad", 'select', valores_combobox_texto('<option value="Personal">Personal</option><option value="Comercial">Comercial</option>', $row["uso_unidad"] ? $row["uso_unidad"] : "Personal"), 'class="form-control" "', '', '', 3, 5);

		echo campo("moto_categoria", "Categoria", 'select', valores_combobox_texto('<option id="" value="motocicleta">Motocicleta</option><option id="" value="motocargo">Motocargo</option><option id="" value="mototaxi">Mototaxi</option><option id="" value="cuatrimoto">Cuatrimoto</option><option id="" value="vehiculo
		">Vehiculo</option>', $row["moto_categoria"] ? $row["moto_categoria"] : "Motocicleta"), 'class=" form-control"', '', '', 3, 5);

		echo campo("cantidad_vehiculos", "Cantidad de Vehiculos", 'number', $row["cantidad_vehiculos"], 'class="form-control"', '', '', 3, 3);

		echo campo("moto_marca", "Marca de la unidad", 'text', $row["moto_marca"], 'class="form-control"', '', '', 3, 3);

		echo campo("moto_modelo", "Modelo de la unidad", 'text', $row["moto_modelo"], 'class="form-control"', '', '', 3, 3);

		echo campo("monto_prestamo", "Valor Motocicleta", 'text', $row["monto_prestamo"], 'class="form-control" onchange="$(\'#monto_financiar\').val(convertir_num($(\'#monto_prestamo\').val())+convertir_num($(\'#monto_seguro\').val())+convertir_num($(\'#gastos_administrativos\').val())+convertir_num($(\'#costo_rtn\').val()-convertir_num($(\'#monto_prima\').val())); " ', '', '', 3, 3);

		echo campo("monto_seguro", "Valor del Seguro", 'text', $row["monto_seguro"], 'class="form-control"  onchange="$(\'#monto_financiar\').val(convertir_num($(\'#monto_prestamo\').val())+convertir_num($(\'#monto_seguro\').val())+convertir_num($(\'#gastos_administrativos\').val())+convertir_num($(\'#costo_rtn\').val()-convertir_num($(\'#monto_prima\').val())); " ', '', '', 3, 3);

		echo campo("monto_prima", "Prima", 'text', $row["monto_prima"], 'class="form-control"  onchange="$(\'#monto_financiar\').val(convertir_num($(\'#monto_prestamo\').val())+convertir_num($(\'#monto_seguro\').val())+convertir_num($(\'#gastos_administrativos\').val())+convertir_num($(\'#costo_rtn\').val())-convertir_num($(\'#monto_prima\').val())); "', '', '', 3, 3);

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


		echo campo("costo_rtn", "Costo RTN", 'text', $row["costo_rtn"], 'class="form-control" onchange="$(\'#monto_financiar\').val(
			convertir_num($(\'#monto_prestamo\').val()) +
			convertir_num($(\'#monto_seguro\').val()) +
			convertir_num($(\'#gastos_administrativos\').val()) +
			convertir_num($(\'#costo_rtn\').val()) -
			convertir_num($(\'#monto_prima\').val())
		);"', '', '', 3, 3);


		echo campo("monto_financiar", "Total Financiar", 'text', $row["monto_financiar"], 'class="form-control"  readonly', '', '', 3, 3);

		$option_values = convertir_array_dropdown($lista_plazos, $row["plazo"], true, "value", "value");
		echo campo("plazo", "Plazo", 'select', $option_values, 'class="form-control" ', '', '', 3, 2);
		echo campo("tasa", "Tasa", 'text', $row["tasa"], 'class="form-control"  ', '', '', 3, 2);


		echo campo("estatus", "Estatus", 'select', valores_combobox_db('prestamo_estatus', $row["estatus"], 'nombre', '', 'nombre'), 'class="form-control" ', '', '', 3, 4);

		echo campo(
			"aplica_promocion_octubre",
			"¿Aplica Promoción Octubre?",
			'select',
			valores_combobox_texto(
				'<option value="0">No</option><option value="1">Sí</option>',
				$row["aplica_promocion_octubre"]
			),
			'class="form-control"',
			'',
			'',
			3,
			3
		);

		echo campo("etapa_proceso", "Etapa Proceso", 'select', valores_combobox_db('prestamo_etapa', $row["etapa_proceso"], 'nombre', '', 'nombre'), 'class="form-control" ', '', '', 3, 8);


		$usuario_es_cd = strpos($_SESSION['usuario'], 'Cd') !== false;
		$ya_enviado_creditos = !es_nulo($row["fecha_enviar_creditos"]);

		if (tiene_permiso(PermisosModulos::PERMISOS_PERSONAL_CREDITO) && !($usuario_es_cd && $ya_enviado_creditos)) {
			echo '<br><a id="Guardar' . $forma . '" href="#" class="btn btn-primary" onclick="procesar_datos(\'creditos_gestion.php?a=2&s=1&cid=' . $solicitud_id . '\',' . $forma . '); return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar</a>';
		}


		echo '<div id="respuesta' . $forma . '"> </div>';

		echo " </form></div></div></div></div>";
	} else {
		echo mensaje("No se encontraron registros", "info");
		exit;
	}


	exit;
}


if ($accion == Modulos::MODULO_DATOS_CLIENTE) //TODO Datos Cliente o aval
{

	$tabla = "prestamo";
	$b = 1;

	if (isset($_REQUEST['b']) && $_REQUEST['b'] == '2') {
		$tabla = "prestamo_aval";
		$b = 2;
	}


	if (isset($_REQUEST[RequestParams::GUARDAR])) { // guardar
		//########## validar datos
		$verror = "";

		$verror .= validar("Nombres", $_REQUEST['nombres'], "text", true, null, 3, null);
		$verror .= validar("Apellidos", $_REQUEST['apellidos'], "text", true, null, 3, null);
		$verror .= validar("Identidad", $_REQUEST['identidad'], "text", true, null, 13, null);


		if ($verror == "") {

			$sqlcampos = "";

			$sqlcampos .= "  identidad =" . GetSQLValue($conn->real_escape_string($_REQUEST["identidad"]), "text");
			$sqlcampos .= " , apellidos =" . GetSQLValue($conn->real_escape_string($_REQUEST["apellidos"]), "text");
			$sqlcampos .= " , nombres =" . GetSQLValue($conn->real_escape_string($_REQUEST["nombres"]), "text");

			$sqlcampos .= " , nombre_empresa_rtn =" . GetSQLValue($conn->real_escape_string($_REQUEST["nombre_empresa_rtn"]), "text");
			$sqlcampos .= " , nombre_empresa =" . GetSQLValue($conn->real_escape_string($_REQUEST["nombre_empresa"]), "text");
			$sqlcampos .= " , tipo_persona =" . GetSQLValue($conn->real_escape_string($_REQUEST["tipo_persona"]), "text");

			if ($_REQUEST["fecha_nacimiento"] <> "")
				$sqlcampos .= " , fecha_nacimiento =" . GetSQLValue(mysqldate($conn->real_escape_string($_REQUEST["fecha_nacimiento"])), "text");
			if ($_REQUEST["empresa_fecha_ingreso"] <> "")
				$sqlcampos .= " , empresa_fecha_ingreso =" . GetSQLValue(mysqldate($conn->real_escape_string($_REQUEST["empresa_fecha_ingreso"])), "text");
			$sqlcampos .= " , empresa_salario =" . GetSQLValue($conn->real_escape_string($_REQUEST["empresa_salario"]), "double");

			$sqlcampos .= " , empresa_salario_otro =" . GetSQLValue($conn->real_escape_string($_REQUEST["empresa_salario_otro"]), "double");


			$sqlcampos .= " , no_dependientes =" . GetSQLValue($conn->real_escape_string($_REQUEST["no_dependientes"]), "int");


			//   $sqlcampos.= " , comentario =".GetSQLValue($conn->real_escape_string($_REQUEST["comentario"]),"text");
			$sqlcampos .= " , empresa_telefono =" . GetSQLValue($conn->real_escape_string($_REQUEST["empresa_telefono"]), "text");
			$sqlcampos .= " , vecino_telefono2 =" . GetSQLValue($conn->real_escape_string($_REQUEST["vecino_telefono2"]), "text");
			//   $sqlcampos.= " , requiere_aval =".GetSQLValue($conn->real_escape_string($_REQUEST["requiere_aval"]),"text");
			$sqlcampos .= " , tipo_vivienda =" . GetSQLValue($conn->real_escape_string($_REQUEST["tipo_vivienda"]), "text");
			$sqlcampos .= " , antiguedad_vivienda =" . GetSQLValue($conn->real_escape_string($_REQUEST["antiguedad_vivienda"]), "text");
			//    $sqlcampos.= " , requiere_verificacion_campo_laboral =".GetSQLValue($conn->real_escape_string($_REQUEST["requiere_verificacion_campo_laboral"]),"text");
			$sqlcampos .= " , empresa =" . GetSQLValue($conn->real_escape_string($_REQUEST["empresa"]), "text");
			$sqlcampos .= " , empresa_puesto =" . GetSQLValue($conn->real_escape_string($_REQUEST["empresa_puesto"]), "text");
			$sqlcampos .= " , empresa_direccion =" . GetSQLValue($conn->real_escape_string($_REQUEST["empresa_direccion"]), "text");
			$sqlcampos .= " , empresa_salario_otro_tipo =" . GetSQLValue($conn->real_escape_string($_REQUEST["empresa_salario_otro_tipo"]), "text");
			$sqlcampos .= " , empresa_tipo_empleo =" . GetSQLValue($conn->real_escape_string($_REQUEST["empresa_tipo_empleo"]), "text");
			$sqlcampos .= " , empresa_tipo_condicion =" . GetSQLValue($conn->real_escape_string($_REQUEST["empresa_tipo_condicion"]), "text");
			$sqlcampos .= " , empresa_telefono2 =" . GetSQLValue($conn->real_escape_string($_REQUEST["empresa_telefono2"]), "text");
			$sqlcampos .= " , empresa_extension =" . GetSQLValue($conn->real_escape_string($_REQUEST["empresa_extension"]), "text");
			$sqlcampos .= " , ref1_telefono_celular =" . GetSQLValue($conn->real_escape_string($_REQUEST["ref1_telefono_celular"]), "text");
			$sqlcampos .= " , ref3_telefono_celular =" . GetSQLValue($conn->real_escape_string($_REQUEST["ref3_telefono_celular"]), "text");
			$sqlcampos .= " , ref4_nombre =" . GetSQLValue($conn->real_escape_string($_REQUEST["ref4_nombre"]), "text");
			$sqlcampos .= " , ref4_telefono_casa =" . GetSQLValue($conn->real_escape_string($_REQUEST["ref4_telefono_casa"]), "text");
			$sqlcampos .= " , ref4_telefono_trabajo =" . GetSQLValue($conn->real_escape_string($_REQUEST["ref4_telefono_trabajo"]), "text");
			$sqlcampos .= " , ref4_telefono_celular =" . GetSQLValue($conn->real_escape_string($_REQUEST["ref4_telefono_celular"]), "text");
			$sqlcampos .= " , ref1_relacion =" . GetSQLValue($conn->real_escape_string($_REQUEST["ref1_relacion"]), "text");
			$sqlcampos .= " , ref2_relacion =" . GetSQLValue($conn->real_escape_string($_REQUEST["ref2_relacion"]), "text");
			$sqlcampos .= " , ref3_relacion =" . GetSQLValue($conn->real_escape_string($_REQUEST["ref3_relacion"]), "text");
			$sqlcampos .= " , ref3_telefono_trabajo =" . GetSQLValue($conn->real_escape_string($_REQUEST["ref3_telefono_trabajo"]), "text");
			$sqlcampos .= " , ref3_telefono_casa =" . GetSQLValue($conn->real_escape_string($_REQUEST["ref3_telefono_casa"]), "text");
			$sqlcampos .= " , ref1_nombre =" . GetSQLValue($conn->real_escape_string($_REQUEST["ref1_nombre"]), "text");
			$sqlcampos .= " , ref1_telefono_casa =" . GetSQLValue($conn->real_escape_string($_REQUEST["ref1_telefono_casa"]), "text");
			$sqlcampos .= " , ref1_telefono_trabajo =" . GetSQLValue($conn->real_escape_string($_REQUEST["ref1_telefono_trabajo"]), "text");
			$sqlcampos .= " , ref2_nombre =" . GetSQLValue($conn->real_escape_string($_REQUEST["ref2_nombre"]), "text");
			$sqlcampos .= " , ref2_telefono_casa =" . GetSQLValue($conn->real_escape_string($_REQUEST["ref2_telefono_casa"]), "text");
			$sqlcampos .= " , ref2_telefono_trabajo =" . GetSQLValue($conn->real_escape_string($_REQUEST["ref2_telefono_trabajo"]), "text");
			$sqlcampos .= " , ref2_telefono_celular =" . GetSQLValue($conn->real_escape_string($_REQUEST["ref2_telefono_celular"]), "text");
			$sqlcampos .= " , ref3_nombre =" . GetSQLValue($conn->real_escape_string($_REQUEST["ref3_nombre"]), "text");
			$sqlcampos .= " , ref4_relacion =" . GetSQLValue($conn->real_escape_string($_REQUEST["ref4_relacion"]), "text");
			$sqlcampos .= " , vecino_telefono =" . GetSQLValue($conn->real_escape_string($_REQUEST["vecino_telefono"]), "text");
			$sqlcampos .= " , vecino =" . GetSQLValue($conn->real_escape_string($_REQUEST["vecino"]), "text");
			$sqlcampos .= " , email =" . GetSQLValue($conn->real_escape_string($_REQUEST["email"]), "text");
			$sqlcampos .= " , sexo =" . GetSQLValue($conn->real_escape_string($_REQUEST["sexo"]), "text");
			$sqlcampos .= " , celular =" . GetSQLValue($conn->real_escape_string($_REQUEST["celular"]), "text");
			$sqlcampos .= " , telefono3 =" . GetSQLValue($conn->real_escape_string($_REQUEST["telefono3"]), "text");
			$sqlcampos .= " , telefono2 =" . GetSQLValue($conn->real_escape_string($_REQUEST["telefono2"]), "text");
			$sqlcampos .= " , telefono =" . GetSQLValue($conn->real_escape_string($_REQUEST["telefono"]), "text");
			$sqlcampos .= " , departamento =" . GetSQLValue($conn->real_escape_string($_REQUEST["departamento"]), "text");
			$sqlcampos .= " , ciudad =" . GetSQLValue($conn->real_escape_string($_REQUEST["ciudad"]), "text");
			//  $sqlcampos.= " , direccion_gps =".GetSQLValue($conn->real_escape_string($_REQUEST["direccion_gps"]),"text");
			$sqlcampos .= " , direccion_referencia =" . GetSQLValue($conn->real_escape_string($_REQUEST["direccion_referencia"]), "text");
			$sqlcampos .= " , direccion =" . GetSQLValue($conn->real_escape_string($_REQUEST["direccion"]), "text");

			//    $sqlcampos.= " , bodega_nombre =".GetSQLValue($conn->real_escape_string($_REQUEST["bodega_nombre"]),"text");
			//    $sqlcampos.= " , bodega =".GetSQLValue($conn->real_escape_string($_REQUEST["bodega"]),"text");

			$sqlcampos .= " , escolaridad =" . GetSQLValue($conn->real_escape_string($_REQUEST["escolaridad"]), "text");
			$sqlcampos .= " , profesion =" . GetSQLValue($conn->real_escape_string($_REQUEST["profesion"]), "text");
			$sqlcampos .= " , nombre_conyuge =" . GetSQLValue($conn->real_escape_string($_REQUEST["nombre_conyuge"]), "text");
			$sqlcampos .= " , estado_civil =" . GetSQLValue($conn->real_escape_string($_REQUEST["estado_civil"]), "text");
			// Nuevos campos a actualizar
			$sqlcampos .= " , tipo_de_cliente =" . GetSQLValue($conn->real_escape_string($_REQUEST["tipo_de_cliente"]), "text");
			$sqlcampos .= " , lugar_nacimiento =" . GetSQLValue($conn->real_escape_string($_REQUEST["lugar_nacimiento"]), "text");
			$sqlcampos .= " , pais =" . GetSQLValue($conn->real_escape_string($_REQUEST["pais"]), "text");
			$sqlcampos .= " , municipio =" . GetSQLValue($conn->real_escape_string($_REQUEST["municipio"]), "text");
			$sqlcampos .= " , ciudad =" . GetSQLValue($conn->real_escape_string($_REQUEST["ciudad"]), "text");
			$sqlcampos .= " , avenida =" . GetSQLValue($conn->real_escape_string($_REQUEST["avenida"]), "text");
			$sqlcampos .= " , calle =" . GetSQLValue($conn->real_escape_string($_REQUEST["calle"]), "text");
			$sqlcampos .= " , sector_edificio =" . GetSQLValue($conn->real_escape_string($_REQUEST["sector_edificio"]), "text");
			$sqlcampos .= " , bloque_piso =" . GetSQLValue($conn->real_escape_string($_REQUEST["bloque_piso"]), "text");
			$sqlcampos .= " , casa_apartamento =" . GetSQLValue($conn->real_escape_string($_REQUEST["casa_apartamento"]), "text");
			$sqlcampos .= " , telefono_conyuge =" . GetSQLValue($conn->real_escape_string($_REQUEST["telefono_conyuge"]), "int");
			$sqlcampos .= " , empresa_propia =" . GetSQLValue($conn->real_escape_string($_REQUEST["empresa_propia"]), "text");

			$sqlcampos .= " , empresa_municipio =" . GetSQLValue($conn->real_escape_string($_REQUEST["empresa_municipio"]), "text");
			$sqlcampos .= " , empresa_departamento =" . GetSQLValue($conn->real_escape_string($_REQUEST["empresa_departamento"]), "text");
			$sqlcampos .= " , empresa_avenida =" . GetSQLValue($conn->real_escape_string($_REQUEST["empresa_avenida"]), "text");
			$sqlcampos .= " , empresa_calle =" . GetSQLValue($conn->real_escape_string($_REQUEST["empresa_calle"]), "text");
			$sqlcampos .= " , empresa_sector_edificio =" . GetSQLValue($conn->real_escape_string($_REQUEST["empresa_sector_edificio"]), "text");
			$sqlcampos .= " , empresa_bloque_piso =" . GetSQLValue($conn->real_escape_string($_REQUEST["empresa_bloque_piso"]), "text");
			$sqlcampos .= " , empresa_casa_apartamento =" . GetSQLValue($conn->real_escape_string($_REQUEST["empresa_casa_apartamento"]), "text");
			$sqlcampos .= " , empresa_casa_matriz =" . GetSQLValue($conn->real_escape_string($_REQUEST["empresa_casa_matriz"]), "text");
			$sqlcampos .= " , empleado_actividad_economica =" . GetSQLValue($conn->real_escape_string($_REQUEST["empleado_actividad_economica"]), "text");
			$sqlcampos .= " , empleado_rango_mensual =" . GetSQLValue($conn->real_escape_string($_REQUEST["empleado_rango_mensual"]), "text");
			$sqlcampos .= " , empresa_puesto =" . GetSQLValue($conn->real_escape_string($_REQUEST["empresa_puesto"]), "text");
			$sqlcampos .= " , empleado_antiguedad =" . GetSQLValue($conn->real_escape_string($_REQUEST["empleado_antiguedad"]), "text");
			$sqlcampos .= " , empresa_salario_tipo =" . GetSQLValue($conn->real_escape_string($_REQUEST["empresa_salario_tipo"]), "text");
			$sqlcampos .= " , empresa_salario_otro =" . GetSQLValue($conn->real_escape_string($_REQUEST["empresa_salario_otro"]), "text");
			$sqlcampos .= " , empresa_salario_otro_tipo =" . GetSQLValue($conn->real_escape_string($_REQUEST["empresa_salario_otro_tipo"]), "text");
			$sqlcampos .= " , prestamo_externo_especifique =" . GetSQLValue($conn->real_escape_string($_REQUEST["prestamo_externo_especifique"]), "text");
			$sqlcampos .= " , origen_fondo_tercero =" . GetSQLValue($conn->real_escape_string($_REQUEST["origen_fondo_tercero"]), "text");
			$sqlcampos .= " , monto_tercero =" . GetSQLValue($conn->real_escape_string($_REQUEST["monto_tercero"]), "double");
			$sqlcampos .= " , vinculo_tercero =" . GetSQLValue($conn->real_escape_string($_REQUEST["vinculo_tercero"]), "text");
			$sqlcampos .= " , nombre_tercero =" . GetSQLValue($conn->real_escape_string($_REQUEST["nombre_tercero"]), "text");
			$sqlcampos .= " , identificacion_tercero =" . GetSQLValue($conn->real_escape_string($_REQUEST["identificacion_tercero"]), "text");
			$sqlcampos .= " , actividad_economica_tercero =" . GetSQLValue($conn->real_escape_string($_REQUEST["actividad_economica_tercero"]), "text");
			$sqlcampos .= " , ingreso_mensual_tercero =" . GetSQLValue($conn->real_escape_string($_REQUEST["ingreso_mensual_tercero"]), "text");
			$sqlcampos .= " , telefono_tercero =" . GetSQLValue($conn->real_escape_string($_REQUEST["telefono_tercero"]), "text");
			$sqlcampos .= " , prevencion_lavado =" . GetSQLValue($conn->real_escape_string($_REQUEST["prevencion_lavado"]), "text");
			$sqlcampos .= " , acreditacion_ente =" . GetSQLValue($conn->real_escape_string($_REQUEST["acreditacion_ente"]), "text");
			$sqlcampos .= " , cliente_nacionalidad =" . GetSQLValue($conn->real_escape_string($_REQUEST["cliente_nacionalidad"]), "text");

			$sql = "UPDATE $tabla SET $sqlcampos WHERE id = $solicitud_id";

			if ($conn->query($sql) === TRUE) {
				echo '<div class="alert alert-success" role="alert">Los datos fueron guardados</div>';
			} else {
				echo '<div class="alert alert-danger" role="alert">Se produjo un error al guardar el registro DB101:<br>' . $conn->error . '</div>';
			}
		} else {
			//mostrar errores validacion
			echo '<div class="alert alert-warning" role="alert">Error en los datos:</strong><br>' . $verror . '</div>';
		}



		exit;
	}

	$sql = "SELECT * FROM $tabla WHERE id = $solicitud_id";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {

		$forma = time();

		$row = $result->fetch_assoc();
		if ($b == 1) {
			if (es_nulo($row["fecha_enviar_creditos"])) {
				echo ' <div class="panel panel-default"> <div id="remitircreditos" class="panel-body"> <div class="row"><div class="col-xs-12"> <form id="forma' . $forma . '" class="form-horizontal" autocomplete="off">';

				echo 'Cuando termine de ingresar la solicitud, precione el boton para remitir la solicitud al departamento de Creditos.';
				echo ' &nbsp;&nbsp;<a id="Guardar' . $forma . '" href="#" class="btn btn-primary" onclick="procesar_datos_remitirgestion(\'creditos_gestion.php?a=2&s2=1&cid=' . $solicitud_id . '\',' . $forma . '); return false;"><span class="glyphicon glyphicon-transfer" aria-hidden="true"></span> Remitir a Creditos</a>';

				echo '<div id="respuesta' . $forma . '"> </div>';

				echo " </form></div></div></div></div>";

				$forma = $forma + 10;
			}
		}

		echo ' <div class="panel panel-default"> <div id="datosgenerales" class="panel-body"> <div class="row"> <div class="col-xs-12"> <form id="forma' . $forma . '" class="form-horizontal" autocomplete="off">';

		$options = convertir_array_dropdown($lista_tipos_personas, $row["tipo_persona"]);
		echo campo("tipo_persona", "Tipo", 'select', $options, 'class="form-control" onchange="$(\'#pjuridica\').toggle();"', '', '', 3, 5);

		$pjestilo = $row["tipo_persona"] == "Persona Juridica" ? "" : 'style="display: none"';
		echo '<div id="pjuridica" ' . $pjestilo . '>';

		echo campo("nombre_empresa", "Nombre Empresa", 'text', $row["nombre_empresa"], 'class="form-control" ', '', '', 3, 7);
		echo campo("nombre_empresa_rtn", "RTN Empresa", 'text', $row["nombre_empresa_rtn"], 'class="form-control" data-mask="99999999999999"', '', '', 3, 7);
		echo '<h4>Datos del representante legal:</h4>';
		echo '</div>';

		$options = convertir_array_dropdown($lista_tipos_clientes, $row["tipo_de_cliente"]);
		echo campo("tipo_de_cliente", "Tipo de Cliente", 'select', $options, 'class="form-control" ', '', '', 3, 5);

		echo campo("nombres", "Nombres", 'text', $row["nombres"], 'class="form-control" ', '', '', 3, 7);
		echo campo("apellidos", "Apellidos", 'text', $row["apellidos"], 'class="form-control" ', '', '', 3, 7);
		echo campo("identidad", "Identidad", 'text', $row["identidad"], 'class="form-control" data-mask="9999-9999-99999?99"', '', '', 3, 3);
		echo campo("lugar_nacimiento", "Lugar de Nacimiento", 'text', $row["lugar_nacimiento"], 'class="form-control" ', '', '', 3, 4);
		echo campo("direccion", "Direccion", 'text', $row["direccion"], 'class="form-control" ', '', '', 3, 7);
		echo campo("direccion_referencia", "Direccion Referencia", 'text', $row["direccion_referencia"], 'class="form-control" ', '', '', 3, 5);
		//  echo campo("direccion_gps","Direccion GPS",'text',$row["direccion_gps"],'class="form-control" ','','',3,3);
		echo campo("ciudad", "Ciudad", 'text', $row["ciudad"], 'class="form-control" ', '', '', 3, 3);
		echo campo("pais", "Pais", 'text', $row["pais"], 'class="form-control" ', '', '', 3, 3);

		$options = convertir_array_dropdown($lista_departamentos, $row["departamento"]);
		echo campo("departamento", "Departamento", 'select', $options, 'class="form-control" ', '', '', 3, 3);

		echo campo("municipio", "Municipio", 'text', $row["municipio"], 'class="form-control" ', '', '', 3, 3);
		echo campo("ciudad", "Ciudad", 'text', $row["ciudad"], 'class="form-control" ', '', '', 3, 3);
		echo campo("avenida", "Avenida", 'text', $row["avenida"], 'class="form-control" ', '', '', 3, 3);
		echo campo("calle", "Calle", 'text', $row["calle"], 'class="form-control" ', '', '', 3, 3);
		echo campo("sector_edificio", "Sector/Edificio", 'text', $row["sector_edificio"], 'class="form-control" ', '', '', 3, 3);
		echo campo("bloque_piso", "Bloque/Piso", 'text', $row["bloque_piso"], 'class="form-control" ', '', '', 3, 3);
		echo campo("casa_apartamento", "Casa/Apartamento", 'text', $row["casa_apartamento"], 'class="form-control" ', '', '', 3, 3);
		echo campo("cliente_nacionalidad", "Nacionalidad", 'text', $row["cliente_nacionalidad"] ? $row["cliente_nacionalidad"] : "", 'class="form-control" ', '', '', 3, 3);

		$default_value = $row["otra_nacionalidad"] ? $row["otra_nacionalidad"] : "";
		$options = convertir_array_dropdown($lista_SI_NO, $default_value);
		echo campo("otra_nacionalidad", "¿Tiene otra nacionalidad?", 'select', $options, 'class="form-control" onchange="seleccionNacionalidad();"', '', '', 3, 3);

		echo campo("nacionalidad_extra", "¿Cual nacionalidad?", 'text', $row["nacionalidad_extra"] ? $row["nacionalidad_extra"] : "", 'class="form-control" ', '', '', 3, 3, 'display: none;');
		echo campo("telefono", "Telefono", 'text', $row["telefono"], 'class="form-control" data-mask="9999-9999"', '', '', 3, 3);
		echo campo("telefono2", "Telefono 2", 'text', $row["telefono2"], 'class="form-control" data-mask="9999-9999"', '', '', 3, 3);
		echo campo("celular", "Celular", 'text', $row["celular"], 'class="form-control" data-mask="9999-9999"', '', '', 3, 3);
		echo campo("telefono3", "Celular 2", 'text', $row["telefono3"], 'class="form-control" data-mask="9999-9999"', '', '', 3, 3);


		echo campo("email", "Email", 'text', $row["email"], 'class="form-control" ', '', '', 3, 4);
		echo campo("sexo", "Sexo", 'select', valores_combobox_texto('<option value="">Seleccione</option><option value="MASCULINO">MASCULINO</option><option value="FEMENINO">FEMENINO</option>', $row["sexo"]), 'class="form-control" ', '', '', 3, 3);

		echo campo("profesion", "Profesion", 'text', $row["profesion"], 'class="form-control" ', '', '', 3, 5);
		echo campo("fecha_nacimiento", "Fecha Nacimiento", 'date', fechademysql($row["fecha_nacimiento"]), 'class="form-control" ', '', '', 3, 3);

		$options = convertir_array_dropdown($lista_tipos_escolaridad, $row["escolaridad"], true, "value", "value");
		echo campo("escolaridad", "Escolaridad", 'select', $options, 'class="form-control" ', '', '', 3, 4);

		$options = convertir_array_dropdown($lista_tipos_estados_civil, $row["estado_civil"], true, "value", "value");
		echo campo("estado_civil", "Estado Civil", 'select', $options, 'class="form-control" ', '', '', 3, 3);

		echo campo("nombre_conyuge", "Nombre Conyuge", 'text', $row["nombre_conyuge"], 'class="form-control" ', '', '', 3, 5);
		echo campo("telefono_conyuge", "Telefono Conyuge", 'text', $row["telefono_conyuge"], 'class="form-control" ', '', '', 3, 5);
		echo campo("no_dependientes", "No. Dependientes", 'text', $row["no_dependientes"], 'class="form-control" data-mask="9?9"', '', '', 3, 2);
		echo campo("vecino", "Pariente / Vecino", 'text', $row["vecino"], 'class="form-control" ', '', '', 3, 4);
		echo campo("vecino_telefono", "Vecino Telefono", 'text', $row["vecino_telefono"], 'class="form-control" data-mask="9999-9999"', '', '', 3, 4);
		echo campo("vecino_telefono2", "Vecino Telefono 2", 'text', $row["vecino_telefono2"], 'class="form-control" data-mask="9999-9999"', '', '', 3, 4);

		echo campo("tipo_vivienda", "Tipo Vivienda", 'select', valores_combobox_texto('<option value="">Seleccione</option><option value="ALQUILA">ALQUILA</option><option value="PROPIA">PROPIA</option><option value="HIPOTECADA">HIPOTECADA</option><option value="FAMILIAR">FAMILIAR</option><option value="CEDIDA POR EMPRESA">CEDIDA POR EMPRESA</option>', $row["tipo_vivienda"]), 'class="form-control" ', '', '', 3, 3);
		echo campo("antiguedad_vivienda", "Tiempo de residir", 'text', $row["antiguedad_vivienda"], 'class="form-control" ', '', '', 3, 2);

		echo "<hr>";
		echo "<br><h4>DATOS LABORALES</h4><br>";

		echo campo("empresa", "Empresa donde Labora", 'text', $row["empresa"], 'class="form-control" ', '', '', 3, 7);

		$options = convertir_array_dropdown($lista_SI_NO, $row["empresa_propia"]);
		echo campo("empresa_propia", "¿La empresa es propia?", 'select', $options, 'class="form-control" ', '', '', 3, 3);

		echo campo("empresa_direccion", "Direccion", 'text', $row["empresa_direccion"], 'class="form-control" ', '', '', 3, 9);

		$options = convertir_array_dropdown($lista_departamentos, $row["empresa_departamento"]);
		echo campo("empresa_departamento", "Departamento", 'select', $options, 'class="form-control" ', '', '', 3, 4);

		echo campo("empresa_municipio", "Municipio", 'text', $row["empresa_municipio"], 'class="form-control" ', '', '', 3, 4);
		echo campo("empresa_avenida", "Avenida", 'text', $row["empresa_avenida"], 'class="form-control" ', '', '', 3, 4);
		echo campo("empresa_calle", "Calle", 'text', $row["empresa_calle"], 'class="form-control" ', '', '', 3, 4);
		echo campo("empresa_sector_edificio", "Sector/Edificio", 'text', $row["empresa_sector_edificio"], 'class="form-control" ', '', '', 3, 4);
		echo campo("empresa_bloque_piso", "Bloque/Piso", 'text', $row["empresa_bloque_piso"], 'class="form-control" ', '', '', 3, 4);
		echo campo("empresa_casa_matriz", "Casa Matriz", 'text', $row["empresa_casa_matriz"], 'class="form-control" ', '', '', 3, 4);
		echo campo("empresa_casa_apartamento", "Casa/Apartamento", 'text', $row["empresa_casa_apartamento"], 'class="form-control" ', '', '', 3, 4);

		$options = convertir_array_dropdown($lista_tipos_trabajos, $row["empresa_tipo_empleo"]);
		echo campo("empresa_tipo_empleo", "Condicion Laboral", 'select', $options, 'class="form-control" ', '', '', 3, 3);

		echo campo("empresa_tipo_condicion", "Tipo de Condicion", 'select', valores_combobox_texto('<option value="">Seleccione</option><option value="FIJO">FIJO</option><option value="TEMPORAL">TEMPORAL</option><option value="INTERINO">INTERINO</option>', $row["empresa_tipo_condicion"]), 'class="form-control" ', '', '', 3, 3);
		echo campo("empleado_actividad_economica", "Actividad Economica", 'text', $row["empleado_actividad_economica"], 'class="form-control" ', '', '', 3, 4);
		echo campo("empleado_rango_mensual", "Rango de Ingreso Mensual", 'text', $row["empleado_rango_mensual"], 'class="form-control" ', '', '', 3, 4);
		echo campo("empresa_puesto", "Ocupacion o Cargo", 'text', $row["empresa_puesto"], 'class="form-control" ', '', '', 3, 4);
		echo campo("empresa_fecha_ingreso", "Fecha Ingreso", 'date', fechademysql($row["empresa_fecha_ingreso"]), 'class="form-control" ', '', '', 3, 3);
		echo campo("empleado_antiguedad", "Antiguedad en la empresa", 'text', $row["empleado_antiguedad"], 'class="form-control" ', '', '', 3, 4);
		echo campo("empresa_telefono", "Telefono", 'text', $row["empresa_telefono"], 'class="form-control" data-mask="9999-9999"', '', '', 3, 4);
		echo campo("empresa_telefono2", "Telefono 2", 'text', $row["empresa_telefono2"], 'class="form-control" data-mask="9999-9999"', '', '', 3, 4);
		echo campo("empresa_extension", "Extension", 'text', $row["empresa_extension"], 'class="form-control" ', '', '', 3, 1);
		echo campo("empresa_salario", "Salario", 'text', $row["empresa_salario"], 'class="form-control" ', '', '', 3, 4);

		$options = convertir_array_dropdown($lista_tipos_salarios, $row["empresa_salario_tipo"]);
		echo campo("empresa_salario_tipo", "Por Concepto", 'select', $options, 'class="form-control" onchange="seleccionSalario();"', '', '', 3, 3);

		echo campo("empresa_salario_otro", "Otros ingresos", 'text', $row["empresa_salario_otro"], 'class="form-control" ', '', '', 3, 4);
		echo campo("empresa_salario_otro_tipo", "Por concepto", 'text', $row["empresa_salario_otro_tipo"], 'class="form-control" ', '', '', 3, 4);
		echo campo("prestamo_externo_especifique", "Especifique el prestamo externo ", 'text', $row["prestamo_externo_especifique"], 'class="form-control"', '', '', 3, 4, 'display: none;');

		$options = convertir_array_dropdown($lista_SI_NO, $row["origen_fondo_tercero"]);
		echo campo("origen_fondo_tercero", "¿El origen de los fondos fue proporcionado por un tercero?", 'select', $options, 'class="form-control" onchange="origenTercero();"', '', '', 3, 3);

		echo campo("monto_tercero", "Monto", 'number', $row["monto_tercero"], 'class="form-control" " ', '', '', 3, 4, 'display: none;');
		echo campo("vinculo_tercero", "Vinculo con el tercero", 'text', $row["vinculo_tercero"], 'class="form-control" " ', '', '', 3, 4, 'display: none;');
		echo campo("nombre_tercero", "Nombre completo del tercero", 'text', $row["nombre_tercero"], 'class="form-control" " ', '', '', 3, 4, 'display: none;');
		echo campo("identificacion_tercero", "Numero de identificacion del tercero", 'text', $row["identificacion_tercero"], 'class="form-control" ', '', '', 3, 4, 'display: none;');
		echo campo("actividad_economica_tercero", "Actividad Economica del tercero", 'text', $row["actividad_economica_tercero"], 'class="form-control"', '', '', 3, 4, 'display: none;');
		echo campo("ingreso_mensual_tercero", "Ingreso mensual del tercero", 'text', $row["ingreso_mensual_tercero"], 'class="form-control"', '', '', 3, 4, 'display: none;');
		echo campo("telefono_tercero", "Numero de telefono del tercero", 'text', $row["telefono_tercero"], 'class="form-control" ', '', '', 3, 4, 'display: none;');

		$options = convertir_array_dropdown($lista_SI_NO, $row["prevencion_lavado"]);
		echo campo("prevencion_lavado", "¿Es sujeto obligado según lo establecido en la normativa vigente en prevención de Lavado de Activos?", 'select', $options, 'class="form-control" ', '', '', 3, 3);

		$options = convertir_array_dropdown($lista_SI_NO, $row["acreditacion_ente"]);
		echo campo("acreditacion_ente", "¿Posee acreditación correspondiente, otorgada por el Ente Regulador?", 'select', $options, 'class="form-control" ', '', '', 3, 3);

		echo "<hr>";
		echo "<br><h4>DATOS DE REFERENCIAS PERSONALES</h4><br>";

		echo campo("ref1_nombre", "Nombre", 'text', $row["ref1_nombre"], 'class="form-control" ', '', '', 3, 6);
		echo campo("ref1_telefono_casa", "Telefono Casa", 'text', $row["ref1_telefono_casa"], 'class="form-control" data-mask="9999-9999"', '', '', 3, 3);
		echo campo("ref1_telefono_trabajo", "Telefono Trabajo", 'text', $row["ref1_telefono_trabajo"], 'class="form-control" data-mask="9999-9999"', '', '', 3, 3);
		echo campo("ref1_telefono_celular", "Telefono Celular", 'text', $row["ref1_telefono_celular"], 'class="form-control" data-mask="9999-9999"', '', '', 3, 3);
		echo campo("ref1_relacion", "Relacion", 'text', $row["ref1_relacion"], 'class="form-control" ', '', '', 3, 4);
		echo "<hr>";
		echo "<br>";
		echo campo("ref2_nombre", "Nombre", 'text', $row["ref2_nombre"], 'class="form-control" ', '', '', 3, 6);
		echo campo("ref2_telefono_casa", "Telefono Casa", 'text', $row["ref2_telefono_casa"], 'class="form-control" data-mask="9999-9999"', '', '', 3, 3);
		echo campo("ref2_telefono_trabajo", "Telefono Trabajo", 'text', $row["ref2_telefono_trabajo"], 'class="form-control" data-mask="9999-9999"', '', '', 3, 3);
		echo campo("ref2_telefono_celular", "Telefono Celular", 'text', $row["ref2_telefono_celular"], 'class="form-control" data-mask="9999-9999"', '', '', 3, 3);
		echo campo("ref2_relacion", "Relacion", 'text', $row["ref2_relacion"], 'class="form-control" ', '', '', 3, 4);
		echo "<hr>";

		echo "<br><h4>DATOS DE REFERENCIAS FAMILIARES</h4><br>";
		echo "<br>";
		echo campo("ref3_nombre", "Nombre", 'text', $row["ref3_nombre"], 'class="form-control" ', '', '', 3, 6);
		echo campo("ref3_telefono_casa", "Telefono Casa", 'text', $row["ref3_telefono_casa"], 'class="form-control"data-mask="9999-9999" ', '', '', 3, 3);
		echo campo("ref3_telefono_trabajo", "Telefono Trabajo", 'text', $row["ref3_telefono_trabajo"], 'class="form-control" data-mask="9999-9999"', '', '', 3, 3);
		echo campo("ref3_telefono_celular", "Telefono Celular", 'text', $row["ref3_telefono_celular"], 'class="form-control" data-mask="9999-9999"', '', '', 3, 3);
		echo campo("ref3_relacion", "Relacion", 'text', $row["ref3_relacion"], 'class="form-control" ', '', '', 3, 4);
		echo "<hr>";
		echo "<br>";
		echo campo("ref4_nombre", "Nombre", 'text', $row["ref4_nombre"], 'class="form-control" ', '', '', 3, 6);
		echo campo("ref4_telefono_casa", "Telefono Casa", 'text', $row["ref4_telefono_casa"], 'class="form-control" data-mask="9999-9999"', '', '', 3, 3);
		echo campo("ref4_telefono_trabajo", "Telefono Trabajo", 'text', $row["ref4_telefono_trabajo"], 'class="form-control" data-mask="9999-9999"', '', '', 3, 3);
		echo campo("ref4_telefono_celular", "Telefono Celular", 'text', $row["ref4_telefono_celular"], 'class="form-control" data-mask="9999-9999"', '', '', 3, 3);
		echo campo("ref4_relacion", "Relacion", 'text', $row["ref4_relacion"], 'class="form-control" ', '', '', 3, 4);

		$usuario = strtoupper($_SESSION['usuario']);
		$es_cd = substr($usuario, 0, 2) === 'CD';
		$ya_enviado = !es_nulo($row["fecha_enviar_creditos"]);

		if (!($es_cd && $ya_enviado)) {
			echo '<br><a id="Guardar' . $forma . '" href="#" class="btn btn-primary" onclick="procesar_datos(\'creditos_gestion.php?a=3&b=' . $b . '&s=1&cid=' . $solicitud_id . '\',' . $forma . '); return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar</a>';
		}

		echo '<div id="respuesta' . $forma . '"> </div>';




		echo " </form></div></div></div></div>";
	} else {
		echo mensaje("No se encontraron registros", "info");
		exit;
	}


	exit;
}


if ($accion == Modulos::MODULO_DATOS_ADJUNTOS) //TODO documentos adjuntos
{
	$sql =
		"SELECT doc1, doc2, doc3, doc4, doc5, doc6, doc7, doc8, doc9, doc10, doc11, doc12,fecha_enviar_creditos
        FROM prestamo WHERE id = {$solicitud_id}";


	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		$row = $result->fetch_assoc();

		if (es_nulo($row["fecha_enviar_creditos"])) {
			echo ' <div class="panel panel-default"> <div id="remitircreditos" class="panel-body"> <div class="row"><div class="col-xs-12"> <form id="forma' . $forma . '" class="form-horizontal" autocomplete="off">';

			echo 'Cuando termine de ingresar la solicitud, precione el boton para remitir la solicitud al departamento de Creditos.';
			echo ' &nbsp;&nbsp;<a id="Guardar' . $forma . '" href="#" class="btn btn-primary" onclick="procesar_datos_remitirgestion(\'creditos_gestion.php?a=2&s2=1&cid=' . $solicitud_id . '\',' . $forma . '); return false;"><span class="glyphicon glyphicon-transfer" aria-hidden="true"></span> Remitir a Creditos</a>';

			echo '<div id="respuesta' . $forma . '"> </div>';

			echo " </form></div></div></div></div>";

			$forma = $forma + 10;
		}


		echo ' <div class="panel panel-default"> <div id="datosgenerales" class="panel-body"> <div class="row"> <div class="col-xs-12"> <form class="form-horizontal" autocomplete="off">';

		echo "<h4>Documentos Adjuntos</h4><br>";

		$doc1 = "upload";
		$doc2 = "upload";
		$doc3 = "upload";
		$doc4 = "upload";
		$doc5 = "upload";
		$doc6 = "upload";
		$doc7 = "upload";
		$doc8 = "upload";
		$doc9 = "upload";
		$doc10 = "upload";
		$doc11 = "upload";
		$doc12 = "upload";


		if ($row["doc1"] <> "") {
			$doc1 = "uploadlink";
		}
		if ($row["doc2"] <> "") {
			$doc2 = "uploadlink";
		}
		if ($row["doc3"] <> "") {
			$doc3 = "uploadlink";
		}
		if ($row["doc4"] <> "") {
			$doc4 = "uploadlink";
		}
		if ($row["doc5"] <> "") {
			$doc5 = "uploadlink";
		}
		if ($row["doc6"] <> "") {
			$doc6 = "uploadlink";
		}
		if ($row["doc7"] <> "") {
			$doc7 = "uploadlink";
		}
		if ($row["doc8"] <> "") {
			$doc8 = "uploadlink";
		}
		if ($row["doc9"] <> "") {
			$doc9 = "uploadlink";
		}
		if ($row["doc10"] <> "") {
			$doc10 = "uploadlink";
		}
		if ($row["doc11"] <> "") {
			$doc11 = "uploadlink";
		}
		if ($row["doc12"] <> "") {
			$doc12 = "uploadlink";
		}


		echo "<hr>";
		// cambio de boton de  portada 
		if ($row["doc1"] <> "" && $row["doc2"] <> "") {
			echo campo("doc1", "SOLICITUD PORTADA Y CONTRAPORTADA GENERADA POR EL SISTEMA", 'label', '<a id="btnimp_1' . $forma . '" href="#" class="btn btn-default" onclick="imprimir_portada(' . $solicitud_id . '); return false;"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Imprimir Portada y Contraportada </a>', 'class="form-control" ', '', '', 4, 5);
			echo "<hr>";
			echo campo_upload("doc1", "SOLICITUD PORTADA", $doc1, $row["doc1"], 'class="form-control" ', $solicitud_id, 3, 9, "SI");
			echo "<hr>";
			echo campo_upload("doc2", "SOLICITUD CONTRAPORTADA", $doc2, $row["doc2"], 'class="form-control" ', $solicitud_id, 3, 9, "SI");
		} else {
			echo campo("doc1", "SOLICITUD PORTADA Y CONTRAPORTADA GENERADA POR EL SISTEMA", 'label', '<a id="btnimp_1' . $forma . '" href="#" class="btn btn-default" onclick="imprimir_portada(' . $solicitud_id . '); return false;"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Imprimir Portada y Contraportada </a>', 'class="form-control" ', '', '', 4, 5);
			echo "<hr>";
			echo campo_upload("doc1", "SOLICITUD PORTADA Y CONTRAPORTADA FIRMADA", $doc1, $row["doc1"], 'class="form-control" ', $solicitud_id, 3, 9, "SI");
		}
		// echo campo_upload("doc2", "SOLICITUD CONTRAPORTADA", $doc2, $row["doc2"], 'class="form-control" ', $solicitud_id, 3, 9, "SI");
		echo "<hr>";
		echo campo_upload("doc3", "IDENTIDAD", $doc3, $row["doc3"], 'class="form-control" ', $solicitud_id, 3, 9, "SI");
		echo "<hr>";
		echo campo_upload("doc4", "LICENCIA", $doc4, $row["doc4"], 'class="form-control" ', $solicitud_id, 3, 9, "SI");
		echo "<hr>";
		echo campo_upload("doc5", "RTN", $doc5, $row["doc5"], 'class="form-control" ', $solicitud_id, 3, 9, "SI");
		echo "<hr>";
		echo campo_upload("doc6", "CONSTANCIA DE TRABAJO", $doc6, $row["doc6"], 'class="form-control" ', $solicitud_id, 3, 9, "SI");
		echo "<hr>";
		echo campo_upload("doc7", "RECIBOS PUBLICOS", $doc7, $row["doc7"], 'class="form-control" ', $solicitud_id, 3, 9, "SI");
		echo "<hr>";
		echo campo_upload("doc8", "CROQUIS", $doc8, $row["doc8"], 'class="form-control" ', $solicitud_id, 3, 9, "SI");
		//   echo "<hr>";
		//    echo campo_upload("doc9","COMPROBANTE PAGO (PRIMA)",$doc9,$row["doc9"],'class="form-control" ',$solicitud_id,3,9,"SI");
		//    echo "<hr>";
		//    echo campo_upload("doc10","COPIA FACTURA",$doc10,$row["doc10"],'class="form-control" ',$solicitud_id,3,9,"SI");



	}

	echo " </form></div></div></div></div>";
}


if ($accion == "41") { // TODO   guardar link de docto subido


	if (!isset($_REQUEST['nn'], $_REQUEST['dd'])) {
		echo mensaje("Debe seleccionar un archivo", "warning");
		exit;
	}
	$archivo_nombre = $conn->real_escape_string($_REQUEST['nn']);
	$dd = $conn->real_escape_string($_REQUEST['dd']);
	if ($archivo_nombre == "") {
		echo mensaje("Debe seleccionar un registro", "warning");
		exit;
	}

	$sql = "UPDATE prestamo SET {$dd} = '{$archivo_nombre}' WHERE id = {$solicitud_id}";

	if ($conn->query($sql) === TRUE) {


		echo "OK";
	} else {
		echo mensaje("Error al guardar documento", "warning");
		exit;
	}

	exit;
}


if ($accion == Modulos::MODULO_GESTIONES) //TODO gestiones
{
	//******* SQL ************************************************************************************

	$sql =
		"SELECT id, nombre, 
            (
				SELECT prestamo_gestion.estatus_id FROM prestamo_gestion 
				WHERE prestamo_gestion.prestamo_id= {$solicitud_id} 
					AND prestamo_gestion.etapa_id=prestamo_etapa.id
					AND prestamo_gestion.gestion_estado IS NULL
				ORDER BY prestamo_gestion.id DESC LIMIT 1
			) as estado_actual,
        	(
				SELECT count(prestamo_gestion.id) FROM prestamo_gestion 
				WHERE prestamo_gestion.prestamo_id = {$solicitud_id} AND 
					prestamo_gestion.etapa_id=prestamo_etapa.id
					and prestamo_gestion.gestion_estado is not null
					and prestamo_gestion.gestion_estado<>'Confirmado'
			) as pendientes       
    	FROM prestamo_etapa
		WHERE incluir_pasos = 1
		ORDER BY orden, id";

	// ****** Fin SQL ********************************************************************************

	$result = $conn->query($sql);

	if ($result->num_rows > 0) {

		echo
			'<div class="panel panel-default"> <div id="datosgenerales" class="panel-body"> <div class="row">  <div class="col-lg-12"> 
			<div class="col-lg-3" > 
			<ul class="nav nav-pills nav-stacked  " style="margin-right: 10px;">';

		while ($row = $result->fetch_assoc()) {

			$clase = "";
			$icono = '<i class="glyphicon glyphicon-question-sign"></i> ';

			if ($row["estado_actual"] == 1 or $row["estado_actual"] == "1") {
				$clase = 'class="alert-info"';
				$icono = '<i class="glyphicon glyphicon-info-sign"></i> ';
			}
			if ($row["estado_actual"] == 2 or $row["estado_actual"] == "2") {
				$clase = 'class="alert-success"';
				$icono = '<i class="glyphicon glyphicon-ok-sign"></i> ';
			}
			if ($row["estado_actual"] == 3 or $row["estado_actual"] == "3") {
				$clase = 'class="alert-danger"';
				$icono = '<i class="glyphicon glyphicon-ban-circle"></i> ';
			}

			$gpendientes = "";

			if ($row["pendientes"] > 0) {
				$gpendientes = '  <span class="label label-warning">' . $row["pendientes"] . '</span>';
			}

			echo '<li ' . $clase . ' onclick="actualizarSelect();"><a href="#b1" onclick=" actualizarbox(\'b1\',\'creditos_gestion.php?a=5b1&cst=' . $row["id"] . '&cid=' . $solicitud_id . '\') ; return false;" id="titulo' . $row["id"] . '" data-titulo="' . $row["nombre"] . '" class="text-muted">' . $icono . $row["nombre"] . $gpendientes . '</a></li>';

			// <li class="alert-success"><a href="#b1"   onclick="actualizarbox(\'b1\',\'creditos_gestion.php?a=5b1&cst=1&cid='. $solicitud_id .'\') ; return false;"  class="text-muted"><i class="glyphicon glyphicon-ok-sign"></i> Personal Info</a></li>
			// <li class="alert-success"><a href="#b2"   onclick="actualizarbox(\'b1\',\'creditos_gestion.php?a=5b1&cst=2&cid='. $solicitud_id .'\') ; return false;"  class="text-muted"><i class="glyphicon glyphicon-ok-sign"></i> Address</a></li>
			// <li class="alert-warning"><a href="#b3" onclick="actualizarbox(\'b1\',\'creditos_gestion.php?a=5b1&cst=3&cid='. $solicitud_id .'\') ; return false;" ><i class="glyphicon glyphicon-ban-circle"></i> Employment</a></li>
			// <li><a href="#" class="text-muted" onclick="actualizarbox(\'b1\',\'creditos_gestion.php?a=5b1&cst=4&cid='. $solicitud_id .'\') ; return false;"><i class="glyphicon glyphicon-question-sign"></i> Signatures</a></li>
			// <li><a href="#" class="text-muted" onclick="actualizarbox(\'b1\',\'creditos_gestion.php?a=5b1&cst=5&cid='. $solicitud_id .'\') ; return false;"><i class="glyphicon glyphicon-question-sign"></i> Status</a></li>
			//     
		}
		echo
			'	</ul>
			<hr>
  		</div>
		<div class="col-lg-9"> 
			<h4 id="eltitulogestion"></h4><br>
			<div class="tab-pane" id="b1"></div>
		</div>';

		echo "</div></div></div></div>";
	}

	if (isset($_REQUEST['b'])) { ?>
		<script>
			actualizarbox('b1', 'creditos_gestion.php?a=5b1&cst=<?php echo $_REQUEST['b'] ?>&cid=<?php echo $solicitud_id ?>');
		</script>
		<?php
	}
	exit;
}


if ($accion == "5x") //TODO gestiones marcar verificado
{
	$salida = "";
	//########## validar datos
	$verror = "";

	//  $verror.=validar("obs",$_REQUEST['obs'], "text", true,  null,  1,  null);



	// ######### Guardar 
	if ($verror == "") {


		$sqlcampos = "";
		$sqlcampos .= "  prestamo_id =" . GetSQLValue($conn->real_escape_string($_REQUEST[RequestParams::CREDITO_ID]), "int");
		$sqlcampos .= " , campo_id =" . GetSQLValue($conn->real_escape_string($_REQUEST["n"]), "int");
		$sqlcampos .= " , gestion_estado='Confirmado' ";
		$sqlcampos .= " , etapa_id =" . GetSQLValue($conn->real_escape_string($_REQUEST["eid"]), "int");
		$sqlcampos .= " , estatus_id =2";
		if (isset($_REQUEST['obs'])) {
			$sqlcampos .= " , descripcion =" . GetSQLValue($conn->real_escape_string($_REQUEST["obs"]), "text");
		}

		//$sqlcampos.= " , usuario_dirigido =".GetSQLValue($conn->real_escape_string($_REQUEST["usr"]),"text");  


		$sqlcampos .= ",usuario= '" . $_SESSION['usuario'] . "',fecha=curdate() ,hora=now()";
		$sqlcampos .= ",usuario_confirma= '" . $_SESSION['usuario'] . "' ,hora_confirma=now()";

		$sqlcampos .= " , bodega=(select prestamo.bodega from prestamo where prestamo.id=" . $conn->real_escape_string($_REQUEST[RequestParams::CREDITO_ID]) . " limit 1) ";
		if (tiene_permiso(PermisosModulos::PERMISOS_CANAL_INDIRECTO)) {
			$sqlcampos .= " , canal='CI'";
		}
		$sql = "insert into prestamo_gestion set " . $sqlcampos;

		if ($conn->query($sql) === TRUE) {
			//$gestion_id_new = mysqli_insert_id($conn);


			$salida = "OK";
		} else {

			$salida = 'Se produjo un error al guardar el registro DB101: <br>' . $conn->error;
		}
	} else {
		//mostrar errores validacion
		$salida = $verror;
	}


	//echo $sql; 
	echo $salida;
	exit;
}


if ($accion == "5x2c") //TODO gestiones marcar verificado, confirmacion de creditos
{
	$salida = "";
	//########## validar datos
	$verror = "";


	// ######### Guardar 
	if ($verror == "") {


		$sqlcampos = "";
		$sqlcampos .= "  gestion_estado='Confirmado' ";

		$sqlcampos .= " , estatus_id =2";

		$sqlcampos .= ",usuario_confirma= '" . $_SESSION['usuario'] . "' ,hora_confirma=now()";

		if (isset($_REQUEST['obs'])) {
			$sqlcampos .= " , texto_confirma =" . GetSQLValue($conn->real_escape_string($_REQUEST["obs"]), "text");
		}


		$sql = "update prestamo_gestion set " . $sqlcampos . " where prestamo_id=" . $conn->real_escape_string($_REQUEST[RequestParams::CREDITO_ID]) . " and campo_id=" . $conn->real_escape_string($_REQUEST['n']) . " and usuario_responde is not null";


		if ($conn->query($sql) === TRUE) {

			if ($_REQUEST['n'] == 0) { //si es nueva solicitud
				$conn->query("update prestamo set fecha_recibe_creditos=now() where id=$solicitud_id");
			}

			$salida = "OK";
		} else {

			$salida = 'Se produjo un error al guardar el registro DB101 <br>' . $conn->error;
		}
	} else {
		//mostrar errores validacion
		$salida = $verror;
	}



	echo $salida;
	exit;
}


if ($accion == "5y") //TODO gestiones nueva
{
	$salida = "";
	//########## validar datos
	$verror = "";

	$verror .= validar("Dirigido a", $_REQUEST['usr'], "text", true, null, 2, null);



	// ######### Guardar 
	if ($verror == "") {


		$sqlcampos = "";
		$sqlcampos .= "  prestamo_id =" . GetSQLValue($conn->real_escape_string($_REQUEST[RequestParams::CREDITO_ID]), "int");
		$sqlcampos .= " , campo_id =" . GetSQLValue($conn->real_escape_string($_REQUEST["n"]), "int");
		if ($conn->real_escape_string($_REQUEST["usr"]) == $_SESSION['usuario']) {
			$sqlcampos .= " , gestion_estado='Creditos' ";
			$sqlcampos .= " , usuario_responde='" . $_SESSION['usuario'] . "' ";
		} else {
			$sqlcampos .= " , gestion_estado='Vendedor' ";
		}

		$sqlcampos .= " , etapa_id =" . GetSQLValue($conn->real_escape_string($_REQUEST["eid"]), "int");
		$sqlcampos .= " , estatus_id =" . GetSQLValue($conn->real_escape_string($_REQUEST["est"]), "int");
		if (isset($_REQUEST['obs'])) {
			$sqlcampos .= " , descripcion =" . GetSQLValue($conn->real_escape_string($_REQUEST["obs"]), "text");
		}

		$sqlcampos .= " , usuario_dirigido =" . GetSQLValue($conn->real_escape_string($_REQUEST["usr"]), "text");


		$sqlcampos .= ",usuario= '" . $_SESSION['usuario'] . "',fecha=curdate() ,hora=now()";
		$sqlcampos .= " , bodega=(select prestamo.bodega from prestamo where prestamo.id=" . $conn->real_escape_string($_REQUEST[RequestParams::CREDITO_ID]) . " limit 1) ";
		if (tiene_permiso(PermisosModulos::PERMISOS_CANAL_INDIRECTO)) {
			$sqlcampos .= " , canal='CI'";
		}
		$sql = "insert into prestamo_gestion set " . $sqlcampos;

		if ($conn->query($sql) === TRUE) {
			$gestion_id_new = mysqli_insert_id($conn);
			enviar_notificacion_gestion($gestion_id_new, '', $conn->real_escape_string($_REQUEST["usr"]), $conn->real_escape_string($_REQUEST["obs"]));

			$salida = "OK";
		} else {

			$salida = 'Se produjo un error al guardar el registro DB101: <br>' . $conn->error;
		}
	} else {
		//mostrar errores validacion
		$salida = $verror;
	}


	//echo $sql;
	echo $salida;
	exit;
}


if ($accion == "5z") //TODO Responder a una gestion
{
	$salida = "";
	//########## validar datos
	$verror = "";
	$sqlcampos2 = "";

	if (isset($_REQUEST['geid'])) {
		$gestion_id = $conn->real_escape_string($_REQUEST['geid']);
	} else {
		$verror = mensaje("Debe seleccionar una solicitud", "danger");
		exit;
	}


	$verror .= validar("Respuesta", $_REQUEST['texto_responde'], "text", true, null, 2, null);

	if (isset($_POST)) {
		$sepa = "";
		while (list($key, $val) = each($_POST)) {
			$key = stripslashes($key);
			$val = stripslashes($val);
			if ($key <> "doc1" and $key <> "doc2" and $key <> "doc3" and $key <> "doc4" and $key <> "doc5" and $key <> "doc6" and $key <> "doc7" and $key <> "doc8" and $key <> "doc9") {
				$sqlcampos2 .= $sepa . $conn->real_escape_string($key) . "=" . GetSQLValue($conn->real_escape_string($val), "text");
				$sepa = " , ";
			}
		}
		// echo "update prestamo set $sqlcampos2 where id=$solicitud_id"; exit;  
		if ($sqlcampos2 <> "") {
			$conn->query("update prestamo set $sqlcampos2 where id=$solicitud_id");
		}
	}


	// ######### Guardar 
	if ($verror == "") {


		$sqlcampos = "";
		$sqlcampos .= "  texto_responde =" . GetSQLValue($conn->real_escape_string($_REQUEST["texto_responde"]), "text");

		$sqlcampos .= ",gestion_estado='Creditos'";
		$sqlcampos .= ",usuario_responde= '" . $_SESSION['usuario'] . "' ,hora_responde=now()";

		$sql = "update prestamo_gestion set " . $sqlcampos . " where id=$gestion_id";

		if ($conn->query($sql) === TRUE) {

			$salida = mensaje("Guardado Satisfactoriamente", "success");
		} else {
			$salida = mensaje('Se produjo un error al guardar el registro DB101: <br>' . $conn->error, "danger"); //<br>'.$conn->error;
		}
	} else {
		//mostrar errores validacion
		$salida = mensaje($verror, "danger");
	}



	echo $salida;
	exit;
}


if ($accion == "5g6") //TODO guardar verificacion de campo
{
	$salida = "";
	$sqlcampos2 = "";



	if (isset($_POST)) {
		$sepa = "";
		while (list($key, $val) = each($_POST)) {
			$key = stripslashes($key);
			$val = stripslashes($val);
			if ($key <> "doc1" and $key <> "doc2" and $key <> "doc3" and $key <> "doc4" and $key <> "doc5" and $key <> "doc6" and $key <> "doc7" and $key <> "doc8" and $key <> "doc9") {
				$sqlcampos2 .= $sepa . $conn->real_escape_string($key) . "=" . GetSQLValue($conn->real_escape_string($val), "text");
				$sepa = " , ";
			}
		}
		// echo "update prestamo set $sqlcampos2 where id=$solicitud_id"; exit;  
		if ($sqlcampos2 <> "") {


			if ($conn->query("update prestamo set $sqlcampos2 where id=$solicitud_id") === TRUE) {

				$salida = mensaje("Guardado Satisfactoriamente", "success");
			} else {
				$salida = mensaje('Se produjo un error al guardar el registro DB101: <br>' . $conn->error, "danger"); //<br>'.$conn->error;
			}
		}
	}





	echo $salida;
	exit;
}


if ($accion == "5g7") //TODO calculo finncero aaprobacion
{
	$salida = "";


	if ($_REQUEST[RequestParams::GUARDAR] == "2") //TODO Responder a una gestion
	{

		//########## validar datos
		$verror = "";
		$sqlcampos2 = "";

		if (isset($_REQUEST['geid'])) {
			$gestion_id = $conn->real_escape_string($_REQUEST['geid']);
		} else {
			$verror = mensaje("Debe seleccionar una solicitud", "danger");
			exit;
		}


		$verror .= validar("Respuesta", $_REQUEST['texto_responde'], "text", true, null, 2, null);


		// ######### Guardar 
		if ($verror == "") {


			$sqlcampos = "";
			$sqlcampos .= "  texto_responde =" . GetSQLValue($conn->real_escape_string($_REQUEST["texto_responde"]), "text");

			$sqlcampos .= ",gestion_estado='Confirmado'";
			$sqlcampos .= ",usuario_responde= '" . $_SESSION['usuario'] . "' ,hora_responde=now()";

			$sql = "update prestamo_gestion set " . $sqlcampos . " where id=$gestion_id";

			if ($conn->query($sql) === TRUE) {
				$conn->query("update prestamo set aprobado_gerencia_usuario='" . $_SESSION['usuario'] . "',aprobado_gerencia_fecha=now() where id=$solicitud_id  limit 1");
				$salida = mensaje("Guardado Satisfactoriamente", "success");
			} else {
				$salida = mensaje('Se produjo un error al guardar el registro DB101: <br>' . $conn->error, "danger"); //<br>'.$conn->error;
			}
		} else {
			//mostrar errores validacion
			$salida = mensaje($verror, "danger");
		}



		echo $salida;
		exit;
	}




	//########## validar datos
	$sql = "";
	$verror = "";
	$sqlcampos2 = "";


	// ######### Guardar 
	if ($verror == "") {
		$usuario_dirigido = "";
		$sql = "select usuario.usuario,usuario.nombre from usuario
                LEFT OUTER JOIN usuario_nivelxgrupo ON (usuario.grupo_id=usuario_nivelxgrupo.grupo_id) 
                where usuario.activo='SI' and usuario_nivelxgrupo.nivel_id=25
                group by usuario.usuario,usuario.nombre";

		$result2 = $conn->query($sql);


		if ($result2->num_rows > 0) {
			$row2 = $result2->fetch_assoc();
			$usuario_dirigido = $row2["usuario"];
		}



		// crear gestion avisar de condiciones aprobacion
		$sqlcampos = "";
		$sqlcampos .= "  prestamo_id =$solicitud_id";
		$sqlcampos .= " , gestion_estado='Creditos' ";
		$sqlcampos .= " , campo_id=701 "; //*****
		$sqlcampos .= " , etapa_id=7 ";
		$sqlcampos .= " , estatus_id=1 ";
		$sqlcampos .= " , descripcion='Solicitud de Aprobacion' ";
		$sqlcampos .= ",usuario= '" . $_SESSION['usuario'] . "',fecha=curdate() ,hora=now()";
		$sqlcampos .= ",usuario_dirigido= '$usuario_dirigido'";
		// $sqlcampos.= ",usuario_confirma= '" .$_SESSION['usuario'] . "' ,hora_confirma=now()";


		$sqlcampos .= " , bodega=(select prestamo.bodega from prestamo where prestamo.id=$solicitud_id limit 1) ";
		if (tiene_permiso(PermisosModulos::PERMISOS_CANAL_INDIRECTO)) {
			$sqlcampos .= " , canal='CI'";
		}
		$sql = "insert into prestamo_gestion set " . $sqlcampos;


		if ($conn->query($sql) === TRUE) {



			$gestion_id_new = mysqli_insert_id($conn);
			enviar_notificacion_gestion($gestion_id_new, '', $usuario_dirigido, 'Condiciones de Aprobacion');


			$salida = mensaje("Guardado Satisfactoriamente", "success");
		} else {
			$salida = mensaje('Se produjo un error al guardar el registro DB101: <br>' . $conn->error, "danger"); //<br>'.$conn->error;
		}
	} else {
		//mostrar errores validacion
		$salida = mensaje($verror, "danger");
	}



	echo $salida;
	exit;
}


if ($accion == "5g8") //TODO condiciones aprobacion
{
	$salida = "";
	//########## validar datos
	$sql = "";
	$verror = "";
	$sqlcampos2 = "";


	// ######### Guardar 
	if ($verror == "") {
		$usuario_dirigido = "";
		$sql = "SELECT usuario_alta
                    FROM prestamo
                    where id=$solicitud_id";

		$result2 = $conn->query($sql);


		if ($result2->num_rows > 0) {
			$row2 = $result2->fetch_assoc();
			$usuario_dirigido = $row2["usuario_alta"];
		}

		if (isset($_POST)) {
			$sepa = "";
			while (list($key, $val) = each($_POST)) {
				$key = stripslashes($key);
				$val = stripslashes($val);
				if ($key <> "doc1" and $key <> "doc2" and $key <> "doc3" and $key <> "doc4" and $key <> "doc5" and $key <> "doc6" and $key <> "doc7" and $key <> "doc8" and $key <> "doc9") {
					$sqlcampos2 .= $sepa . $conn->real_escape_string($key) . "=" . GetSQLValue($conn->real_escape_string($val), "text");
					$sepa = " , ";
				}
			}
			// echo "update prestamo set $sqlcampos2 where id=$solicitud_id"; exit;  
			if ($sqlcampos2 <> "") {
				$sql = "update prestamo set $sqlcampos2 where id=$solicitud_id";
			}
		}

		if ($sql <> "") {

			if ($conn->query($sql) === TRUE) {


				// crear gestion avisar de condiciones aprobacion
				$sqlcampos = "";
				$sqlcampos .= "  prestamo_id =$solicitud_id";
				$sqlcampos .= " , gestion_estado='Vendedor' ";
				$sqlcampos .= " , campo_id=801 "; //*****
				$sqlcampos .= " , etapa_id=8 ";
				$sqlcampos .= " , estatus_id=1 ";
				$sqlcampos .= " , descripcion='Condiciones de Aprobacion' ";
				$sqlcampos .= ",usuario= '" . $_SESSION['usuario'] . "',fecha=curdate() ,hora=now()";
				$sqlcampos .= ",usuario_dirigido= '$usuario_dirigido'";
				// $sqlcampos.= ",usuario_confirma= '" .$_SESSION['usuario'] . "' ,hora_confirma=now()";


				$sqlcampos .= " , bodega=(select prestamo.bodega from prestamo where prestamo.id=$solicitud_id limit 1) ";
				if (tiene_permiso(PermisosModulos::PERMISOS_CANAL_INDIRECTO)) {
					$sqlcampos .= " , canal='CI'";
				}
				$sql = "insert into prestamo_gestion set " . $sqlcampos;
				$conn->query($sql);
				$gestion_id_new = mysqli_insert_id($conn);
				enviar_notificacion_gestion($gestion_id_new, '', $usuario_dirigido, 'Condiciones de Aprobacion');


				$salida = mensaje("Guardado Satisfactoriamente", "success");
			} else {
				$salida = mensaje('Se produjo un error al guardar el registro DB101: <br>' . $conn->error, "danger"); //<br>'.$conn->error;
			}
		} else {
			$salida = mensaje('No se guardo ningun campo', "danger");
		}
	} else {
		//mostrar errores validacion
		$salida = mensaje($verror, "danger");
	}



	echo $salida;
	exit;
}


if ($accion == Modulos::MODULO_GUARDAR_CONTRATO_ADMIN) //TODO Generar Contrato
{
	$salida = "";
	$nuevo = true;
	//########## validar datos
	$verror = "";
	$verror .= validar("Fecha de Primera Cuota", $_REQUEST['cierre_cuota_primera'], "date", true, null, null, null);
	$verror .= validar("Fecha de Ultima Cuota", $_REQUEST['cierre_cuota_final'], "date", true, null, null, null);
	$verror .= validar("Fecha de Firma Contrato", $_REQUEST['cierre_firma_fecha'], "date", true, null, null, null);

	$sqlcampos2 = "";
	$usuario_dirigido = "";

	// ######### Guardar 
	if ($verror == "") {

		$sql = "SELECT cierre_contrato,usuario_alta FROM prestamo WHERE id = {$solicitud_id}";
		$result2 = $conn->query($sql);

		if ($result2->num_rows > 0) {
			$row2 = $result2->fetch_assoc();
			$usuario_dirigido = $row2["usuario_alta"];
			if ($row2["cierre_contrato"] > 0) {
				$nuevo = false;
			}
		}



		$sqlcampos = "";
		$sqlcampos .= "  numero_credito_sifco =" . GetSQLValue($conn->real_escape_string($_REQUEST["numero_credito_sifco"]), "text");
		$sqlcampos .= " , cierre_cuota_dia_pago =" . GetSQLValue($conn->real_escape_string($_REQUEST["cierre_cuota_dia_pago"]), "double");
		$sqlcampos .= " , cierre_cuota_primera =" . GetSQLValue(mysqldate($conn->real_escape_string($_REQUEST["cierre_cuota_primera"])), "text");
		$sqlcampos .= " , cierre_cuota_final =" . GetSQLValue(mysqldate($conn->real_escape_string($_REQUEST["cierre_cuota_final"])), "text");
		$sqlcampos .= " , cierre_firma_fecha =" . GetSQLValue(mysqldate($conn->real_escape_string($_REQUEST["cierre_firma_fecha"])), "text");

		if ($nuevo == true) {
			$contrato_num = get_dato_sql('prestamo', "(max(cierre_contrato)+1)", '');
			$sqlcampos .= " , cierre_contrato =" . GetSQLValue($contrato_num, "int");
		}


		$sqlcampos .= " , moto_serie =" . GetSQLValue($conn->real_escape_string($_REQUEST["moto_serie"]), "text");
		$sqlcampos .= " , moto_marca =" . GetSQLValue($conn->real_escape_string($_REQUEST["moto_marca"]), "text");
		$sqlcampos .= " , moto_modelo =" . GetSQLValue($conn->real_escape_string($_REQUEST["moto_modelo"]), "text");
		$sqlcampos .= " , moto_motor =" . GetSQLValue($conn->real_escape_string($_REQUEST["moto_motor"]), "text");
		$sqlcampos .= " , moto_color =" . GetSQLValue($conn->real_escape_string($_REQUEST["moto_color"]), "text");
		$sqlcampos .= " , moto_ano =" . GetSQLValue($conn->real_escape_string($_REQUEST["moto_ano"]), "text");
		$sqlcampos .= " , moto_cilindraje =" . GetSQLValue($conn->real_escape_string($_REQUEST["moto_cilindraje"]), "text");
		$sqlcampos .= " , moto_valor =" . GetSQLValue($conn->real_escape_string(filter_var($_REQUEST["moto_valor"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)), "double");
		$sqlcampos .= " , moto_categoria =" . GetSQLValue($conn->real_escape_string($_REQUEST["moto_categoria"]), "text");

		$sql = "UPDATE prestamo SET {$sqlcampos} WHERE id = {$solicitud_id}";


		if ($conn->query($sql) === TRUE) {
			$salida = "OK";

			// crear gestion avisar que fue creado contrato
			$sqlcampos = "";
			$sqlcampos .= "  prestamo_id =$solicitud_id";
			$sqlcampos .= " , gestion_estado='Vendedor' ";
			$sqlcampos .= " , campo_id=901 "; //*****
			$sqlcampos .= " , etapa_id=9 ";
			$sqlcampos .= " , estatus_id=1 ";
			$sqlcampos .= " , descripcion='Documentos legales listos para imprimir' ";
			$sqlcampos .= ",usuario= '" . $_SESSION['usuario'] . "',fecha=curdate() ,hora=now()";
			$sqlcampos .= ",usuario_dirigido= '$usuario_dirigido'";

			$sqlcampos .= " , bodega=(select prestamo.bodega from prestamo where prestamo.id = {$solicitud_id} limit 1) ";
			if (tiene_permiso(PermisosModulos::PERMISOS_CANAL_INDIRECTO)) {
				$sqlcampos .= " , canal='CI'";
			}
			$sql = "INSERT INTO prestamo_gestion SET " . $sqlcampos;
			//echo $sql;

			if ($nuevo == true) {
				$conn->query($sql);
				$gestion_id_new = mysqli_insert_id($conn);
				enviar_notificacion_gestion($gestion_id_new, '', $usuario_dirigido, 'Documentos legales listos para imprimir');
			}
		} else {
			$salida = mensaje('Se produjo un error al guardar el registro DB101: <br>' . $conn->error, "danger"); //<br>'.$conn->error; AQUIII
		}
	} else {
		//mostrar errores validacion
		$salida = mensaje($verror, "danger");
	}



	echo $salida;
	exit;
}


if ($accion == "5g10") //TODO Gestion crear envio de documentos
{
	$salida = "";
	$nuevo = true;
	//########## validar datos
	$verror = "";


	$sqlcampos2 = "";
	$usuario_dirigido = "";

	// ######### Guardar 
	if ($verror == "") {

		$sql = "SELECT cierre_documentos_enviados_gestion,usuario_alta
                    FROM prestamo
                    where id=$solicitud_id";

		$result2 = $conn->query($sql);


		if ($result2->num_rows > 0) {
			$row2 = $result2->fetch_assoc();
			$usuario_dirigido = $row2["usuario_alta"];
			if ($row2["cierre_documentos_enviados_gestion"] == "SI") {
				$nuevo = false;
			}
		}



		if ($nuevo == true) {
			$sql = "update prestamo set cierre_documentos_enviados_gestion='SI' where id=$solicitud_id";

			if ($conn->query($sql) === TRUE) {
				$salida = "OK";


				// crear gestion avisar que envio documentos
				$sqlcampos = "";
				$sqlcampos .= "  prestamo_id =$solicitud_id";
				$sqlcampos .= " , gestion_estado='Vendedor' ";
				$sqlcampos .= " , campo_id=1001 "; //*****
				$sqlcampos .= " , etapa_id=10 ";
				$sqlcampos .= " , estatus_id=1 ";
				$sqlcampos .= " , descripcion='Enviar documentos legales' ";
				$sqlcampos .= ",usuario= '" . $_SESSION['usuario'] . "',fecha=curdate() ,hora=now()";
				$sqlcampos .= ",usuario_dirigido= '$usuario_dirigido'";

				$sqlcampos .= " , bodega=(select prestamo.bodega from prestamo where prestamo.id=$solicitud_id limit 1) ";
				if (tiene_permiso(PermisosModulos::PERMISOS_CANAL_INDIRECTO)) {
					$sqlcampos .= " , canal='CI'";
				}
				$sql = "insert into prestamo_gestion set " . $sqlcampos;
				$conn->query($sql);
				$gestion_id_new = mysqli_insert_id($conn);
				enviar_notificacion_gestion($gestion_id_new, '', $usuario_dirigido, 'Enviar documentos legales');
			} else {
				$salida = mensaje('Se produjo un error al guardar el registro DB101: <br>' . $conn->error, "danger"); //<br>'.$conn->error;
			}
		}
	} else {
		//mostrar errores validacion
		$salida = mensaje($verror, "danger");
	}



	echo $salida;
	exit;
}


if ($accion == "5g11") //TODO en cierre de credito
{
	$salida = "";
	//########## validar datos
	$sql = "";
	$verror = "";
	$sqlcampos2 = "";

	$codcierre = $_REQUEST['cierre_razon'];

	if ($codcierre == "0" or $codcierre == "") {
		$verror = "Debe ingresar la razon del cierre";
	}


	// ######### Guardar 
	if ($verror == "") {

		if (isset($_POST)) {
			$sepa = "";
			while (list($key, $val) = each($_POST)) {
				$key = stripslashes($key);
				$val = stripslashes($val);
				if ($key <> "doc1" and $key <> "doc2" and $key <> "doc3" and $key <> "doc4" and $key <> "doc5" and $key <> "doc6" and $key <> "doc7" and $key <> "doc8" and $key <> "doc9") {
					$sqlcampos2 .= $sepa . $conn->real_escape_string($key) . "=" . GetSQLValue($conn->real_escape_string($val), "text");
					$sepa = " , ";
				}
			}
			// echo "update prestamo set $sqlcampos2 where id=$solicitud_id"; exit;  

			if ($codcierre == "1" or $codcierre === "2") {
				$sqlcampos2 .= " , estatus=2";
			} else {
				$sqlcampos2 .= " , estatus=3";
			}
			if ($sqlcampos2 <> "") {
				$sql = "update prestamo set $sqlcampos2 where id=$solicitud_id";
			}
		}

		if ($sql <> "") {

			if ($conn->query($sql) === TRUE) {

				$salida = mensaje("Guardado Satisfactoriamente", "success");
			} else {
				$salida = mensaje('Se produjo un error al guardar el registro DB101: <br>' . $conn->error, "danger"); //<br>'.$conn->error;
			}
		} else {
			$salida = mensaje('No se guardo ningun campo', "danger");
		}
	} else {
		//mostrar errores validacion
		$salida = mensaje($verror, "danger");
	}



	echo $salida;
	exit;
}


if ($accion == Modulos::MODULO_GUARDAR_DATOS_GESTION) //TODO en gestiones solo guardar
{
	$salida = "";
	//########## validar datos
	$sql = "";
	$verror = "";
	$sqlcampos2 = "";


	// ######### Guardar 
	if ($verror == "") {

		if (isset($_POST)) {

			$sepa = "";
			while (list($key, $val) = each($_POST)) {
				$key = stripslashes($key);
				$val = stripslashes($val);

				$campos_excluidos = [
					"doc1",
					"doc2",
					"doc3",
					"doc4",
					"doc5",
					"doc6",
					"doc7",
					"doc8",
					"doc9",
					"obs2_701",
					"estado2_701",
					"usr2_701"
				];
				if (!in_array($key, $campos_excluidos)) {
					$sqlcampos2 .= $sepa . $conn->real_escape_string($key) . "=" . GetSQLValue($conn->real_escape_string($val), "text");
					$sepa = " , ";
				}
			}
			// echo "update prestamo set $sqlcampos2 where id=$solicitud_id"; exit;  
			if ($sqlcampos2 <> "") {
				$sql = "UPDATE prestamo SET $sqlcampos2 WHERE id=$solicitud_id";
			}
		}

		if ($sql <> "") {

			if ($conn->query($sql) === TRUE) {

				$salida = mensaje("Guardado Satisfactoriamente", "success");
			} else {
				$salida = mensaje("Se produjo un error al guardar el registro DB101: <br> " . $conn->error, "danger"); //<br>'.$conn->error;
			}
		} else {
			$salida = mensaje('No se guardo ningun campo', "danger");
		}
	} else {
		//mostrar errores validacion
		$salida = mensaje($verror, "danger");
	}



	echo $salida;
	exit;
}


if ($accion === "IntAdd") {
	$salida = "";
	$sql = "";
	$verror = "";
	$interes_agregado = "";

	$interes_agregado = $_POST["interes_agregado"];
	$tasa = $_POST["tasa"];

	if ($verror == "") {
		if (isset($_POST)) {
			$interes_agregado = $_POST["interes_agregado"];
			$sql .= "update prestamo set cierre_interes_mensual = " . $interes_agregado . ", tasa = " . $tasa . " where id = " . $solicitud_id;

			if ($conn->query($sql) === true) {
				$salida = mensaje("Guardado Satisfactoriamente", "success");
			} else {
				$salida = mensaje('Se produjo un error al guardar el registro DB101: <br>' . $conn->error, "error");
			}
		}
	} else {
		$salida = mensaje($verror, "danger");
	}

	echo $salida;
	exit;
}


if ($accion == Modulos::MODULO_MOSTRAR_GESTION) //TODO gestiones
{

	if (isset($_REQUEST[RequestParams::GUARDAR])) {

		$etapa_id = GetSQLValue($conn->real_escape_string($_REQUEST["etapa_id"]), "int");

		$verror = "";
		$verror .= validar("Descripcion", $_REQUEST['descripcion'], "text", true, null, 2, null);
		$verror .= validar("Estado", $_REQUEST['estado'], "int", true, null, null, null);

		//  if ($_REQUEST['estado']=="0") {$verror.= 'Debe seleccionar el Estado de la gestion';}

		// Validar que no tenga gestiones abiertas para aprobar
		if (intval($_REQUEST['estado']) == Estados::ESTADO_APROBADO) {
			$sqlver =
				"SELECT count(prestamo_gestion.id) as pendientes FROM prestamo_gestion 
				WHERE prestamo_gestion.prestamo_id= {$solicitud_id} 
					AND prestamo_gestion.etapa_id = {$etapa_id}
					AND prestamo_gestion.gestion_estado is not null
					AND prestamo_gestion.gestion_estado<>'Confirmado'";
			$result = $conn->query($sqlver);
			if ($result->num_rows > 0) {
				$row = $result->fetch_assoc();
				if ($row["pendientes"] > 0) {
					$verror = "{$sqlver} <br><br> No se puede aprobar la etapa porque hay {$row["pendientes"]} gestiones pendientes";
				}
			}
		}


		// ######### Guardar 
		if ($verror == "") {
			$prestamo_id = GetSQLValue($conn->real_escape_string($_REQUEST[RequestParams::CREDITO_ID]), "int");

			$sqlcampos = "";
			$sqlcampos .= "  etapa_id =" . $etapa_id;
			$sqlcampos .= " , prestamo_id =" . $prestamo_id;
			$sqlcampos .= " , estatus_id =" . GetSQLValue($conn->real_escape_string($_REQUEST["estado"]), "int");
			$sqlcampos .= " , descripcion =" . GetSQLValue($conn->real_escape_string($_REQUEST["descripcion"]), "text");
			$sqlcampos .= " , bodega=(select prestamo.bodega from prestamo where prestamo.id=" . $prestamo_id . " limit 1) ";

			$sqlcampos .= ",usuario= '" . $_SESSION['usuario'] . "',fecha=curdate() ,hora=now()";
			if (tiene_permiso(PermisosModulos::PERMISOS_CANAL_INDIRECTO)) {
				$sqlcampos .= ", canal='CI'";
			}
			$sql = "insert into prestamo_gestion set " . $sqlcampos;

			if ($conn->query($sql) === true) {
				$insert_id = mysqli_insert_id($conn);

				$conn->query("update prestamo set etapa_proceso=ifnull((select max(etapa_id) from prestamo_gestion where prestamo_id=$prestamo_id limit 1),$etapa_id) where id=$prestamo_id");
				$stud_arr[0]["pcode"] = 1;
				$stud_arr[0]["pmsg"] = 'Los datos fueron guardados satisfactoriamente. El numero de gestion es: <strong>' . $insert_id . '</strong>';
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

	if (isset($_REQUEST[RequestParams::ETAPA_CREDITO])) {
		$cod_status = $conn->real_escape_string($_REQUEST[RequestParams::ETAPA_CREDITO]);
	} else {
		exit;
	}

	$campo_unico = "";
	if (isset($_REQUEST[RequestParams::CAMPO_UNICO])) {
		$campo_unico = $conn->real_escape_string($_REQUEST[RequestParams::CAMPO_UNICO]);
	}

	if ($campo_unico == "") {
		echo '<script> $(\'#eltitulogestion\').text($(titulo' . $cod_status . ').data(\'titulo\')) ; </script>';
	}

	$mostrar_todo = $campo_unico == "701" && tiene_permiso(PermisosModulos::PERMISOS_PERSONAL_CREDITO);
	// $mostrar_todo = false;
	// if ($campo_unico == "701" && tiene_permiso(PermisosModulos::PERMISOS_PERSONAL_CREDITO)) {
	// 	$mostrar_todo = true;
	// }

	//----------------------------------

	$asignados = leer_verificaciones_asignados($solicitud_id);

	echo campo('ccetapa', '', 'hidden', $cod_status, '');

	echo "<form id='forma2{$forma}' class='form-horizontal' autocomplete='off'>";

	if ($cod_status == TipoEtapas::ETAPA_NUEVA_SOLICITUD && tiene_permiso(PermisosModulos::PERMISOS_PERSONAL_CREDITO)) { //Nueva solicitud

		$tmpasignado[0] = 'Creditos';
		$tmpasignado["desc"][0] = "";
		$tmpasignado["desc2"][0] = "";
		$tmpasignado["desc3"][0] = "";
		$tmpasignado["desc4"][0] = "";

		echo '<div class="row">';
		echo "<hr>";
		echo "<strong>Solicitud Nueva</strong>, presione el boton para confirmar la recepcion de la misma:<br><br>";
		echo boton_verificar(0, $solicitud_id, false, '', $tmpasignado, $campo_unico);
		echo "<hr>";
		echo '</div>';
	}

	if ($cod_status == TipoEtapas::ETAPA_VERIFICAR_DOCUMENTOS || $mostrar_todo) { //VERIFICAR DOCUMENTOS

		$sql =
			"SELECT id, doc1, doc2, doc3, doc4, doc5, doc6, doc7, doc8, doc9, doc10, doc11, doc12, usuario_alta
			FROM prestamo WHERE id = {$solicitud_id}";

		$result = $conn->query($sql);

		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();

			if ($mostrar_todo == true) {
				//desplegar documentos adjuntos

				if (!empty($row["doc2"])) {
					echo incrustar_objeto("SOLICITUD PORTADA", $row["doc1"]);
					echo incrustar_objeto("SOLICITUD CONTRAPORTADA", $row["doc2"]);
				} else {
					echo incrustar_objeto("SOLICITUD PORTADA Y CONTRAPORTADA", $row["doc1"]);
				}

				echo incrustar_objeto("IDENTIDAD", $row["doc3"]);

				echo incrustar_objeto("LICENCIA", $row["doc4"]);

				echo incrustar_objeto("RTN", $row["doc5"]);

				echo incrustar_objeto("CONSTANCIA DE TRABAJO", $row["doc6"]);

				echo incrustar_objeto("RECIBOS PUBLICOS", $row["doc7"]);

				echo incrustar_objeto("CROQUIS", $row["doc8"]);

				echo incrustar_objeto("COMPROBANTE PAGO (PRIMA)", $row["doc9"]);
			} else {

				if (!empty($row["doc2"])) {
					if ($campo_unico == "" or $campo_unico == 1 or $mostrar_todo) {
						echo "<hr><div class=\"row\"><div class=\"col-xs-6\">" . "SOLICITUD PORTADA" . "</div><div class=\"col-xs-6\">" . boton_verificar(1, $solicitud_id, false, $row["usuario_alta"], $asignados, $campo_unico) . "</div></div>";
						echo "<div class=\"row\"><div class=\"col-xs-12\">" . campo_upload("doc1", "", 'uploadlink', $row["doc1"], 'class="form-control" ', $row["id"], 0, 12, "SI") . "</div></div><br><br>";
					}

					if ($campo_unico == "" or $campo_unico == 2 or $mostrar_todo) {
						echo "<hr><div class=\"row\"><div class=\"col-xs-6\">" . "SOLICITUD CONTRAPORTADA" . "</div><div class=\"col-xs-6\">" . boton_verificar(2, $solicitud_id, false, $row["usuario_alta"], $asignados, $campo_unico) . "</div></div>";
						echo "<div class=\"row\"><div class=\"col-xs-12\">" . campo_upload("doc2", "", 'uploadlink', $row["doc2"], 'class="form-control" ', $row["id"], 0, 12, "SI") . "</div></div><br><br>";
					}
				} else {
					if ($campo_unico == "" or $campo_unico == 1 or $mostrar_todo) {
						echo "<hr><div class=\"row\"><div class=\"col-xs-6\">" . "SOLICITUD PORTADA Y CONTRAPORTADA" . "</div><div class=\"col-xs-6\">" . boton_verificar(1, $solicitud_id, false, $row["usuario_alta"], $asignados, $campo_unico) . "</div></div>";
						echo "<div class=\"row\"><div class=\"col-xs-12\">" . campo_upload("doc1", "", 'uploadlink', $row["doc1"], 'class="form-control" ', $row["id"], 0, 12, "SI") . "</div></div><br><br>";
					}
				}
				if ($campo_unico == "" or $campo_unico == 3 or $mostrar_todo) {
					echo "<hr><div class=\"row\"><div class=\"col-xs-6\">" . "IDENTIDAD" . "</div><div class=\"col-xs-6\">" . boton_verificar(3, $solicitud_id, false, $row["usuario_alta"], $asignados, $campo_unico) . "</div></div>";
					echo "<div class=\"row\"><div class=\"col-xs-12\">" . campo_upload("doc3", "", 'uploadlink', $row["doc3"], 'class="form-control" ', $row["id"], 0, 12, "SI") . "</div></div><br><br>";
				}
				if ($campo_unico == "" or $campo_unico == 4 or $mostrar_todo) {
					echo "<hr><div class=\"row\"><div class=\"col-xs-6\">" . "LICENCIA" . "</div><div class=\"col-xs-6\">" . boton_verificar(4, $solicitud_id, false, $row["usuario_alta"], $asignados, $campo_unico) . "</div></div>";
					echo "<div class=\"row\"><div class=\"col-xs-12\">" . campo_upload("doc4", "", 'uploadlink', $row["doc4"], 'class="form-control" ', $row["id"], 0, 12, "SI") . "</div></div><br><br>";
				}
				if ($campo_unico == "" or $campo_unico == 5 or $mostrar_todo) {
					echo "<hr><div class=\"row\"><div class=\"col-xs-6\">" . "RTN" . "</div><div class=\"col-xs-6\">" . boton_verificar(5, $solicitud_id, false, $row["usuario_alta"], $asignados, $campo_unico) . "</div></div>";
					echo "<div class=\"row\"><div class=\"col-xs-12\">" . campo_upload("doc5", "", 'uploadlink', $row["doc5"], 'class="form-control" ', $row["id"], 0, 12, "SI") . "</div></div><br><br>";
				}
				if ($campo_unico == "" or $campo_unico == 6 or $mostrar_todo) {
					echo "<hr><div class=\"row\"><div class=\"col-xs-6\">" . "CONSTANCIA DE TRABAJO" . "</div><div class=\"col-xs-6\">" . boton_verificar(6, $solicitud_id, false, $row["usuario_alta"], $asignados, $campo_unico) . "</div></div>";
					echo "<div class=\"row\"><div class=\"col-xs-12\">" . campo_upload("doc6", "", 'uploadlink', $row["doc6"], 'class="form-control" ', $row["id"], 0, 12, "SI") . "</div></div><br><br>";
				}
				if ($campo_unico == "" or $campo_unico == 7 or $mostrar_todo) {
					echo "<hr><div class=\"row\"><div class=\"col-xs-6\">" . "RECIBOS PUBLICOS" . "</div><div class=\"col-xs-6\">" . boton_verificar(7, $solicitud_id, false, $row["usuario_alta"], $asignados, $campo_unico) . "</div></div>";
					echo "<div class=\"row\"><div class=\"col-xs-12\">" . campo_upload("doc7", "", 'uploadlink', $row["doc7"], 'class="form-control" ', $row["id"], 0, 12, "SI") . "</div></div><br><br>";
				}
				if ($campo_unico == "" or $campo_unico == 8 or $mostrar_todo) {
					echo "<hr><div class=\"row\"><div class=\"col-xs-6\">" . "CROQUIS" . "</div><div class=\"col-xs-6\">" . boton_verificar(8, $solicitud_id, false, $row["usuario_alta"], $asignados, $campo_unico) . "</div></div>";
					echo "<div class=\"row\"><div class=\"col-xs-12\">" . campo_upload("doc8", "", 'uploadlink', $row["doc8"], 'class="form-control" ', $row["id"], 0, 12, "SI") . "</div></div><br><br>";
				}
				if ($campo_unico == "" or $campo_unico == 9 or $mostrar_todo) {
					echo "<hr><div class=\"row\"><div class=\"col-xs-6\">" . "COMPROBANTE PAGO (PRIMA)" . "</div><div class=\"col-xs-6\">" . boton_verificar(9, $solicitud_id, false, $row["usuario_alta"], $asignados, $campo_unico) . "</div></div>";
					echo "<div class=\"row\"><div class=\"col-xs-12\">" . campo_upload("doc9", "", 'uploadlink', $row["doc9"], 'class="form-control" ', $row["id"], 0, 12, "SI") . "</div></div><br><br>";
					echo "<hr>";
				}
			} //else mostrar todo fin
		} else {
			echo mensaje("No se encontraron registros", "info");
			exit;
		}
	}

	if ($cod_status == TipoEtapas::ETAPA_VERIFICAR_BURO || $mostrar_todo) { //VERIFICAR BURO

		$sql =
			"SELECT id,doc1, doc2, doc3, doc4, doc5, doc6, doc7, doc8, doc9, doc10, doc11, doc12, usuario_alta
    		FROM prestamo WHERE id = {$solicitud_id}";

		$result = $conn->query($sql);

		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();

			if ($mostrar_todo == true) {
				//desplegar documentos adjuntos
				echo incrustar_objeto("IDENTIDAD", $row["doc3"]);
				echo incrustar_objeto("DOCUMENTO DEL BURO", $row["doc11"]);
			} else {
				echo ' <div class="row">';
				echo "<hr>";
				echo campo("doc3", "IDENTIDAD", 'uploadlink', $row["doc3"], 'class="form-control" ', $row["id"]);
				echo "<hr>";
				echo campo_upload("doc11", "DOCUMENTO DEL BURO", 'uploadlink', $row["doc11"], 'class="form-control" ', $row["id"], 0, 12, "SI");
				echo "<hr>";
				echo '</div>';
			}
		} else {
			echo mensaje("No se encontraron registros", "info");
			exit;
		}
	}

	if ($cod_status == TipoEtapas::ETAPA_VERIFICAR_TELEFONIA || $mostrar_todo) { //VERIFICAR telefonica


		$sql =
			"SELECT 
				id, nombres, apellidos, identidad, 
				direccion, ciudad, departamento, direccion_gps,
				telefono, celular, sexo, email, estado_civil, 
				nombre_conyuge, profesion, fecha_nacimiento, escolaridad, no_dependientes, 
				vecino, vecino_telefono, tipo_vivienda, antiguedad_vivienda, 
				empresa, empresa_direccion, empresa_tipo_empleo, empresa_tipo_condicion, empresa_telefono, 
				empresa_extension, empresa_salario, empresa_salario_otro, empresa_salario_otro_tipo, 
				empresa_puesto, empresa_fecha_ingreso, 
            	ref1_nombre, ref1_telefono_casa, ref1_telefono_trabajo, ref1_telefono_celular, 
				ref2_nombre, ref2_telefono_casa, ref2_telefono_trabajo, ref2_telefono_celular, 
				ref3_nombre, ref3_telefono_casa, ref3_telefono_trabajo, ref3_telefono_celular, 
				ref4_nombre, ref4_telefono_casa, ref4_telefono_trabajo, ref4_telefono_celular,
         		ref1_relacion, ref2_relacion, ref3_relacion, ref4_relacion,
            	telefono2, telefono3, vecino_telefono2, empresa_telefono2, usuario_alta
            FROM prestamo
            WHERE id = {$solicitud_id}";

		$result = $conn->query($sql);

		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();

			echo
				"<div class='row'>
				<hr>
				<div class='row'>
					<div class='col-xs-8'><h4>DATOS</h4></div>
					<div class='col-xs-4'></div>
				</div>
				<div class='row'>
					<div class='col-xs-8'>
						<h5>Nombre</h5>: {$row["nombres"]} {$row["apellidos"]}<br>
						<h5>Direccion</h5>: {$row["direccion"]}<br>
						<h5>Ciudad</h5>: {$row["ciudad"]}<br>
					</div>
				</div>
			</div>";
			//echo '<div class="row">';
			// echo "<hr><div class=\"row\"><div class=\"col-xs-8\"><h4>DATOS </h4></div><div class=\"col-xs-4\"></div></div>";
			// echo "<div class=\"row\"><div class=\"col-xs-8\">";
			// echo "Nombre: " . $row["nombres"] . " " . $row["apellidos"] . "<br>";
			// echo "Direccion: " . $row["direccion"] . "<br>";
			// echo "Ciudad: " . $row["ciudad"] . "<br>";
			// echo "</div></div>";


			if ($campo_unico == "" or $campo_unico == 11 or $mostrar_todo) {
				echo "<hr><div class=\"row\"><div class=\"col-xs-8\">" . "Telefono" . "</div></div>";
				echo "<div class=\"row\"><div class=\"col-xs-8\">" . campo("telefono", "", 'text', $row["telefono"], 'class="form-control" ', '', '', 3, 3) . "</div><div class=\"col-xs-4\">" . boton_verificar(11, $solicitud_id, true, $row["usuario_alta"], $asignados, $campo_unico) . "</div></div>";
			}
			if ($campo_unico == "" or $campo_unico == 12 or $mostrar_todo) {
				echo "<hr><div class=\"row\"><div class=\"col-xs-8\">" . "Telefono 2" . "</div></div>";
				echo "<div class=\"row\"><div class=\"col-xs-8\">" . campo("telefono2", "", 'text', $row["telefono2"], 'class="form-control" ', '', '', 3, 3) . "</div><div class=\"col-xs-4\">" . boton_verificar(12, $solicitud_id, true, $row["usuario_alta"], $asignados, $campo_unico) . "</div></div>";
			}
			if ($campo_unico == "" or $campo_unico == 13 or $mostrar_todo) {
				echo "<hr><div class=\"row\"><div class=\"col-xs-8\">" . "Celular" . "</div></div>";
				echo "<div class=\"row\"><div class=\"col-xs-8\">" . campo("celular", "", 'text', $row["celular"], 'class="form-control" ', '', '', 3, 3) . "</div><div class=\"col-xs-4\">" . boton_verificar(13, $solicitud_id, true, $row["usuario_alta"], $asignados, $campo_unico) . "</div></div>";
			}
			if ($campo_unico == "" or $campo_unico == 14 or $mostrar_todo) {
				echo "<hr><div class=\"row\"><div class=\"col-xs-8\">" . "Celular 2" . "</div></div>";
				echo "<div class=\"row\"><div class=\"col-xs-8\">" . campo("telefono3", "", 'text', $row["telefono3"], 'class="form-control" ', '', '', 3, 3) . "</div><div class=\"col-xs-4\">" . boton_verificar(14, $solicitud_id, true, $row["usuario_alta"], $asignados, $campo_unico) . "</div></div>";
			}


			if ($campo_unico == "" or $campo_unico == 15 or $mostrar_todo) {
				echo "<hr><div class=\"row\"><div class=\"col-xs-8\">" . "<h4>DATOS DE PARIENTE / VECINO</h4>" . "</div></div>";
				echo "<div class=\"row\"><div class=\"col-xs-8\">";
				echo "Pariente / Vecino: " . $row["vecino"] . "<br>";
				echo "Vecino Telefono: " . $row["vecino_telefono"] . "<br>";
				echo "Vecino Telefono2: " . $row["vecino_telefono2"] . "<br>";
				echo "</div><div class=\"col-xs-4\">" . boton_verificar(15, $solicitud_id, true, $row["usuario_alta"], $asignados, $campo_unico) . "</div></div>";
			}
			if ($campo_unico == "" or $campo_unico == 16 or $mostrar_todo) {
				echo "<hr><div class=\"row\"><div class=\"col-xs-8\">" . "<h4>DATOS DE REFERENCIAS PERSONALES</h4>" . "</div></div>";
				echo "<div class=\"row\"><div class=\"col-xs-8\">";
				echo "Nombre: " . $row["ref1_nombre"] . "<br>";
				echo "Telefono Casa: " . $row["ref1_telefono_casa"] . "<br>";
				echo "Telefono Trabajo: " . $row["ref1_telefono_trabajo"] . "<br>";
				echo "Telefono Celular: " . $row["ref1_telefono_celular"] . "<br>";
				echo "Relacion: " . $row["ref1_relacion"] . "<br>";
				echo "</div><div class=\"col-xs-4\">" . boton_verificar(16, $solicitud_id, true, $row["usuario_alta"], $asignados, $campo_unico) . "</div></div>";
			}
			if ($campo_unico == "" or $campo_unico == 17 or $mostrar_todo) {
				echo "<hr><div class=\"row\"><div class=\"col-xs-8\">" . "<h4>DATOS DE REFERENCIAS PERSONALES</h4>" . "</div></div>";
				echo "<div class=\"row\"><div class=\"col-xs-8\">";
				echo "Nombre: " . $row["ref2_nombre"] . "<br>";
				echo "Telefono Casa: " . $row["ref2_telefono_casa"] . "<br>";
				echo "Telefono Trabajo: " . $row["ref2_telefono_trabajo"] . "<br>";
				echo "Telefono Celular: " . $row["ref2_telefono_celular"] . "<br>";
				echo "Relacion: " . $row["ref2_relacion"] . "<br>";
				echo "</div><div class=\"col-xs-4\">" . boton_verificar(17, $solicitud_id, true, $row["usuario_alta"], $asignados, $campo_unico) . "</div></div>";
			}

			if ($campo_unico == "" or $campo_unico == 18 or $mostrar_todo) {
				echo "<hr><div class=\"row\"><div class=\"col-xs-8\">" . "<h4>DATOS DE REFERENCIAS FAMILIARES</h4>" . "</div></div>";
				echo "<div class=\"row\"><div class=\"col-xs-8\">";
				echo "Nombre: " . $row["ref3_nombre"] . "<br>";
				echo "Telefono Casa: " . $row["ref3_telefono_casa"] . "<br>";
				echo "Telefono Trabajo: " . $row["ref3_telefono_trabajo"] . "<br>";
				echo "Telefono Celular: " . $row["ref3_telefono_celular"] . "<br>";
				echo "Relacion: " . $row["ref3_relacion"] . "<br>";
				echo "</div><div class=\"col-xs-4\">" . boton_verificar(18, $solicitud_id, true, $row["usuario_alta"], $asignados, $campo_unico) . "</div></div>";
			}
			if ($campo_unico == "" or $campo_unico == 19 or $mostrar_todo) {
				echo "<hr><div class=\"row\"><div class=\"col-xs-8\">" . "<h4>DATOS DE REFERENCIAS FAMILIARES</h4>" . "</div></div>";
				echo "<div class=\"row\"><div class=\"col-xs-8\">";
				echo "Nombre: " . $row["ref4_nombre"] . "<br>";
				echo "Telefono Casa: " . $row["ref4_telefono_casa"] . "<br>";
				echo "Telefono Trabajo: " . $row["ref4_telefono_trabajo"] . "<br>";
				echo "Telefono Celular: " . $row["ref4_telefono_celular"] . "<br>";
				echo "Relacion: " . $row["ref4_relacion"] . "<br>";
				echo "</div><div class=\"col-xs-4\">" . boton_verificar(19, $solicitud_id, true, $row["usuario_alta"], $asignados, $campo_unico) . "</div></div>";
			}

			echo '</div>';
			echo "<hr>";
		}
	}

	if ($cod_status == TipoEtapas::ETAPA_VERIFICACION_LABORAL || $mostrar_todo) { //VERIFICAR de laboral

		$sql =
			"SELECT 
				id, nombres, apellidos, identidad, 
				direccion, ciudad, departamento, telefono, 
				celular, sexo, email, direccion_gps, 
				estado_civil, nombre_conyuge, profesion, fecha_nacimiento, escolaridad, 
				no_dependientes, vecino, vecino_telefono, tipo_vivienda, antiguedad_vivienda, empresa, empresa_direccion, empresa_tipo_empleo, empresa_tipo_condicion, empresa_telefono, empresa_extension, empresa_salario, empresa_salario_otro, empresa_salario_otro_tipo, empresa_puesto, empresa_fecha_ingreso, 
            	ref1_nombre, ref1_telefono_casa, ref1_telefono_trabajo, ref1_telefono_celular, 
				ref2_nombre, ref2_telefono_casa, ref2_telefono_trabajo, ref2_telefono_celular, 
				ref3_nombre, ref3_telefono_casa, ref3_telefono_trabajo, ref3_telefono_celular, 
				ref4_nombre, ref4_telefono_casa, ref4_telefono_trabajo, ref4_telefono_celular,
            	ref1_relacion, ref2_relacion, ref3_relacion, ref4_relacion,
            	telefono2, telefono3, vecino_telefono2, empresa_telefono2, requiere_verificacion_campo_laboral,
            	usuario_alta
            FROM prestamo WHERE id = {$solicitud_id}";

		$result = $conn->query($sql);


		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();
			echo "<br><h4>DATOS LABORALES</h4><br>";
		}
		if ($campo_unico == "" or $campo_unico == 20 or $mostrar_todo) {
			echo ' <div class="row">';
		}
		if ($campo_unico == "" or $campo_unico == 21 or $mostrar_todo) {
			echo "<hr><div class=\"row\"><div class=\"col-xs-8\">" . "Empresa donde Labora" . "</div></div>";
			echo "<div class=\"row\"><div class=\"col-xs-8\">" . campo("empresa", "", 'text', $row["empresa"], 'class="form-control" ', '', '', 3, 7) . "</div><div class=\"col-xs-4\">" . boton_verificar(20, $solicitud_id, false, $row["usuario_alta"], $asignados, $campo_unico) . "</div></div>";
		}
		if ($campo_unico == "" or $campo_unico == 21 or $mostrar_todo) {
			echo "<hr><div class=\"row\"><div class=\"col-xs-8\">" . "Direccion" . "</div></div>";
			echo "<div class=\"row\"><div class=\"col-xs-8\">" . campo("empresa_direccion", "", 'text', $row["empresa_direccion"], 'class="form-control" ', '', '', 3, 9) . "</div><div class=\"col-xs-4\">" . boton_verificar(21, $solicitud_id, false, $row["usuario_alta"], $asignados, $campo_unico) . "</div></div>";
		}
		if ($campo_unico == "" or $campo_unico == 22 or $mostrar_todo) {
			echo "<hr><div class=\"row\"><div class=\"col-xs-8\">" . "Condicion Laboral" . "</div></div>";
			echo "<div class=\"row\"><div class=\"col-xs-8\">" . campo("empresa_tipo_empleo", "", 'select', valores_combobox_texto('<option value="">Seleccione</option><option value="ASALARIADO">ASALARIADO</option><option value="INDEPENDIENTE">INDEPENDIENTE</option>', $row["empresa_tipo_empleo"]), 'class="form-control" ', '', '', 3, 3) . "</div><div class=\"col-xs-4\">" . boton_verificar(22, $solicitud_id, false, $row["usuario_alta"], $asignados, $campo_unico) . "</div></div>";
		}
		if ($campo_unico == "" or $campo_unico == 23 or $mostrar_todo) {
			echo "<hr><div class=\"row\"><div class=\"col-xs-8\">" . "Tipo de Condicion" . "</div></div>";
			echo "<div class=\"row\"><div class=\"col-xs-8\">" . campo("empresa_tipo_condicion", "", 'select', valores_combobox_texto('<option value="">Seleccione</option><option value="FIJO">FIJO</option><option value="TEMPORAL">TEMPORAL</option><option value="INTERINO">INTERINO</option>', $row["empresa_tipo_condicion"]), 'class="form-control" ', '', '', 3, 3) . "</div><div class=\"col-xs-4\">" . boton_verificar(23, $solicitud_id, false, $row["usuario_alta"], $asignados, $campo_unico) . "</div></div>";
		}
		if ($campo_unico == "" or $campo_unico == 24 or $mostrar_todo) {
			echo "<hr><div class=\"row\"><div class=\"col-xs-8\">" . "Telefono" . "</div></div>";
			echo "<div class=\"row\"><div class=\"col-xs-8\">" . campo("empresa_telefono", "", 'text', $row["empresa_telefono"], 'class="form-control" ', '', '', 3, 4) . "</div><div class=\"col-xs-4\">" . boton_verificar(24, $solicitud_id, true, $row["usuario_alta"], $asignados, $campo_unico) . "</div></div>";
		}
		if ($campo_unico == "" or $campo_unico == 25 or $mostrar_todo) {
			echo "<hr><div class=\"row\"><div class=\"col-xs-8\">" . "Telefono 2" . "</div></div>";
			echo "<div class=\"row\"><div class=\"col-xs-8\">" . campo("empresa_telefono2", "", 'text', $row["empresa_telefono2"], 'class="form-control" ', '', '', 3, 4) . "</div><div class=\"col-xs-4\">" . boton_verificar(25, $solicitud_id, true, $row["usuario_alta"], $asignados, $campo_unico) . "</div></div>";
		}
		if ($campo_unico == "" or $mostrar_todo) {
			echo "<hr><div class=\"row\"><div class=\"col-xs-8\">" . "Extension" . "</div><div class=\"col-xs-4\"></div></div>";
			echo "<div class=\"row\"><div class=\"col-xs-8\">" . campo("empresa_extension", "", 'text', $row["empresa_extension"], 'class="form-control" ', '', '', 3, 1) . "</div></div>";
		}
		if ($campo_unico == "" or $campo_unico == 26 or $mostrar_todo) {
			echo "<hr><div class=\"row\"><div class=\"col-xs-8\">" . "Salario" . "</div></div>";
			echo "<div class=\"row\"><div class=\"col-xs-8\">" . campo("empresa_salario", "", 'text', $row["empresa_salario"], 'class="form-control" ', '', '', 3, 4) . "</div><div class=\"col-xs-4\">" . boton_verificar(26, $solicitud_id, false, $row["usuario_alta"], $asignados, $campo_unico) . "</div></div>";
		}
		if ($campo_unico == "" or $campo_unico == 27 or $mostrar_todo) {
			echo "<hr><div class=\"row\"><div class=\"col-xs-8\">" . "Ortos ingresos" . "</div></div>";
			echo "<div class=\"row\"><div class=\"col-xs-8\">" . campo("empresa_salario_otro", "", 'text', $row["empresa_salario_otro"], 'class="form-control" ', '', '', 3, 4) . "</div><div class=\"col-xs-4\">" . boton_verificar(27, $solicitud_id, false, $row["usuario_alta"], $asignados, $campo_unico) . "</div></div>";
		}
		if ($campo_unico == "" or $campo_unico == 28 or $mostrar_todo) {
			echo "<hr><div class=\"row\"><div class=\"col-xs-8\">" . "Por concepto" . "</div></div>";
			echo "<div class=\"row\"><div class=\"col-xs-8\">" . campo("empresa_salario_otro_tipo", "", 'select', valores_combobox_texto('<option value="">Seleccione</option><option value="REMESAS">REMESAS</option><option value="ALQUILERES">ALQUILERES</option><option value="OTROS">OTROS</option>', $row["empresa_salario_otro_tipo"]), 'class="form-control" ', '', '', 3, 3) . "</div><div class=\"col-xs-4\">" . boton_verificar(28, $solicitud_id, false, $row["usuario_alta"], $asignados, $campo_unico) . "</div></div>";
		}
		if ($campo_unico == "" or $campo_unico == 29 or $mostrar_todo) {
			echo "<hr><div class=\"row\"><div class=\"col-xs-8\">" . "Ocupacion o Cargo" . "</div></div>";
			echo "<div class=\"row\"><div class=\"col-xs-8\">" . campo("empresa_puesto", "", 'text', $row["empresa_puesto"], 'class="form-control" ', '', '', 3, 4) . "</div><div class=\"col-xs-4\">" . boton_verificar(29, $solicitud_id, false, $row["usuario_alta"], $asignados, $campo_unico) . "</div></div>";
		}
		if ($campo_unico == "" or $campo_unico == 30 or $mostrar_todo) {
			echo "<hr><div class=\"row\"><div class=\"col-xs-8\">" . "Fecha Ingreso" . "</div></div>";
			echo "<div class=\"row\"><div class=\"col-xs-8\">" . campo("empresa_fecha_ingreso", "", 'date', $row["empresa_fecha_ingreso"], 'class="form-control" ', '', '', 3, 3) . "</div><div class=\"col-xs-4\">" . boton_verificar(30, $solicitud_id, false, $row["usuario_alta"], $asignados, $campo_unico) . "</div></div>";
		}
		if ($campo_unico == "" or $mostrar_todo) {
			echo "<hr>";
			echo campo("requiere_verificacion_campo_laboral", "Requiere verificacion de Campo", 'select', valores_combobox_texto('<option value="">Seleccione</option><option value="SI">SI</option><option value="NO">NO</option>', $row["requiere_verificacion_campo_laboral"]), 'class="form-control" ', '', '', 3, 3) . "</div></div>";
		}

		echo '</div>';
		echo "<hr>";



		if ($campo_unico == "" or $mostrar_todo) {

			$sql =
				"SELECT id,doc1, doc2, doc3, doc4, doc5, doc6, doc7, doc8, doc9, doc10, doc11, doc12
    			FROM prestamo WHERE id = $solicitud_id";

			$result = $conn->query($sql);


			if ($result->num_rows > 0) {
				$row = $result->fetch_assoc();

				if ($mostrar_todo == true) {
					//desplegar documentos adjuntos
					echo incrustar_objeto("CONSTANCIA DE TRABAJO", $row["doc6"]);
				} else {
					echo '<div class="row">';
					echo "<hr>";
					echo campo("doc6", "CONSTANCIA DE TRABAJO", 'uploadlink', $row["doc6"], 'class="form-control" ', $row["id"]);
					echo "<hr>";

					echo '</div>';
				}
			} else {
				echo mensaje("No se encontraron registros", "info");
				exit;
			}
		}
	}

	if ($cod_status == TipoEtapas::ETAPA_VERIFICACION_CAMPO || $mostrar_todo) { //VERIFICAR de campo

		$sql = "SELECT * FROM prestamo WHERE id = $solicitud_id";

		$result = $conn->query($sql);


		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();
			// error_reporting(0);

			if (tiene_permiso(PermisosModulos::PERMISOS_PERSONAL_CREDITO)) {
				echo boton_verificar(31, $solicitud_id, false, $row["usuario_alta"], $asignados, $campo_unico);
				echo '  <hr>';
			}
			if ($campo_unico == "" or $campo_unico == 31 or $mostrar_todo) {

				echo '<div class="row">';
				echo "<H4>Verificacion Domiciciaria</H4>";
				echo "<hr>";
				echo $row["nombres"] . " " . $row["apellidos"] . "<br>";
				echo $row["direccion"] . " " . $row["ciudad"] . " " . $row["departamento"] . $row["pais"] . " " . "<br>";
				echo $row["direccion_referencia"] . "<br>";
				echo $row["telefono"] . " " . $row["telefono2"] . " " . $row["celular"] . "<br>";
				echo "<hr>";
				if ($mostrar_todo == true) {
					//desplegar documentos adjuntos
					echo incrustar_objeto("CROQUIS", $row["doc8"]);
				} else {
					echo campo("doc8", "CROQUIS", 'uploadlink', $row["doc8"], 'class="form-control" ', $row["id"]);
				}
				echo "<hr>";

				echo campo("vivienda", "Vivienda", 'select', valores_combobox_texto('<option value="">Seleccione</option><option value="Propia">Propia</option><option value="Alquilada">Alquilada</option><option value="Familiar">Familiar</option><option value="Empresa">Empresa</option>', $row["vivienda"]), 'class="form-control" ', '', '', 3, 3);

				echo campo("vivienda_tiempo", "Tiempo de residir en a&ntilde;os", 'text', $row["vivienda_tiempo"], 'class="form-control" ', '', '', 3, 3);

				echo campo("vivienda_condicion", "Condiciones de Construccion", 'select', valores_combobox_texto('<option value="">Seleccione</option><option value="Excelente">Excelente</option><option value="Buena">Buena</option><option value="Regular">Regular</option><option value="Malas">Malas</option>', $row["vivienda_condicion"]), 'class="form-control" ', '', '', 3, 3);


				echo campo("vivienda_tipo", "Tipo de Domicilio", 'select', valores_combobox_texto('<option value="">Seleccione</option><option value="Casa">Casa</option><option value="Apartamentos">Apartamentos</option><option value="Cuartearia">Cuartearia</option><option value="Otros">Otros</option>', $row["vivienda_tipo"]), 'class="form-control" ', '', '', 3, 3);
				echo campo("vivienda_construccion", "Tipo Construccion", 'select', valores_combobox_texto('<option value="">Seleccione</option><option value="Bloque">Bloque</option><option value="Ladrillo">Ladrillo</option><option value="Madera">Madera</option><option value="Adobe">Adobe</option><option value="Lamina">Lamina</option><option value="Carton">Carton</option><option value="Otros">Otros</option>', $row["vivienda_construccion"]), 'class="form-control" ', '', '', 3, 3);
				echo campo("vivienda_construccion_obs", "Comentarios", 'textarea', $row["vivienda_construccion_obs"], 'class="form-control" ', '', '', 3, 7);


				echo campo("vivienda_vecino1", "Referencias Vecino 1", 'select', valores_combobox_texto('<option value="">Seleccione</option><option value="Excelentes">Excelentes</option><option value="Buenas">Buenas</option><option value="Regulares">Regulares</option><option value="Malas">Malas</option><option value="No los conocen">No los conocen</option>', $row["vivienda_vecino1"]), 'class="form-control" ', '', '', 3, 4);
				echo campo("vivienda_vecino1_obs", "Comentarios Vecino 1", 'textarea', $row["vivienda_vecino1_obs"], 'class="form-control" ', '', '', 3, 7);

				echo campo("vivienda_vecino2", "Referencias Vecino 2", 'select', valores_combobox_texto('<option value="">Seleccione</option><option value="Excelentes">Excelentes</option><option value="Buenas">Buenas</option><option value="Regulares">Regulares</option><option value="Malas">Malas</option><option value="No los conocen">No los conocen</option>', $row["vivienda_vecino2"]), 'class="form-control" ', '', '', 3, 4);
				echo campo("vivienda_vecino2_obs", "Comentarios Vecino 2", 'textarea', $row["vivienda_vecino2_obs"], 'class="form-control" ', '', '', 3, 7);
				echo "<hr>";


				echo campo("vivienda_comentarios", "Comentarios Adicionales", 'textarea', $row["vivienda_vecino1_obs"], 'class="form-control" ', '', '', 3, 7);
				echo campo("vivienda_recomienda", "Recomendacion", 'select', valores_combobox_texto('<option value="">Seleccione</option><option value="Aprobar">Aprobar</option><option value="Condicionar">Condicionar</option><option value="Rechazar">Rechazar</option>', $row["vivienda_recomienda"]), 'class="form-control" ', '', '', 3, 4);
				echo "<hr>";

				echo campo("direccion_gps", "<a href=\"#\" onclick=\"ubicacion_gps() ; return false;\"   class=\"btn btn-info\"><span class=\"glyphicon glyphicon-map-marker\" aria-hidden=\"true\"></span> Ubicacion GPS</a> <br><br> <a href=\"#\" onclick=\"abrir_mapa($('#direccion_gps').val()) ; return false;\"   class=\"btn btn-default\"><span class=\"glyphicon glyphicon-globe\" aria-hidden=\"true\"></span> Ver Mapa</a>", 'text', $row["direccion_gps"], 'class="form-control" ', '', '', 3, 7);

				echo "<div id=\"GeoAPI\" style=\"display: none;\"></div>";


				if ($mostrar_todo) {
					//desplegar documentos adjuntos
					echo incrustar_objeto("Foto", $row["doc_foto1"]);
					echo incrustar_objeto("Foto", $row["doc_foto2"]);
					echo incrustar_objeto("Foto", $row["doc_foto3"]);
					echo incrustar_objeto("Foto", $row["doc_foto4"]);
					echo incrustar_objeto("Foto", $row["doc_foto5"]);
					echo incrustar_objeto("Foto", $row["doc_foto6"]);
				} else {
					echo "<hr>";
					echo campo_upload("doc_foto1", "FOTO 1", 'uploadlink', $row["doc_foto1"], 'class="form-control" ', $row["id"], 0, 12, "SI");
					echo "<hr>";
					echo campo_upload("doc_foto2", "FOTO 2", 'uploadlink', $row["doc_foto2"], 'class="form-control" ', $row["id"], 0, 12, "SI");
					echo "<hr>";
					echo campo_upload("doc_foto3", "FOTO 3", 'uploadlink', $row["doc_foto3"], 'class="form-control" ', $row["id"], 0, 12, "SI");
					echo "<hr>";
					echo campo_upload("doc_foto4", "FOTO 4", 'uploadlink', $row["doc_foto4"], 'class="form-control" ', $row["id"], 0, 12, "SI");
					echo "<hr>";
					echo campo_upload("doc_foto5", "FOTO 5", 'uploadlink', $row["doc_foto5"], 'class="form-control" ', $row["id"], 0, 12, "SI");
					echo "<hr>";
					echo campo_upload("doc_foto6", "FOTO 6", 'uploadlink', $row["doc_foto6"], 'class="form-control" ', $row["id"], 0, 12, "SI");
					echo "<hr>";
				}

				echo "<H4>Verificacion Laboral</H4>";
				echo "<hr>";
				echo "" . $row["nombres"] . " " . $row["apellidos"] . "<br>";

				echo "" . $row["empresa"] . "<br>";
				echo "" . $row["empresa_direccion"] . "<br>";
				echo "" . $row["empresa_telefono"] . " " . $row["empresa_telefono2"] . "<br>";
				echo "<hr>";
				//     echo campo("direccion","Direccion",'text',$row["direccion"],'class="form-control" ','','',3,7);
				//   echo campo("direccion","Nombre del Trabajo o Negocio",'text',$row["direccion"],'class="form-control" ','','',3,7);
				echo campo("negocio_rotulo", "Tiene Rotulo", 'select', valores_combobox_texto('<option value="">Seleccione</option><option value="Si">Si</option><option value="No">No</option>', $row["negocio_rotulo"]), 'class="form-control" ', '', '', 3, 3);

				// echo  campo("rotulo","Relacion del Trabajo",'select',valores_combobox_texto('<option value="">Seleccione</option><option value="Propio">Propio</option><option value="Empleado">Empleado</option>',$row["rotulo"]),'class="form-control" ','','',3,3);
				if ($row["empresa_tipo_empleo"] == 'ASALARIADO') {
					echo "<hr>";
					echo "<strong>Empleado</strong><br>";
					echo campo("empleado_patrono", "Patrono", 'text', $row["empleado_patrono"], 'class="form-control" ', '', '', 3, 7);
					echo campo("empleado_antiguedad", "Antiguedad", 'text', $row["empleado_antiguedad"], 'class="form-control" ', '', '', 3, 3);
					echo campo("empleado_ingreso", "Salario Mensual L.", 'text', $row["empleado_ingreso"], 'class="form-control" ', '', '', 3, 3);

					$items = [
						["value" => "", "label" => "Seleccione"],
						["value" => "SALARIO", "label" => "SALARIO"],
						["value" => "JUBILACION O PENSION", "label" => "JUBILACIÓN O PENSIÓN"],
						["value" => "VENTAS BIENES MUEBLES/INMUEBLES", "label" => "VENTAS BIENES MUEBLES/INMUEBLES"],
						["value" => "REMESAS", "label" => "REMESAS"],
						["value" => "ALQUILERES", "label" => "ALQUILERES"],
						["value" => "NEGOCIO PROPIO", "label" => "NEGOCIO PROPIO"],
						["value" => "AHORROS", "label" => "AHORROS"],
						["value" => "HERENCIA", "label" => "HERENCIA"],
						["value" => "PREMIOS DE LOTERIA", "label" => "PREMIOS DE LOTERÍA"],
						["value" => "PRESTAMO INTERNO", "label" => "PRÉSTAMO INTERNO"],
						["value" => "PRESTAMO EXTERNO", "label" => "PRÉSTAMO EXTERNO"],
						["value" => "OTROS", "label" => "OTROS"],
					];
					$option_values = implode(array_map(function ($item) {
						return "<option value='{$item["value"]}'>{$item["label"]}</option>";
					}, $items));
					$default_value = $row["empresa_salario_tipo"] ? $row["empresa_salario_tipo"] : 'SALARIO';

					echo campo(
						"empresa_salario_tipo",
						"Por Concepto",
						'select',
						valores_combobox_texto($option_values, $default_value),
						'class="form-control"',
						'',
						'',
						3,
						3
					);
					echo campo("empresa_salario_otro", "Otros Ingresos", 'text', $row["empresa_salario_otro"], 'class="form-control" ', '', '', 3, 3);
					echo campo("empresa_salario_otro_tipo", "Por concepto", 'text', $row["empresa_salario_otro_tipo"], 'class="form-control" ', '', '', 3, 3);

					echo campo("empleado_tipo", "Tipo Empleo", 'select', valores_combobox_texto('<option value="">Seleccione</option><option value="Permanente">Permanente</option><option value="Temporal">Temporal</option><option value="Contrato">Contrato</option><option value="Otro">Otro</option>', $row["empleado_tipo"]), 'class="form-control" ', '', '', 3, 3);
					echo campo("empleado_tipo_empresa", "Tipo de empresa", 'select', valores_combobox_texto('<option value="">Seleccione</option><option value="Servicios">Servicios</option><option value="Industria">Industria</option><option value="Comercio">Comercio</option><option value="Gobierno">Gobierno</option>', $row["empleado_tipo_empresa"]), 'class="form-control" ', '', '', 3, 3);
					echo campo("empleado_prestamo", "Prestamos con la Empresa L.", 'text', $row["empleado_prestamo"], 'class="form-control" ', '', '', 3, 3);
					echo campo("empleado_prestamo_telefono", "Telefono", 'text', $row["empleado_prestamo_telefono"], 'class="form-control" ', '', '', 3, 3);

					echo campo("empleado_referencia", "Referencia Compa&ntilde;eros o vecinos", 'select', valores_combobox_texto('<option value="">Seleccione</option><option value="Excelentes">Excelentes</option><option value="Buenas">Buenas</option><option value="Regulares">Regulares</option><option value="Malas">Malas</option><option value="No los conocen">No los conocen</option>', $row["empleado_referencia"]), 'class="form-control" ', '', '', 3, 4);
					echo campo("empleado_obs", "Comentarios", 'textarea', $row["empleado_obs"], 'class="form-control" ', '', '', 3, 7);
				} else {


					echo "<hr>";
					echo "<strong>Negocio Propio</strong><br>";
					echo campo("negocio_tipo", "Negocio Propio", 'select', valores_combobox_texto('<option value="">Seleccione</option><option value="Propietario">Propietario</option><option value="Socio">Socio</option><option value="Familiar Informal">Familiar Informal</option>', $row["negocio_tipo"]), 'class="form-control" ', '', '', 3, 3);
					echo campo("negocio_permiso", "Permiso de Operacion", 'select', valores_combobox_texto('<option value="">Seleccione</option><option value="Si">Si</option><option value="No">No</option>', $row["negocio_permiso"]), 'class="form-control" ', '', '', 3, 3);
					echo campo("negocio_tamano", "Tama&ntilde;o", 'select', valores_combobox_texto('<option value="">Seleccione</option><option value="Grande">Grande</option><option value="Mediano">Mediano</option><option value="Peque&ntilde;o">Peque&ntilde;o</option>', $row["negocio_tamano"]), 'class="form-control" ', '', '', 3, 3);
					echo campo("negocio_tiempo", "Tiempo de Operar", 'text', $row["negocio_tiempo"], 'class="form-control" ', '', '', 3, 3);

					echo campo("negocio_condicion", "Condiciones de Construccion", 'select', valores_combobox_texto('<option value="">Seleccione</option><option value="Excelente">Excelente</option><option value="Buena">Buena</option><option value="Regular">Regular</option><option value="Malas">Malas</option>', $row["negocio_condicion"]), 'class="form-control" ', '', '', 3, 3);
					echo campo("negocio_afluencia", "Afluencia de clientes", 'select', valores_combobox_texto('<option value="">Seleccione</option><option value="Alta">Alta</option><option value="Media">Media</option><option value="Baja">Baja</option><option value="Otros">Otros</option>', $row["negocio_afluencia"]), 'class="form-control" ', '', '', 3, 3);
					echo campo("negocio_empleados", "Numero de empleados", 'select', valores_combobox_texto('<option value="">Seleccione</option><option value="1-2">1-2</option><option value="3-5">3-5</option><option value="5-10">5-10</option><option value="mas de 10">mas de 10</option>', $row["negocio_empleados"]), 'class="form-control" ', '', '', 3, 3);
					echo campo("negocio_ubicacion", "Zona de ubicacion", 'select', valores_combobox_texto('<option value="">Seleccione</option><option value="En Domicilio">En Domicilio</option><option value="Mercado">Mercado</option><option value="Centro Comercial">Centro Comercial</option><option value="Ambulante">Ambulante</option><option value="Industria">Industria</option><option value="Indistinta">Indistinta</option><option value="Especifique">Especifique</option>', $row["negocio_ubicacion"]), 'class="form-control" ', '', '', 3, 3);

					echo campo("negocio_ingreso", "Ingreso Mensual L.", 'text', $row["negocio_ingreso"], 'class="form-control" ', '', '', 3, 3);

					echo campo("negocio_referencia", "Referencias Vecino 2", 'select', valores_combobox_texto('<option value="">Seleccione</option><option value="Excelentes">Excelentes</option><option value="Buenas">Buenas</option><option value="Regulares">Regulares</option><option value="Malas">Malas</option><option value="No los conocen">No los conocen</option>', $row["negocio_referencia"]), 'class="form-control" ', '', '', 3, 4);
					echo campo("negocio_obs", "Comentarios", 'textarea', $row["negocio_obs"], 'class="form-control" ', '', '', 3, 7);
				}

				if ($mostrar_todo == true) {
					//desplegar documentos adjuntos
					echo incrustar_objeto("Foto", $row["doc_foto7"]);
					echo incrustar_objeto("Foto", $row["doc_foto8"]);
					echo incrustar_objeto("Foto", $row["doc_foto9"]);
					echo incrustar_objeto("Foto", $row["doc_foto10"]);
					echo incrustar_objeto("Foto", $row["doc_foto11"]);
					echo incrustar_objeto("Foto", $row["doc_foto12"]);
				} else {
					echo "<hr>";
					echo campo_upload("doc_foto7", "FOTO 1", 'uploadlink', $row["doc_foto7"], 'class="form-control" ', $row["id"], 0, 12, "SI");
					echo "<hr>";
					echo campo_upload("doc_foto8", "FOTO 2", 'uploadlink', $row["doc_foto8"], 'class="form-control" ', $row["id"], 0, 12, "SI");
					echo "<hr>";
					echo campo_upload("doc_foto9", "FOTO 3", 'uploadlink', $row["doc_foto9"], 'class="form-control" ', $row["id"], 0, 12, "SI");
					echo "<hr>";
					echo campo_upload("doc_foto10", "FOTO 4", 'uploadlink', $row["doc_foto10"], 'class="form-control" ', $row["id"], 0, 12, "SI");
					echo "<hr>";
					echo campo_upload("doc_foto11", "FOTO 5", 'uploadlink', $row["doc_foto11"], 'class="form-control" ', $row["id"], 0, 12, "SI");
					echo "<hr>";
					echo campo_upload("doc_foto12", "FOTO 6", 'uploadlink', $row["doc_foto12"], 'class="form-control" ', $row["id"], 0, 12, "SI");
					echo "<hr>";
				}

				echo '</div>';
				if ($campo_unico <> "" && !$mostrar_todo)  //que el vendedor tambien la pueda modificar despues
				{
					echo "<hr>";
					echo '<a id="Guardar' . $forma . '" href="#" class="btn btn-primary" onclick="procesar_datos_gestion(\'creditos_gestion.php?a=5g6&s=1&cid=' . $solicitud_id . '\',' . $forma . '); return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar (Parcial)</a>';
					echo '<div id="respuesta' . $forma . '"> </div>';
				}
			}

			if (tiene_permiso(PermisosModulos::PERMISOS_PERSONAL_CREDITO) && !$mostrar_todo)  //que el vendedor tambien la pueda modificar
			{
				echo "<hr>";
				echo '<a id="Guardar' . $forma . '" href="#" class="btn btn-primary" onclick="procesar_datos_gestion(\'creditos_gestion.php?a=5g6&s=1&cid=' . $solicitud_id . '\',' . $forma . '); return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar</a>';
				echo '<div id="respuesta' . $forma . '"> </div>';
			}
		} else {
			echo mensaje("No se encontraron registros", "info");
			exit;
		}
	}

	if ($cod_status == TipoEtapas::ETAPA_CALCULO_FINANCIERO) { //CALCULO FINANCIERO

		$sql = "SELECT * FROM prestamo WHERE id = {$solicitud_id}";

		$result = $conn->query($sql);

		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();

			if ($campo_unico == "" or $mostrar_todo) {

				if ($row["aprobado_gerencia_usuario"] <> "") {
					echo boton_verificar(701, $solicitud_id, false, '', $asignados, $campo_unico);
				}

				echo '<div class="row">';
				echo '<div><div><div class="form">';

				echo "<hr>";
				echo campo("monto_prestamo", "Valor Motocicleta", 'text', $row["monto_prestamo"], 'class="form-control" onchange="calculos_financieros4(); " ', '', '', 3, 3);
				echo campo("monto_seguro", "Valor del Seguro", 'text', $row["monto_seguro"], 'class="form-control"  onchange="calculos_financieros4(); " ', '', '', 3, 3);
				echo campo("monto_prima", "Prima", 'text', $row["monto_prima"], 'class="form-control"  onchange="calculos_financieros4();"', '', '', 3, 3);

				echo '<div id="amort">';
				echo campo("monto_financiar", "Total Financiar", 'text', $row["monto_financiar"], 'class=" form-control"  readonly', '', '', 3, 3);

				$option_values = convertir_array_dropdown($lista_plazos, $row["plazo"], true, "value", "value");
				// echo campo("plazo", "Plazo", 'select', $option_values, 'class=" form-control" onchange="calculos_financieros4(); "', '', '', 3, 2);
				echo campo("plazo", "Plazo", 'select', $option_values, 'class="form-control" onchange="calculos_financieros4()"', '', '', 3, 2);

				$default_value = !empty($row["cierre_interes_mensual"]) ? $row["cierre_interes_mensual"] : "3.98";
				$option_values = convertir_array_dropdown($lista_perfiles, $default_value, true, "value", "label", "id");
				echo campo("cierre_interes_mensual", "Perfil", 'select', $option_values, 'class="form-control" onchange="perfil_interes()"', '', '', 3, 3);

				// echo campo("tasa", "Tasa", 'text', $row["tasa"], 'class=" form-control"  onchange="calculos_financieros4(); "', '', '', 3, 2); // AQUÍ SE AGREGA LA TASA
				echo campo("tasa", "Tasa", 'text', $row["tasa"], 'class="form-control" onchange="calculos_financieros4();"', '', '', 3, 2);

				echo campo("cierre_interes_moratorio", "", 'text', ($row["cierre_interes_moratorio"] != 0.00 ? $row["cierre_interes_moratorio"] : 3.9), 'class=" form-control"  onchange="" style="display: none;"', '', '', 3, 2);
				echo '</div>';

				echo campo("tipo_perfil", "", 'text', ($row["tipo_perfil"] ? $row["tipo_perfil"] : "Perfil Prima Normal"), 'class=" form-control"  onchange="" style="display: none;"', '', '', 3, 2);
				echo '</div>';

				echo "<hr>";
				echo "<h4>CALCULO RELACION CUOTA INGRESO</h4>";
				echo campo("cuota", "Cuota Mensual", 'text', $row["cuota"], 'class="form-control" onchange="calculos_financieros2();" readonly', '', '', 3, 2);
				echo campo("cuota_promocion_octubre", "Cuota Promoción Octubre", 'text', $row["cuota_promocion_octubre"], 'class="form-control" id="cuota_promocion" onchange="calculos_financieros2();" readonly', '', '', 3, 2);


				echo campo("endeuda_sueldo_requerido", "Sueldo Requerido", 'text', $row["endeuda_sueldo_requerido"], 'class="form-control "  readonly', '', '', 3, 3);

				echo "<hr>";
				echo "<h4>NIVEL DE ENDEUDAMIENTO</h4>";

				echo campo("endeuda_sueldo", "Sueldo Neto", 'text', $row["endeuda_sueldo"], 'class="form-control"  onchange="calculos_financieros1();  " ', '', '', 3, 3);
				echo campo("endeuda_tarjeta", "Tarjeta de   Credito", 'text', $row["endeuda_tarjeta"], 'class="form-control" onchange="calculos_financieros1();  " ', '', '', 3, 3);
				echo campo("endeuda_prestamo", "Prestamo", 'text', $row["endeuda_prestamo"], 'class="form-control" onchange="calculos_financieros1();  " ', '', '', 3, 3);
				echo campo("endeuda_cooperativa", "Cooperativa", 'text', $row["endeuda_cooperativa"], 'class="form-control" onchange="calculos_financieros1();  " ', '', '', 3, 3);
				echo campo("endeuda_movesa", "Prestamo Movesa", 'text', $row["cuota"], 'class="form-control"  readonly', '', '', 3, 3); // AQUI

				echo campo("endeuda_otros", "Otros", 'text', $row["endeuda_otros"], 'class="form-control" onchange="calculos_financieros1();  " ', '', '', 3, 3);

				echo campo("endeuda_total", "Total Obligaciones", 'text', $row["endeuda_total"], 'class="form-control"  readonly', '', '', 3, 3);
				echo campo("endeuda_nivel", "Nivel de Endeudamiento", 'text', $row["endeuda_nivel"], 'class="form-control"  readonly', '', '', 3, 3);


				if (tiene_permiso(PermisosModulos::PERMISOS_PERSONAL_CREDITO) and !$mostrar_todo) {
					echo "<hr>";
					echo '<a id="Guardar' . $forma . '" href="#" class="btn btn-primary" onclick="procesar_datos_gestion(\'creditos_gestion.php?a=5b1ss&s=1&cid=' . $solicitud_id . '\',' . $forma . '); return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar</a>';
					if (es_nulo($row["aprobado_gerencia_usuario"])) {
						echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a id="AutorizarGes' . $forma . '" href="#" class="btn btn-info" onclick="procesar_datos_gestion(\'creditos_gestion.php?a=5g7&s=1&cid=' . $solicitud_id . '\',' . $forma . '); $(this).hide(); return false;"> Solicitar Aprobacion de Gerente</a>';
					}

					echo '<div id="respuesta' . $forma . '"> </div>';
				}
			}

			if ($mostrar_todo and tiene_permiso(PermisosModulos::PERMISOS_MARCA_AUTORIZADO_GERENCIA)) {

				echo '<br><div class="well">';
				echo '<div class="row"><div class="col-xs-12"> <form id="forma' . $forma . '" class="form-horizontal" autocomplete="off" >';

				echo campo("texto_responde", "Respuesta", 'textarea', '', 'class="form-control" rows="5"', '', '', 2, 9);
				echo '<a id="Guardar' . $forma . '" href="#" class="btn btn-primary" onclick="procesar_datos_gestion(\'creditos_gestion.php?a=5g7&s=2&geid=' . $_REQUEST['geid'] . '&cid=' . $solicitud_id . '\',' . $forma . '); return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar</a>';

				echo '<div id="respuesta' . $forma . '"> </div>';

				echo " </form></div></div></div><hr>";
			}
		} else {
			echo mensaje("No se encontraron registros", "info");
			exit;
		}
	}

	if ($cod_status == TipoEtapas::ETAPA_CONDICION_APROBACION) { //CONDICIONES DE APROBACION

		$sql = "SELECT * FROM prestamo WHERE id = {$solicitud_id}";




		$result = $conn->query($sql);

		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();

			echo '<div class="row">';

			if (isset($asignados[801])) {
				echo boton_verificar(801, $solicitud_id, false, $row["usuario_alta"], $asignados, $campo_unico);
				// echo "<hr>";
			}

			if ($campo_unico == "801" or $mostrar_todo) {
				echo "<h4>Notificacion de Condiciones de aprobacion</h4>";
				echo boton_verificar(801, $solicitud_id, false, '', $asignados, $campo_unico);

				echo "<hr>";

				echo campo("monto_prestamo", "Valor Motocicleta", 'label', $row["monto_prestamo"], 'class="form-control" onchange="calculo_financiar_total();" ', '', '', 3, 3);
				echo campo("monto_seguro", "Valor del Seguro", 'label', $row["monto_seguro"], 'class="form-control" onchange="calculo_financiar_total();" ', '', '', 3, 3);
				echo campo("monto_prima", "Prima", 'label', $row["monto_prima"], 'class="form-control" onchange="calculo_financiar_total();" ', '', '', 3, 3);

				echo campo("monto_financiar", "Total Financiar", 'label', $row["monto_financiar"], 'class="form-control" readonly', '', '', 3, 3);

				echo campo("plazo", "Plazo", 'label', $row["plazo"], 'class="form-control"', '', '', 3, 2);
				echo campo("tasa", "Tasa", 'label', $row["tasa"], 'class="form-control"', '', '', 3, 2);
				echo campo("cuota", "Letra", 'label', $row["cuota"], 'class="form-control"', '', '', 3, 2);
			}

			if ($campo_unico == "") {
				echo "<hr>";

				echo campo("monto_prestamo", "Valor Motocicleta", 'text', $row["monto_prestamo"], 'class="form-control" onchange="calculo_financiar_total();" ', '', '', 3, 3);
				echo campo("monto_seguro", "Valor del Seguro", 'text', $row["monto_seguro"], 'class="form-control" onchange="calculo_financiar_total();" ', '', '', 3, 3);
				echo campo("monto_prima", "Prima", 'text', $row["monto_prima"], 'class="form-control" onchange="calculo_financiar_total();" ', '', '', 3, 3);

				// Agrega campos ocultos
				echo '<input type="hidden" id="gastos_administrativos" value="' . floatval($row["gastos_administrativos"]) . '">';
				echo '<input type="hidden" id="costo_rtn" value="' . floatval($row["costo_rtn"]) . '">';

				echo '<div id="amort">';
				echo campo("monto_financiar", "Total Financiar", 'text', $row["monto_financiar"], 'class="form-control" readonly', '', '', 3, 3);

				$option_values = convertir_array_dropdown($lista_plazos, $row["plazo"], true, "value", "value");
				echo campo("plazo", "Plazo", 'select', $option_values, 'class="form-control" onchange="calculo_financiar_total();"', '', '', 3, 2);
				echo campo("tasa", "Tasa", 'text', $row["tasa"], 'class="form-control" onchange="calculo_financiar_total();"', '', '', 3, 2);
				echo '</div>';

				echo campo("cuota", "Cuota Mensual", 'text', $row["cuota"], 'class="form-control" onchange="calculos_financieros2();" readonly', '', '', 3, 2);

				echo campo("cuota_promocion_octubre", "Cuota Promoción Octubre", 'text', $row["cuota_promocion_octubre"], 'class="form-control" readonly id="cuota_promocion_octubre"', '', '', 3, 2);


				echo '<div id="endeuda2" style="display:none">';
				echo campo("endeuda_sueldo_requerido", "Sueldo Requerido", 'text', $row["endeuda_sueldo_requerido"], 'class="form-control" readonly', '', '', 3, 3);
				echo campo("endeuda_sueldo", "Sueldo Neto", 'text', $row["endeuda_sueldo"], 'class="form-control" onchange="calculos_financieros1();"', '', '', 3, 3);
				echo campo("endeuda_tarjeta", "Tarjeta de Credito", 'text', $row["endeuda_tarjeta"], 'class="form-control" onchange="calculos_financieros1();"', '', '', 3, 3);
				echo campo("endeuda_prestamo", "Prestamo", 'text', $row["endeuda_prestamo"], 'class="form-control" onchange="calculos_financieros1();"', '', '', 3, 3);
				echo campo("endeuda_cooperativa", "Cooperativa", 'text', $row["endeuda_cooperativa"], 'class="form-control" onchange="calculos_financieros1();"', '', '', 3, 3);
				echo campo("endeuda_movesa", "Prestamo Movesa", 'text', $row["cuota"], 'class="form-control" readonly', '', '', 3, 3);
				echo campo("endeuda_otros", "Otros", 'text', $row["endeuda_otros"], 'class="form-control" onchange="calculos_financieros1();"', '', '', 3, 3);
				echo campo("endeuda_total", "Total Obligaciones", 'text', $row["endeuda_total"], 'class="form-control" readonly', '', '', 3, 3);
				echo campo("endeuda_nivel", "Nivel de Endeudamiento", 'text', $row["endeuda_nivel"], 'class="form-control" readonly', '', '', 3, 3);
				echo '<div id="amSchedule"></div><div class="clear"></div>';


				echo '</div>';

				echo
					'<script>
            		$(document).ready(function(){
						$(function () {
							showValues();
							 if (typeof window.calcularCuotaPromocionOctubre === "function") {
								window.calcularCuotaPromocionOctubre();
							}
						})
					});
				</script>';

				echo '<script>
				function convertir_num(valor) {
					return parseFloat(valor.toString().replace(",", "").trim()) || 0;
				}

				function calculo_financiar_total() {
					let monto = convertir_num($("#monto_prestamo").val());
					let seguro = convertir_num($("#monto_seguro").val());
					let prima = convertir_num($("#monto_prima").val());
					let rtn = convertir_num($("#costo_rtn").val());
					let gastos = convertir_num($("#gastos_admin").val());

					let total = monto + seguro + rtn + gastos - prima;
					$("#monto_financiar").val(total.toFixed(2));
				}
				</script>';



				if (tiene_permiso(PermisosModulos::PERMISOS_PERSONAL_CREDITO)) {
					echo "<hr>";
					echo '<a id="Guardar' . $forma . '" href="#" class="btn btn-primary" onclick="procesar_datos_gestion(\'creditos_gestion.php?a=5g8&s=1&cid=' . $solicitud_id . '\',' . $forma . '); return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar y Notificar a Vendedor</a>';
					echo '<div id="respuesta' . $forma . '"> </div>';
				}
			}
			echo "<hr>";
			echo '</div>';
		} else {
			echo mensaje("No se encontraron registros", "info");
			exit;
		}
	}

	if ($cod_status == TipoEtapas::ETAPA_IMPRESION_DOCUMENTOS) { //IMPRESION DE DOCUMENTOS LEGALES

		$sql = "SELECT * FROM prestamo WHERE id = $solicitud_id";

		$result = $conn->query($sql);

		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();

			echo '<div class="row">';
			echo "<hr>";
			// imprimir contrato

			if ($row["cierre_contrato"] <> 0) {
				echo campo("cierre_contrato", "No. Contrato", 'label', $row["cierre_contrato"], 'class="form-control"  ', '', '', 3, 4);

				if (tiene_permiso(PermisosModulos::PERMISOS_IMPRIMIR_CONTRATO)) {
					echo '<a id="btnimp_1' . $forma . '" href="#" class="btn btn-default" onclick="imprimir_contratos(' . $solicitud_id . '); return false;"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Documentos</a>';
					echo '<a id="btnimp_1' . $forma . '" href="#" style="display: none;" class="btn btn-default" onclick="imprimir_relacionci(' . $solicitud_id . '); return false;"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Relacion CI</a>';
					echo '<a id="btnimp_1' . $forma . '" href="#" class="btn btn-default" onclick="imprimir_carta_poder(' . $solicitud_id . '); return false;"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Carta Poder</a>';
					echo '<a id="btnimp_1' . $forma . '" href="#" class="btn btn-default" onclick="imprimir_autorizacion_decomiso(' . $solicitud_id . '); return false;"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Autorización de Decomiso</a>';
					echo '<a id="btnimp_1' . $forma . '" href="#" class="btn btn-default" onclick="imprimir_forma_pago(' . $solicitud_id . '); return false;"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Forma de Pago</a>';
				} else {
					echo Mensaje("No tiene permisos para Imprimir Contrato", "info");
				}
				echo "<hr>";
			}

			if (isset($asignados[901])) {
				echo boton_verificar(901, $solicitud_id, false, $row["usuario_alta"], $asignados, $campo_unico);
				echo "<hr>";
			}


			if ($campo_unico == "" or $campo_unico == 902) {

				echo '<div class=\"row\"><div id="motospanel"></div>';
				echo '<a href="#" class="btn btn-default" onclick="actualizarbox(\'motospanel\',\'get.php?a=11&sub=1&sb=&creditos=1\'); return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Seleccionar Serie</a><br><br>';
				echo "</div>";

				echo "<div class=\"row\"><div class=\"col-xs-8\">" . "<h4>Datos de la Moto</h4>" . "</div></div><hr>";
				echo "<div class=\"row\"><div class=\"col-xs-8\">";

				echo campo("moto_categoria", "Categoria", 'select', valores_combobox_texto('<option id="" value="motocicleta">Motocicleta</option><option id="" value="motocargo">Motocargo</option><option id="" value="mototaxi">Mototaxi</option><option id="" value="cuatrimoto">Cuatrimoto</option><option id="" value="vehiculo">Vehiculo</option>', ($row["moto_categoria"] ? $row["moto_categoria"] : "motocicleta")), 'class=" form-control"', '', '', 3, 8);
				echo campo("moto_serie", "Serie", 'text', $row["moto_serie"], 'class="form-control"  ', '', '', 3, 8);
				//   echo campo("moto_tipo","Tipo",'text',$row["moto_tipo"],'class="form-control"  ','','',3,8);
				echo campo("moto_marca", "Marca", 'text', $row["moto_marca"], 'class="form-control"  ', '', '', 3, 8);
				echo campo("moto_modelo", "Modelo", 'text', $row["moto_modelo"], 'class="form-control"  ', '', '', 3, 8);
				echo campo("moto_motor", "Motor", 'text', $row["moto_motor"], 'class="form-control"  ', '', '', 3, 8);
				echo campo("moto_color", "Color", 'text', $row["moto_color"], 'class="form-control"  ', '', '', 3, 5);
				echo campo("moto_ano", "A&ntilde;o", 'text', $row["moto_ano"], 'class="form-control"  ', '', '', 3, 5);
				echo campo("moto_cilindraje", "Cilindraje", 'text', $row["moto_cilindraje"], 'class="form-control"  ', '', '', 3, 5);
				echo campo("moto_valor", "Valor en LPS", 'text', $row["moto_valor"], 'class="form-control"  ', '', '', 3, 8);

				if ((tiene_permiso(PermisosModulos::PERMISOS_VENDEDOR) or tiene_permiso(PermisosModulos::PERMISOS_JEFE_TIENDA)) and $row["moto_serie"] == "") { //vendedor o jefe de tienda
					echo '<a id="Guardar' . $forma . '" href="#" class="btn btn-primary" onclick="procesar_datos_gestion(\'creditos_gestion.php?a=5b1ss&s=1&cid=' . $solicitud_id . '\',' . $forma . '); return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar</a>';
					echo '<div id="respuesta' . $forma . '"> </div>';
				}

				echo "</div><div class=\"col-xs-4\">" . boton_verificar(902, $solicitud_id, true, $row["usuario_alta"], $asignados, $campo_unico) . "</div></div>";
			}
			// generar contrato

			if (tiene_permiso(PermisosModulos::PERMISOS_GENERAR_CONTRATO)) {

				echo "<h4>Datos del contrato</h4><hr>";

				echo campo("numero_credito_sifco", "Numero Desembolso SIFCO", 'text', $row["numero_credito_sifco"], "class='form-control'", '', '', 3, 3);
				echo campo("cierre_cuota_dia_pago", "Dia Pago Cuota", 'text', $row["cierre_cuota_dia_pago"], 'class="form-control"  ', '', '', 3, 3);
				echo campo("cierre_cuota_primera", "Fecha de Primera Cuota", 'date', fechademysql($row["cierre_cuota_primera"]), 'class="form-control" ', '', '', 3, 3);
				echo campo("cierre_cuota_final", "Fecha de Ultima Cuota", 'date', fechademysql($row["cierre_cuota_final"]), 'class="form-control" ', '', '', 3, 3);
				echo campo("cierre_firma_fecha", "Fecha de Firma Contrato", 'date', fechademysql($row["cierre_firma_fecha"]), 'class="form-control" ', '', '', 3, 3);

				echo '<a id="Guardar' . $forma . '" href="#" class="btn btn-primary" onclick="procesar_datos_contrato(\'creditos_gestion.php?a=5g9&cid=' . $solicitud_id . '\',' . $forma . '); return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar Contrato</a>';
				echo '<div id="respuesta' . $forma . '"> </div>';
				if (tiene_permiso(PermisosModulos::PERMISOS_IMPRIMIR_CONTRATO)) {
					echo '<div style="display: none;" id="respboton' . $forma . '"><a id="btnimp_1' . $forma . '" href="#" class="btn btn-default" onclick="imprimir_contratos(' . $solicitud_id . '); return false;"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Imprimir Documentos</a></div>';
				}
			}

			echo "<hr>";
			echo '</div>';
		} else {
			echo mensaje("No se encontraron registros", "info");
			exit;
		}
	}

	if ($cod_status == TipoEtapas::ETAPA_FIRMA_CONTRATOS) { //FIRMA DE CONTRATO

		$sql = "SELECT * FROM prestamo WHERE id = {$solicitud_id}";

		$result = $conn->query($sql);

		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();

			echo '<div class="row">';

			if (es_nulo($row["cierre_documentos_enviados_gestion"])) {

				if (tiene_permiso(PermisosModulos::PERMISOS_JEFE_TIENDA)) {
					echo '<a id="gestionbtn' . $forma . '" href="#" class="btn btn-primary" onclick="procesar_gestion10(\'creditos_gestion.php?a=5g10&cid=' . $solicitud_id . '\',' . $forma . '); return false;"><span class="glyphicon glyphicon-road" aria-hidden="true"></span> Crear gestion para enviar documentos</a>';
					echo '<div id="gestionrespuesta' . $forma . '"> </div>';
				}
			} else {
				if (empty($campo_unico) or $campo_unico == 1001) {

					//$campo_sifco_desabilitado = empty($row["doc_contrato"]) && empty($row["doc_foto_persona"]) ? "disabled" : "";
					//$campo_sifco_desabilitado = !tiene_permiso(PermisosModulos::PERMISOS_PERSONAL_CREDITO) ? "disabled" : "";
					//$numero_credito_sifco_field = campo("numero_credito_sifco", "", 'text', $row["numero_credito_sifco"], "class='form-control' {$campo_sifco_desabilitado}", '', '', 3, 8);

					$cierre_documentos_tracking_field = campo("cierre_documentos_tracking", "", 'text', $row["cierre_documentos_tracking"], 'class="form-control"  ', '', '', 3, 8);
					$campo_gestion = boton_verificar(1001, $solicitud_id, false, $row["usuario_alta"], $asignados, $campo_unico);


					echo
						"<hr>
						<div class='row'>
							<div class='col-xs-8'> 
							<p>Numero Guia de Envio</p>
							{$cierre_documentos_tracking_field}<br>
						</div>
						<div class=\"col-xs-4\">
							{$campo_gestion}
						</div>
					</div>";
					//echo "<div class=\"row\"><div class=\"col-xs-8\">".campo("cierre_documentos_tracking","",'text',$row["cierre_documentos_tracking"],'class="form-control"  ','','',3,8)."</div></div><br><br>";
					echo "<hr>";
					//  echo campo("doc_contrato","Contrato Firmado",'upload',$row["doc_contrato"],'class="form-control" ',$row["id"]);
					echo campo_upload("doc_contrato", "Contrato Firmado", 'upload', $row["doc_contrato"], 'class="form-control" ', $row["id"], 0, 12, "SI");
					echo campo_upload("doc_foto_persona", "Foto de la Persona", 'upload', $row["doc_foto_persona"], 'class="form-control" ', $row["id"], 0, 12, "SI");
				}

				// $documentos_subidos = !empty($row["doc_contrato"]) && !empty($row["doc_foto_persona"]);
				// if (tiene_permiso(PermisosModulos::PERMISOS_PERSONAL_CREDITO)) {
				// 	$disabled = !$documentos_subidos ? "disabled" : "";
				// 	echo campo("numero_credito_sifco", "", 'text', $row["numero_credito_sifco"], "class='form-control' {$disabled} ", '', '', 3, 9);
				// 	echo "<hr>";
				// }
			}

			echo "<hr>";
			echo '</div>';
		} else {
			echo mensaje("No se encontraron registros", "info");
			exit;
		}
	}

	if ($cod_status == TipoEtapas::ETAPA_CIERRE) { //CIERRE

		$sql = "SELECT * FROM prestamo WHERE id = {$solicitud_id}";

		$result = $conn->query($sql);

		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();

			echo ' <div class="row">';
			echo "<hr>";
			echo campo("cierre_razon", "Razon de Cierre", 'select', valores_combobox_db('prestamo_cierre', $row["cierre_razon"], "nombre as texto", " order by nombre", 'texto', 'Seleccione'), 'class="form-control" ', '', '', 3, 6);
			echo campo("cierre_factura", "Numero de Factura", 'text', $row["cierre_factura"], 'class="form-control"  ', '', '', 3, 5);
			if (tiene_permiso(PermisosModulos::PERMISOS_PERSONAL_CREDITO)) {
				echo "<hr>";
				echo '<a id="Guardar' . $forma . '" href="#" class="btn btn-primary" onclick="procesar_datos_gestion(\'creditos_gestion.php?a=5g11&s=1&cid=' . $solicitud_id . '\',' . $forma . '); return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar</a>';
				echo '<div id="respuesta' . $forma . '"> </div>';
			}
			echo "<hr>";
			echo '</div>';
		} else {
			echo mensaje("No se encontraron registros", "info");
			exit;
		}
	}

	if ($cod_status == TipoEtapas::ETAPA_RECIBIR_DOCUMENTACION) { //RECIBIR DOCUMENTACION FISICA

		$sql = "SELECT * FROM prestamo WHERE id = $solicitud_id";

		$result = $conn->query($sql);

		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();

			echo ' <div class="row">';
			echo "<hr>";

			echo campo("cierre_documentos_recibidos", "Documentos Recibidos", 'select', valores_combobox_texto('<option value="">Seleccione</option><option value="SI">SI</option><option value="NO">NO</option>', $row["cierre_documentos_recibidos"]), 'class="form-control" ', '', '', 3, 3);
			echo campo("cierre_documentos_recibidos_fecha", "Fecha Recibido", 'date', ($row["cierre_documentos_recibidos_fecha"]), 'class="form-control" ', '', '', 3, 3);
			if (tiene_permiso(PermisosModulos::PERMISOS_PERSONAL_CREDITO)) {
				echo "<hr>";
				echo '<a id="Guardar' . $forma . '" href="#" class="btn btn-primary" onclick="procesar_datos_gestion(\'creditos_gestion.php?a=5b1ss&s=1&cid=' . $solicitud_id . '\',' . $forma . '); return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar</a>';
				echo '<div id="respuesta' . $forma . '"> </div>';
			}
			echo "<hr>";
			echo '</div>';
		} else {
			echo mensaje("No se encontraron registros", "info");
			exit;
		}
	}

	echo '</form>';

	//---------------------------------- 
	//----------------------------------  
	if (!empty($campo_unico)) {

		if ($_REQUEST['gest'] == 'Vendedor') {
			echo '<br><div class="well">';
			echo "<div class='row'><div class='col-xs-12'> <form id='forma{$forma}' class='form-horizontal' >";

			echo campo("texto_responde", "Respuesta", 'textarea', '', 'class="form-control" rows="5"', '', '', 2, 9);
			echo '<a id="Guardar' . $forma . '" href="#" class="btn btn-primary" onclick="procesar_datos_gestion(\'creditos_gestion.php?a=5z&s=1&geid=' . $_REQUEST['geid'] . '&cid=' . $solicitud_id . '\',' . $forma . '); return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar y Notificar a Creditos</a>';
			echo '<div id="respuesta' . $forma . '"> </div>';
			echo "</form></div></div></div><hr>";
		}
	}



	if ($campo_unico == "") {

		// ************* ver  historial gestiones
		//******* SQL ************************************************************************************

		$sql = "SELECT prestamo_gestion.id, fecha, hora, usuario, etapa_id, descripcion 
            , prestamo_etapa.nombre as vetapa
            , prestamo_estatus.nombre as vestatus
                    FROM prestamo_gestion
                    LEFT OUTER JOIN prestamo_etapa ON (prestamo_etapa.id=prestamo_gestion.etapa_id) 
                    LEFT OUTER JOIN prestamo_estatus ON (prestamo_estatus.id=prestamo_gestion.estatus_id) 
                    where prestamo_id=$solicitud_id and etapa_id=$cod_status
                    and gestion_estado is null
                    order by prestamo_gestion.id";
		// ****** Fin SQL ********************************************************************************

		$result = $conn->query($sql);


		if ($result->num_rows > 0) {
			//echo $sql;

			echo
				'<br><br><hr><br>
			<table class="table table-striped" id="tbl-gestiones">
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


			while ($row = $result->fetch_assoc()) {
				echo
					"<tr>
					<td class='text-center'>" . fechademysql($row["fecha"]) . " " . horademysql($row["hora"]) . "</td>
					<td class='text-left'>" . $row["vetapa"] . "</td>
					<td class='text-center'>" . $row["vestatus"] . "</td>
					<td class='text-left'>" . $row["descripcion"] . "</td>
					<td class='text-center'>" . $row["usuario"] . "</td>
				</tr>";
			}

			echo "</tbody></table><br>";
		}


		//******** nueava gestion ********************************************************
		if (tiene_permiso(PermisosModulos::PERMISOS_PERSONAL_CREDITO)) {
			echo '<br><div class="well">';
			echo '<div id="nuevagestion" class="row"><div class="col-xs-12"> <form id="formagestion" class="form-horizontal" >';

			echo "<h4>Gestion</h4><br>";

			echo campo("etapa_id", "", 'hidden', $cod_status, '');
			echo campo("descripcion", "Descripcion", 'textarea', '', 'class="form-control" rows="6"', '', '', 3, 9);
			echo campo("estado", "Estado", 'select', valores_combobox_db('prestamo_estatus', '', 'nombre', '', '', $texto_primera = 'Seleccione...'), 'class="form-control" ', '', '', 3, 4);

			echo '<div id="botones"><a id="btnguardar" href="#" class="btn btn-primary" onclick="procesarforma() ; return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar</a></div>
   				<img id="cargando" style="display: none;" src="images/load.gif"/> ';

			echo " </form></div></div>";


			$siguienteb = intval($cod_status) + 1;

			echo
				'<div id="salidagestion" class="row"><div class="col-xs-12"> 
					<br>  
					<div id="salida"></div>
					<div id="siguientegestion" style="display: none;"><a href="#a4"  onclick="actualizarbox(\'a1\',\'creditos_gestion.php?a=5&cid=' . $solicitud_id . '&b=' . $siguienteb . '\') ; return false;" data-toggle="tab" class="btn btn-primary">Siguiente</a> </div>
				</div></div>';

			echo "</div>";
			?>


			<script>
				function procesarforma() {
					$("#botones *").attr("disabled", "disabled");
					$("#formagestion :input").attr('readonly', true);
					$('#cargando').show();
					var myTable = '';

					var url = "creditos_gestion.php?a=5b1&s=1&cid=<?php echo $solicitud_id; ?>";
					$.getJSON(url, $("#formagestion").serialize(), function (json) {

						i = 1;
						if (json.length > 0) {
							if (json[0].pcode == 0) {

								$('#salida').empty().append('<div class="alert alert-warning" role="alert">' + json[0].pmsg + '</div>');

							}
							if (json[0].pcode == 1) {


								$('#nuevagestion').hide();
								$('#siguientegestion').show();
								$('#salida').empty().append('<div class="alert alert-info" role="alert">' + json[0].pmsg + '</div>');

							}
						} else {
							$('#salida').empty().append('<div class="alert alert-danger" role="alert">Se produjo un error en comunicacion JSON:101</div>');
						}

					}).error(function () {
						$('#salida').empty().append('<div class="alert alert-danger" role="alert">Se produjo un error en comunicacion JSON:102</div>');
					}).complete(function () {

						$('#cargando').hide();
						$("#formagestion :input").attr('readonly', false);
						$("#botones *").removeAttr("disabled");
					});

				}
			</script>




			<?php

		} //nueva gestion 


	} else {
		$solicitud_num = '';
		if (isset($_REQUEST['num'])) {
			$solicitud_num = $_REQUEST['num'];
		}
		echo "<br><br><a href=\"#\" onclick=\"actualizarbox('pagina','creditos.php?a=0b&cid=$solicitud_id&num=$solicitud_num') ; return false;\"   class=\"btn btn-default\">REGRESAR</a>";
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a  href=\"#\" class=\"btn btn-default btn-sm\" onclick=\"actualizarbox('pagina','creditos_gestion.php?a=1&cid=" . $solicitud_id . "') ; return false;\" ><span class=\"glyphicon glyphicon-folder-open\" aria-hidden=\"true\"></span>&nbsp; Abrir Solicitud</a>";
	}

	exit;
}


if ($accion == Modulos::MODULO_HISTORIAL_GESTIONES) //TODO Historial gestiones
{

	//******* SQL ************************************************************************************

	$sql = "SELECT prestamo_gestion.id, fecha, hora, usuario, etapa_id, descripcion 
            ,usuario_responde, usuario_confirma, hora_responde, hora_confirma
            , prestamo_etapa.nombre as vetapa
            , prestamo_estatus.nombre as vestatus
                    FROM prestamo_gestion
                    LEFT OUTER JOIN prestamo_etapa ON (prestamo_etapa.id=prestamo_gestion.etapa_id) 
                    LEFT OUTER JOIN prestamo_estatus ON (prestamo_estatus.id=prestamo_gestion.estatus_id) 
                    where prestamo_id=$solicitud_id 
                    ";

	// ****** Fin SQL ********************************************************************************

	$result = $conn->query($sql);

	if ($result->num_rows > 0) {


		echo
			'<div class="row"><div class="">
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


		while ($row = $result->fetch_assoc()) {
			$fecha_recibe = "";
			$fecha_confirma = "";
			if (!is_null($row["hora_responde"])) {
				$fecha_recibe = date("d/m/y h:i a", strtotime($row["hora_responde"]));
			}
			if (!is_null($row["hora_confirma"])) {
				$fecha_confirma = date("d/m/y h:i a", strtotime($row["hora_confirma"]));
			}

			echo
				"<tr>
				<td class='text-center'>" . $row["id"] . "</td>               
				<td class='text-center'>" . $row["usuario"] . "<br>" . fechademysql($row["fecha"]) . ' ' . horademysql($row["hora"]) . "</td>
				<td class='text-center'>" . $row["usuario_responde"] . '<br>' . $fecha_recibe . "</td>
				<td class='text-center'>" . $row["usuario_confirma"] . '<br>' . $fecha_confirma . "</td>
				<td class='text-left'>" . $row["vetapa"] . "</td>
				<td class='text-center'>" . $row["vestatus"] . "</td>
				<td class='text-left'>" . $row["descripcion"] . "</td>
			</tr>";
		}

		echo "</tbody></table></div></div>";

		echo crear_datatable('tabla', 'false', true, false);
	} else {
		echo mensaje("No se encontraron registros", "info");
		exit;
	}


	exit;
}


if ($accion == "52") //TODO gestion Nueva
{
	echo ' <div class="panel panel-default"> <div id="datosgenerales" class="panel-body"> <div class="row">                  <div class="col-xs-12"> <form class="form-horizontal" autocomplete="off">';

	echo "<h4>Nueva Gestion</h4><br>";


	echo campo("etapa_id", "Tipo de Gestion", 'select', valores_combobox_db('prestamo_etapa', '', 'nombre', '', 'nombre'), 'class="form-control" ', '', '', 3, 8);
	echo campo("descripcion", "Descripcion", 'textarea', '', 'class="form-control" rows="6"', '', '', 3, 9);

	echo '<a id="btnguardar" href="#" class="btn btn-primary" onclick="procesarforma() ; return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar</a>';

	echo " </form></div></div></div></div>";
}


?>