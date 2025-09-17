<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

\Ease\Shared::init([
    'POHODA_URL', 'POHODA_USERNAME', 'POHODA_PASSWORD', 'POHODA_ICO',
    'CERT_FILE', 'CERT_PASS', 'XIBMCLIENTID', 'ACCOUNT_NUMBER', 'POHODA_BANK_IDS'
], __DIR__.'/../.env');

Pohoda\RaiffeisenBank\PohodaBankClient::checkCertificate(
    \Ease\Shared::cfg('CERT_FILE'),
    \Ease\Shared::cfg('CERT_PASS')
);

$transactor = new Pohoda\RaiffeisenBank\Transactor(\Ease\Shared::cfg('ACCOUNT_NUMBER'));
$transactor->setScope(\Ease\Shared::cfg('IMPORT_SCOPE', 'last_two_months'));
$transactor->import();
