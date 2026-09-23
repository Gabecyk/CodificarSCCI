<?php

namespace App\Repositories\Eloquent;

use App\Models\Responsible;
use App\Repositories\Contracts\ResponsibleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ResponsibleRepository implements ResponsibleRepositoryInterface
{
    public function paginate(): LengthAwarePaginator
    {
        return Responsible::query()
            ->latest('created_at')
            ->paginate(15);
    }
}
           
