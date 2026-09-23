<?php

namespace App\Actions\Responsible;

use App\Repositories\Contracts\ResponsibleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListResponsibleAction
{
    public function __construct(
        private readonly ResponsibleRepositoryInterface $responsibles,
    ) {}

    public function execute(): LengthAwarePaginator
    {
        return $this->responsibles->paginate();
    }
}