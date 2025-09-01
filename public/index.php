<?php
session_start();

require '../vendor/autoload.php';

function getEnvVar($key) {
    if (!isset($_ENV[$key])) {
        throw new Exception("Variável de ambiente $key não definida.");
    }
    return $_ENV[$key];
}

function errorAndExit($msg, $httpCode = 400) {
    http_response_code($httpCode);
    exit($msg);
}

try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    $discord_client_id = getEnvVar('DISCORD_CLIENT_ID');
    $discord_client_secret = getEnvVar('DISCORD_CLIENT_SECRET');
    $redirect_uri = getEnvVar('DISCORD_REDIRECT_URI');

    $db_host = getEnvVar('DB_HOST');
    $db_name = getEnvVar('DB_DATABASE');
    $db_user = getEnvVar('DB_USERNAME');
    $db_pass = getEnvVar('DB_PASSWORD');

    $db = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($db->connect_error) {
        throw new Exception('Erro ao conectar ao banco de dados: ' . $db->connect_error);
    }

    $redis_host = getEnvVar('REDIS_HOST');
    $redis_port = getEnvVar('REDIS_PORT');
    $redis_password = getenv('REDIS_PASSWORD') ?: null;
    $redis_config = [
        'scheme' => 'tcp',
        'host'   => $redis_host,
        'port'   => $redis_port
    ];
    if (!empty($redis_password)) {
        $redis_config['password'] = $redis_password;
    }
    $redis = new Predis\Client($redis_config);

    $code = $_GET['code'] ?? null;
    $state = $_GET['state'] ?? null;
    if (!$code || !$state) errorAndExit('Faltando parâmetros.');

    $stmt = $db->prepare("SELECT unique_id FROM discord_link_states WHERE state=? AND used=0");
    if (!$stmt) throw new Exception('Erro ao preparar statement: ' . $db->error);
    $stmt->bind_param('s', $state);
    $stmt->execute();
    $stmt->bind_result($unique_id);
    $stmt->fetch();
    $stmt->close();
    if (empty($unique_id)) errorAndExit('Vinculação não encontrada ou já utilizada.');

    $client = new \GuzzleHttp\Client();
    try {
        $res = $client->post('https://discord.com/api/oauth2/token', [
            'form_params' => [
                'client_id' => $discord_client_id,
                'client_secret' => $discord_client_secret,
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $redirect_uri,
            ]
        ]);
        $token = json_decode($res->getBody(), true);
    } catch (Exception $e) {
        errorAndExit('Erro ao obter token do Discord: ' . $e->getMessage());
    }

    if (empty($token['access_token'])) {
        errorAndExit('Token de acesso não recebido do Discord.');
    }

    try {
        $res = $client->get('https://discord.com/api/users/@me', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token['access_token']
            ]
        ]);
        $user = json_decode($res->getBody(), true);
    } catch (Exception $e) {
        errorAndExit('Erro ao obter usuário do Discord: ' . $e->getMessage());
    }

    $message = $unique_id . ':' . $token['access_token'];
    $redis->publish('discord-integration-channel', $message);

    $stmt = $db->prepare("UPDATE discord_link_states SET used=1 WHERE state=?");
    if (!$stmt) throw new Exception('Erro ao preparar update: ' . $db->error);
    $stmt->bind_param('s', $state);
    $stmt->execute();
    $stmt->close();

    $db->close();

    header('Location: /success.html');
    exit;
} catch (Exception $e) {
    errorAndExit('Erro: ' . $e->getMessage(), 500);
}
