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
    // Datos principales
    $printer->setJustification(Printer::JUSTIFY_CENTER);

    // $success_icon = EscposImage::load('images/success-icon.jpg', false);
    // $printer->bitImage($success_icon);
    // $logo = EscposImage::load('images/Logo-Bambolina-Nuevo.jpg', false);
    // $printer->bitImage($logo);

    $printer->text("Bambolina - Escuela de Danza\n");
    $printer->text("Recibo de Pago\n\n");
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->text("Fecha: " . date("d/m/Y H:i:s", strtotime($datos['pagos_fecha'])) . "\n");
    $printer->text("Alumno: " . $datos['clientes_nombre'] . "\n\n");

    // Métodos de pago
    $printer->text("Método de Pago:\n");
    foreach ($metodos_usados as $metodo) {
        $printer->text($metodo['metodospago_nombre'] . ": Bs. " . $metodo['pagos_metodospago_monto'] . "\n");
    }
    $printer->text("\n");

    // Detalles de paquetes
    $printer->text("Detalles:\n");
    foreach ($detalle_paquetes as $paquete) {
        $printer->text($paquete['paquetes_nombre'] . " - Bs. " . $paquete['paquetes_precio'] . "\n");
    }
    $printer->text("\nTotal Pagado: Bs. " . $datos['pagos_monto'] . "\n");

    // Fechas de inicio
    $printer->text("\nInicio de Clases:\n");
    foreach ($fechas_inicio as $paquete) {
        $printer->text($paquete['paquetes_nombre'] . "\n");
        $printer->text("Inicio: " . date("d/m/Y", strtotime($paquete['paquetes_clientes_fechainicio'])) . " - ");
        $printer->text("Fin: " . date("d/m/Y", strtotime($paquete['paquetes_clientes_fechavencimiento'])) . "\n");
    }

    // Horarios
    if (count($data) > 0) {
        $printer->text("\nHorario:\n");
        $detectar_duplicados = [];
        foreach ($data as $dia) {
            $printer->text(dia_semana($dia['dia']) . "\n");
            foreach ($dia as $horario) {
                if (!is_array($horario)) continue;
                foreach ($horario as $clase) {
                    if (!in_array($clase, $detectar_duplicados)) {
                        $detectar_duplicados[] = $clase;
                        $printer->text($clase['clases_nombre'] . " - " . $clase['hora_inicio'] . " a " . $clase['hora_fin'] . "\n");
                    }
                }
            }
        }
    }

    // Footer
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text("\nGracias por tu pago. ¡Disfruta tus clases!\n");
    $printer->text("#ThePinkHouse\n");

    // Corta el papel
    $printer->cut();
    echo '
    <script type="text/javascript">
        window.onload = function() {
            // Espera 3 segundos antes de cerrar la ventana para asegurar que todo se cargue
            setTimeout(function() {
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
