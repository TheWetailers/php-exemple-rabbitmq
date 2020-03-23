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
    $channel->queue_declare("", false, false, false, false);

    switch ($_POST['dest']) {
        case 'left':
            $dest = 'left';
            $type = 'direct';
            break;
        case 'right':
            $dest = 'right';
            $type = 'direct';
            break;
        case 'all':
            $dest = 'to_all';
            $type = 'fanout';
            break;
        default:
            $dests = ['left', 'right'];
            $dest = $dests[array_rand($dests)];
            $type = 'direct';
    }

    $channel->exchange_declare($dest, $type, false, false, false);

    $response['dest'] = $dest;
    $response['type'] = $type;

    $message = new AMQPMessage($_POST['message']);
    $channel->basic_publish($message, $dest);

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
