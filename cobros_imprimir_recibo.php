<?php
  
require_once ('include/protect.php');
require_once ('include/framework.php');  
  
require_once('lib/tcpdf/config/lang/spa.php');
require_once('lib/tcpdf/tcpdf.php');  


if (isset($_REQUEST['cid'])) { $solicitud_id = ($_REQUEST['cid']) ; } else	{exit ;}

if (!tiene_permiso(27)) { echo mensaje("No tiene privilegios para accesar esta seccion","danger");exit;}

$conn = new mysqli(db_ip, db_user, db_pw, db_name);
if (mysqli_connect_errno()) {  echo mensaje("Error al Conectar a la Base de Datos [DB:101]","danger");exit;}
$conn->set_charset("utf8");


	$sql = " select *
	       FROM recibo ";
	$sql.= sqladd (' where id = ',$conn->real_escape_string($solicitud_id), "int");

	$result = $conn -> query($sql);

	if ($result->num_rows > 0) {
			
		$row = mysqli_fetch_array($result) ;


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
$pdf->SetTitle('RECIBO');
$pdf->SetSubject('Recibo');
$pdf->SetKeywords('Recibo');

$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(16, 5, 16);
$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(0);

$pdf->setPrintFooter(false);
$pdf->setPrintHeader(false);

$pdf->SetAutoPageBreak(TRUE, 15);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);



// ---------------------------------------------------------

$margenizq=7;

$pdf->SetFont('helvetica', '', 10); //courier     times 



$pdf->AddPage();

	$bMargin = $pdf->getBreakMargin();
	$auto_page_break = $pdf->getAutoPageBreak();
    $pdf->SetAutoPageBreak(false, 0);
    $pdf->SetAutoPageBreak($auto_page_break, $bMargin);
    $pdf->setPageMark();

    $html = '
<table width="100%" border="0" cellpadding="0" cellspacing="6">
  <tr>
    <td width="19%"><img src="images/logo.jpg" width="166" height="78" /></td>
    <td colspan="2" align="center" valign="middle"><h4>Motos y Vehiculos S. A.<br />
      Barrio la Guardia, 1 y 3 ae. 16 Calle S. O. San Pedro Sula<br />
      Telefonos: 2557-7916, 2557-7892</h4></td>
  </tr>

</table>
<hr width="100%" size="2" noshade="noshade" />
<table width="100%" border="0" cellspacing="6" cellpadding="0">
  <tr>
    <td width="19%">&nbsp;</td>
    <td width="60%" align="center">&nbsp;</td>
    <td width="21%">&nbsp;</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td align="center"><h2>R E C I B O</h2></td>
    <td align="center"><strong>ORIGINAL</strong></td>
  </tr>
  <tr>
    <td colspan="3"><table width="100%" border="0" cellspacing="0" cellpadding="6">
      <tr>
        <td width="70%"> <strong>Recibo # {numero} </strong> </td>
        <td width="30%" align="right">Fecha {fecha}</td>
      </tr>
      <tr>
        <td> <strong>Cliente: {cliente} </strong> </td>
        <td align="right">&nbsp;</td>
      </tr>
      <tr>
        <td valign="middle">Facturas pagadas:<br />
          {facturas_pagadas}</td>
        <td align="right" valign="middle">{forma_pago}</td>
      </tr>
      <tr>
        <td>
        Saldo Actual: {saldo_actual}<br><br>
        Autor: {usuario_alta} </td>
        <td align="right" class="style1"> <strong>Total Recibo</strong> {monto} </td>
      </tr>
      <tr>
        <td>Comentarios: {comentarios} </td>
        <td>&nbsp;</td>
      </tr>
    </table>
      <p>&nbsp;</p>
    <p>&nbsp;</p></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td align="center"><p>&nbsp;</p>
    <p>__________________________________<br />
    Firma</p></td>
    <td>&nbsp;</td>
  </tr>
</table>';
    
    

 
    $html = str_replace("{numero}",$row["numero"],$html);
    $html = str_replace("{cliente}",$row["cliente_nombre"],$html);
    $html = str_replace("{usuario_alta}",$row["usuario_alta"],$html);
    $html = str_replace("{comentarios}",$row["comentarios"],$html);
    $html = str_replace("{fecha}",fechademysql($row["fecha_alta"]),$html);
    
    $fpagadas='<table width="100%" border="1" cellspacing="0" cellpadding="3">
            <tr>
              <td>Factura</td>
              <td>Plazo</td>
              <td>Capital</td>
              <td>Intereses</td>
              <td>Intereses Moratorios </td>
              <td>Total</td>
            </tr>
         ';
    
        $result3 = $conn -> query("select * from recibo_detalle where recibo_id=".$conn->real_escape_string($solicitud_id));
        if ($result3 -> num_rows > 0) {
            while ($row3 = $result3 -> fetch_assoc()) {
                
                 $fpagadas.='
                    <tr>
                      <td align="center" nowrap="nowrap">'.$row3["numero_documento"].'</td>
                      <td align="center">'.$row3["plazo"].'</td>
                      <td align="right">'.formato_numero($row3["capital"],2,'').'</td>
                      <td align="right">'.formato_numero($row3["interes"],2,'').'</td>
                      <td align="right">'.formato_numero($row3["mora"],2,'').'</td>
                      <td align="right">'.formato_numero($row3["monto"],2,'').'</td>
                    </tr>
                  ';
            }
         }
        
        $fpagadas.='</table>';
        
    $html = str_replace("{facturas_pagadas}",$fpagadas,$html);

    $fpago="";
    if ($row["efe_monto"]>0) { $fpago.="Efectivo &nbsp;&nbsp;".$row["efe_monto"]."<br>"; } 
    if ($row["tc_monto"]>0) { $fpago.="Tarjeta de Credito &nbsp;&nbsp;".$row["tc_monto"]."<br>"; } 
    if ($row["tb_monto"]>0) { $fpago.="Transferencia Bancaria &nbsp;&nbsp;".$row["tb_monto"]."<br>"; } 
    if ($row["chk_monto"]>0) { $fpago.="Cheque &nbsp;&nbsp;".$row["chk_monto"]."<br>"; } 
    
    $html = str_replace("{forma_pago}",$fpago,$html);
    
    $html = str_replace("{saldo_actual}",formato_numero($row["saldo_actual"],2,'LPS '),$html);
    
    
    $html = str_replace("{monto}",formato_numero($row["monto"],2,'LPS '),$html);
    

$pdf->writeHTML($html, true, false, true, false, '');




// ---------------------------------------------------------

$pdf->Output('Recibo_'.$row["numero"].'.pdf', 'I'); //D = descargar


} else { echo "Error - Recibo no disponible";}

?>