<?php

global $iChecksPerDepositForm;

require_once '../Include/Config.php';
require_once '../Include/Functions.php';

use ChurchCRM\Authentication\AuthenticationManager;
use ChurchCRM\dto\SystemURLs;
use ChurchCRM\Utils\InputUtils;
use ChurchCRM\Utils\RedirectUtils;

//Security
AuthenticationManager::redirectHomeIfFalse(AuthenticationManager::getCurrentUser()->isFinanceEnabled());

$iBankSlip = 0;
if (array_key_exists('BankSlip', $_GET)) {
    $iBankSlip = InputUtils::legacyFilterInput($_GET['BankSlip'], 'int');
}
if (!$iBankSlip && array_key_exists('report_type', $_POST)) {
    $iBankSlip = InputUtils::legacyFilterInput($_POST['report_type'], 'int');
}

$output = 'pdf';
if (array_key_exists('output', $_POST)) {
    $output = InputUtils::legacyFilterInput($_POST['output']);
}

$iDepositSlipID = 0;
if (array_key_exists('deposit', $_POST)) {
    $iDepositSlipID = InputUtils::legacyFilterInput($_POST['deposit'], 'int');
}

if (!$iDepositSlipID && array_key_exists('iCurrentDeposit', $_SESSION)) {
    $iDepositSlipID = $_SESSION['iCurrentDeposit'];
}

// If CSVAdminOnly option is enabled and user is not admin, redirect to the menu.
// If no DepositSlipId, redirect to the menu
if ((!AuthenticationManager::getCurrentUser()->isAdmin() && $bCSVAdminOnly && $output != 'pdf') || !$iDepositSlipID) {
    RedirectUtils::redirect('v2/dashboard');
}

if ($output === 'pdf') {
    header('Location: ' . SystemURLs::getRootPath() . '/api/deposits/' . $iDepositSlipID . '/pdf');
} elseif ($output === 'csv') {
    header('Location: ' . SystemURLs::getRootPath() . '/api/deposits/' . $iDepositSlipID . '/csv');
}
