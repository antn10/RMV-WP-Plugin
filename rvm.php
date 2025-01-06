<?php
/**
 * Plugin Name:       rvmwpplugin
 * Plugin URI:        https://lav.uy/rvmwpplugin
 * Description:       Reproduce juegos de computadoras clásicas como Spectrum o Amstrad directamente en tu sitio WordPress.
 * Version:           1.0.0
 * Requires at least: 5.5
 * Requires PHP:      7.0
 * Author:            lav
 * Author URI:        https://lav.uy
 * Text Domain:       rvmwpplugin
 * Domain Path:       /assets/languages
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * rvmwpplugin es un software libre: puedes redistribuirlo y/o modificarlo bajo los términos de la
 * Licencia Pública General de GNU publicada por la Free Software Foundation, ya sea la versión 2
 * de la Licencia o cualquier versión posterior.
 *
 * rvmwpplugin se distribuye con la esperanza de que sea útil, pero SIN NINGUNA GARANTÍA; incluso sin la
 * garantía implícita de COMERCIABILIDAD o IDONEIDAD PARA UN PROPÓSITO PARTICULAR. Consulta la
 * Licencia Pública General de GNU para más detalles.
 *
 * Deberías haber recibido una copia de la Licencia Pública General de GNU junto con este plugin.
 * Si no es así, visita <https://www.gnu.org/licenses/>.
 */

// Agregar página de configuración al menú de administración
function rvmplugin_add_settings_page() {
    add_menu_page(
        'Configuración RVM Player', // Título de la página
        'Cómo usar RVM', // Título del menú
        'manage_options', // Capacidad requerida
        'rvmplugin-settings', // Slug
        'rvmplugin_render_settings_page', // Función que muestra el contenido
        'dashicons-album', // Icono del menú
        81 // Posición en el menú
    );
}
add_action('admin_menu', 'rvmplugin_add_settings_page');

function rvmplugin_render_settings_page() {
    // Verifica permisos
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1>Cómo usar Retro Virtual Machine en este sitio</h1>
        <p>Este plugin permite activar un reproductor de Retro Virtual Machine en tus páginas o posts.</p>
        <p>Puedes iniciar el emulador de Spectrum+3 o Amstrad CPC6128<p>
        <h2>Para iniciar un Spectrum+3 utiliza el shortcode: <code>[rvmplus3]</code></h2>
        <h2>Para iniciar un Amstrad CPC6128 utiliza el shortcode: <code>[rvmcpc6128]</code></h2>
        <hr>
        <h2>Si quieres activar un juego en spectrum</h2>
        <p>Sigue los siguientes pasos</p>
        <ol>
            <li>Prepara un archivo DSK con el loader llamado 'disk'</li>
            <li>Sube el archivo a la carpeta /plugins/rvm/assets/disks/ de esta web.</li>
            <li>Utiliza el shortcode <code>rvmplus3</code> usando el nombre del archivo en el atributo <code>disk_url</code>.</li>
        </ol>
        <h2>Ejemplo:<code>[rvmplus3 disk_url="pachic.dsk"]</code></h2>
        <hr>
        <h2>Si quieres activar un juego en amstrad</h2>
        <p>Sigue los siguientes pasos</p>
        <ol>
            <li>Prepara un archivo DSK con el juego. Fijate en el nombre del cargador.</li>
            <li>Sube el archivo a la carpeta /plugins/rvm/assets/disks/ de esta web.</li>
            <li>Utiliza el shortcode <code>rvmcpc6128</code></li>
            <li>Agregale el nombre del archivo dsk en el atributo <code>disk_url</code>.</li>
            <li>Agregale el comando run en el atributo <code>command</code> con el nombre del loader y usando los caracteres \\n al final</li>
        </ol>
        <h2>Ejemplo:<code>[rvmcpc6128 disk_url="Abu Simbel Profanation (S) (1986).dsk" command='run "profana\\n']</code></h2>
        <hr>
        <h2>Otras opciones del shortcode</h2>
        <p>Parámetros adicionales:</p>
        <ul>
            <li><code>width</code>: Ancho del contenedor (por defecto: <code>800px</code>).</li>
            <li><code>height</code>: Altura del contenedor (por defecto: <code>600px</code>).</li>
        </ul>
    </div>
    <?php
}

function rvmplayer_shortcode_plus3($atts) {
    // URL base para los archivos DSK
    $base_disk_url = plugins_url('assets/disks/', __FILE__);
    // Extraer los atributos del shortcode    
    $atts = shortcode_atts(
        [
            'disk_url' => '', // Archivo DSK
            'width' => '800px', // Ancho del contenedor
            'height' => '600px', // Altura del contenedor
        ], $atts,'rvmplayer'
    );
    // Manejo del archivo DSK: verificar si se pasó en el shortcode
    $disk_url = '';
    if (!empty($atts['disk_url'])) { $disk_url = $base_disk_url . ltrim($atts['disk_url'], '/');}
    // Registrar el script externo
    wp_enqueue_script(
        'rvmplayer-script',
        'https://cdn.rvmplayer.org/rvmplayer.plus3.min.js',
        [],null,true
    );
    // Generar un identificador único para el contenedor
    $container_id = 'rvmplayer_' . uniqid();
    // Generar el HTML del reproductor
    ob_start();
    ?>
    <div id="<?php echo esc_attr($container_id); ?>" class="rvmplayer-container" style="width: <?php echo esc_attr($atts['width']); ?>; height: <?php echo esc_attr($atts['height']); ?>;"></div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('#<?php echo esc_js($container_id); ?>');            
            const config = { 
                command: '\n',
                warpFrames: 20 * 50 };
            <?php if (!empty($disk_url)): ?>
                config.disk = {type: 'dsk',url: '<?php echo esc_url($disk_url); ?>'};
            <?php endif; ?>
            rvmPlayer_plus3(container, config);
        });
    </script>
    <?php
    return ob_get_clean();
}

function rvmplayer_shortcode_cpc6128($atts) {
    // URL base para los archivos DSK
    $base_disk_url = plugins_url('assets/disks/', __FILE__);
    // Extraer los atributos del shortcode    
    $atts = shortcode_atts(
        [
            'disk_url' => '', // Archivo DSK
            'width' => '800px', // Ancho del contenedor
            'height' => '600px', // Altura del contenedor
            'command' => 'run "disk\n', // Comando predeterminado
        ], $atts,'rvmplayer'
    );
    // Manejo del archivo DSK: verificar si se pasó en el shortcode
    $disk_url = '';
    if (!empty($atts['disk_url'])) { $disk_url = $base_disk_url . ltrim($atts['disk_url'], '/');}
    // Registrar el script externo
    wp_enqueue_script(
        'rvmplayer-script',
        'https://cdn.rvmplayer.org/rvmplayer.cpc6128.min.js',
        [],null,true
    );
    // Generar un identificador único para el contenedor
    $container_id = 'rvmplayer_' . uniqid();
    // Generar el HTML del reproductor
    ob_start();
    ?>
    <div id="<?php echo esc_attr($container_id); ?>" class="rvmplayer-container" style="width: <?php echo esc_attr($atts['width']); ?>; height: <?php echo esc_attr($atts['height']); ?>;"></div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('#<?php echo esc_js($container_id); ?>');            
            const config = { 
                command: '<?php echo $atts['command'] ?>', 
                warpFrames: 20 * 50 };
            <?php if (!empty($disk_url)): ?>
                config.disk = {type: 'dsk',url: '<?php echo esc_url($disk_url); ?>'};
            <?php endif; ?>
            rvmPlayer_cpc6128(container, config);
        });
    </script>
    <?php
    return ob_get_clean();
}

add_shortcode('rvmcpc6128', 'rvmplayer_shortcode_cpc6128');
add_shortcode('rvmplus3', 'rvmplayer_shortcode_plus3');