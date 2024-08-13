<?php
require 'vendor/autoload.php';

use setasign\Fpdi\Fpdi;
use Intervention\Image\ImageManager;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

// Configura las rutas
$nombredelhost = "https://devssoftbusiness.com/dev";
$physical_addrs = "C:/wamp64/www/bambolinaprinter"; // Ajusta esta ruta según tu configuración
$tipo = $_GET['tipo'];
$pdfUrl = $nombredelhost . '/archivos/' . $_GET['tipo'] . '/' . $_GET['pdf'];
$localFilePath = $physical_addrs . '/archivos/' . $_GET['tipo'] . '/' . $_GET['pdf'];
$imagePath = $physical_addrs . '/archivos/' . $_GET['tipo'] . '/' . pathinfo($_GET['pdf'], PATHINFO_FILENAME) . '.png';

// Descargar el archivo PDF
$ch = curl_init($pdfUrl);
$fp = fopen($localFilePath, 'wb');
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_FAILONERROR, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_exec($ch);
if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
}
curl_close($ch);
fclose($fp);

if (isset($error_msg)) {
    if (file_exists($localFilePath)) {
        unlink($localFilePath);
    }
    die("Error al descargar el archivo: $error_msg");
}

// Crear una instancia del manejador de imágenes especificando el driver
$manager = new ImageManager(['driver' => 'gd']);

// Convertir PDF a imagen usando setasign/fpdi y Intervention Image
$pdf = new Fpdi();
$pdf->AddPage();
$pdf->setSourceFile($localFilePath);
$page = $pdf->importPage(1);
$pdf->useTemplate($page);

// Guardar el PDF en una variable
$pdfContent = $pdf->Output('', 'S');

// Crear una imagen a partir del contenido del PDF
$image = $manager->make($pdfContent);
$image->save($imagePath);

// Imprimir la imagen
try {
    $printerPort = 'LR2000'; // Usa el puerto exacto de tu impresora
    $connector = new WindowsPrintConnector($printerPort);
    $printer = new Printer($connector);

    // Imprimir la imagen
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->graphics($imagePath);
    $printer->cut();
    $printer->close();

    echo "Impresión realizada.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
