<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        // Refuse conflicting email data rather than merging/deleting existing people.
        if (DB::table('users')->select('email')->groupBy('email')->havingRaw('COUNT(*) > 1')->exists()) {
            throw new \RuntimeException('Duplicate user emails exist. Assign unique emails before this migration.');
        }
        Schema::table('users', function (Blueprint $table): void {
            $table->unique('email');
            $table->string('password', 255)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
        });
        Schema::create('password_reset_tokens', function (Blueprint $table): void {
            $table->string('email', 100)->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
        Schema::table('tasks', function (Blueprint $table): void {
            $table->boolean('is_archived')->default(false)->index();
        });
        // TSA3 used "completed"; TSA4 explicitly requires "done". No mail is sent by migrations.
        DB::table('tasks')->where('status', 'completed')->update(['status' => 'done']);
    }
    public function down(): void
    {
        // Do not change task statuses or delete task rows during rollback.
        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropIndex('tasks_is_archived_index');
            $table->dropColumn('is_archived');
        });
        Schema::dropIfExists('password_reset_tokens');
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique('users_email_unique');
            $table->dropColumn(['password', 'email_verified_at', 'remember_token']);
        });
    }
};
