<?php
use catechesis\PdoDatabaseManager;
use catechesis\DatabaseAccessMode;
use PHPUnit\Framework\TestCase;

// Provide a stub uLogin class so tests don't require the real authentication backend
class uLogin
{
    public function CreateUser($username, $password, $profile = null)
    {
        return strlen($password) >= 10; // fail if password is too short
    }
}

require_once __DIR__ . '/../core/PdoDatabaseManager.php';
require_once __DIR__ . '/../core/DatabaseManager.php';

class PdoDatabaseManagerCreateUserAccountTest extends TestCase
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

    public function testCreateUserAccountShortPasswordThrows(): void
    {
        $this->expectException(Exception::class);
        $this->manager->createUserAccount('shortpwd', 'Short Password', '12345', false, false);
    }

    public function testCreateUserAccountInvalidEmailThrows(): void
    {
        $this->expectException(Exception::class);
        $this->manager->createUserAccount('bademail', 'Bad Email', 'longenough1', false, false, true, null, 'invalid');
    }
}
?>
