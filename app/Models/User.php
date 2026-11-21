<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable
{
    use Notifiable;
    protected $fillable = ['username', 'full_name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array
    {
        return ['password' => 'hashed', 'email_verified_at' => 'datetime'];
    }
    public function getNameAttribute(): string
    {
        return (string) $this->full_name;
    }
}
