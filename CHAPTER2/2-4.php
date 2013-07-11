<?php

// Parser originally by Torsten Kˆster (torsten at jserver dot de) with some mods by me

// List elements as of the 2011-08-01 Amazon WSDL
$xml_list_elements = array(
"Accessory","Actor","AlternateVersion","Argument","Artist","ASIN","AudienceRating","AudioFormat","Author","Bin","BinParameter","BrowseNode","BrowseNodeId","BrowseNodes","Cart","CartItem","CatalogNumberListElement","Category","Collection","CollectionItem","Creator","Director","Disc","EANListElement","EditorialReview","EISBN","Error","Feature","Format","Header","ImageSet","ImageSets","Item","ItemId","ItemLink","Items","KeyValuePair","Language","MetaData","NewRelease","Offer","OfferListing","OtherCategoriesSimilarProduct","PictureFormat","Platform","Promotion","Property","RelatedItem","RelatedItems","RelationshipType","Request","ResponseGroup","SavedForLaterItem","SearchBinSet","SearchIndex","SimilarProduct","SimilarViewedProduct","Subject","TopItem","TopItemSet","TopSeller","Track","UPCListElement","Value","VariationAttribute","VariationDimension"
);

// Global error strings from XmlParser and GetData
$parser_error='';
$getdata_error='';

// returns associative array or false on error. If there's an error, the global $parser_error will
// contain the error details
function XmlParser($string)
{
    global $parser_error;

    $parser_error='';
    $values=array();

    // Create parser
    $p = xml_parser_create("UTF-8");
    xml_parser_set_option($p,XML_OPTION_CASE_FOLDING,false);
    xml_parser_set_option($p,XML_OPTION_SKIP_WHITE,true);

    // Parse into array structure
    $rc = xml_parse_into_struct($p,$string,$values);

    /* Check for Parsing Error */
    if (!$rc)
    {
        $errorcode = xml_get_error_code($p);
        $errstring = xml_error_string($errorcode);
        $byte= xml_get_current_byte_index($p);
        $parser_error = "XML PARSER ERROR: Error Code= $errorcode, Explanation= $errstring, Byte Number= $byte";
        xml_parser_free($p);
        return false;
    }

    xml_parser_free($p);

    // We store our path here
    $hash_stack = array();

    // This is our target
    $ret = array();

    foreach ($values as $val) {

        switch ($val['type']) {
            case 'open': // Start array structure
            array_push($hash_stack, $val['tag']);
            $valarg= (isset($val['attributes'])) ? $val['attributes'] : null;
            $ret = composeArray($ret, $hash_stack, $valarg);
            break;

            case 'close': // All done with this element
            array_pop($hash_stack);
            break;

            case 'complete':
            array_push($hash_stack, $val['tag']);
            $valarg=(isset($val['value']))? $val['value'] : null;
            // handle all attributes except those in 'open' container tags
            if (isset($val['attributes'])) {
                $temparr=array($val['tag'] => $valarg);
                $valarg=array_merge($val['attributes'], $temparr);
            };
            $ret = composeArray($ret, $hash_stack, $valarg);
            array_pop($hash_stack);
            break;

            default:
            // Ignoring CDATA type
        }
    }

    return $ret;
}

function &composeArray($array, $elements, $value)
{
    global $xml_list_elements;

    // Get current element
    $element = array_shift($elements);

    // Does the current element refer to a list?
    if (in_array($element,$xml_list_elements))
    {
        // Are there more elements?
        if(sizeof($elements) > 0)
        {
            $array[$element][sizeof($array[$element])-1] = &composeArray($array[$element][sizeof($array[$element])-1], $elements, $value);
        }
        else // It's an array
        {
            $size = (isset($array[$element]))?  sizeof($array[$element]) : 0;
            $array[$element][$size] = $value;
        }
    }
    else
    {
        // Are there more elements?
        if(sizeof($elements) > 0)
        {
            $array[$element] = &composeArray($array[$element], $elements, $value);
        }
        else
        {
            $array[$element] = $value;
        }
    }

    return $array;
}


?>
