Coding Standard
===============

This document describes the coding standard for the project.

## Fluent Interface

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

## Event Listener

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