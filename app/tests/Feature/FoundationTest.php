<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_homepage_is_accessible_without_login(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('Plusthree')
            ->assertSee('WHERE AFRICA’S GEMS MEET GLOBAL WORK.')
            ->assertSee('noindex, nofollow');
    }

    public function test_database_tests_use_the_isolated_mysql_database(): void
    {
        $this->assertSame('mysql', DB::connection()->getDriverName());
        $this->assertSame('plusthree_testing', DB::connection()->getDatabaseName());
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('users'));
    }
}
