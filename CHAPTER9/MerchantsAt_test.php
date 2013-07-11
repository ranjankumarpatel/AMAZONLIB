<?php
error_reporting(E_ALL);

require_once('MerchantsAtProMerchant.php');

$merchantid = 'M_SEARS_ROEBUCK_229753';
$merchantname = 'Sears Roebuck';
$login = 'sears@robuck.com';
$password = 'searspassword';

$t = new MerchantsAtProMerchant($login, $password, $merchantid, $merchantname);

$rc= $t->GetAllPendingDocumentInfo(GOD);

// $rc = $t->PostDocumentInterfaceConformance(POFD, './conf_attachment.xml');

// $rc = $t->PostDocument(PPD, './upload.xml');

// $rc = $t->PostDocument(PIAD, './upload2.xml');

// $rc = $t->PostDocument(PPPD, './upload3.xml');

// $rc= $t->GetDocumentProcessingStatus('382519303');

// $rc = $t->GetDocument('96488663');

// $rc= $t->GetDocumentInfoInterfaceConformance(GOD);

// $rc = $t->GetLastNDocumentInfo(GOD, 1);

if ($t->amazerror){
    echo $t->amazerror;
}

echo "RESULT=";
print_r($rc);

?>
