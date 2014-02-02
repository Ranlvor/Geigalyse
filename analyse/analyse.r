

library(RSQLite) 

con <- dbConnect(SQLite(), "../db/mesurements.db") 

res <- dbSendQuery(con, "SELECT count FROM mesurements WHERE source = 2 LIMIT 500") # ORDER BY timestamp DESC LIMIT 500
data <- fetch(res)
dbClearResult(res)

rawdata = data[,1]

hist(rawdata, nclass=max(rawdata), prob=T)
lines(density(rawdata), col=2)

x <- seq(from=0, to=max(rawdata), by=0.1)
lines(x,dnorm(x, mean(rawdata), sd(rawdata)), col=3)
savePlot(filename="plot-500-mesurements.png", type="png")

