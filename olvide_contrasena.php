<?php
require_once('include/framework.php');


$mmensaje="";


if (isset($_REQUEST['ac'])){

//initialize the session
//if (!isset($_SESSION)) {  session_start();}

$lAction = $_REQUEST['ac'];
$loginFormAction = $_SERVER['PHP_SELF'];


if (isset($_POST['web_user']) ) {
 
$conn = new mysqli(db_ip, db_user, db_pw, db_name);
if (mysqli_connect_errno()) {  exit('Error al conectar a la bese de datos');}

  $loginUsername=mysqli_escape_string($conn,$_POST['web_user']);

  
  $LoginRS__query=sprintf("SELECT * FROM usuario WHERE email='%s' and activo='SI'",
    get_magic_quotes_gpc() ? $loginUsername : addslashes($loginUsername)); 

      $result = $conn->query($LoginRS__query) ; 


if ($result->num_rows > 0)  {
  $row_login  = $result->fetch_assoc();

	if(!filter_var($row_login['email'], FILTER_VALIDATE_EMAIL)) {
		$mm=1;	
	} else {
		//
		$nueva=substr(md5(uniqid(rand(), true)), 3,8) ;
		

		$sqlu="update usuario set clave='".generate_hash($nueva)."' where id=".$row_login['id'];
	
			if ($conn->query($sqlu) === TRUE) 
			{
				$mensaje='Su contraseña fue reestablecida. su nueva contraseña es: '.$nueva;
				enviar_correo($row_login['email'],'Informacion ingreso MOVESA' ,$mensaje,$mensaje );
				$mm=2;	
		
		} else {$mm=1;}
	
		
	}
		
   


    header("Location: olvide_contrasena.php?msg=$mm" );
	exit;
	
  }
  else {

   $mmensaje="La dirreccion de correo proporcionada no es valida";
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

    <title><?php echo app_title;?></title>

    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/sistema.css" rel="stylesheet">

  </head>

  <body>

    <div class="container pantallaingreso">

      <form name="form1" id="form1" method="post" class="form-signin" action="<?php echo $_SERVER['PHP_SELF']  ?>" enctype="multipart/form-data">

        <h2 class="form-signin-heading" align="center" valign="middle"><img src="images/logo.png" /></h2>
		<p>&nbsp; </p>
		<?php  if (isset($_REQUEST['msg'])<>"") {
					$mensaje_msg="";
					if ($_REQUEST['msg']=="1") {$mensaje_msg="Se produjo un error al enviar la contrase&ntilde;a";}
					if ($_REQUEST['msg']=="2") {$mensaje_msg="La contrase&ntilde;a fue enviada a su correo";}
					echo '<div class="alert alert-warning" role="alert">'.$mensaje_msg.'</div>';
					
					echo " <br><br><p><br><a href=\"index.php\"  class=\"btn btn-lg btn-default btn-block \">REGRESAR</a></p>";
				} else { ?>
				<?php  if ($mmensaje<>"") {echo '<div class="alert alert-warning" role="alert">'.$mmensaje.'</div>';} ?> 
				
		
        <label for="web_user">Ingrese su correo electronico</label>
        <input type="text" name="web_user" id="web_user" class="form-control" placeholder="Correo" required autofocus>
        <p>&nbsp; </p>
		
      
					<input type="hidden" name="ac" value="login">
      
      
        <button class="btn btn-lg btn-primary btn-block" type="submit">Reestablecer contrase&ntilde;a</button>
        
      <?php }  ?>
      </form>
      
      
    </div> <!-- /container -->

	


  </body>
</html>
