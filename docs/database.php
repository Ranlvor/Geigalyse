<?php

class GeigalyseDatabse {
  private $uploadsdb;
  private $beginTransactionStmt = null;
  private $commitTransactionStmt = null;

  function GeigalyseDatabse() {
    $this->uploadsdb = new SQLite3('../db/uploads.db');
    $this->beginTransactionStmt = $this->uploadsdb->prepare('BEGIN TRANSACTION;');
    $this->commitTransactionStmt = $this->uploadsdb->prepare('COMMIT;');
  }

  private $addUploadMetadataStmt = null;
  private $addUploadDataStmt = null;
  function addUploadToDatabase($uploader, $data) {
    if($this->addUploadMetadataStmt == null)
      $this->addUploadMetadataStmt = $this->uploadsdb->prepare('INSERT INTO uploads (uploader, uploadtime) VALUES (:uploader, :uploadtime)');
    if($this->addUploadDataStmt == null)
      $this->addUploadDataStmt = $this->uploadsdb->prepare('INSERT INTO uploadsdata (id, data) VALUES (:id, :data)');

    $this->beginTransaction();

    $this->addUploadMetadataStmt->bindParam (':uploader', $uploader, SQLITE3_INTEGER);
    $this->addUploadMetadataStmt->bindValue(':uploadtime', time(), SQLITE3_INTEGER);
    $this->addUploadMetadataStmt->execute();

    $this->addUploadDataStmt->bindValue(':id', $this->uploadsdb->lastInsertRowID(), SQLITE3_INTEGER);
    $this->addUploadDataStmt->bindParam(':data', $data, SQLITE3_BLOB);
    $this->addUploadDataStmt->execute();

    $this->commitTransaction();
  }

  private function beginTransaction() {
    $this->beginTransactionStmt->execute();
  }

  private function commitTransaction() {
    $this->commitTransactionStmt->execute();
  }
}

global $db;
$db = new GeigalyseDatabse();
