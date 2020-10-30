<?php

namespace App\Contracts\Soundblock;

interface LedgerContract {
    public function getTablesList() : array;
    public function getTableDocuments(string $tableName, ?array $fields = null) : array;
    public function getDocument(string $tableName, string $id, ?array $fields = null) : array;
    public function insertDocument(string $tableName, array $data) : array;
    public function updateDocument(string $tableName, string $id, array $data) : array;
    public function createTable(string $tableName, ?array $indexes = null) : bool;
    public function deleteTable(string $tableName) : bool;
    public function deleteAllTables() : bool;
}
