<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Autoloader
require('./vendor/autoload.php');

// Configuration
require('./config.php');

// Exception de validation
require('./ValidationException.php');

// On initialise le retour
$response = [
    'status' => 'success',
];


try {
    if (empty($_POST['message'])) {
        throw new ValidationException('Le message ne doit pas être vide.');
    }

    $connection = new AMQPStreamConnection(
        RABBITMQ_HOST,
        RABBITMQ_PORT,
        RABBITMQ_USER,
        RABBITMQ_PASSWORD
    );
    $channel = $connection->channel();
    $channel->queue_declare(RABBITMQ_CHANNEL, false, true, false, false);

    $message = new AMQPMessage($_POST['message']);
    $channel->basic_publish($message, '', RABBITMQ_CHANNEL);

    $channel->close();
    $connection->close();
} catch (ValidationException $e) {
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = 'Technical error.'.$e->getMessage();
}

// On affiche le resultat
echo json_encode($response);
