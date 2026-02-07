<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id', 'subject', 'description', 'severity', 'status', 'client_id',
        'device', 'locked_by', 'locked_at', 'ticket_number', 'recurring',
        'resolution_notes', 'closed_at', 'closed_by'
    ];

    protected $casts = [
        'locked_at' => 'datetime',
        'closed_at' => 'datetime',
        'recurring' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function lockedBy()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function alertHistories()
    {
        return $this->hasMany(AlertHistory::class);
    }

    /**
     * Get the SLA deadline based on the client's policy.
     */
    public function getSlaDeadline()
    {
        if (!$this->client || !$this->client->slaPolicy) {
            return $this->created_at->addHour(); // Default 1 hour if no policy
        }

        return $this->created_at->addMinutes($this->client->slaPolicy->response_time_minutes);
    }

    /**
     * Check if the alert has breached its SLA.
     */
    public function isOverdue(): bool
    {
        if ($this->status === 'closed' || $this->status === 'resolved') {
            return false;
        }
        return now()->greaterThan($this->getSlaDeadline());
    }

    /**
     * Get time remaining until SLA breach or time overdue.
     */
    public function timeUntilSla(): string
    {
        $deadline = $this->getSlaDeadline();
        return $deadline->diffForHumans(['parts' => 2]);
    }

    /**
     * Get the SLA status for UI styling.
     */
    public function getSlaStatus(): string
    {
        if ($this->status === 'closed' || $this->status === 'resolved') {
            return 'ok';
        }

        if ($this->isOverdue()) {
            return 'critical'; // Blinking red/orange
        }

        $deadline = $this->getSlaDeadline();
        $warningThreshold = $deadline->copy()->subMinutes(15); // Warn if < 15 mins left

        if (now()->greaterThan($warningThreshold)) {
            return 'warning'; // Yellow/Orange
        }

        return 'ok'; // Green/Blue
    }
}
