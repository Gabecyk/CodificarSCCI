<?php

namespace App\Enums;

enum TicketStatus: string
{
    case OPEN = 'open';
    case IN_PROGRESS = 'in_progress';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';

    public function isOpen(): bool
    {
        return in_array($this, [self::OPEN, self::IN_PROGRESS]);
    }
}