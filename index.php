<?php
/* index.php (raiz) */
include_once 'api/main.php';

echo "<h1>CMS BASE</h1>";
// Ahora substr(AUTH_SALT...) no dará error porque la constante se define en main.php
echo "<p>El sistema está operativo. Salt actual: " . substr(AUTH_SALT, 0, 10) . "...</p>";
?>