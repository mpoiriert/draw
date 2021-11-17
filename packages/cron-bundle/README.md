Draw Cron Bundle
================

This bundle is use to configure cron job that can be dump into a compatible cron job file format.
You can configure cron base on the environment configuration or a **enabled** setting.

This is mainly useful if you want to configure the cron in the project and during your deployment flow you call the
command to dump the cron file with the proper environment configure.

**This bundle does no intent to run the cron, it's just to allow a centralize configuration.**

## Configuration

Here is a sample of the configuration:

```YAML
parameters:
    cron.console.execution: "www-data php %kernel.project_dir%/bin/console"
    cron.context.enabled: true

draw_cron:
    jobs:
        acme_cron:
            description: "Execute acme:command every 5 minutes"
            command: "%cron.console.execution% acme:command"
            expression: "*/5 * * * *"
            output: ">/dev/null 2>&1" #This is the default value
            enabled: "%cron.context.enabled%"
```

This would output something like this:

```
#Description: Execute acme:command every 5 minutes
* * * * * www-data php /var/www/acme/bin/console acme:command >/dev/null 2>&1

```

## Command

The command to dump the file is *draw:cron:dump-to-file*.

If you want to dump it the first time or in a new file path you can simply do this:

```
bin/console draw:cron:dump-to-file /path/to/the/file
```

It will throw a exception if the file already exists. If you want to override it simply
add the --override option.

```
bin/console draw:cron:dump-to-file /path/to/the/file --override
```

Normally you should integrate this in you deployment pipeline