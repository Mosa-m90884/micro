<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    //
    public function store(Request $request)
    {
        $post = Post::create($request->all());
        // نشر حدث إلى RabbitMQ
        $this->publishPostCreated($post);
        return response()->json($post, 201);
    }

    public function index()
    {
        return Post::with('user')->get(); // عرض المشاركات مع معلومات المستخدم
    }

// طريقة لنشر الأحداث إلى RabbitMQ
    private function publishPostCreated($post)
    {
        $connection = new \PhpAmqpLib\Connection\AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();
        $channel->queue_declare('post_created', false, true, false, false, false, []);

        $messageBody = json_encode($post);
        $msg = new \PhpAmqpLib\Message\AMQPMessage($messageBody);
        $channel->basic_publish($msg, '', 'post_created');

        $channel->close();
        $connection->close();
    }
}
