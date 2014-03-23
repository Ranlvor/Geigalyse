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
    $this->sql->busyTimeout(60000);
    $this->sql->exec("ATTACH DATABASE '../db/mesurements.db' AS mesurements");
    $this->sql->exec("ATTACH DATABASE '../db/settings.db' AS settings");
    $this->sql->exec("ATTACH DATABASE '../db/results.db' AS results");


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
    $this->sql->exec("INSERT OR REPLACE INTO mesurements.mesurements -- source, timestamp, count
                      SELECT uploader, hexdec(timestamp), hexdec(count), 0
                      FROM uploadsGet
                      WHERE processed = 0;");
    $this->sql->exec("UPDATE uploadsGet
                      SET processed = 1
                      WHERE processed = 0;");
    $this->commitTransaction();
  }

  function processUnprocessedMesurements(){
    $this->beginTransaction();

    $this->sql->exec("INSERT OR REPLACE INTO results.processedmesurements
                      SELECT source, timestamp, 
                      (1+(count* --eliminate deadtime

                          (SELECT `deadtimeS`
                           FROM settings.tubes
                           WHERE id = source)

                        )/60)*count --eliminate deadtime end


                        / (SELECT `cpm-per-mysvph` --convert to µSv/h
                           FROM settings.tubes
                           WHERE id = source)
                      FROM mesurements.mesurements
                      WHERE processed = 0;");

    $this->sql->exec("UPDATE mesurements.mesurements
                      SET processed = 1
                      WHERE processed = 0;");

    $this->commitTransaction();
  }

  private $getLatestProcessedMesurementsSlidingAverageStmt = null;
  function getLatestProcessedMesurementsSlidingAverage($count, $window) {
    if($this->getLatestProcessedMesurementsSlidingAverageStmt  == null) {
      $this->getLatestProcessedMesurementsSlidingAverageStmt = $this->sql->prepare('
        SELECT timestamp, value AS slidingAVG
        FROM slidingaveragecache
        WHERE window = :window
        ORDER BY timestamp DESC
        LIMIT :count;
      ');
    }

    $this->getLatestProcessedMesurementsSlidingAverageStmt->bindParam(':window', $window, SQLITE3_INTEGER);
    $this->getLatestProcessedMesurementsSlidingAverageStmt->bindParam(':count', $count, SQLITE3_INTEGER);

    return $this->getLatestProcessedMesurementsSlidingAverageStmt->execute();
  }

  private $populateSlidingAverageCacheStmt = null;
  function populateSlidingAverageCache($window) {
    if($this->populateSlidingAverageCacheStmt == null) {
      $this->populateSlidingAverageCacheStmt = $this->sql->prepare('
        INSERT INTO slidingaveragecache
        SELECT timestamp, :window AS window, NULL AS lastIncludedTimestamp, (
                                    SELECT AVG(mysvph)
                                    FROM processedmesurements AS innerPM
                                    WHERE innerPM.timestamp >= outerPM.timestamp - :window
                                      AND innerPM.timestamp <= outerPM.timestamp + :window
                                  ) AS value

        FROM processedmesurements AS outerPM

        WHERE timestamp IN (
          SELECT pm.timestamp
          FROM processedmesurements AS pm
          LEFT JOIN slidingaveragecache AS sac
                ON (pm.timestamp = sac.timestamp) AND (sac.window = :window)
          WHERE sac.timestamp IS NULL
          ORDER BY pm.timestamp DESC
        )
      ');
    }

    $this->populateSlidingAverageCacheStmt->bindParam(':window', $window, SQLITE3_INTEGER);

    $this->populateSlidingAverageCacheStmt->execute();
  }

  function applyBadTimestampTimes() {
    $this->sql->exec('
      DELETE FROM processedmesurements
        WHERE timestamp IN (
          SELECT pm.timestamp
          FROM        baddata
            LEFT JOIN processedmesurements AS pm
                    ON baddata.source = pm.source
                  AND baddata.begin <= pm.timestamp
                  AND baddata.end   >= pm.timestamp
        )
     ');
  }

  private $getLatestMesurementExtendetStmt = null;
  function getLatestMesurementExtendet() {
    if($this->getLatestMesurementExtendetStmt == null)
      $this->getLatestMesurementExtendetStmt = $this->sql->prepare('
        SELECT pm.source, pm.timestamp, pm.mysvph, tubes.name, tubes.deadtimeS, tubes."cpm-per-mysvph", mesurements.count
        FROM processedMesurements AS pm
        LEFT JOIN tubes ON pm.source = tubes.id
        LEFT JOIN mesurements ON mesurements.timestamp = pm.timestamp
        ORDER BY pm.timestamp DESC
        LIMIT 1;
      ');
    
    $result = $this->getLatestMesurementExtendetStmt->execute();
    $resultArray = $result->fetchArray();
    $result->finalize();
    return $resultArray;
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
