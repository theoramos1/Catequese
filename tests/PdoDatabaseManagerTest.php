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
            estado TEXT,
            data_pagamento TEXT
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
        $result = $this->manager->insertPayment('john', 1, 10.5, 'pendente');
        $this->assertTrue($result);

        $stmt = $this->pdo->query('SELECT username, cid, valor, estado FROM pagamentos');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('john', $row['username']);
        $this->assertEquals(1, $row['cid']);
        $this->assertEquals(10.5, $row['valor']);
        $this->assertEquals('pendente', $row['estado']);
    }

    public function testGetTotalPaymentsByCatechumen(): void
    {
        $this->manager->insertPayment('john', 1, 10.5, 'pendente');
        $this->manager->insertPayment('john', 1, 5.5, 'pendente');

        $total = $this->manager->getTotalPaymentsByCatechumen(1);
        $this->assertEquals(16.0, $total);
    }

    public function testListPaymentsWithStatusAndDebt(): void
    {
        $this->manager->insertPayment('john', 1, 30.0, 'confirmado');
        $this->manager->insertPayment('john', 1, 20.0, 'pendente');
        $this->manager->insertPayment('jane', 2, 50.0, 'confirmado');

        $payments = $this->manager->getPaymentsByCatechumen(1);
        $this->assertCount(2, $payments);

        // Latest payment (pending) should come first due to DESC ordering by date
        $this->assertEquals('pendente', $payments[0]['estado']);
        $this->assertEquals(20.0, $payments[0]['valor']);

        $this->assertEquals('confirmado', $payments[1]['estado']);
        $this->assertEquals(30.0, $payments[1]['valor']);

        $totalConfirmed = 0.0;
        foreach ($payments as $p) {
            if ($p['estado'] === 'confirmado') {
                $totalConfirmed += floatval($p['valor']);
            }
        }

        $expectedFee = 100.0;
        $debt = max($expectedFee - $totalConfirmed, 0.0);
        $this->assertEquals(70.0, $debt);
    }
}
?>
