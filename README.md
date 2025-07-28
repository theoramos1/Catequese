# CatecheSis

CatecheSis is a web application for managing catechesis records and online enrollments.

## Prerequisites

CatecheSis requires a standard LAMP stack. Recommended software versions are:

- Apache web server
- MySQL 5.7 or MariaDB 10.3
- PHP 7.4 or newer
- PHP extensions: `pdo_mysql`, `zip`, `gd`, `xml`, `XMLWriter`, `DOM`, `MBString`
- A valid SSL certificate (HTTPS enabled)
- About 200 MB of free disk space

These requirements are listed in the installation manual which ships with the project.

## Configuration files

All configuration lives under `core/config/`:

- `catechesis_config.inc.php` – main configuration file loaded by all entry points.
- `catechesis_config.inc.template.php` – template used to create the configuration file.
- `catechesis_config.inc.docker.php` – example configuration used inside containers.

The file defines the base URL, domain name, local paths and the location of the external data directory that stores sensitive data. Copy the template file and adjust the constants to match your environment.

Additional sensitive options are loaded from `CATECHESIS_DATA_DIRECTORY/config/catechesis_config.shadow.php` which should be located outside the web root.

## Payment provider settings

Payment details for online enrollments are stored in the application database and can be managed through the **Configurações** page (link `configuracoes.php`). The following options exist:

- `ENROLLMENT_PAYMENT_ENTITY` – numeric entity identifier
- `ENROLLMENT_PAYMENT_REFERENCE` – numeric payment reference
- `ENROLLMENT_PAYMENT_AMOUNT` – amount expected for each enrollment
- `ENROLLMENT_PAYMENT_ACCEPT_BIGGER_DONATIONS` – allow paying more than the default amount
- `ENROLLMENT_PAYMENT_PROOF` – address or URL where payers should send the proof of payment
- `PAYMENT_PROVIDER_URL` – API endpoint used to verify payments
- `PAYMENT_PROVIDER_TOKEN` – authentication token for the payment provider API

These settings are stored in the table `configuracoes` and can also be updated manually if needed.

## Pix payment

Brazilian donors may contribute using Pix. Configure the following keys in the **Configurações** page:

- `PIX_KEY` – Pix key associated with the parish
- `PIX_RECEIVER` – name of the receiver shown on the QR code
- `PIX_CITY` – receiver city required by the Pix standard
- `PIX_DESCRIPTION` – text description included in the payment data
- `PIX_TXID` – default transaction identifier
- `PIX_API_URL` – optional endpoint used to generate the QR code
- `PIX_API_TOKEN` – token for the Pix API
- `PIX_API_TIMEOUT` – timeout in seconds when calling the API

Call `PixQRCode::generatePixQRCode($amount)` to generate the QR image for the desired amount. The `$amount` parameter is optional; pass `null` to omit the value from the generated code. When used for enrollments this amount usually corresponds to `ENROLLMENT_PAYMENT_AMOUNT` (default R$100).

Payment confirmation can optionally be automated with `PixPaymentVerificationService`, which expects the provider endpoint, token and timeout to be set via `PIX_PROVIDER_URL`, `PIX_PROVIDER_TOKEN` and `PIX_PROVIDER_TIMEOUT`.

## Localization

Administrators can select the country used for address and phone formats in
**Configurações** → **País**.  The configuration key for this setting is
`LOCALIZATION_CODE` and it accepts the values `PT` (Portugal) and `BR`
(Brazil).  The choice affects several labels and validation rules across the
application.

If changing the value through the interface is not possible, update it directly
in the database:

```sql
UPDATE configuracoes SET valor='PT' WHERE chave='LOCALIZATION_CODE';
```

## Updating and database migrations

The `updater/` directory contains a web based assistant that downloads new versions and executes SQL scripts found under `updater/sql_scripts`. To run the updater navigate to `updater/index.php` in your browser (you must be logged in as an administrator) or execute it from the command line with `php updater/index.php`.

Whenever an update includes database migrations, the assistant will automatically run the provided SQL scripts. If you update files manually, execute the scripts from `updater/sql_scripts` using your database client.

