# WP SMTP

[![Latest Stable Version](https://poser.pugx.org/log1x/wp-smtp/v/stable)](https://packagist.org/packages/log1x/wp-smtp) [![Total Downloads](https://poser.pugx.org/log1x/wp-smtp/downloads)](https://packagist.org/packages/log1x/wp-smtp)

WP SMTP is a simple Composer package for handling WordPress SMTP with `.env`. No admin menus or [other bloat](https://blog.sucuri.net/2019/03/0day-vulnerability-in-easy-wp-smtp-affects-thousands-of-sites.html). Just a simple admin notice to verify your connection when needed and the ability to do a simple task WordPress should probably be handling natively.

## Getting Started

### Requirements

- [Sage](https://github.com/roots/sage) >= 9.0
- [Bedrock](https://github.com/roots/bedrock)
- [PHP](https://secure.php.net/manual/en/install.php) >= 7.1.3
- [Composer](https://getcomposer.org/download/)

### Installation

Install via Composer:

```sh
composer require log1x/wp-smtp
```

## Usage

### Configuration

All configuration goes into `.env`.

#### Required

```conf
WP_SMTP_HOST=mail.example.com  # Host
WP_SMTP_USERNAME=example       # Username
WP_SMTP_PASSWORD=secure123     # Password
```

#### Optional

```conf
WP_SMTP_PORT=587                      # Port
WP_SMTP_PROTOCOL=tls                  # Protocol
WP_SMTP_TIMEOUT=10                    # Timeout
WP_SMTP_FORCEFROM=example@example.com # Force From Email
WP_SMTP_FORCEFROMNAME=Example         # Force From Name
```

#### Mailhog

```conf
WP_SMTP_HOST=localhost
WP_SMTP_PORT=1025
WP_SMTP_PROTOCOL=false
```

## Debugging

> SMTP connect() failed.

This error means the initial connection to your host/port failed.

## Contributing

Contributing whether it be through PRs, reporting an issue, or suggesting an idea is encouraged and appreciated. When contributing code, please follow the existing code style.

If you're feeling generous, I also take contributions in the form of [coffee & energy drinks](https://www.buymeacoffee.com/log1x).

## License

WP SMTP is provided under the [MIT License](https://github.com/log1x/wp-smtp/blob/master/LICENSE.md).
