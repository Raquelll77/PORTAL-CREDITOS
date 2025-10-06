<?php
// ##############################################################################
// #  						                                                    #
// # Modulo sesion                                                              #
// # 2015 Derechos reservados INFORMATICA Y SISTEMAS.                           #
// # Web: http://infosistemas.hn3.net  Email: infosistemas@hn3.net              #
// # Se prohibe la copia o distribucion del codigo sin autorizacion por escrito #
// ##############################################################################

function renovar_session()
{

	$_SESSION['hora_ultima_tran'] = time();

}

function colocar_cookie()
{
	$randomid = generate_id();
	$cookieid = trim(sha1($randomid . $_SERVER['HTTP_USER_AGENT'] . obtener_ip2()));
	setcookie("urs", $cookieid);
	setcookie("sgt", $randomid);
	return $cookieid;
}

function renovar_cookie()
{
	$randomid = generate_id();
	$cookieid = trim(sha1($randomid . $_SERVER['HTTP_USER_AGENT'] . obtener_ip2()));
	setcookie("urs", $cookieid);
	setcookie("sgt", $randomid);
	return $cookieid;
}

function colocar_cookie_usuario($usr)
{
	setcookie("usr", $usr);
}

function verificar_cookie()
{
	if (!isset($_COOKIE['sgt'], $_COOKIE['urs'])) {
		return FALSE;
		exit;
	}
	$randomid = "";
	$randomid = $_COOKIE['sgt'];
	$cookieid = trim(sha1($randomid . $_SERVER['HTTP_USER_AGENT'] . obtener_ip2()));
	if (trim($cookieid) === trim($_COOKIE['urs'])) {
		return TRUE;
	} else {
		return FALSE;
	}
}

// function obtener_ip2() {


// 	$pieces = explode(".", $_SERVER['REMOTE_ADDR']);
// 	//print_r($pieces);

// 	return $pieces[0] . "." . $pieces[1];
// }

function obtener_ip2()
{
	$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

	// Si la IP es IPv6 localhost (::1), devuélvela en formato simplificado
	if ($ip === '::1') {
		return '127.0.0';
	}

	$pieces = explode(".", $ip);

	// Validar que tenga al menos 2 segmentos
	if (count($pieces) >= 2) {
		return $pieces[0] . "." . $pieces[1];
	} else {
		// Retornar IP completa si no cumple formato esperado
		return $ip;
	}
}


function generate_id()// de 40 caracteres de ancho
{
	return sha1(rand(10000, 30000) . time() . rand(10000, 30000));
}

/*
 * PRUEBA
 */
function nueva_session($usuario, $nombre, $id, $grupo, $distribuidor, $bodega, $distribuidor_nombre, $bodega_nombre, $empresa)
{
	session_start();
	$_SESSION['usuario'] = $usuario;
	$_SESSION['usuario_nombre'] = $nombre;
	$_SESSION['usuario_id'] = $id;
	$_SESSION['usuario_distribuidor'] = $distribuidor;
	$_SESSION['usuario_bodega'] = $bodega;
	$_SESSION['usuario_distribuidor_nombre'] = $distribuidor_nombre;
	$_SESSION['usuario_bodega_nombre'] = $bodega_nombre;
	$_SESSION['hora_inicio'] = time();
	$_SESSION['hora_ultima_tran'] = time();
	$_SESSION['seg'] = leer_permisos_asignados($grupo);
	$_SESSION['grupo_bodegas'] = array();
	$_SESSION['grupo_distribuidores'] = array();
	$_SESSION['empresa'] = $empresa;


}

function cerrar_session()
{
	// borrar session
	session_start();
	session_destroy();

	// borrar cookies
	setcookie("urs", "", time() - 3600, "/", "", 0);
	setcookie("sgt", "", time() - 3600, "/", "", 0);
	setcookie("usr", "", time() - 3600, "/", "", 0);
	setcookie("PHPSESSID", "", time() - 3600, "/", "", 0);

}
?>