<?php

namespace App\Tests\Functionnal\Api;

class DexAvailabilitiesApiTest extends AbstractApiTest
{
    public function testGetCollectionFilteredByDex(): void
    {
        /** @var string[]|string[][] $content */
        $content = $this->apiRequestContent('dex_availabilities', ['dex.slug' => 'redbluegreenyellow']);

        $this->assertCount(4, $content);

        /** @var string[]|string[][] $content */
        $content = $this->apiRequestContent('dex_availabilities', ['dex.slug' => 'goldsilvercrystal']);

        $this->assertCount(3, $content);

        /** @var string[]|string[][] $content */
        $content = $this->apiRequestContent('dex_availabilities', ['dex.slug' => 'home']);

        $this->assertCount(7, $content);
    }
}
