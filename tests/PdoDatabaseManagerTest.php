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
}
?>
