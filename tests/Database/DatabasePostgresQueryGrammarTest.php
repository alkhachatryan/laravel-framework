<?php

namespace Illuminate\Tests\Database;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Grammars\PostgresGrammar;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class DatabasePostgresQueryGrammarTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testToRawSql()
    {
        $connection = m::mock(Connection::class);
        $connection->shouldReceive('escape')->with('foo', false)->andReturn("'foo'");
        $grammar = new PostgresGrammar;
        $grammar->setConnection($connection);

        $query = $grammar->substituteBindingsIntoRawSql(
            'select * from "users" where \'{}\' ?? \'Hello\\\'\\\'World?\' AND "email" = ?',
            ['foo'],
        );

        $this->assertSame('select * from "users" where \'{}\' ? \'Hello\\\'\\\'World?\' AND "email" = \'foo\'', $query);
    }

    public function testCustomOperators()
    {
        PostgresGrammar::customOperators(['@@@', '@>', '']);
        PostgresGrammar::customOperators(['@@>', 1]);

        $connection = m::mock(Connection::class);
        $grammar = new PostgresGrammar;
        $grammar->setConnection($connection);

        $operators = $grammar->getOperators();

        $this->assertIsList($operators);
        $this->assertContains('@@@', $operators);
        $this->assertContains('@@>', $operators);
        $this->assertNotContains('', $operators);
        $this->assertNotContains(1, $operators);
        $this->assertSame(array_unique($operators), $operators);
    }
}
