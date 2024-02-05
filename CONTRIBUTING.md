Contributing
============

All projects are using [Docker](https://www.docker.com/) and [Docker Compose](https://docs.docker.com/compose/).

To install the project, you need to have Docker and Docker Compose installed on your machine and Make.

We **STRONGLY** recommend using Ubuntu since there is known issue with mac and docker performance due to the file system.

Symfony use file caching and the file system performance is really important, event more in development to check cache invalidation.

## Communication

Every written communication that leave a trace should be done in English (Jira, GitHub, Slack channel, etc.)
Code is written in English.
Comments is written in English.

### Avoid acronyms

Avoid using acronyms in variable, function, method, class names, etc.

This also applies to comments, documentation, commit messages, jira tickets, emails, etc.

Some exception can be made for well-known acronyms (e.g. HTML, URL, etc.)

Acronyms might save you time to write, but they can cost a lot of time to understand for someone else.

### `MIGHT`, `SHOULD`, `MUST`

Try to use `MIGHT`, `SHOULD`, `MUST` convention to express the importance of something.

* MIGHT: A suggestion, or something that can occur in some cases.
* SHOULD: A strong suggestion, something that should be done in most cases.
* MUST: A requirement, something that must be done.

## Installation

The installation process is expecting that you are using Ubuntu and docker.

A simple `make install` will install the project, dependencies, docker containers, setup database and launch the tests.

### Domain

At the top of the `Makefile` you will find the `DOMAIN` variable.

You should add this domain to your `/etc/hosts` file.

A self-signed certificate is generated for this domain, you should add it to your browser.

### Configuration

The configuration is done using environment variables.

You MUST use a docker-compose.override.yml file to override the environment variables for your local environment.

The .env file SHOULD only contain the default values that are valid for all environments.

We prefer omit default values in the .env file to make it clear that the default value is not the one used in production.
This also force the infrastructure to provide the value per environment. If the value is not provided, the application will not work.
This prevents the application to work with a default value that is not the one that should be used in production.

Check your project documentation to know which environment variables you should override.

## Coding Standard

This document describes the coding standard for the project.

### Coding style

Different linter are configured to enforce the coding style.

Just execute `make linter` to execute the linters.

> Do not commit change blindness! Make sure to understand the change done by the fixer.

Since there is some file that we don't want to apply default indentation (E.g. Admin and Configuration)
you must ensure indentation are done properly before pushing your code.

Intention is 4 spaces.

Sonata admin use if, tab, with, you should keen readable indentation:

```php
class ExecutionAdmin extends AbstractAdmin
    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->tab('Execution')
                ->with('General')
                    ->add('id')
                    ->add('command')
                    ->add('commandName')
                    ->add('state')
                    ->add('createdAt')
                    ->add('updatedAt')
                    ->add('input', 'array')
                    ->add('autoAcknowledgeReason')
                ->end()
                ->with('Execution')
                    ->add('commandLine', 'text')
                    ->add('outputHtml', 'html')
                ->end()
            ->end();
    }
}
```

Same thing for configuration:

```php
class Configuration implements ConfigurationInterface
    private function createAutoActionNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('auto_action'))
            ->canBeEnabled()
            ->children()
                ->arrayNode('ignore_admins')
                    ->defaultValue([])
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('actions')
                    ->defaultValue(AutoActionExtension::DEFAULT_ACTIONS)
                    ->arrayPrototype()
                        ->ignoreExtraKeys(false)
                    ->end()
                ->end()
            ->end();
    }
}
```

### Naming conventions

* Prefer [camelCase](https://en.wikipedia.org/wiki/Camel_case) for PHP variables, function and method names, arguments (e.g. `$acceptableContentTypes`, `$isActivated`);
* Variables, functions and methods should have meaningful names that clearly express the intent.
* Functions and methods should start with a verb (e.g. `writeLog`, `doThing`, `performAction`, `closeThing`) in most cases. Exceptions to this rule do exist. Use your best judgment.
* Use [PascalCase](https://en.wikipedia.org/wiki/Camel_case) for class names.
* Boolean variables should be named accordingly (e.g. `$isActivated`, `$hasPermission`). Same for function and methods (`isRequestValid(Request $request)`).
* Prefer the "verbScope**State**" convention over "verb**State**Scope". The former makes it easier to organize similar variables and functions in cohesive groups. E.g.:
    * Prefer `$isEmailValid` over `$isValidEmail`
    * Prefer `isEmailBlocked($email)` over `isBlockedEmail($email)`.
* When using acronyms/code words in variable/class/function names, avoid consecutive capital letters as they make names less readable when used in conjunction with camelCase. E.g.
    * Prefer `$htmlBody` over `$HTMLBody`
    * Prefer `isHtmlValid()` over `isHTMLValid()`
    * Prefer `SqlException` over `SQLException`
* Suffix interfaces with `Interface` (e.g. `AuthenticatorInterface`)
* Suffix exceptions with `Exception` (e.g. `DatabaseException`)

### Strong typing

Use type hints for all function and method arguments and return types.

### Fluent Interface

We use fluent interface for all the classes that are not a service.

Example:

```php
<?php

class Foo
{
    public function setBar($bar): static
    {
        $this->bar = $bar;
        
        return $this;
    }
}
```

This is really useful to build object quickly for test or fixture.
It also eases the "no temporary variable" rule.

### No temporary variable

Avoid using temporary variables when it's not necessary.

```php
// Bad
$foo = $request->get('foo');

$this->doSomething($foo);

// Good
$this->doSomething($request->get('foo'));
```

This cannot be applied to all cases, but it's a good rule to follow. 
It helps the brain to not have to keep track of temporary state.

Another example with the fluent interface:

```php
// Bad
$user = new User();
$user->setFirstName('John');
$user->setLastName('Doe');

$device = new Device();
$device->setType('ios');
$device->setVersion('14.5');

$user->addDevice($device);

// Good
$user = (new User())
    ->setFirstName('John')
    ->setLastName('Doe')
    ->addDevice(
        (new Device())
            ->setType('ios')
            ->setVersion('14.5')
    );
```

### Event Listener

When in project context (not a library), we use the AsEventListener attribute to declare the event listener.

We use the `on` prefix for the event listener method name and the event name as the suffix.

The event argument name is $event.

Example:

```php
<?php

use Draw\Component\Tester\Event\BarEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class Foo
{   
    #[AsEventListener]
    public function onBarEvent(BarEvent $event): void
    {
        // ...
    }
}
```

### Avoid super long lines

Maximum line length is 120 characters.

```PHP
// Bad
$records = DB::fetchMany("SELECT * FROM sometable JOIN Stuff ON Stuff.Thing", [$user['id'], $user['locale'], $something['id']]);

// Good
$records = DB::fetchMany(
    "SELECT * FROM sometable JOIN Stuff ON Stuff.Thing", 
    [
        $user['id'],
        $user['local'],
        $something['id']
    ]
);
```

## Code Review

This is a set of guidelines to follow when reviewing code.

### Git Workflow

All code submissions should be in a **new Git Branch**. We have the following convention:

* `master`: Long Development branch
* `[0-9.]+.x`: Release branch (eg: 7.14.x)
* `<JIRA_ID>-title`: Ticket branch (eg: POR-1234-fixing-something)

JIRA_ID is the ticket number in JIRA, it can also be the GitHub issue number in case there is not jira ticket.

Don't refer to jira ticket in an unrelated project.

### Commits

Try to keep your commits small and focused. This helps the review process.
You don't have to commit every time you change a line of code, but try to keep your commits focused on a single task.

If commit B remove what you did id commit A, then commit B should be squashed into commit A. 
Since reviewer might check one commit at the time having to review commit that are not relevant anymore is a waste of time.

> Same logic apply if commit C change what commit A did, reorder your commit and squash them.

At the end commit will be squashed into a single commit before merging into the main branch.

During the review process, any change request should be done in a new commit. No interactive rebase should be done during the review process
since all the commits will be marked as new and the reviewer will have to review everything again.

### Pull Request

A pull request (PR) is a request to merge a branch into another branch.

If there is a template to fill on GitHub, make sure to fill it properly.

If your PR is not ready to review, mark it as draft.

Make sure to review your own code before requesting a review.

Once ready mark your PR as ready to review. Code owners will be notified.

Consider that when your PR is ready to review it should be ready to merge. 
If you are not sure about something, mark your PR as draft and ask for feedback.

### Change request

If a change request is done, the PR will be marked as "Changes requested".

Make sure to address all the change request before marking the PR as ready to review again.

Each change request should be done in a new commit.

Never rebase your branch during the review process unless explicitly asked by the reviewer.

Don't mark the change request as resolved yourself, let the reviewer do it.

### Merge

PR that are merge should be ready for production.

### QA/Tester

Consider that the QA/Tester is a last resort, not a first line of defense. They might not have to test everything.
Make sure to add any relevant information for the QA/Tester in the original ticket, tester don't have access to the PR.
