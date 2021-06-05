<?php

namespace App\Services\Impls;

use Exception;
use Carbon\Carbon;
use App\Services\InboxService;
use Cmgmyr\Messenger\Models\Message;
use Cmgmyr\Messenger\Models\Participant;
use Cmgmyr\Messenger\Models\Thread;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InboxServiceImpl implements InboxService
{
    public function getThreads($userId)
    {
        $t = Message::with('thread', 'participants', 'participants.user')->where('user_id', '=', $userId)->get();

        $tt = $t->map(function ($item) {
            return [
                'id' => $item->id,
                'subject' => $item->thread->subject,
                'body' => $item->body,
                'participants' => $this->participantString($item->participants),
                'created_at' => $item->created_at->diffForHumans(),
                'updated_at' => $item->updated_at->diffForHumans(),
            ];
        });

        return $tt;
    }

    private function participantString($participants)
    {
        $result = [];

        foreach ($participants as $p)
        {
            array_push($result, $p->user->name);
        }

        return implode(", ", $result);
    }

    public function store($userId, $to, $subject, $message)
    {
        DB::beginTransaction();

        $r = 0;
        try {
            $thread = Thread::create([
                'subject' => $subject,
            ]);
            $r++;

            Message::create([
                'thread_id' => $thread->id,
                'user_id' => $userId,
                'body' => $message,
            ]);
            $r++;

            Participant::create([
                'thread_id' => $thread->id,
                'user_id' => $userId,
                'last_read' => new Carbon,
            ]);
            $r++;

            $thread->addParticipant($to);
            $r++;

            DB::commit();

            return $r;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function update($id, $to, $subject, $message)
    {

    }
}
