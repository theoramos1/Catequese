<?php
use catechesis\PdoDatabaseManager;
use catechesis\DatabaseAccessMode;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../core/PdoDatabaseManager.php';
require_once __DIR__ . '/../core/DatabaseManager.php';
// DatabaseAccessMode class is defined in DatabaseManager.php

class PdoDatabaseManagerTest extends TestCase
{
    private PDO $pdo;
    private PdoDatabaseManager $manager;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // SQLite does not provide the NOW() function by default.  Register it so
        // that queries using it behave like on MySQL during tests.
        $this->pdo->sqliteCreateFunction('NOW', function () {
            return date('Y-m-d H:i:s');
        });
        $this->pdo->exec('CREATE TABLE pagamentos (
            pid INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT,
            cid INTEGER,
            valor REAL,
            ficheiro TEXT,
            estado TEXT,

            data_pagamento TEXT,
            file_path TEXT

            comprovativo TEXT,
            obs TEXT,
            aprovado_por TEXT


        );');
        $this->pdo->exec('CREATE TABLE catequizando (
            cid INTEGER PRIMARY KEY,
            nome TEXT
        );');
        $this->pdo->exec('CREATE TABLE inscreve (
            cid INTEGER,
            ano_lectivo INTEGER
        );');
        $this->pdo->exec('CREATE TABLE configuracoes (
            chave TEXT PRIMARY KEY,
            valor TEXT
        );');
        $this->pdo->exec("INSERT INTO configuracoes (chave, valor) VALUES ('ENROLLMENT_PAYMENT_AMOUNT', '20')");

        $this->manager = new PdoDatabaseManager();
        $ref = new ReflectionClass(PdoDatabaseManager::class);
        $connProp = $ref->getProperty('_connection');
        $modeProp = $ref->getProperty('_connection_access_mode');
        $connProp->setAccessible(true);
        $modeProp->setAccessible(true);
        $connProp->setValue($this->manager, $this->pdo);
        $modeProp->setValue($this->manager, DatabaseAccessMode::DEFAULT_EDIT);
    }

    public function testInsertPayment(): void
    {
        $result = $this->manager->insertPayment('john', 1, 10.5, 'file1.pdf', 'pendente');
        $this->assertTrue($result);

        $stmt = $this->pdo->query('SELECT username, cid, valor, ficheiro, estado, obs FROM pagamentos');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('john', $row['username']);
        $this->assertEquals(1, $row['cid']);
        $this->assertEquals(10.5, $row['valor']);
        $this->assertEquals('file1.pdf', $row['ficheiro']);
        $this->assertEquals('pendente', $row['estado']);
    }

    public function testGetTotalPaymentsByCatechumen(): void
    {
        $this->manager->insertPayment('john', 1, 10.5, 'file1.pdf', 'pendente');
        $this->manager->insertPayment('john', 1, 5.5, 'file2.pdf', 'pendente');

        $total = $this->manager->getTotalPaymentsByCatechumen(1);
        $this->assertEquals(16.0, $total);
    }

    public function testListPaymentsWithStatusAndDebt(): void
    {

        $this->manager->insertPayment('john', 1, 15.0, 'confirmado');
        $this->manager->insertPayment('john', 1, 20.0, 'pendente');
        $this->manager->insertPayment('jane', 2, 20.0, 'confirmado');


        $payments = $this->manager->getPaymentsByCatechumen(1);
        $this->assertCount(2, $payments);

        // Latest payment (pending) should come first due to DESC ordering by date
        $this->assertEquals('pendente', $payments[0]['estado']);
        $this->assertEquals(20.0, $payments[0]['valor']);

        $this->assertEquals('confirmado', $payments[1]['estado']);
        $this->assertEquals(15.0, $payments[1]['valor']);


        $totalConfirmed = 0.0;
        foreach ($payments as $p) {
            if ($p['estado'] === 'aprovado') {
                $totalConfirmed += floatval($p['valor']);
            }
        }

        $expectedFee = 100.0;
        $debt = max($expectedFee - $totalConfirmed, 0.0);
        $this->assertEquals(85.0, $debt);
    }

    public function testInsertPaymentRejectsTooHighAmount(): void
    {
        $this->expectException(Exception::class);
        $this->manager->insertPayment('john', 1, 25.0, 'pendente');
    }

    public function testSetPaymentStatus(): void
    {
        $this->manager->insertPayment('john', 1, 10.0, 'rec.pdf', 'pendente');

        $pid = (int)$this->pdo->query('SELECT pid FROM pagamentos')->fetchColumn();

        $result = $this->manager->setPaymentStatus($pid, 'aprovado', 'ok');
        $this->assertTrue($result);

        $row = $this->pdo->query('SELECT estado, obs FROM pagamentos WHERE pid='.$pid)->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('aprovado', $row['estado']);
        $this->assertEquals('ok', $row['obs']);
    }

    public function testGetPendingPayments(): void
    {
        $this->manager->insertPayment('john', 1, 10.0, 'p1.pdf', 'pendente');
        $this->manager->insertPayment('john', 1, 5.0, 'p2.pdf', 'aprovado');

        $pending = $this->manager->getPendingPayments();
        $this->assertCount(1, $pending);
        $this->assertEquals('p1.pdf', $pending[0]['ficheiro']);
    }

    public function testInsertPendingPaymentWithFileAndApprove(): void
    {
        $result = $this->manager->insertPendingPayment('john', 1, 15.0, '/tmp/doc.pdf');
        $this->assertTrue($result);

        $stmt = $this->pdo->query('SELECT pid, file_path, estado FROM pagamentos');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('pendente', $row['estado']);
        $this->assertEquals('/tmp/doc.pdf', $row['file_path']);

        $pid = intval($row['pid']);
        $this->assertTrue($this->manager->approvePayment($pid));

        $status = $this->pdo->query("SELECT estado FROM pagamentos WHERE pid=$pid")->fetchColumn();
        $this->assertEquals('confirmado', $status);
    }

    public function testInsertPendingPaymentInvalidAmount(): void
    {
        $this->expectException(Exception::class);
        $this->manager->insertPendingPayment('john', 1, -5, 'file');
    }
}
?>
