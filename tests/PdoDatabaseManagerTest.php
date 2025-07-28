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

    public function testGetPaymentsSummaryByCatecheticalYear(): void
    {
        // Setup data
        $this->pdo->exec("INSERT INTO catequizando (cid, nome) VALUES (1, 'John Doe'), (2, 'Jane Roe')");
        $this->pdo->exec("INSERT INTO inscreve (cid, ano_lectivo) VALUES (1, 2023), (2, 2023)");

        $this->manager->insertPayment('user', 1, 15.0, 'ok');
        $this->manager->insertPayment('user', 2, 20.0, 'ok');

        $result = $this->manager->getPaymentsSummaryByCatecheticalYear(2023);
        $this->assertCount(2, $result);

        $byCid = [];
        foreach ($result as $row) {
            $byCid[$row['cid']] = $row;
        }

        $this->assertEquals(15.0, $byCid[1]['total_pago']);
        $this->assertEquals(5.0, $byCid[1]['saldo']);
        $this->assertEquals('Em débito', $byCid[1]['estado']);

        $this->assertEquals(20.0, $byCid[2]['total_pago']);
        $this->assertEquals(0.0, $byCid[2]['saldo']);
        $this->assertEquals('Pago', $byCid[2]['estado']);
    }
}
?>
