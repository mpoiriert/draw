`help`
------

Display help for a command

### Usage

* `help [--format FORMAT] [--raw] [--] [<command_name>]`

The help command displays help for a given command:

  /home/wwwroot/vendor/phpunit/phpunit/phpunit help list

You can also output the help in other formats by using the --format option:

  /home/wwwroot/vendor/phpunit/phpunit/phpunit help --format=xml list

To display the list of available commands, please use the list command.

### Arguments

#### `command_name`

The command name

* Is required: no
* Is array: no
* Default: `'help'`

### Options

#### `--format`

The output format (txt, xml, json, or md)

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `'txt'`

#### `--raw`

To output raw command help

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--help|-h`

Display help for the given command. When no command is given display help for the list command

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--ansi|--no-ansi`

Force (or disable --no-ansi) ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: yes
* Default: `NULL`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

`list`
------

List commands

### Usage

* `list [--raw] [--format FORMAT] [--short] [--] [<namespace>]`

The list command lists all commands:

  /home/wwwroot/vendor/phpunit/phpunit/phpunit list

You can also display the commands for a specific namespace:

  /home/wwwroot/vendor/phpunit/phpunit/phpunit list test

You can also output the information in other formats by using the --format option:

  /home/wwwroot/vendor/phpunit/phpunit/phpunit list --format=xml

It's also possible to get raw list of commands (useful for embedding command runner):

  /home/wwwroot/vendor/phpunit/phpunit/phpunit list --raw

### Arguments

#### `namespace`

The namespace name

* Is required: no
* Is array: no
* Default: `NULL`

### Options

#### `--raw`

To output raw command list

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--format`

The output format (txt, xml, json, or md)

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `'txt'`

#### `--short`

To skip describing commands' arguments

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--help|-h`

Display help for the given command. When no command is given display help for the list command

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--ansi|--no-ansi`

Force (or disable --no-ansi) ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: yes
* Default: `NULL`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

`_complete`
-----------

Internal command to provide shell completion suggestions

### Usage

* `_complete [-s|--shell SHELL] [-i|--input INPUT] [-c|--current CURRENT] [-S|--symfony SYMFONY]`

Internal command to provide shell completion suggestions

### Options

#### `--shell|-s`

The shell type ("bash")

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--input|-i`

An array of input tokens (e.g. COMP_WORDS or argv)

* Accept value: yes
* Is value required: yes
* Is multiple: yes
* Is negatable: no
* Default: `array ()`

#### `--current|-c`

The index of the "input" array that the cursor is in (e.g. COMP_CWORD)

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--symfony|-S`

The version of the completion script

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--help|-h`

Display help for the given command. When no command is given display help for the list command

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--ansi|--no-ansi`

Force (or disable --no-ansi) ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: yes
* Default: `NULL`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

`completion`
------------

Dump the shell completion script

### Usage

* `completion [--debug] [--] [<shell>]`

The completion command dumps the shell completion script required
to use shell autocompletion (currently only bash completion is supported).

Static installation
-------------------

Dump the script to a global completion file and restart your shell:

    /home/wwwroot/vendor/phpunit/phpunit/phpunit completion bash | sudo tee /etc/bash_completion.d/phpunit

Or dump the script to a local file and source it:

    /home/wwwroot/vendor/phpunit/phpunit/phpunit completion bash > completion.sh

    # source the file whenever you use the project
    source completion.sh

    # or add this line at the end of your "~/.bashrc" file:
    source /path/to/completion.sh

Dynamic installation
--------------------

Add this to the end of your shell configuration file (e.g. "~/.bashrc"):

    eval "$(/home/wwwroot/vendor/phpunit/phpunit/phpunit completion bash)"

### Arguments

#### `shell`

The shell type (e.g. "bash"), the value of the "$SHELL" env var will be used if this is not given

* Is required: no
* Is array: no
* Default: `NULL`

### Options

#### `--debug`

Tail the completion debug log

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--help|-h`

Display help for the given command. When no command is given display help for the list command

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--ansi|--no-ansi`

Force (or disable --no-ansi) ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: yes
* Default: `NULL`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

`draw:console:generate-documentation`
-------------------------------------

Generate a documentation for all the command of the application.

### Usage

* `draw:console:generate-documentation [--aws-newest-instance-role AWS-NEWEST-INSTANCE-ROLE] [--draw-execution-id DRAW-EXECUTION-ID] [--draw-execution-ignore] [--] <path>`

Generate a documentation for all the command of the application.

### Arguments

#### `path`

The path where the documentation will be generated.

* Is required: yes
* Is array: no
* Default: `NULL`

### Options

#### `--aws-newest-instance-role`

The instance role the server must be the newest of to run the command.

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--draw-execution-id`

The existing execution id of the command. Use internally by the DrawCommandBundle.

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--draw-execution-ignore`

Flag to ignore login of the execution to the databases.

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--help|-h`

Display help for the given command. When no command is given display help for the list command

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--ansi|--no-ansi`

Force (or disable --no-ansi) ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: yes
* Default: `NULL`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

