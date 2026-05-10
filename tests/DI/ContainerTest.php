<?php

namespace Tests\DI;

use PHPUnit\Framework\TestCase;
use Smerteliko\MicroCli\DI\Container;
use RuntimeException;

// Dummy classes for testing autowiring
class Database {
    public string $name = 'MySQL';
}

class UserRepository {
    public Database $db;
    public function __construct(Database $db) {
        $this->db = $db;
    }
}

class ContainerTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    public function testSetAndGetSingleton(): void
    {
        $container = new Container();
        $db = new Database();

        $container->set(Database::class, $db);

        $this->assertTrue($container->has(Database::class));
        $this->assertSame($db, $container->get(Database::class));
    }

    /**
     * @throws \ReflectionException
     */
    public function testAutowiresDependencies(): void
    {
        $container = new Container();

        /** @var UserRepository $repo */
        $repo = $container->get(UserRepository::class);

        $this->assertInstanceOf(UserRepository::class, $repo);
        $this->assertInstanceOf(Database::class, $repo->db);
        $this->assertSame('MySQL', $repo->db->name);
    }

    /**
     * @throws \ReflectionException
     */
    public function testThrowsExceptionOnUnknownService(): void
    {
        $container = new Container();

        $this->expectException(RuntimeException::class);
        $container->get('NonExistentClass123');
    }
}