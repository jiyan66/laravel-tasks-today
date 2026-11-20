<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['title', 'status', 'task_date'];

    protected function casts(): array
    {
        return ['task_date' => 'date'];
    }

    // A reusable Eloquent query scope; the database performs the date filter.
    public function scopeDueOn(Builder $query, string $date): Builder
    {
        return $query->whereDate('task_date', $date);
    }
}
