# recaptcha-for-wp
Invisible reCAPTCHA integration for the WordPress login.

## Requirements
WordPress 4.7 or later, PHP 5.4 or later and Composer.

## Installation
```
$ composer require ssnepenthe/recaptcha-for-wp
```

*OR*

```
$ cd /path/to/project/wp-content/plugins
$ git clone git@github.com:ssnepenthe/recaptcha-for-wp.git
$ cd recaptcha-for-wp
$ composer install
```

## Usage
To use this plugin you must provide API keys from reCAPTCHA.

First visit [reCAPTCHA](https://www.google.com/recaptcha/intro/android.html), register your site and get your keys.

Then activate the plugin and provide your keys under `settings > reCAPTCHA`.

That's it! reCAPTCHA is automatically enabled for the login, lost password and registration forms.

## Configuration
Plugin settings can be overridden via the following constants:

`RFW_LOGIN`: whether to integrate reCAPTCHA with the login form. Must be a string, "1" (for enabled) or "0" (for disabled).

`RFW_LOSTPASSWORD`: whether to integrate reCAPTCHA with the lost password form. Must be a string, "1" (for enabled) or "0" (for disabled).

`RFW_REGISTRATION`: whether to integrate reCAPTCHA with the registration form. Must be a string, "1" (for enabled) or "0" (for disabled).

`RFW_SECRET_KEY`: the "secret" API key provided by reCAPTCHA. Must be a string.

`RFW_SITE_KEY`: the "site" API key provided by reCAPTCHA. Must be a string.

## Considerations
If you have any browser extensions installed for privacy (such as [Privacy Badger](https://www.eff.org/privacybadger)) you may want to whitelist your domain.

If you enter either of your API keys incorrectly, it is possible to get locked out of your site. You should be able to work around this by setting the corresponding constant.

## Compatibility
The plugin is tested with the [Google Authenticator plugin](https://wordpress.org/plugins/google-authenticator/) and the [GA Per-User Prompt plugin](https://wordpress.org/plugins/google-authenticator-per-user-prompt/).

It should work, but is not tested with any other plugins that modify `wp-login.php`.
