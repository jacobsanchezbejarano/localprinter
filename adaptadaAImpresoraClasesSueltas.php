<?php
require 'vendor/autoload.php'; // Composer autoload

use Mike42\Escpos\EscposImage;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

// Helper functions for date and time handling
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

if (isset($_GET['datos']) && isset($_GET['detalle_paquetes']) && isset($_GET['data']) && isset($_GET['metodos_usados'])) {
    $datos = $_GET['datos'];
    $detalle_paquetes = $_GET['detalle_paquetes'];
    $data = $_GET['data'];
    $metodos_usados = $_GET['metodos_usados'];
}

// Process customer information
$cliente_nombre = $datos['compradores_eventuales_nombre'];
$cliente_telefono = '';
if (!empty($datos['clientes_telefono'])) {
    $telefonos = explode(',', $datos['clientes_telefono']);
    $cliente_telefono = $telefonos[0] ?? ''; // Use first number if available
}

$monto_pagado = $datos['pagos_clasessueltas_monto'];
$fecha_hora_pago = strtotime($datos['pagos_clasessueltas_fecha']);
$fecha_recibo = date("d/m/Y", $fecha_hora_pago);
$hora_recibo = date("H:i:s", $fecha_hora_pago);

// Prepare payment methods string for the receipt
$metodo_pago = "";
foreach ($metodos_usados as $metodo) {
    $metodo_pago .= $metodo['metodospago_nombre'] . ": Bs. " . $metodo['pagos_metodospago_monto'] . "\n";
}

// Initialize printer connection
$printerPort = 'LR2000'; // Update with your printer's port
$connector = new WindowsPrintConnector($printerPort);
$printer = new Printer($connector);

try {
    // Set receipt formatting and logo
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text("Bambolina - Escuela de Danza\n");
    $printer->text("Recibo de Pago\n\n");

    // Print customer and payment details
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->text("Fecha: " . $fecha_recibo . " " . $hora_recibo . "\n");
    $printer->text("Alumno: " . $cliente_nombre . "\n\n");

    // Print payment methods
    $printer->text("Método de Pago:\n");
    $printer->text($metodo_pago . "\n");

    // Print class details
    $printer->text("Detalles de la Clase:\n");
    foreach ($detalle_paquetes as $paquete) {
        $printer->text($paquete['clases_nombre'] . " - " . dia_semana($paquete['clases_horarios_dia']) . "\n");
        $printer->text("Cantidad: " . $paquete['clases_sueltas_cantidad'] . "\n");
        $printer->text("Precio: Bs. " . $paquete['clases_sueltas_precio'] . "\n");
        $printer->text("Subtotal: Bs. " . number_format($paquete['clases_sueltas_cantidad'] * $paquete['clases_sueltas_precio'], 2) . "\n\n");
    }

    // Print total amount paid
    $printer->text("Total Pagado: Bs. " . $monto_pagado . "\n\n");

    if (count($data) > 0) {
        $printer->text("Horario:\n");
        $detectar_duplicados = [];
        foreach ($data as $dia) {
            $printer->text($dia['dia'] . "\n");
            foreach ($dia as $horario) {
                if (!is_array($horario)) continue;
                foreach ($horario as $clase) {
                    $detectar_duplicados[] = $clase;
                    $printer->text($clase['clases_nombre'] . " - " . $clase['hora_inicio'] . " a " . $clase['hora_fin'] . "\n");
                }
            }
        }
    }

    // Footer message
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text("\nGracias por tu pago. ¡Disfruta tus clases!\n");
    $printer->text("#ThePinkHouse\n");

    // Cut the receipt
    $printer->cut();

    // Auto-close the window after printing
    echo '
    <script type="text/javascript">
        window.onload = function() {
            setTimeout(function() {
                window.close();
            }, 3000);
        };
    </script>';
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    // Close the printer connection
    $printer->close();
}
