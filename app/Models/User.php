<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// A profile record only. This assessment does not implement authentication.
class User extends Model
{
    protected $fillable = ['username', 'full_name', 'email'];
}
