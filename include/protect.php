<?php
// ##############################################################################
// #                                                                            #
// # Modulo Protect                                                           #
// # 2015 Derechos reservados INFORMATICA Y SISTEMAS.                           #
// # Web: http://infosistemas.hn3.net  Email: infosistemas@hn3.net              #
// # Se prohibe la copia o distribucion del codigo sin autorizacion por escrito #
// ##############################################################################

require_once ('sesion.php');

//Variables
$SSL_authFailedURL = "index.php?msg=expirada";

// verificar cookies si existen
if (!verificar_cookie()) {

	header("Location: $SSL_authFailedURL");
	exit ;
}

//initialize the session
if (!isset($_SESSION)) {  session_start();
}

if (!isset($_SESSION['usuario'])) {

	header("Location: $SSL_authFailedURL");
	exit ;
}

renovar_session();
renovar_cookie();
?>