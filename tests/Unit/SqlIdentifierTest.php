<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\ActiveRecord;
use App\Exceptions\DatabaseException;
use PHPUnit\Framework\TestCase;

/**
 * Verifica el blindaje de identificadores SQL en ActiveRecord
 * (los valores van parametrizados; columnas/ORDER BY no pueden,
 * así que se validan contra whitelist y formato).
 */
class SqlIdentifierTest extends TestCase
{
    public function test_accepts_whitelisted_column(): void
    {
        $this->assertSame('email', SqlIdentifierFixture::column('email'));
    }

    public function test_rejects_unknown_column(): void
    {
        $this->expectException(DatabaseException::class);
        SqlIdentifierFixture::column('password; DROP TABLE users');
    }

    public function test_rejects_column_not_in_whitelist(): void
    {
        $this->expectException(DatabaseException::class);
        SqlIdentifierFixture::column('is_admin');
    }

    public function test_accepts_valid_order_by(): void
    {
        $this->assertSame('created_at DESC', SqlIdentifierFixture::orderBy('created_at DESC'));
        $this->assertSame('id ASC, email DESC', SqlIdentifierFixture::orderBy('id ASC, email DESC'));
    }

    public function test_rejects_malicious_order_by(): void
    {
        $this->expectException(DatabaseException::class);
        SqlIdentifierFixture::orderBy('id; DELETE FROM users');
    }
}

/**
 * Expone los validadores protegidos de ActiveRecord para los tests.
 */
final class SqlIdentifierFixture extends ActiveRecord
{
    protected static string $table = 'users';

    /** @var list<string> */
    protected static array $columns = ['id', 'name', 'email', 'created_at'];

    public static function column(string $c): string
    {
        return self::assertColumn($c);
    }

    public static function orderBy(string $o): string
    {
        return self::assertOrderBy($o);
    }
}
