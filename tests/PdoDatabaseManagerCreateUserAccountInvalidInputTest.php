<?php
use catechesis\PdoDatabaseManager;
use catechesis\DatabaseAccessMode;
use PHPUnit\Framework\TestCase;

// Ensure the uLogin stub is available
if (!class_exists('uLogin')) {
    class uLogin
    {
        public function CreateUser($username, $password, $profile = null)
        {
            return strlen($password) >= 10; // fail if password is too short
        }
    }
}

require_once __DIR__ . '/../core/PdoDatabaseManager.php';
require_once __DIR__ . '/../core/DatabaseManager.php';

class PdoDatabaseManagerCreateUserAccountInvalidInputTest extends TestCase
{
    private PDO $pdo;
    private PdoDatabaseManager $manager;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Minimal tables required by createUserAccount
        $this->pdo->exec('CREATE TABLE utilizador (
            username TEXT PRIMARY KEY,
            nome TEXT,
            admin INTEGER,
            tel INTEGER,
            email TEXT,
            estado INTEGER,
            CHECK (instr(email, "@") > 0)
        );');
        $this->pdo->exec('CREATE TABLE catequista (
            username TEXT PRIMARY KEY,
            estado INTEGER
        );');

        $this->manager = new PdoDatabaseManager();
        $ref = new ReflectionClass(PdoDatabaseManager::class);
        $connProp = $ref->getProperty('_connection');
        $modeProp = $ref->getProperty('_connection_access_mode');
        $connProp->setAccessible(true);
        $modeProp->setAccessible(true);
        $connProp->setValue($this->manager, $this->pdo);
        $modeProp->setValue($this->manager, DatabaseAccessMode::DEFAULT_EDIT);
    }

    public function testShortPasswordCausesFailure(): void
    {
        try {
            $this->manager->createUserAccount('user', 'User Name', 'short', false, false);
            $this->fail('Exception not thrown');
        } catch (Exception $e) {
            // expected
        }

        $count = $this->pdo->query('SELECT COUNT(*) FROM utilizador')->fetchColumn();
        $this->assertEquals(0, $count);
    }

    public function testInvalidEmailCausesFailure(): void
    {
        try {
            $this->manager->createUserAccount('user', 'User Name', 'longenough1', false, false, true, null, 'bad');
            $this->fail('Exception not thrown');
        } catch (Exception $e) {
            // expected
        }

        $count = $this->pdo->query('SELECT COUNT(*) FROM utilizador')->fetchColumn();
        $this->assertEquals(0, $count);
    }
}
?>
