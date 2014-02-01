<?php
function sqliteHexdec($hex) {
  return hexdec($hex)."";
}

class GeigalyseDatabse {
  private $sql;
  private $beginTransactionStmt = null;
  private $commitTransactionStmt = null;

  function GeigalyseDatabse() {
    $this->sql = new SQLite3('../db/uploads.db');
    $this->sql->exec("ATTACH DATABASE '../db/mesurements.db' AS mesurements");
    $this->beginTransactionStmt = $this->sql->prepare('BEGIN TRANSACTION;');
    $this->commitTransactionStmt = $this->sql->prepare('COMMIT;');
    $this->sql->createFunction('hexdec', 'sqliteHexdec', 1);
  }

  private $addUploadMetadataStmt = null;
  private $addUploadDataStmt = null;
  function addUploadToDatabase($uploader, $data) {
    if($this->addUploadMetadataStmt == null)
      $this->addUploadMetadataStmt = $this->sql->prepare('INSERT INTO uploads (uploader, uploadtime) VALUES (:uploader, :uploadtime)');
    if($this->addUploadDataStmt == null)
      $this->addUploadDataStmt = $this->sql->prepare('INSERT INTO uploadsdata (id, data) VALUES (:id, :data)');

    $this->beginTransaction();

    $this->addUploadMetadataStmt->bindParam (':uploader', $uploader, SQLITE3_INTEGER);
    $this->addUploadMetadataStmt->bindValue(':uploadtime', time(), SQLITE3_INTEGER);
    $this->addUploadMetadataStmt->execute();

    $this->addUploadDataStmt->bindValue(':id', $this->sql->lastInsertRowID(), SQLITE3_INTEGER);
    $this->addUploadDataStmt->bindParam(':data', $data, SQLITE3_BLOB);
    $this->addUploadDataStmt->execute();

    $this->commitTransaction();
  }

  private $addUploadDataGetStmt = null;
  function addGetUploadToDatabase($uploader, $timestamp, $count){
    if($this->addUploadDataGetStmt == null)
      $this->addUploadDataGetStmt = $this->sql->prepare('INSERT INTO uploadsGet (uploader, uploadtime, timestamp, count) VALUES (:uploader, :uploadtime, :timestamp, :count)');

    $this->addUploadDataGetStmt->bindParam(':uploader', $uploader, SQLITE3_INTEGER);
    $this->addUploadDataGetStmt->bindValue(':uploadtime', time(), SQLITE3_INTEGER);
    $this->addUploadDataGetStmt->bindParam(':timestamp', $timestamp, SQLITE3_BLOB);
    $this->addUploadDataGetStmt->bindParam(':count', $count, SQLITE3_BLOB);

    $this->addUploadDataGetStmt->execute();
  }

  function processUnprocessedGetUploads(){
    $this->beginTransaction();
    $this->sql->exec("INSERT OR REPLACE INTO mesurements -- source, timestamp, count
                      SELECT uploader, hexdec(timestamp), hexdec(count)
                      FROM uploadsGet
                      WHERE processed = 0;");
    $this->sql->exec("UPDATE uploadsGet
                      SET processed = 1
                      WHERE processed = 0;");
    $this->commitTransaction();
  }

  private $updateMesurementStmt = null;
  function updateMesurement($source, $timestamp, $count){
    if($this->updateMesurement == null)
      $this->updateMesurement = $this->sql->prepare('INSERT INTO OR REPLACE mesurements (source, timestamp, count) VALUES (:source, :timestamp, :count)');

    $this->updateMesurement->bindParam(':uploader', $source, SQLITE3_INTEGER);
    $this->updateMesurement->bindParam(':timestamp', $timestamp, SQLITE3_INTEGER);
    $this->updateMesurement->bindParam(':count', $count, SQLITE3_INTEGER);

    $this->updateMesurement->execute();
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
