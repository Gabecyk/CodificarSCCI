<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();

        $connection = $app['config']->get('database.default');

        if ($connection !== 'sqlite') {
            throw new RuntimeException(
                "Testes abortados: a conexão \"{$connection}\" não é sqlite e seria apagada pelo RefreshDatabase."
            );
        }

        return $app;
    }
}
