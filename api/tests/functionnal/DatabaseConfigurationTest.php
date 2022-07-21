<?php

namespace App\Tests\Functionnal;

use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DatabaseConfigurationTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testDatabaseUrl(): void
    {
        $this->assertEquals('test', self::$kernel->getEnvironment());
        $this->assertIsString(getenv('DATABASE_URL'));
        $this->assertEquals(
            'postgresql://api-platform:!ChangeMe!@database_test:5432/api?serverVersion=13&charset=utf8',
            urldecode((string) getenv('DATABASE_URL'))
        );
    }
}
