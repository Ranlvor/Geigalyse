mkfifo window-3600-$$.csv
mkfifo window-300-$$.csv
mkfifo window-0-$$.csv

echoProcessedMesurementsGetStatement() {
  LIMIT=$1
  WINDOW=$2
cat <<EOS

INSERT INTO slidingaveragecache
SELECT timestamp, $WINDOW AS window, NULL AS lastIncludedTimestamp, (
                            SELECT AVG(mysvph)
                            FROM processedmesurements AS innerPM
                            WHERE innerPM.timestamp >= outerPM.timestamp - $WINDOW
                              AND innerPM.timestamp <= outerPM.timestamp + $WINDOW
                          ) AS value

FROM processedmesurements AS outerPM

WHERE timestamp IN (
  SELECT pm.timestamp
  FROM processedmesurements AS pm
  LEFT JOIN slidingaveragecache AS sac
        ON (pm.timestamp = sac.timestamp) AND (sac.window = $WINDOW)
  WHERE sac.timestamp IS NULL
  ORDER BY pm.timestamp DESC
  LIMIT $LIMIT);

.output window-$WINDOW-$$.csv
SELECT timestamp - 2208988800, value AS slidingAVG
FROM slidingaveragecache
WHERE window = $WINDOW
ORDER BY timestamp DESC
LIMIT $LIMIT;

EOS

}

{
cat <<EOS
BEGIN TRANSACTION;

.timeout 60000

.output window-0-$$.csv
SELECT timestamp - 2208988800, mysvph AS slidingAVG
FROM processedMesurements
ORDER BY timestamp DESC
LIMIT $LIMIT;
EOS

echoProcessedMesurementsGetStatement $LIMIT 300
echoProcessedMesurementsGetStatement $LIMIT 3600

cat <<EOS
COMMIT;
EOS
} | sqlite3 ../db/results.db &

gnuplot <<EOF

set terminal pngcairo dashed
set output
set term pngcairo size 700,280
set datafile separator "|"
set xdata time
set xlabel "Time"
set ylabel "Radiation\nµSv/h"
set timefmt "%s"
set format x "$TIMEFORMAT"
set xtics rotate
set yrange [0:]
set tmargin 2
set bmargin 5
#I do not know how and why the following line works, it is adapted from https://stackoverflow.com/questions/10834037/gnuplot-adjust-size-of-key-legend
set key above width -11 vertical maxrows 1

set style line 1 lc rgb "red" ps 0.1 pt 1
set style line 2 lc rgb "green" ps 0.1
set style line 3 lc rgb "blue" ps 0.1

set grid xtics ytics mxtics mytics

plot "window-0-$$.csv"  using 1:2 notitle  ls 3, \
    "window-300-$$.csv"  using 1:2 notitle ls 2, \
    "window-3600-$$.csv" using 1:2 ls 1 notitle, \
    0/0 title "1 minute sampling interval" with points ls 3 ps 1, \
    0/0 title "average +- 5 min"  with points ls 2 ps 1, \
    0/0 title "average +- 60 min" with points ls 1 ps 1

EOF

rm window-3600-$$.csv window-300-$$.csv window-0-$$.csv
