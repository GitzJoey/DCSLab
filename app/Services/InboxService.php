<?php

namespace App\Services;

interface InboxService
{
    public function getThreads($userId);

    public function getThread($id);

    public function store($userId, $to, $subject, $message);

    public function update($threadId, $userId, $to, $subject, $message);
}
