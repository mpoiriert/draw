DrawCommandBundle
=================

This bundle allows triggering command from the sonata admin. It also logs every command execution in the database even
if they have been started from the command line.

## Configuration

The configuration lists the commands available from Sonata to be selected and executed:

```YAML
draw_command:
    sonata:
        commands:
            clearCache:
                commandName: "redis:flushdb"
                label: "Clear Cache"
                icon: "fa-ban"
            reIndexSearch:
                commandName: "fos:elastica:populate"
                label: "Re-Index Search"
                icon: "fa-search-plus"
```

## Logging command execution

All command execution are log in the databases. They also log the output, so you can debug them in case an error happen.

If you want to proper log the output from command line interface you must use the BufferedConsoleOutput for you output
in bin/console:

```PHP
$application->run($input, new \Draw\Bundle\CommandBundle\Console\Output\BufferedConsoleOutput());
```

Some predefined command are ignored by the command logging. This is a predefined list:

 - help
 - doctrine:database:drop
 - doctrine:database:create
 - cache:clear

## Todo

 - Allow to configure the list of command that are ignored form the logger
 - Allow to have argument on the command execution, could be predefined from the configuration, 
   an input in the admin, or a more complex input done by reverse engineering the arguments of a command