<?php

if (isset($_REQUEST['a'])) { $archivo = $_REQUEST['a']; } else	{exit ;}
require( 'lib/uploader/Uploader.php');



$upload_dir = 'doctos/';

$uploader = new FileUpload('uploadfile');

$uploader->allowedExtensions=array('png', 'jpg', 'gif','zip','doc','docx','xls','xlsx','pdf');
$uploader->sizeLimit=5242880; //5mb
$uploader->newFileName=$archivo.'.'.$uploader->getExtension();

$result = $uploader->handleUpload($upload_dir);

if (!$result) {
  exit(json_encode(array('success' => false, 'msg' => $uploader->getErrorMsg())));  
}

echo json_encode(array('success' => true, 'msg' => $uploader->getSavedFile()));
