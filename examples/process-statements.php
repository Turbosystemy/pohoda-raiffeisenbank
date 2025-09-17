<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

\Ease\Shared::init([
    'POHODA_URL', 'POHODA_USERNAME', 'POHODA_PASSWORD', 'POHODA_ICO',
    'CERT_FILE', 'CERT_PASS', 'XIBMCLIENTID', 'ACCOUNT_NUMBER',
    'ACCOUNT_CURRENCY', 'STATEMENT_SAVE_DIR', 'POHODA_BANK_IDS'
], __DIR__.'/../.env');

Pohoda\RaiffeisenBank\PohodaBankClient::checkCertificate(
    \Ease\Shared::cfg('CERT_FILE'),
    \Ease\Shared::cfg('CERT_PASS')
);

$statementor = new Pohoda\RaiffeisenBank\Statementor(\Ease\Shared::cfg('ACCOUNT_NUMBER'));
$statementor->setScope(\Ease\Shared::cfg('IMPORT_SCOPE', 'last_month'));
$statementor->downloadXML();
$statementor->downloadPDF();
$imported = $statementor->import(\Ease\Shared::cfg('POHODA_BANK_IDS', 'RB'));

printf("Imported %d items\n", \count($imported));
