# Backup Packages

## Description

This composer plugin check for each plugin if a default config file is available and ask the user if they want to install it.

## Plugin default configs

All the config files are [available on Github](https://github.com/BeAPI/bea-plugin-defaults).

Here the list of currently available configs :

* `default-acf-gmaps-key.php` (ACF Pro),
* `default-add-to-any.php` (Add to any),
* `default-autoptimize.php` (Autoptimize),
* `default-bbq.php` (BBQ Pro),
* `default-bwp-minify.php` (BWP Minify),
* `default-cookie-notice.php` (Cookie Notice),
* `default-custom-order-taxonomy-ne.php` (Custom Order Taxonomy NE),
* `default-mlp.php` (MultilingualPress),
* `default-open-external-links.php` (Open external links in a new window),
* `default-optimus.php` (Optimus),
* `default-wp-deffer.php` (WP Deffered Javascript),
* `default-wp-pagenavi.php` (WP Pagenavi),
* `default-wpseo.php` (WordPress Seo)

## Installation

*The OAuth and Composer Auth configuration steps should be done for the first installation. After that you can skip them and go directly to step 3.*

### 1. OAuth

Since this command is hosted on a private repository composer need an OAuth access to be able to download and install it.

1. Go to your account setting : https://bitbucket.org/account/
1. Go to `OAuth` under `Access Management`
1. Click `Add consumer`
1. Choose a `Name`
1. Set `Callback URL` to `https://example.com`
1. Check `Read` permissions for `Projects`. This should automatically check the `Read` permissions for `Repositories`.
1. Click `Save`

### 2. Composer Auth

We need to set the OAuth key and secret we just created for composer. See the [composer documentation for more details](https://getcomposer.org/doc/05-repositories.md#bitbucket-driver-configuration).

1. In your terminal run `nano ~/.composer/auth.json`
1. Add your credentials for Bitbucket :
```JSON
  "bitbucket-oauth": {
        "bitbucket.org": {
            "consumer-key": "XXXXXXXXXXXXX",
            "consumer-secret": "XXXXXXXXXXXXXXXXXXXXXXX"
        }
    }
```
1. Save and close the file

### 3. Package installation

You can now install the command globally.

1. Add the bitbucket repository in your global `repositories` list
```
composer config -g repositories.composerbackuppackages vcs https://bitbucket.org/beapi/composer-plugins-defaults
```
1. Install the command
```
composer global require beapi/composer-plugins-defaults
```