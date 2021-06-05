<?php

namespace App\Services;

interface InboxService
{
    public function getThreads($userId);

    public function store($userId, $to, $subject, $message);

    public function update($id, $to, $subject, $message);
}
