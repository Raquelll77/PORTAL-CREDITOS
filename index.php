<?php
require_once('include/framework.php');

$mmensaje = "";

$redireccionar_a_credito = isset($_REQUEST["redirect_to"]) && !empty($_REQUEST["redirect_to"]);
$credito_id = !isset($_REQUEST["redirect_to"]) || empty($_REQUEST["redirect_to"]) ? "" : $_REQUEST["redirect_to"];
$link_redirect_to = $redireccionar_a_credito ? "redirect_to={$credito_id}" : "";

if (isset($_REQUEST['ac'])) {

	//initialize the session
	//if (!isset($_SESSION)) {  session_start();}

	$lAction = $_REQUEST['ac'];
	$loginFormAction = $_SERVER['PHP_SELF'];

	if ($lAction == "logout") {
		cerrar_session();
		header("Location: $loginFormAction?msg=logout&" . $link_redirect_to);
		exit;
	}


	if (isset($_POST['web_user'], $_POST['web_password'])) {
		//echo var_dump(db_ip, db_user, db_pw, db_name);
		$conn = new mysqli(db_ip, db_user, db_pw, db_name);
		if (mysqli_connect_errno()) {
			exit('Error al conectar a la bese de datos');
		}


		$loginUsername = mysqli_escape_string($conn, $_POST['web_user']);
		$password = mysqli_escape_string($conn, $_POST['web_password']);
		$MM_fldUserAuthorization = "";
		$MM_redirectLoginSuccess = "inicio.php?" . $link_redirect_to;
		$MM_redirectLoginFailed = $loginFormAction . "?m=1&" . $link_redirect_to;
		$MM_redirecttoReferrer = true;


		$LoginRS__query = sprintf(
			"SELECT * FROM usuario WHERE usuario='%s' and activo='SI'",
			get_magic_quotes_gpc() ? $loginUsername : addslashes($loginUsername)
		);

		$result = $conn->query($LoginRS__query);


		if ($result->num_rows > 0) {
			$row_login  = $result->fetch_assoc();

			//if ($row_login['acceso_intentos']>=3) {
			//$mmensaje="Su cuenta se encuentra temporalmente deshabilitada, por favor contacte a su proveedor para que active la cuenta";
			//} else {

			//}
			if (!verificar_password($password, $row_login['clave'])) {
				$mmensaje = "El usuario y/o contrase&ntilde;a son incorrectos";
				ejecutar_sql('update usuario set acceso_intentos=acceso_intentos+1 where id=' . $row_login['id']);
			} else {

				// registro de acceso
				ejecutar_sql('update usuario set acceso_ultimo=now(), acceso_intentos=0 where id=' . $row_login['id']);

				//######## definir Variables de sesion ##########
				$distribuidor_nombre = get_dato_sql('distribuidor', 'nombre', " where codigo='" . $row_login['distribuidor'] . "'");
				$bodega_nombre = get_dato_sql('bodega', 'nombre', " where codigo='" . $row_login['bodega'] . "'");
				// $usuario, $nombre, $id, $grupo, $distribuidor, $bodega, $distribuidor_nombre, $bodega_nombre, $empresa


				nueva_session(
					ucfirst(strtolower($row_login['usuario'])), // usuario
					ucfirst(strtolower($row_login['nombre'])), // nombre
					$row_login['id'], // id
					$row_login['grupo_id'], // grupo
					$row_login['distribuidor'], // distribuidor
					$row_login['bodega'], // bodega
					$distribuidor_nombre, // distribuidor_nombre
					$bodega_nombre, // bodega_nombre
					$row_login['empresa_id'] // empresa
				);
				colocar_cookie();

				if (tiene_permiso(7)) {
					cargar_bodegas_dist_grupo($row_login['grupo_id']);
				}

				header("Location: " . $MM_redirectLoginSuccess);
				//echo var_dump($MM_redirectLoginSuccess);
				exit;
			}
		} else {

			$mmensaje = "El usuario y/o contrase&ntilde;a son incorrectos";
		}
	}
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="Infosistemas">
	<link rel="icon" href="favicon.ico">

	<title><?php echo app_title; ?></title>

	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/sistema.css" rel="stylesheet">

</head>

<body>

	<div class="container pantallaingreso">

		<form name="form1" id="form1" method="post" class="form-signin" action="<?php echo $_SERVER['PHP_SELF'] . "?{$link_redirect_to}"  ?>" enctype="multipart/form-data">

			<h2 class="form-signin-heading" align="center" valign="middle"><img src="images/logo.png" /></h2>
			<p>&nbsp; </p>
			<?php if (isset($_REQUEST['msg']) <> "") {
				$mensaje_msg = "";
				if ($_REQUEST['msg'] == "logout") {
					$mensaje_msg = "La sesi&oacute;n ha sido finalizada";
				}
				if ($_REQUEST['msg'] == "expirada") {
					$mensaje_msg = "La sesi&oacute;n fue finalizada debido a que expir&oacute;";
				}
				echo '<div class="alert alert-warning" role="alert">' . $mensaje_msg . '</div>';
			} ?>
			<?php if ($mmensaje <> "") {
				echo '<div class="alert alert-warning" role="alert">' . $mmensaje . '</div>';
			} ?>

			<input type="hidden" value="<?php echo $credito_id; ?>" name="redirect_to">

			<label for="web_user" class="sr-only">Usuario</label>
			<input type="text" name="web_user" id="web_user" class="form-control" placeholder="Usuario" required autofocus>
			<p>&nbsp; </p>
			<label for="web_password" class="sr-only">Contrase&ntilde;a</label>
			<input type="password" name="web_password" id="web_password" class="form-control" placeholder="Contrase&ntilde;a" required>
			<p>&nbsp; </p>

			<input type="hidden" name="ac" value="login">


			<button class="btn btn-lg btn-primary btn-block" type="submit">Ingresar</button>
		</form>

		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<span class="pull-right"> <a href="olvide_contrasena.php">Olvid&eacute; mi contrase&ntilde;a</a></span>

	</div> <!-- /container -->




</body>

</html>