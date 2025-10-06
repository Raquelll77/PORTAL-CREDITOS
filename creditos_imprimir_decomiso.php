<?php
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


$sql = " select *
	       FROM prestamo ";
$sql .= sqladd(' where id = ', $conn->real_escape_string($solicitud_id), "int");

$result = $conn->query($sql);

if ($result->num_rows > 0) {

    $row = mysqli_fetch_array($result);

    //Extend the TCPDF class to create custom Header and Footer
    class MYPDF extends TCPDF
    {
        //Page header
        public function Header()
        {
            
        }
    }

    ob_start();
    error_reporting(E_ALL & ~E_NOTICE);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);

    // create new PDF document
    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false);

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('MOVESA');
    $pdf->SetTitle('DOCUMENTOS');
    $pdf->SetSubject('Documentos');
    $pdf->SetKeywords('Carta, Poder, Documento');

    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(25, 10, 25);
    $pdf->SetHeaderMargin(0);
    $pdf->SetFooterMargin(0);

    $pdf->setPrintFooter(false);
    $pdf->setPrintHeader(false);

    $pdf->SetAutoPageBreak(TRUE, 15);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    $margenizq = 7;

    $pdf->SetFont('helvetica', '', 12); //courier     times 

    $pdf->AddPage();

    $bMargin = $pdf->getBreakMargin();
    $auto_page_break = $pdf->getAutoPageBreak();
    $pdf->SetAutoPageBreak(false, 0);

    $pdf->Image('images/formato_decomiso.jpg', 0, 0, 216, 279, '', '', '', false, 300, '', false, false, 0);

    $pdf->SetAutoPageBreak($auto_page_break, $bMargin);
    $pdf->setPageMark();

 
    $html = file_get_contents("include/plantillas/decomiso.html");

    if ($html === false) {
        exit("Error al leer la plantilla.");
    }

    $html = str_replace("{nombre_completo}", $row["nombres"] . " " . $row["apellidos"], $html);
    $html = str_replace("{identidad}", $row["identidad"], $html);
    $html = str_replace("{direccion}", $row["direccion"], $html);
    $html = str_replace("{cierre_firma_fecha}", $row["cierre_firma_fecha"], $html);
    $html = str_replace("{moto_marca}", $row['moto_marca'], $html);
    $html = str_replace("{moto_modelo}", $row["moto_modelo"], $html);
    $html = str_replace("{moto_serie}",$row["moto_serie"],$html);
    require_once('lib/numletras.php');

    $html = str_replace("{moto_motor}",$row["moto_motor"],$html);
    $html = str_replace("{moto_color}",$row["moto_color"],$html);
    $html = str_replace("{moto_ano}",$row["moto_ano"],$html);
    $html = str_replace("{moto_cilindraje}", $row["moto_cilindraje"], $html);
    $html = str_replace("{categoria}", mayus_string($row["moto_categoria"]), $html);

    $pdf->writeHTML($html, true, false, true, false, '');

    ob_end_clean();

    $pdf->Output('documentos_'.$row["numero"].'.pdf', 'I');
} else 
{ 
    echo "Error - Carta Poder no disponible ";
}