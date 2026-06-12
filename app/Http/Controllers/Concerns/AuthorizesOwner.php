<?php

namespace App\Http\Controllers\Concerns;

trait AuthorizesOwner
{
    protected function authorizeOwner(?object $model, string $field = 'user_id'): void
    {
        abort_unless($model && (int) $model->{$field} === (int) auth()->id(), 403);
    }
}
