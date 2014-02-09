#!/bin/sh
mkfifo window-3600.csv
mkfifo window-300.csv
mkfifo window-0.csv
{
sqlite3 ../db/results.db <<EOF

.timeout 60000
.output window-0.csv
SELECT timestamp - 2208988800, mysvph AS slidingAVG
FROM processedMesurements
ORDER BY timestamp DESC
LIMIT 10080;

.output window-300.csv
SELECT timestamp - 2208988800, value AS slidingAVG
FROM slidingaveragecache
WHERE window = 300
ORDER BY timestamp DESC
LIMIT 10080;

.output window-3600.csv
SELECT timestamp - 2208988800, value AS slidingAVG
FROM slidingaveragecache
WHERE window = 3600
ORDER BY timestamp DESC
LIMIT 10080;

EOF
} &

gnuplot <<EOF

set terminal pngcairo dashed
set output
set term pngcairo size 700,230
set datafile separator "|"
set xdata time
set xlabel "Time"
set ylabel "Radiation\nµSv/h"
set timefmt "%s"
set format x "%d.%m."
set xtics rotate

set style line 1 lc rgb "red" ps 0.1 pt 1
set style line 2 lc rgb "green" ps 0.1
set style line 3 lc rgb "blue" ps 0.1

plot "window-0.csv"  using 1:2 title "1 minute sampling interval"  ls 3, \
    "window-300.csv"  using 1:2 title "average +- 5 min"  ls 2, \
    "window-3600.csv" using 1:2 title "average +- 60 min" ls 1

EOF

rm window-3600.csv window-300.csv window-0.csv
