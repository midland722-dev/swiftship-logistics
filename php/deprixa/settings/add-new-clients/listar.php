<?php
// *************************************************************************
// *                                                                       *
// * DEPRIXA -  logistics Worldwide Software                               *
// * Copyright (c) JAOMWEB. All Rights Reserved                            *
// *                                                                       *
// *************************************************************************
// *                                                                       *
// * Email: osorio2380@yahoo.es                                            *
// * Website: http://www.jaom.info                                         *
// *                                                                       *
// *************************************************************************
// *                                                                       *
// * This software is furnished under a license and may be used and copied *
// * only  in  accordance  with  the  terms  of such  license and with the *
// * inclusion of the above copyright notice.                              *
// * If you Purchased from Codecanyon, Please read the full License from   *
// * here- http://codecanyon.net/licenses/standard                         *
// *                                                                       *
// *************************************************************************
 

include('../../database-settings.php');
  // asignamos la función de conexion a una variable
  $con = conexion();
  // realizamos la consulta SQL para recuperar todos los registros de la tabla
  $resultado = $con->query("SELECT * FROM tbl_clients");
  // creamo una variable del tipo array la cual almacena todos los registros
  $datos = array();
  // iteramos todos los registros devueltos y llenamos el array
  while ($row = $resultado->fetch_assoc()){

   $datos[] = $row;
   
  }

  // convertimos el array al formato json y mostramos para que el Plugin Data Tables pueda formatera la información
  echo json_encode($datos);

?>