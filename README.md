# CatecheSis

This project contains the code for the CatecheSis application. The application supports localization via simple PHP translation files located in the `locale` directory.

## Available locales

Two locale files ship with the repository:

- **pt_PT** – European Portuguese (default)
- **pt_BR** – Brazilian Portuguese

The active locale is selected at runtime based on the configuration value `LOCALIZATION_CODE`.

## Adding new translation keys

1. Edit `locale/pt_PT.php` and add the desired key/value pair to the `$lang` array. This file serves as the fallback for all locales.
2. (Optional) Add the same key to `locale/pt_BR.php` with the Brazilian Portuguese translation. If the key is missing in `pt_BR.php`, the application will automatically fall back to the `pt_PT.php` entry.
3. The translation strings are accessed in code through `Translation::t('key')`.

## Switching locale

Administrators can change the locale through the configuration panel (see `configuracoes.php`). Internally this updates the configuration constant `LOCALIZATION_CODE` to either `PT` or `BR`. The `Translation` class reloads the appropriate locale on the next request.

