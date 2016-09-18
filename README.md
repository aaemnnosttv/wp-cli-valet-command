# WP-CLI Valet Command

Harness the power of Laravel Valet for creating fully functional WordPress installs in seconds.

[![Build Status](https://travis-ci.org/aaemnnosttv/wp-cli-valet-command.svg?branch=master)](https://travis-ci.org/aaemnnosttv/wp-cli-valet-command)

Quick links: [Using](#using) | [Installing](#installing) | [Contributing](#contributing)

## Using

This package implements the following commands:

### wp valet new

Create a new WordPress install -- fast

~~~
wp valet new <domain> [--project=<project>] [--version=<version>] [--locale=<locale>] [--db=<db>] [--dbname=<dbname>] [--dbuser=<dbuser>] [--dbpass=<dbpass>] [--dbprefix=<dbprefix>] [--admin_user=<username>] [--admin_password=<password>] [--admin_email=<email>] [--unsecure]
~~~

**OPTIONS**
	<domain>
		Site domain name without TLD.  Eg:  example.com = example

	[--project=<project>]
		Composer project to use instead of vanilla WordPress.
		---
		default: wp
		options:
		  - wp
		  - bedrock
		---

	[--version=<version>]
		WordPress version to install
		---
		default: latest
		---

	[--locale=<locale>]
		Select which language you want to install

	[--db=<db>]
		Database driver
		---
		default: mysql
		options:
		  - mysql
		  - sqlite
		---

	[--dbname=<dbname>]
		Database name (MySQL only). Default: 'wp_{domain}'

	[--dbuser=<dbuser>]
		Database User (MySQL only)
		---
		default: root
		---

	[--dbpass=<dbpass>]
		Set the database user password (MySQL only).
		---
		Default: ''
		---

	[--dbprefix=<dbprefix>]
		Set the database table prefix. Default: 'wp_'
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
		Provisions the site for http rather than https.



### wp valet destroy

Blow away an installation.

~~~
wp valet destroy <name> [--yes]
~~~

**OPTIONS**

	<name>
		Site domain name without TLD.  Eg:  example.com = example

	[--yes]
		Pre-approve the confirmation to delete all files and drop database.

## Installing

Installing this package requires WP-CLI v0.23.0 or greater. Update to the latest stable release with `wp cli update`.

Once you've done so, you can install this package with `wp package install aaemnnosttv/wp-cli-valet-command`.

## Contributing

We appreciate you taking the initiative to contribute to this project.

Contributing isn’t limited to just code. We encourage you to contribute in the way that best fits your abilities, by writing tutorials, giving a demo at your local meetup, helping other users with their support questions, or revising our documentation.

### Reporting a bug

Think you’ve found a bug? We’d love for you to help us get it fixed.

Before you create a new issue, you should [search existing issues](https://github.com/aaemnnosttv/wp-cli-valet-command/issues?q=label%3Abug%20) to see if there’s an existing resolution to it, or if it’s already been fixed in a newer version.

Once you’ve done a bit of searching and discovered there isn’t an open or fixed issue for your bug, please [create a new issue](https://github.com/aaemnnosttv/wp-cli-valet-command/issues/new) with the following:

1. What you were doing (e.g. "When I run `wp post list`").
2. What you saw (e.g. "I see a fatal about a class being undefined.").
3. What you expected to see (e.g. "I expected to see the list of posts.")

Include as much detail as you can, and clear steps to reproduce if possible.

### Creating a pull request

Want to contribute a new feature? Please first [open a new issue](https://github.com/aaemnnosttv/wp-cli-valet-command/issues/new) to discuss whether the feature is a good fit for the project.

Once you've decided to commit the time to seeing your pull request through, please follow our guidelines for creating a pull request to make sure it's a pleasant experience:

1. Create a feature branch for each contribution.
2. Submit your pull request early for feedback.
3. Include functional tests with your changes. [Read the WP-CLI documentation](https://wp-cli.org/docs/pull-requests/#functional-tests) for an introduction.
4. Follow the [WordPress Coding Standards](http://make.wordpress.org/core/handbook/coding-standards/).


*This README.md is generated dynamically from the project's codebase using `wp scaffold package-readme` ([doc](https://github.com/wp-cli/scaffold-package-command#wp-scaffold-package-readme)). To suggest changes, please submit a pull request against the corresponding part of the codebase.*
