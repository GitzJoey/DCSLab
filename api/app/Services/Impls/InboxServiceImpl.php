<?php

namespace App\Services\Impls;

use App\Services\InboxService;
use Carbon\Carbon;
use Cmgmyr\Messenger\Models\Message;
use Cmgmyr\Messenger\Models\Participant;
use Cmgmyr\Messenger\Models\Thread;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InboxServiceImpl implements InboxService
{
    public function __construct()
    {
    }

    public function getThreads(int $userId)
    {
        $t = Thread::with('participants')
            ->whereHas('participants', function ($p) use ($userId) {
                $p->where('user_id', '=', $userId);
            })->get();

        return $t;
    }

    public function getThread(int $id)
    {
        $m = Message::with('user')->where('thread_id', '=', $id)->get();

        return $m;
    }

    public function store(int $userId, array $to, string $subject, string $message)
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
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
        }
    }

    public function update(int $treadId, int $userId, array $to, string $subject, string $message)
    {
        DB::beginTransaction();

        try {
            $thread = Thread::findOrFail($treadId);

            $thread->activateAllParticipants();

            Message::create([
                'thread_id' => $thread->id,
                'user_id' => $userId,
                'body' => $message,
            ]);

            $participant = Participant::firstOrCreate([
                'thread_id' => $thread->id,
                'user_id' => $userId,
            ]);

            $participant->last_read = new Carbon;
            $participant->save();

            $thread->addParticipant($to);

            DB::commit();

            return 0;
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
        }
    }
}
