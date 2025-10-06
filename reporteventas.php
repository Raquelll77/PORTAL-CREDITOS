<?php
    if ($accion=="31") { // TODO pagina VENTAS
	if (!tiene_permiso(31)) { echo mensaje("No tiene privilegios para accesar esta seccion","danger");exit;}
	 
	 if (isset($_REQUEST['sub'])) {
	 		
	 	if (isset($_REQUEST['fd'],$_REQUEST['fh'],$_REQUEST['td'])) {
	 		
			$fechadesde=$conn->real_escape_string($_REQUEST['fd']);
			$fechahasta=$conn->real_escape_string($_REQUEST['fh']);
			$latienda=$conn->real_escape_string($_REQUEST['td']);
			if (!checkfecha_mysql($fechadesde)) {echo mensaje("Error en la fecha Desde. Debe ingresar una fecha valida","warning");exit;}
			if (!checkfecha_mysql($fechahasta)) {echo mensaje("Error en la fecha Hasta. Debe ingresar una fecha valida","warning");exit;}
			$sqlfiltro="";
			if ($latienda<>"" and $latienda<>"0"){$sqlfiltro=" and tienda=$latienda";}
		
	 		$sql=  "SELECT Fecha, count(*) AS cantidad,
					Sum(Total) AS SumOfTotal
					From Factura
					Where Fecha >= '$fechadesde' AND Fecha <= '$fechahasta' $sqlfiltro
					AND Tipo_factura<>3
					GROUP BY Fecha";
	 		$result = $conn -> query($sql);
	 	
		if ($result->num_rows > 0) {
			$totalrp=0;
			echo '<div class="row">
			        <div class="col-xs-12">
			          <table class="table table-striped">
			            <thead>
			              <tr>
			                <th>Fecha</th>
			                <th class="text-center">Facturas</th>
			                <th class="text-right">Total</th>
			              </tr>
			            </thead>
			            <tbody>';
			
			while ($row = $result -> fetch_assoc()) {
				echo "<tr>
              		  <td>".fechademysql($row["Fecha"])."</td>
		              <td class='text-center'>".$row["cantidad"]."</td>
		              <td class='text-right'>".formato_numero($row["SumOfTotal"],2,"")."</td>
		              </tr>"	;
				$totalrp+=$row["SumOfTotal"];
			}
			
			
			 echo" </tbody>
		             <tfoot>
					    <tr>
					      <td>&nbsp;</td>
					      <td  class='text-right'> <strong>Total:</strong></td>
					      <td  class='text-right'>".formato_numero($totalrp,2,"")."</td>
					    </tr>
					  </tfoot>
		          </table>
		        </div>
		      </div>";
      	
			} else { echo mensaje( "No se encontraron registros","info");exit;}
	
		} 
		
	 	exit;
	 }
	 ?>
	 <script type="text/javascript">
			function procesarrep() {
				
				var url = "get.php?a=31&sub=1&fd="+$("#fd").val()+"&fh="+$("#fh").val()+"&td="+$("#td").val() ;				
				actualizarbox('reportev',url) ;	
			
			}	
	 </script>

    <div class="panel panel-default">
	    <div class="panel-heading">
	      <h4 class="panel-title">Reporte de Ventas</h4>
	    </div>
	    <div class="panel-body">
	    	
	 <form class="form-horizontal">
       <?php 
       
       echo campo("fd","Desde","date",actual_time("Y-m-d"),'class="form-control"');
	   echo campo("fh","Hasta","date",actual_time("Y-m-d"),'class="form-control"');
	   
	   echo campo("td","Tienda","select",valores_combobox_db('tienda',$_SESSION['usuario_tienda'],'Nombre',' order by Nombre','Nombre','Todas las Tiendas','Cod'),'class="form-control"');
	   

	   echo campo("Aceptar","Aceptar","boton","","onclick=\"procesarrep() ; return false;\" ");
	   
       ?> 
       
       
    </form>	    	
	    	
	    </div>
	</div>
	
	<div id="reportev"> </div>

	 
	 <?php 




} // fin pagina VENTAS
?>