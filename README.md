WP-CLI Valet Command
====================

Quick links: [Installing](#installing) | [Contributing](#contributing)

## Overview
```
NAME

  wp valet

DESCRIPTION

  Zonda is golden.

SYNOPSIS

  wp valet <command>

SUBCOMMANDS

  new      Create a new WordPress install -- fast

```

## `new`

Currently the only valet subcommand, this command can spin up a new WordPress installation, complete with DB, and ready to use in your browser over https faster than you can put your pants on.

You should have some understanding of how Valet works, especially the portion on [Serving Sites](https://laravel.com/docs/5.2/valet#serving-sites)

From the terminal, within a Valet "parked" directory, you can now run `wp valet new my-project`
Once complete, you can now view your new site at https://my-project.dev.

## Installing

### Prerequisites
This command leverages [Laravel Valet](https://laravel.com/docs/5.2/valet#installation) which is installed globally via Composer.
Follow the [installation instructions](https://laravel.com/docs/5.2/valet#installation) on the Laravel documentation to get started.

## Database Options
New sites create a new MySQL database by default, but the `new` command also supports using [SQLite](https://www.sqlite.org/) for a completely portable install. Simply add `--db=sqlite` when running `wp valet new`.

Installing this package requires WP-CLI v0.23.0 or greater. Update to the latest stable release with `wp cli update`.

Once you've done so, you can install this package with `wp package install aaemnnosttv/wp-cli-valet-command`

## Contributing

Code and ideas are more than welcome.

Please [open an issue](https://github.com/aaemnnosttv/wp-cli-valet-command/issues) with questions, feedback, and violent dissent.
