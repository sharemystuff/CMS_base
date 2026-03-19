<?php
$active_id = $page_config['menu_id'] ?? '';
?>
        <aside>

            <!-- Menu -->
            <section id="menu">
                <ul>
                    <li id="dashboard" class="link enlace <?php echo $active_id === 'dashboard' ? 'activo' : ''; ?>" enlace="pag-admin">
                        <div class="menu-item">
                            <i class="ti-panel"></i> <span class="movil">Administración</span>
                        </div>
                        <div class="submenu">
                            <div class="item-mov">
                                <span>Administración</span>
                            </div>
                        </div>
                    </li>

                    <li class="link enlace <?php echo $active_id === 'perfil' ? 'activo' : ''; ?>" enlace="perfil">
                        <div class="menu-item">
                            <i class="ti-face-smile"></i> <span class="movil">Mi perfil</span>
                        </div>
                        <div class="submenu">
                            <div class="item-mov">
                                <span>Mi perfil</span>
                            </div>
                        </div>
                    </li>

                    <li class='sub <?php echo $active_id === 'paginas' ? 'activo' : ''; ?>' id='paginas'>
                        <div class='menu-item'>
                            <i class='ti-files'></i> <span class='movil'>Páginas</span> <i
                                class='ti-angle-right movil fade'></i>
                        </div>
                        <div class='submenu'>
                            <div class='item-mov'>
                                <span>Páginas</span>
                            </div>
                            <div class='item enlace' enlace='modulos/paginas/pag-inicio'>
                                <i class='ti-angle-right'></i> <span>Inicio</span>
                            </div>
                            <div class='item enlace' enlace='modulos/paginas/pag-servicios'>
                                <i class='ti-angle-right'></i> <span>Servicios</span>
                            </div>
                        </div>
                    </li>
                    <li id='productos' class='link enlace <?php echo $active_id === 'productos' ? 'activo' : ''; ?>' enlace='modulos/productos/pag-productos'>
                        <div class='menu-item'>
                            <i class='ti-view-list-alt'></i> <span class='movil'>Productos</span>
                        </div>
                        <div class='submenu'>
                            <div class='item-mov'>
                                <span>Productos</span>
                            </div>
                        </div>
                    </li>
                    <li id='servicios' class='link enlace <?php echo $active_id === 'servicios' ? 'activo' : ''; ?>' enlace='modulos/servicios/pag-servicios'>
                        <div class='menu-item'>
                            <i class='ti-layout-grid2'></i> <span class='movil'>Servicios</span>
                        </div>
                        <div class='submenu'>
                            <div class='item-mov'>
                                <span>Servicios</span>
                            </div>
                        </div>
                    </li>
                    <li class='sub <?php echo $active_id === 'sitio-web' ? 'activo' : ''; ?>' id='sitio-web'>
                        <div class='menu-item'>
                            <i class='ti-layout'></i> <span class='movil'>Sitio Web</span> <i
                                class='ti-angle-right movil fade'></i>
                        </div>
                        <div class='submenu'>
                            <div class='item-mov'>
                                <span>Sitio Web</span>
                            </div>
                            <div class='item enlace' enlace='modulos/sitioweb/pag-contacto'>
                                <i class='ti-angle-right'></i> <span>Contacto</span>
                            </div>
                            <div class='item enlace' enlace='modulos/sitioweb/pag-seo'>
                                <i class='ti-angle-right'></i> <span>SEO</span>
                            </div>
                            <div class='item enlace' enlace='modulos/sitioweb/pag-opciones'>
                                <i class='ti-angle-right'></i> <span>Opciones</span>
                            </div>
                        </div>
                    </li>
                    <li class='sub <?php echo $active_id === 'usuarios' ? 'activo' : ''; ?>' id='usuarios'>
                        <div class='menu-item'>
                            <i class='ti-user'></i> <span class='movil'>Usuarios</span> <i
                                class='ti-angle-right movil fade'></i>
                        </div>
                        <div class='submenu'>
                            <div class='item-mov'>
                                <span>Usuarios</span>
                            </div>
                            <div class='item enlace' enlace='modulos/usuarios/pag-usuarionuevo'>
                                <i class='ti-angle-right'></i> <span>Nuevo Usuario</span>
                            </div>
                            <div class='item enlace' enlace='modulos/usuarios/pag-usuarios'>
                                <i class='ti-angle-right'></i> <span>Ver todos</span>
                            </div>
                        </div>
                    </li>

                </ul>
            </section>
        </aside>