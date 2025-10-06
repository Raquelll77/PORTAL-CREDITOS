<?php
  
require_once ('include/protect.php');
require_once ('include/framework.php');  
  
require_once('lib/tcpdf/config/lang/spa.php');
require_once('lib/tcpdf/tcpdf.php');  


if (isset($_REQUEST['g'])) { $garantia = $_REQUEST['g']; } else	{exit ;}

$conn = new mysqli(db_ip, db_user, db_pw, db_name);
if (mysqli_connect_errno()) {  echo mensaje("Error al Conectar a la Base de Datos [DB:101]","danger");exit;}
$conn->set_charset("utf8");


	$sql = "select id as numero,distribuidor_nombre,bodega_nombre,fecha_compra,factura_numero,usuario_alta,nombres,apellidos
			,identidad,direccion,telefono,celular,sexo,marca,estilo,modelo,color,serie_motor,serie_chasis
			,rodaje
	       FROM garantia ";
	$sql.= sqladd (' where id = ',$conn->real_escape_string($garantia), "int");

	$result = $conn -> query($sql);

	if ($result->num_rows > 0) {
			
		$row = mysqli_fetch_array($result) ;

$rodaje="2";
if ($row["rodaje"]=="4") {$rodaje="4";}

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {
    //Page header
    public function Header() {
       
    }
}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('MOVESA');
$pdf->SetTitle('GARANTIA');
$pdf->SetSubject('Certificado de Garantia');
$pdf->SetKeywords('Certificado, Garantia');

$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(0);

$pdf->setPrintFooter(false);
$pdf->setPrintHeader(false);

$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);



// ---------------------------------------------------------

// PAGINA 1
$pdf->AddPage();

	$bMargin = $pdf->getBreakMargin();
	$auto_page_break = $pdf->getAutoPageBreak();
    $pdf->SetAutoPageBreak(false, 0);
    $img_file = K_PATH_IMAGES.$rodaje.'r_garantia.jpg';
    $pdf->Image($img_file, 0, 0, 215, 278, '', '', '', true, 300, '', false, false, 0);
    $pdf->SetAutoPageBreak($auto_page_break, $bMargin);
    $pdf->setPageMark();

// $html = 'Hola Hola Hola Hola';
// $pdf->writeHTML($html, true, false, true, false, '');


$margenizq=7;

$pdf->SetFont('helvetica', '', 8); //courier     times 



$lineax=27;
$pdf->SetXY($margenizq+164,$lineax,true);
$pdf->Cell(0, 0, $row["numero"] , 0, false, 'L', 0, '', 0, false, 'M', 'M');

$lineax=42;
$pdf->SetXY($margenizq,$lineax,true);
$pdf->Cell(0, 0, $row["distribuidor_nombre"].' / '.$row["bodega_nombre"], 0, false, 'L', 0, '', 0, false, 'M', 'M');
$pdf->SetXY($margenizq+90,$lineax,true);
$pdf->Cell(0, 0, fechademysql($row["fecha_compra"]) , 0, false, 'L', 0, '', 0, false, 'M', 'M');
$pdf->SetXY($margenizq+130,$lineax,true);
$pdf->Cell(0, 0, $row["factura_numero"] , 0, false, 'L', 0, '', 0, false, 'M', 'M');
$pdf->SetXY($margenizq+160,$lineax,true);
$pdf->Cell(0, 0, $row["usuario_alta"] , 0, false, 'L', 0, '', 0, false, 'M', 'M');


$lineax=57;
$pdf->SetXY($margenizq,$lineax,true);
$pdf->Cell(0, 0, $row["nombres"] . ' '. $row["apellidos"], 0, false, 'L', 0, '', 0, false, 'M', 'M');
$pdf->SetXY($margenizq+110,$lineax,true);
$pdf->Cell(0, 0, $row["identidad"] , 0, false, 'L', 0, '', 0, false, 'M', 'M');
$pdf->SetXY($margenizq+160,$lineax,true);
$pdf->Cell(0, 0, $row["sexo"] , 0, false, 'L', 0, '', 0, false, 'M', 'M');

$lineax=66;
$pdf->SetXY($margenizq,$lineax,true);
$pdf->Cell(0, 0, $row["direccion"] , 0, false, 'L', 0, '', 0, false, 'M', 'M');
$pdf->SetXY($margenizq+110,$lineax,true);
$pdf->Cell(0, 0, $row["telefono"] , 0, false, 'L', 0, '', 0, false, 'M', 'M');
$pdf->SetXY($margenizq+160,$lineax,true);
$pdf->Cell(0, 0, $row["celular"] , 0, false, 'L', 0, '', 0, false, 'M', 'M');

$lineax=86;
$pdf->SetXY($margenizq,$lineax,true);
$pdf->Cell(0, 0, $row["marca"] , 0, false, 'L', 0, '', 0, false, 'M', 'M');
$pdf->SetXY($margenizq+32,$lineax,true);
$pdf->Cell(0, 0, $row["estilo"] , 0, false, 'L', 0, '', 0, false, 'M', 'M');
$pdf->SetXY($margenizq+85,$lineax,true);
$pdf->Cell(0, 0, $row["modelo"] , 0, false, 'L', 0, '', 0, false, 'M', 'M');
$pdf->SetXY($margenizq+110,$lineax,true);
$pdf->Cell(0, 0, $row["color"] , 0, false, 'L', 0, '', 0, false, 'M', 'M');

$lineax=96;
$pdf->SetXY($margenizq,$lineax,true);
$pdf->Cell(0, 0, $row["serie_motor"] , 0, false, 'L', 0, '', 0, false, 'M', 'M');
$pdf->SetXY($margenizq+85,$lineax,true);
$pdf->Cell(0, 0, $row["serie_chasis"] , 0, false, 'L', 0, '', 0, false, 'M', 'M');



// PAGINA 2
$pdf->AddPage();

	$bMargin = $pdf->getBreakMargin();
	$auto_page_break = $pdf->getAutoPageBreak();
	$pdf->SetAutoPageBreak(false, 0);
	$img_file = K_PATH_IMAGES.$rodaje.'r_control.png';
	$pdf->Image($img_file, 0, 0, 212, 278, '', '', '', true, 300, '', false, false, 0);
	$pdf->SetAutoPageBreak($auto_page_break, $bMargin);
	$pdf->setPageMark();


// ---------------------------------------------------------

$pdf->Output('garantia.pdf', 'I'); //D = descargar


} else { echo "Error - Garantia no disponible";}

?>