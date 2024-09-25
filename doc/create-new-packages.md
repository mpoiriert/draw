# Create a new package

This document describes how to create a new package for in the monorepo structure.

In this example we will create a new package called `draw/demo` and the namespace will be `Draw\Component\Demo`.

> Except for the first step, you can run `bin/console make:draw-package` to create the package.

### Step 1: Create the draw/demo repository on Github

The repository must be an exact match of the package name.

## Step 2: Create the folder

Create the folder `packages/demo` in the root of the repository.

## Step 3: Adjust the replace section of the root composer.json

Add the package to the replace section:

```json
{
    "replace": {
        "draw/demo": "self.version"
    }
}
```

This is necessary since the package is available in the main repository and should not be installed from packagist.

## Step 4: Adjust the autoload section of the root composer.json

Add the package to the autoload section:

```json
{
    "autoload": {
        "psr-4": {
            "Draw\\Component\\DemoPackage\\": "packages/demo/"
        }
    }
}
```

## Step 5: Create package folder

### Step 5.1: `packages/demo/composer.json`

```json
{
  "name": "draw/demo",
  "description": "Demo package",
  "license": "MIT",
  "type": "library",
  "keywords": ["draw", "demo"],
  "authors": [
    {
      "name": "Martin Poirier Theoret",
      "email": "mpoiriert@gmail.com"
    }
  ],
  "require": {
    "php": ">=8.2"
  },
  "require-dev": {
    "phpunit/phpunit": "^11.3"
  },
  "minimum-stability": "dev",
  "prefer-stable": true,
  "autoload": {
    "psr-4": {
      "Draw\\Component\\Demo\\": "packages/demo/"
    }
  },
  "extra": {
    "branch-alias": {
      "dev-master": "0.11-dev"
    }
  }
}
```

### Step 5.2: `packages/demo/phpunit.xml.dist`

```xml
<phpunit
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/10.0/phpunit.xsd">
    <testsuites>
        <testsuite name="Main">
            <directory>./Tests</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

### Step 6: Adjust github action build script

Edit the `.github/workflows/after_splitting_test.yaml`  to add the package in the matrix.

```yaml
jobs:
  after_split_testing:
    strategy:
      matrix:
        package_name:
          - demo
```

