<?php    
    // include("permisos_adm.php");
    noCache();
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
?>

<head>

<style>


</style>

</head>

<div class="left-sidenav border-0">
        <!-- LOGO -->
        <div class="brand">
            <a href="menu_principal.php" class="logo">
                <span>
                    <img src="assets/images/symtrnasparente2.png" alt="logo-small" height="60" width="120" class="mt-4">
                </span>
            </a>
        </div>
        <!--end logo--> 
        <br>

        <a href="menu_principal.php" class="logo">
            <h5 class="text-center text-white mt-4">SISTEMA JUDICIAL</h5>
        </a>

        <div class="menu-content h-100" data-simplebar>
            

            <ul class="metismenu left-sidenav-menu mb-5">	
            <?php 
                $sql = "EXEC _SP_MENU_PERFILES ".$_SESSION["PERFIL"]	;

                $stmt = sqlsrv_query( $conn, $sql );
                //echo $sql ;	
                if( $stmt === false) {	
                    die( print_r( sqlsrv_errors(), true) );
                }                                        
                while( $menu = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
            ?>    				
                <li>
                    <a href="javascript: void(0);"> 
                    <i data-feather="minus-square" class="align-self-center menu-icon"></i>
                        <span><?= $menu["DESCRIPCION"]?></span>
                        <span class="menu-arrow">
                    <i class="mdi mdi-chevron-right"></i></span></a>

                    <ul class="nav-second-level" aria-expanded="false">	
                    <?php 
                        $sql2 = "_SP_MENU_PERFIL_PROGRAMAS ".$_SESSION["PERFIL"].','.$menu["ID_MODULO"];

                        $stmt2 = sqlsrv_query( $conn, $sql2 );
                        //echo $sql ;	
                        if( $stmt2 === false) {	
                            die( print_r( sqlsrv_errors(), true) );
                        }                                        
                        while( $programa = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC) ) {
                        ?>    	
                        <li class="nav-item"><a class="nav-link" href="<?=$programa["PHP"];?>"><i class="ti-control-record"></i><?=$programa["PROGRAMA"];?></a></li>
                        <?php };?>
                    </ul>
                </li>					
            <?php };?>	
                </ul>
        
        </div>
</div>