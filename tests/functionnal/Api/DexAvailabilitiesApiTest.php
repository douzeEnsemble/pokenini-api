<?php

declare(strict_types=1);

namespace App\Tests\Functionnal\Api;

class DexAvailabilitiesApiTest extends AbstractApiTest
{
    public function testGetCollectionFilteredByDex(): void
    {
        /** @var string[]|string[][] $content */
        $content = $this->apiRequestContent('dex_availabilities', ['dex.slug' => 'redgreenblueyellow']);

        $this->assertCount(7, $content);

        /** @var string[]|string[][] $content */
        $content = $this->apiRequestContent('dex_availabilities', ['dex.slug' => 'goldsilvercrystal']);

        $this->assertCount(6, $content);

        /** @var string[]|string[][] $content */
        $content = $this->apiRequestContent('dex_availabilities', ['dex.slug' => 'home']);

        $this->assertCount(12, $content);
    }
}
