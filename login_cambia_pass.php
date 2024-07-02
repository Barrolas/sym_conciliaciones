<?php
include("funciones.php");
error_reporting(0);
$url=$_SERVER['HTTP_REFERER'];

// echo $url;exit;

if(strpos($sentence, "localhost") != false) {
   header("Location: login.php");
	exit;
};

if (strpos($sentence, "ogmios") != false) {
	header("Location: login.php");
	exit;
};


$op = ($_GET["op"]=='') ? '' : $_GET["op"];


?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8" />
        <title>Gestión en Terreno</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta content="Login" name="description" />
        <meta content="Acreditamos.cl" name="author" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon.ico">

        <!-- App css -->
        <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />
		<script src="assets/js/sweetalert2/sweetalert2.all.min.js"></script>		

    </head>

    <body class="account-body accountbg">

        <!-- Log In page -->
        <div class="container">
            <div class="row vh-100 d-flex justify-content-center">
                <div class="col-12 align-self-center">
                    <div class="row">
                        <div class="col-lg-5 mx-auto">
                            <div class="card">
                                <div class="card-body p-0 auth-header-box">
                                    <div class="text-center p-3">
                                        <H1 style="color: white">GESTION TERRENO</H1>
										<BR>
											 <img src="assets/images/logo2.jpg" alt="logo-small" >
										<BR>	
                                        <h4 class="mt-3 mb-1 font-weight-semibold text-white font-18">CAMBIO DE CONTRASEÑA</h4>   

                                    </div>
                                </div>
                                <div class="card-body p-0">
                                     <!-- Tab panes -->
                                    <div class="tab-content">
                                        <div class="tab-pane active p-3" id="LogIn_Tab" role="tabpanel">                                        
										<form class="form-horizontal auth-form" action="login_actualiza.php" method="post" accept-charset="UTF-8" role="form" name="formulario" enctype="multipart/form-data" autocomplete="off" onsubmit="return valida_envia();return false;" >												
                                                <div class="form-group mb-2">
                                                    <label for="pass">CONTRASEÑA</label>
                                                    <div class="input-group">                                                                                         
                                                        <input type="password" class="form-control" name="pass" id="pass" placeholder="Ingrese Contraseña" autocomplete="off">
														<input name = "DummyUsername" type="text" style="display:none;">
														
                                                    </div>                                    
                                                </div><!--end form-group--> 
                    
                                                <div class="form-group mb-2">
                                                    <label for="pass1">CONFIRMA CONTRASEÑA</label>                                            
                                                    <div class="input-group">  
														<input type="password" class="form-control" name="pass1" id="pass1" placeholder="Confirma Contraseña" autocomplete="off">
														<input name = "DummyPassword" type="password" style="display:none;">                                                    
													</div>                               
                                                </div><!--end form-group--> 
                    
                                                <div class="form-group row my-3">
                                                    <div class="col-sm-6">
                                                       
                                                    </div><!--end col--> 
                                                    
                                                </div><!--end form-group--> 
                    
                                                <div class="form-group mb-0 row">
                                                    <div class="col-12">
                                                        <button class="btn btn-primary btn-block waves-effect waves-light" type="submit">ACTUALIZAR <i class="fas fa-sign-in-alt ml-1"></i></button>
                                                    </div><!--end col--> 
                                                </div> <!--end form-group-->                           
                                            </form><!--end form-->
                                        </div>                                        
                                    </div>
                                </div><!--end card-body-->
                                
                            </div><!--end card-->
                        </div><!--end col-->
                    </div><!--end row-->
                </div><!--end col-->
            </div><!--end row-->
        </div><!--end container-->
        <!-- End Log In page -->

        


        <!-- jQuery  -->
        <script src="assets/js/jquery.min.js"></script>
        <script src="assets/js/bootstrap.bundle.min.js"></script>
        <script src="assets/js/waves.js"></script>
        <script src="assets/js/feather.min.js"></script>
        <script src="assets/js/simplebar.min.js"></script>
<script>
$(document).ready(function() {
    var some_id = $('#usuario');
    some_id.prop('type', 'text');
    some_id.removeAttr('autocomplete');
});		
</script>
<script language="javascript">
	var letras="abcdefghyjklmnñopqrstuvwxyz";

	function tiene_letras(texto){
	   texto = texto.toLowerCase();
	   for(i=0; i<texto.length; i++){
		  if (letras.indexOf(texto.charAt(i),0)!=-1){
			 return 1;
		  }
	   }
	   return 0;
	} 
	var numeros="0123456789";

	function tiene_numeros(texto){
	   for(i=0; i<texto.length; i++){
		  if (numeros.indexOf(texto.charAt(i),0)!=-1){
			 return 1;
		  }
	   }
	   return 0;
	} 
	var letras_mayusculas="ABCDEFGHYJKLMNÑOPQRSTUVWXYZ";

	function tiene_mayusculas(texto){
	   for(i=0; i<texto.length; i++){
		  if (letras_mayusculas.indexOf(texto.charAt(i),0)!=-1){
			 return 1;
		  }
	   }
	   return 0;
	} 
	
	function validar_email( email ) 
	{
		var regex = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
		return regex.test(email) ? true : false;
	}			

	function valida_envia(){
		//valido el nombre
		if (document.formulario.pass.value.length <= 5){
				Swal.fire({
				  width: 600,
				  icon: 'error',
				  title: 'Debe ingresar una Contraseña mayor a 5 caracteres.',
				  showConfirmButton: false,
				  timer: 2000,
				})
				
				return false;
		}
		if (document.formulario.pass1.value.length <= 5){
				Swal.fire({
				  width: 600,
				  icon: 'error',
				  title: 'Debe confirmar una Contraseña mayor a 5 caracteres',
				  showConfirmButton: false,
				  timer: 2000,
				})
				
				return false;
		}
		
		if (document.formulario.pass.value != document.formulario.pass1.value){
				Swal.fire({
				  width: 600,
				  icon: 'error',
				  title: 'Contraseñas son Distintas.',
				  showConfirmButton: false,
				  timer: 2000,
				})
				
				return false;
		};
		
		var espacios = false;
		var cont = 0;

		while (!espacios && (cont < document.formulario.pass.value.length)) {
		  if (document.formulario.pass.value.charAt(cont) == " ")
			espacios = true;
		  cont++;
		}

		if (espacios) {
		  Swal.fire({
				  width: 600,
				  icon: 'error',
				  title: 'Contraseñas son puede tener espacios.',
				  showConfirmButton: false,
				  timer: 2000,
				})
				
				return false;
		}

		if (tiene_numeros(document.formulario.pass.value) == 0) {
			Swal.fire({
				  width: 600,
				  icon: 'error',
				  title: 'Contraseñas debe terner al menos un Número.',
				  showConfirmButton: false,
				  timer: 2000,
				})
			return false;
		};
		
		if (tiene_letras(document.formulario.pass.value) == 0) {
			Swal.fire({
				  width: 600,
				  icon: 'error',
				  title: 'Contraseñas debe terner al menos una Letra.',
				  showConfirmButton: false,
				  timer: 2000,
				})
			return false;
		};
		
		if (tiene_mayusculas(document.formulario.pass.value) == 0) {
			Swal.fire({
				  width: 600,
				  icon: 'error',
				  title: 'Contraseñas debe terner al menos una Mayuscula.',
				  showConfirmButton: false,
				  timer: 2000,
				})
			return false;
		};
		
		document.formulario.submit();
	};
	
 
</script> 
				<div class="content">
					<script>
						Swal.fire({
						  width: 600,
						  icon: 'success',
						  title: 'DEBE CAMBIAR SU CONTRASEÑA',
						  showConfirmButton: false,
						  timer: 2000,
						})
					</script>				
				</div>			
			<?php if ($op == 1) {?>
				<div class="content">
					<script>
						Swal.fire({
						  width: 600,
						  icon: 'error',
						  title: 'USUARIO NO EXISTE',
						  showConfirmButton: false,
						  timer: 2000,
						})
					</script>				
				</div>
			<?php };
			   if ($op == 2) {?>
				<div class="content">
					<script>
						Swal.fire({
						  width: 600,
						  icon: 'error',
						  title: 'USUARIO O CONTRASEÑA ERRONEA',
						  showConfirmButton: false,
						  timer: 2000,
						})
					</script>				
				</div>
			<?php };
			   if ($op == 3) {?>
				<div class="content">
					<script>
						Swal.fire({
						  width: 600,
						  icon: 'success',
						  title: 'CORREO DE RECUPERACION ENVIADO',
						  showConfirmButton: false,
						  timer: 2000,
						})
					</script>				
				</div>
			<?php };?>	
			<?php 
			   if ($op == 4) {?>
				<div class="content">
					<script>
						Swal.fire({
						  width: 600,
						  icon: 'success',
						  title: 'CUENTA DESHABILITADA',
						  showConfirmButton: false,
						  timer: 2000,
						})
					</script>				
				</div>
			<?php };?>
			<?php 
			   if ($op == 5) {?>
				<div class="content">
					<script>
						Swal.fire({
						  width: 600,
						  icon: 'success',
						  title: 'RECUPERACION NO VALIDAD',
						  showConfirmButton: false,
						  timer: 2000,
						})
					</script>				
				</div>
			<?php };?>
<script language="javascript">
	document.getElementById("pass").focus();
</script>				
    </body>

</html>