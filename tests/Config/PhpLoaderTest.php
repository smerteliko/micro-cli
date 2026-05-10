<?php

namespace Tests\Config;

use PHPUnit\Framework\TestCase;
use Smerteliko\MicroCli\Config\Loader\PhpLoader;
use RuntimeException;

class PhpLoaderTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/microcli_tests';
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir);
        }
    }

    public function testLoadsValidPhpConfig(): void
    {
        $file = $this->tempDir . '/valid.php';
        file_put_contents($file, "<?php\nreturn ['foo' => 'bar'];");

        $loader = new PhpLoader();
        $this->assertTrue($loader->supports($file));

        $data = $loader->load($file);
        $this->assertSame(['foo' => 'bar'], $data);

        unlink($file);
    }

    public function testThrowsExceptionOnMissingFile(): void
    {
        $loader = new PhpLoader();

        $this->expectException(RuntimeException::class);
        $loader->load($this->tempDir . '/missing.php');
    }

    public function testThrowsExceptionOnInvalidReturnType(): void
    {
        $file = $this->tempDir . '/invalid_return.php';
        file_put_contents($file, "<?php\nreturn 'not an array';");

        $loader = new PhpLoader();

        $this->expectException(RuntimeException::class);
        $loader->load($file);

        unlink($file);
    }
}