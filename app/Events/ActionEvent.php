<?php
namespace App\Events;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use App\Models\Product;

class ActionEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $batchData;

    /**
     * Create a new event instance.
     *
     * @author Author
     *
     * @return void
     */
    public function __construct($batchData)
    {
        $this->batchData = $batchData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @author Author
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('action-channel-one');
    }

}