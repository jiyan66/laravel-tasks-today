<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\View\View;
class PublicProfileController extends Controller
{
    public function show(): View
    {
        // Registration must not change whose public demo profile is displayed.
        $user = User::query()->where('username', config('tasks.profile_username'))->first();
        return view('profile', compact('user'));
    }
}
