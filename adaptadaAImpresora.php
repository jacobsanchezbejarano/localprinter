<?php
require 'vendor/autoload.php'; // Asegúrate de tener Composer autoloading

use Mike42\Escpos\EscposImage;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

function extraer_mes($numero)
{
    return [
        '01' => 'Enero',
        '02' => 'Febrero',
        '03' => 'Marzo',
        '04' => 'Abril',
        '05' => 'Mayo',
        '06' => 'Junio',
        '07' => 'Julio',
        '08' => 'Agosto',
        '09' => 'Septiembre',
        '10' => 'Octubre',
        '11' => 'Noviembre',
        '12' => 'Diciembre',
    ][$numero];
}

function dia_semana($dia)
{
    return [
        '1' => 'Lunes',
        '2' => 'Martes',
        '3' => 'Miércoles',
        '4' => 'Jueves',
        '5' => 'Viernes',
        '6' => 'Sábado',
        '7' => 'Domingo',
    ][$dia];
}

function escribir_log($mensaje, $archivo = "printer.log")
{
    try {
        $handle = fopen($archivo, "a");
        if ($handle) {
            fwrite($handle, date('Y-m-d H:i:s') . " - " . $mensaje . "\n");
            fclose($handle);
        } else {
            throw new Exception("No se pudo abrir el archivo de log: $archivo");
        }
    } catch (Exception $e) {
        // Manejar el error, por ejemplo, enviar un correo electrónico
        echo "Error al escribir en el log: " . $e->getMessage();
    }
}

$host = $_GET['host'];
$pdfname = $_GET['pdfname'];

$datos_pago_print = time() . ' | ' . $_GET['datos']['pagos_id'] . ' | ' . $_SERVER['REMOTE_ADDR'] . '\n';
escribir_log($datos_pago_print);

escribir_log(print_r($_GET, true) . '\n');

$data = [];

if (isset($_GET['datos'])) $datos = $_GET['datos'];
if (isset($_GET['detalle_paquetes']))  $detalle_paquetes = $_GET['detalle_paquetes'];
if (isset($_GET['fechas_inicio']))  $fechas_inicio = $_GET['fechas_inicio'];
if (isset($_GET['data']))  $data = $_GET['data'];
if (isset($_GET['metodos_usados']))  $metodos_usados = $_GET['metodos_usados'];


foreach ($_GET as $get) {
    // echo "<br>";
    // var_dump($get);
    // echo "<br>";
}

// exit;

// Conectar a la impresora
$printerPort = 'LR2000'; // Usa el puerto exacto de tu impresora
$connector = new WindowsPrintConnector($printerPort);
$printer = new Printer($connector);

try {

    echo '
    <script type="text/javascript">
        window.onload = function() {
            // Espera 3 segundos antes de cerrar la ventana para asegurar que todo se cargue
            setTimeout(function() {
                window.open("' . $host . '/archivos/recibos/' . $pdfname . '");
                window.close();
            }, 3000); // 3000 milisegundos = 3 segundos
        };
    </script>
    ';
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    // Cerrar la conexión
    $printer->close();
}
