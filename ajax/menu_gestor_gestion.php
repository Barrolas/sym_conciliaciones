<?php
session_start();
include("funciones.php");
include("conexiones.php");

noCache();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$id = $_GET["id"];
$nomina	= (!isset($_GET["nomina"])) ? '0' : $_GET["nomina"];

$sql       = "select G.*,CS.NOMBRE AS SUBCLIENTE,CS.PDF
				from los_usuarios_gestiones G 
				join los_clientes_servicio CS ON CS.RUT = G.RUTCLIENTE
				where G.RUT = '$id' and G.ESTADO=1"; 
$resultado = $conn->query($sql);
$gestion   = $resultado->fetch();

if ($gestion["ID_GESTION"] != 0) {
	header("Location: menu_gestor.php?op=5");
	exit;
};

$sql       = "SELECT COUNT(DISTINCT RUT) AS TOTAL 
				FROM `los_usuarios_gestiones` 
				WHERE `USUARIO_ASIGNADO` = '".$_SESSION["GESTOR"]."' AND ESTADO=1;"; 
$resultado = $conn->query($sql);
$pendientes   = $resultado->fetch();

$sql       = "SELECT COUNT(DISTINCT RUT) AS TOTAL 
				FROM `los_usuarios_gestiones` 
				WHERE `USUARIO_ASIGNADO` = '".$_SESSION["GESTOR"]."' AND ESTADO=2;"; 
$resultado = $conn->query($sql);
$realizadas   = $resultado->fetch();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
	
		<meta charset="utf-8" />
        <title><?= $sistema?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta content="CRM" name="description" />
        <meta content="" name="author" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon.ico">	
	
		<!-- DataTables -->
		<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
		<link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
		<link href="https://cdn.datatables.net/rowreorder/1.4.1/css/rowReorder.dataTables.min.css" rel="stylesheet" type="text/css">	
		<link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css" rel="stylesheet" type="text/css">
		<link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/metisMenu.min.css" rel="stylesheet" type="text/css" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
		<script src="https://unpkg.com/feather-icons"></script>
        <script src="assets/js/sweetalert2/sweetalert2.all.min.js"></script>
	
		

		<script language="javascript">
			
			navigator.geolocation.getCurrentPosition(position => {
				console.log(position);
			}, e => {
				console.log(e);
				// alert('Active la GEOPOSICION PARA ESTE SITIO')
			});
			-->
			if ("geolocation" in navigator){ //check Geolocation available 
					//try to get user current location using getCurrentPosition() method
					navigator.geolocation.getCurrentPosition(function(position){ 
					document.getElementById("lat").value = position.coords.latitude;
					document.getElementById("lon").value = position.coords.longitude;
					//window.location.href = "ventas_crear.php?id=<?= $id;?>&lat=" + position.coords.latitude+"&lon=" + position.coords.longitude;
					// window.location.href = "ventas_crear.php?id=<?= $id;?>&lat=1&lon=1" ;
					
				});
			} 

		</script>		
</head>

<body>
<div class="container-fluid">
  <!-- Content here -->
	<div class="row bg-primary ">
		<div class="col-6">
				<h6><span class="text-light bg-primary"><strong><?php echo $_SESSION["NOMBRES"];?> (<?php echo $_SESSION["PERFIL"];?>)</strong></span></h6>                
		</div>	
		<div class="col-6 text-end">
				<h6><span class="text-light bg-primary"><strong>GESTIONES PENDIENTES : (<?= $pendientes["TOTAL"]?>) - GESTIONES REALIZADAS (<?= $realizadas["TOTAL"]?>)</strong></span></h6>                
		</div>
	</div>
	<BR>
	<div class="row">
		<div class="col-lg-9 offset-md-2">
			<?php if ($gestion["PDF"] <> '') { ?>
			<a aling="rigth" target="new"  href="pdf/tutorial/<?= $gestion["PDF"]?>?id=<?= $id;?>&id1=<?= $gestion["RUTCLIENTE"];?>"><button type="button" class="btn btn-md btn-danger"><i class="fa fa-plus"></i> IMPRIMIR NOTIFICACION</button></a>
			<BR><BR>
			<?php };?>	
			<div class="card">

				<div class="card-body"> 
					
						<div class="row">
							<div class="col-md-12">
								<div class="form-group row">
									<label for="gestion" class="col-lg-2 col-form-label">SUB-CLIENTE</label>
									<div class="col-lg-6 col-sm-6">
										<input type="text" name="gestion" id="gestion" class="form-control" maxlength="50" value="<?= $gestion["SUBCLIENTE"]?>" autocomplete="off" disabled/> 
									</div>
								</div><!--end form-group-->
							</div><!--end col-->

						</div><!--end row-->	
					
						<div class="row">
							<div class="col-md-12">
								<div class="form-group row">
									<label for="gestion" class="col-lg-2 col-form-label">CAMPAÑA</label>
									<div class="col-lg-6 col-sm-6">
										<input type="text" name="gestion" id="gestion" class="form-control" maxlength="50" value="<?= $gestion["CAMPANA"]?>" autocomplete="off" disabled/> 
									</div>
								</div><!--end form-group-->
							</div><!--end col-->

						</div><!--end row-->	
					
						<div class="row">
							<div class="col-md-12">
								<div class="form-group row">
									<label for="gestion" class="col-lg-2 col-form-label">RUT</label>
									<div class="col-lg-2 col-sm-6">
										<input type="text" name="gestion" id="gestion" class="form-control" maxlength="50" value="<?= trim($gestion["RUT"].'-'.$gestion["DV"]);?>" autocomplete="off" disabled/> 
									</div>
								</div><!--end form-group-->
							</div><!--end col-->

						</div><!--end row-->										

						<div class="row">
							<div class="col-md-12">
								<div class="form-group row">
									<label for="nombre" class="col-lg-2 col-form-label">NOMBRE</label>
									<div class="col-lg-6 col-sm-6 col-md-6">
										<input type="text" name="apellido" id="apellido" class="form-control" maxlength="70"  value="<?= trim($gestion["NOMBRE"])?>" autocomplete="off" disabled/>  
									</div>
								</div><!--end form-group-->
							</div><!--end col-->																					
						</div><!--end row-->

						<div class="row">
							<div class="col-md-12">
								<div class="form-group row">
									<label for="nombre" class="col-lg-2 col-form-label">TOTAL DEUDA</label>
									<div class="col-lg-6 col-sm-6 col-md-6">
										<input type="text" name="morosidad" id="morosidad" class="form-control" maxlength="70"  value="<?= '$'.my_number_format($gestion["CONTENCION"],'','.')?>" autocomplete="off" disabled/>  
									</div>
								</div><!--end form-group-->
							</div><!--end col-->																					
						</div><!--end row-->
						<div class="row">
							<div class="col-md-12">
								<div class="form-group row">
									<label for="cuota" class="col-lg-2 col-form-label">VALOR CUOTA</label>
									<div class="col-lg-6 col-sm-6 col-md-6">
										<input type="text" name="cuota" id="cuota" class="form-control" maxlength="70"  value="<?= '$'.my_number_format($gestion["CUOTA"],'','.')?>" autocomplete="off" disabled/>  
									</div>
								</div><!--end form-group-->
							</div><!--end col-->																					
						</div><!--end row-->
				<BR>
				<table id="example" class="display nowrap" style="width:100%">
					<thead>
						<tr>
							
							<th>VENCIMIENTO</th>
							<th>MONTO</th>
						</tr>
					</thead>
					<tbody>
						<?php 
						$sql ="SELECT date_format(F_VCTO_MENOR, '%d-%m-%Y') AS F_VCTO_MENOR, CUOTA 
								FROM `los_usuarios_gestiones`
								WHERE RUT='$id' and ESTADO=1;";
						// echo $sql ;	
						$resultado = $conn->query($sql);
						while($gestion   = $resultado->fetch()) {
						?>
						<tr>
							
							<td><?php echo $gestion["F_VCTO_MENOR"]?></td>
							<td><?php echo '$'.my_number_format($gestion["CUOTA"],'','.')?></td>
						</tr>
						<?php };?>
					</tbody>
				</table>			
				<HR>
				<form method="post" action="menu_gestor_guardar.php?id=<?= $id?>&nomina=<?= $nomina?>" class="form-horizontal " id="validate" role="form" id="formulario" name="formulario" class="needs-validation" autocomplete="on" onsubmit="return valida_envia();return false;">	
						<div class="row">
							<div class="col-md-6">
								<div class="form-group row">
									<label for="categoria" class="col-lg-5 col-form-label">CATEGORIA</label>
									<div class="col-lg-7">
										
										<select name="categoria" id="categoria"  class="form-select form-select-sm" aria-label=".form-select-lg example" onChange="javascript:muestra()">	
											<option value="0">Seleccione una Categoría</option>
										<?php 
											$sql ="select *
													from los_categoria
													where ESTADO=1
													order by DESCRIPCION";
											$resultado = $conn->query($sql);
											while($gestion   = $resultado->fetch()) {
										?>
											<option value="<?php echo $gestion["ID_CATEGORIA"]?>"><?php echo $gestion["DESCRIPCION"]?></option>
										<?php };?>	
										</select> 
									</div>
								</div><!--end form-group-->
							</div><!--end col-->

						</div><!--end row-->	
					
						<div class="row">
							<div class="col-md-6">
								<div class="form-group row">
									<label for="subcategoria" class="col-lg-5 col-form-label">SUB-CATEGORIA</label>
									<div class="col-lg-7">
										<select name="subcategoria" id="subcategoria"  class="form-select form-select-sm" aria-label=".form-select-lg example" onChange="javascript:muestra()">
															
										</select>
									</div>
								</div><!--end form-group-->
							</div><!--end col-->

						</div><!--end row-->

						<div id="idfecha" class="row">
							<div class="col-md-6">
								<div class="form-group row">
									<label for="nombre" class="col-lg-5 col-form-label">FECHA COMPROMISO</label>
									<div class="col-lg-6 col-sm-6 col-md-6">
										<input type="date" name="fecha" id="fecha" class="form-control" maxlength="70"   autocomplete="off" />  
									</div>
								</div><!--end form-group-->
							</div><!--end col-->																					
						</div><!--end row-->

						<div id="idmonto" class="row">
							<div class="col-md-6">
								<div class="form-group row">
									<label for="monto" class="col-lg-5 col-form-label">MONTO</label>
									<div class="col-lg-6 col-sm-6 col-md-6">
										<input type="text" name="monto" id="monto" class="form-control" maxlength="70"   autocomplete="off" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;"/>  
									</div>
								</div><!--end form-group-->
							</div><!--end col-->																					
						</div><!--end row-->
					
						<div id="idtelefono" class="row">
							<div class="col-md-6">
								<div class="form-group row">
									<label for="telefono" class="col-lg-5 col-form-label">TELEFONO</label>
									<div class="col-lg-6 col-sm-6 col-md-6">
										<input type="text" name="telefono" id="telefono" class="form-control" maxlength="70"   autocomplete="off" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;"/>  
									</div>
								</div><!--end form-group-->
							</div><!--end col-->																					
						</div><!--end row-->
						
						<div id="iddireccion" class="row">
							<div class="col-md-6">
								<div class="form-group row">
									<label for="telefono" class="col-lg-5 col-form-label">NUEVA DIRECCION</label>
									<div class="col-lg-6 col-sm-6 col-md-6">
										<input type="text" name="ndireccion" id="ndireccion" class="form-control" maxlength="255"   autocomplete="off" />  
									</div>
								</div><!--end form-group-->
							</div><!--end col-->																					
						</div><!--end row-->
						
						<div id="idcomuna" class="row">
							<div class="col-md-6">
								<div class="form-group row">
									<label for="telefono" class="col-lg-5 col-form-label">NUEVA COMUNA</label>
									<div class="col-lg-6 col-sm-6 col-md-6">
										<!--<input type="text" name="ncomuna" id="ncomuna" class="form-control" maxlength="100"   autocomplete="off" /> 
										  -->
										 <select name="ncomuna" id="ncomuna" class="js-example-basic-single form-control mb-3 " style="width: 100%; height:36px;">	  
											
											<option value="0">Seleccione una Comuna</option>
										<?php 
											$sql ="select *
													from los_comunas
													order by NOMBRE";
											$resultado = $conn->query($sql);
											while($comuna   = $resultado->fetch()) {
										?>
											<option value="<?php echo $comuna["IDINTERNO"]?>"><?php echo $comuna["NOMBRE"]?></option>
										<?php };?>	
										
										</select> 
										
									</div>
								</div><!--end form-group-->
							</div><!--end col-->																					
						</div><!--end row-->
					
						
					
						<div id="idtipodireccion" class="row">
							<div class="col-md-6">
								<div class="form-group row">
									<label for="telefono" class="col-lg-5 col-form-label">TIPO DIRECCION</label>
									<div class="col-lg-6 col-sm-6 col-md-6">
										<input class="form-check-input" type="radio" name="tipodireccion" id="tipodireccion" value="1">
										  <label class="form-check-label" for="tipodireccion">
											Comercial
										  </label>
										<input checked class="form-check-input" type="radio" name="tipodireccion" id="tipodireccion" value="2">
										  <label class="form-check-label" for="tipodireccion">
											Particular
										  </label>
										
									</div>
								</div><!--end form-group-->
							</div><!--end col-->																					
						</div><!--end row-->
						
						<div id="idnuevagestion" class="row">
							<div class="col-md-6">
								<div class="form-group row">
									<label for="telefono" class="col-lg-5 col-form-label"></label>
									<div class="col-lg-6 col-sm-6 col-md-6">
										<input class="form-check-input" type="radio" name="nuevagestion" id="nuevagestion" value="1" checked>
										  <label class="form-check-label" for="nuevagestion">
											GUARDAR
										  </label>
										<input class="form-check-input" type="radio" name="nuevagestion" id="nuevagestion" value="2">
										  <label class="form-check-label" for="nuevagestion">
											GESTIONAR
										  </label>
										
									</div>
								</div><!--end form-group-->
							</div><!--end col-->																					
						</div><!--end row-->
					
						<div id="telefono2" class="row">
							<div class="col-md-6">
								<div class="form-group row">
									<label for="telefono" class="col-lg-5 col-form-label">NUEVO TELEFONO</label>
									<div class="col-lg-6 col-sm-6 col-md-6">
										<input type="number" name="ntelefono" id="ntelefono" class="form-control" autocomplete="off" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;" maxlength="9" oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"/>  
									</div>
								</div><!--end form-group-->
							</div><!--end col-->																					
						</div><!--end row-->
					
						<div id="idnota" class="row">
							<div class="col-md-6">
								<div class="form-group row">
									<label for="nota" class="col-lg-5 col-form-label">NOTAS</label>
									<div class="col-lg-6 col-sm-6 col-md-6">
										<textarea name="nota" id="nota" maxlength="255" class="form-control" id="exampleFormControlTextarea1" rows="3"></textarea>
										<div class="text-right"><span class="valid-text pt-3" id="txaCount">0/255</span></div>
									</div>
								</div><!--end form-group-->
							</div><!--end col-->																					
						</div><!--end row-->
					
						
					
						<input id="lat" name="lat" type="hidden">
						<input id="lon" name="lon" type="hidden">
						<div class="mt-3" align="center">
							<button type="submit" class="btn btn-primary waves-effect waves-light">GUARDAR GESTION</button>
							<button type="button" onClick="javascript:location.href='menu_gestor.php';" class="btn btn-danger waves-effect waves-light">VOLVER</button>
						</div>
				</form>	
				</div> <!-- end card-body -->
			</div> <!-- end card -->                                       
		</div> <!-- end col -->
	</div> <!-- end row -->
	
</div>	
	
	<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
	<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/rowreorder/1.4.1/js/dataTables.rowReorder.min.js"></script>
	<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
	<script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
 	<script src="assets/js/feather.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>			
<script language="javascript">
// In your Javascript (external .js resource or <script> tag)
		$(document).ready(function() {
			$('.js-example-basic-single').select2();
		});
</script>	
				
<script language="javascript">
const mensaje = document.getElementById('nota');
const contador = document.getElementById('txaCount');

mensaje.addEventListener('input', function(e) {
    const target = e.target;
    const longitudMax = target.getAttribute('maxlength');
    const longitudAct = target.value.length;
    contador.innerHTML = `${longitudAct}/${longitudMax}`;
});
	
$(document).ready(function(){
			$('#idfecha').hide(); //oculto mediante id
			$('#idmonto').hide(); //oculto mediante id
			
			$('#idtelefono').hide(); //oculto mediante id
			$('#iddireccion').hide(); //oculto mediante id
			$('#idcomuna').hide(); //oculto mediante id
				$('#idtipodireccion').hide(); //oculto mediante id
			$('#telefono2').hide(); //oculto mediante id
			$('#idnuevagestion').hide(); //oculto mediante id
		
});		
</script>
<script language="javascript">
	function muestra() {
		if (document.formulario.categoria.selectedIndex == 1) { // CONTACTO DIRECTO
			$('#idfecha').show(); //oculto mediante id
			$('#idmonto').show(); //oculto mediante id
			$('#idnota').show(); //oculto mediante id
			$('#idtelefono').show(); //oculto mediante id
			$('#iddireccion').show(); //oculto mediante id
			$('#idcomuna').show(); //oculto mediante id
			$('#idtipodireccion').show(); //oculto mediante id
			$('#telefono2').show(); //oculto mediante id
			$('#idnuevagestion').show(); //oculto mediante id

			$("#fecha").val('');
			$("#monto").val('');			
			$("#telefono").val('');
			
		} else {
			if (document.formulario.categoria.selectedIndex == 2) { // CONTACTO INDIRECTO
				$('#iddireccion').show(); //oculto mediante id
				$('#idcomuna').show(); //oculto mediante id
				$('#idtipodireccion').show(); //oculto mediante id
				$('#telefono2').show(); //oculto mediante id
				$('#idnuevagestion').show(); //oculto mediante id
				
				$('#idfecha').hide(); //oculto mediante id
				$('#idmonto').hide(); //oculto mediante id
				$('#idtelefono').hide(); //oculto mediante id
				
			} else {
				$('#idfecha').hide(); //oculto mediante id
				$('#idmonto').hide(); //oculto mediante id
				$('#idtelefono').hide(); //oculto mediante id
				$('#iddireccion').hide(); //oculto mediante id
				$('#idcomuna').hide(); //oculto mediante id
				$('#idtipodireccion').hide(); //oculto mediante id
				$('#telefono2').hide(); //oculto mediante id
				$('#idnuevagestion').hide(); //oculto mediante id
			}
		} 
	};
	
function valida_envia(){
				
				var date1 = new Date(document.formulario.fecha.value);
				var date2 = new Date('<?= $hoy_corto?>');
				var date3 = new Date('<?= $findemes?>');

	
				if (document.formulario.categoria.selectedIndex == 0){
						Swal.fire({
						  width: 600,
						  icon: 'error',
						  title: 'Debe seleccionar una Categoría',
						  showConfirmButton: false,
						  timer: 2000,
						})
						return false;
				}
	
				
				
				if (document.formulario.subcategoria.value == 6){
						if (document.formulario.fecha.value.length == 0){
							Swal.fire({
							  width: 600,
							  icon: 'error',
							  title: 'Debe ingresar Fecha de Compromiso',
							  showConfirmButton: false,
							  timer: 2000,
							})
							return false;
						};	
					
						if (document.formulario.monto.value < 5000){
							Swal.fire({
							  width: 600,
							  icon: 'error',
							  title: 'Debe ingresar Monto mayor a $5.000',
							  showConfirmButton: false,
							  timer: 2000,
							})
							return false;
						};	
					
						
											
				}
				// APORTA NUEVA DIRECCION

				if (document.formulario.subcategoria.value == 9){
						if (document.formulario.ndireccion.value.length == 0){
							Swal.fire({
							  width: 600,
							  icon: 'error',
							  title: 'Debe ingresar Nueva Dirección',
							  showConfirmButton: false,
							  timer: 2000,
							})
							return false;
						};	
					
						if (document.formulario.ncomuna.selectedIndex == 0){
								Swal.fire({
								  width: 600,
								  icon: 'error',
								  title: 'Debe Seleccionar una Nueva Comuna',
								  showConfirmButton: false,
								  timer: 2000,
								})
								return false;
						}
						
				}
				
				if (document.formulario.subcategoria.selectedIndex == 0){
						Swal.fire({
						  width: 600,
						  icon: 'error',
						  title: 'Debe seleccionar una Sub-Categoría',
						  showConfirmButton: false,
						  timer: 2000,
						})
						return false;
				}
	 
				if (date1 < date2){
						Swal.fire({
						  width: 600,
						  icon: 'error',
						  title: 'Fecha No debe ser Menor al Dia Actual',
						  showConfirmButton: false,
						  timer: 2000,
						})
						return false;
				}
	
				if (date1 > date3){
						Swal.fire({
						  width: 600,
						  icon: 'error',
						  title: 'Fecha No debe ser Mayor a Fin de Mes',
						  showConfirmButton: false,
						  timer: 2000,
						})
						return false;
				}
				
				if (document.formulario.ntelefono.value.length > 0){
					if (document.formulario.ntelefono.value.length < 9){
						Swal.fire({
						  width: 600,
						  icon: 'error',
						  title: 'El telefono debe ser de 9 digitos',
						  showConfirmButton: false,
						  timer: 2000,
						})
						return false;
					}
				}
	
				if (document.formulario.nota.value.length == 0){
						Swal.fire({
						  width: 600,
						  icon: 'error',
						  title: 'Debe ingresar una Nota',
						  showConfirmButton: false,
						  timer: 2000,
						})
						return false;
				}
				
				
				
				document.formulario.submit();
			}	
</script>
		
	
<script language="javascript">
new DataTable('#example', {
	select: {
        info: false
    },
	fixedHeader: true,
	info: false,
	searching: false,
 	 paging: false,
	 language: {
        "decimal": "",
		  
        "emptyTable": "No hay información",
        "info": "Mostrando _START_ a _END_ de _TOTAL_ Datos",
        "infoEmpty": "Mostrando 0 to 0 of 0 Entradas",
        "infoFiltered": "",
        "infoPostFix": "",
        "thousands": ",",
        "lengthMenu": "Mostrar _MENU_ Datos",
        "loadingRecords": "Cargando...",
        "processing": "Procesando...",
        "search": "Buscar:",
        "zeroRecords": "Sin resultados encontrados",
        "paginate": {
            "first": "Primero",
            "last": "Ultimo",
            "next": "Siguiente",
            "previous": "Anterior"
        }
    },
    responsive: true,
    rowReorder: {
        selector: 'td:nth-child(2)'
    }
});		
</script>	
<script language="javascript">
		   $(document).ready(function(){
			$("#categoria").on('change', function () {
				$("#categoria option:selected").each(function () {
					var id_categoria = $(this).val();
					
					$.post("get_subcategorias.php", { id_categoria: id_categoria }, function(data) {
						$("#subcategoria").html(data);
					});			
				});
		   });
		});
</script>	
</body>
</html>