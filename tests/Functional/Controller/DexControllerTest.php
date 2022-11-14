<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Tests\Common\Traits\GetterTrait\GetPokedexTrait;
use App\Tests\Functional\Api\AbstractApiTest;
use App\Tests\Functional\Api\AlbumApiTestData;

class DexControllerTest extends AbstractApiTest
{
    use GetPokedexTrait;

    public function testListUser12(): void
    {
        $response = $this->apiRequest('dex/7b52009b64fd0a2a49e6d8a939753077792b0554/list');

        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        /** @var string[][]|string[][][]|int[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertEquals(
            DexControllerTestData::getUser12Content(),
            $data
        );
    }

    public function testListUser13(): void
    {
        $response = $this->apiRequest('dex/bd307a3ec329e10a2cff8fb87480823da114f8f4/list');

        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        /** @var string[][]|string[][][]|int[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertEquals(
            DexControllerTestData::getUser13Content(),
            $data
        );
    }

    public function testListUserUnknown(): void
    {
        $response = $this->apiRequest('dex/46546542313186/list');

        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        /** @var string[][]|string[][][]|int[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertEquals(
            DexControllerTestData::getUserUnknownContent(),
            $data
        );
    }
}
