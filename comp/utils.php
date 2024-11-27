<?php
function convertirMoneda($precio, $monedaActual, $monedaDestino) {
    if ($monedaActual === $monedaDestino) {
        return $precio;
    }

    $apiURL = "https://v6.exchangerate-api.com/v6/12ba185e3240700d65b485e2/pair/$monedaActual/$monedaDestino/1";

    $iniciarCURL = curl_init($apiURL);
    curl_setopt($iniciarCURL, CURLOPT_RETURNTRANSFER, true);
    $respuesta = curl_exec($iniciarCURL);

    if (curl_errno($iniciarCURL)) {
        echo "Error al obtener la tasa de cambio: " . curl_error($iniciarCURL);
        return $precio; 
    }

    curl_close($iniciarCURL);
    $datos = json_decode($respuesta, true);

    if (isset($datos['conversion_rate'])) {
        return $precio * $datos['conversion_rate'];
    }

    return $precio;
}
?>
