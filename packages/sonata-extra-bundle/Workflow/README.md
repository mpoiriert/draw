Introduction
------------

This library add Symfony Workflow component integration within Sonata Admin.

It's base on [Yokai Sonata Workflow](https://github.com/yokai-php/sonata-workflow)

### Features

- add a menu dropdown to your admin detail pages on which you have buttons to apply available transitions
- ship a controller to apply transition

Configuration
-------------

Let say that you have an entity named `PullRequest` that is under workflow and for which you have an admin.

```yaml
# config/packages/workflow.yml
framework:
    workflows:
        pull_request:
            type: state_machine
            marking_store:
                type: state_machine
                property: status
            supports:
                - App\Entity\PullRequest
            places:
                - opened
                - pending_review
                - merged
                - closed
            initial_marking:
                - opened
            transitions:
                start_review:
                    from: opened
                    to:   pending_review
                merge:
                    from: pending_review
                    to:   merged
                close:
                    from: pending_review
                    to:   closed
```

### One extension for everything

The extension is usable for many entities and with no configuration.

You only need to enable the draw_sonata_extra workflow section and configure the list of admins that should use the extension.

For instance :

```yaml
# config/packages/draw_sonata_extra.yaml
draw_sonata_extra:
    workflow:
        enabled: true
        # This can be any compatible configuration with sonata extensions configuration, no validation is done
        sonata_admin_extensions:
            admins:
                - 'admin.pull_request'
```

### More specific extension per admin

But the extension accepts many options if you wish to customize the behavior.

For instance :

```yaml
# config/packages/sonata_admin.yml
services:
    admin.extension.pull_request_workflow:
        class: 'Draw\Bundle\SonataExtraBundle\Workflow\Extension\WorkflowExtension'
        arguments:
            - '@workflow.registry'
            - render_actions: [show]
              workflow_name: pull_request
              no_transition_label: No transition for pull request
              no_transition_icon: fa fa-times
              dropdown_transitions_label: Pull request transitions
              dropdown_transitions_icon: fa fa-archive
              transitions_default_icon: fa fa-step-forward
              transitions_icons:
                  start_review: fa fa-search
                  merge: fa fa-check
              ignore_transitions: [close] # ignore the close transition even if it is available

sonata_admin:
    extensions:
        admin.extension.pull_request_workflow:
            admins:
                - admin.pull_request
```

Or by creating a service that extends the `Draw\Bundle\SonataExtraBundle\Workflow\Extension\WorkflowExtension` class.

```php
namespace App\Admin\Extension;

use App\Admin\PullRequestAdmin;
use App\Integration\FirstNameRequest\Api\Admin\FirstNameRequestAdmin;
use Draw\Bundle\SonataExtraBundle\Workflow\Extension\WorkflowExtension;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Workflow\Registry;

#[AutoconfigureTag(
    'sonata.admin.extension',
    [
        'target' => PullRequestAdmin::class,
    ]
)]
class PullRequestWorkflowExtension extends WorkflowExtension
{
    public function __construct(
        Registry $registry,
    ) {
        parent::__construct(
            $registry,
            [
                'render_actions' => ['show']
                /* ... */
            ]
        );
    }
}
```

What are these options ?

- `render_actions` : Admin action names on which the extension should render its menu (defaults to `[show, edit]`)
- `workflow_name` : The name of the Workflow to handle (defaults to `null`)
- `no_transition_display` : Whether to display a button when no transition is enabled (defaults to `false`)
- `no_transition_label` : The button label when no transition is enabled (defaults to `workflow_transitions_empty`)
- `no_transition_icon` : The button icon when no transition is enabled (defaults to `fa fa-code-fork`)
- `dropdown_transitions_label` : The dropdown button label when there is transitions enabled (defaults to `workflow_transitions`)
- `dropdown_transitions_icon` : The dropdown button icon when there is transitions enabled (defaults to `fa fa-code-fork`)
- `transitions_default_icon` : The default transition icon for all transition (defaults to `null` : no icon)
- `transitions_icons` : A hash with transition name as key and icon as value (defaults to `[]`)
- `ignore_transitions` : A list of transitions to ignore even if they are available (defaults to `[]`)
- `admin_action_class` : The admin action (defaults to `Draw\Bundle\SonataExtraBundle\Workflow\Action\WorkflowTransitionAction`)
- `controller` : The controller to use for the transition (defaults to `Draw\Bundle\SonataExtraBundle\Workflow\Action\WorkflowTransitionAction`)
