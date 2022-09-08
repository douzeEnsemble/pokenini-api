<?php

namespace App\Tests\Functionnal\Service;

use App\Service\ImportPokemonsService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ImportPokemonsServiceTest extends KernelTestCase
{
    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testDoMethod(): void
    {
        /** @var ImportPokemonsService $service */
        $service = static::getContainer()->get(ImportPokemonsService::class);

        $service->do();
    }
}
