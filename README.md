aaemnnosttv/wp-cli-valet-command
================================

White-glove services for turn-key installs in seconds.

[![Travis Build](https://img.shields.io/travis/aaemnnosttv/wp-cli-valet-command/master.svg)](https://travis-ci.org/aaemnnosttv/wp-cli-valet-command) [![Packagist](https://img.shields.io/packagist/v/aaemnnosttv/wp-cli-valet-command.svg)](https://packagist.org/packages/aaemnnosttv/wp-cli-valet-command)

Quick links: [Using](#using) | [Installing](#installing) | [Contributing](#contributing) | [Support](#support)

## Using

This package implements the following commands:

### wp valet new

Create a new WordPress install -- fast

~~~
wp valet new <name> [--project=<project>] [--in=<dir>] [--version=<version>] [--locale=<locale>] [--db=<db>] [--dbname=<dbname>] [--dbuser=<dbuser>] [--dbpass=<dbpass>] [--dbprefix=<dbprefix>] [--admin_user=<username>] [--admin_password=<password>] [--admin_email=<email>] [--unsecure] [--portable]
~~~

This command will spin up a new WordPress installation -- complete with database and https
_ready-to-use in your browser_ faster than you can put your pants on.

**OPTIONS**

	<name>
		Site domain name without TLD. This will become the directory name of the project root.
		Eg: To create an install for example.dev, `wp valet new example`

	[--project=<project>]
		The WordPress project to install. Choose from any project supported by Laravel Valet.
		---
		default: wp
		options:
		  - wp
		  - bedrock
		---

	[--in=<dir>]
		Specify the path to the parent directory to create the install in.
		Defaults to the current working directory.

	[--version=<version>]
		WordPress version to install.
		---
		default: latest
		---

	[--locale=<locale>]
		Select which language you want to install.

	[--db=<db>]
		Database driver to provision the site with.
		---
		default: mysql
		options:
		  - mysql
		  - sqlite
		---

	[--dbname=<dbname>]
		Database name (MySQL only).
		Defaults to 'wp_<name>'.

	[--dbuser=<dbuser>]
		Database User (MySQL only).
		---
		default: root
		---

	[--dbpass=<dbpass>]
		Set the database user password (MySQL only).
		---
		Default: ''
		---

	[--dbprefix=<dbprefix>]
		Set the database table prefix.
		---
		default: 'wp_'
		---

	[--admin_user=<username>]
		The username to create for the WordPress admin user.
		---
		default: admin
		---

	[--admin_password=<password>]
		The password to create for the WordPress admin user.
		---
		default: admin
		---

	[--admin_email=<email>]
		The email to use for the WordPress admin user.

	[--unsecure]
		Provision the site for http rather than https.

	[--portable]
		Provision the site to be portable. Implies --unsecure and --db=sqlite.



### wp valet destroy

Completely remove an installation.

~~~
wp valet destroy <name> [--yes]
~~~

This will drop the database, and delete all of the files as well as
remove any self-signed TLS certificate that was generated for serving
this install over https.

**OPTIONS**

	<name>
		Site domain name without TLD. It should match the directory name of the project root.

	[--yes]
		Pre-approve the confirmation to delete all files and drop the database.

## Installing

### Prerequisites

This command leverages [Laravel Valet](https://laravel.com/docs/valet) -- an open source development environment for Mac + \*nix minimalists. It runs various commands lightning fast, allowing you to spin up a site in your browser immediately after creating it, without any other configuration, all from a single command.

You should also have some understanding of how Valet works, especially the portion on [Serving Sites](https://laravel.com/docs/5.2/valet#serving-sites).

#### DevEnv Set-Up
1) MacOS users should set up [Homebrew](https://brew.sh/) first. 

2) Follow the [Valet installation instructions](https://laravel.com/docs/valet#installation) on the Laravel documentation to get started.

> _Note: Linux users are encouraged to use [Valet-linux](https://github.com/cpriego/valet-linux) instead, a fork of the original project that shares the same `valet` commands powering this `wp-cli` plugin._

3) Using this package also requires [WP-CLI](http://wp-cli.org/), v0.23.0 or greater. Update, if needed, to the latest stable release with `wp cli update`.

4) Your credentials to create a database should also be stored in `~/my.cnf`.

#### Loading the wp-cli-valet-command package

Once you've done so, you can install this package with `wp package install aaemnnosttv/wp-cli-valet-command`.

Installing this package requires WP-CLI v1.3.0 or greater. Update to the latest stable release with `wp cli update`.

Once you've done so, you can install this package with:

    wp package install git@github.com:aaemnnosttv/wp-cli-valet-command.git

## Contributing

We appreciate you taking the initiative to contribute to this project.

Contributing isn’t limited to just code. We encourage you to contribute in the way that best fits your abilities, by writing tutorials, giving a demo at your local meetup, helping other users with their support questions, or revising our documentation.

For a more thorough introduction, [check out WP-CLI's guide to contributing](https://make.wordpress.org/cli/handbook/contributing/). This package follows those policy and guidelines.

### Reporting a bug

Think you’ve found a bug? We’d love for you to help us get it fixed.

Before you create a new issue, you should [search existing issues](https://github.com/aaemnnosttv/wp-cli-valet-command/issues?q=label%3Abug%20) to see if there’s an existing resolution to it, or if it’s already been fixed in a newer version.

Once you’ve done a bit of searching and discovered there isn’t an open or fixed issue for your bug, please [create a new issue](https://github.com/aaemnnosttv/wp-cli-valet-command/issues/new). Include as much detail as you can, and clear steps to reproduce if possible. For more guidance, [review our bug report documentation](https://make.wordpress.org/cli/handbook/bug-reports/).

### Creating a pull request

Want to contribute a new feature? Please first [open a new issue](https://github.com/aaemnnosttv/wp-cli-valet-command/issues/new) to discuss whether the feature is a good fit for the project.

Once you've decided to commit the time to seeing your pull request through, [please follow our guidelines for creating a pull request](https://make.wordpress.org/cli/handbook/pull-requests/) to make sure it's a pleasant experience. See "[Setting up](https://make.wordpress.org/cli/handbook/pull-requests/#setting-up)" for details specific to working on this package locally.

## Support

Github issues aren't for general support questions, but there are other venues you can try: http://wp-cli.org/#support


*This README.md is generated dynamically from the project's codebase using `wp scaffold package-readme` ([doc](https://github.com/wp-cli/scaffold-package-command#wp-scaffold-package-readme)). To suggest changes, please submit a pull request against the corresponding part of the codebase.*
