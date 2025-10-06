<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");


require_once ('include/protect.php');
require_once ('include/framework.php');

$verror = "";

if (!tiene_permiso(27)) { echo mensaje("No tiene privilegios para accesar esta seccion","danger");exit;}
    

$conn = new mysqli(db_ip, db_user, db_pw, db_name);
if (mysqli_connect_errno()) {  echo mensaje("Error al Conectar a la Base de Datos [DB:101]","danger");exit;}
$conn->set_charset("utf8");




function valores_combobox_db_SAP($wsql){
    global $entorno_desarrollo;
     $salida="";    
     $salida="<option value=\"\">Seleccione...</option>";
   
    
    if (isset($entorno_desarrollo) ) {
      $salida.='<option value="1" >Prueba [1]</option>'; 
        $salida.='<option value="2" >Prueba [2]</option>';  

    } else {
        
        
    
    $sql="$wsql";                    
    //echo $sql;

    $conn2 = sqlsrv_connect( db2_ip, array( "Database"=>db2_dbn, "UID"=>db2_usuario, "PWD"=>db2_clave , "CharacterSet" => "UTF-8") );   if( $conn2 === false ) { echo mensaje("Error al Conectar a la Base de Datos [DB:102]","danger");exit;}
    $stmt2 = sqlsrv_query( $conn2, $sql );  
    if( $stmt2 === false) {   exit;}
    
    if (sqlsrv_has_rows($stmt2)===true) 
            {
  
             while( $row = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC) ) {
                $salida.='<option value="'.$row['codigo'].'" '.$seleccionado.'>'.$row['texto'].'</option>';
            }

    }
    }
    
     return $salida;
    
}


// TODO ############### Buscar cliente
    if (!isset($_REQUEST['sub']) ) {
        ?>
    
    
<div id="buscar"> 
    <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">Registrar un cobro</h4>
        </div>
        <div class="panel-body">
            
     <form class="form-horizontal">
       <?php 
       
        echo campo("sb","Nombre o Codigo del Cliente","text","",' class="form-control" autofocus');
   
    //   echo campo( "Aceptar","Buscar","boton","","onclick=\"procesarrep(1) ; return false;\" ");
    
   ?>    
      <div class="form-group"><div class="col-sm-offset-3 col-sm-9">
        <button type="submit" class="btn btn-primary" onclick="procesarrep(1) ; return false;">Buscar</button>

    </div></div>
       
    </form>         
            
        </div>
    </div>
    </div>

     
     
        
        <div id="reportev"> </div>
        
         <script type="text/javascript">
            function procesarrep(todos) {
                var busca=$("#sb").val();
                if (todos==2) {busca='';} 
                var url = "cobros.php?sub=1&sb="+busca ;             
                actualizarbox('reportev',url) ; 
            
            }   
            
            
     </script>
     
     <?php
     // *** Reporte
      if (tiene_permiso(28))    {
     ?>
    <br><br>    
<div id="reportecobros"> 
    <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">Reporte de Cobros</h4>
        </div>
        <div class="panel-body">
            
     <form id="reporteform" class="form-horizontal">
       <?php 
       
       echo campo("fd","Desde","date",'','class="form-control"','','',3,3);
       echo campo("fh","Hasta","date",'','class="form-control"','','',3,3);
   
 
    
   ?>    
      <div class="form-group"><div class="col-sm-offset-3 col-sm-9">
        <button type="submit" class="btn btn-primary" onclick="procesarrep2(1) ; return false;">Aceptar</button>

    </div></div>
       
    </form>         
            
        </div>
    </div>
    </div>

   
        
         <script type="text/javascript">
            function procesarrep2(todos) {
               var ajaxData = $("#reporteform").serialize();
                var url = "cobros.php?sub=4&"+ajaxData ;             
                actualizarbox('pagina',url) ; 
            
            }   
            
            
     </script>
     
     
     
        
    <?php
    }
    
    exit;
    }
    
    
    
    
 // TODO  ###### Consultar Cliente ########
    
    if (isset($_REQUEST['sub']) ) 
    {
        
        if ($_REQUEST['sub']=="1") {
        
            
        if (isset($_REQUEST['sb']) ) 
        {
            
            $buscar="";
            if (isset($_REQUEST['sb'])) {$buscar=$conn->real_escape_string($_REQUEST['sb']);}
            
                        
          if ($buscar=="") { echo mensaje( "Debe ingresar el codigo o nombre del cliente","warning"); exit;}
            
            //******* SQL ************************************************************************************
               
            $sql="SELECT T0.[CardCode], 
                    T0.[CardName],  
                    T0.[CardType],   
                    T0.[MailAddres], 
                    T0.[Address], 
                    T0.[Phone1], T0.[Phone2], T0.[CntctPrsn], 
                    T0.[Balance],  
                     T0.[CreditLine]
                FROM OCRD T0 WHERE T0.[CardType]  = 'C' ";
                
              
        
            if ($buscar<>"") { $sql.=  "  and ( CardCode='$buscar' or  CardName LIKE '%$buscar%' )";            }   
            
    
             
            // ****** Fin SQL ********************************************************************************
      
             $conn2 = sqlsrv_connect( db2_ip, array( "Database"=>db2_dbn, "UID"=>db2_usuario, "PWD"=>db2_clave , "CharacterSet" => "UTF-8") );   if( $conn2 === false ) { echo mensaje("Error al Conectar a la Base de Datos [DB:102]","danger");exit;}
            $stmt2 = sqlsrv_query( $conn2, $sql );  
            if( $stmt2 === false) {   die( print_r( sqlsrv_errors(), true) );}
    
            if (sqlsrv_has_rows($stmt2)===true) 
            {
                    
                $reg=0;
            echo '<div class="row">
                    <div >
                      <table class="display nowrap" id="tabla" width="100%" cellspacing="0">
                        <thead>
                          <tr>
                        <th  > </th>
                            <th class="text-center">Codigo</th>
                            <th class="text-center">Nombre</th>
                            <th class="text-center">Direccion</th>
                            <th class="text-center">Telefono</th>
                            <th class="text-center">Contacto</th>
           
                           
                          </tr>
                        </thead>
                        
                        <tfoot>
                         <tr>
                      <th  > </th>
                            <th class="text-center">Codigo</th>
                            <th class="text-center">Nombre</th>
                            <th class="text-center">Direccion</th>
                            <th class="text-center">Telefono</th>
                            <th class="text-center">Contacto</th>
                           
                          </tr>
                        </tfoot>
                        <tbody>';

     
                         while( $row = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC) ) {
           
                           echo "<tr>
                         
                             <td class='text-center'><a  href=\"#\" class=\"btn btn-default btn-sm\" onclick=\"actualizarbox('pagina','cobros.php?sub=2&sb=".trim($row["CardCode"])."') ; return false;\" ><span class=\"glyphicon glyphicon-folder-open\" aria-hidden=\"true\"></span></a></td>
                              <td class='text-left'>".trim($row["CardCode"])."</td>
                              <td class='text-left'>".trim($row["CardName"])."</td>
                              <td class='text-left'>".trim($row["Address"])."</td>
                              <td class='text-left'>".trim($row["Phone1"]).'  ' .trim($row["Phone2"])."</td>
                              <td class='text-left'>".trim($row["CntctPrsn"])."</td>
             
                      
                              </tr>"    ;
                        $reg++;
                        }


                        
                 echo" </tbody>                   
                          </table>
                            </div>
                          </div> ";
              
                echo crear_datatable('tabla','false',true,true) ;

              
              // <div class=\"row col-xs-12\">Registros <span class=\"badge\">$reg</span></div>
                 
                 
                } else { echo mensaje( "No se encontraron registros","info"); exit;}  
    
    
    
    
    
 
    
        } //else { echo mensaje( "Debe ingresar informacion en el campo para buscar","warning"); exit;}
        
        exit;
     
     }

// TODO ######### Consultar Facturas Pendientes ##########

if ($_REQUEST['sub']=="2") {
       
        if (isset($_REQUEST['sb']) ) 
        {
            
            $buscar="";
            if (isset($_REQUEST['sb'])) {$buscar=$conn->real_escape_string($_REQUEST['sb']);}
            
                        
          if ($buscar=="") { echo mensaje( "Debe ingresar el codigo o nombre del cliente","warning"); exit;}
            
            //******* SQL ************************************************************************************
               
            $sql="SELECT T0.[CardCode], 
                    T0.[CardName],  
                    T0.[CardType],   
                    T0.[MailAddres], 
                    T0.[Address], 
                    T0.[Phone1], T0.[Phone2], T0.[CntctPrsn], 
                    T0.[Balance],  
                     T0.[CreditLine]
                FROM OCRD T0 WHERE T0.[CardType]  = 'C' ";
            
                
              
        
            if ($buscar<>"") { $sql.=  "  and ( CardCode='$buscar' or  CardName LIKE '%$buscar%' )";            }   
            
    
             
            // ****** Fin SQL ********************************************************************************
      
             $conn2 = sqlsrv_connect( db2_ip, array( "Database"=>db2_dbn, "UID"=>db2_usuario, "PWD"=>db2_clave , "CharacterSet" => "UTF-8") );   if( $conn2 === false ) { echo mensaje("Error al Conectar a la Base de Datos [DB:102]","danger");exit;}
            $stmt2 = sqlsrv_query( $conn2, $sql );  
            if( $stmt2 === false) {   die( print_r( sqlsrv_errors(), true) );}
    
            if (sqlsrv_has_rows($stmt2)===true) 
            {
                    
             
                $row = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC);
           
          
                         
                      echo '
                          <div class="panel panel-default">
                                <div class="panel-heading">
                                  <h4 class="panel-title">Cliente</h4>
                                </div>
                                <div class="panel-body">
                                    Codigo: '.trim($row["CardCode"]).' <br>
                                  Nombre: '.trim($row["CardName"]).'<br>
                                  Direccion: '.trim($row["Address"]).'<br>
                                  Telefono: '.trim($row["Phone1"]).'  ' .trim($row["Phone2"]).'<br>
                                  Contacto: '.trim($row["CntctPrsn"]).'<br>  
                             
                                    
                                </div>
                            </div>';
                            
                            
                            
                            
                 //****** Pagos PENDIENTES           
                  echo '  
                  <div class="panel panel-default">
                                <div class="panel-heading">
                                  <h4 class="panel-title">Detalle Pagos Pendientes</h4>
                                </div>
                                <div class="panel-body">
                          ';
                   
                   
                   
                    // Consulta pendiente SQL      
                    $sql3="SELECT T2.[CardCode], T2.[CardName], T0.[DocNum] as numero_documento, T1.[InstlmntID] as clase_documento, T1.[InstlmntID] as plazo,DATEDIFF(dd,T1.[DueDate], getdate()) as dias_atraso , CONVERT(VARCHAR(10),T1.[DueDate],103) as fecha, T1.[InsTotal] as total, T1.[PaidToDate],(T1.[InsTotal]- T1.[PaidToDate])as saldo_vencido,
                         (SELECT U_CuotaNivelada/2 FROM OINV WHERE DocNum=T0.DocNum) as cuota_nivelada,
                        ( (SELECT U_CuotaNivelada/2 FROM OINV WHERE DocNum=T0.DocNum) - T1.[InsTotal]) as intereses ,
                        (case when DATEDIFF(dd,T1.[DueDate], getdate()) > 0 then (T1.[InsTotal]- T1.[PaidToDate]) * (select IntrstRate from ocrd where cardcode=T0.CardCode) / 100 * DATEDIFF(dd,T1.[DueDate], getdate()) else 0 end) as intereses_moratorios
                        
                        FROM OINV T0  INNER JOIN INV6 T1 ON T0.[DocEntry] = T1.[DocEntry] INNER JOIN OCRD T2 ON T0.[CardCode] = T2.[CardCode] 
                        WHERE  T1.[Status] ='O'  
                        and  T0.[CardCode] ='".trim($row["CardCode"])."'  
                    ";
                    
                    
                    
                    
                    
                    
                    if (isset($entorno_desarrollo) ) {
                        
                     $sql3="SELECT numero_documento, plazo, clase_documento, LEFT(CONVERT(VARCHAR, fecha, 103), 10) as fecha, dias_atraso, total, saldo_vencido, bloqueado, descuento_pronto_pago, 
                            total_importe_redondeo, capital, supervisor, ejecucion_pago, cuota_nivelada, intereses, intereses_moratorios, total_cobro
                            FROM dbo.facturas_pagos 
                             
                    ";
                    }
                
                
                
                
                
                    $stmt3 = sqlsrv_query( $conn2, $sql3 );  
                    if( $stmt3 === false) {   die( print_r( sqlsrv_errors(), true) );                    }
            
                    if (sqlsrv_has_rows($stmt3)===true) 
                    {
                         
             //************************************                       
                            // <th class="text-center" ><input type="checkbox" id="checkAll"/> </th>
                            // <th class="text-center">No. documento</th>
                            // <th class="text-center">Plazo</th>
                            // <th class="text-center">Clase de documento</th>
                            // <th class="text-center">Fecha</th>
                            // <th class="text-center">Dias de atraso</th>
                            // <th class="text-center">Total</th>
                            // <th class="text-center">Saldo vencido</th>
                            // <th class="text-center">Bloqueado</th>
                            // <th class="text-center">% de descuento pronto pago</th>
                            // <th class="text-center">Total importe de redondeo</th>
                            // <th class="text-center">Capital</th>
                            // <th class="text-center">Supervisor</th>
                            // <th class="text-center">Ejecucion de orden de pago</th>
                            // <th class="text-center">Cuota Nivelada</th>
                            // <th class="text-center">Intereses</th>
                            // <th class="text-center">Intereses Moratorios</th>
                            // <th class="text-center">Total Cobro</th>  
            //************************************                                      
                              // <td class='text-center'><input type=\"checkbox\" name=\"pagos[]\" value=\"".trim($row3["numero_documento"])."\" data-monto=\"".number_format($row3["total_cobro"],2)."\" ></td>
                              // <td class='text-left'>".trim($row3["numero_documento"])."</td>
                              // <td class='text-left'>".trim($row3["plazo"])."</td>
                              // <td class='text-left'>".trim($row3["clase_documento"])."</td>
                              // <td class='text-center'>".($row3["fecha"])."</td>
                              // <td class='text-center'>".($row3["dias_atraso"])."</td>
                              // <td class='text-right'>".number_format($row3["total"],2)."</td>
                              // <td class='text-right'>".number_format($row3["saldo_vencido"],2)."</td>
                              // <td class='text-left'>".trim($row3["bloqueado"])."</td>
                              // <td class='text-right'>".number_format($row3["descuento_pronto_pago"],2)."</td>
                              // <td class='text-left'>".trim($row3["total_importe_redondeo"])."</td>
                              // <td class='text-right'>".number_format($row3["capital"],2)."</td>
                              // <td class='text-left'>".trim($row3["supervisor"])."</td>
                              // <td class='text-center'>".trim($row3["ejecucion_pago"])."</td>
                              // <td class='text-right'>".number_format($row3["cuota_nivelada"],2)."</td>
                              // <td class='text-right'>".number_format($row3["intereses"],2)."</td>
                              // <td class='text-right'>".number_format($row3["intereses_moratorios"],2)."</td>
                              // <td class='text-right'>".number_format($row3["total_cobro"],2)."</td>                                            
            //************************************             
                         
                         
                    $reg=0;
                    echo '
             
            <div class="row">
                    <div class="table-responsive">
                      <table class="display nowrap table table-striped table-hover" id="tabla" width="100%" cellspacing="0">
                        <thead>
                          <tr>
                            <th class="text-center" ><input type="checkbox" id="checkAll"/> </th>
                            <th class="text-center">No. documento</th>
                            <th class="text-center">Plazo</th>
                            <th class="text-center">Fecha</th>
                            <th class="text-center">Dias de atraso</th>
                     
                            <th class="text-center">Saldo vencido</th>

                            <th class="text-center">Cuota Nivelada</th>
                            <th class="text-center">Intereses</th>
                            <th class="text-center">Intereses Moratorios</th>
                            <th class="text-center">Total Cobro</th>          
                           
                          </tr>
                        </thead>
                        
               
                        <tbody>';
                           
                                                                               
                         $cuota_orden=0;
                          while( $row3 = sqlsrv_fetch_array( $stmt3, SQLSRV_FETCH_ASSOC) ) {
                                    $cuota_orden++;
                                    $total_cobro=$row3["cuota_nivelada"]+$row3["intereses_moratorios"] ;
                                   echo "<tr>
                                 
                                     <td class='text-center'><input type=\"checkbox\" name=\"pagos[]\" value=\"".trim($row3["numero_documento"])."\" data-monto=\"".round($total_cobro,2)."\" data-plazo=\"".trim($row3["plazo"])."\" data-capital=\"".round(trim($row3["total"]),2)."\" data-interes=\"".round(trim($row3["intereses"]),2)."\" data-mora=\"".round(trim($row3["intereses_moratorios"]),2)."\" data-cuota=\"".$cuota_orden."\"></td>
                                      <td class='text-left'>".trim($row3["numero_documento"])."</td>
                                      <td class='text-left'>".trim($row3["plazo"])."</td>
                                      <td class='text-center'>".($row3["fecha"])."</td>
                                      <td class='text-center'>".($row3["dias_atraso"])."</td>
                                  
                                      <td class='text-right'>".number_format($row3["saldo_vencido"],2)."</td>

                                      <td class='text-right'>".number_format($row3["cuota_nivelada"],2)."</td>
                                      <td class='text-right'>".number_format($row3["intereses"],2)."</td>
                                      <td class='text-right'>".number_format($row3["intereses_moratorios"],2)."</td>
                                       <td class='text-right'>".number_format($total_cobro,2)."</td>
                                      
                     
                              
                                      </tr>"    ;
                                $reg++;
                                }
                
                   echo" </tbody>                   
                          </table>
                            </div>
                          </div> 
                           ";
                 ?>         
                      <form id="formop" class="form-horizontal">
      
              
                   <br>
                    <button type="submit" class="btn btn-primary" id="btnpagar" onclick="crear_recibo() ; return false;">Crear Recibo</button>
                        
                     <div id="forma_pago" style="display: none">
                      
                      <?php    echo campo("monto","Total","text","",' class="form-control" readonly','','',3,3); ?>
                      <?php    echo campo("comentarios","Comentarios","text","",' class="form-control" ','','',3,8); ?>
                      <?php    echo campo("did","","hidden","",' ',''); ?>
                      <?php    echo campo("dmonto","","hidden","",' ',''); ?>
                      <?php    echo campo("dplazo","","hidden","",' ',''); ?>
                      
                      <?php    echo campo("dcapital","","hidden","",' ',''); ?>
                      <?php    echo campo("dinteres","","hidden","",' ',''); ?>
                      <?php    echo campo("dmora","","hidden","",' ',''); ?>
                      
                      <?php    echo campo("cliente_codigo","","hidden",trim($row["CardCode"]),' ',''); ?>
                      <?php    echo campo("cliente_nombre","","hidden",trim($row["CardName"]),' ',''); ?>
              
                      
                      <div id="exTab1" class="container">  
                            <ul  class="nav nav-tabs">
                                <li class="active"><a  href="#1a" data-toggle="tab">Cheque</a>  </li>
                                <li><a href="#2a" data-toggle="tab">Transferencia Bancaria</a>  </li>
                                <li><a href="#3a" data-toggle="tab">Tarjeta de Credito</a>  </li>
                            <li><a href="#4a" data-toggle="tab">Efectivo</a> </li>
                            </ul>
                    
                                <div class="tab-content clearfix">
                                  <div class="tab-pane active" id="1a">
                              <h3>Cheque</h3>

                                
                                    <?php    echo campo("chk_monto","Monto","text","",' class="form-control" ','','',3,3); ?>
                                    <?php   // echo campo("chk_fecha","Fecha","date","",' class="form-control" ','','',3,3); 
                                    ?>
                                    <?php    echo campo("chk_nombre","Banco","select",valores_combobox_db_SAP("SELECT T0.[BankCode] as codigo, T0.[BankName] as texto FROM ODSC T0"),' class="form-control" ','','',3,7); ?>
                                    <?php    echo campo("chk_sucursal","Sucursal","text","",' class="form-control" ','','',3,7); ?>
                                     <?php    echo campo("chk_cuenta","Cuenta","text","",' class="form-control" ','','',3,4); ?>
                                     
                                    <br>
                                    <button type="submit" class="btn btn-primary" id="btnguardar" onclick="crear_recibo2('chk') ; return false;">Guardar</button>
                     
                                    </div>
                                    
                                    <div class="tab-pane" id="2a">
                              <h3>Transferencia Bancaria</h3>
               
                                <?php    echo campo("tb_monto","Monto","text","",' class="form-control" ','','',3,3); ?>
                                <?php    echo campo("tb_banco","Banco","select",valores_combobox_db_SAP("SELECT T0.[AcctCode] as codigo, T0.[AcctName] as texto,T0.[FormatCode] , T0.[FatherNum] FROM OACT T0 WHERE T0.[FatherNum]  = '112001' "),' class="form-control" ','','',3,7); ?>
                                <?php    echo campo("tb_fecha","Fecha","date","",' class="form-control" ','','',3,3); ?>
                                <?php    echo campo("tb_referencia","Referencia","text","",' class="form-control" ','','',3,4); ?>
    
                                    <br>
                                    <button type="submit" class="btn btn-primary" id="btnguardar" onclick="crear_recibo2('tra') ; return false;">Guardar</button>
                                    </div>
                                    
                            <div class="tab-pane" id="3a">
                              <h3>Tarjeta de Credito</h3>
                         
                                <?php    echo campo("tc_monto","Monto","text","",' class="form-control" ','','',3,3); ?>
                                <?php    echo campo("tc_numero","Numero","text","",' class="form-control" ','','',3,3); ?>
                                <?php    echo campo("tc_nombre","Nombre","select",valores_combobox_db_SAP("SELECT T0.[CreditCard] as codigo, T0.[CardName] as texto, T0.[AcctCode] FROM OCRC T0 "),' class="form-control" ','','',3,7); ?>
                                <?php    echo campo("tc_forma_pago","Forma de Pago","select",valores_combobox_db_SAP("SELECT T0.[CrTypeCode] as codigo, CONCAT (T0.[CrTypeName],' [',T0.[CreditCard],']') as texto, T0.[CreditCard] FROM OCRP T0 "),' class="form-control" ','','',3,7); ?>
                                <?php    echo campo("tc_valido_hasta","Valido Hasta","text","",' class="form-control" ','','',3,3); ?>
                                <?php    echo campo("tc_id","Numero de ID","text","",' class="form-control" ','','',3,3); ?>
                                <?php    echo campo("tc_telefono","Telefono","text","",' class="form-control" ','','',3,3); ?>
                                <?php    echo campo("tc_retencion","Monto Retencion","text","",' class="form-control" ','','',3,3); ?>
                               
                                    <br>
                                   <button type="submit" class="btn btn-primary" id="btnguardar" onclick="crear_recibo2('tc') ; return false;">Guardar</button>
                                    </div>
                                    <br>
                    
               
                              <div class="tab-pane" id="4a">
                              <h3>Efectivo</h3>
                           
                              <?php    echo campo("efe_monto","Monto","text","",' class="form-control" ','','',3,3); ?>
                              <br>
                              <button type="submit" class="btn btn-primary" id="btnguardar" onclick="crear_recibo2('efe') ; return false;">Guardar</button>
                                    </div>
                                  
                                  <br>
                                  <div id="respuesta" style="display: none"> </div>
                                      
                                        
                                    
                                </div>
                       </div>
                      
                      
       
                            
                           
                     </div>   
                
                    </form>
                    
                    <script>
					
						var names = $('#tc_forma_pago option').clone();

						$('#tc_nombre').change(function() {
							var val = $(this).val();  
							$('#tc_forma_pago').empty();
							names.filter(function(idx, el) {
								return val === 'ALL' || $(el).text().indexOf('[' + val + ']') >= 0;
							}).appendTo('#tc_forma_pago');
						});
                    
                        function crear_recibo(){
                              var allVals = [];
                              var mtotal=0;
                              var cuota_actual=1;
                              var cuota_seleccionada=0;
                              var error_orden_cuota=false;
                              
                             $('#tabla :checked').each(function() {
                               allVals.push($(this).val());
                               mtotal=mtotal+$(this).data('monto');
                               
                               cuota_seleccionada=parseInt($(this).data('cuota'));
                               if (cuota_actual!=cuota_seleccionada) {error_orden_cuota=true;}
                               cuota_actual=cuota_actual+1;
                               
                             });
                             
                         
                           if  (allVals.length>0) { 
                                 if (error_orden_cuota==false){
                                   $('#monto').val(mtotal); 
                                   $('#tabla input[type=checkbox]').attr('disabled','true');
                                    $('#forma_pago').show();  
                                    $('#btnpagar').hide();
                                    } else {  showmessage('Error','Debe seleccionar las primeras cuotas disponibles, no puede saltar una cuota.'); }
                                 } else {
                                    showmessage('Error','Debe seleccionar al menos una factura a pagar');
                                 }
                                     
                        }
                        
                        
                        
                         function crear_recibo2(){
                            var totalpagar=0;
                            var total2=0;
                            var efe=0;
                            var tc=0;
                            var tb=0;
                            var chk=0;
                            
                            total2=parseFloat($('#monto').val());
                            efe=  parseFloat($('#efe_monto').val());
                            tb=  parseFloat($('#tb_monto').val());
                            tc=  parseFloat($('#tc_monto').val());
                            chk=  parseFloat($('#chk_monto').val());
                            if (!isNaN(efe)) {totalpagar=totalpagar+ efe;}
                            if (!isNaN(tc)) {totalpagar=totalpagar+ tc;}
                            if (!isNaN(tb)) {totalpagar=totalpagar+ tb;}
                            if (!isNaN(chk)) {totalpagar=totalpagar+ chk;}
                          
                                if (totalpagar<total2) { showmessage('Error','El monto ingresado en la forma de pago no cubre el total a pagar'); }
                                else {
                                        
                                        var mensaje1= "";
                                        
                                        mensaje1= "Desea guardar el recibo por el monto de "+total2+ "?" ;
                                        
                                        if (totalpagar>total2) {mensaje1= "El monto ingresado a pagar es superior al valor total, desea abonar la diferencia a la ULTIMA CUOTA? <br><br>De lo contrario ingrese el valor a pagar igual al valor total" ;}
                                    
                                        var NewDialog = $('<div><p><span class="ui-icon ui-icon-info" style="display:inline-block"></span> '+mensaje1+'</p> </div>');
                                        NewDialog.dialog({
                                        modal: true,
                                        title: 'Guardar',
                                        show: 'clip',
                                        hide: 'clip',
                                        stack: true,
                                        close: function(event, ui)
                                        {
                                           $(this).dialog('destroy').remove();
                                        },
                                        buttons: [
                                            {text: "SI", click: function() {
                                                                                                 
                                                
                                                guardar_recibo();

                                                $(this).dialog("close");}},
                                            {text: "NO", click: function() {$(this).dialog("close");}}
                                        ]
                                    });
                                    
                                    
                                }
                            
                                
                                  
                        }
                     
                        
                        function guardar_recibo() { 
                            
                            
                              $("#btnguardar").attr("disabled", "disabled");
                             $("#respuesta").html('<div><p><img src="images/load.gif"/> Espere un momento, Procesando...</p></div>');
                             $("#respuesta").show();
                             
                               var detalle = [];
                               var dmontos = [];
                               var dplazos = [];
                               var dcapital2 = [];
                               var dinteres2 = [];
                               var dmora2 = [];
                               $('#tabla :checked').each(function() {
                               detalle.push($(this).val());
                               dmontos.push($(this).data('monto'));
                               dplazos.push($(this).data('plazo'));
                               dcapital2.push($(this).data('capital'));
                               dinteres2.push($(this).data('interes'));
                               dmora2.push($(this).data('mora'));
                             });
                             
                         
                           $("#did").val(detalle);
                           $("#dmonto").val(dmontos);
                            $("#dplazo").val(dplazos);
                            $("#dcapital").val(dcapital2);
                            $("#dinteres").val(dinteres2);
                            $("#dmora").val(dmora2);
                             
                             var ajaxData = $("#formop").serialize();
                             
                             var respuesta=  $.ajax({
                                type: 'POST',
                                data: ajaxData,
                                url: 'cobros.php?sub=3',
                                async: false,
                            }).responseText;
                  
                           
                            $("#respuesta").html(respuesta.trim());
                            
                            if (respuesta.substring(0, 5)=='ERROR') {
                                $("#btnguardar").removeAttr("disabled"); 
                            } else {$("#btnguardar").hide(); }

                        }
                        

                        $("#checkAll").change(function () {
                            $("input:checkbox").prop('checked', $(this).prop("checked"));
                        });
                        
                        
                        function imprimir_recibos(rid) {
                            var url="cobros_imprimir_recibo.php?cid="+rid;
                            popupwindow(url, "Recibo", screen.width, screen.height);
                        }
                        
                    </script> 
                    
                    
              <?php
                     
                        
                    }  else { echo mensaje( "No se encontraron registros","info"); }      
                             
                  echo '                   
                                </div>
                            </div>
                            
                        ';  
                        
                        
                        
                        
                        
                        
                 
                        
                          
                 echo ' 
                <br><br><br>
                       <a href="#" onclick="actualizarbox(\'pagina\',\'cobros.php\') ; return false;" class="btn btn-default">REGRESAR</a>
                              
                         ';
          


  
                 
                } else { echo mensaje( "No se encontraron registros","info"); exit;}  
    
    

    
        } 
        
        exit;     
        
    
        }
    


// TODO  ******* Guardar Recibo

 if ($_REQUEST['sub']=="3") {

     $salida="";
     
    //########## validar datos
        $verror="";
        
        $verror.=validar("Monto",$_REQUEST['monto'], "double", true,  null,  1,  null);
        $verror.=validar("Monto Efectivo",$_REQUEST['efe_monto'], "double", false,  null,  1,  null);
        $verror.=validar("Monto Transferencia Bancaria",$_REQUEST['tb_monto'], "double", false,  null,  1,  null);
        $verror.=validar("Monto Tarjeta Credito",$_REQUEST['tc_monto'], "double", false,  null,  1,  null);
        $verror.=validar("Monto Cheque",$_REQUEST['chk_monto'], "double", false,  null,  1,  null);
       
        
    // ######### Guardar 
        if ($verror==""){

      //  numero, , , , , ,

        
            $sqlcampos="";
               
             $sqlcampos.= "  monto =".GetSQLValue($conn->real_escape_string(filter_var($_REQUEST["monto"], FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION)),"double"); 
             $sqlcampos.= " , efe_monto =".GetSQLValue($conn->real_escape_string(filter_var($_REQUEST["efe_monto"], FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION)),"double");
             $sqlcampos.= " , tb_monto =".GetSQLValue($conn->real_escape_string(filter_var($_REQUEST["tb_monto"], FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION)),"double");
             $sqlcampos.= " , tc_monto =".GetSQLValue($conn->real_escape_string(filter_var($_REQUEST["tc_monto"], FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION)),"double");
             $sqlcampos.= " , chk_monto =".GetSQLValue($conn->real_escape_string(filter_var($_REQUEST["chk_monto"], FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION)),"double");        
 
            // $sqlcampos.= " , efe_cuenta_mayor =".GetSQLValue($conn->real_escape_string($_REQUEST["efe_cuenta_mayor"]),"text");
            // $sqlcampos.= " , tb_cuenta_mayor =".GetSQLValue($conn->real_escape_string($_REQUEST["tb_cuenta_mayor"]),"text");
            // $sqlcampos.= " , tc_cuenta_mayor =".GetSQLValue($conn->real_escape_string($_REQUEST["tc_cuenta_mayor"]),"text");
            // $sqlcampos.= " , chk_cuenta_mayor =".GetSQLValue($conn->real_escape_string($_REQUEST["chk_cuenta_mayor"]),"text");
            $sqlcampos.= " , tb_fecha =".GetSQLValue($conn->real_escape_string($_REQUEST["tb_fecha"]),"text");
            $sqlcampos.= " , tb_referencia =".GetSQLValue($conn->real_escape_string($_REQUEST["tb_referencia"]),"text");
            $sqlcampos.= " , tb_banco =".GetSQLValue($conn->real_escape_string($_REQUEST["tb_banco"]),"text");
            
            $sqlcampos.= " , tc_nombre =".GetSQLValue($conn->real_escape_string($_REQUEST["tc_nombre"]),"text");
            $sqlcampos.= " , tc_numero =".GetSQLValue($conn->real_escape_string($_REQUEST["tc_numero"]),"text");
            $sqlcampos.= " , tc_valido_hasta =".GetSQLValue($conn->real_escape_string($_REQUEST["tc_valido_hasta"]),"text");
            $sqlcampos.= " , tc_id =".GetSQLValue($conn->real_escape_string($_REQUEST["tc_id"]),"text");
            $sqlcampos.= " , tc_telefono =".GetSQLValue($conn->real_escape_string($_REQUEST["tc_telefono"]),"text");
            $sqlcampos.= " , tc_retencion =".GetSQLValue($conn->real_escape_string($_REQUEST["tc_retencion"]),"text");
            
            $sqlcampos.= " , tc_forma_pago =".GetSQLValue($conn->real_escape_string($_REQUEST["tc_forma_pago"]),"text");
            
            
            $sqlcampos.= " , chk_fecha =".GetSQLValue($conn->real_escape_string($_REQUEST["chk_fecha"]),"text");
            $sqlcampos.= " , chk_nombre =".GetSQLValue($conn->real_escape_string($_REQUEST["chk_nombre"]),"text");
            $sqlcampos.= " , chk_sucursal =".GetSQLValue($conn->real_escape_string($_REQUEST["chk_sucursal"]),"text");
            $sqlcampos.= " , chk_cuenta =".GetSQLValue($conn->real_escape_string($_REQUEST["chk_cuenta"]),"text");
            $sqlcampos.= " , comentarios =".GetSQLValue($conn->real_escape_string($_REQUEST["comentarios"]),"text");
            
             $sqlcampos.= " , cliente_codigo =".GetSQLValue($conn->real_escape_string($_REQUEST["cliente_codigo"]),"text");
             $sqlcampos.= " , cliente_nombre =".GetSQLValue($conn->real_escape_string($_REQUEST["cliente_nombre"]),"text");
  
            $sqlcampos.= ",usuario_alta= '" .$_SESSION['usuario'] . "',fecha_alta=curdate() ,hora_alta=now()";
           
           $sqlcampos.= ",bodega= '" .$_SESSION['usuario_bodega'] . "'";
   
            $sql="insert into recibo set " . $sqlcampos;
            
            if ($conn->query($sql) === TRUE) {
                
                $recibo_id_new = mysqli_insert_id($conn);
               
               // *** Guardar el detalle del recibo
                $detalle = explode(",", $conn->real_escape_string($_REQUEST['did']));
                $montos=explode(",", $conn->real_escape_string($_REQUEST['dmonto']));  
                $plazos=explode(",", $conn->real_escape_string($_REQUEST['dplazo']));
                $capital=explode(",", $conn->real_escape_string($_REQUEST['dcapital'])); 
                $interes=explode(",", $conn->real_escape_string($_REQUEST['dinteres'])); 
                $mora=explode(",", $conn->real_escape_string($_REQUEST['dmora']));              
                $x=0;
                foreach($detalle as $key=>$value) {
                    $sqladd='';
                    $sqladd.=" , numero_documento =".GetSQLValue($value,"text");
                    $sqladd.=" , monto =".GetSQLValue($montos[$x],"double");
                     $sqladd.=" , plazo =".GetSQLValue($plazos[$x],"text");
                     $sqladd.=" , capital =".GetSQLValue($capital[$x],"double");
                     $sqladd.=" , interes =".GetSQLValue($interes[$x],"double");
                     $sqladd.=" , mora =".GetSQLValue($mora[$x],"double");
                    $conn->query('insert into recibo_detalle set recibo_id='.$recibo_id_new.$sqladd);
                    $x++;
                }   
                
                // **** Ejecutar Proceso SAP
                
               //** Ejecutar web service:
                                         
                    // $salida=true;
                     // ini_set('default_socket_timeout', 30); //timeout
                     // ini_set("soap.wsdl_cache_enabled", "0"); // deshabilita cache
                     // $client = new 
                    // SoapClient("http://localhost:8082/Service1.asmx?WSDL",array("trace" => 1,"exceptions" => 0,'encoding'=>'ISO-8859-1') );
                     // $return = $client->RecibosSAP(array('Trama' => $recibo_id_new));
                     // // deteccion de errores
                     // if (is_soap_fault($return)) {
                         // $salida=false;
                     // } else {
                         // $salida=true;
                     // }              
        
                
                //**ejecutar archivo:
               // exec("C:/RecibosSAP.exe"." ".$recibo_id_new);
                
                $terminar=false;
                $vuelta=1;
                $procesado='N';
                $procesado_error='';
                $procesado_numero='';
                
                $espera=2; // 2 segundos
                
                while ($terminar==false) {
                   
                    sleep($espera);
                    
                    $espera=4; // incrementar a 4 segundos para la segunda vuelta
                    
                    //leer si ya fue procesada por aplicativo externo
                    $consulta = $conn -> query("select procesado, procesado_error, numero from recibo where procesado<>'N' and id=$recibo_id_new");
                    if ($consulta->num_rows > 0) {
                        $consulta_row = mysqli_fetch_array($consulta) ;
                        $procesado=$consulta_row["procesado"];
                        $procesado_error=$consulta_row["procesado_error"];
                        $procesado_numero=$consulta_row["numero"];
                        $terminar=true;
                    }
                    
                    
                    if ($vuelta>=3) { $terminar=true; }
                    $vuelta++;
                }

                if ($procesado=='N') {
                    $salida=mensaje( "ERROR"."No se pudo verificar con SAP si se guardo el recibo, verifique en SAP si fue creado el recibo","warning"); 
                } else {
                    if ($procesado=='S') {
                        
                        $salida=mensaje( "El Recibo fue guardado.","success");
                        
                       $salida.= ' <a id="btnimp_1" href="#" class="btn btn-default" onclick="imprimir_recibos('.$recibo_id_new.'); return false;"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Imprimir Recibo</a>';
                    } else {
                        // error
                        $salida="ERROR ".mensaje($procesado_error,"warning");
                    }
                       
                }
                    
            } else {

                $salida="ERROR ".mensaje( 'Se produjo un error al guardar el registro DB101: <br>'.$conn->error,"warning");

            }
        
            
            
            
        } else {
            //mostrar errores validacion
            $salida="ERROR ".mensaje( $verror,"warning");
        }           
                
        
     
   

     echo $salida;
     
    exit;
 }



// TODO  ******* consultar recibos

 if ($_REQUEST['sub']=="4") {
     
             $fdesde=""; $fhasta=""; $filtro_fecha="";
        if (isset($_REQUEST['fd'])) { $fdesde = mysqldate($_REQUEST['fd']); $reporte_fecha=$_REQUEST['fd'];} else   {$fdesde=get_fecha_sistema(); $reporte_fecha=fechademysql($fdesde); }
        if (isset($_REQUEST['fh'])) { $fhasta = mysqldate($_REQUEST['fh']); $reporte_fecha.=' al '.$_REQUEST['fh'];} else    {$fhasta=$fdesde;  $reporte_fecha.=' al '.fechademysql($fhasta);}
       
        $fechaerror="";
        if (!checkfecha_mysql($fdesde)) {$fechaerror="Error en la fecha Desde. Debe ingresar una fecha valida";}
        if (!checkfecha_mysql($fhasta)) {$fechaerror="Error en la fecha Hasta. Debe ingresar una fecha valida";}
        if ($fechaerror<>"") { echo mensaje($fechaerror,'danger'); echo'<br><br><a href="#" onclick="actualizarbox(\'pagina\',\'cobros.php\') ; return false;" class="btn btn-default">REGRESAR</a>'; exit;}

        $filtro_fecha=" and fecha_alta>='$fdesde' and fecha_alta<='$fhasta'";
        

                $titulo="Reporte de Cobros ";
             

            //******* SQL ************************************************************************************
                       
            $sql=" select * from recibo
            WHERE  procesado='S' ";
        
            $sql.=" $filtro_fecha  ";
 
            // ****** Fin SQL ********************************************************************************
          
             $result = $conn -> query($sql);

              echo "<h4>$titulo $reporte_fecha</h4><br>";
            if ($result -> num_rows > 0) {
                
                $reg=0;
                
                echo '
                   <script> 
                   function imprimir_recibos(rid) {
                            var url="cobros_imprimir_recibo.php?cid="+rid;
                            popupwindow(url, "Recibo", screen.width, screen.height);
                        }
                      </script> 
                  ';
                        
                 
            echo '<div class="row">
                    <div >
                      <table class="display nowrap" id="tabla" width="100%" cellspacing="0">
                        <thead>
                          <tr>
                            <th class="text-center"></th>
                            <th class="text-center">Fecha</th>
                            <th class="text-center">Numero</th>
                            <th class="text-center">Cod. Cliente</th>
                            <th class="text-center">Cliente</th>
                            <th class="text-center">Monto</th>
                            <th class="text-center">Usuario</th>
                          </tr>
                        </thead>
                        <tbody>';
                
 
                        while ($row = $result -> fetch_assoc()) {
                           echo "<tr>
                              <td class='text-center'><a  href=\"#\" class=\"btn btn-default btn-sm\" onclick=\"imprimir_recibos(".$row["id"]."); return false;\" ><span class=\"glyphicon glyphicon-print\" aria-hidden=\"true\"></span></a></td>  
                              <td class='text-center'>".fechademysql($row["fecha_alta"])."</td>
                              <td class='text-left'>".$row["numero"]."</td>
                              <td class='text-left'>".$row["cliente_codigo"]."</td>
                              <td class='text-left'>".$row["cliente_nombre"]."</td> 
                              <td class='text-left'>".formato_numero($row["monto"],2,'')."</td>
                            
                               <td class='text-center'>".$row["usuario_alta"]."</td>";
                           
                             echo "   
                              </tr>"    ;
                        $reg++;
                        }

                        
                 echo" </tbody>                   
                          </table>
                            </div>
                          </div> ";
              
                echo crear_datatable('tabla','false') ; 
              
  echo'<br><br><a href="#" onclick="actualizarbox(\'pagina\',\'cobros.php\') ; return false;" class="btn btn-default">REGRESAR</a>';
                 
                } else { echo mensaje( "No se encontraron registros","info"); echo'<br><br><a href="#" onclick="actualizarbox(\'pagina\',\'cobros.php\') ; return false;" class="btn btn-default">REGRESAR</a>'; exit;}  
    
    

    exit; 
 }



 }







?>