<?php

error_reporting(E_ALL);

/************** USER CONFIGURABLE CONSTANTS ***************/
/* How many seconds we wait for an Amazon request to complete */
define('TIMEOUT', 30);
/* Name of error log file, if any */
define('ERROR_LOG', 'error_log.txt');
/* Name of debug output file */
define('LOGGING_FILE', 'curl_log.txt');
/*********** NOTHING USER CONFIGURABLE BELOW HERE ********/

// The secure URLs for making requests
define('ADDMODIFYDELETE_URL', 'https://secure.amazon.com/exec/panama/seller-admin/catalog-upload/add-modify-delete');
define('MODIFYONLY_URL', 'https://secure.amazon.com/exec/panama/seller-admin/catalog-upload/modify-only');
define('PURGEREPLACE_URL', 'https://secure.amazon.com/exec/panama/seller-admin/catalog-upload/purge-replace');
define('GETBATCHES_URL', 'https://secure.amazon.com/exec/panama/seller-admin/catalog-upload/get-batches');
define('ERRORLOG_URL', 'https://secure.amazon.com/exec/panama/seller-admin/download/errorlog');
define('QUICKFIX_URL', 'https://secure.amazon.com/exec/panama/seller-admin/download/quickfix');
define('GETREPORTSTATUS_URL', 'https://secure.amazon.com/exec/panama/seller-admin/manual-reports/get-report-status');
define('REPORT_URL', 'https://secure.amazon.com/exec/panama/seller-admin/download/report');
define('GENERATEREPORTNOW_URL', 'https://secure.amazon.com/exec/panama/seller-admin/manual-reports/generate-report-now');
define('GETPENDINGUPLOADSCOUNT_URL', 'https://secure.amazon.com/exec/panama/seller-admin/manual-reports/get-pending-uploads-count');
define('BATCHREFUND_URL', 'https://secure.amazon.com/exec/panama/seller-admin/catalog-upload/batch-refund');

// Convenient defines
define('INVENTORY_ADD_MODIFY_DELETE', 1);
define('INVENTORY_MODIFY_ONLY', 2);
define('INVENTORY_PURGE_REPLACE', 3);
define('GET_ERRORLOG', 1);
define('GET_QUICKFIX', 2);

// Amazon Locales
define('US', '.com');
define('DE', '.de');
define('JP', '.co.jp');
define('FR', '.fr');
define('UK', '.co.uk');
define('CA', '.ca');

// All report types
$Reports = array('Order', 'BatchRefund', 'OpenListings', 'OpenListingsLite', 'OpenListingsLiter');

// Report types that can be generated
$ReportsGen = array('Order', 'OpenListings', 'OpenListingsLite', 'OpenListingsLiter');

class MarketplaceProMerchant {
    var $Locale = null;
    var $FileFormat = 'TabDelimited';
    var $UploadFor = 'Marketplace';
    var $CommonHeaders = array();
    var $Logging = false;
    var $Debug = false;

    // parser vars
    var $rawxml = null;
    var $records = array();

    function MarketplaceProMerchant($username, $password, $locale = US)
    {
        $this->Locale = $locale;
        $this->CommonHeaders[] = "Authorization: Basic " . base64_encode($username.':'.$password);
        $this->CommonHeaders[] = "Content-Type: text/xml";
        $this->CommonHeaders[] = "Cookie: x-main=YvjPkwfntqDKun0QEmVRPcTTZDMe?Tn?;ubid-main=002-8989859-9917520;ubid-tacbus=019-5423258-4241018;x-tacbus=vtm4d53DvX\@Sc9LxTnAnxsFL3DorwxJa; ubid-tcmacb=087-8055947-0795529;ubid-ty2kacbus=161-5477122-2773524; session-id=087-178254-5924832; session-id-time=950660664";
    }

    function InventoryUpload($Task, $Data, $Isfile = true)
    {

        $headers = array();
        $headers[] = "UploadFor: " . $this->UploadFor;
        $headers[] = "FileFormat: " . $this->FileFormat;

        // Data can be in either a file or string
        if ($Isfile) {
            if (!$contents = file_get_contents($Data)) {
                die('FATAL ERROR: Can not open inventory file');
            }
        } else {
            $contents=$Data;
        }

        switch ($Task)
        {
            case INVENTORY_ADD_MODIFY_DELETE:
            $headers[] = "Content-Length: " . strlen($contents);
            $url = ADDMODIFYDELETE_URL;
            break;
            case INVENTORY_MODIFY_ONLY:
            $headers[] = "Content-Length: " . strlen($contents);
            $url = MODIFYONLY_URL;
            break;
            case INVENTORY_PURGE_REPLACE:
            $headers[] = "Content-Length: " . strlen($contents);
            $url = PURGEREPLACE_URL;
            break;
            default:
            die('ERROR: Unknown task type in AmazonInventory. Task = '.$Task);

        }

        $this->rawxml = $this->AmazonPost($url, $headers, $contents);

        $this->parse();
        return ($this->records);

    }

    function GetUploadStatus($Num) {

        $headers = array();
        $headers[] = "NumberOfBatches: " . $Num;
        $url = GETBATCHES_URL;

        $this->rawxml = $this->AmazonPost($url, $headers, null);

        $this->parse();
        return ($this->records);
    }

    function InventoryError($Task, $BatchId) {

        $headers = array();
        $headers[] = "BatchId: " . $BatchId;

        switch ($Task)
        {
            case GET_ERRORLOG:
            $url = ERRORLOG_URL;
            break;
            case GET_QUICKFIX:
            $url = QUICKFIX_URL;
            break;
        }

        $this->rawxml = $this->AmazonPost($url, $headers, null);

        $this->parse();
        return ($this->records);
    }


    function GetReport($ReportId) {

        $headers = array();
        $headers[] = "ReportID: " . $ReportId;

        $url = REPORT_URL;

        $this->rawxml = $this->AmazonPost($url, $headers, null);

        $this->parse();
        return ($this->records);
    }

    function GetReportIds ($ReportName, $Num) {
        global $Reports;

        $headers = array();
        $headers[] = "ReportName: " . $ReportName;
        $headers[] = "NumberOfReports: " . $Num;

        if (!in_array($ReportName, $Reports)) {
            die('ERROR: The report name '.$ReportName.' is not valid.');
        }

        $url = GETREPORTSTATUS_URL;

        $this->rawxml = $this->AmazonPost($url, $headers, null);

        $this->parse();
        return ($this->records);
    }

    function GetPendingUploadsCount () {

        $url = GETPENDINGUPLOADSCOUNT_URL;

        $this->rawxml = $this->AmazonPost($url, null, null);

        $this->parse();
        return ($this->records);
    }

    function GenerateReport ($ReportName, $NumDays) {
        global $ReportsGen;

        $headers = array();
        $headers[] = "ReportName: " . $ReportName;
        $headers[] = "NumberOfDays: " . $NumDays;

        if (!in_array($ReportName, $ReportsGen)) {
            die('ERROR: The report name '.$ReportName.' is not valid.');
        }

        $url = GENERATEREPORTNOW_URL;

        $this->rawxml = $this->AmazonPost($url, $headers, null);

        $this->parse();
        return ($this->records);
    }

    function BatchRefund($Data, $IsFile)
    {

        $headers = array();

        $url = BATCHREFUND_URL;

        if ($IsFile) {
            if (!$contents = file_get_contents($Data)) {
                die('FATAL ERROR: Can not open the bulk refund file');
            }
        } else {
            $contents=$Data;
        }

        $headers[] = "Content-Length: " . strlen($contents);

        $this->rawxml = $this->AmazonPost($url, $headers, $contents);

        $this->parse();
        return ($this->records);

    }

    function AmazonPost($url, $Headers, $Data = null)
    {
        $Headers = (is_null($Headers)) ? $this->CommonHeaders : array_merge($this->CommonHeaders, $Headers);

        if ($this->Debug) {
            echo "<h3>HTTP HEADERS</h3>";
            echo "<pre>";
            print_r($Headers);
            echo "</pre>";
            if (!is_null($Data)) {
                echo "<h3>INVENTORY DATA</h3>";
                echo "<pre>";
                echo $Data;
                echo "</pre>";
            }
        }

        // Set Locale
        if ($this->Locale != US) {
            $url = str_replace(US, $this->Locale, $url);
        }

        // Change domain name to vendornet for Japan
        if ($this->Locale == JP) {
            $url = str_replace('secure.', 'vendornet.', $url);
        }

        // Use built-in curl libraries
        if (function_exists("curl_init"))
        {

            // Curl is available. If curl_init succeeds, then SSL is
            // Available
            if (!$session = curl_init($url))
            {
                return false;
            }

            curl_setopt($session, CURLOPT_HTTPHEADER, $Headers);
            curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($session, CURLOPT_SSLVERSION, 3);
            curl_setopt($session, CURLOPT_POST, true);
            curl_setopt($session, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($session, CURLOPT_TIMEOUT, TIMEOUT);

            if (!is_null($Data))
            {
                curl_setopt($session, CURLOPT_POSTFIELDS, $Data);
            }

            if ($this->Logging)
            {
                // Record all headers in the file DEBUG_FILE
                curl_setopt($session, CURLOPT_VERBOSE, true);
                $fh = fopen(LOGGING_FILE, 'a');
                curl_setopt($session, CURLOPT_STDERR, $fh);
            }

            if (!($data_out = curl_exec($session)))
            {
                if ($this->Debug)
                {
                    echo "<h3>CURL SESSION</h3>";
                    print_r(curl_getinfo($session));
                }

                curl_close($session);

                if ($this->Logging)
                {
                    $datestr = date("F j Y h:i:s A");
                    $errstr = "$datestr: Curl_exec failed - curl_error($session)\n";
                    error_log($errstr, 3, ERROR_LOG);
                }
                die('FATAL ERROR: Curl failed.');
            }
            else
            {
                if ($this->Debug)
                {
                    echo "<h3>CURL SESSION</h3>";
                    print_r(curl_getinfo($session));
                    echo "<h3>RAW OUTPUT</h3>";
                    echo htmlspecialchars($data, ENT_QUOTES);
                }
                curl_close($session);
                return $data_out;
            }

        }
        else
        {
            die('Curl not available.');
        }

    }

    /* A simple parser for the AIMS API */

    function parse()
    {
        $this->records = array();

        if ($this->rawxml == '1' or $this->rawxml == false) {
            $this->records[0]='The attempt to communicate with Amazon failed or returned no results';
            return;
        }

        //remove extra spaces between tags (will create array elements otherwise)
        $this->rawxml = eregi_replace(">"."[[:space:]]+"."<", "><", $this->rawxml);
        //get rid of superfluous line terminators
        $this->rawxml=str_replace ("\n", " ", $this->rawxml);
        $this->rawxml=str_replace ("\r\n", " ", $this->rawxml);
        //trim anything hanging on the beginning or end
        $this->rawxml=trim($this->rawxml);

        $rarr=array();
        //removes all tags and leaves data in array elements
        $rarr=preg_split('/<[^>]+>/ix',$this->rawxml, -1, PREG_SPLIT_NO_EMPTY );

        //Interpretation
        if (isset($rarr[0])) {

            //Check for invalid user/password
            if (stristr($rarr[0], 'login') || stristr($rarr[0], 'identification') || stristr($rarr[0], 'ENCODING')) {
                $this->records[0]='Amazon says that the username and/or password you supplied is invalid';
            } else {

                switch ($rarr[0]) {
                    case 'FILE_NOT_FOUND':
                    $this->records[0]='Amazon returned '. $rarr[0] . ': the requested file was not found';
                    break;
                    case 'ACCESS_DENIED':
                    $this->records[0]='Amazon returned '. $rarr[0] . ': you are denied access to this report';
                    break;
                    case 'INVALID_BATCH':
                    $this->records[0]='Amazon returned '. $rarr[0] . ': the batch you requested was invalid';
                    break;
                    case 'SUCCESS':
                    $this->records[0]='Amazon returned '. $rarr[0] . ': your request succeeded';
                    break;
                    case 'CUSTOMER_UNAUTHORIZED':
                    $this->records[0]='Amazon returned '. $rarr[0] . ': you are not authorized to access this resource';
                    break;
                    case 'NO_DEFAULT_PAYMENT':
                    $this->records[0]='Amazon returned '. $rarr[0] . ': a default payment type is missing';
                    break;
                    case 'INVALID_LISTING_PROGRAM':
                    $this->records[0]='Amazon returned '. $rarr[0] . ': this listing resource is invalid';
                    break;
                    case 'INVALID_FILE_FORMAT':
                    $this->records[0]='Amazon returned '. $rarr[0] . ': your file format was invalid';
                    break;
                    default:
                    $this->records=$rarr;
                }
            }
        } else {
            $this->records[0]='Amazon returned XML tags with no data between them which may mean that there is nothing to return.';
        }

        return;
    }

} // End class

?>
