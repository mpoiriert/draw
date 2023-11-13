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

