<?php

function GetData($url, $timeout) {
    
    // Parse the URL into parameters for fsockopen
    $UrlArr = parse_url($url);
    $host = $UrlArr['host'];
    $port = (isset($UrlArr['port'])) ? $UrlArr['port'] : 80;
    $path = $UrlArr['path'] . '?' . $UrlArr['query'];

    // Zero out the error variables
    $errno = null;
    $errstr = '';
   
    // Open the connection to Amazon
    $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);

    // Failed to open the URL
    if (!is_resource($fp)) {
    // fsockopen failed
        return false;
    }

    // Send an HTTP GET header and Host header
    if (!(fwrite($fp, 'GET '. $path .' HTTP/1.0' . "\r\n". 'Host: ' . $host . "\r\n\ r\n"))) {
        fclose($fp);
        // Could not write HTTP requests
        return false;
    }

    // Block on the socket port, waiting for response from Amazon
    if (function_exists('socket_set_timeout')) {
        @socket_set_timeout($fp, $timeout);
        socket_set_blocking($fp, true);
    }

    // Get the HTTP response code from Amazon
    $line = fgets($fp , 1024);

    if ($line == false){
        fclose($fp);
        // Amazon didn’t respond
        return false;
    }

    // HTTP return code of 200 means success
    if (!(strstr($line, '200'))) {
        fclose($fp);
        // Didn’t get the proper HTTP response code -- log this, if desired
        return false;
    }
    // Find blank line between header and data
    do {
        $line = fgets($fp , 1024);
        if ($line == false) {
            fclose($fp);
            // Didn’t get data back from Amazon
            return false;
        }
        if (strlen($line) < 3) {
            break;
        }
    } while (true);

    $xml='';
    // Fetch the data from Amazon
    while ($line = fread($fp, 8192))
    {
        if ($line == false) {
            fclose($fp);
            // Couldn’t read any data from Amazon
            return false;
  }
        $xml .= $line;
    }

    fclose($fp);
    return $xml;
}
?>

