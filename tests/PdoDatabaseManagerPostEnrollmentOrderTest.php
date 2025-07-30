<?php
use catechesis\PdoDatabaseManager;
use catechesis\DatabaseAccessMode;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../core/PdoDatabaseManager.php';
require_once __DIR__ . '/../core/DatabaseManager.php';

class FakePDOStatement
{
    public array $params = [];
    public bool $shouldExecute = true;

    public function bindParam($param, &$var, $type = null)
    {
        $this->params[$param] = $var;
        return true;
    }

    public function execute()
    {
        return $this->shouldExecute;
    }
}

class FakePDO
{
    public string $preparedSql = '';
    public FakePDOStatement $stmt;
    public int $insertId = 42;

    public function __construct()
    {
        $this->stmt = new FakePDOStatement();
    }

    public function prepare($sql)
    {
        $this->preparedSql = $sql;
        return $this->stmt;
    }

    public function lastInsertId()
    {
        return $this->insertId;
    }
}

class PdoDatabaseManagerPostEnrollmentOrderTest extends TestCase
{
    private PdoDatabaseManager $manager;
    private FakePDO $pdo;

    protected function setUp(): void
    {
        $this->manager = new PdoDatabaseManager();
        $this->pdo = new FakePDO();

        $ref = new ReflectionClass(PdoDatabaseManager::class);
        $connProp = $ref->getProperty('_connection');
        $modeProp = $ref->getProperty('_connection_access_mode');
        $connProp->setAccessible(true);
        $modeProp->setAccessible(true);
        $connProp->setValue($this->manager, $this->pdo);
        $modeProp->setValue($this->manager, DatabaseAccessMode::ONLINE_ENROLLMENT);
    }

    public function testPostEnrollmentOrderValid(): void
    {
        $result = $this->manager->postEnrollmentOrder(
            'John Doe', '01/01/2010', 'City', 1,
            'Address', '12345-678',
            0, '127.0.0.1',
            true, true, false, []
        );

        $this->assertEquals($this->pdo->insertId, $result);
        $this->assertArrayHasKey(':nome', $this->pdo->stmt->params);
        $this->assertEquals('John Doe', $this->pdo->stmt->params[':nome']);
        $this->assertEquals(1, $this->pdo->stmt->params[':num_irmaos']);
        $this->assertEquals('a:0:{}', $this->pdo->stmt->params[':autorizacoesSaidaMenores']);
    }

    public function testPostEnrollmentOrderInvalid(): void
    {
        $this->pdo->stmt->shouldExecute = false;
        $this->expectException(Exception::class);
        $this->manager->postEnrollmentOrder(
            'John Doe', '01/01/2010', 'City', 1,
            'Address', '12345-678',
            0, '127.0.0.1',
            true, true, false, []
        );
    }
}
?>
