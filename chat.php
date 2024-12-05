<?php
// Cargar variables de entorno desde el archivo .env
require_once __DIR__ . '/vendor/autoload.php'; // Asegúrate de que esta ruta es correcta

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Obtener la clave de la API desde la variable de entorno
$apiKey = $_ENV['OPENAI_API_KEY'];

// Verificar que la clave de API esté configurada
if (empty($apiKey)) {
    echo "Error: La clave de API no está configurada correctamente.";
    exit;
}

// Obtener el mensaje del usuario
$message = $_POST['message'] ?? '';
$message=$message.' en MasGPS';

// Verificar si el mensaje está vacío
if (empty($message)) {
    echo "Por favor, escribe un mensaje.";
    exit;
}

// Filtrar preguntas que no sean sobre MasGPS
if (stripos($message, 'MasGPS') === false) {
    echo "Solo puedo responder preguntas relacionadas con la plataforma MasGPS.";
    exit;
}

// Endpoint de OpenAI
$url = "https://api.openai.com/v1/chat/completions";

// Configurar la solicitud
$data = [
    "model" => "gpt-3.5-turbo",
    "messages" => [
        ["role" => "system", "content" => "Eres un asistente de navixy.com que solo responde preguntas relacionadas con la plataforma MasGPS. Siempre usa 'MasGPS' en las respuestas, incluso si el usuario menciona 'Navixy'. Siempre responde  en formato html. Si la respuesta incluye varios item, muestralo en forma de lista"],
        ["role" => "user", "content" => $message],
    ]
];
$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\nAuthorization: Bearer $apiKey\r\n",
        'method'  => 'POST',
        'content' => json_encode($data),
    ],
];
$context = stream_context_create($options);

// Realizar la solicitud
$response = file_get_contents($url, false, $context);
if ($response === FALSE) {
    echo "Error al conectar con ChatGPT.";
    exit;
}

// Procesar la respuesta
$result = json_decode($response, true);
$reply = $result['choices'][0]['message']['content'] ?? "Lo siento, no puedo responder en este momento.";

// Reemplazar "Navixy" con "MasGPS" en la respuesta
$reply = str_ireplace("Navixy", "MasGPS", $reply);

// Enviar la respuesta al frontend
echo $reply;
