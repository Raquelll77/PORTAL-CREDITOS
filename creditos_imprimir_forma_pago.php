<?php

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);
ob_start();

require_once('include/protect.php');
require_once('include/framework.php');

require_once('lib/tcpdf/config/lang/spa.php');
require_once('lib/tcpdf/tcpdf.php');


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


// ----------------- DATOS DE PRESTAMOS -------------------------
$sql = "SELECT * FROM prestamo WHERE id = " . $conn->real_escape_string($solicitud_id);
$result = $conn->query($sql);

if ($result->num_rows > 0) {

    $informacion_prestamo = mysqli_fetch_array($result);

    // ----------------- DATOS DE CLIENTE -------------------------

    $API_key = "@B1F5E814-4";
    $dni = $informacion_prestamo["identidad"];
    $url = "http://web.grupomovesa.com/portal/modulo_creditos/services/portalCreditosAPI.services.php";
    $url .= "?request=obtener_datos_cliente_por_identidad&dni={$dni}&token={$API_key}";
    $response = file_get_contents($url);

    $informacion_cliente = [];
    if ($response !== false) {
        $informacion_cliente = json_decode($response, true);
    }


    // Extend the TCPDF class to create custom Header and Footer
    class MYPDF extends TCPDF
    {
        //Page header
        public function Header()
        {
        }
    }


    // create new PDF document
    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LEGAL', true, 'UTF-8', false);

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('MOVESA');
    $pdf->SetTitle('DOCUMENTOS');
    $pdf->SetSubject('Documentos');
    $pdf->SetKeywords('Contrato, Pagare, Traspaso');

    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(20, 10, 30);
    $pdf->SetHeaderMargin(0);
    $pdf->SetFooterMargin(0);

    $pdf->setPrintFooter(false);
    $pdf->setPrintHeader(false);

    $pdf->SetAutoPageBreak(TRUE, 15);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // ---------------------------------------------------------

    $margenizq = 7;
    $pdf->SetFont('helvetica', '', 10); //courier     times 


    $pdf->AddPage();

    $bMargin = $pdf->getBreakMargin();
    $auto_page_break = $pdf->getAutoPageBreak();
    $pdf->SetAutoPageBreak(false, 0);
    $pdf->SetAutoPageBreak($auto_page_break, $bMargin);
    $pdf->setPageMark();

    $html = file_get_contents("include/plantillas/formaPago.html");

    $codigo_cliente = empty($informacion_cliente["CARDCODE"]) ? "(no encontrado)" : $informacion_cliente["CARDCODE"];
    $nombre_cliente = $informacion_prestamo["nombres"] . " " . $informacion_prestamo["apellidos"];

    $html = str_replace("{{dni_cliente}}", $informacion_prestamo["identidad"], $html);
    $html = str_replace("{{codigo_cliente}}", $codigo_cliente, $html);
    $html = str_replace("{{nombre_cliente}}", $nombre_cliente, $html);

    $cuota_mostrar = $informacion_prestamo["cuota"]; // valor por defecto

    if (!empty($informacion_prestamo["aplica_promocion_octubre"]) && $informacion_prestamo["aplica_promocion_octubre"] == 1) {
        // Si aplica promoción, usar la cuota promocional
        if (!empty($informacion_prestamo["cuota_promocion_octubre"])) {
            $cuota_mostrar = $informacion_prestamo["cuota_promocion_octubre"];
        }
    }

    $html = str_replace("{{total_prestamo}}", number_format($informacion_prestamo["monto_financiar"], 2), $html);
    $html = str_replace("{{plazo}}", $informacion_prestamo["plazo"], $html);
    $html = str_replace("{{cuota_mensual}}", number_format($cuota_mostrar, 2), $html);
    $html = str_replace("{{dia_pago}}", intval($informacion_prestamo["cierre_cuota_dia_pago"]), $html);


    $meses = [
        1 => 'enero',
        'febrero',
        'marzo',
        'abril',
        'mayo',
        'junio',
        'julio',
        'agosto',
        'septiembre',
        'octubre',
        'noviembre',
        'diciembre'
    ];

    $fecha_primera_cuota = new DateTime($informacion_prestamo["cierre_cuota_primera"]);
    // Formatear la fecha
    $dia = $fecha_primera_cuota->format('d');
    $mes = $meses[(int) $fecha_primera_cuota->format('m')];
    $anio = $fecha_primera_cuota->format('Y');

    $html = str_replace("{{fecha_primera_cuota}}", "{$dia} {$mes} {$anio}", $html);

    $pdf->writeHTML($html, true, false, true, false, '');


    // ---------------------------------------------------------

    $pdf->Output("documentos_{$solicitud_id}.pdf", 'I'); //D = descargar


} else {
    echo "Error - Contratos no disponible";
}

// Tu código que podría generar warnings aquí
