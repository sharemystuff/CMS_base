<?php
/* admin/sec-footer.php */
?>
            <footer>&copy; <?php echo date('Y'); ?> CMS BASE - Versión 1.0</footer>
        </div> <!-- Fin .content-wrapper -->
    </div> <!-- Fin .admin-layout -->

    <!-- Variables Globales para JS -->
    <script>
        const CMS_VARS = {
            csrf_token: "<?php echo $_SESSION['csrf_token'] ?? ''; ?>"
        };
    </script>

    <script src="<?php echo recurso('assets/plugins/jquery.js'); ?>"></script>
    <script src="<?php echo recurso('admin/js/admin.js'); ?>"></script>
    
    <!-- Scripts Específicos de la página -->
    <?php if(!empty($page_config['scripts'])): foreach($page_config['scripts'] as $js): ?>
        <script src="<?php echo recurso($js); ?>"></script>
    <?php endforeach; endif; ?>

    <!-- Mensajes Flash inyectados desde PHP -->
    <?php if(!empty($script_mensaje)): ?>
    <script>
        $(document).ready(function() { <?php echo $script_mensaje; ?> });
    </script>
    <?php endif; ?>
</body>
</html>