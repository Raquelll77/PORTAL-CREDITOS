<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (isset($_REQUEST['a'])) {
	$accion = $_REQUEST['a'];
} else {
	exit;
}
require_once('include/protect.php');
require_once('include/framework.php');

$verror = "";

$conn = new mysqli(db_ip, db_user, db_pw, db_name);
if (mysqli_connect_errno()) {
	echo mensaje("Error al Conectar a la Base de Datos [DB:101]", "danger");
	exit;
}
$conn->set_charset("utf8");


// $conn2 = sqlsrv_connect( db2_ip, array( "Database"=>db2_dbn, "UID"=>db2_usuario, "PWD"=>db2_clave) );	if( $conn2 === false ) { echo mensaje("Error al Conectar a la Base de Datos [DB:102]","danger");exit;}
// $stmt2 = sqlsrv_query( $conn2, $sql );	
// if( $stmt2 === false) {   die( print_r( sqlsrv_errors(), true) );}
// 
// //if (sqlsrv_has_rows($stmt)===true) {    }
// 
// while( $row = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC) ) {
//    
// //     echo $row['WhsCode'].", ".$row['WhsName'].", ".$row['U_CardCode']."<br />";
// //	 echo $row['CardCode'].", ".$row['CardName']."<br />";
// echo $row["ModeloESpecifico"] ." - ".$row["SerieChasis"] ." - ". $row["SerieMotor"] ." - ". $row["anio"] ." - ". $row["Marca"] ." - ". $row["Almacen"] ."<br>";	
// 	
// }

if ($accion == "ax") {
	$row = array();
	$return_arr = array();
	$row_array = array();
	$tp = "";

	if ((isset($_GET['term']) && strlen($_GET['term']) > 0) || (isset($_GET['id']) && is_numeric($_GET['id']))) {
		if (isset($_GET['tp'])) {
			$tp = $conn->real_escape_string($_GET['tp']);
		}
		if (isset($_GET['term'])) {
			$getVar = $conn->real_escape_string($_GET['term']);
			$whereClause =  " and ( SerieMotor LIKE '%" . $getVar . "%' ) ";
		} elseif (isset($_GET['id'])) {
			$whereClause =  " SerieMotor = '" . $conn->real_escape_string($_GET['id']) . "'";
		}

		if (isset($_GET['page_limit'])) {
			$limit = intval($_GET['page_limit']);
		} else {
			$limit = 20;
		}
		//SAP
		$sql = "SELECT TOP $limit SerieMotor,Marca, (Modelo +' '+Marca) AS text FROM serie WHERE  [Status]  = '0' and  Whscode='TRO-02' $whereClause ";



		$conn2 = sqlsrv_connect(db2_ip, array("Database" => db2_dbn, "UID" => db2_usuario, "PWD" => db2_clave));
		if ($conn2 === false) {
			echo mensaje("Error al Conectar a la Base de Datos [DB:102]", "danger");
			exit;
		}
		$stmt2 = sqlsrv_query($conn2, $sql);
		if ($stmt2 === false) {
			die(print_r(sqlsrv_errors(), true));
		}

		if (sqlsrv_has_rows($stmt2) === true) {
			while ($row = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)) {
				$row_array['id'] = $row['SerieMotor'];
				$row_array['text'] = utf8_encode($row['SerieMotor'] . " - " . $row["Marca"]);
				array_push($return_arr, $row_array);
			}
		}
	} else {
		$row_array['id'] = 0;
		$row_array['text'] = utf8_encode('Escriba....');
		array_push($return_arr, $row_array);
	}

	$ret = array();

	if (isset($_GET['id'])) {
		$ret = $row_array;
	} else {
		$ret['results'] = $return_arr;
	}

	$conn->close();
	echo salida_json($ret);
	exit;
}


//####################################
// TODO submenu garantia
if ($accion == "sub1") {
	echo " <p>&nbsp;</p>";
	if (tiene_permiso(5)) {
		echo " <p><br><a href=\"#\" onclick=\"actualizarbox('pagina','get.php?a=11') ; return false;\" class=\"btn btn-lg btn-primary btn-block \">ACTIVAR GARANTIA</a></p>";
	}
	if (tiene_permiso(4)) {
		echo " <p><br><a href=\"#\" onclick=\"actualizarbox('pagina','get.php?a=14') ; return false;\" class=\"btn btn-lg btn-primary btn-block \">CONSULTAR GARANTIAS</a></p>";
	}
	if (tiene_permiso(10)) {
		echo " <p><br><a href=\"#\" onclick=\"actualizarbox('pagina','get.php?a=18&b=p') ; return false;\" class=\"btn btn-lg btn-primary btn-block \">CONSULTAR PENDIENTES FACTURAR</a></p>";
	}
	if (tiene_permiso(11)) {
		echo " <p><br><a href=\"#\" onclick=\"actualizarbox('pagina','get.php?a=18&b=p') ; return false;\" class=\"btn btn-lg btn-primary btn-block \">CONSULTAR FACTURADAS</a></p>";
	}

	echo " <br><p><br><a href=\"inicio.php\"  class=\"btn btn-lg btn-default btn-block \">REGRESAR</a></p>";
}




//####################################
// TODO modificar contrase�a
if ($accion == "7") {
	$a2 = "";
	$o1 = "";
	$o2 = "";
	$o3 = "";
	$o4 = "";

	if (isset($_REQUEST['o1'])) {
		$o1 = $conn->real_escape_string($_REQUEST['o1']);
	}
	if (isset($_REQUEST['o2'])) {
		$o2 = $conn->real_escape_string($_REQUEST['o2']);
	}
	if (isset($_REQUEST['o3'])) {
		$o3 = $conn->real_escape_string($_REQUEST['o3']);
	}
	if (isset($_REQUEST['o4'])) {
		$o4 = $conn->real_escape_string($_REQUEST['o4']);
	}
	if (isset($_REQUEST['a2'])) {
		$a2 = $_REQUEST['a2'];
	}


	if ($o1 == 2) { //modificar clave usuario propio

		if ($a2 == "u") {
			$verror = "";
			if ($o2 == "" or $o3 == "") {
				echo "Debe ingresar la contrase&ntilde;a actual y la nueva";
				exit;
			}
			$verror = validar("Contrase&ntilde;a Nueva", $o3, "text", true, null, 6);
			if ($verror <> "") {
				echo $verror;
				exit;
			}

			$cactual = get_dato_sql("usuario", "clave", " where id=" . $_SESSION['usuario_id']);
			if (!verificar_password($o2, $cactual)) {
				echo "La contrase&ntilde;a actual no es correcta";
				exit;
			}

			$sqlu = "update usuario set clave='" . generate_hash($o3) . "' where id=" . $_SESSION['usuario_id'];

			if ($conn->query($sqlu) === TRUE) {
				echo "ok";
			} else {
				echo "Error al actualizar contrase&ntilde;a";
			}

			exit;
		}

		echo '<h6></h6><div class="row-fluid">
		<div class="control-group"><label class="control-label">Contrase&ntilde;a Actual:</label><div class="controls"> <input name="c1" id="mcca" type="password" class="" ></div></div>
		<hr class="pmshr2" />
		<div class="control-group"><label class="control-label">Contrase&ntilde;a Nueva:</label><div class="controls"> <input name="c2" id="mccb" type="password" class="" ></div></div>
		</div>
		';
	}

	if ($o1 == 3) { //modificar clave desde administrador

		if ($a2 == "u") {
			$verror = "";
			if ($o4 == "" or $o3 == "") {
				echo "Debe ingresar la contrase&ntilde;a nueva";
				exit;
			}
			$verror = validar("Contrase&ntilde;a Nueva", $o3, "text", true, null, 6);
			if ($verror <> "") {
				echo $verror;
				exit;
			}


			$sqlu = "update usuario set clave='" . generate_hash($o3) . "' where id=" . $o4;

			if ($conn->query($sqlu) === TRUE) {
				echo "ok";
			} else {
				echo "Error al actualizar contrase&ntilde;a";
			}

			exit;
		}

		echo '<h6></h6><div class="row-fluid"> 
		<div class="control-group"><label class="control-label">Contrase&ntilde;a Nueva:</label><div class="controls"> <input name="c2" id="mccb" type="password" class="" ></div></div>
		</div>
		';
	}

	exit;
} // accion 7


//####################################
// TODO asignar permisos usuario
if ($accion == "8") {
	if (!tiene_permiso(1)) {
		exit;
	}

	if (isset($_REQUEST['o'])) {
		$opcion = $_REQUEST['o'];
	} else exit;
	if (isset($_REQUEST['rid'])) {
		$r_id = $_REQUEST['rid'];
	} else exit;
	//*** guardar  ****
	if ($opcion == "sv") {
		$salida = "";
		$sql_errores = false;

		$losdatos = json_decode(stripslashes($_POST['valores']), true);

		$conn->autocommit(FALSE);

		// borrar registros actuales
		if ($r_id <> 0 and $r_id <> "") {
			if ($conn->query("delete from usuario_nivelxgrupo where grupo_id=$r_id") === FALSE) {
				$sql_errores = true;
			}
		}


		// guardar registros
		if (count($losdatos) > 0) {
			foreach ($losdatos as $value) {
				if ($conn->query("insert into usuario_nivelxgrupo set grupo_id=$r_id , nivel_id=$value") === FALSE) {
					$sql_errores = true;
					break;
				}
			}
		}

		if ($sql_errores == true) {
			$conn->rollback();
		} else {
			$conn->commit();
			$salida = "ok";
		}
		$conn->autocommit(TRUE);
		echo $salida;
		exit;
	}

	//Listar listbox
	if ($opcion == "lb") {
?>
		<p>&nbsp;</p>
		<form name="form" method="post" id="duallist">
			<table align="center" width="80%">
				<tr>
					<td>
						<strong>DISPONIBLES:</strong><br />
						Filtro: <input type="text" id="box1Filter" class="input-medium" /><button class="btn btn-mini " type="button" id="box1Clear"><i class="glyphicon glyphicon-remove"></i></button><br />

						<select id="box1View" multiple="multiple" style="height:350px;width:300px;">
							<?php echo valores_combobox_db('usuario_nivel', '', "nombre as texto", " order by nombre", 'texto', false); ?>

						</select><br />

						<span id="box1Counter" class="countLabel"></span> <select id="box1Storage"></select>
					</td>
					<td>
						<button id="to2" type="button" class="btn btn-small pmsseparador "><i class="glyphicon glyphicon-chevron-right"></i></button>
						<button id="allTo2" type="button" class="btn btn-small pmsseparador  "><i class="glyphicon glyphicon-forward"></i></button>
						<button id="allTo1" type="button" class="btn btn-small pmsseparador  "><i class="glyphicon glyphicon-backward"></i></button>
						<button id="to1" type="button" class="btn btn-small pmsseparador  "><i class="glyphicon glyphicon-chevron-left"></i></button>
					</td>
					<td>
						<strong>ASIGNADOS:</strong><br />
						Filtro: <input type="text" id="box2Filter" class="input-medium" /><button class="btn btn-mini" type="button" id="box2Clear"><i class="glyphicon glyphicon-remove"></i></button><br />

						<select id="box2View" multiple="multiple" style="height:350px;width:300px;">

							<?php if ($r_id <> 0 and $r_id <> "") {
								echo valores_combobox_db_permisos_asignados($r_id);
							} ?>
						</select><br />
						<span id="box2Counter" class="countLabel"></span><select id="box2Storage"></select>
					</td>
				</tr>
			</table>
		</form>

		<script language="javascript" type="text/javascript">
			$(function() {
				$.configureBoxes();
			});
		</script>

	<?php
		exit;
	}
} //opcion 8


//####################################
// TODO asignar permisos grupo distribuidor y  clientes
if ($accion == "81" or $accion == "82") {
	if (!tiene_permiso(1)) {
		exit;
	}

	if ($accion == "81") {
		$tabla = "distribuidor";
	} else {
		$tabla = "bodega";
	}

	if (isset($_REQUEST['o'])) {
		$opcion = $_REQUEST['o'];
	} else exit;
	if (isset($_REQUEST['rid'])) {
		$r_id = $_REQUEST['rid'];
	} else exit;
	//*** guardar  ****
	if ($opcion == "sv") {
		$salida = "";
		$sql_errores = false;

		$losdatos = json_decode(stripslashes($_POST['valores']), true);

		$conn->autocommit(FALSE);

		// borrar registros actuales
		if ($r_id <> 0 and $r_id <> "") {
			if ($conn->query("delete from " . $tabla . "xusuario_grupo where grupo_id=$r_id") === FALSE) {
				$sql_errores = true;
			}
		}


		// guardar registros
		if (count($losdatos) > 0) {
			foreach ($losdatos as $value) {
				if ($conn->query("insert into " . $tabla . "xusuario_grupo set grupo_id=$r_id , " . $tabla . "_id='$value'") === FALSE) {
					$sql_errores = true;
					break;
				}
			}
		}

		if ($sql_errores == true) {
			$conn->rollback();
		} else {
			$conn->commit();
			$salida = "ok";
		}
		$conn->autocommit(TRUE);
		echo $salida;
		exit;
	}

	//Listar listbox
	if ($opcion == "lb") {
	?>
		<p>&nbsp;</p>
		<form name="form" method="post" id="duallist">
			<table align="center" width="80%">
				<tr>
					<td>
						<strong>DISPONIBLES:</strong><br />
						Filtro: <input type="text" id="box1Filter" class="input-medium" /><button class="btn btn-mini " type="button" id="box1Clear"><i class="glyphicon glyphicon-remove"></i></button><br />

						<select id="box1View" multiple="multiple" style="height:350px;width:300px;">
							<?php echo valores_combobox_db($tabla, '', "nombre as texto", " order by nombre", 'texto', false, 'codigo'); ?>

						</select><br />

						<span id="box1Counter" class="countLabel"></span> <select id="box1Storage"></select>
					</td>
					<td>
						<button id="to2" type="button" class="btn btn-small pmsseparador "><i class="glyphicon glyphicon-chevron-right"></i></button>
						<button id="allTo2" type="button" class="btn btn-small pmsseparador  "><i class="glyphicon glyphicon-forward"></i></button>
						<button id="allTo1" type="button" class="btn btn-small pmsseparador  "><i class="glyphicon glyphicon-backward"></i></button>
						<button id="to1" type="button" class="btn btn-small pmsseparador  "><i class="glyphicon glyphicon-chevron-left"></i></button>
					</td>
					<td>
						<strong>ASIGNADOS:</strong><br />
						Filtro: <input type="text" id="box2Filter" class="input-medium" /><button class="btn btn-mini" type="button" id="box2Clear"><i class="glyphicon glyphicon-remove"></i></button><br />

						<select id="box2View" multiple="multiple" style="height:350px;width:300px;">

							<?php if ($r_id <> 0 and $r_id <> "") {
								echo valores_combobox_db_distribuidores_bodegas_asignados($r_id, $tabla);
							} ?>
						</select><br />
						<span id="box2Counter" class="countLabel"></span><select id="box2Storage"></select>
					</td>
				</tr>
			</table>
		</form>

		<script language="javascript" type="text/javascript">
			$(function() {
				$.configureBoxes();
			});
		</script>

	<?php
		exit;
	}
} //opcion 8



if ($accion == "11") { // TODO  pagina ver garantia
	if (!tiene_permiso(2)) {
		echo mensaje("No tiene privilegios para accesar esta seccion", "danger");
		exit;
	}


	if (!isset($_REQUEST['sub'])) {
	?>


		<div id="buscar">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">Ingrese la Serie de la Motocicleta</h4>
				</div>
				<div class="panel-body">

					<form class="form-horizontal">
						<?php

						echo campo("sb", "No. de Serie", "text", "", 'class="form-control" autofocus');
						//	echo campo('sb','No. de Serie','select2ajax','','class="form-control select22"','get.php?a=ax&tp=1',""); 


						echo campo("Aceptar", "Buscar", "boton", "", "onclick=\"procesarrep() ; return false;\" ");

						?>




					</form>

				</div>
			</div>
		</div>




		<div id="reportev"> </div>

		<script type="text/javascript">
			function procesarrep() {

				var url = "get.php?a=11&sub=1&sb=" + $("#sb").val();
				actualizarbox('reportev', url);

			}

			procesarrep();
		</script>

	<?php


	}





	if (isset($_REQUEST['sub'])) {

		if (isset($_REQUEST['sb'])) {

			$buscar = "";
			if (isset($_REQUEST['sb'])) {
				$buscar = $conn->real_escape_string($_REQUEST['sb']);
			}


			//	if ($buscar=="") { echo mensaje( "Debe ingresar el numero de serie","warning"); exit;}

			//******* SQL ************************************************************************************
			$sql =  "SELECT distinct T1.ItemCode ,T1.ItemName,T0.SuppSerial SerieChasis,
			    (select top 1 DistNumber from OSRN where MnfSerial=T0.SuppSerial)as SerieMotor,
		        (select top 1 LotNumber from OSRN where MnfSerial=T0.SuppSerial)as anio,
					  T0.WhsCode Almacen,T5.Name Marca,T6.Name Modelo,t4.Name Color,T7.Name ModeloESpecifico,T8.U_CardCode CodigoCliente
					  ,T8.WhsCode NombreAlmacen
					  FROM OSRI T0  
				      INNER JOIN OITM T1 ON T0.ItemCode = T1.ItemCode 
				      INNER JOIN OITB T2 ON T1.ItmsGrpCod =     T2.ItmsGrpCod
				      inner join [@SCOLOR] t4 on T1.U_ACOLOR = t4.Code
				      inner join [@AMARCA] T5 ON T1.U_AMARCA = T5.Code
				      INNER JOIN [@AMODELO] T6 ON T1.U_AMODELO=T6.Code
				      INNER JOIN [@OITMMODELOSMOTOS] T7 ON T1.U_ModelID=T7.Code
				      Inner join OWHS T8 on T0.WhsCode = T8.WhsCode
					  WHERE T0.[Status]  = '0' and  T2.[ItmsGrpNam] ='Motocicleta' 							
	 		  ";
			// if ($_SESSION['usuario_bodega']<>"") {$sql.=" and  T0.WhsCode='".$_SESSION['usuario_bodega']."'";} 
			// if ($_SESSION['usuario_distribuidor']<>"") {$sql.=" and T8.U_CardCode='".$_SESSION['usuario_distribuidor']."'";} 


			//DISTRIBUIDOR: select CardCode,CardName from OCRD
			//BODEGAS: select WhsCode,WhsName from OWHS


			//	$sql=  "SELECT TOP 1000 SerieChasis,SerieMotor,Marca,Modelo ,Color ,anio ,ModeloESpecifico,Almacen
			//	 		FROM serie 
			//	 		WHERE  [Status]  = '0'  ";



			if ($buscar <> "") {
				$sql .=  "  and ( T0.SuppSerial LIKE '%$buscar%' )";
			}

			if (tiene_permiso(7)) {
				$texto = armar_sql('T8.U_CardCode', $_SESSION['grupo_distribuidores'], 'or');
				if ($texto <> "") {
					$sql .= " and $texto";
				}

				$texto = armar_sql('T0.WhsCode', $_SESSION['grupo_bodegas'], 'or');
				if ($texto <> "") {
					$sql .= " and $texto";
				}
			} else {
				if ($_SESSION['usuario_bodega'] <> "") {
					$sql .= " and  T0.WhsCode='" . $_SESSION['usuario_bodega'] . "'";
				} // T0.WhsCode
				if ($_SESSION['usuario_distribuidor'] <> "") {
					$sql .= " and T8.U_CardCode='" . $_SESSION['usuario_distribuidor'] . "'";
				} //T8.U_CardCode				

			}


			// ****** Fin SQL ********************************************************************************

			$conn2 = sqlsrv_connect(db2_ip, array("Database" => db2_dbn, "UID" => db2_usuario, "PWD" => db2_clave));
			if ($conn2 === false) {
				echo mensaje("Error al Conectar a la Base de Datos [DB:102]", "danger");
				exit;
			}
			$stmt2 = sqlsrv_query($conn2, $sql);
			if ($stmt2 === false) {
				die(print_r(sqlsrv_errors(), true));
			}

			if (sqlsrv_has_rows($stmt2) === true) {
				//<div class="table-responsive">
				//<table class="table table-striped">
				$reg = 0;
				echo '<div class="row">
			        <div >
			          <table class="display nowrap" id="tabla" width="100%" cellspacing="0">
			            <thead>
			              <tr>
			               <th class="text-center"></th>
			                <th class="text-center">Marca</th>
			                <th class="text-center">Modelo</th>
			                <th class="text-center">Chasis</th>
			                <th class="text-center">Motor</th>
			                <th class="text-center">A&ntilde;o</th>
			                <th class="text-center">Color</th>
			                <th class="text-center">CC</th>
			                <th class="text-center">Almacen</th>
			                
			              </tr>
			            </thead>
			            <tbody>';

				while ($row = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)) {
					echo "<tr>
		               <td class='text-center'><a  href=\"#\" class=\"btn btn-default btn-sm\" onclick=\"actualizarbox('pagina','get.php?a=12&cid=" . trim($row["SerieMotor"]) . "') ; return false;\" ><span class=\"glyphicon glyphicon-folder-open\" aria-hidden=\"true\"></span></a></td>
		              <td class='text-center'>" . trim($row["Marca"]) . "</td>
		              <td class='text-center'>" . trim($row["Modelo"]) . "</td>
		              <td class='text-center'>" . trim($row["SerieChasis"]) . "</td>
		              <td ><a  href=\"#\" onclick=\"actualizarbox('pagina','get.php?a=12&cid=" . trim($row["SerieMotor"]) . "') ; return false;\" >" . trim($row["SerieMotor"]) . "</a></td>
		               <td class='text-center'>" . trim($row["anio"]) . "</td>
		              <td class='text-center'>" . trim($row["Color"]) . "</td>
		               <td class='text-center'>" . trim($row["ModeloESpecifico"]) . "</td>
		               <td class='text-center'>" . trim($row["Almacen"]) . "</td>
		              </tr>";
					$reg++;
				}




				echo " </tbody>
		           
		          </table>
		        </div>
		      </div>
		      ";

				//	<div class=\"row col-xs-12\">Registros <span class=\"badge\">$reg</span></div>

				echo crear_datatable('tabla');
			} else {
				echo mensaje("No se encontraron registros", "info");
				exit;
			}
		} //else { echo mensaje( "Debe ingresar informacion en el campo para buscar","warning"); exit;}

		exit;
	}
} // fin pagina PRODUCTOS

//******************************************************************************





if ($accion == "12") { // TODO  pagina Mant serie
	if (!tiene_permiso(2)) {
		echo mensaje("No tiene privilegios para accesar esta seccion", "danger");
		exit;
	}

	if (!isset($_REQUEST['cid'])) {
		echo mensaje("Debe seleccionar un registro", "warning");
		exit;
	}
	$codigo = $conn->real_escape_string($_REQUEST['cid']);
	if ($codigo == "") {
		echo mensaje("Debe seleccionar un registro", "warning");
		exit;
	}


	//******* SQL ************************************************************************************
	$sql =  "SELECT distinct T1.ItemCode ,T1.ItemName,T0.SuppSerial SerieChasis,t3.DistNumber SerieMotor,t3.LotNumber anio,
					  T0.WhsCode Almacen,T5.Name Marca,T6.Name Modelo,t4.Name Color,T7.Name ModeloESpecifico,T8.U_CardCode CodigoCliente
					  FROM OSRI T0  
				      INNER JOIN OITM T1 ON T0.ItemCode = T1.ItemCode 
				      INNER JOIN OITB T2 ON T1.ItmsGrpCod =     T2.ItmsGrpCod
				      inner join OSRN t3 on T0.ItemCode = t3.ItemCode
				      inner join [@SCOLOR] t4 on T1.U_ACOLOR = t4.Code
				      inner join [@AMARCA] T5 ON T1.U_AMARCA = T5.Code
				      INNER JOIN [@AMODELO] T6 ON T1.U_AMODELO=T6.Code
				      INNER JOIN [@OITMMODELOSMOTOS] T7 ON T1.U_ModelID=T7.Code
				      Inner join OWHS T8 on T0.WhsCode = T8.WhsCode
					  WHERE T0.[Status]  = '0' and  T2.[ItmsGrpNam] ='Motocicleta' 	and  t3.DistNumber = '$codigo'						
	 		  ";
	// if ($_SESSION['usuario_distribuidor']<>"") {$sql.=" and T8.U_CardCode='".$_SESSION['usuario_distribuidor']."'";} 


	//	$sql=  "SELECT TOP 1000 SerieChasis,SerieMotor,Marca,Modelo ,Color ,anio,ModeloESpecifico,Almacen
	//	 		FROM serie 
	//	 		WHERE  [Status]  = '0'   and ( SerieMotor = '$codigo' )							
	//	";

	if (tiene_permiso(7)) {
		$texto = armar_sql('T8.U_CardCode', $_SESSION['grupo_distribuidores'], 'or');
		if ($texto <> "") {
			$sql .= " and $texto";
		}

		$texto = armar_sql('T0.WhsCode', $_SESSION['grupo_bodegas'], 'or');
		if ($texto <> "") {
			$sql .= " and $texto";
		}
	} else {
		if ($_SESSION['usuario_bodega'] <> "") {
			$sql .= " and  T0.WhsCode='" . $_SESSION['usuario_bodega'] . "'";
		} // T0.WhsCode
		if ($_SESSION['usuario_distribuidor'] <> "") {
			$sql .= " and T8.U_CardCode='" . $_SESSION['usuario_distribuidor'] . "'";
		} //T8.U_CardCode				

	}
	// ****** Fin SQL ********************************************************************************

	$conn2 = sqlsrv_connect(db2_ip, array("Database" => db2_dbn, "UID" => db2_usuario, "PWD" => db2_clave));
	if ($conn2 === false) {
		echo mensaje("Error al Conectar a la Base de Datos [DB:102]", "danger");
		exit;
	}
	$stmt2 = sqlsrv_query($conn2, $sql);
	if ($stmt2 === false) {
		die(print_r(sqlsrv_errors(), true));
	}


	if (sqlsrv_has_rows($stmt2) == false) {
		echo mensaje("No se encontraron registros", "warning");
		exit;
	}

	$row = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC);


	$texto_seg = "text";


	?>


	<form class="form-horizontal">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h4 class="panel-title">Datos Generales</h4>
			</div>
			<div class="panel-body">


				<div class="row">
					<div class="table-responsive">

						<table class="table table-striped">
							<thead>
								<tr>
									<th>Marca</th>
									<th>Modelo</th>
									<th>Serie Chasis</th>
									<th>Serie Motor</th>
									<th>a&ntilde;o</th>
									<th>Color</th>
									<th>CC</th>
									<th>Almacen</th>


								</tr>
							</thead>
							<tbody>



								<tr>
									<?php


									// echo campotabla("Descripcion","Descripcion",$texto_seg,$row["Descripcion"],'class="form-control" ');	
									echo campotabla_columna("Marca", "Marca", $texto_seg, $row["Marca"], 'class="form-control" ');
									echo campotabla_columna("Modelo", "Modelo", $texto_seg, $row["Modelo"], 'class="form-control" ');
									echo campotabla_columna("SerieChasis", "Serie Chasis", $texto_seg, $row["SerieChasis"], 'class="form-control" ');
									echo campotabla_columna("SerieMotor", "Serie Motor", $texto_seg, $row["SerieMotor"], 'class="form-control" ');
									echo campotabla_columna("anio", "a&ntilde;o", $texto_seg, $row["anio"], 'class="form-control" ');
									echo campotabla_columna("Color", "Color", $texto_seg, $row["Color"], 'class="form-control" ');
									echo campotabla_columna("ModeloESpecifico", "CC", $texto_seg, $row["ModeloESpecifico"], 'class="form-control" ');
									echo campotabla_columna("Almacen", "Almacen", $texto_seg, $row["Almacen"], 'class="form-control" ');



									?>
								</tr>

							</tbody>
						</table>
					</div>
				</div>


			</div>
		</div>



		<br>
	</form>

	<?php


	if (tiene_permiso(5)) {

		if ((trim($row["Almacen"]) == trim($_SESSION['usuario_bodega'])) or tiene_permiso(9)) {
	?>
			<a href="#" class="btn btn-primary" onclick="actualizarbox('activar_garantia','get.php?a=13&cid=<?php echo urldecode($row["SerieMotor"]); ?>') ; $(this).hide(); return false;"><span class="glyphicon glyphicon-certificate" aria-hidden="true"></span> Activar Garant&iacute;a</a>
	<?php }
	} ?>
	<form class="form-horizontal" id="garantiaform">



		<?php
		echo campo("Marca", "", 'hidden', $row["Marca"], '');
		echo campo("Modelo", "", 'hidden', $row["Modelo"], '');
		echo campo("anio", "", 'hidden', $row["anio"], '');
		echo campo("Color", "", 'hidden', $row["Color"], '');
		echo campo("ModeloESpecifico", "", 'hidden', $row["ModeloESpecifico"], '');
		echo campo("SerieMotor", "", 'hidden', $row["SerieMotor"], '');
		echo campo("SerieChasis", "", 'hidden', $row["SerieChasis"], '');
		echo campo("Almacen", "", 'hidden', $row["Almacen"], '');

		?>
		<div id="activar_garantia">



		</div>
	</form>






<?php


	echo boton_regresar(11);
} // fin pagina 





//******************************************************************************


if ($accion == "13") { // TODO  Activar serie
	if (!tiene_permiso(5)) {
		echo mensaje("No tiene privilegios para accesar esta seccion", "danger");
		exit;
	}


	//guardar garantia
	if (isset($_REQUEST['s'])) {

		//########## validar datos
		$verror = "";

		// $verror.=validar("Numero de Factura",$_REQUEST['factura_numero'], "text", true,  null,  null,  null);
		$verror .= validar("Fecha de Compra", $_REQUEST['fecha_compra'], "date", true,  null,  null,  null);

		$verror .= validar("Nombres", $_REQUEST['nombres'], "text", true,  null,  3,  null);
		$verror .= validar("Apellidos", $_REQUEST['apellidos'], "text", true,  null,  3,  null);
		$verror .= validar("Identidad", $_REQUEST['identidad'], "text", true,  null,  13,  null);
		$verror .= validar("Direccion", $_REQUEST['direccion'], "text", true,  null,  5,  null);
		$verror .= validar("Ciudad", $_REQUEST['ciudad'], "text", true,  null,  5,  null);
		$verror .= validar("Departamento", $_REQUEST['departamento'], "text", true,  null,  5,  null);
		$verror .= validar("Telefono", $_REQUEST['telefono'], "text", true,  null,  8,  null);
		$verror .= validar("Celular", $_REQUEST['celular'], "text", true,  null,  8,  null);
		$verror .= validar("Sexo", $_REQUEST['sexo'], "text", true,  null,  1,  null);

		// if (isset($_REQUEST["doc1"])) {$verror.=validar("Documento Adjunto 1",$_REQUEST['doc1'], "text", true,  null,  5,  null); }
		// if (isset($_REQUEST["doc2"])) {$verror.=validar("Documento Adjunto 2",$_REQUEST['doc2'], "text", true,  null,  5,  null); }
		// if (isset($_REQUEST["doc3"])) {$verror.=validar("Documento Adjunto 3",$_REQUEST['doc3'], "text", true,  null,  5,  null); }
		// if (isset($_REQUEST["doc4"])) {$verror.=validar("Documento Adjunto 4",$_REQUEST['doc4'], "text", true,  null,  5,  null); }
		// if (isset($_REQUEST["doc5"])) {$verror.=validar("Documento Adjunto 5",$_REQUEST['doc5'], "text", true,  null,  5,  null); }
		// if (isset($_REQUEST["doc6"])) {$verror.=validar("Documento Adjunto 6",$_REQUEST['doc6'], "text", true,  null,  5,  null); }


		// ######### Guardar garantia
		if ($verror == "") {


			$sqlcampos = "";
			$sqlcampos .= "  marca =" . GetSQLValue($conn->real_escape_string($_REQUEST["Marca"]), "text");
			$sqlcampos .= " , modelo =" . GetSQLValue($conn->real_escape_string($_REQUEST["Modelo"]), "text");
			$sqlcampos .= " , color =" . GetSQLValue($conn->real_escape_string($_REQUEST["Color"]), "text");
			$sqlcampos .= " , estilo =" . GetSQLValue($conn->real_escape_string($_REQUEST["ModeloESpecifico"]), "text");
			$sqlcampos .= " , Serie_motor =" . GetSQLValue($conn->real_escape_string($_REQUEST["SerieMotor"]), "text");
			$sqlcampos .= " , Serie_chasis =" . GetSQLValue($conn->real_escape_string($_REQUEST["SerieChasis"]), "text");
			$sqlcampos .= " , anio =" . GetSQLValue($conn->real_escape_string($_REQUEST["anio"]), "text");

			$sqlcampos .= " , factura_numero =" . GetSQLValue($conn->real_escape_string($_REQUEST["factura_numero"]), "text");
			$sqlcampos .= " , fecha_compra =" . GetSQLValue(mysqldate($conn->real_escape_string($_REQUEST["fecha_compra"])), "text");
			$sqlcampos .= " , nombres =" . GetSQLValue($conn->real_escape_string($_REQUEST["nombres"]), "text");
			$sqlcampos .= " , apellidos =" . GetSQLValue($conn->real_escape_string($_REQUEST["apellidos"]), "text");
			$sqlcampos .= " , identidad =" . GetSQLValue($conn->real_escape_string($_REQUEST["identidad"]), "text");
			$sqlcampos .= " , direccion =" . GetSQLValue($conn->real_escape_string($_REQUEST["direccion"]), "text");
			$sqlcampos .= " , ciudad =" . GetSQLValue($conn->real_escape_string($_REQUEST["ciudad"]), "text");
			$sqlcampos .= " , departamento =" . GetSQLValue($conn->real_escape_string($_REQUEST["departamento"]), "text");
			$sqlcampos .= " , telefono =" . GetSQLValue($conn->real_escape_string($_REQUEST["telefono"]), "text");
			$sqlcampos .= " , celular =" . GetSQLValue($conn->real_escape_string($_REQUEST["celular"]), "text");
			$sqlcampos .= " , sexo =" . GetSQLValue($conn->real_escape_string($_REQUEST["sexo"]), "text");

			if (isset($_REQUEST["doc1"])) {
				$sqlcampos .= " , doc1 =" . GetSQLValue($conn->real_escape_string($_REQUEST["doc1"]), "text");
			}
			if (isset($_REQUEST["doc2"])) {
				$sqlcampos .= " , doc2 =" . GetSQLValue($conn->real_escape_string($_REQUEST["doc2"]), "text");
			}
			if (isset($_REQUEST["doc3"])) {
				$sqlcampos .= " , doc3 =" . GetSQLValue($conn->real_escape_string($_REQUEST["doc3"]), "text");
			}
			if (isset($_REQUEST["doc4"])) {
				$sqlcampos .= " , doc4 =" . GetSQLValue($conn->real_escape_string($_REQUEST["doc4"]), "text");
			}
			if (isset($_REQUEST["doc5"])) {
				$sqlcampos .= " , doc5 =" . GetSQLValue($conn->real_escape_string($_REQUEST["doc5"]), "text");
			}
			if (isset($_REQUEST["doc6"])) {
				$sqlcampos .= " , doc6 =" . GetSQLValue($conn->real_escape_string($_REQUEST["doc6"]), "text");
			}

			$sqlcampos .= ",usuario_alta= '" . $_SESSION['usuario'] . "' ,fecha_alta=now()";
			$sqlcampos .= ",distribuidor= '" . $_SESSION['usuario_distribuidor'] . "' ,distribuidor_nombre='" . $_SESSION['usuario_distribuidor_nombre'] . "'";
			$sqlcampos .= ",bodega= '" . $_SESSION['usuario_bodega'] . "' ,bodega_nombre='" . $_SESSION['usuario_bodega_nombre'] . "'";


			$sql = "insert into garantia set " . $sqlcampos;

			if ($conn->query($sql) === TRUE) {
				$insert_id = mysqli_insert_id($conn);
				$stud_arr[0]["pcode"] = 1;
				$stud_arr[0]["pmsg"] = 'Los datos fueron guardados satisfactoriamente. El numero de garantia es: <strong>' . $insert_id . '</strong>';
				$stud_arr[0]["pcodid"] = $insert_id;
			} else {
				$stud_arr[0]["pcode"] = 0;
				$stud_arr[0]["pmsg"] = 'Se produjo un error al guardar el registro DB101:<br>' . $conn->error;
				$stud_arr[0]["pcodid"] = 0;
			}

			$conn->close();
		} else {
			//mostrar errores validacion
			$stud_arr[0]["pcode"] = 0;
			$stud_arr[0]["pmsg"] = 'Error en los datos:</strong><br>' . $verror;
			$stud_arr[0]["pcodid"] = 0;
		}


		echo salida_json($stud_arr);
		exit;
	} // fin guardar garantia




	if (!isset($_REQUEST['cid'])) {
		echo mensaje("Debe seleccionar un registro", "warning");
		exit;
	}
	$codigo = $conn->real_escape_string($_REQUEST['cid']);
	if ($codigo == "") {
		echo mensaje("Debe seleccionar un registro", "warning");
		exit;
	}


?>



	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title">Datos del Cliente</h4>
		</div>
		<div id="datosgenerales" class="panel-body">


			<div class="row">
				<div class="col-xs-12">


					<?php



					echo campo("factura_numero", "No. Factura", 'text', '', 'class="form-control" ', '', '', 3, 3);

					echo campo("fecha_compra", "Fecha de Compra", 'date', '', 'class="form-control" ', '', '', 3, 3);

					echo campo("nombres", "Nombres", 'text', '', 'class="form-control" ', '', '', 3, 7);
					echo campo("apellidos", "Apellidos", 'text', '', 'class="form-control" ', '', '', 3, 7);
					echo campo("identidad", "No. de Cedula", 'text', '', 'class="form-control" ', '', '', 3, 4);
					echo campo("direccion", "Direccion Completa", 'text', '', 'class="form-control" ', '', '', 3, 7);
					echo campo("ciudad", "Ciudad", 'text', '', 'class="form-control" ', '', '', 3, 4);
					echo campo("departamento", "Departamento", 'select', valores_combobox_texto('<option value="">Seleccione</option>
<option value="Atlantida">Atlantida</option>
<option value="Choluteca">Choluteca</option>
<option value="Colon">Colon</option>
<option value="Comayagua">Comayagua</option>
<option value="Copan">Copan</option>
<option value="Cortes">Cortes</option>
<option value="El Paraiso">El Paraiso</option>
<option value="Francisco Morazan">Francisco Morazan</option>
<option value="Gracias a Dios">Gracias a Dios</option>
<option value="Intibuca">Intibuca</option>
<option value="Islas de la Bahia">Islas de la Bahia</option>
<option value="La Paz">La Paz</option>
<option value="Lempira">Lempira</option>
<option value="Ocotepeque">Ocotepeque</option>
<option value="Olancho">Olancho</option>
<option value="Santa Barbara">Santa Barbara</option>
<option value="Valle">Valle</option>
<option value="Yoro">Yoro</option>', ''), 'class="form-control" ', '', '', 3, 4);

					echo campo("telefono", "Telefono Casa", 'text', '', 'class="form-control" ', '', '', 3, 4);
					echo campo("celular", "Celular", 'text', '', 'class="form-control" ', '', '', 3, 4);
					echo campo("sexo", "Sexo", 'select', valores_combobox_texto('<option value="">Seleccione</option><option value="MASCULINO">MASCULINO</option><option value="FEMENINO">FEMENINO</option>', ''), 'class="form-control" ', '', '', 3, 3);


					?>


				</div>
			</div>


		</div>
	</div>




	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title">Documentos Adjuntos</h4>
		</div>
		<div id="adjuntos" class="panel-body">

			<div class="row">

				<?php
				echo campo("doc1", "Identidad", 'upload', '', 'class="form-control" ');
				echo campo("doc2", "RTN", 'upload', '', 'class="form-control" ');
				echo campo("doc3", "Notificacion de Venta", 'upload', '', 'class="form-control" ');
				echo campo("doc4", "Comprobante de Entrega", 'upload', '', 'class="form-control" ');
				echo campo("doc5", "Contrato", 'upload', '', 'class="form-control" ');
				?>
			</div>


		</div>
	</div>

	<div id="botones">
		<a id="btnguardar" href="#" class="btn btn-primary" onclick="procesarforma() ; return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Guardar</a>
		<a id="btnimprimir" href="#" style="display: none;" class="btn btn-info" onclick="imprimir_garantia($('#ridg').val()) ; return false;"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Imprimir Garantia</a>
		<input id="ridg" name="" ridg type="hidden" value="" />

		<img id="cargando" style="display: none;" src="images/load.gif" />
		<div class="row">
			<br>
			<div id="salida"> </div>
		</div>
	</div>
	<script>
		function procesarforma() {
			$("#botones *").attr("disabled", "disabled");
			$("#garantiaform :input").attr('readonly', true);
			$('#cargando').show();
			var myTable = '';



			var url = "get.php?a=13&s=1";
			$.getJSON(url, $("#garantiaform").serialize(), function(json) {

				i = 1;
				if (json.length > 0) {
					if (json[0].pcode == 0) {

						$('#salida').empty().append('<div class="alert alert-warning" role="alert">' + json[0].pmsg + '</div>');

					}
					if (json[0].pcode == 1) {

						if (json[0].pcodid != 0) {
							$("#ridg").val(json[0].pcodid);
							$('#btnimprimir').show();

						}
						$("#datosgenerales *").attr("disabled", "disabled");
						$("#adjuntos *").attr("disabled", "disabled");
						$('#btnguardar').hide();
						$('#salida').empty().append('<div class="alert alert-success" role="alert">' + json[0].pmsg + '</div>');

					}
				} else {
					$('#salida').empty().append('<div class="alert alert-danger" role="alert">Se produjo un error en comunicacion JSON:101</div>');
				}

			}).error(function() {
				$('#salida').empty().append('<div class="alert alert-danger" role="alert">Se produjo un error en comunicacion JSON:102</div>');
			}).complete(function() {

				$('#cargando').hide();
				$("#garantiaform :input").attr('readonly', false);
				$("#botones *").removeAttr("disabled");
			});

		}
	</script>

	<?php



} // fin pagina


//******************************************************************************


if ($accion == "14") { // TODO  consultar garantias activadas
	if (!tiene_permiso(4)) {
		echo mensaje("No tiene privilegios para accesar esta seccion", "danger");
		exit;
	}


	if (!isset($_REQUEST['sub'])) {
	?>


		<div id="buscar">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">Ingrese la Serie de la Motocicleta</h4>
				</div>
				<div class="panel-body">

					<form class="form-horizontal">
						<?php

						echo campo("sb", "No. de Serie", "text", "", 'class="form-control" autofocus');

						echo campo("Aceptar", "Buscar", "boton", "", "onclick=\"procesarrep() ; return false;\" ");

						?>




					</form>

				</div>
			</div>
		</div>




		<div id="reportev"> </div>

		<script type="text/javascript">
			function procesarrep() {

				var url = "get.php?a=14&sub=1&sb=" + $("#sb").val();
				actualizarbox('reportev', url);

			}
		</script>

	<?php


	}





	if (isset($_REQUEST['sub'])) {

		if (isset($_REQUEST['sb'])) {

			$buscar = "";
			if (isset($_REQUEST['sb'])) {
				$buscar = $conn->real_escape_string($_REQUEST['sb']);
			}


			//	if ($buscar=="") { echo mensaje( "Debe ingresar el numero de serie","warning"); exit;}

			//******* SQL ************************************************************************************


			$sql = "SELECT id, fecha_alta, usuario_alta, distribuidor, distribuidor_nombre, bodega, bodega_nombre, fecha_compra, factura_numero, 
			nombres, apellidos, identidad, direccion, ciudad, departamento, telefono, celular, sexo, 
			marca, estilo, modelo, color, serie_motor, serie_chasis,anio, 
			nulo, nulo_fecha, nulo_usuario, nulo_numero, nulo_motivo, doc1, doc2, doc3, doc4, doc5, doc6
			, (DATEDIFF(CURDATE(),fecha_alta) ) as antiguedad 
			FROM garantia 
			WHERE  1=1 ";


			if ($buscar <> "") {
				$sql .=  "  and ( serie_chasis LIKE '%$buscar%' )";
			}

			if (tiene_permiso(7)) {
				$texto = armar_sql('distribuidor', $_SESSION['grupo_distribuidores'], 'or');
				if ($texto <> "") {
					$sql .= " and $texto";
				}

				$texto = armar_sql('bodega', $_SESSION['grupo_bodegas'], 'or');
				if ($texto <> "") {
					$sql .= " and $texto";
				}
			} else {
				if ($_SESSION['usuario_bodega'] <> "") {
					$sql .= " and  bodega='" . $_SESSION['usuario_bodega'] . "'";
				}
				if ($_SESSION['usuario_distribuidor'] <> "") {
					$sql .= " and distribuidor='" . $_SESSION['usuario_distribuidor'] . "'";
				}
			}


			// ****** Fin SQL ********************************************************************************

			$result = $conn->query($sql);

			if ($result->num_rows > 0) {

				$reg = 0;
				echo '<div class="row">
                    <div >
                      <table class="display nowrap" id="tabla" width="100%" cellspacing="0">
                        <thead>
                          <tr>
                           <th class="text-center"></th>
                            <th class="text-center">Marca</th>
                            <th class="text-center">Modelo</th>
                            <th class="text-center">Chasis</th>
                            <th class="text-center">Motor</th>
                            <th class="text-center">A&ntilde;o</th>
                            <th class="text-center">Color</th>
                        
                            <th class="text-center">Almacen</th>
                            <th class="text-center">Ant.</th>
                
                            <th class="text-center">Nula</th>
                            
                          </tr>
                        </thead>
                        <tbody>';
				//    <th class="text-center">CC</th>

				while ($row = $result->fetch_assoc()) {
					echo "<tr>
                              <td class='text-center'><a  href=\"#\" class=\"btn btn-default btn-sm\" onclick=\"actualizarbox('pagina','get.php?a=15&cid=" . trim($row["serie_motor"]) . "&eid=" . $row["id"] . "') ; return false;\" ><span class=\"glyphicon glyphicon-folder-open\" aria-hidden=\"true\"></span></a></td>  
                              <td class='text-center'>" . $row["marca"] . "</td>
                              <td class='text-center'>" . $row["modelo"] . "</td>
                              <td class='text-center'>" . $row["serie_chasis"] . "</td>
                              <td ><a  href=\"#\" onclick=\"actualizarbox('pagina','get.php?a=15&cid=" . trim($row["serie_motor"]) . "&eid=" . $row["id"] . "') ; return false;\" >" . $row["serie_motor"] . "</a></td>
                              <td class='text-center'>" . $row["anio"] . "</td> 
                              <td class='text-center'>" . $row["color"] . "</td>
                            
                               <td class='text-center'>" . $row["bodega_nombre"] . "</td>
                               <td class='text-center'>" . $row["antiguedad"] . "</td>
                               <td class='text-center'>" . $row["nulo"] . "</td>
                              </tr>";
					$reg++;
				}

				//   <td class='text-center'>".$row["estilo"]."</td>

				echo " </tbody>                   
                          </table>
                            </div>
                          </div> ";

				echo crear_datatable('tabla');

				// <div class=\"row col-xs-12\">Registros <span class=\"badge\">$reg</span></div>


			} else {
				echo mensaje("No se encontraron registros", "info");
				exit;
			}
		} //else { echo mensaje( "Debe ingresar informacion en el campo para buscar","warning"); exit;}

		exit;
	}
} // fin pagina 

//******************************************************************************

if ($accion == "15") { // TODO  serie  - subir doctos o anular
	if (!tiene_permiso(2)) {
		echo mensaje("No tiene privilegios para accesar esta seccion", "danger");
		exit;
	}

	if (!isset($_REQUEST['cid'])) {
		echo mensaje("Debe seleccionar un registro", "warning");
		exit;
	}
	$codigo = $conn->real_escape_string($_REQUEST['cid']);
	$eid = $conn->real_escape_string($_REQUEST['eid']);
	if ($codigo == "") {
		echo mensaje("Debe seleccionar un registro", "warning");
		exit;
	}


	//******* SQL ************************************************************************************



	$sql =  "SELECT id ,  fecha_alta ,  usuario_alta ,  distribuidor ,  distribuidor_nombre ,  bodega ,  bodega_nombre ,  
                    fecha_compra ,  factura_numero ,  nombres ,  apellidos ,  identidad ,  direccion ,  ciudad ,  departamento ,  
                    telefono ,  celular ,  sexo ,  marca ,  estilo ,  modelo ,  color ,  serie_motor ,  serie_chasis ,  anio ,  
                    nulo ,  nulo_fecha ,  nulo_usuario ,  nulo_numero ,  nulo_motivo , 
                     doc1 ,  doc2 ,  doc3 ,  doc4 ,  doc5 ,  doc6
                     , (DATEDIFF(CURDATE(),fecha_alta) ) as antiguedad
                    FROM garantia 
                    WHERE    serie_motor = '$codigo' and id=$eid                         
            ";


	// ****** Fin SQL ********************************************************************************
	// echo $sql;exit;

	$result = $conn->query($sql);

	if ($result->num_rows <= 0) {
		echo mensaje("No se encontraron registros", "warning");
		exit;
	}

	$row = $result->fetch_assoc();


	$texto_seg = "text";


	?>


	<form class="form-horizontal">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h4 class="panel-title">Datos Generales</h4>
			</div>
			<div class="panel-body">


				<div class="row">
					<div class="table-responsive">

						<table class="table table-striped">
							<thead>
								<tr>
									<th>Marca</th>
									<th>Modelo</th>
									<th>Serie Chasis</th>
									<th>Serie Motor</th>
									<th>a&ntilde;o</th>
									<th>Color</th>
									<th>CC</th>
									<th>Almacen</th>


								</tr>
							</thead>
							<tbody>



								<tr>
									<?php


									// echo campotabla("Descripcion","Descripcion",$texto_seg,$row["Descripcion"],'class="form-control" ');   
									echo campotabla_columna("Marca", "Marca", $texto_seg, $row["marca"], 'class="form-control" ');
									echo campotabla_columna("Modelo", "Modelo", $texto_seg, $row["modelo"], 'class="form-control" ');
									echo campotabla_columna("SerieChasis", "Serie Chasis", $texto_seg, $row["serie_chasis"], 'class="form-control" ');
									echo campotabla_columna("SerieMotor", "Serie Motor", $texto_seg, $row["serie_motor"], 'class="form-control" ');
									echo campotabla_columna("anio", "a&ntilde;o", $texto_seg, $row["anio"], 'class="form-control" ');
									echo campotabla_columna("Color", "Color", $texto_seg, $row["color"], 'class="form-control" ');
									echo campotabla_columna("ModeloESpecifico", "CC", $texto_seg, $row["estilo"], 'class="form-control" ');
									echo campotabla_columna("Almacen", "Almacen", $texto_seg, $row["bodega_nombre"], 'class="form-control" ');



									?>
								</tr>

							</tbody>
						</table>
					</div>
				</div>


			</div>
		</div>



		<br>
	</form>

	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title">Datos del Cliente</h4>
		</div>
		<div id="datosgenerales" class="panel-body">


			<div class="row">
				<div class="col-sm-12">

					<form class="form-horizontal ">
						<?php



						echo campo("factura_numero", "No. Factura", 'text', $row["factura_numero"], 'class="form-control" readonly ', '', '', 3, 3);
						echo campo("fecha_compra", "Fecha de Compra", 'text', fechademysql($row["fecha_compra"]), 'class="form-control" readonly ', '', '', 3, 3);

						echo campo("nombres", "Nombres", 'text', $row["nombres"], 'class="form-control" readonly ', '', '', 3, 7);
						echo campo("apellidos", "Apellidos", 'text', $row["apellidos"], 'class="form-control" readonly ', '', '', 3, 7);
						echo campo("identidad", "No. de Cedula", 'text', $row["identidad"], 'class="form-control" readonly ', '', '', 3, 4);
						echo campo("direccion", "Direccion Completa", 'text', $row["direccion"], 'class="form-control" readonly ', '', '', 3, 7);
						echo campo("ciudad", "Ciudad", 'text', $row["ciudad"], 'class="form-control" readonly ', '', '', 3, 4);
						echo campo("departamento", "Departamento", 'text', $row["departamento"], 'class="form-control" readonly ', '', '', 3, 4);
						echo campo("telefono", "Telefono Casa", 'text', $row["telefono"], 'class="form-control" readonly ', '', '', 3, 4);
						echo campo("celular", "Celular", 'text', $row["celular"], 'class="form-control" readonly ', '', '', 3, 4);
						echo campo("sexo", "Sexo", 'text', $row["sexo"], 'class="form-control" readonly ', '', '', 3, 3);


						?>
					</form>

				</div>
			</div>


		</div>
	</div>





	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title">Documentos Adjuntos</h4>
		</div>
		<div id="adjuntos" class="panel-body">
			<form class="form-horizontal ">
				<div class="row">

					<?php
					$doc1 = "upload";
					$doc2 = "upload";
					$doc3 = "upload";
					$doc4 = "upload";
					$doc5 = "upload";

					if ($row["doc1"] <> "") {
						$doc1 = "uploadlink";
					}
					if ($row["doc2"] <> "") {
						$doc2 = "uploadlink";
					}
					if ($row["doc3"] <> "") {
						$doc3 = "uploadlink";
					}
					if ($row["doc4"] <> "") {
						$doc4 = "uploadlink";
					}
					if ($row["doc5"] <> "") {
						$doc5 = "uploadlink";
					}

					//validar tiempo limite para subir documentos pendientes
					if ($row['antiguedad'] > app_dias_subir_doctos) {
						$doc1 = "uploadlink";
						$doc2 = "uploadlink";
						$doc3 = "uploadlink";
						$doc4 = "uploadlink";
						$doc5 = "uploadlink";
					}

					echo campo("doc1", "Identidad", $doc1, $row["doc1"], 'form-control ', $row["id"]);
					echo campo("doc2", "RTN", $doc2, $row["doc2"], 'class="form-control" ', $row["id"]);
					echo campo("doc3", "Notificacion de Venta", $doc3, $row["doc3"], 'class="form-control" ', $row["id"]);
					echo campo("doc4", "Comprobante de Entrega", $doc4, $row["doc4"], 'class="form-control" ', $row["id"]);
					echo campo("doc5", "Contrato", $doc5, $row["doc5"], 'class="form-control" ', $row["id"]);
					?>
				</div>

			</form>
		</div>
	</div>



	<?php

	if ($row["nulo"] == "SI") {
	?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h4 class="panel-title">Garantia Anulada</h4>
			</div>
			<div id="datosgenerales" class="panel-body">


				<div class="row">
					<div class="col-xs-12">
						<form class="form-horizontal ">
							<?php

							echo campo("nulo2", "No. Documento", 'text', $row["nulo_numero"], 'class="form-control" readonly', '', '', 3, 3);
							echo campo("nulo3", "Fecha", 'text', fechademysql($row["nulo_fecha"]), 'class="form-control" readonly', '', '', 3, 3);
							echo campo("nulo4", "Anulado por", 'text', $row["nulo_usuario"], 'class="form-control" readonly', '', '', 3, 3);
							echo campo("nulo5", "Motivo", 'text', $row["nulo_motivo"], 'class="form-control" readonly', '', '', 3, 7);

							?> </form>
					</div>
				</div>


			</div>
		</div>

	<?php
	}
	?>




	<div id="accion">



	</div>

	<div class="form-inline">
		<a id="btnimprimirgarantia" href="#" class="btn btn-info" onclick="imprimir_garantia(<?php echo $row["id"]; ?>) ; return false;"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Imprimir Garantia</a>
		&nbsp; &nbsp;
		<a id="btnimprimir" href="#" style="display: none;" class="btn btn-info" onclick="imprimir_garantia_anulada(<?php echo $row["id"]; ?>) ; return false;"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Imprimir Anulacion de Garantia</a>
		&nbsp; &nbsp;


		<?php

		if ($row["nulo"] == "SI") {
		?>
			<script>
				$('#btnimprimir').show();
			</script>

			<?php } else {
			if (tiene_permiso(8)) {  ?>
				&nbsp;&nbsp;&nbsp;&nbsp;
				<a id="btnanular" href="#" class="btn btn-danger" onclick="actualizarbox_confirmar('accion','get.php?a=16&cid=<?php echo trim($row["serie_motor"]) . "&eid=" . $row["id"]; ?>','Anular','Desea anular la garantia?','ui-icon-comment','btnanular') ;  return false;"><span class="glyphicon glyphicon-ban-circle" aria-hidden="true"></span> Anular Garant&iacute;a</a>

		<?php }
		}  ?>

	</div>

<?php
	echo boton_regresar(14);





	exit;
} // fin pagina 


//******************************************************************************


//******************************************************************************

if ($accion == "16") { // TODO   anular garantia
	if (!tiene_permiso(8)) {
		echo mensaje("No tiene privilegios para accesar esta seccion", "danger");
		exit;
	}

	if (!isset($_REQUEST['cid'], $_REQUEST['eid'])) {
		echo mensaje("Debe seleccionar un registro", "warning");
		exit;
	}
	$codigo = $conn->real_escape_string($_REQUEST['cid']);
	$eid = $conn->real_escape_string($_REQUEST['eid']);
	if ($codigo == "") {
		echo mensaje("Debe seleccionar un registro", "warning");
		exit;
	}


	//******* SQL ************************************************************************************



	$sql =  "SELECT id   
                    nulo ,  nulo_fecha ,  nulo_usuario ,  nulo_numero ,  nulo_motivo 
                    FROM garantia 
                    WHERE    serie_motor = '$codigo' and id=$eid                         
            ";


	// ****** Fin SQL ********************************************************************************
	// echo $sql;exit;

	$result = $conn->query($sql);

	if ($result->num_rows <= 0) {
		echo mensaje("No se encontraron registros", "warning");
		exit;
	}

	if (SAP_verificar_estado_moto($codigo) <> 0) {
		echo mensaje("No puede anular la garantia, porque debe estar el estado en disponible en el sistema SAP", "warning");
		exit;
	}

	$row = $result->fetch_assoc();

	if ($row['nulo'] == 'SI') {
		echo mensaje("La garantia ya se encuentra Nula", "warning");
		exit;
	}

	$result = $conn->query($sql);

	$sqlcampos = "";
	// $sqlcampos.= " , nulo_motivo =".GetSQLValue($conn->real_escape_string($_REQUEST["nulo_motivo"]),"text");


	$sqlcampos .= ",nulo_usuario= '" . $_SESSION['usuario'] . "' ,nulo_fecha=now()";
	$num_nulo = get_dato_sql('garantia', "(ifnull(max(nulo_numero),0)+1)", " where nulo='SI'");
	$sqlcampos .= ",nulo_numero= $num_nulo";


	$sql = "update garantia set nulo='SI' ,factura_numero_movesa=null " . $sqlcampos . " where  serie_motor = '$codigo' and id=$eid ";

	if ($conn->query($sql) === TRUE) {


		mensaje("La garantia fue anulada, el numero de anulacion es: <strong>$num_nulo</strong>", "success");
	} else {
		echo mensaje("Error al Anular garantia", "warning");
		exit;
	}





	exit;
} // fin pagina 


//******************************************************************************

if ($accion == "17") { // TODO   subir docto despues de creada garantia
	if (!tiene_permiso(5)) {
		echo mensaje("No tiene privilegios para accesar esta seccion", "danger");
		exit;
	}

	if (!isset($_REQUEST['cid'], $_REQUEST['eid'], $_REQUEST['dd'])) {
		echo mensaje("Debe seleccionar un registro", "warning");
		exit;
	}
	$codigo = $conn->real_escape_string($_REQUEST['cid']);
	$eid = $conn->real_escape_string($_REQUEST['eid']);
	$dd = $conn->real_escape_string($_REQUEST['dd']);
	if ($codigo == "") {
		echo mensaje("Debe seleccionar un registro", "warning");
		exit;
	}


	//******* SQL ************************************************************************************



	$sql =  "UPDATE garantia set $dd='$codigo'
                    WHERE     id=$eid                         
            ";


	// ****** Fin SQL ********************************************************************************
	// echo $sql;exit;


	if ($conn->query($sql) === TRUE) {


		echo "OK";
	} else {
		echo mensaje("Error al guardar documento", "warning");
		exit;
	}





	exit;
} // fin pagina 


//******************************************************************************

//******************************************************************************


if ($accion == "18") { // TODO  consultar garantias facturadas y no facturadas
	if (!tiene_permiso(10)) {
		echo mensaje("No tiene privilegios para accesar esta seccion", "danger");
		exit;
	}

	if (!isset($_REQUEST['b'])) {
		echo mensaje("Debe seleccionar un registro", "warning");
		exit;
	}

	$titulo = "";
	if ($_REQUEST['b'] == "p") {
		$titulo = "Garantias Pendientes Facturar";
		$sqladd = " and factura_numero_movesa is null ";
		$col1 = "Ant.";
	}

	if ($_REQUEST['b'] == "f") {
		$titulo = "Garantias Facturadas";
		$sqladd = " and factura_numero_movesa is not null";
		$col1 = "Factura";
	}

	if ($titulo == "") {
		exit;
	}

	//   SAP_actualiza_numero_factura_moto(); //##################

	//******* SQL ************************************************************************************


	$sql = "SELECT id, fecha_alta, usuario_alta, distribuidor, distribuidor_nombre, bodega, bodega_nombre, fecha_compra, factura_numero, 
            nombres, apellidos, identidad, direccion, ciudad, departamento, telefono, celular, sexo, 
            marca, estilo, modelo, color, serie_motor, serie_chasis,anio, 
            nulo, nulo_fecha, nulo_usuario, nulo_numero, nulo_motivo, doc1, doc2, doc3, doc4, doc5, doc6
            , (DATEDIFF(CURDATE(),fecha_alta) ) as antiguedad ,factura_numero_movesa
            FROM garantia 
            WHERE  1=1 ";

	$sql .= " $sqladd ";

	if (tiene_permiso(7)) {
		$texto = armar_sql('distribuidor', $_SESSION['grupo_distribuidores'], 'or');
		if ($texto <> "") {
			$sql .= " and $texto";
		}

		$texto = armar_sql('bodega', $_SESSION['grupo_bodegas'], 'or');
		if ($texto <> "") {
			$sql .= " and $texto";
		}
	} else {
		if ($_SESSION['usuario_bodega'] <> "") {
			$sql .= " and  bodega='" . $_SESSION['usuario_bodega'] . "'";
		}
		if ($_SESSION['usuario_distribuidor'] <> "") {
			$sql .= " and distribuidor='" . $_SESSION['usuario_distribuidor'] . "'";
		}
	}


	// ****** Fin SQL ********************************************************************************

	$result = $conn->query($sql);

	echo "<h3>$titulo</h3><br>";
	if ($result->num_rows > 0) {

		$reg = 0;
		echo '<div class="row">
                    <div >
                      <table class="display nowrap" id="tabla" width="100%" cellspacing="0">
                        <thead>
                          <tr>
                           <th class="text-center"></th>
                            <th class="text-center">Marca</th>
                            <th class="text-center">Modelo</th>
                            <th class="text-center">Chasis</th>
                            <th class="text-center">Motor</th>
                            <th class="text-center">A&ntilde;o</th>
                            <th class="text-center">Color</th>
                        
                            <th class="text-center">Almacen</th>
                            <th class="text-center">' . $col1 . '</th>
                
                            <th class="text-center">Nula</th>
                            
                          </tr>
                        </thead>
                        <tbody>';
		//    <th class="text-center">CC</th>

		while ($row = $result->fetch_assoc()) {
			echo "<tr>
                              <td class='text-center'><a  href=\"#\" class=\"btn btn-default btn-sm\" onclick=\"actualizarbox('pagina','get.php?a=15&cid=" . trim($row["serie_motor"]) . "&eid=" . $row["id"] . "') ; return false;\" ><span class=\"glyphicon glyphicon-folder-open\" aria-hidden=\"true\"></span></a></td>  
                              <td class='text-center'>" . $row["marca"] . "</td>
                              <td class='text-center'>" . $row["modelo"] . "</td>
                              <td class='text-center'>" . $row["serie_chasis"] . "</td>
                              <td ><a  href=\"#\" onclick=\"actualizarbox('pagina','get.php?a=15&cid=" . trim($row["serie_motor"]) . "&eid=" . $row["id"] . "') ; return false;\" >" . $row["serie_motor"] . "</a></td>
                              <td class='text-center'>" . $row["anio"] . "</td> 
                              <td class='text-center'>" . $row["color"] . "</td>
                            
                               <td class='text-center'>" . $row["bodega_nombre"] . "</td>";

			if ($_REQUEST['b'] == "p") {
				echo "<td class='text-center'>" . $row["antiguedad"] . "</td>";
			} else {
				echo "<td class='text-center'>" . $row["factura_numero_movesa"] . "</td>";
			}


			echo "  <td class='text-center'>" . $row["nulo"] . "</td>
                              </tr>";
			$reg++;
		}

		//   <td class='text-center'>".$row["estilo"]."</td>

		echo " </tbody>                   
                          </table>
                            </div>
                          </div> ";

		echo crear_datatable('tabla');
	} else {
		echo mensaje("No se encontraron registros", "info");
		exit;
	}








	exit;
} // fin pagina 

//******************************************************************************


?>