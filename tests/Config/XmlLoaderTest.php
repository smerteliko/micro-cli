<?php

namespace Tests\Config;

use PHPUnit\Framework\TestCase;
use Smerteliko\MicroCli\Config\Loader\XmlLoader;
use RuntimeException;

class XmlLoaderTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/microcli_tests';
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir);
        }
    }

    public function testLoadsValidXmlConfig(): void
    {
        $file = $this->tempDir . '/valid.xml';
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<config>
    <foo>bar</foo>
    <database>
        <host>localhost</host>
    </database>
</config>
XML;
        file_put_contents($file, $xml);

        $loader = new XmlLoader();
        $this->assertTrue($loader->supports($file));

        $data = $loader->load($file);

        $this->assertArrayHasKey('foo', $data);
        $this->assertSame('bar', $data['foo']);
        $this->assertSame('localhost', $data['database']['host']);

        unlink($file);
    }

    public function testThrowsExceptionOnInvalidXml(): void
    {
        $file = $this->tempDir . '/invalid.xml';
        file_put_contents($file, "<config><foo>bar</config>"); // Missing closing foo tag

        $loader = new XmlLoader();

        $this->expectException(RuntimeException::class);
        $loader->load($file);

        unlink($file);
    }
}