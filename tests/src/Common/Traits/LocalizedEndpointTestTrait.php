<?php

declare(strict_types=1);

namespace App\Tests\Common\Traits;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * The tests of an endpoint which takes a "lang" parameter and answers the name in that language.
 *
 * The using class is a WebTestCase.
 */
trait LocalizedEndpointTestTrait
{
    #[Test]
    public function getInTheRequestedLanguage(): void
    {
        $example = $this->localizedExample();

        $this->assertSame($example['fr'], $this->findBySlug($this->getList('fr'), $example['slug']));
    }

    #[Test]
    public function getInTheFallbackLanguage(): void
    {
        $example = $this->localizedExample();

        $this->assertSame($example['en'], $this->findBySlug($this->getList('en'), $example['slug']));
    }

    #[Test]
    public function getInANotEnabledLanguageIsABadRequest(): void
    {
        $client = $this->localizedClient();
        $client->request('GET', $this->localizedUri().'?lang=de', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        self::assertResponseStatusCodeSame(400);
    }

    /**
     * The client which sends the requests: a new one, or the one the test case created already.
     */
    abstract protected function localizedClient(): KernelBrowser;

    abstract protected function localizedUri(): string;

    /**
     * A record of the fixtures, as the endpoint answers it for each language.
     *
     * @return array{slug: string, fr: array<string, mixed>, en: array<string, mixed>}
     */
    abstract protected function localizedExample(): array;

    /**
     * @return array<array-key, mixed>
     */
    private function getList(string $lang): array
    {
        $client = $this->localizedClient();
        $client->request('GET', $this->localizedUri().'?lang='.$lang, [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        self::assertResponseIsSuccessful();

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<array-key, mixed> $data */
        $data = json_decode($content, associative: true);
        self::assertIsArray($data);

        return $data;
    }

    /**
     * @param array<array-key, mixed> $list
     *
     * @return array<array-key, mixed>
     */
    private function findBySlug(array $list, string $slug): array
    {
        /** @var mixed $item */
        foreach ($list as $item) {
            if (\is_array($item) && $slug === $item['slug']) {
                return $item;
            }
        }

        $this->fail("No record {$slug}");
    }
}
