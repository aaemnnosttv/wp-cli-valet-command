### Prerequisites

This command leverages [Laravel Valet](https://laravel.com/docs/valet) -- an open source development environment for Mac + \*nix minimalists. 

It runs various commands lightning fast, allowing you to spin up a site in your browser immediately after creating it, without any other configuration, all from a single command.

You should also understand how Valet works, especially the portion on [Serving Sites](https://laravel.com/docs/5.2/valet#serving-sites).

#### Environment Setup
0) MacOS users should install [Homebrew](https://brew.sh/) first.

1) Follow the [Valet installation instructions](https://laravel.com/docs/valet#installation) on the Laravel documentation to get started.

> _Note: Linux users should use [Valet-linux](https://github.com/cpriego/valet-linux) instead, a fork of the original project that shares most of the same `valet` commands powering this `wp-cli` plugin._

2) Using this package also requires [WP-CLI](http://wp-cli.org/), v0.23.0 or greater. 

Confirm your `wp-cli` environment works by running `wp cli info` and proceed if the output looks something like:
```
PHP binary:	/usr/bin/php7.0
PHP version:	7.0.22-0ubuntu0.16.04.1
php.ini used:	/etc/php/7.0/cli/php.ini
WP-CLI root dir:	phar://wp-cli.phar
WP-CLI vendor dir:	phar://wp-cli.phar/vendor
WP_CLI phar path:	/home/user/wp-cli-valet-command
WP-CLI packages dir:	/home/user/.wp-cli/packages/
WP-CLI global config:	/home/user/.wp-cli/config.yml
WP-CLI project config:	
WP-CLI version:	1.4.1
```

Update, if needed, to the latest stable release with `wp cli update`.

#### Loading the wp-cli-valet-command package

Once you've set up your environment, you can install this package with `wp package install aaemnnosttv/wp-cli-valet-command`.

### Troubleshooting

`Error: ERROR 1045 (28000): Access denied for user 'root'@'localhost'`
The installer halts at the database creation stage because it doesn't have a password for your local `MySQL` instance.

Prevent this from happening by appending your `wp valet` commands like such: `wp valet new site --dbpass=local_root_password`.

At this point, you can: 1) Either create a `wp-config.php` file manually or use the `wp config` command, or 2) use `wp valet destroy site` and try again using the `--dbpass` attribute.
