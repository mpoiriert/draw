# Application

This bundle is used to integrate draw/* component in Symfony.

Everything is configurable via the **draw_framework_extra** namespace.

Some sections are enabled by default if the corresponding draw component is available.

Other will need you to enabled them manually base on the fact they need configuration, or they will
create useless side effect (load, database structure, etc.) if they are not needed.

## Cron

You can configure cron base on the environment configuration or an **enabled** setting.

This is mainly useful if you want to configure the cron in the project and during your deployment flow you call the
command to dump the cron file with the proper environment configure.

**It's not intent to run the cron, it's just to allow to centralize configuration.**

### Configuration

Here is a sample of the configuration:

```YAML
parameters:
    cron.console.execution: "www-data php %kernel.project_dir%/bin/console"
    cron.context.enabled: true

draw_framework_extra:
    cron:
        jobs:
            acme_cron:
                description: "Execute acme:command every 5 minutes"
                command: "%cron.console.execution% acme:command"
                expression: "*/5 * * * *"
                output: ">/dev/null 2>&1" #This is the default value
                enabled: "%cron.context.enabled%"
```

The command ```draw:cron:dump-to-file``` will dump something like this

```
#Description: Execute acme:command every 5 minutes
* * * * * www-data php /var/www/acme/bin/console acme:command >/dev/null 2>&1
```

### Command

The command to dump the file is ```draw:cron:dump-to-file```.

If you want to dump it the first time or in a new file path you can simply do this:

```
bin/console draw:cron:dump-to-file /path/to/the/file
```

It will throw an exception if the file already exists. If you want to override it, simply use the --override option.

```
bin/console draw:cron:dump-to-file /path/to/the/file --override
```

Normally you should integrate this in your deployment pipeline.
