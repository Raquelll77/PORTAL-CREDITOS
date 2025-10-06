<?php
  
require_once ('include/protect.php');
require_once ('include/framework.php');  
  
require_once('lib/tcpdf/config/lang/spa.php');
require_once('lib/tcpdf/tcpdf.php');  


if (isset($_REQUEST['cid'])) { $solicitud_id = ($_REQUEST['cid']) ; } else  {exit ;}

$conn = new mysqli(db_ip, db_user, db_pw, db_name);
if (mysqli_connect_errno()) {  echo mensaje("Error al Conectar a la Base de Datos [DB:101]","danger");exit;}
$conn->set_charset("utf8");


    $sql = " select *
           FROM prestamo ";
    $sql.= sqladd (' where id = ',$conn->real_escape_string($solicitud_id), "int");

    $result = $conn -> query($sql);

    if ($result->num_rows > 0) {
            
        $row = mysqli_fetch_array($result) ;

$sqlAval = "
    SELECT 
        CONCAT(nombres, ' ', apellidos) AS nombrecompleto,
        estado_civil,
        identidad,
        ciudad,
        departamento
    FROM prestamo_aval
    WHERE nombres IS NOT NULL 
      AND id = '".$conn->real_escape_string($solicitud_id)."'
    LIMIT 1
";

    $resultAval = $conn->query($sqlAval);

    $clausulaAval = "";
    if ($resultAval && $resultAval->num_rows > 0) {
        $aval = $resultAval->fetch_assoc();

        $clausulaAval = "m) </strong>FIADOR</strong> SOLIDARIO, PRINCIPAL PAGADOR Y CODEUDOR:
        Comparece <b>{$aval['nombrecompleto']}</b>, mayor de edad, {$aval['estado_civil']}, hondureño, 
        con Tarjeta de Identidad No. {$aval['identidad']}, con domicilio en {$aval['ciudad']}, {$aval['departamento']}, 
        quien en este acto se constituye <b><strong>FIADOR</strong> SOLIDARIO, PRINCIPAL PAGADOR Y CODEUDOR</b> de todas las obligaciones 
        que EL <strong>ARRENDATARIO</strong> asume en este contrato a favor de <strong>EL ARRENDADOR</strong> SKG, S.A. de C.V., incluyendo —de manera enunciativa 
        y no limitativa— el pago íntegro del precio pactado, intereses ordinarios y moratorios, comisiones, costas, gastos, 
        multas, daños y perjuicios, gastos de cobranza prejudicial y judicial, honorarios profesionales y cualesquiera otros cargos 
        derivados del presente contrato, sus anexos y modificaciones.
        <strong>El FIADOR</strong> declara que su obligación es solidaria, indivisible, irrevocable y de plazo igual al del deudor principal, 
        prorrogable automáticamente con las prórrogas del contrato, y renuncia expresamente a los beneficios de excusión, división y orden. 
        <strong>El FIADOR</strong> acepta que <strong>EL ARRENDADOR</strong> pueda exigirle total o parcialmente el cumplimiento sin necesidad de requerir o ejecutar primero 
        al <strong>ARRENDATARIO</strong> ni al bien arrendado.
        <strong>El FIADOR</strong> reconoce válidas las modificaciones que las partes principales acuerden sobre plazos, cuotas, intereses o reestructuraciones, 
        sin necesidad de su notificación o consentimiento previo, manteniéndose su responsabilidad hasta la extinción total de la deuda. 
        El FIADOR autoriza la compensación de saldos a su favor con cualquier obligación derivada de este contrato, así como la cesión 
        de los derechos de crédito por parte de <strong>EL ARRENDADOR</strong>.
        Para todos los efectos, <strong>FIADOR, ARRENDATARIO y ARRENDADOR</strong> señalan como domicilios contractuales los indicados en este instrumento 
        y aceptan que las notificaciones remitidas a esos domicilios o correos producirán plenos efectos. 
        <strong>El FIADOR</strong> declara haber leído y comprendido este contrato y firma en señal de aceptación.
        ";

        $firmaAval = "
        
            ____________________________<br>
            {$aval['nombrecompleto']}<br>
            <strong>EL FIADOR</strong><br>
            IDENT. No. {$aval['identidad']}<br>
        
    ";
    }


// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {
    //Page header
    public function Header() {
       
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



// PAGINA 1 CONTRATO
$pdf->AddPage();

    $bMargin = $pdf->getBreakMargin();
    $auto_page_break = $pdf->getAutoPageBreak();
    $pdf->SetAutoPageBreak(false, 0);
    $pdf->SetAutoPageBreak($auto_page_break, $bMargin);
    $pdf->setPageMark();

    $html = file_get_contents("include/plantillas/contrato.html");
    
 
    $html = str_replace("{cliente_nombre}",$row["nombres"]." ".$row["apellidos"],$html);
    $html = str_replace("{cliente_estado_civil}",$row["estado_civil"],$html);
    $html = str_replace("{cliente_identidad}",$row["identidad"],$html);
    $html = str_replace("{moto_categoria}", mayus_string($row["moto_categoria"]), $html);
    $html = str_replace("{moto_tipo}",$row["moto_tipo"],$html);
    $html = str_replace("{moto_marca}",$row["moto_marca"],$html);
    $html = str_replace("{moto_modelo}",$row["moto_modelo"],$html);
    $html = str_replace("{moto_serie}",$row["moto_serie"],$html);
    $html = str_replace("{moto_motor}",$row["moto_motor"],$html);
    $html = str_replace("{moto_color}",$row["moto_color"],$html);
    $html = str_replace("{moto_ano}",$row["moto_ano"],$html);
    $html = str_replace("{moto_cilindraje}",$row["moto_cilindraje"],$html);
    
 
require_once('lib/numletras.php');  

        
    // $html = str_replace("{cierre_plazo}",$row["cierre_plazo"],$html);
    // $html = str_replace("{cierre_total_usd_letras}",numletras($row["cierre_total_usd"],'LEMPIRAS'),$html);
    // $html = str_replace("{cierre_total_usd}",$row["cierre_total_usd"],$html);
    // $html = str_replace("{cierre_interes_mensual_letra}",numletras($row["cierre_interes_mensual"],'PORCIENTO'),$html);
    // $html = str_replace("{cierre_interes_mensual}",$row["cierre_interes_mensual"],$html);
    // $html = str_replace("{cierre_total_usd_contado}",$row["cierre_total_usd_contado"],$html);
    // $html = str_replace("{cierre_total_seguro_usd}",$row["cierre_total_seguro_usd"],$html);
    // $html = str_replace("{cierre_total_prima_usd}",$row["cierre_total_prima_usd"],$html);
    // $html = str_replace("{cierre_cuota_cantidad}",$row["cierre_cuota_cantidad"],$html);
    // $html = str_replace("{cierre_cuota_total_usd}",$row["cierre_cuota_total_usd"],$html);
     
    $html = str_replace("{cierre_plazo}",$row["plazo"],$html);
    $html = str_replace("{cierre_total_usd_letras}",numletras($row["monto_financiar"],'LEMPIRAS'),$html);
    $html = str_replace("{cierre_total_usd}",formato_numero($row["monto_financiar"],2,'Lps. '),$html);
    $html = str_replace("{cierre_interes_mensual_letra}",numLetrasProcentaje($row["cierre_interes_mensual"]),$html);
    $html = str_replace("{cierre_interes_mensual}",$row["cierre_interes_mensual"],$html);
    $html = str_replace("{cierre_total_usd_contado}",formato_numero($row["moto_valor"],2,'Lps. '),$html);
    $html = str_replace("{cierre_total_seguro_usd}",formato_numero($row["monto_seguro"],2,'Lps. '),$html);
    $html = str_replace("{cierre_total_prima_usd}",formato_numero($row["monto_prima"],2,'Lps. '),$html);
    $html = str_replace("{cierre_cuota_cantidad}",$row["plazo"],$html);
    $html = str_replace("{cierre_cuota_total_usd}",formato_numero($row["cuota"],2,'Lps. '),$html);
    
    
    $html = str_replace("{cierre_cuota_dia_pago}",formato_numero($row["cierre_cuota_dia_pago"],0,''),$html);
    
    $html = str_replace("{cierre_cuota_primera_dia}",get_dia($row["cierre_cuota_primera"]),$html);
    $html = str_replace("{cierre_cuota_primera_mes}",get_mes($row["cierre_cuota_primera"]),$html);
    $html = str_replace("{cierre_cuota_primera_ano}",get_anio($row["cierre_cuota_primera"]),$html);
    $html = str_replace("{cierre_cuota_final_dia}",get_dia($row["cierre_cuota_final"]),$html);
    $html = str_replace("{cierre_cuota_final_mes}",get_mes($row["cierre_cuota_final"]),$html);
    $html = str_replace("{cierre_cuota_final_ano}",get_anio($row["cierre_cuota_final"]),$html);
    $html = str_replace("{cierre_firma_dia}",get_dia($row["cierre_firma_fecha"]),$html);
    $html = str_replace("{cierre_firma_mes}",get_mes($row["cierre_firma_fecha"]),$html);
    $html = str_replace("{cierre_firma_ano}",get_anio($row["cierre_firma_fecha"]),$html);
    $html = str_replace("{cierre_interes_moratorio_letra}",numLetrasProcentaje($row["cierre_interes_moratorio"]),$html);
    $html = str_replace("{cierre_interes_moratorio}",$row["cierre_interes_moratorio"],$html);

    $html = str_replace("{FIADOR}", $clausulaAval, $html);
    $html = str_replace("{FIRMA_FIADOR}", $firmaAval, $html);



$pdf->writeHTML($html, true, false, true, false, '');




// PAGINA 2  TRASPASO
$pdf->AddPage();

    $bMargin = $pdf->getBreakMargin();
    $auto_page_break = $pdf->getAutoPageBreak();
    $pdf->SetAutoPageBreak(false, 0);
    $pdf->SetAutoPageBreak($auto_page_break, $bMargin);
    $pdf->setPageMark();
    
    $html = file_get_contents("include/plantillas/traspaso.html");

    
    $html = str_replace("{traspaso_nombre}",$row["nombres"]." ".$row["apellidos"],$html);
    $html = str_replace("{traspaso_estado_civil}",$row["estado_civil"],$html);
    $html = str_replace("{traspaso_identidad}",$row["identidad"],$html);
    $html = str_replace("{moto_categoria}", mayus_string($row["moto_categoria"]), $html);
    $html = str_replace("{moto_tipo}",$row["moto_tipo"],$html);
    $html = str_replace("{moto_marca}",$row["moto_marca"],$html);
    $html = str_replace("{moto_modelo}",$row["moto_modelo"],$html);
    $html = str_replace("{moto_serie}",$row["moto_serie"],$html);
    $html = str_replace("{moto_motor}",$row["moto_motor"],$html);
    $html = str_replace("{moto_color}",$row["moto_color"],$html);
    $html = str_replace("{moto_ano}",$row["moto_ano"],$html);
    $html = str_replace("{moto_cilindraje}",$row["moto_cilindraje"],$html);
        
        
    // {favor_nombre}
    // {favor_identidad}
  
    // {fecha_dia}
    // {fecha_mes}
    // {fecha_ano}
    
    
$pdf->writeHTML($html, true, false, true, false, '');


// // PAGINA 3  PAGARE
// $pdf->AddPage();
// 
    // $bMargin = $pdf->getBreakMargin();
    // $auto_page_break = $pdf->getAutoPageBreak();
    // $pdf->SetAutoPageBreak(false, 0);
    // $pdf->SetAutoPageBreak($auto_page_break, $bMargin);
    // $pdf->setPageMark();
//     
    // $html = 'PAGARE AQUI...';
// $pdf->writeHTML($html, true, false, true, false, '');


// ---------------------------------------------------------

$pdf->Output('documentos_'.$row["numero"].'.pdf', 'I'); //D = descargar


} else { echo "Error - Contratos no disponible";}

?>