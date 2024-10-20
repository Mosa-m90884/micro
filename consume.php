<?php


require __DIR__ . '/vendor/autoload.php'; // تأكد من وجود هذا السطر

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();
$channel->queue_declare('post_created', false, true, false, false, false, []);
$channel->basic_consume('post_created', '', false, true, false, false, $callback);

$channel->queue_declare('comment_created', false, true, false, false, false, []);


while ($channel->is_consuming()) {
    $channel->wait();
}
$callback = function ($msg) {
    $comment = json_decode($msg->body, true);
    // هنا يمكنك إضافة كود لمعالجة التعليق أو تخزينه في قاعدة بيانات أخرى
    echo "Received comment: ", $comment['content'], "\n";
};

$channel->basic_consume('comment_created', '', false, true, false, false, $callback);
$callback = function ($msg) {
    $post = json_decode($msg->body, true);
    // هنا يمكنك إضافة كود لمعالجة المشاركة أو تخزينها في قاعدة بيانات أخرى
    echo "Received post: ", $post['title'], "\n";
};

$channel->close();
$connection->close();
