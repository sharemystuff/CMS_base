<?php
/* index.php */

// Cargamos el cerebro. Si no hay instalación, esto nos mandará a Pacheco.
include_once 'api/main.php';

echo "<h1>CMS BASE</h1>";
echo "<p>El sistema está operativo. Salt actual: " . substr(AUTH_SALT, 0, 10) . "...</p>";
?>