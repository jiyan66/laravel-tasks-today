<?php
namespace App\Console\Commands;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
class SetTaskPassword extends Command
{
    protected $signature = 'tsa4:set-password {username : Existing account username}';
    protected $description = 'Set a password for an existing TSA3 account without creating another user';
    public function handle(): int
    {
        $user = User::query()->where('username', $this->argument('username'))->first();
        if (! $user) {
            $this->error('No matching user. No records changed.');
            return self::FAILURE;
        }
        $password = $this->secret('New password (at least 8 characters)');
        $confirmation = $this->secret('Confirm new password');
        $validator = Validator::make(['password' => $password, 'password_confirmation' => $confirmation], [
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);
        if ($validator->fails()) {
            $this->error('Passwords must match and meet the password rules. No records changed.');
            return self::FAILURE;
        }
        DB::transaction(function () use ($user, $password): void {
            $user->password = $password; // User model hashes this automatically.
            $user->remember_token = Str::random(60);
            $user->save();
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
        });
        $this->info('Password set. Sign in with this account email: '.$user->email);
        return self::SUCCESS;
    }
}
