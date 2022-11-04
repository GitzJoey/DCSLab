<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Vinkla\Hashids\Facades\Hashids;

class PusherPrivateMessageEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $fromName;

    private int $toId;

    public string $toName;

    private string $toEmail;

    public string $message;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(string $fromName, string $toEmail, string $message)
    {
        $this->fromName = $fromName;

        $toUser = User::where('email', '=', $toEmail)->firstOr(function () {
            return '';
        });
        $this->toId = empty($toUser) ? '' : $toUser->id;
        $this->toEmail = empty($toUser) ? '' : $toUser->email;
        $this->toName = empty($toUser) ? '' : $toUser->name;

        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-'.Hashids::encode($this->toId));
    }

    public function broadcastAs()
    {
        return 'event-private-pusher';
    }
}
