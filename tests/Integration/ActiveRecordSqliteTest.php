<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Core\ActiveRecord;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Ejercita el CRUD real de ActiveRecord contra SQLite en memoria.
 * Verifica que el SQL del ORM es portable (sin LIMIT en UPDATE/DELETE).
 */
class ActiveRecordSqliteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('La extensión pdo_sqlite no está disponible.');
        }

        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec(
            'CREATE TABLE posts (
                id    INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                body  TEXT
            )',
        );

        ActiveRecord::setDB($pdo);
    }

    public function test_insert_assigns_id_and_find_retrieves(): void
    {
        $post        = new SqlitePost();
        $post->title = 'Hola';
        $post->body  = 'Mundo';

        $this->assertTrue($post->save());
        $this->assertNotNull($post->id);

        $found = SqlitePost::find($post->id);
        $this->assertNotNull($found);
        $this->assertSame('Hola', $found->title);
        $this->assertSame('Mundo', $found->body);
    }

    public function test_all_and_count(): void
    {
        (new SqlitePost(['title' => 'A']))->save();
        (new SqlitePost(['title' => 'B']))->save();

        $this->assertSame(2, SqlitePost::count());
        $this->assertCount(2, SqlitePost::all());
    }

    public function test_where_and_find_by(): void
    {
        (new SqlitePost(['title' => 'Buscable']))->save();

        $this->assertCount(1, SqlitePost::where('title', 'Buscable'));
        $this->assertNotNull(SqlitePost::findBy('title', 'Buscable'));
        $this->assertNull(SqlitePost::findBy('title', 'No existe'));
    }

    public function test_update_persists_changes(): void
    {
        $post = new SqlitePost(['title' => 'Original']);
        $post->save();

        $post->title = 'Editado';
        $this->assertTrue($post->save());

        $reloaded = SqlitePost::find($post->id);
        $this->assertSame('Editado', $reloaded?->title);
    }

    public function test_delete_removes_row(): void
    {
        $post = new SqlitePost(['title' => 'Borrar']);
        $post->save();
        $id = $post->id;

        $this->assertTrue($post->delete());
        $this->assertNull(SqlitePost::find((int) $id));
        $this->assertSame(0, SqlitePost::count());
    }
}

/**
 * Modelo de prueba para el test de SQLite.
 */
final class SqlitePost extends ActiveRecord
{
    protected static string $table = 'posts';

    /** @var list<string> */
    protected static array $columns = ['id', 'title', 'body'];

    public string $title = '';
    public ?string $body = null;
}
