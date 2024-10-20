<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
// إنشاء تعليق جديد
    public function store(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'content' => 'required|string',
        ]);
        $comment = Comment::create($request->all());
// نشر حدث إلى RabbitMQ
        $this->publishCommentCreated($comment);
        return response()->json($comment, 201);
    }

// عرض جميع التعليقات
    public function index()
    {
        return response()->json(Comment::with('post')->get(), 200);
    }

// نشر الأحداث إلى RabbitMQ
    private function publishCommentCreated($comment)
    {
        $connection = new \PhpAmqpLib\Connection\AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();
        $channel->queue_declare('comment_created', false, true, false, false, false, []);
        $messageBody = json_encode($comment);
        $msg = new \PhpAmqpLib\Message\AMQPMessage($messageBody);
        $channel->basic_publish($msg, '', 'comment_created');
        $channel->close();
        $connection->close();
    }
}
