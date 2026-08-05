<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\VersionService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(VersionService::class)]
final class VersionServiceTest extends TestCase
{
    private string $tempDir;

    #[\Override]
    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir().'/version_service_test_'.uniqid();
        mkdir($this->tempDir);
    }

    #[\Override]
    protected function tearDown(): void
    {
        $files = glob($this->tempDir.'/*');
        if (false !== $files) {
            foreach ($files as $file) {
                unlink($file);
            }
        }
        rmdir($this->tempDir);
    }

    public function testGetVersionReturnsTrimmedFileContent(): void
    {
        file_put_contents($this->tempDir.'/version', "1.2.12\n");
        $service = new VersionService($this->tempDir);

        $this->assertSame('1.2.12', $service->getVersion());
    }

    public function testGetVersionReturnsFallbackWhenFileMissing(): void
    {
        $service = new VersionService($this->tempDir);

        $this->assertSame('unknown', $service->getVersion());
    }
}
