#!/bin/sh
echo "this skript is not adapted to the new populateMesurements which forks in background and calculates sliding averages"
exit(1);




echo "Resetting database";
cd ../db
sqlite3 uploads.db <<EOS
BEGIN;
UPDATE uploads SET processed = 0;
UPDATE uploadsGet SET processed = 0;
COMMIT;
EOS

echo "Reprocessing uploads";
cd ../analyse
php populateMesurements.php

echo "discarding bad data"
php <<EOS
<?php
include("../docs/database.php");
\$db->applyBadTimestampTimes();
EOS

cd ../db
echo "deleting sliding average cache (just to be sure)"
sqlite3 results.db <<EOS
DELETE FROM slidingaveragecache;
EOS

cd ../analyse
echo "regenerating slidingaveragecache..."
echo -n "regenerating last day..."
./plot-dayly.sh > /dev/null && echo "[OK]" || echo "[FAIL]"
echo -n "regenerating last week..."
./plot-weekly.sh > /dev/null && echo "[OK]" || echo "[FAIL]"
echo -n "regenerating last month..."
./plot-monthly.sh > /dev/null && echo "[OK]" || echo "[FAIL]"
