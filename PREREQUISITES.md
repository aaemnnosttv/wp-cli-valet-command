### Prerequisites

This command leverages [Laravel Valet](https://laravel.com/docs/valet) -- an open source development environment for Mac + \*nix minimalists. It runs various commands lightning fast, allowing you to spin up a site in your browser immediately after creating it, without any other configuration, all from a single command.

You should also understand how Valet works, especially the portion on [Serving Sites](https://laravel.com/docs/5.2/valet#serving-sites).

#### DevEnv Set-Up
1) MacOS users should set up [Homebrew](https://brew.sh/) first. 

2) Follow the [Valet installation instructions](https://laravel.com/docs/valet#installation) on the Laravel documentation to get started.

> _Note: Linux users are encouraged to use [Valet-linux](https://github.com/cpriego/valet-linux) instead, a fork of the original project that shares the same `valet` commands powering this `wp-cli` plugin._

3) Using this package also requires [WP-CLI](http://wp-cli.org/), v0.23.0 or greater. Update, if needed, to the latest stable release with `wp cli update`.

4) Your credentials to create a database should also be stored in `~/my.cnf`.

#### Loading the wp-cli-valet-command package

Once you've done so, you can install this package with `wp package install aaemnnosttv/wp-cli-valet-command`.