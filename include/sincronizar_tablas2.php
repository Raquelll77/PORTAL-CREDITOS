<?php
require_once ('framework.php');


$mysqli = new mysqli(db_ip, db_user, db_pw, db_name);
if (mysqli_connect_errno()) {  echo mensaje("Error al Conectar a la Base de Datos [DB:101]","danger");exit;}
$mysqli->set_charset("utf8");



$mysqli->autocommit(FALSE);

//CLIENTES
			$sql="SELECT CardCode codigo, CardName nombre FROM OCRD WHERE CardType='C'  ";//and GroupCode='100'
			$conn = sqlsrv_connect( db2_ip, array( "Database"=>db2_dbn, "UID"=>db2_usuario, "PWD"=>db2_clave) );	if( $conn === false ) { echo mensaje("Error al Conectar a la Base de Datos [DB:102]","danger");exit;}
			$conn2 = sqlsrv_connect( db2_ip, array( "Database"=>db2_dbn, "UID"=>db2_usuario, "PWD"=>db2_clave) );	if( $conn2 === false ) { echo mensaje("Error al Conectar a la Base de Datos [DB:102]","danger");exit;}
			$stmt = sqlsrv_query( $conn2, $sql );	
			if( $stmt === false) {   die( print_r( sqlsrv_errors(), true) );}
	
	        if (sqlsrv_has_rows($stmt)===true) 
	        {
	        	$mysqli->query("TRUNCATE TABLE distribuidor");
				$mysqli->query("TRUNCATE TABLE bodega");
				
	        	while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) )
				{
					$cod_cliente=$row["codigo"];	
					$mysqli->query("INSERT INTO distribuidor SET codigo='".$cod_cliente."',nombre='".trim($row["nombre"])."';");
										
					//BODEGAS
					$sql2="SELECT WhsCode codigo, WhsName nombre FROM OWHS WHERE U_CardCode='$cod_cliente'";				
					$stmt2 = sqlsrv_query( $conn2, $sql2 );	
					if( $stmt2 === false) {   die( print_r( sqlsrv_errors(), true) );}
	
			        if (sqlsrv_has_rows($stmt2)===true) 
			        {
			        	
			        	while( $row2 = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC) )
						{
				
							$mysqli->query("INSERT INTO bodega SET codigo='".trim($row2["codigo"])."',nombre='".trim($row2["nombre"])."',id_distribuidor='".$cod_cliente."' ;");	
						}
					}
					//Fin bodegas
					
				}
	        	
			}
			








/* commit transaction */
if (!$mysqli->commit()) {
    print("ERROR al sincronizar los datos");
    exit();
} else {echo "Proceso terminado";}



$mysqli->close();




?>