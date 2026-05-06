<?php

namespace App\Events;

use App\Models\AttendanceLog;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceProcessed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $log;

    public function __construct(AttendanceLog $log)
    {
        $this->log = $log->load(['student.user', 'student.schoolClass']);
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('attendance-tracker'),
        ];
    }

    public function broadcastAs()
    {
        return 'attendance.updated';
    }
}