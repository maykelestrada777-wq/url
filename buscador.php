
<?php
header('Content-Type: text/html; charset=utf-8');

$rutaJson = __DIR__ . '/data-1.json';
if (!file_exists($rutaJson)) {
    echo '<div class="card-panel">No se encontro el archivo data-1.json.</div>';
    exit;
}

$propiedades = json_decode(file_get_contents($rutaJson), true);
if (!is_array($propiedades)) {
    echo '<div class="card-panel">No se pudieron leer los registros del JSON.</div>';
    exit;
}

function limpiarPrecio($valorPrecio) {
    $soloNumeros = preg_replace('/[^0-9.]/', '', (string)$valorPrecio);
    return (float)$soloNumeros;
}

$mostrarTodos = isset($_POST['mostrarTodos']) && $_POST['mostrarTodos'] === '1';
$ciudadFiltro = isset($_POST['ciudad']) ? trim($_POST['ciudad']) : '';
$tipoFiltro   = isset($_POST['tipo']) ? trim($_POST['tipo']) : '';
$precioRango  = isset($_POST['precio']) ? trim($_POST['precio']) : '';

$precioMin = 0.0;
$precioMax = 100000.0;

if ($precioRango !== '' && strpos($precioRango, ';') !== false) {
    $extremos = explode(';', $precioRango);
    if (isset($extremos[0]) && is_numeric($extremos[0])) {
        $precioMin = (float)$extremos[0];
    }
    if (isset($extremos[1]) && is_numeric($extremos[1])) {
        $precioMax = (float)$extremos[1];
    }
}

$filtrados = [];

foreach ($propiedades as $registro) {
    if ($mostrarTodos) {
        $filtrados[] = $registro;
        continue;
    }

    $precioNum = limpiarPrecio(isset($registro['Precio']) ? $registro['Precio'] : 0);
    $cumplePrecio = ($precioNum >= $precioMin && $precioNum <= $precioMax);

    $ciudadRegistro = trim(isset($registro['Ciudad']) ? $registro['Ciudad'] : '');
    $tipoRegistro   = trim(isset($registro['Tipo']) ? $registro['Tipo'] : '');

    $cumpleCiudad = ($ciudadFiltro === '') || (strcasecmp($ciudadRegistro, $ciudadFiltro) === 0);
    $cumpleTipo   = ($tipoFiltro === '') || (strcasecmp($tipoRegistro, $tipoFiltro) === 0);

    if ($cumplePrecio && $cumpleCiudad && $cumpleTipo) {
        $filtrados[] = $registro;
    }
}

if (empty($filtrados)) {
    echo '<div class="card-panel">No hay registros que coincidan con los filtros seleccionados.</div>';
    exit;
}

foreach ($filtrados as $prop) {
    $direccion = htmlspecialchars(isset($prop['Direccion']) ? $prop['Direccion'] : '', ENT_QUOTES, 'UTF-8');
    $ciudad    = htmlspecialchars(isset($prop['Ciudad']) ? $prop['Ciudad'] : '', ENT_QUOTES, 'UTF-8');
    $telefono  = htmlspecialchars(isset($prop['Telefono']) ? $prop['Telefono'] : '', ENT_QUOTES, 'UTF-8');
    $cp        = htmlspecialchars(isset($prop['Codigo_Postal']) ? $prop['Codigo_Postal'] : '', ENT_QUOTES, 'UTF-8');
    $tipo      = htmlspecialchars(isset($prop['Tipo']) ? $prop['Tipo'] : '', ENT_QUOTES, 'UTF-8');
    $precio    = htmlspecialchars(isset($prop['Precio']) ? $prop['Precio'] : '', ENT_QUOTES, 'UTF-8');

    echo '<div class="card horizontal" style="margin-bottom: 15px;">';
    echo '  <div class="card-stacked">';
    echo '    <div class="card-content">';
    echo '      <p><strong>Direccion:</strong> ' . $direccion . '</p>';
    echo '      <p><strong>Ciudad:</strong> ' . $ciudad . '</p>';
    echo '      <p><strong>Telefono:</strong> ' . $telefono . '</p>';
    echo '      <p><strong>Codigo Postal:</strong> ' . $cp . '</p>';
    echo '      <p><strong>Tipo:</strong> ' . $tipo . '</p>';
    echo '      <p><strong>Precio:</strong> ' . $precio . '</p>';
    echo '    </div>';
    echo '  </div>';
    echo '</div>';
}