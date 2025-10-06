<?php
require_once ('include/protect.php');

if (isset($_REQUEST['tbl'])) { $numtabla = $_REQUEST['tbl']; } else	exit ;
require_once ('include/framework.php');

require_once ('crud_tablas.php');

if ($tabla=="" ){exit;}

$nuevo_registro=false;
$verror = "";
$accion="b";

if (isset($_REQUEST['a'])) {$accion=$_REQUEST['a'];}

if ($accion=="r" or $accion=="u" or $accion=="d" ) {

if (!isset($_REQUEST['rid'])) {exit;}	

	$conn = new mysqli(db_ip, db_user, db_pw, db_name);
	if (mysqli_connect_errno()) {	echo '<div class="row-fluid">	<div class="alert alert-info">Error al Conectar a la Base de Datos [DB:101]</div></div>'; exit; } 
	$conn->set_charset("utf8");
	$rid = $conn->real_escape_string($_REQUEST['rid']);
	
		
}

//### create ### 
if ($accion=="c") {

$nuevo_registro=true;
$rid =0;
$accion="r";		

}

//### read ### 
if ($accion=="r") {
	

?>

<script type="text/javascript">

function guardarforma() {
				$("#botones *").attr("disabled", "disabled");
				$("#form :input").attr('readonly', true);
				$('#salida').hide();
				$('#cargando').show();
				var myTable = '';
				
				var url = "crud.php?tbl=<?php echo $numtabla; ?>&a=u&rid=<?php echo $rid; ?>";
				$.getJSON(url, $("#form").serialize(), function(json) {
					
					i = 1;
					if (json.length > 0) {
						if (json[0].pcode == 0) {
							$("#salida").html( json[0].pmsg );
						}
						if (json[0].pcode == 1) {
							$("#salida").html(json[0].pmsg);
							<?php if ($nuevo_registro==true) {echo "$('#btnsend').hide();";}  ?>
							
						}
					} else {
						$("#salida").html('<div class="alert alert-error">Se produjo un error en comunicacion JSON:101</div>');
					}

				}).error(function() {
					$("#salida").html('<div class="alert alert-error">Se produjo un error en comunicacion JSON:102</div>');
				}).complete(function() {
					$('#salida').show();
					$('#cargando').hide();
					$("#form :input").attr('readonly', false);
					$("#botones *").removeAttr("disabled");
				//	mostrar_ventana();
				});

			}
			
			
			

			function confirmar_borrar() {

				$("#dialog-confirm").dialog({
					resizable : false,
					modal : true,
					position : {my: 'center center' , at: 'center center'},
					buttons : {
						"Borrar" : function() {
							
							actualizarbox('pagina','crud.php?tbl=<?php echo $numtabla; ?>&a=d&rid=<?php echo $rid; ?>') ; 
							$(this).dialog("close");
						},
						"Cancelar" : function() {
							$(this).dialog("close");
						}
					}
				});

			};
</script>

<div id="dialog-confirm" title="Desea Borrar este registro?" style="display: none;">
<p><span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>Los registros borrados no pueden ser recuperados, desea borrarlo?</p>
</div>



	
<div class="row-fluid" >
	<div class="span12">
		<div class="pmbox round">
        <h2><?php echo $tabla_etiqueta; ?></h2>
        <div class="pmblock">		
		
		
 <form id="form" class="form-horizontal">

    <?php
   
   if ($nuevo_registro==true) {
   	
	$i=0;
   		 foreach ($columnas as $campo) {
   		 	$campotipo="text";
			$campoclase='class="form-control"';
   		 	if ($campo=='id') {$campoclase='class="input-small" disabled';}
			
			$valor="";
			if (isset($columnas_combo)){
				$key = array_search($campo, $columnas_combo);
				if ($key===false) {
				    if ($columnas_mask[$i]<>''){$campoclase.=' data-mask="'.$columnas_mask[$i].'" '; }
					echo campo($campo,$columnas_etiquetas[$i],$campotipo,'' ,$campoclase);
				} else {
					$campoid="id";
					if ($campo=="bodega" or $campo=="distribuidor") {$campoid="codigo"; }
			
					echo campo($campo,$columnas_etiquetas[$i],'select2',valores_combobox_db($columnas_combo2[$key],'',$columnas_combo3[$key],'','','Seleccione',$campoid),$campoclase);
					}
				
				
			} else {
			if ($columnas_mask[$i]<>''){$campoclase.=' data-mask="'.$columnas_mask[$i].'" '; }
			 echo campo($campo,$columnas_etiquetas[$i],$campotipo,'' ,$campoclase);
			}
			 $i++;
		}
	
	
	
   } else { 
    	$sql = "SELECT ".implode(',', $columnas)." FROM " .$tabla . " where id=".$rid;
	$result = $conn -> query($sql);
	if ($result->num_rows > 0) {
      	while ($row = mysqli_fetch_array($result)) {
			

		// $finfo = $result->fetch_fields();
// 
    	// foreach ($finfo as $val) {
//     		
	        // printf("Name:     %s\n", $val->name);
	        // printf("Table:    %s\n", $val->table);
	        // printf("max. Len: %d\n", $val->max_length);
	        // printf("Flags:    %d\n", $val->flags);
	        // printf("Type:     %d\n\n", $val->type);
   		 // }
   		 $i=0;
   		 foreach ($columnas as $campo) {
   		 	$campotipo="text";
			$campoclase='class="form-control"';
   		 	if ($campo=='id') {$campoclase='class="input-small" disabled';}
			
			$valor="";
			if (isset($columnas_combo)){
				$key = array_search($campo, $columnas_combo);
				if ($key===false) {
					if ( $tabla=="usuario" and $campo=="clave") {
						// echo '<div class="control-group"><label class="control-label">Clave</label><div class="controls">' ;
						// echo "<button onClick = \"efectuar_proceso(7,'Modificar Contrase�a',3,$rid,'');\" class=\"btn btn-small\" type=\"button\"><i class=\"icon-cog\"></i> Modificar Contrase�a</button>";
						// echo '</div></div>' ;
						
					 echo	campo("modboton",'Modificar Contrase&ntilde;a','boton','',"onClick = \"efectuar_proceso(7,'Modificar Contrase&ntilde;a',3,$rid,''); return false; \"");
					} else {
					    if ($columnas_mask[$i]<>''){$campoclase.=' data-mask="'.$columnas_mask[$i].'" '; }
					echo campo($campo,$columnas_etiquetas[$i],$campotipo, $row[$campo],$campoclase); //htmlentities($row[$campo], ENT_QUOTES, 'UTF-8')
					}
				} else {
					$campoid="id";
					if ($campo=="bodega" or $campo=="distribuidor") {$campoid="codigo"; }
			
					echo campo($campo,$columnas_etiquetas[$i],'select2',valores_combobox_db($columnas_combo2[$key],$row[$campo],$columnas_combo3[$key],'','','..',$campoid),$campoclase);
					}
				
				
			} else {
			
				if ($columnas_mask[$i]<>''){$campoclase.=' data-mask="'.$columnas_mask[$i].'" '; }
			 echo campo($campo,$columnas_etiquetas[$i],$campotipo, $row[$campo],$campoclase); //htmlentities($row[$campo], ENT_QUOTES, 'UTF-8')
			
			}
			
			 
			 $i++;
		}
		
	}
	
	$conn -> close();
	}  else {echo '<div class="row-fluid">	<div class="alert alert-info">No se encontraron Registros</div></div>'; exit; }
	} 
	?>



 	
<div class="row-fluid">
	<div class="span12">
				<!--HERE WRITE THE RESPONSE DATA -->
				<div id="cargando" style="display: none;" align="center" > <img src="images/load.gif"/></div>
			<div id ="salida"  align="center">	</div> 
			
			<!---END-->
	</div>
</div>
 <div class="row-fluid">
	    <div class="form-actions" id="botones">  
	    	
	    	<div class="span3">
            <button id="btnsend" onClick = "guardarforma()" class="btn btn-small btn-primary" type="button"><i class="icon-ok icon-white"></i> Guardar</button>   
          <button id="btncerrar" onClick = "actualizarbox('pagina','crud.php?tbl=<?php echo $numtabla; ?>&a=b&rid=<?php echo $rid; ?>') ; return false;" class="btn btn-small " type="button"><i class="icon-arrow-left "></i> Cerrar</button>
        
          
<?php if ($nuevo_registro==false) { ?>
	<?php if ($numtabla<>28) { ?>
    <br>
    <br>
    <br>
<button id="btnborrar" onClick = "confirmar_borrar()" class="btn btn-small btn-danger" type="button"><i class="icon-trash icon-white"></i> Borrar</button>  

<?php } 
	}?>
  </div>
</div>



       
  </div>	
 </form>
       </div>
	</div>
</div>
<?php	
	
	}


//### update ### 
if ($accion=="u") {

if ($rid==0) {$nuevo_registro=true;} else {$rid=GetSQLValue($conn->real_escape_string($rid),"int");}
	
	 	$i=0;
		$sqlcampos="";
   		foreach ($columnas as $campo) {
			
   		 	if ($campo!='id') {
   		 			
				if ($tabla=="usuario" and $campo=="clave") {
					if ($nuevo_registro==true) {
						if ($sqlcampos!="") {$sqlcampos.=" , ";}	
						$sqlcampos.= $campo."=".GetSQLValue(generate_hash($conn->real_escape_string($_REQUEST[$campo])),$columnas_tipo[$i]);
					} else {
						
					}	
				} else {
				if ($sqlcampos!="") {$sqlcampos.=" , ";}
   		 		$sqlcampos.= $campo."=".GetSQLValue($conn->real_escape_string($_REQUEST[$campo]),$columnas_tipo[$i]);
				}
			}
			
			$i++;
		}
		
		

	if ($nuevo_registro==true) {
		$sql="insert into ". $tabla . " set " . $sqlcampos;
	} else {
		$sql="update ". $tabla . " set " .$sqlcampos ." where id=".$rid;
	 
	}
		

 if ($conn->query($sql) === TRUE) {
	$stud_arr[0]["pcode"] = 1;
	$stud_arr[0]["pmsg"] ='<div class="row-fluid"><div class="alert alert-success">El registro ha sido guardado</div></div>';    }

	// //MALO actualizar union de habitaciones
	 // if ($tabla=="habitacion"){
		// $unionid=GetSQLValue($conn->real_escape_string($_REQUEST['id']),"int");
		// $union_anterior=get_dato_sql("habitacion","union_habitacion_id"," where id=$unionid") ;
	 	// $numhabunion=GetSQLValue($conn->real_escape_string($_REQUEST['union_habitacion_id']),"int");
		// if (!es_nulo($union_anterior)){ $conn->query("update habitacion set union_habitacion_id=null where id=".$union_anterior );}
	 	// if (!es_nulo($unionid) and !es_nulo($numhabunion)) {$conn->query("update habitacion set union_habitacion_id=".$rid. " where id=".$numhabunion );}
// 
	//   }
	 
    else {
    $stud_arr[0]["pcode"] = 0;
	$stud_arr[0]["pmsg"] ='<div class="row-fluid"><div class="alert alert-error">Se produjo un error al guardar el registro <br>'.$conn->error.'</div></div>';
    }

    $conn->close();
	

echo salida_json($stud_arr);
exit;
	
}


//### delete ### 
if ($accion=="d") {
	$sql="delete from ".$tabla . " where id=".$rid; ;
	 if ($conn->query($sql) === TRUE) {
      $salida='<div class="alert alert-info">El registro ha sido borrado</div>';
    }
    else {
      $salida='<div class="alert alert-error">No se pudo eliminar el registro</div>';
    }

    $conn->close();
	
	?>
	
	<div class="row-fluid" >
	<div class="span12">
		<div class="pmbox round">
        <h2><?php echo $tabla_etiqueta; ?></h2>
        <div class="pmblock">
		
	<div class="row-fluid">
	<?php echo $salida ?>
	</div>
	<div class="row-fluid">
		<button id="btncerrar" onClick = "actualizarbox('pagina','crud.php?tbl=<?php echo $numtabla; ?>&a=b&rid=<?php echo $rid; ?>') ; return false;" class="btn btn-small " type="button"><i class="icon-arrow-left "></i> Regresar</button>
	</div>
	       </div>
	</div>
</div>
	<?php
}

//### Browse ### 
if ($accion=="b") {
	?>
<script type="text/javascript" charset="utf-8">

			
	
 var oTable;

$(document).ready(function() {


     
 /* Add a click handler to the rows - this could be used as a callback */
    $("#tabla tbody").click(function(event) {
        $(oTable.fnSettings().aoData).each(function (){
            $(this.nTr).removeClass('row_selected');
        });
        $(event.target.parentNode).addClass('row_selected');
    });	
 
	 
    /* Init the table */
    var oTable = $('#tabla').dataTable(     	{
		//	"bAutoWidth": true,
			"bFilter": true,
			"sPaginationType": "full_numbers",
			//"bPaginate": false,
			//"bSort": false,
        	//"bInfo": false,
        	"bStateSave": true,
        	
        	"responsive": true,
        	
        	"dom": '<"clear">lfrtTip',
        	"oTableTools": {
	        "sSwfPath": "js/datatable/extensions/TableTools/swf/copy_csv_xls_pdf.swf"
	        },
    
       		//  "bLengthChange": true,

        	//"sDom": '<"top"i>rt<"bottom"flp><"clear">',
        	//"sDom": '<"top"iflp<"clear">>rt<"bottom"iflp<"clear">>',
        //	 "sScrollY": "350px",
        	// "sScrollX": "100px",
        	 
        "bScrollCollapse": true,
			"bProcessing": true,
			"bServerSide": true,
			"sAjaxSource": "crud_trans.php?tbl=<?php echo $numtabla; ?>",
			"bJQueryUI": false,
			
			"fnRowCallback": function( nRow, aData, iDisplayIndex ) {
            	
                $('td:eq(0)', nRow).html('<button type="button" onclick="actualizarbox(\'pagina\',\'crud.php?tbl=<?php echo $numtabla; ?>&a=r&rid='+aData[0]+'\') ; return false; " class="btn btn-sm"><i class="icon-pencil"></i> Abrir</button><?php if ($tabla=="usuario_grupo") {echo '<br><button type="button" onclick="asignar_niveles(\'+aData[0]+\')" class="btn btn-sm btn-warning"><i class="icon-lock icon-white"></i> Permisos</button>  <br><button type="button" onclick="asignar_distribuidor(\'+aData[0]+\')" class="btn btn-sm btn-info"><i class="icon-lock icon-white"></i> Asignar clientes</button>     <button type="button" onclick="asignar_bodega(\'+aData[0]+\')" class="btn btn-sm btn-info"><i class="icon-lock icon-white"></i> Asignar almacenes</button>' ;}?>' );
      
        },
			
			"oLanguage": {
				    "sProcessing":     "Procesando...",
				    "sLengthMenu":     "Mostrar _MENU_ registros",
				    "sZeroRecords":    "No se encontraron resultados",
				    "sEmptyTable":     "Ningun dato disponible en esta tabla",
				    "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
				    "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
				    "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
				    "sInfoPostFix":    "",
				    "sSearch":         "Buscar:",
				    "sUrl":            "",
				    "sInfoThousands":  ",",
				    "sLoadingRecords": "Cargando...",
				    "oPaginate": {
				        "sFirst":    "Primero",
				        "sLast":     "Ultimo",
				        "sNext":     "Siguiente",
				        "sPrevious": "Anterior"
				    },
				    "oAria": {
				        "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
				        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
				    }

			},
			
			"aoColumnDefs": [
                        { "bSearchable": false,"bSortable": false,  "aTargets": [ 0 ] }
                        //,{ "sType": "date", "bVisible": true, "aTargets": [ 1 ] }
                    ] 
			
			
    } 
    );
	  
	  /* Evento del filtro de datos */
 jQuery.fn.dataTableExt.oApi.fnSetFilteringEnterPress = function ( oSettings ) {
    var _that = this;

    this.each( function ( i ) {
        $.fn.dataTableExt.iApiIndex = i;
        var
            $this = this, 
            oTimerId = null, 
            sPreviousSearch = null,
            anControl = $( 'input', _that.fnSettings().aanFeatures.f );

            anControl
              .unbind( 'keyup' )
              .bind( 'keyup', function(e) {

              if (  e.keyCode == 13){ //anControl.val().length > 2 &&
                _that.fnFilter( anControl.val() );
              }
        });

        return this;
    } );
    return this;
}

	  $('#tabla').dataTable().fnSetFilteringEnterPress();
	  

} );


		</script>
	
	<div class="row-fluid" >
	<div class="span12">
		<div class="pmbox round ">
        <h2><?php echo $tabla_etiqueta; ?></h2>
        <div class="pmblock">
	
	
	<div class="row-fluid">
		<p><button onclick="actualizarbox('pagina','crud.php?tbl=<?php echo $numtabla; ?>&a=c&rid=0') ; return false; " class="btn btn-small" type="button"><i class="icon-plus"></i> Nuevo</button></p>
		</div>
	
		<div id="container">

			



			<div id="dynamic">
<table  class="display nowrap" id="tabla" width="100%" cellspacing="0">
	<thead>
		<tr>
			<th width="9%"> </th>
			<?php
			foreach ($columnas_etiquetas as $item) {
			 if ( $item <>"Clave" ){ echo "<th >".$item."</th>";}
			}
			?>
	
		</tr>

	</thead>
	
	<tbody>

		<tr>
			<td colspan="5" class="dataTables_empty">Cargando datos</td>
		</tr>
	</tbody>
	

</table>
			</div>	
			
			       </div>
	</div>
</div>
<?php	
}



?>