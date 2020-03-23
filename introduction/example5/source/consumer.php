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
    $channel->exchange_declare('to_all', 'fanout', false, false, false);
    $channel->exchange_declare($_POST['queue_name'], 'direct', false, false, false);
    $channel->queue_declare($_POST['queue_name'], false, false, false, false);
    $channel->queue_bind($_POST['queue_name'], 'to_all');
    $channel->queue_bind($_POST['queue_name'], $_POST['queue_name']);

    $channel->basic_consume($_POST['queue_name'], '', false, false, false, false, function(AMQPMessage $message) use (&$response) {
        foreach ($_POST['ignore'] as $word) {
            if (false !== strpos($message->body, $word)) {
                throw new Exception();
            }
        }

        $response['message'] = $message->body;
        // Un traitement long qui peut s'arrêter plus de multiple raison (limite de mémoire atteinte, données invalides, …).
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
    });

    if ($channel->is_consuming()) {
        $channel->wait();
    }

    $channel->close();
    $connection->close();
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = 'Technical error.'.$e->getMessage();
}

// On affiche le resultat
echo json_encode($response);
