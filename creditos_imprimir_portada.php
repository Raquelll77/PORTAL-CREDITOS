<?php

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);
ob_start();
require_once('include/protect.php');
require_once('include/framework.php');

require_once('lib/tcpdf/config/lang/spa.php');
require_once('lib/tcpdf/tcpdf.php');


$html = file_get_contents("include/plantillas/solicitud.html");
if ($html === false) {
    echo "Error al buscar la plantilla";
}

if (isset($_REQUEST['cid'])) {
    $solicitud_id = ($_REQUEST['cid']);
} else {
    exit;
}

$conn = new mysqli(db_ip, db_user, db_pw, db_name);
if (mysqli_connect_errno()) {
    echo mensaje("Error al Conectar a la Base de Datos [DB:101]", "danger");
    exit;
}
$conn->set_charset("utf8");


$sql = " select *
               FROM prestamo ";
$sql .= sqladd(' where id = ', $conn->real_escape_string($solicitud_id), "int");

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = mysqli_fetch_array($result);

    $sqlAval = "
            SELECT 
                CONCAT(TRIM(nombres), ' ', TRIM(apellidos)) AS nombrecompleto,
                estado_civil,
                identidad,
                ciudad,
                departamento,
                direccion,
                direccion_referencia,
                celular,
                email,
                sexo   
            FROM prestamo_aval
            WHERE id = '" . $conn->real_escape_string($solicitud_id) . "'
              AND TRIM(nombres) <> ''
            LIMIT 1
        ";

    $clienteNombre = trim($row["nombres"] . " " . $row["apellidos"]);
    $nombreAval = "";
    $filaFirmas = "";
    $firmaAvalLinea = "";

    $resAval = $conn->query($sqlAval);

    if ($resAval && $resAval->num_rows > 0) {
        $aval = $resAval->fetch_assoc();

        if (!empty($aval["nombrecompleto"])) {
            $nombreAval = " Y " . $aval["nombrecompleto"];

            // Con Aval
            $filaFirmas = ' <tr> <td colspan="2" class="left bottom" style="text-align: center"> <b>Firma del cliente</b> </td> <td colspan="2" style="text-align:center; border-top:1px solid #000;"> <b>Firma del Aval</b> </td> <td colspan="2" class="right bottom" style="text-align: center"> <b>Lugar y Fecha</b> </td> </tr>';
        }

        $firmaPrevencion = strtoupper($clienteNombre . $nombreAval);

        // Reemplazar en la plantilla
        $html = str_replace("{aval_nombre}", $aval["nombrecompleto"], $html);
        $html = str_replace("{aval_estado_civil}", $aval["estado_civil"], $html);
        $html = str_replace("{aval_identidad}", $aval["identidad"], $html);
        $html = str_replace("{aval_ciudad}", $aval["ciudad"], $html);
        $html = str_replace("{aval_departamento}", $aval["departamento"], $html);
        $html = str_replace("{aval_direccion}", $aval["direccion"], $html);
        $html = str_replace("{aval_direccion_referencia}", $aval["direccion_referencia"], $html);
        $html = str_replace("{aval_celular}", $aval["celular"], $html);
        $html = str_replace("{aval_email}", $aval["email"], $html);

        if ($aval["sexo"] == "MASCULINO") {
            $html = str_replace("{aval_masculino}", "x", $html);
            $html = str_replace("{aval_femenino}", "", $html);
        } else if ($aval["sexo"] == "FEMENINO") {
            $html = str_replace("{aval_masculino}", "", $html);
            $html = str_replace("{aval_femenino}", "x", $html);
        } else {
            $html = str_replace("{aval_masculino}", "", $html);
            $html = str_replace("{aval_femenino}", "", $html);
        }

    } else {
        // Sin Aval
        $filaFirmas = ' <tr>
        <td colspan="2" class="left bottom" style="text-align: center">
          <b>Firma del cliente</b>
        </td>
        
        <td colspan="2" class="bottom"></td>
        <td colspan="2" class="right bottom" style="text-align: center">
          <b>Lugar y Fecha</b>
        </td>
      </tr>';

        $firmaPrevencion = strtoupper($clienteNombre);

        // Si no hay aval, limpiar placeholders
        $html = str_replace("{aval_nombre}", "", $html);
        $html = str_replace("{aval_estado_civil}", "", $html);
        $html = str_replace("{aval_identidad}", "", $html);
        $html = str_replace("{aval_ciudad}", "", $html);
        $html = str_replace("{aval_departamento}", "", $html);
        $html = str_replace("{aval_direccion}", "", $html);
        $html = str_replace("{aval_direccion_referencia}", "", $html);
        $html = str_replace("{aval_celular}", "", $html);
        $html = str_replace("{aval_email}", "", $html);
        $html = str_replace("{aval_femenino}", "", $html);
        $html = str_replace("{aval_masculino}", "", $html);
    }

    // ---------------- PDF CONFIG -----------------
    class MYPDF extends TCPDF
    {
        public function Header()
        {
        }
        public function TRIM($str, $charlist = " \t\n\r\0\x0B")
        {
            $start = 0;
            $end = strlen($str);
            while ($start < $end && strpos($charlist, $str[$start]) !== false) {
                $start++;
            }
            while ($end > $start && strpos($charlist, $str[$end - 1]) !== false) {
                $end--;
            }
            return substr($str, $start, $end - $start);
        }
    }

    ob_start();
    error_reporting(E_ALL & ~E_NOTICE);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);

    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LEGAL', true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('MOVESA');
    $pdf->SetTitle('DOCUMENTOS');
    $pdf->SetSubject('Documentos');
    $pdf->SetKeywords('Carta, Poder, Documento');

    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(15, 10, 15);
    $pdf->SetHeaderMargin(0);
    $pdf->SetFooterMargin(0);
    $pdf->setPrintFooter(false);
    $pdf->setPrintHeader(false);
    $pdf->SetAutoPageBreak(TRUE, 15);

    $pdf->SetFont('helvetica', '', 11);
    $pdf->AddPage();

    $bMargin = $pdf->getBreakMargin();
    $auto_page_break = $pdf->getAutoPageBreak();
    $pdf->SetAutoPageBreak(false, 0);
    $pdf->SetAutoPageBreak($auto_page_break, $bMargin);
    $pdf->setPageMark();

    // ---------------- REEMPLAZOS GENERALES -----------------
    $html = str_replace("{firmaPrevencion}", $firmaPrevencion, $html);
    $html = str_replace("{firmaAvalLinea}", $firmaAvalLinea, $html);
    $html = str_replace("{filaFirmas}", $filaFirmas, $html);


    if ($row["moto_categoria"] == "motocicleta") {
        $html = str_replace("{motocicleta}", "x", $html);
        $html = str_replace("{cuatrimoto}", "", $html);
        $html = str_replace("{mototaxi}", "", $html);
        $html = str_replace("{motocargo}", "", $html);
        $html = str_replace("{otro}", "", $html);
    } else if ($row["moto_categoria"] == "cuatrimoto") {
        $html = str_replace("{motocicleta}", "", $html);
        $html = str_replace("{cuatrimoto}", "x", $html);
        $html = str_replace("{mototaxi}", "", $html);
        $html = str_replace("{motocargo}", "", $html);
        $html = str_replace("{otro}", "", $html);
    } else if ($row["moto_categoria"] == "mototaxi") {
        $html = str_replace("{motocicleta}", "", $html);
        $html = str_replace("{cuatrimoto}", "", $html);
        $html = str_replace("{mototaxi}", "x", $html);
        $html = str_replace("{motocargo}", "", $html);
        $html = str_replace("{otro}", "", $html);
    } else if ($row["moto_categoria"] == "motocargo") {
        $html = str_replace("{motocicleta}", "", $html);
        $html = str_replace("{cuatrimoto}", "", $html);
        $html = str_replace("{mototaxi}", "", $html);
        $html = str_replace("{motocargo}", "x", $html);
        $html = str_replace("{otro}", "", $html);
    } else {
        $html = str_replace("{motocicleta}", "", $html);
        $html = str_replace("{cuatrimoto}", "", $html);
        $html = str_replace("{mototaxi}", "", $html);
        $html = str_replace("{motocargo}", "", $html);
        $html = str_replace("{otro}", "x", $html);
    }


    $html = str_replace("{nombre_completo}", $row["nombres"] . " " . $row["apellidos"], $html);
    $html = str_replace("{identidad}", $row["identidad"], $html);
    $html = str_replace("{direccion}", $row["direccion"], $html);
    $html = str_replace("{pais}", $row["pais"], $html);
    $html = str_replace("{cliente_nacionalidad}", $row["cliente_nacionalidad"], $html);
    $html = str_replace("{claveEnee}", $row["clave_enee"], $html);
    $html = str_replace("{lugar_nacimiento}", $row["lugar_nacimiento"], $html);
    $html = str_replace("{rtn}", $row["rtn"], $html);

    $html = str_replace("{moto_marca}", $row["moto_marca"], $html);
    $html = str_replace("{moto_modelo}", $row["moto_modelo"], $html);
    $html = str_replace("{codigo_cliente}", $row["codigo_cliente"], $html);
    $html = str_replace("{sucursal_tienda}", $row["bodega_nombre"], $html);
    $html = str_replace("{compra_productos}", $row["compra_productos"], $html);

    if ($row["tipo_identificacion"] == "DNI") {
        $html = str_replace("{dniNueva}", "x", $html);
        $html = str_replace("{idVieja}", "", $html);
        $html = str_replace("{residente}", "", $html);
        $html = str_replace("{licenciaConducir}", "", $html);
    } else if ($row["tipo_identificacion"] == "ID Vieja") {
        $html = str_replace("{dniNueva}", "", $html);
        $html = str_replace("{idVieja}", "x", $html);
        $html = str_replace("{residente}", "", $html);
        $html = str_replace("{licenciaConducir}", "", $html);
    } else if ($row["tipo_identificacion"] == "Carne de Residente") {
        $html = str_replace("{dniNueva}", "", $html);
        $html = str_replace("{idVieja}", "", $html);
        $html = str_replace("{residente}", "x", $html);
        $html = str_replace("{licenciaConducir}", "", $html);
    } else if ($row["tipo_identificacion"] == "Licencia de Conducir Vigente") {
        $html = str_replace("{dniNueva}", "", $html);
        $html = str_replace("{idVieja}", "", $html);
        $html = str_replace("{residente}", "", $html);
        $html = str_replace("{licenciaConducir}", "x", $html);
    } else {
        $html = str_replace("{dniNueva}", "", $html);
        $html = str_replace("{idVieja}", "", $html);
        $html = str_replace("{residente}", "", $html);
        $html = str_replace("{licenciaConducir}", "", $html);
    }

    if ($row["otra_nacionalidad"] == "SI") {
        $html = str_replace("{siNacionalidad}", "x", $html);
        $html = str_replace("{noNacionalidad}", "", $html);
        $html = str_replace("{nacionalidadExtra}", $row["nacionalidad_extra"], $html);
    } else {
        $html = str_replace("{siNacionalidad}", "", $html);
        $html = str_replace("{noNacionalidad}", "x", $html);
        $html = str_replace("{nacionalidadExtra}", "", $html);
    }

    if ($row["cliente_nuevo_recompra"] == "Nuevo") {
        $html = str_replace("{nuevo}", "x", $html);
        $html = str_replace("{recompra}", "", $html);
    } else {
        $html = str_replace("{nuevo}", "", $html);
        $html = str_replace("{recompra}", "x", $html);
    }

    if ($row["uso_unidad"] == "Personal") {
        $html = str_replace("{personal}", "x", $html);
        $html = str_replace("{comercial}", "", $html);
    } else {
        $html = str_replace("{personal}", "", $html);
        $html = str_replace("{comercial}", "x", $html);
    }

    if ($row["producto_servicio"] == "Vehiculo Nuevo") {
        $html = str_replace("{vehiculo_nuevo}", "x", $html);
        $html = str_replace("{vehiculo_usado}", "", $html);
        $html = str_replace("{flota_vehiculos}", "", $html);
        $html = str_replace("{servicio_taller_mecanica}", "", $html);
        $html = str_replace("{servicio_taller_pintura}", "", $html);
        $html = str_replace("{productos_automotrices}", "", $html);
        $html = str_replace("{otros}", "", $html);
        $html = str_replace("{otro_cargo_servicio_especificar}", "", $html);

    } else if ($row["producto_servicio"] == "Vehiculo Usado") {
        $html = str_replace("{vehiculo_usado}", "x", $html);
        $html = str_replace("{vehiculo_nuevo}", "", $html);
        $html = str_replace("{flota_vehiculos}", "", $html);
        $html = str_replace("{servicio_taller_mecanica}", "", $html);
        $html = str_replace("{servicio_taller_pintura}", "", $html);
        $html = str_replace("{productos_automotrices}", "", $html);
        $html = str_replace("{otros}", "", $html);
        $html = str_replace("{otro_cargo_servicio_especificar}", "", $html);

    } else if ($row["producto_servicio"] == "Flota de Vehiculos") {
        $html = str_replace("{flota_vehiculos}", "x", $html);
        $html = str_replace("{vehiculo_usado}", "", $html);
        $html = str_replace("{vehiculo_nuevo}", "", $html);
        $html = str_replace("{servicio_taller_mecanica}", "", $html);
        $html = str_replace("{servicio_taller_pintura}", "", $html);
        $html = str_replace("{productos_automotrices}", "", $html);
        $html = str_replace("{otros}", "", $html);
        $html = str_replace("{otro_cargo_servicio_especificar}", "", $html);

    } else if ($row["producto_servicio"] == "Servicio de Taller de Mecanica") {
        $html = str_replace("{servicio_taller_mecanica}", "x", $html);
        $html = str_replace("{flota_vehiculos}", "", $html);
        $html = str_replace("{vehiculo_usado}", "", $html);
        $html = str_replace("{vehiculo_nuevo}", "", $html);
        $html = str_replace("{servicio_taller_pintura}", "", $html);
        $html = str_replace("{productos_automotrices}", "", $html);
        $html = str_replace("{otros}", "", $html);
        $html = str_replace("{otro_cargo_servicio_especificar}", "", $html);


    } else if ($row["producto_servicio"] == "Servicio de Taller de Pintura y Enderezado") {
        $html = str_replace("{servicio_taller_pintura}", "x", $html);
        $html = str_replace("{servicio_taller_mecanica}", "", $html);
        $html = str_replace("{flota_vehiculos}", "", $html);
        $html = str_replace("{vehiculo_usado}", "", $html);
        $html = str_replace("{vehiculo_nuevo}", "", $html);
        $html = str_replace("{productos_automotrices}", "", $html);
        $html = str_replace("{otros}", "", $html);
        $html = str_replace("{otro_cargo_servicio_especificar}", "", $html);

    } else if ($row["producto_servicio"] == "Productos Automotrices") {
        $html = str_replace("{productos_automotrices}", "x", $html);
        $html = str_replace("{servicio_taller_pintura}", "", $html);
        $html = str_replace("{servicio_taller_mecanica}", "", $html);
        $html = str_replace("{flota_vehiculos}", "", $html);
        $html = str_replace("{vehiculo_usado}", "", $html);
        $html = str_replace("{vehiculo_nuevo}", "", $html);
        $html = str_replace("{otros}", "", $html);
        $html = str_replace("{otro_cargo_servicio_especificar}", "", $html);

    } else {
        $html = str_replace("{otros}", "x", $html);
        $html = str_replace("{vehiculo_nuevo}", "", $html);
        $html = str_replace("{vehiculo_usado}", "", $html);
        $html = str_replace("{flota_vehiculos}", "", $html);
        $html = str_replace("{servicio_taller_mecanica}", "", $html);
        $html = str_replace("{servicio_taller_pintura}", "", $html);
        $html = str_replace("{productos_automotrices}", "", $html);
        $html = str_replace("{otro_cargo_servicio_especificar}", $row["otro_cargo_servicio_especificar"], $html);

    }

    if ($row["cantidad_vehiculos"] == 1) {
        $html = str_replace("{uno}", "x", $html);
        $html = str_replace("{dos_a_cinco}", "", $html);
        $html = str_replace("{seis_a_cincuenta}", "", $html);
        $html = str_replace("{numero_exacto}", "", $html);
        $html = str_replace("{mas_cincuenta}", "", $html);

    } else if ($row["cantidad_vehiculos"] >= 2 && $row["cantidad_vehiculos"] <= 5) {
        $html = str_replace("{dos_a_cinco}", "x", $html);
        $html = str_replace("{uno}", "", $html);
        $html = str_replace("{seis_a_cincuenta}", "", $html);
        $html = str_replace("{numero_exacto}", "", $html);
        $html = str_replace("{mas_cincuenta}", "", $html);

    } else if ($row["cantidad_vehiculos"] >= 6 && $row["cantidad_vehiculos"] <= 50) {
        $html = str_replace("{seis_a_cincuenta}", "x", $html);
        $html = str_replace("{uno}", "", $html);
        $html = str_replace("{dos_a_cinco}", "", $html);
        $html = str_replace("{numero_exacto}", "", $html);
        $html = str_replace("{mas_cincuenta}", "", $html);

    } else if ($row["cantidad_vehiculos"] > 50) {
        $html = str_replace("{mas_cincuenta}", "x", $html);
        $html = str_replace("{seis_a_cincuenta}", "", $html);
        $html = str_replace("{uno}", "", $html);
        $html = str_replace("{dos_a_cinco}", "", $html);
        $html = str_replace("{numero_exacto}", "", $html);
    } else {
        $html = str_replace("{mas_cincuenta}", "", $html);
        $html = str_replace("{seis_a_cincuenta}", "", $html);
        $html = str_replace("{uno}", "", $html);
        $html = str_replace("{dos_a_cinco}", "", $html);
        $html = str_replace("{numero_exacto}", $row["cantidad_vehiculos"], $html);
    }

    $html = str_replace("{fecha_nacimiento}", $row["fecha_nacimiento"], $html);

    if ($row["estado_civil"] == "SOLTERO") {
        $html = str_replace("{soltero}", "x", $html);
        $html = str_replace("{casado}", "", $html);
        $html = str_replace("{divorciado}", "", $html);
        $html = str_replace("{viudo}", "", $html);
        $html = str_replace("{unionLibre}", "", $html);

    } else if ($row["estado_civil"] == "CASADO") {
        $html = str_replace("{casado}", "x", $html);
        $html = str_replace("{soltero}", "", $html);
        $html = str_replace("{divorciado}", "", $html);
        $html = str_replace("{viudo}", "", $html);
        $html = str_replace("{unionLibre}", "", $html);

    } else if ($row["estado_civil"] == "DIVORCIADO") {
        $html = str_replace("{divorciado}", "x", $html);
        $html = str_replace("{casado}", "", $html);
        $html = str_replace("{soltero}", "", $html);
        $html = str_replace("{viudo}", "", $html);
        $html = str_replace("{unionLibre}", "", $html);

    } else if ($row["estado_civil"] == "VIUDO") {
        $html = str_replace("{viudo}", "x", $html);
        $html = str_replace("{divorciado}", "", $html);
        $html = str_replace("{casado}", "", $html);
        $html = str_replace("{soltero}", "", $html);
        $html = str_replace("{unionLibre}", "", $html);

    } else if ($row["estado_civil"] == "UNION LIBRE") {
        $html = str_replace("{unionLibre}", "x", $html);
        $html = str_replace("{viudo}", "", $html);
        $html = str_replace("{divorciado}", "", $html);
        $html = str_replace("{casado}", "", $html);
        $html = str_replace("{soltero}", "", $html);

    } else {
        $html = str_replace("{unionLibre}", "", $html);
        $html = str_replace("{viudo}", "", $html);
        $html = str_replace("{divorciado}", "", $html);
        $html = str_replace("{casado}", "", $html);
        $html = str_replace("{soltero}", "", $html);

    }

    $html = str_replace("{direccion}", $row["direccion"], $html);
    $html = str_replace("{departamento}", $row["departamento"], $html);
    $html = str_replace("{municipio}", $row["municipio"] ? $row["municipio"] : " - ", $html);
    $html = str_replace("{ciudad}", $row["ciudad"] ? $row["ciudad"] : " - ", $html);
    $html = str_replace("{avenida}", $row["avenida"] ? $row["avenida"] : " - ", $html);
    $html = str_replace("{calle}", $row["calle"] ? $row["calle"] : " - ", $html);
    $html = str_replace("{sector_edificio}", $row["sector_edificio"] ? $row["sector_edificio"] : " - ", $html);
    $html = str_replace("{bloque_piso}", $row["bloque_piso"] ? $row["bloque_piso"] : " - ", $html);
    $html = str_replace("{casa_apartamento}", $row["casa_apartamento"] ? $row["casa_apartamento"] : " - ", $html);
    $html = str_replace("{aldea_caserio}", $row["aldea_caserio"] ? $row["aldea_caserio"] : " - ", $html);
    $html = str_replace("{direccion_referencia}", $row["direccion_referencia"] ? $row["direccion_referencia"] : " - ", $html);
    $html = str_replace("{telefono_fijo}", $row["telefono"], $html);
    $html = str_replace("{telefono_movil}", $row["celular"], $html);
    $html = str_replace("{correo}", $row["email"], $html);

    if ($row["tipo_vivienda"] == "ALQUILA") {
        $html = str_replace("{alquila}", "x", $html);
        $html = str_replace("{propia}", "", $html);
        $html = str_replace("{hipotecada}", "", $html);
        $html = str_replace("{familiar}", "", $html);
        $html = str_replace("{cedida}", "", $html);

    } else if ($row["tipo_vivienda"] == "PROPIA") {
        $html = str_replace("{alquila}", "", $html);
        $html = str_replace("{propia}", "x", $html);
        $html = str_replace("{hipotecada}", "", $html);
        $html = str_replace("{familiar}", "", $html);
        $html = str_replace("{cedida}", "", $html);

    } else if ($row["tipo_vivienda"] == "HIPOTECADA") {
        $html = str_replace("{alquila}", "", $html);
        $html = str_replace("{propia}", "", $html);
        $html = str_replace("{hipotecada}", "x", $html);
        $html = str_replace("{familiar}", "", $html);
        $html = str_replace("{cedida}", "", $html);

    } else if ($row["tipo_vivienda"] == "FAMILIAR") {
        $html = str_replace("{alquila}", "", $html);
        $html = str_replace("{propia}", "", $html);
        $html = str_replace("{hipotecada}", "", $html);
        $html = str_replace("{familiar}", "x", $html);
        $html = str_replace("{cedida}", "", $html);

    } else if ($row["tipo_vivienda"] == "CEDIDA POR EMPRESA") {
        $html = str_replace("{alquila}", "", $html);
        $html = str_replace("{propia}", "", $html);
        $html = str_replace("{hipotecada}", "", $html);
        $html = str_replace("{familiar}", "", $html);
        $html = str_replace("{cedida}", "x", $html);
    } else {
        $html = str_replace("{alquila}", "", $html);
        $html = str_replace("{propia}", "", $html);
        $html = str_replace("{hipotecada}", "", $html);
        $html = str_replace("{familiar}", "", $html);
        $html = str_replace("{cedida}", "", $html);
    }

    $html = str_replace("{antiguedad_vivienda}", $row["antiguedad_vivienda"], $html);
    $html = str_replace("{nombreConyugue}", $row["nombre_conyuge"], $html);
    $html = str_replace("{telefonoConyugue}", $row["telefono_conyuge"], $html);

    if ($row["sexo"] == "MASCULINO") {
        $html = str_replace("{masculino}", "x", $html);
        $html = str_replace("{femenino}", "", $html);
    } else if ($row["sexo"] == "FEMENINO") {
        $html = str_replace("{masculino}", "", $html);
        $html = str_replace("{femenino}", "x", $html);
    } else {
        $html = str_replace("{masculino}", "", $html);
        $html = str_replace("{femenino}", "", $html);
    }


    // ----------------- tipo de cliente -------------------------------

    $tipo_cliente = strtoupper($pdf->TRIM($row["tipo_de_cliente"]));

    $listas_tipos_clientes = [
        "PEP",
        "Publico no PEP",
        "Privado",
        "Estudiante",
        "Ama de Casa",
        "Pensionado",
        "Independiente Formal",
        "Informal",
        "APNFD",
        "Registrada",
        "No Registrada",
        "Extranjero Residente",
        "Extranjero no Residente",
        "Otro"
    ];

    $grd_tipo_cliente = "";
    foreach ($listas_tipos_clientes as $item) {
        $active = strtoupper($pdf->TRIM($item)) == $tipo_cliente ? "x" : "";
        $grd_tipo_cliente .= "{$item}( {$active} ) &nbsp;&nbsp;";
    }

    $html = str_replace("{{grd_tipo_cliente}}", $grd_tipo_cliente, $html);


    // ----------------- tipo de empleo -------------------------------

    $tipo_empleo = strtoupper($pdf->TRIM($row["empresa_tipo_empleo"]));

    $lista_tipo_empleo = [
        "Asalariado",
        "Independiente",
        "Comerciante/Negocio Propio",
        "Otro"
    ];

    $grd_tipo_empleo = "";
    foreach ($lista_tipo_empleo as $item) {
        $active = strtoupper($item) == $tipo_empleo ? "x" : "";
        $grd_tipo_empleo .= "{$item}( {$active} ) &nbsp;&nbsp;";
    }

    $html = str_replace("{{grd_tipo_empleo}}", $grd_tipo_empleo, $html);

    // $html = str_replace("{empresa_tipo_empleo}", $row["empresa_tipo_empleo"], $html);

    // if($row["empresa_tipo_empleo"] == "ASALARIADO"){
    //     $html = str_replace("{asalariado}", "x", $html);
    //     $html = str_replace("{independiente}", "", $html);
    //     $html = str_replace("{comerciante}", "", $html);
    //     $html = str_replace("{otro}", "", $html);
    // } else if($row["empresa_tipo_empleo"] == "INDEPENDIENTE"){
    //     $html = str_replace("{asalariado}", "", $html);
    //     $html = str_replace("{independiente}", "x", $html);
    //     $html = str_replace("{comerciante}", "", $html);
    //     $html = str_replace("{otro}", "", $html);
    // } else if($row["empresa_tipo_empleo"] == "COMERCIANTE/NEGOCIO PROPIO"){
    //     $html = str_replace("{asalariado}", "", $html);
    //     $html = str_replace("{independiente}", "", $html);
    //     $html = str_replace("{comerciante}", "x", $html);
    //     $html = str_replace("{otro}", "", $html);
    // } else {
    //     $html = str_replace("{asalariado}", "", $html);
    //     $html = str_replace("{independiente}", "", $html);
    //     $html = str_replace("{comerciante}", "", $html);
    //     $html = str_replace("{otro}", "x", $html);
    // }

    $html = str_replace("{nombre_empresa}", $row["empresa"], $html);
    $html = str_replace("{direccion_empresa}", $row["empresa_direccion"], $html);
    $html = str_replace("{casa_matriz}", $row["empresa_casa_matriz"], $html);
    $html = str_replace("{empresa_departamento}", $row["empresa_departamento"], $html);
    $html = str_replace("{empresa_municipio}", $row["empresa_municipio"] ? $row["empresa_municipio"] : " - ", $html);
    $html = str_replace("{empresa_avenida}", $row["empresa_avenida"] ? $row["empresa_avenida"] : " - ", $html);
    $html = str_replace("{empresa_calle}", $row["empresa_calle"] ? $row["empresa_calle"] : " - ", $html);
    $html = str_replace("{empresa_sector_edificio}", $row["empresa_sector_edificio"] ? $row["empresa_sector_edificio"] : " - ", $html);
    $html = str_replace("{empresa_bloque_piso}", $row["empresa_bloque_piso"] ? $row["empresa_bloque_piso"] : " - ", $html);
    $html = str_replace("{empresa_casa_apartamento}", $row["empresa_casa_apartamento"] ? $row["empresa_casa_apartamento"] : " - ", $html);
    $html = str_replace("{empresa_salario}", $row["empresa_salario"] ? $row["empresa_salario"] : " - ", $html);
    $html = str_replace("{profesion}", $row["profesion"], $html);
    $html = str_replace("{ocupacion}", $row["empresa_puesto"], $html);
    $html = str_replace("{cargo}", $row["empresa_puesto"], $html);
    $html = str_replace("{empresa_telefono}", $row["empresa_telefono"], $html);
    $html = str_replace("{empleado_antiguedad}", $row["empleado_antiguedad"], $html);
    $html = str_replace("{empleado_actividad_economica}", $row["empleado_actividad_economica"], $html);
    $html = str_replace("{empleado_rango_mensual}", $row["empleado_rango_mensual"], $html);

    if ($row["empresa_propia"] == "SI") {
        $html = str_replace("{empresa_propia}", "x", $html);
        $html = str_replace("{empresa_no_propia}", "", $html);
    } else {
        $html = str_replace("{empresa_propia}", "", $html);
        $html = str_replace("{empresa_no_propia}", "x", $html);
    }


    // ----------------- tipo de ingresos -------------------------------

    $tipo_salario = strtoupper($pdf->TRIM($row["empresa_salario_tipo"]));
    $lista_tipo_salarios = [
        "JUBILACION O PENSION" => ["key" => "{jubilacion}", "value" => ""],
        "VENTAS BIENES MUEBLES/INMUEBLES" => ["key" => "{bienes_inmuebles}", "value" => ""],
        "REMESAS" => ["key" => "{remesas}", "value" => ""],
        "ALQUILERES" => ["key" => "{renta}", "value" => ""],
        "NEGOCIO PROPIO" => ["key" => "{negocio_propio}", "value" => ""],
        "AHORROS" => ["key" => "{ahorros}", "value" => ""],
        "HERENCIA" => ["key" => "{herencia}", "value" => ""],
        "PREMIOS DE LOTERIA" => ["key" => "{premios}", "value" => ""],
        "PRESTAMO INTERNO" => ["key" => "{prestamo_interno}", "value" => ""],
        "PRESTAMO EXTERNO" => ["key" => "{prestamo_externo}", "value" => ""]
    ];

    if (isset($lista_tipo_salarios[$tipo_salario])) {
        $lista_tipo_salarios[$tipo_salario]["value"] = "x";
    }

    foreach ($lista_tipo_salarios as $item) {
        $html = str_replace($item["key"], $item["value"], $html);
    }

    $html = str_replace("{prestamo_externo_especifique}", $row["prestamo_externo_especifique"], $html);
    $html = str_replace("{empresa_salario_otro}", $row["empresa_salario_otro"], $html);
    $html = str_replace("{empresa_salario_otro_tipo}", $row["empresa_salario_otro_tipo"], $html);

    // if($row["empresa_salario_tipo"] == "JUBILACION O PENSION"){
    //     $html = str_replace("{jubilacion}", "x", $html);
    //     $html = str_replace("{bienes_inmuebles}", "", $html);
    //     $html = str_replace("{remesas}", "", $html);
    //     $html = str_replace("{renta}", "", $html);
    //     $html = str_replace("{negocio_propio}", "", $html);
    //     $html = str_replace("{ahorros}", "", $html);
    //     $html = str_replace("{herencia}", "", $html);
    //     $html = str_replace("{premios}", "", $html);
    //     $html = str_replace("{prestamo_interno}", "", $html);
    //     $html = str_replace("{prestamo_externo}", "", $html);
    //     $html = str_replace("{prestamo_externo_especifique}", "", $html);
    //     $html = str_replace("{empresa_salario_otro}", "", $html);
    //     $html = str_replace("{empresa_salario_otro_tipo}", "", $html);

    // } else if($row["empresa_salario_tipo"] == "VENTAS BIENES MUEBLES/INMUEBLES"){
    //     $html = str_replace("{jubilacion}", "", $html);
    //     $html = str_replace("{bienes_inmuebles}", "x", $html);
    //     $html = str_replace("{remesas}", "", $html);
    //     $html = str_replace("{renta}", "", $html);
    //     $html = str_replace("{negocio_propio}", "", $html);
    //     $html = str_replace("{ahorros}", "", $html);
    //     $html = str_replace("{herencia}", "", $html);
    //     $html = str_replace("{premios}", "", $html);
    //     $html = str_replace("{prestamo_interno}", "", $html);
    //     $html = str_replace("{prestamo_externo}", "", $html);
    //     $html = str_replace("{prestamo_externo_especifique}", "", $html);
    //     $html = str_replace("{empresa_salario_otro}", "", $html);
    //     $html = str_replace("{empresa_salario_otro_tipo}", "", $html);

    // } else if($row["empresa_salario_tipo"] == "REMESAS") {
    //     $html = str_replace("{jubilacion}", "", $html);
    //     $html = str_replace("{bienes_inmuebles}", "", $html);
    //     $html = str_replace("{remesas}", "x", $html);
    //     $html = str_replace("{renta}", "", $html);
    //     $html = str_replace("{negocio_propio}", "", $html);
    //     $html = str_replace("{ahorros}", "", $html);
    //     $html = str_replace("{herencia}", "", $html);
    //     $html = str_replace("{premios}", "", $html);
    //     $html = str_replace("{prestamo_interno}", "", $html);
    //     $html = str_replace("{prestamo_externo}", "", $html);
    //     $html = str_replace("{prestamo_externo_especifique}", "", $html);
    //     $html = str_replace("{empresa_salario_otro}", "", $html);
    //     $html = str_replace("{empresa_salario_otro_tipo}", "", $html);

    // } else if($row["empresa_salario_tipo"] == "ALQUILERES"){
    //     $html = str_replace("{jubilacion}", "", $html);
    //     $html = str_replace("{bienes_inmuebles}", "", $html);
    //     $html = str_replace("{remesas}", "", $html);
    //     $html = str_replace("{renta}", "x", $html);
    //     $html = str_replace("{negocio_propio}", "", $html);
    //     $html = str_replace("{ahorros}", "", $html);
    //     $html = str_replace("{herencia}", "", $html);
    //     $html = str_replace("{premios}", "", $html);
    //     $html = str_replace("{prestamo_interno}", "", $html);
    //     $html = str_replace("{prestamo_externo}", "", $html);
    //     $html = str_replace("{prestamo_externo_especifique}", "", $html);
    //     $html = str_replace("{empresa_salario_otro}", "", $html);
    //     $html = str_replace("{empresa_salario_otro_tipo}", "", $html);

    // } else if($row["empresa_salario_tipo"] == "NEGOCIO PROPIO"){
    //     $html = str_replace("{jubilacion}", "", $html);
    //     $html = str_replace("{bienes_inmuebles}", "", $html);
    //     $html = str_replace("{remesas}", "", $html);
    //     $html = str_replace("{renta}", "", $html);
    //     $html = str_replace("{negocio_propio}", "x", $html);
    //     $html = str_replace("{ahorros}", "", $html);
    //     $html = str_replace("{herencia}", "", $html);
    //     $html = str_replace("{premios}", "", $html);
    //     $html = str_replace("{prestamo_interno}", "", $html);
    //     $html = str_replace("{prestamo_externo}", "", $html);
    //     $html = str_replace("{prestamo_externo_especifique}", "", $html);
    //     $html = str_replace("{empresa_salario_otro}", "", $html);
    //     $html = str_replace("{empresa_salario_otro_tipo}", "", $html);

    // } else if($row["empresa_salario_tipo"] == "AHORROS"){
    //     $html = str_replace("{jubilacion}", "", $html);
    //     $html = str_replace("{bienes_inmuebles}", "", $html);
    //     $html = str_replace("{remesas}", "", $html);
    //     $html = str_replace("{renta}", "", $html);
    //     $html = str_replace("{negocio_propio}", "", $html);
    //     $html = str_replace("{ahorros}", "x", $html);
    //     $html = str_replace("{herencia}", "", $html);
    //     $html = str_replace("{premios}", "", $html);
    //     $html = str_replace("{prestamo_interno}", "", $html);
    //     $html = str_replace("{prestamo_externo}", "", $html);
    //     $html = str_replace("{prestamo_externo_especifique}", "", $html);
    //     $html = str_replace("{empresa_salario_otro}", "", $html);
    //     $html = str_replace("{empresa_salario_otro_tipo}", "", $html);

    // } else if($row["empresa_salario_tipo"] == "HERENCIA") {
    //     $html = str_replace("{jubilacion}", "", $html);
    //     $html = str_replace("{bienes_inmuebles}", "", $html);
    //     $html = str_replace("{remesas}", "", $html);
    //     $html = str_replace("{renta}", "", $html);
    //     $html = str_replace("{negocio_propio}", "", $html);
    //     $html = str_replace("{ahorros}", "", $html);
    //     $html = str_replace("{herencia}", "x", $html);
    //     $html = str_replace("{premios}", "", $html);
    //     $html = str_replace("{prestamo_interno}", "", $html);
    //     $html = str_replace("{prestamo_externo}", "", $html);
    //     $html = str_replace("{prestamo_externo_especifique}", "", $html);
    //     $html = str_replace("{empresa_salario_otro}", "", $html);
    //     $html = str_replace("{empresa_salario_otro_tipo}", "", $html);

    // } else if($row["empresa_salario_tipo"] == "PREMIOS DE LOTERIA"){
    //     $html = str_replace("{jubilacion}", "", $html);
    //     $html = str_replace("{bienes_inmuebles}", "", $html);
    //     $html = str_replace("{remesas}", "", $html);
    //     $html = str_replace("{negocio_propio}", "", $html);
    //     $html = str_replace("{ahorros}", "", $html);
    //     $html = str_replace("{herencia}", "", $html);
    //     $html = str_replace("{premios}", "x", $html);
    //     $html = str_replace("{prestamo_interno}", "", $html);
    //     $html = str_replace("{prestamo_externo}", "", $html);
    //     $html = str_replace("{prestamo_externo_especifique}", "", $html);
    //     $html = str_replace("{empresa_salario_otro}", "", $html);
    //     $html = str_replace("{empresa_salario_otro_tipo}", "", $html);

    // } else if($row["empresa_salario_tipo"] == "PRESTAMO INTERNO"){
    //     $html = str_replace("{jubilacion}", "", $html);
    //     $html = str_replace("{bienes_inmuebles}", "", $html);
    //     $html = str_replace("{remesas}", "", $html);
    //     $html = str_replace("{renta}", "", $html);
    //     $html = str_replace("{negocio_propio}", "", $html);
    //     $html = str_replace("{ahorros}", "", $html);
    //     $html = str_replace("{herencia}", "", $html);
    //     $html = str_replace("{premios}", "", $html);
    //     $html = str_replace("{prestamo_interno}", "x", $html);
    //     $html = str_replace("{prestamo_externo}", "", $html);
    //     $html = str_replace("{prestamo_externo_especifique}", "", $html);
    //     $html = str_replace("{empresa_salario_otro}", "", $html);
    //     $html = str_replace("{empresa_salario_otro_tipo}", "", $html);

    // } else if($row["empresa_salario_tipo"] == "PRESTAMO EXTERNO"){
    //     $html = str_replace("{jubilacion}", "", $html);
    //     $html = str_replace("{bienes_inmuebles}", "", $html);
    //     $html = str_replace("{remesas}", "", $html);
    //     $html = str_replace("{renta}", "", $html);
    //     $html = str_replace("{negocio_propio}", "", $html);
    //     $html = str_replace("{ahorros}", "", $html);
    //     $html = str_replace("{herencia}", "", $html);
    //     $html = str_replace("{premios}", "", $html);
    //     $html = str_replace("{prestamo_interno}", "", $html);
    //     $html = str_replace("{prestamo_externo}", "x", $html);
    //     $html = str_replace("{prestamo_externo_especifique}", $row["prestamo_externo_especifique"], $html);
    //     $html = str_replace("{empresa_salario_otro}", "", $html);
    //     $html = str_replace("{empresa_salario_otro_tipo}", "", $html);

    // } else if($row["empresa_salario_tipo"] == "OTROS"){
    //     $html = str_replace("{jubilacion}", "", $html);
    //     $html = str_replace("{bienes_inmuebles}", "", $html);
    //     $html = str_replace("{remesas}", "", $html);
    //     $html = str_replace("{renta}", "", $html);
    //     $html = str_replace("{negocio_propio}", "", $html);
    //     $html = str_replace("{ahorros}", "", $html);
    //     $html = str_replace("{herencia}", "", $html);
    //     $html = str_replace("{premios}", "", $html);
    //     $html = str_replace("{prestamo_interno}", "", $html);
    //     $html = str_replace("{prestamo_externo}", "", $html);
    //     $html = str_replace("{prestamo_externo_especifique}", "", $html);
    //     $html = str_replace("{empresa_salario_otro}", $row["empresa_salario_otro"], $html);
    //     $html = str_replace("{empresa_salario_otro_tipo}", $row["empresa_salario_otro_tipo"], $html);

    // } else {
    //     $html = str_replace("{jubilacion}", "", $html);
    //     $html = str_replace("{bienes_inmuebles}", "", $html);
    //     $html = str_replace("{remesas}", "", $html);
    //     $html = str_replace("{renta}", "", $html);
    //     $html = str_replace("{negocio_propio}", "", $html);
    //     $html = str_replace("{ahorros}", "", $html);
    //     $html = str_replace("{herencia}", "", $html);
    //     $html = str_replace("{premios}", "", $html);
    //     $html = str_replace("{prestamo_interno}", "", $html);
    //     $html = str_replace("{prestamo_externo}", "", $html);
    //     $html = str_replace("{prestamo_externo_especifique}", "", $html);
    //     $html = str_replace("{empresa_salario_otro}", "", $html);
    //     $html = str_replace("{empresa_salario_otro_tipo}", "", $html);
    // }

    if ($row["origen_fondo_tercero"] == "SI") {
        $html = str_replace("{fondo_de_tercero}", "x", $html);
        $html = str_replace("{fondo_no_de_tercero}", "", $html);
        $html = str_replace("{monto_tercero}", $row["monto_tercero"], $html);
        $html = str_replace("{vinculo_tercero}", $row["vinculo_tercero"], $html);
        $html = str_replace("{nombre_tercero}", $row["nombre_tercero"], $html);
        $html = str_replace("{identificacion_tercero}", $row["identificacion_tercero"], $html);
        $html = str_replace("{actividad_tercero}", $row["actividad_economica_tercero"], $html);
        $html = str_replace("{ingreso_tercero}", $row["ingreso_mensual_tercero"], $html);
        $html = str_replace("{telefono_tercero}", $row["telefono_tercero"], $html);

    } else {
        $html = str_replace("{fondo_de_tercero}", "", $html);
        $html = str_replace("{fondo_no_de_tercero}", "x", $html);
        $html = str_replace("{monto_tercero}", $row["monto_tercero"], $html);
        $html = str_replace("{vinculo_tercero}", "", $html);
        $html = str_replace("{nombre_tercero}", "", $html);
        $html = str_replace("{identificacion_tercero}", "", $html);
        $html = str_replace("{actividad_tercero}", "", $html);
        $html = str_replace("{ingreso_tercero}", "", $html);
        $html = str_replace("{telefono_tercero}", "", $html);
    }

    $html = str_replace("{ref1_nombre}", $row["ref1_nombre"], $html);
    $html = str_replace("{ref1_relacion}", $row["ref1_relacion"], $html);
    $html = str_replace("{ref1_telefono_celular}", $row["ref1_telefono_celular"], $html);
    $html = str_replace("{ref2_nombre}", $row["ref2_nombre"], $html);
    $html = str_replace("{ref2_relacion}", $row["ref2_relacion"], $html);
    $html = str_replace("{ref2_telefono_celular}", $row["ref2_telefono_celular"], $html);
    $html = str_replace("{ref3_nombre}", $row["ref3_nombre"], $html);
    $html = str_replace("{ref3_relacion}", $row["ref3_relacion"], $html);
    $html = str_replace("{ref3_telefono_celular}", $row["ref3_telefono_celular"], $html);
    $html = str_replace("{ref4_nombre}", $row["ref4_nombre"], $html);
    $html = str_replace("{ref4_relacion}", $row["ref4_relacion"], $html);
    $html = str_replace("{ref4_telefono_celular}", $row["ref4_telefono_celular"], $html);

    if ($row["prevencion_lavado"] == "SI") {
        $html = str_replace("{siPrevencion}", "x", $html);
        $html = str_replace("{noPrevencion}", "", $html);
    } else {
        $html = str_replace("{siPrevencion}", "", $html);
        $html = str_replace("{noPrevencion}", "x", $html);
    }

    if ($row["acreditacion_ente"] == "SI") {
        $html = str_replace("{siAcreditacion}", "x", $html);
        $html = str_replace("{noAcreditacion}", "", $html);
    } else {
        $html = str_replace("{siAcreditacion}", "", $html);
        $html = str_replace("{noAcreditacion}", "x", $html);
    }

    require_once('lib/numletras.php');

    $html = str_replace("{cierre_plazo}", $row["plazo"], $html);
    $html = str_replace("{cierre_total_usd_letras}", numletras($row["monto_financiar"], 'LEMPIRAS'), $html);
    $html = str_replace("{cierre_total_usd}", formato_numero($row["monto_financiar"], 2, 'Lps. '), $html);
    $html = str_replace("{cierre_interes_mensual_letra}", numLetrasProcentaje($row["cierre_interes_mensual"]), $html);
    $html = str_replace("{cierre_interes_mensual}", $row["cierre_interes_mensual"], $html);
    $html = str_replace("{cierre_total_usd_contado}", formato_numero($row["monto_prestamo"], 2, 'Lps. '), $html);
    $html = str_replace("{cierre_total_seguro_usd}", formato_numero($row["monto_seguro"], 2, 'Lps. '), $html);
    $html = str_replace("{cierre_total_prima_usd}", formato_numero($row["monto_prima"], 2, 'Lps. '), $html);
    $html = str_replace("{costo_rtn}", formato_numero($row["costo_rtn"], 2, 'Lps. '), $html);
    $html = str_replace("{gastos_administrativos}", formato_numero($row["gastos_administrativos"], 2, 'Lps. '), $html);
    $html = str_replace("{empresa_salario_otro}", formato_numero($row["empresa_salario_otro"], 2, 'Lps. '), $html);
    $html = str_replace("{cierre_cuota_cantidad}", $row["plazo"], $html);
    $html = str_replace("{cierre_cuota_total_usd}", formato_numero($row["cuota"], 2, 'Lps. '), $html);

    $pdf->writeHTML($html, true, false, true, false, '');
    ob_end_clean();

    $pdf->Output('portada_' . $row["numero"] . '.pdf', 'I');

} else {
    echo "Error - Portada de la solicitud no disponible ";
}
?>