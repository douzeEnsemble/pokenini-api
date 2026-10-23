<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\TrainerPreferenceController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
#[CoversClass(TrainerPreferenceController::class)]
final class TrainerPreferenceControllerTest extends AbstractTestControllerApi
{
    private const string TRAINER = '7b52009b64fd0a2a49e6d8a939753077792b0554';

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        // Same reason as TrainerDexLinkControllerTest: a PUT-then-GET within a test
        // must share the same uncommitted DB state.
        $this->client->disableReboot();
    }

    #[Test]
    public function getDefaultsToTheHub(): void
    {
        $this->apiRequest('GET', '/trainer_preference/'.self::TRAINER);

        $this->assertJsonResponseIsOK();
        $this->assertSame(['home_page' => 'hub'], $this->getJsonDecodedResponseContent());
    }

    #[Test]
    public function updateThenGet(): void
    {
        $this->apiRequest(
            'PUT',
            '/trainer_preference/'.self::TRAINER,
            [],
            null,
            json_encode(['home_page' => 'election'], JSON_THROW_ON_ERROR),
        );
        $this->assertResponseIsOK();

        $this->apiRequest('GET', '/trainer_preference/'.self::TRAINER);
        $this->assertSame(['home_page' => 'election'], $this->getJsonDecodedResponseContent());

        $this->apiRequest(
            'PUT',
            '/trainer_preference/'.self::TRAINER,
            [],
            null,
            json_encode(['home_page' => 'game'], JSON_THROW_ON_ERROR),
        );
        $this->assertResponseIsOK();

        $this->apiRequest('GET', '/trainer_preference/'.self::TRAINER);
        $this->assertSame(['home_page' => 'game'], $this->getJsonDecodedResponseContent());
    }

    #[Test]
    #[DataProvider('providerBadBodies')]
    public function updateRejectsABadBody(string $body): void
    {
        $this->apiRequest('PUT', '/trainer_preference/'.self::TRAINER, [], null, $body);

        $this->assertResponseStatusCodeSame(400);

        $this->apiRequest('GET', '/trainer_preference/'.self::TRAINER);
        $this->assertSame(['home_page' => 'hub'], $this->getJsonDecodedResponseContent());
    }

    /**
     * @return string[][]
     */
    public static function providerBadBodies(): array
    {
        return [
            'empty' => [''],
            'missing' => ['{}'],
            'not a string' => ['{"home_page": 1}'],
            'unknown page' => ['{"home_page": "admin"}'],
        ];
    }
}
