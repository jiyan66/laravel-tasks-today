<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }
    protected function signIn(): \App\Models\User
    {
        $user = \App\Models\User::query()->create(['username' => 'operator', 'full_name' => 'Test Operator', 'email' => 'operator@example.test', 'password' => 'Testing123!']);
        $this->actingAs($user);
        return $user;
    }
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }
}
