<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ResponsibleRepositoryInterface
{
    public function paginate(): LengthAwarePaginator;
}
