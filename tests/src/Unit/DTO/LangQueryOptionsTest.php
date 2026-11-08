<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\LangQueryOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

/**
 * @internal
 */
#[CoversClass(LangQueryOptions::class)]
final class LangQueryOptionsTest extends TestCase
{
    #[Test]
    public function defaults(): void
    {
        $options = new LangQueryOptions();

        $this->assertNull($options->lang);
    }

    #[Test]
    public function withoutLanguage(): void
    {
        $options = new LangQueryOptions(['lang' => null], ['en', 'fr']);

        $this->assertNull($options->lang);
    }

    #[DataProvider('providerEnabledLanguages')]
    #[Test]
    public function enabledLanguage(string $lang): void
    {
        $options = new LangQueryOptions(['lang' => $lang], ['en', 'fr']);

        $this->assertSame($lang, $options->lang);
    }

    /**
     * @return array<string, array{lang: string}>
     */
    public static function providerEnabledLanguages(): array
    {
        return [
            'first' => ['lang' => 'en'],
            'last' => ['lang' => 'fr'],
        ];
    }

    #[DataProvider('providerRejectedLanguages')]
    #[Test]
    public function rejectedLanguage(string $lang): void
    {
        $this->expectException(InvalidOptionsException::class);

        new LangQueryOptions(['lang' => $lang], ['en', 'fr']);
    }

    /**
     * @return array<string, array{lang: string}>
     */
    public static function providerRejectedLanguages(): array
    {
        return [
            'not enabled' => ['lang' => 'de'],
            'empty' => ['lang' => ''],
            'wrong case' => ['lang' => 'FR'],
        ];
    }

    #[Test]
    public function aLanguageWhichIsNotAStringIsRejected(): void
    {
        $this->expectException(InvalidOptionsException::class);

        new LangQueryOptions(['lang' => 5], ['en', 'fr']);
    }

    #[Test]
    public function noLanguageIsEnabledWithoutLocales(): void
    {
        $this->expectException(InvalidOptionsException::class);

        new LangQueryOptions(['lang' => 'en']);
    }

    #[Test]
    public function unknownOption(): void
    {
        $this->expectException(UndefinedOptionsException::class);

        new LangQueryOptions(['foo' => 'bar'], ['en']);
    }
}
