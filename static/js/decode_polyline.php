<?php
/**
 * Decoder funkcji dla zakodowanych danych polyline od OpenRouteService API
 * 
 * API zwraca dane geometrii w formacie zakodowanego polyline.
 * Ten skrypt dekoduje te dane i zwraca punkty w formacie [lat,lon].
 */

// Funkcja dekodowania polyline
function decodePolyline($encoded) {
    $length = strlen($encoded);
    $index = 0;
    $points = [];
    $lat = 0;
    $lng = 0;

    while ($index < $length) {
        // Decode latitude
        $result = 1;
        $shift = 0;
        $b = null;
        do {
            $b = ord(substr($encoded, $index++, 1)) - 63;
            $result += ($b & 0x1f) << $shift;
            $shift += 5;
        } while ($b >= 0x20);
        $dlat = (($result & 1) ? ~($result >> 1) : ($result >> 1));
        $lat += $dlat;

        // Decode longitude
        $result = 1;
        $shift = 0;
        do {
            $b = ord(substr($encoded, $index++, 1)) - 63;
            $result += ($b & 0x1f) << $shift;
            $shift += 5;
        } while ($b >= 0x20);
        $dlng = (($result & 1) ? ~($result >> 1) : ($result >> 1));
        $lng += $dlng;

        $points[] = [$lat / 100000.0, $lng / 100000.0];
    }

    return $points;
}

// Pobierz zakodowany polyline z żądania GET
$polyline = isset($_GET['polyline']) ? $_GET['polyline'] : '';

if (empty($polyline)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No polyline data provided']);
    exit;
}

// Dekoduj polyline
$points = decodePolyline($polyline);

// Zwróć zdekodowane punkty jako JSON
header('Content-Type: application/json');
echo json_encode(['points' => $points]);