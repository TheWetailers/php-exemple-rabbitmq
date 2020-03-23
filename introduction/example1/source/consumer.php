<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Autoloader
require('./vendor/autoload.php');

// Configuration
require('./config.php');

// On initialise le retour
$response = [
    'status' => 'success',
];


try {
    $connection = new AMQPStreamConnection(
        RABBITMQ_HOST,
        RABBITMQ_PORT,
        RABBITMQ_USER,
        RABBITMQ_PASSWORD
    );
    $channel = $connection->channel();
    $channel->queue_declare(RABBITMQ_CHANNEL, false, true, false, false);

    $channel->basic_consume(RABBITMQ_CHANNEL, '', false, true, false, false, function(AMQPMessage $message) use (&$response) {
        $response['message'] = $message->body;
    });

    if ($channel->is_consuming()) {
        $channel->wait();
    }

    $channel->close();
    $connection->close();
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = 'Technical error';
}

// On affiche le resultat
echo json_encode($response);
