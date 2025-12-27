<?php

/*
 * Rate limiting simple en PHP puro (archivo plano por IP/clave).
 * No requiere extensiones ni servicios externos. Para producción usar Redis/WAF si es posible.
 */

function rateLimit($key, $limit = 60, $windowSeconds = 60) {
    $dir = __DIR__ . '/../storage/ratelimit';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $file = $dir . '/' . md5($key) . '.json';
    $now = time();
    $windowStart = $now - $windowSeconds;

    $hits = [];
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        if (is_array($data)) {
            $hits = $data;
        }
    }

    // Conservar solo hits dentro de la ventana
    $hits = array_filter($hits, function ($ts) use ($windowStart) {
        return $ts >= $windowStart;
    });

    if (count($hits) >= $limit) {
        http_response_code(429);
        die(json_encode(['error' => 'Demasiadas solicitudes, intenta más tarde']));
    }

    $hits[] = $now;
    file_put_contents($file, json_encode(array_values($hits)));
    return true;
}

?>
