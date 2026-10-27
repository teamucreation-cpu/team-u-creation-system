<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Mail;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        config(['services.sms.enabled' => false]);
    }

    public function createApplication()
    {
        $app = parent::createApplication();

        if (
            $app->environment() !== 'testing'
            || $app['config']->get('database.default') !== 'sqlite'
            || $app['config']->get('database.connections.sqlite.database') !== ':memory:'
        ) {
            throw new \RuntimeException('Automated tests must use the in-memory SQLite testing database.');
        }

        return $app;
    }
}
