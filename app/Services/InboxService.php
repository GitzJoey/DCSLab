<?php

namespace App\Services;

use Cmgmyr\Messenger\Models\Message;
use Cmgmyr\Messenger\Models\Thread;

interface InboxService
{
    public function getThreads(int $userId);

    public function getThread(int $id);

    public function store(int $userId, array $to, string $subject, string $message);

    public function update(int $threadId, int $userId, array $to, string $subject, string $message);
}
