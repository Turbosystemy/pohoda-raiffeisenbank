# Pohoda Raiffeisenbank Connector

This repository contains the PHP classes that bridge the Raiffeisenbank Premium API and Stormware Pohoda via mServer.  
Use `Pohoda\RaiffeisenBank\Transactor` to push transactions into Pohoda and
`Pohoda\RaiffeisenBank\Statementor` to download, parse and import bank statements.

## Installation

```
composer require vitexsoftware/pohoda-raiffeisenbank-connector
```

## Configuration

The classes rely on [`Ease\Shared`](https://github.com/VitexSoftware/EaseCore) for configuration.  
Load the required values from a `.env` file or from environment variables before creating
an instance of `Transactor` or `Statementor`:

```php
\Ease\Shared::init(
    [
        'POHODA_URL', 'POHODA_USERNAME', 'POHODA_PASSWORD', 'POHODA_ICO',
        'CERT_FILE', 'CERT_PASS', 'XIBMCLIENTID', 'ACCOUNT_NUMBER'
    ],
    __DIR__.'/.env'
);
```

### Raiffeisenbank Premium API

| Key | Description |
| --- | --- |
| `CERT_FILE` | Path to the PKCS#12 certificate obtained from the bank. |
| `CERT_PASS` | Password protecting the certificate. |
| `XIBMCLIENTID` | Client identifier issued for the Premium API application. |
| `ACCOUNT_NUMBER` | Bank account number used for API calls. |
| `ACCOUNT_CURRENCY` | ISO currency code (defaults to `CZK`). |
| `STATEMENT_LINE` | `MAIN` or `ADDITIONAL` statement line to download. |

When importing foreign currency statements provide one of the following:

* `FIXED_RATE` (+ optional `FIXED_RATE_AMOUNT`) for a predefined conversion rate, **or**
* `CNB_CACHE` URL pointing to a CNB cache service together with optional `RATE_OFFSET`
  (`today` or `yesterday`).

### Stormware Pohoda mServer

| Key | Description |
| --- | --- |
| `POHODA_URL` | Base URL of the mServer instance. |
| `POHODA_USERNAME`, `POHODA_PASSWORD` | Credentials for the Pohoda API user. |
| `POHODA_BANK_IDS` | Identifier of the Pohoda bank agenda (e.g. `RB`). |
| `POHODA_TIMEOUT`, `POHODA_COMPRESS`, `POHODA_DEBUG` | Optional request tuning flags. |
| `POHODA_ICO` | Company identification number used when building liquidation requests. |
| `JOB_ID` | Optional identifier shown in the imported records. |

## Usage

### Import Raiffeisenbank transactions

```php
<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

\Ease\Shared::init([
    'POHODA_URL', 'POHODA_USERNAME', 'POHODA_PASSWORD', 'POHODA_ICO',
    'CERT_FILE', 'CERT_PASS', 'XIBMCLIENTID', 'ACCOUNT_NUMBER', 'POHODA_BANK_IDS'
]);

$transactor = new Pohoda\RaiffeisenBank\Transactor(\Ease\Shared::cfg('ACCOUNT_NUMBER'));
$transactor->setScope('last_two_months');
$transactor->import();
```

The import reads movements from the Premium API and writes them to Pohoda.  
Scopes accepted by `setScope()` include `today`, `yesterday`, `last_week`,
`last_month`, `last_two_months`, `previous_month`, `two_months_ago`, month
names (`January` … `December`), `this_year`, a single day (`YYYY-MM-DD`), or a
custom range (`YYYY-MM-DD>YYYY-MM-DD`).

### Work with statements

```php
<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

\Ease\Shared::init([
    'POHODA_URL', 'POHODA_USERNAME', 'POHODA_PASSWORD', 'POHODA_ICO',
    'CERT_FILE', 'CERT_PASS', 'XIBMCLIENTID', 'ACCOUNT_NUMBER',
    'ACCOUNT_CURRENCY', 'STATEMENT_SAVE_DIR'
]);

$statementor = new Pohoda\RaiffeisenBank\Statementor(\Ease\Shared::cfg('ACCOUNT_NUMBER'));
$statementor->setScope('last_month');
$statementor->downloadXML();
$statementor->downloadPDF();
$imported = $statementor->import(\Ease\Shared::cfg('POHODA_BANK_IDS', 'RB'));

printf("Imported %d statement items\n", \count($imported));
```

`Statementor` exposes helper methods for offline processing as well:

* `importXML($path)` – register a downloaded XML file for import.
* `download('xml'|'pdf')` / `downloadOne()` – download statements into the configured directory.
* `getStatementFilenames('xml'|'pdf')` – build friendly filenames for the downloaded files.
* `getMessages()` – inspect messages returned by Pohoda after the import.

### Certificate helper

Before running an import you can validate the PKCS#12 file with:

```php
Pohoda\RaiffeisenBank\PohodaBankClient::checkCertificate($pathToCert, $password);
```

This prevents the process from running with an unreadable or invalid certificate.

## License

The code is released under the MIT License. See the [LICENSE](LICENSE) file for details.
