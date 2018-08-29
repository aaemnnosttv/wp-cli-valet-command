`Error: ERROR 1045 (28000): Access denied for user 'root'@'localhost'`
The installer halts at the database creation stage because it doesn't have a password for your local `MySQL` instance.

Prevent this from happening by appending your `wp valet` commands like such: `wp valet new site --dbpass=local_root_password`.

At this point, you can: 
1) Either create a `wp-config.php` file manually,
2) use `wp config`command to have wp-cli create one for you, or 
3) use `wp valet destroy site` and try running your `wp valet new` command again, this time using the `--dbpass` attribute.

### Configuring Alternate Defaults
As with other `wp-cli` commands, you can set default attributes when running `wp valet`.

Simply add the appropriate details in `~/.wp-cli/config.yml`:

```yml
valet new:
  ## Uncomment or update the relevant lines when necessary to set your own defaults.
  project: wp # or bedrock
  # in: # override - defaults to current directory
  version: latest
  # locale:  # use if not English
  db: mysql # or sqlite
  # dbname: # defaults to wp_name
  dbuser: root # or any other local user capable of creating databases (MySQL only)
  # dbpass: # enter the appropriate password if necessary (MySQL only)
  dbprefix: wp_
  admin_user: admin
  admin_password: admin
  ## Boolean options can also be configured, too.
  # unsecure: false # set to true to override
  # portable: false # set to true to override
```

The `wp valet new` defaults are shown here as an example for clarity.

One simple usage for the `config.yml` could look like
```yml
valet new:
  dbuser: root # or any db creating user
  dbpass: password # set yours here
```
to enable `wp valet new site` to spin up a full, live, running local WordPress site in ~3 seconds without any additional parameters.