<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\SqlFileLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(SqlFileLoader::class)]
final class SqlFileLoaderTest extends TestCase
{
    private string $tempDir;

    #[\Override]
    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir().'/sql_loader_test_'.uniqid();
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

    public function testLoadReturnsFileContent(): void
    {
        file_put_contents($this->tempDir.'/test.sql', 'SELECT 1');
        $loader = new SqlFileLoader($this->tempDir);

        $this->assertSame('SELECT 1', $loader->load('test.sql'));
    }

    public function testLoadThrowsOnMissingFile(): void
    {
        $loader = new SqlFileLoader($this->tempDir);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to read SQL file "missing.sql"');
        $loader->load('missing.sql');
    }

    public function testAllReferencedSqlFilesExist(): void
    {
        $loader = new SqlFileLoader(dirname(__DIR__, 4).'/resources/sql');

        $this->assertNotEmpty($loader->load('pokemons-get_n_to_pick.sql'));
        $this->assertNotEmpty($loader->load('pokemons-get_n_to_vote.sql'));
        $this->assertNotEmpty($loader->load('trainer_pokemon_elo-get_top_n.sql'));
    }
}
