<?php
$nombredelhost = "";
$physical_addrs = "";
$tipo = $_GET['tipo'];
$pdfUrl = $nombredelhost . '/archivos/recibos/' . $_GET['pdf'];
$localFilePath = $physical_addrs . '/archivos/' . $_GET['tipo'] . '/' . $_GET['pdf'];

$ch = curl_init($pdfUrl);
$fp = fopen($localFilePath, 'wb');
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_FAILONERROR, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'); // User agent
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

if (file_exists($localFilePath)) {
    // Nombre de la impresora configurada en Windows
    $printerName = 'Nombre de tu impresora por default'; // Impresora default

    if ($_GET['tipo'] == 'recibos') {
        $printerName = 'Nombre de tu impresora para recibos';
    } elseif ($_GET['tipo'] == 'contratos') {
        $printerName = 'Nombre de tu impresora para contratos';
    }

    $command = "powershell.exe -Command \"Start-Process -FilePath '$localFilePath' -ArgumentList '/p /h $printerName' -NoNewWindow -Wait\"";
    exec($command, $output, $return_var);

    if ($return_var === 0) {
        header("Location: $pdfUrl");
    } else {
        echo "Hubo un error al enviar el documento a la impresora.";
        print_r($output);
    }
} else {
    echo "Error al descargar el archivo.";
}
