############################################################
#   CMS BASE - DOCUMENTO DE CONTEXTO PARA IA (v1.0)  #
############################################################
# Usuario: Pelín (Developer & Project Lead, fanático de Les Luthiers)
# Fecha de actualización: Marzo 2026
# Misión: Mantener la coherencia técnica y evitar repetir errores pasados.
# Descripción: El CMS BASE es un sitio web basado en PHP, HTML, CSS y JAVASCRIPT (incluye PHPMailer) pensado para ser usado como la base de todos los proyectos de Pelín, ya sean grandes o pequeños. Es parecido a Wordpress pero muchísimo más sencillo y con menos código inútil. Sin embargo, es un sistema centrado en la extrema seguridad para los usuarios.
# Alcance: Como es un CMS base, se podrá aplicar a cualquier tipo de necesidad, ya sea una tienda online, una sitio personal, para una empresa, un negocio, etc. Cuenta con las opciones básicas para que funcione. Por eso no es necesario adornarlo con funcionalidades para la comodidad de un usuario normal. Es más parecido a un Framework personalizado para Pelín. Puedes sugerir approaches para lograr los objetivos de Pelín.
# Contexto: Pelín es un desarrollador freelance especializado en HTML, CSS, JAVASCRIPT, PHP y rara vez NODEJS. Realiza proyectos a todo tipo de clientes. Generalmente los proyectos son sitios web o también otros servicios digitales como restauración fotográfica, edición de audio y video, diseño gráfico.
# Entorno de desarrollo: Servidor Virtual de XAMPP "https://www.cmsbase.mahg/"

============================================================
1. DETALLES DE ENTORNO LOCAL DE DESARROLLO
============================================================
- El entorno de desarrollo de Pelín funciona con XAMPP. 
- Pelín está corriendo CMS BASE en un servidor virtual al que accede a traves de "https://www.cmsbase.mahg/" y reside localmente en D:\WWW\cmsBase\
- Corre bajo https gracias a mkcert, lo que nos permite implementar código PHP listo para producción, para entornos seguros basados en https.  
- Dispone además de otro servidor virtual "https://www.lab.mahg/" que usa como "laboratorio" de pruebas de diferentes cosas. Podemos usarlo para ejecutar desde allí scripts maliciosos de prueba que ataquen https://www.cmsbase.mahg/ y así encontrar vulnerabilidades que mejorar.
Tener sus proyectos con https le ayuda a desarrollarlos sin problemas de http inseguro. Evita problemas como no poder cargar ciertos servicios porque estan en https y el proyecto de Pelín en http solamente. 
De modo que si necesito hacer pruebas que requieran otro servidor, https://www.lab.mahg/ está disponible para correr scripts que llamen a https://www.cmsbase.mahg/ y probar lo que se necesite

============================================================
2. ESPECIFICACIONES DE DESARROLLO
============================================================
- SEGURIDAD EXTREMA: todo el sitio, especialamente el login debe estar ultra protegido contra todo tipo de amenazas. Debe manejar:
    - SQL Injection (SQLi)
    - Cross-Site Scripting (XSS)
    - Session Hijacking
    - Brute Force (Login)
    - CSRF (Falsificación de Petición)
    - Clickjacking
    - Host Header Injection
    - Email Header Injection
    - Path Disclosure
    - MIME Sniffing
    - Inyección de Sesión
    - Divulgación de Versión
    - Weak Hashing
    - Acceso Directo a Core
    - Opción Técnica Oculta
    - Subida de Archivos Sin Restricciones
    - Spoofing de MIME
    - Esteganografía Maliciosa
    - Polyglots
    Períodicamente durante el proceso de desarrollo se pueden realizar tests para probar la seguridad, usando también https://www.lab.mahg/
- REUTILIZACIÓN DE FUNCIONES: en vez de escribir código php para cada página del sitio, se harán funciones flexibles que permitan aprovechar al máximo el código escrito.
- NUNCA ROMPER CÓDIGO EXITOSO: si algunas funciones, código o diseño ya funciona bien, no tocarlo al escribir código nuevo.
- URLs LIMPIAS: aunque internamente php trabajará con hashes y variables, la url siempre será amigable. Ejemplo: "index.php?pagina=23" siempre debería mostrarse "/productos/mi-producto-genial/".
- WORDPRESS LIKE: Worpress divide su contenido en 2 tipos, cronológico y estático. Nuestro proyecto también dividirá el contenido de la misma manera:
    - Páginas (custom pages): contenido estático para información que "siempre está ahí" y no depende del tiempo. (Contacto, Quiénes somos, etc)
    - Items (custom posts): un tipo de contenidos cronologico organizado por categorias, etiquetas, fechas
- IMPLEMENTACIÓN MODULAR: el sitio debe ser flexible para todo tipo de proyectos. Por defecto el sitio manejará un tipo de contenido que llamaremos "Artículos" (posts). Deben poder agregarse nuevos tipos de contenido siguiendo algunas reglas básicas
- TIPOS DE CONTENIDO: Pelín podrá agregar cualquier "tipo de contenido" al proyecto según las necesidades de su cliente. Bastará con agregar una carpeta dentro de "admin/contenidos/" con los archivos necesarios (estilo plugins) para que se integre de forma robusta al sistema CMS BASE. Cada tipo de contenido tendrá sus propios Items, páginas, js, css, etc, según necesite para su funcionamiento. Los modulos se podrán reutilizar en futuros proyectos.
- TEMPLATES: Se usarán estructuras estilo templates en el sitio web, para reutilizar codigo. Por ejemplo, el footer y el header tanto de admin como de public tienen el prefijo sec- (sección) y se integran dinamicamente para formar las páginas. Cuando se usen módulos, estos deberán tener sus propios sec-header sec-aside, etc, para tener más flexibilidad con el diseño.
- USO DE GITHUB: Revisa mi repositorio en todo momento.
- FILOSOFÍA DE EFICACIA (LA OBSESIÓN): El sistema se desplegará en hostings compartidos económicos y accederá desde conexiones lentas.
    - **Client-Side Optimization:** Delegar el procesamiento pesado (como redimensionado de imágenes) al navegador para ahorrar CPU del servidor y reducir drásticamente el uso de ancho de banda.
    - **Simplicidad Robusta:** Evitar frameworks pesados o dependencias de Node.js. PHP puro y eficiente es la norma.
    - **Legibilidad vs Bytes:** Priorizar nombres de funciones descriptivos en español para el mantenimiento. La optimización de transferencia se logra vía lógica (procesamiento en cliente) y compresión de servidor, no ofuscando el código fuente.

============================================================
3. USO DE GITHUB
============================================================
Es IMPORTANTÍSIMO que en este proyecto aproveches la ventaja de usar gitHub. Mi código se irá actualizando cada vez que me des código nuevo. Puedes leer todo directamnte ahí.
A medida que vamos avanzando, necesito que puedas leer los archivos de este proyecto para no estar copiándolo una y otra vez en el chat.

Puedes leer el raw de todos mis archivos en GitHub
Mi repositorio: https://github.com/sharemystuff/CMS_base

Los raw:
https://raw.githubusercontent.com/sharemystuff/CMS_base/refs/heads/main/ + cualquier archivo que necesites

Ejemplo: 
quieres leer admin/admin.php
https://raw.githubusercontent.com/sharemystuff/CMS_base/refs/heads/main/ + admin/admin.php
Quedaría así:
https://raw.githubusercontent.com/sharemystuff/CMS_base/refs/heads/main/admin/admin.php


============================================================
4. REGLAS DE ORO APRENDIDAS DURANTE EL DESARROLO DEL CMS
============================================================
- Prioridad absoluta al desarrollo incremental y testeo temprano: no se avanzará en capas superiores de código sin haber verificado la funcionalidad de las bases (Evitar el 'efecto cascada' de errores). 
- Cada archivo entregado por la IA debe comenzar con un comentario en la primera línea dentro de "<?php" indicando su ruta relativa (ej: /* carpeta/archivo.php */) para facilitar la organización, checkeo y el despliegue rápido.
- Código siempre indentado a 4 espacios. Fundamental para que los humanos entendamos mejor el código.
- Metodología de entrega de código: La IA debe entregar siempre los archivos en su versión completa (sin recortes por agilidad) para asegurar que el desarrollador pueda copiar y pegar el bloque íntegro sin errores de integración. 
- Tras cada hito exitoso, se realizará un commit conmemorativo.
- Cada vez que se crea algo nuevo, la implementación debe ser consecuente con las medidas de seguridad implementadas. Las funciones deben adaptarse a la seguridad, no al revés.
- Mi entorno de desarrollo funciona con XAMPP. Estoy corriendo CMS BASE en un servidor virtual al que accedo a traves de "https://www.cmsbase.mahg/"
- Corre bajo https gracias a mkcert
- Dispongo además de otro servidor virtual "https://www.lab.mahg/" que uso como "laboratorio" de pruebas de diferentes cosas. 
Tener mis proyectos con https me ayuda a desarrollarlos sin problemas de http inseguro, no poder cargar ciertos servicios porque estan en https y yo en http solamente. 
De modo que si necesitas hacer pruebas que requieran otro servidor, https://www.lab.mahg/ está disponible para que le pongamos scripts que llamen a https://www.cmsbase.mahg/ y probemos lo que se necesite

============================================================
5. MEDIDAS DE SEGURIDAD YA IMPLEMENTADAS
============================================================
Amenaza Neutralizada, Medida Tomada, Ubicación en el Código, Por qué es Crítico
1. SQL Injection (SQLi), Sentencias preparadas con bind_param., "tovi/funciones.php,  api/login_proceso.php", Evita que un atacante extraiga o borre la base de datos mediante comandos en los inputs.
2. Cross-Site Scripting (XSS), Función limpiar_entrada y escape de salida con e()., "seguridad/funciones.php,  api/main.php", Impide que se ejecuten scripts maliciosos en el navegador de otros usuarios.
3. Session Hijacking, "Cookies con HttpOnly,  Secure y SameSite=Strict.", api/main.php (configuración de sesión), Evita que un hacker robe la cookie de sesión mediante JavaScript o redes no seguras.
4. Brute Force (Login), Retraso forzado con sleep(2) en intentos fallidos., api/login_proceso.php, Ralentiza los ataques de bots; probar un diccionario de claves tomaría años en lugar de minutos.
5. CSRF (Falsificación de Petición), Generación y validación de tokens únicos por sesión., "seguridad/funciones.php,  api/main.php", Asegura que las acciones (como borrar posts) solo las realice el usuario desde tu sitio real.
6. Clickjacking, Cabecera X-Frame-Options: DENY., api/main.php, Impide que tu panel de admin sea cargado dentro de un iframe invisible en sitios maliciosos.
7. Host Header Injection, Uso de url_sitio fijo desde la base de datos., "api/registro_proceso.php,  tovi/pacheco.php", Evita que los correos del sistema apunten a dominios de hackers para robar tokens.
8. Email Header Injection, Eliminación de \r y \n en inputs., seguridad/funciones.php (función limpiar_entrada), Impide que se inyecten campos como Bcc: para usar tu servidor como plataforma de SPAM.
9. Path Disclosure, Desactivación de display_errors y rutas silenciadas., "api/main.php,  api/db.php", Evita que un error de PHP revele la estructura de carpetas privada de tu servidor.
10. MIME Sniffing, Cabecera X-Content-Type-Options: nosniff., api/main.php, "Obliga al navegador a respetar el tipo de archivo enviado,  evitando que un .txt se ejecute como script."
11. Inyección de Sesión, session.use_strict_mode = 1., api/main.php, Evita que un atacante inicialice una sesión con un ID inventado por él.
12. Divulgación de Versión, Silenciamiento de errores y headers genéricos., Servidor / api/main.php, "Oculta que usas PHP o versiones específicas,  dificultando la búsqueda de exploits conocidos."
13. Weak Hashing, Uso de PASSWORD_BCRYPT., tovi/funciones.php, Hace que las contraseñas sean computacionalmente costosas de descifrar incluso si roban la DB.
14. Acceso Directo a Core, Constante INSTALACION_PERMITIDA y bloqueos de ruta., "tovi/pacheco.php,  api/db.php", Evita que se reinicie la instalación o se lean archivos de config directamente.
15. Opción Técnica Oculta, Filtrado en get_all_opciones., tovi/funciones.php, Impide que un usuario administrador cambie la URL del sitio o el Salt Maestro por error.

============================================================
6. ESTADO ACTUAL Y PRÓXIMOS PASOS
============================================================
- CMS BASE ya tiene practicamente toda la sección de login y registro completo. Hay que corregir algunas inconsistencias y errores provocados por la implementación de una robusta seguridad contra todo tipo de ataques externos.

- Está faltando la zona de administración
- Está faltando la implementación de url amigables
- Listado de usuarios
- perfil básico de usuarios
- estructura de módulos
- y más...

############################################################







¡¡¡Hola maestro Mangiacaprini!!!