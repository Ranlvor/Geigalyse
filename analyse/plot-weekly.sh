#!/bin/sh
mkfifo window-3600-$$.csv
mkfifo window-300-$$.csv
mkfifo window-0-$$.csv
{
sqlite3 ../db/results.db <<EOF

.timeout 60000
.output window-0-$$.csv
SELECT timestamp - 2208988800, mysvph AS slidingAVG
FROM processedMesurements
ORDER BY timestamp DESC
LIMIT 10080;

.output window-300-$$.csv
SELECT timestamp - 2208988800, value AS slidingAVG
FROM slidingaveragecache
WHERE window = 300
ORDER BY timestamp DESC
LIMIT 10080;

.output window-3600-$$.csv
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
set term pngcairo size 700,280
set datafile separator "|"
set xdata time
set xlabel "Time"
set ylabel "Radiation\nµSv/h"
set timefmt "%s"
set format x "%d.%m."
set xtics rotate
set yrange [0:]
set tmargin 2
set bmargin 5
#I do not know how and why the following line works, it is adapted from https://stackoverflow.com/questions/10834037/gnuplot-adjust-size-of-key-legend
set key above width -11 vertical maxrows 1

set style line 1 lc rgb "red" ps 0.1 pt 1
set style line 2 lc rgb "green" ps 0.1
set style line 3 lc rgb "blue" ps 0.1

plot "window-0-$$.csv"  using 1:2 notitle  ls 3, \
    "window-300-$$.csv"  using 1:2 notitle ls 2, \
    "window-3600-$$.csv" using 1:2 ls 1 notitle, \
    0/0 title "1 minute sampling interval" with points ls 3 ps 1, \
    0/0 title "average +- 5 min"  with points ls 2 ps 1, \
    0/0 title "average +- 60 min" with points ls 1 ps 1

EOF

rm window-3600-$$.csv window-300-$$.csv window-0-$$.csv
