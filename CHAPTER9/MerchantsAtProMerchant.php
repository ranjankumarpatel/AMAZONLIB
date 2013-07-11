<?php

// ********************** CONFIGURABLE PARAMETERS *************
// ************************************************************
define('WSDLPATH', './merchant-interface-mime.wsdl');
define('TIMEFORMAT', 'l, F j, Y, g:ia T'); // Easy to read time format
// ****************** END OF CONFIGURABLE PARAMETERS **********
// ************************************************************
// ************************************************************

error_reporting(E_ALL);

require_once('SOAP/Client.php');      // PEAR SOAP client libraries
require_once('SOAP/Value.php');       // PEAR SOAP attachment libraries
require_once('XML/Unserializer.php'); // PEAR XML parser
require_once('Cache/Lite.php');       // Used for WSDL Caching


// Message Types for PostDocument
define('PPD', '_POST_PRODUCT_DATA_');
define('PPRD','_POST_PRODUCT_RELATIONSHIP_DATA_');
define('PPOD','_POST_PRODUCT_OVERRIDES_DATA_');
define('PPID', '_POST_PRODUCT_IMAGE_DATA_');
define('PPPD', '_POST_PRODUCT_PRICING_DATA_');
define('PIAD', '_POST_INVENTORY_AVAILABILITY_DATA_');
define('PTOD', '_POST_TEST_ORDERS_DATA_');
define('POAD', '_POST_ORDER_ACKNOWLEDGEMENT_DATA_');
define('POFD', '_POST_ORDER_FULFILLMENT_DATA_');
define('PPAD', '_POST_PAYMENT_ADJUSTMENT_DATA_');
define('PSD', '_POST_STORE_DATA_');

// Message Types for report information
define('GOD', '_GET_ORDERS_DATA_');
define('GPSD','_GET_PAYMENT_SETTLEMENT_DATA_');

class MerchantsAtProMerchant {
    var $client = null;
    var $amazerror = null;
    var $merchant = null;
    var $Logging = false;
    var $Debug = true;


    function MerchantsAtProMerchant($login, $password, $merchantid, $merchantname)
    {
        $this->merchant = array( 'merchantIdentifier' => $merchantid, 'merchantName' => $merchantname );
        $proxy=array('user'=> $login,'pass'=> $password);

        // Cache Lite options
        $cache_options = array(
        'cacheDir' => '/cachelite/',  // Directory to place cache files
        'lifeTime' => 999999999       // Only update if the cache gets corrupted
        );

        $cache_id = 'MerchantsAt';

        // Fetch a Cache instance
        $CL = new Cache_Lite($cache_options);

        if ($temp = $CL->get($cache_id)) {

            // Get the cached WSDL
            $this->client = unserialize($temp);

        } else {

            // Parse the WSDL and cache it
            $this->client = new SOAP_Client(WSDLPATH, true, false, $proxy);

            if (PEAR::isError($this->client)){
                $this->amazerror = "Error: " . $this->client->getMessage();
                return false;
            }

            $CL->save(serialize($this->client));
        }

        return true;
    }


    function PostDocument($documentType, $filename) {

        $this->amazerror = null;
        $att =  new SOAP_Attachment('doc', 'text/xml', $filename);

        if (PEAR::isError($att)) {
            $this->amazerror = "Error: " . $att->getMessage();
            return false;
        }

        $att->options['attachment']['encoding'] = '8bit';

        $params = array('merchant' => $this->merchant, 'messageType' => $documentType, 'doc' => $att);

        $encoding = 'Mime';
        $options=array('trace' => true, 'attachments' => $encoding, 'timeout' => '10');

        $Result = $this->client->call('postDocument', $params, $options);

        if (PEAR::isError($Result)) {
            $this->amazerror = "Error: " . $Result->getMessage();
            if ($this->Debug) {
                echo '<h2>Request/Response</h2>';
                echo '<pre>' . htmlspecialchars($this->client->__get_wire(), ENT_QUOTES) . '</pre>';
            }
            return false;
        }

        if ($this->Debug) {
            echo '<h2>Request/Response</h2>';
            echo '<pre>' . htmlspecialchars($this->client->__get_wire(), ENT_QUOTES) . '</pre>';
        }

        return $Result;

    }


    function PostDocumentInterfaceConformance($documentType, $filename) {

        $this->amazerror = null;
        $att =  new SOAP_Attachment('doc', 'text/xml', $filename);

        if (PEAR::isError($att)) {
            echo "Error: " . $att->getMessage();
            $this->amazerror = "Error: " . $att->getMessage();
            return false;
        }

        $att->options['attachment']['encoding'] = '8bit';

        $params = array('merchant' => $this->merchant, 'messageType' => $documentType, 'doc' => $att);

        $encoding = 'Mime';
        $options=array('trace' => true, 'attachments' => $encoding, 'timeout' => '10');

        $Result = $this->client->call('postDocumentInterfaceConformance', $params, $options);

        if (PEAR::isError($Result)) {
            $this->amazerror = "Error: " . $Result->getMessage();
            if ($this->Debug) {
                echo '<h2>Request/Response</h2>';
                echo '<pre>' . htmlspecialchars($this->client->__get_wire(), ENT_QUOTES) . '</pre>';
            }
            return false;
        }

        if ($this->Debug) {
            echo '<h2>Request/Response</h2>';
            echo '<pre>' . htmlspecialchars($this->client->__get_wire(), ENT_QUOTES) . '</pre>';
        }

        return $Result;

    }


    function GetDocument($docId) {
        $this->amazerror = null;

        $params = array('merchant' => $this->merchant, 'documentIdentifier' => $docId);

        $options=array('trace' => true, 'timeout' => '10');

        $Result = $this->client->call('getDocument', $params, $options);

        if (PEAR::isError($Result)) {
            $this->amazerror = "Error: " . $Result->getMessage();
            if ($this->Debug) {
                echo '<h2>Request/Response</h2>';
                echo '<pre>' . htmlspecialchars($this->client->__get_wire(), ENT_QUOTES) . '</pre>';
            }

            return false;
        }

        if (!isset($this->client->__attachments)) {
            $this->amazerror = 'No attachment returned. Might be an error';
            return($Result);
        }

        // Found an attachment
        $xml=current($this->client->__attachments);

        $parser = & new XML_Unserializer();
        $rc = $parser->unserialize($xml);

        if (PEAR::isError($rc)) {
            $this->amazerror = $rc->getMessage();
            return false;
        }

        if ($this->Debug) {
            echo '<h2>Request/Response</h2>';
            echo '<pre>' . htmlspecialchars($this->client->__get_wire(), ENT_QUOTES) . '</pre>';
        }

        // Return the parsed document
        return ($parser->getUnserializedData());

    }

    function GetDocumentInterfaceConformance($docId) {
        $this->amazerror = null;

        $params = array('merchant' => $this->merchant, 'documentIdentifier' => $docId);

        $options=array('trace' => true, 'timeout' => '10');

        $Result = $this->client->call('getDocumentInterfaceConformance', $params, $options);

        if (PEAR::isError($Result)) {
            $this->amazerror = "Error: " . $Result->getMessage();
            if ($this->Debug) {
                echo '<h2>Request/Response</h2>';
                echo '<pre>' . htmlspecialchars($this->client->__get_wire(), ENT_QUOTES) . '</pre>';
            }

            return false;
        }

        $xml=current($this->client->__attachments);

        $parser = & new XML_Unserializer();
        $rc = $parser->unserialize($xml);

        if (PEAR::isError($rc)) {
            $this->amazerror = $rc->getMessage();
            return false;
        }

        if ($this->Debug) {
            echo '<h2>Request/Response</h2>';
            echo '<pre>' . htmlspecialchars($this->client->__get_wire(), ENT_QUOTES) . '</pre>';
        }

        // Return the parsed document
        return ($parser->getUnserializedData());

    }


    function GetAllPendingDocumentInfo($documentType) {
        $this->amazerror = null;

        $params = array('merchant' => $this->merchant, 'messageType' => $documentType);

        $options = array('trace' => true, 'timeout' => '10');

        $Result = $this->client->call('getAllPendingDocumentInfo', $params, $options);

        if (PEAR::isError($Result)) {
            $this->amazerror = "Error: " . $Result->getMessage();
            if ($this->Debug) {
                echo '<h2>Request/Response</h2>';
                echo '<pre>' . htmlspecialchars($this->client->__get_wire(), ENT_QUOTES) . '</pre>';
            }

            return false;
        }

        if ($this->Debug) {
            echo '<h2>Request/Response</h2>';
            echo '<pre>' . htmlspecialchars($this->client->__get_wire(), ENT_QUOTES) . '</pre>';
        }

        // Return the parsed document
        return ($Result);

    }

    function GetDocumentInfoInterfaceConformance($documentType) {
        $this->amazerror = null;

        $params = array('merchant' => $this->merchant, 'messageType' => $documentType);

        $options = array('trace' => true, 'timeout' => '10');

        $Result = $this->client->call('getDocumentInfoInterfaceConformance', $params, $options);

        if (PEAR::isError($Result)) {
            $this->amazerror = "Error: " . $Result->getMessage();
            echo "Error: " . $Result->getMessage();
            if ($this->Debug) {
                echo '<h2>Request/Response</h2>';
                echo '<pre>' . htmlspecialchars($this->client->__get_wire(), ENT_QUOTES) . '</pre>';
            }

            return false;
        }

        if ($this->Debug) {
            echo '<h2>Request/Response</h2>';
            echo '<pre>' . htmlspecialchars($this->client->__get_wire(), ENT_QUOTES) . '</pre>';
        }

        // Return the parsed document
        return ($Result);

    }

    function GetDocumentProcessingStatus($documentId) {
        $this->amazerror = null;

        $params = array('merchant' => $this->merchant, 'documentTransactionIdentifier' => $documentId);

        $options=array('trace' => true, 'timeout' => '10');

        $Result = $this->client->call('getDocumentProcessingStatus', $params, $options);

        if (PEAR::isError($Result)) {
            $this->amazerror = "Error: " . $Result->getMessage();
            if ($this->Debug) {
                echo '<h2>Request/Response</h2>';
                echo '<pre>' . htmlspecialchars($this->client->__get_wire(), ENT_QUOTES) . '</pre>';
            }

            return false;
        }

        if ($this->Debug) {
            echo '<h2>Request/Response</h2>';
            echo '<pre>' . htmlspecialchars($this->client->__get_wire(), ENT_QUOTES) . '</pre>';
        }


        // Return the parsed document
        return ($Result);

    }

    function PostDocumentDownloadAck($idArray) {
        $this->amazerror = null;

        $idArray = (!is_array($idArray)) ? array($idArray) : $idArray;

        $params = array('merchant' => $this->merchant, 'documentIdentifierArray' => $idArray);

        $options = array('trace' => true, 'timeout' => '10');

        $Result = $this->client->call('postDocumentDownloadAck', $params, $options);

        if (PEAR::isError($Result)) {
            $this->amazerror = "Error: " . $Result->getMessage();
            if ($this->Debug) {
                echo '<h2>Request/Response</h2>';
                echo '<pre>' . htmlspecialchars($this->client->__get_wire(), ENT_QUOTES) . '</pre>';
            }

            return false;
        }

        if ($this->Debug) {
            echo '<h2>Request/Response</h2>';
            echo '<pre>' . htmlspecialchars($this->client->__get_wire(), ENT_QUOTES) . '</pre>';
        }


        if ($Result->DocumentDownloadAckStatus->documentDownloadAckProcessingStatus != '_SUCCESSFUL_') {
            $this->amazerror='OrderAck returned status of '.$Result->DocumentDownloadAckStatus->documentDownloadAckProcessingStatus.' with document id '.$Result->DocumentDownloadAckStatus->documentID;
            return false;
        }

        return true;

    }


    function GetLastNDocumentInfo($documentType, $howMany) {
        $this->amazerror = null;

        $params = array('merchant' => $this->merchant, 'messageType' => $documentType, 'howMany' => $howMany);

        $options = array('trace' => true, 'timeout' => '10');

        $Result = $this->client->call('getLastNDocumentInfo', $params, $options);

        if (PEAR::isError($Result)) {
            $this->amazerror = "Error: " . $Result->getMessage();
            if ($this->Debug) {
                echo '<h2>Request/Response</h2>';
                echo '<pre>' . htmlspecialchars($this->client->__get_wire(), ENT_QUOTES) . '</pre>';
            }

            return false;
        }

        if ($this->Debug) {
            echo '<h2>Request/Response</h2>';
            echo '<pre>' . htmlspecialchars($this->client->__get_wire(), ENT_QUOTES) . '</pre>';
        }

        // Return the parsed document
        return ($Result);
    }
}
?>
