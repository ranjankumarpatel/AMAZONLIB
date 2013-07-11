<?php

require_once('./MarketplaceProMerchant.php');

$t = new MarketplaceProMerchant('youremail@whatever.com','yourpassword', US);

$t->Debug=false;
$t->Logging=true;

echo "GetPendingUploadsCount: <br>";

$rc= $t->GetPendingUploadsCount();

echo '<pre>';
print_r($rc);
echo '</pre>';

echo "GetReportIds: <br>";

$rc= $t->GetReportIds('Order', 5);

echo '<pre>';
print_r($rc);
echo '</pre>';

echo "GenerateReport: <br>";

$rc= $t->GenerateReport('Order', 5);

echo '<pre>';
print_r($rc);
echo '</pre>';

echo "GetReportIds: <br>";

$rc= $t->GetReportIds('Order', 10);

echo '<pre>';
print_r($rc);
echo '</pre>';

echo "GetReport: <br>";

$rc= $t->GetReport('198266221');

echo '<pre>';
print_r($rc);
echo '</pre>';

// echo "InventoryUpload: <br>";
//
// $rc= $t->InventoryUpload(INVENTORY_ADD_MODIFY_DELETE, 'upload.txt');
//
// echo '<pre>';
// print_r($rc);
// echo '</pre>';

echo "GetUploadStatus: <br>";

$rc= $t->GetUploadStatus(5);

echo '<pre>';
print_r($rc);
echo '</pre>';


?>
