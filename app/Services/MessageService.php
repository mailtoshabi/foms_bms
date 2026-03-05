<?php

namespace App\Services;

use App\Models\Message;

class MessageService
{
    public function send($data)
    {
        return Message::create($data);
    }
}
