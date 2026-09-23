<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Responsible extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'responsible_id');
    }

    public function openTickets(): HasMany
    {
        return $this->tickets()->whereIn('status', [
            TicketStatus::OPEN->value,
            TicketStatus::IN_PROGRESS->value,
        ]);
    }

    public function openTicketsCount(): int
    {
        return $this->openTickets()->count();
    }
}