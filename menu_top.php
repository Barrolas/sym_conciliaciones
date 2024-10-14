            <div class="topbar">            
                <!-- Navbar -->
                <nav class="navbar-custom ">    
					
                    <ul class="list-unstyled topbar-nav float-right mb-0">  
						
						<li class="dropdown">
							
                            <a class="nav-link dropdown-toggle waves-effect waves-light nav-user" data-toggle="dropdown" href="#" role="button"
                                aria-haspopup="false" aria-expanded="false">
                                <span class="ml-1 nav-user-name hidden-sm"> <i class="fa fa-user-circle px-2" aria-hidden="true"></i><b></b> <?php echo $_SESSION["NOMBRES"];?> <i class="fa fa-bell px-2" aria-hidden="true"></i></span>
                                                                
                            </a>
							
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="logout_session.php"><i data-feather="power" class="align-self-center icon-xs icon-dual mr-1"></i> Salir</a>
                            </div>
                        </li>
                    </ul><!--end topbar-nav-->
        
                    <ul class="list-unstyled topbar-nav mb-0">                        
                        <li>
                            <button class="nav-link button-menu-mobile">
                                <i data-feather="menu" class="align-self-center topbar-icon"></i>
                            </button>
                        </li> 
                        <li class="creat-btn">
                            <div class="nav-link">
                               <!-- <a class=" btn btn-sm btn-soft-primary" href="usuarios_crear.php" role="button"><i class="fas fa-plus mr-2"></i>Nuevo Usuario</a>   -->         
								
                            </div>                                
                        </li>                           
                    </ul>
                </nav>
            </div>
				