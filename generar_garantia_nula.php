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
			, nulo ,  nulo_fecha ,  nulo_usuario ,  nulo_numero ,  nulo_motivo
	       FROM garantia ";
	$sql.= sqladd (' where id = ',$conn->real_escape_string($garantia), "int");

	$result = $conn -> query($sql);

	if ($result->num_rows > 0) {
			
		$row = mysqli_fetch_array($result) ;

 if ($row["nulo"]=="NO" ) {echo "Esta garantia no se encuentra Nula"; exit; }

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
$pdf->SetSubject('Anulacion de Garantia');
$pdf->SetKeywords('Certificado, Garantia, Anulacion');

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

    $pdf->SetAutoPageBreak($auto_page_break, $bMargin);
    $pdf->setPageMark();
 
   // <tr>
    // <td width="24%" align="left">Motivo</td>
    // <td width="76%" align="left">'.$row["nulo_motivo"].'</td>
  // </tr>
$html = '<table width="100%" border="0" cellspacing="0" cellpadding="2">
  <h2 align="center">ANULACION DE GARANTIA</h2>
  <tr>
    <td width="24%" align="left">Numero</td>
    <td width="76%" align="left">'.$row["nulo_numero"].'</td>
  </tr>
  <tr>
    <td width="24%" align="left">Fecha de Anulacion</td>
    <td width="76%" align="left">'.fechademysql($row["nulo_fecha"]).'</td>
  </tr> 
  <tr>
    <td width="24%" align="left">Anulado por</td>
    <td width="76%" align="left">'.$row["nulo_usuario"].'</td>
  </tr> 

   <tr>
    <td width="24%" align="left">&nbsp;</td>
    <td width="76%" align="left">&nbsp;</td>
  </tr>
  <tr>
    <td width="24%" align="left" bgcolor="#F3F3F3">&nbsp;</td>
    <td width="76%" align="left" bgcolor="#F3F3F3">Datos de la garantia</td>
  </tr>
    <tr>
    <td width="24%" align="left">&nbsp;</td>
    <td width="76%" align="left">&nbsp;</td>
  </tr>
  <tr>
    <td width="24%" align="left">No. Garantia</td>
    <td width="76%" align="left">'.$row["numero"].'</td>
  </tr>
  <tr>
    <td width="24%" align="left">Distribuidor</td>
    <td width="76%" align="left">'.$row["distribuidor_nombre"].' / '.$row["bodega_nombre"].'</td>
  </tr>
  <tr>
    <td width="24%" align="left">Fecha Compra</td>
    <td width="76%" align="left">'.fechademysql($row["fecha_compra"]).'</td>
  </tr>
  <tr>
    <td width="24%" align="left">No. Factura</td>
    <td width="76%" align="left">'.$row["factura_numero"].'</td>
  </tr>
  <tr>
    <td width="24%" align="left">Usuario</td>
    <td width="76%" align="left">'.$row["usuario_alta"].'</td>
  </tr>
  <tr>
    <td width="24%" align="left">Cliente</td>
    <td width="76%" align="left">'.$row["identidad"].'</td>
  </tr>
    <tr>
    <td width="24%" align="left">&nbsp;</td>
    <td width="76%" align="left">&nbsp;</td>
  </tr>
  <tr>
    <td width="24%" align="left" bgcolor="#F3F3F3">&nbsp;</td>
    <td width="76%" align="left" bgcolor="#F3F3F3">Datos del vehiculo</td>
  </tr>
    <tr>
    <td width="24%" align="left">&nbsp;</td>
    <td width="76%" align="left">&nbsp;</td>
  </tr>
  <tr>
    <td width="24%" align="left">Marca</td>
    <td width="76%" align="left">'.$row["marca"].'</td>
  </tr>
  <tr>
    <td width="24%" align="left">Estilo</td>
    <td width="76%" align="left">'.$row["estilo"].'</td>
  </tr>
  <tr>
    <td width="24%" align="left">Modelo</td>
    <td width="76%" align="left">'.$row["modelo"].'</td>
  </tr>
  <tr>
    <td width="24%" align="left">Color</td>
    <td width="76%" align="left">'.$row["color"].'</td>
  </tr>
  <tr>
    <td width="24%" align="left">Serie Motor</td>
    <td width="76%" align="left">'.$row["serie_motor"].'</td>
  </tr>
  <tr>
    <td width="24%" align="left">serie Chasis</td>
    <td width="76%" align="left">'.$row["serie_chasis"].'</td>
  </tr>
  
  
  </table>
';


$pdf->writeHTML($html, true, false, true, false, '');

$pdf->Image('images/logo.png', 85,  10, 40, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);

// ---------------------------------------------------------

$pdf->Output('garantia.pdf', 'I'); //D = descargar


} else { echo "Error - Registro no disponible";}

?>