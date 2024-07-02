<!DOCTYPE html>
<html>
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

</head>
<body>

	<input id="usuario">
	<input id="nombre">
	<button id="boton">Send an HTTP POST request to a page and get the result back</button>

<script>
$(document).ready(function(){
  $("#boton").click(function(){
    $.post("bd_comun.php",
    {
      usuario: $("#usuario").val()
    },
    function(data,status){
      // alert("Data: " + data + "\nStatus: " + status);
      let json1 = JSON.parse(data);

        //alert(json1.NOMBRE);	
        $("#nombre").val(json1.NOMBRES)
        $("#nombre").html(json1.NOMBRES)
      
    });
  });
});
</script>

</body>
</html>