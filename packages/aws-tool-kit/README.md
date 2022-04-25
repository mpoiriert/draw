# Aws Tool Kit

Multiple tools related to Aws infrastructure.

## draw:aws:cloud-watch-logs:download

Allow to download a cloud watch log locally.

A user case would be to download slow query log from a rds cluster and aggregate them locally

    bin/console draw:aws:cloud-watch-logs:download /aws/rds/cluster/prod-dbcluster/slowquery prod-1 ./tmp/slow-log.log
    bin/console draw:aws:cloud-watch-logs:download /aws/rds/cluster/prod-dbcluster/slowquery prod-2 ./tmp/slow-log.log --fileMode=a+
    
## --aws-newest-instance-role

Allowing to ignore a command if it's not the newest instance in a pool of instance base on it's role.

Sometimes it's complex to configure cron that should be executed only on one instance in a pool of server that are
auto-scaling. A good way to do that is to check if the current instance is the newest one, that way only one instance
can be the newest at a specific time.

    bin/console acme:purge-database --aws-newest-instance-role=prod
