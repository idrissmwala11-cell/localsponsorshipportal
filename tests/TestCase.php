<?php

namespace Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $compiledPath = storage_path('framework/testing/v/' . substr(md5(static::class . '::' . $this->name()), 0, 12));

        File::ensureDirectoryExists($compiledPath);

        config(['view.compiled' => $compiledPath]);
    }
}
