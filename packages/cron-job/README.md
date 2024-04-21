# Cron Job

This library is used to manage and process cron jobs from the database.

The cron are sent to a queue and processed by a worker via symfony messenger.

## Configuration

The package can be enabled as follows:
> **_NOTE:_** Below are the default configs which can be overwritten by your needs.

### Framework extra

```yaml
draw_framework_extra:
  # ...
  cron_job:
    enabled: true
    doctrine:
      orm:
        mappings:
          DrawCronJob:
            is_bundle: false
            type: attribute
            dir: ...
            prefix: Draw\Component\CronJob\Entity
```

> **_NOTE:_** The following services are available:
> - **draw.cron_job.command.queue_cron_job_by_name_command**: Draw\Component\CronJob\Command\QueueCronJobByNameCommand
> - **draw.cron_job.command.queue_due_cron_jobs_command**: Draw\Component\CronJob\Command\QueueDueCronJobsCommand
> - **draw.cron_job.cron_job_processor**: Draw\Component\CronJob\CronJobProcessor
> - **draw.cron_job.message_handler.execute_cron_job_message_handler**: Draw\Component\CronJob\MessageHandler\ExecuteCronJobMessageHandler

### Sonata integration

```yaml
draw_sonata_integration:
  cron_job:
    enabled: true
    admin:
      cron_job:
        group: Cron Job
        entity_class: Draw\Component\CronJob\Entity\CronJob
        controller_class: Draw\Bundle\SonataIntegrationBundle\CronJob\Controller\CronJobController
        icon: fas fa-clock
        label: Cron Job
        pager_type: simple
        show_in_dashboard: true
        translation_domain: SonataAdminBundle
      cron_job_execution:
        group: Cron Job
        entity_class: Draw\Component\CronJob\Entity\CronJobExecution
        controller_class: sonata.admin.controller.crud
        icon: null
        label: Cron Job Execution
        pager_type: simple
        show_in_dashboard: true
        translation_domain: SonataAdminBundle
```

### Messenger

You need to configure the routing for the messenger component for the message that will be used to process the cron jobs.

```yaml
framework:
  messenger:
    routing:
      Draw\Component\CronJob\Message\ExecuteCronJobMessage: 'async'
```

## Usage

Once the package is enabled, a new admin page will be available - **Cron Job**. The package also provides
2 console commands:
- **draw:cron-job:queue-due** - it is used to process due cron jobs by their configs; it should be configured as a cron to be executed with * * * * *
- **draw:cron-job:queue-by-name** - it allows to manually process a cron job by its name passed as an argument
