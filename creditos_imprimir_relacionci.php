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
    $pdf->SetKeywords('Relacion, CI, Documento');

    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(16, 5, 16);
    $pdf->SetHeaderMargin(0);
    $pdf->SetFooterMargin(0);

    $pdf->setPrintFooter(false);
    $pdf->setPrintHeader(false);

    $pdf->SetAutoPageBreak(TRUE, 15);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    $margenizq = 7;

    $pdf->SetFont('helvetica', '', 10); //courier     times 

    $pdf->AddPage();

    $bMargin = $pdf->getBreakMargin();
    $auto_page_break = $pdf->getAutoPageBreak();
    $pdf->SetAutoPageBreak(false, 0);
    $pdf->SetAutoPageBreak($auto_page_break, $bMargin);
    $pdf->setPageMark();

    $html = file_get_contents("include/plantillas/relacionCI.html");

    if ($html === false) {
        exit("Error al leer la plantilla.");
    }

    $html = str_replace("{logo}", "images/logo.jpg", $html);
    $html = str_replace("{numero}", $row['numero'], $html);
    $html = str_replace("{cliente_nombre}", $row["nombres"] . " " . $row["apellidos"], $html);
    $html = str_replace("{cliente_identidad}",$row["identidad"],$html);
    $html = str_replace("{cierre_firma_fecha}", $row["cierre_firma_fecha"], $html);
    require_once('lib/numletras.php');

    $html = str_replace("{moto_valor}",formato_numero($row["moto_valor"],2,'Lps. '),$html);
    $html = str_replace("{monto_financiar}",formato_numero($row["monto_financiar"],2,'Lps. '),$html);
    $html = str_replace("{monto_prima}",formato_numero($row["monto_prima"],2,'Lps. '),$html);
    $html = str_replace("{plazo}", $row["plazo"], $html);
    $html = str_replace("{cuota_mensual}", formato_numero($row["cuota"], 2, 'Lps. '), $html);
    $html = str_replace("{monto_prestamo}", formato_numero($row["monto_prestamo"], 2, 'Lps. '), $html);

    $pdf->writeHTML($html, true, false, true, false, '');

    ob_end_clean();

    $pdf->Output('documentos_'.$row["numero"].'.pdf', 'I');
} else 
{ 
    echo "Error - Relacion CI no disponible ";
}