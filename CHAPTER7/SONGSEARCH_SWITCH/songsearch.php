<?php 

// japanese tooltips won't show up right unless you select the right font
// Windows Control-Panel > Display Properties > Appearance > Advanced > Item: ToolTip)
// Won't work right if english words are used for Japanese search because I don't
// use lowercase/uppercase search for Japanese site
//
// Sometimes matches (esp german and french sites) same words without accents.
// I get the matches but I don't display the song titles because the words don't match.

$ACCESS_KEY_ID = "XXXXXXXXXXXXXXXXXX";
$SECRET_ACCESS_KEY = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";

define('LOCALE_FR', 'fr');
define('LOCALE_UK', 'uk');
define('LOCALE_DE', 'de');
define('LOCALE_US', 'us');
define('LOCALE_JP', 'jp');

define('DEFAULT_LOCALE', 'us');
define('VERSION', '2011-08-01');
define('ASSOCIATES_ID','ws');

if (isset($_GET['locale'])) {
    //locale change requested
    $locale=$_GET['locale'];
    $oneday=time()+60*60*24*30; // Cookie lasts 30 days
    if (!setcookie('songsearch', $locale, $oneday)) {
        echo 'Your browser will not accept cookies';
    }
} elseif (isset($_COOKIE['songsearch'])) {
    $locale = stripslashes($_COOKIE['songsearch']);
} else {
    //No cookie. Create one
    $locale=DEFAULT_LOCALE;
    $oneday=time()+60*60*24*30;
    if (!setcookie('songsearch', $locale, $oneday)) {
        echo 'Your browser will not accept cookies';
    }
}
require_once("tools.inc.php");

// Language-specific defines
switch ($locale) {
    case LOCALE_DE:
    define ('TO_ENCODING', 'ISO-8859-1');
    define ('DOMAIN', 'de');
    define ('NOIMAGE', 'http://images-eu.amazon.com/images/G/03/general/no-img-de_90x90.gif');
    define ('SUBMIT', 'Einsenden');
    define ('SONGTITLE', 'Liedertitelsuche');
    define ('ENTERTITLE', 'Geben Sie Liedertitel Suchworte Ein');
    define ('CHOOSE', 'Wähle eine zu durchsuchende website');
    define ('PLEASE_ENTER', 'Tragen sie bitte schlüsselwörter ein');
    define ('NOTRACKS', 'Keine liedertitellisten verf�gbar');
    define ('DISC', 'Disk');
    define ('TRACK', 'Titel');
    break;
    case LOCALE_JP:
    define ('TO_ENCODING', 'Shift_JIS');
    define ('DOMAIN', 'jp');
    define ('NOIMAGE', 'http://images-jp.amazon.com/images/G/09/x-locale/detail/thumb-no-image.gif');
    define ('SUBMIT', '検索');
    define ('SONGTITLE', '曲名検索');
    define ('ENTERTITLE', '曲名キーワードを入力してください。');
    define ('CHOOSE', '検索サイトを選んでください。');
    define ('PLEASE_ENTER', 'キーワードを入力してください。');
    define ('NOTRACKS', '入力したキーワードに該当する曲名は見つかりませんでした。');
    define ('DISC', 'ディスク');
    define ('TRACK', 'トラック');
    break;
    case LOCALE_UK:
    define ('TO_ENCODING', 'ISO-8859-1');
    define ('DOMAIN', 'co.uk');
    define ('NOIMAGE', 'http://images-eu.amazon.com/images/G/02/misc/no-img-lg-uk.gif');
    define ('SUBMIT', 'Submit');
    define ('SONGTITLE', 'Song Title Search');
    define ('ENTERTITLE', 'Enter Song Title Keywords');
    define ('CHOOSE', 'Choose a site to search');
    define ('PLEASE_ENTER', 'Please enter keywords');
    define ('NOTRACKS', 'No matching song titles were found');
    define ('DISC', 'Disc');
    define ('TRACK', 'Track');
    break;
    case LOCALE_US:
    default:
    define ('TO_ENCODING', 'ISO-8859-1');
    define ('DOMAIN', 'com');
    define ('NOIMAGE', 'http://g-images.amazon.com/images/G/01/x-site/icons/no-img-lg.gif');
    define ('SUBMIT', 'Submit');
    define ('SONGTITLE', 'Song Title Search');
    define ('ENTERTITLE', 'Enter Song Title Keywords');
    define ('CHOOSE', 'Choose a site to search');
    define ('PLEASE_ENTER', 'Please enter keywords');
    define ('NOTRACKS', 'No matching song titles were found');
    define ('DISC', 'Disc');
    define ('TRACK', 'Track');
    break;
    case LOCALE_FR:
    default:
    define ('TO_ENCODING', 'ISO-8859-1');
    define ('DOMAIN', 'fr');
    define ('NOIMAGE', 'http://images-eu.amazon.com/images/G/08/x-site/icons/no-img-lg.gif');
    define ('SUBMIT', 'Soumettez');
    define ('SONGTITLE', 'Recherche de Titre de Chanson');
    define ('ENTERTITLE', 'Écrivez Les Mots-clés De Titre De Chanson');
    define ('CHOOSE', 'Choisissez un emplacement pour rechercher');
    define ('PLEASE_ENTER', 'Veuillez écrire les mots-clés');
    define ('NOTRACKS', 'Aucun titre assorti de chanson n\'a été trouvé');
    define ('DISC', 'Disque');
    define ('TRACK', 'Voie');
    break;

}

if (!empty($_POST['songtitle'])) {

    $search_string=urlencode(langreverse(trim($_POST['songtitle'], ' ')));

$timestamp = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z");
$timestamp = str_replace(':','%3A',$timestamp);

$requestparms = 'AWSAccessKeyId='.$ACCESS_KEY_ID.'&AssociateTag='.ASSOCIATES_ID.'&Keywords='.$search_string.'&Operation=ItemSearch&ResponseGroup=Request,Small,Tracks,Images&SearchIndex=MusicTracks&Service=AWSECommerceService&Timestamp='.$timestamp.'&Version='.VERSION;

$request = "GET\necs.amazonaws.".DOMAIN."\n/onca/xml\n".$requestparms;

$request = str_replace(':','%3A',$request);
$request = str_replace(';','%3B',$request);
$request = str_replace(',','%2C',$request);

// Signing
$signature = base64_encode(hash_hmac("sha256",$request,$SECRET_ACCESS_KEY,TRUE));

$signature = str_replace('+','%2B',$signature);
$signature = str_replace('=','%3D',$signature);
$signature = str_replace('/','%2F',$signature);

$fullrequest = 'http://ecs.amazonaws.'.DOMAIN.'/onca/xml?'.$requestparms.'&Signature='.$signature;

    $xml = GetData($fullrequest, 10);
    
    $Result = xmlparser($xml);

} else {

    $Result='';
}

require_once('layout.php');

function findstring($haystack, $needle) {
    global $locale;

    switch ($locale) {
        case 'jp':
        // return iconv_strpos($haystack, $needle, 0, 'UTF-8');
        return mb_strpos($haystack, $needle, 0, 'UTF-8');
        break;
        case 'de':
        // setlocale(LC_ALL, 'de_DE@euro', 'de_DE', 'deu_deu');
        // $haystack=strtolower(utf8_decode($haystack));
        // $needle=strtolower(utf8_decode($needle));
        // return iconv_strpos(utf8_encode($haystack), utf8_encode($needle), 0, 'UTF-8');
        $haystack=mb_strtolower($haystack, 'UTF-8');
        $needle=mb_strtolower($needle, 'UTF-8');
        return mb_strpos($haystack, $needle, 0, 'UTF-8');
        break;
        case 'us':
        case 'uk':
        default:
        return strpos(strtolower($haystack), strtolower($needle));

    }
    return true;
}

function lang($string) {
    global $locale;

    switch ($locale) {
        case 'jp':
        // return iconv('UTF-8', TO_ENCODING, $string);
        return mb_convert_encoding($string, TO_ENCODING, 'UTF-8');
        break;
        default:
        return utf8_decode($string);
    }
}

function langreverse($string) {

    // should be going from native encoding to UTF-8, but amazon is
    // currently encoding REST requests in ISO-8859, except Japan, which is in UTF-8

    global $locale;
    switch ($locale) {
        case 'jp':
        return mb_convert_encoding($string, 'UTF-8', TO_ENCODING);
        // return iconv(TO_ENCODING, 'UTF-8', $string);
        break;
        default:
        // return utf8_encode($string); Switch to this when Amazon fixes encoding
        return $string;
    }
}


function theResults() {
    global $Result;

    if (empty($Result)) {
        echo lang(PLEASE_ENTER);
    } elseif  (isset($Result['ItemSearchResponse']['Items'][0]['Request']['Errors']['Error'][0]['Message']) and ($Result['ItemSearchResponse']['Items'][0]['TotalResults'] == '0'))  {
        echo lang($Result['ItemSearchResponse']['Items'][0]['Request']['Errors']['Error'][0]['Message']);
    } else {

        $keywords=$Result['ItemSearchResponse']['Items'][0]['Request'][0]['ItemSearchRequest']['Keywords'];
        $words = explode(' ', $keywords);

        echo '<table width="100%" border="1"><tr>';
        foreach ($Result['ItemSearchResponse']['Items'][0]['Item'] as $item) {

            $trackout='';
            if (isset($item['Tracks']['Disc'])) {
                foreach ($item['Tracks']['Disc'] as $disc) {
                    $trackout .= lang(DISC)." ".$disc['Number']."\n";
                    foreach ($disc['Track'] as $track) {
                        foreach ($words as $word) {
                            if (findstring($track['Track'], $word) === false) {
                                $trackout .= "";
                            } else {
                                $trackout .= "   ".lang(TRACK)." ".$track['Number'].' - '.lang($track['Track'])."\n";
                            }
                        }
                    }
                }
                $trackout=str_replace('"', "&#34;", $trackout);
            } else {
                $trackout=lang(NOTRACKS);
            }


            if (isset($item['SmallImage']['URL'])) {
                $image='<img src="'.$item["SmallImage"]["URL"].'" title="'.$trackout.'" />';
            } else {
                $image='<img src="'.NOIMAGE.'" title="'.$trackout.'" />';
            }

            if (!isset($item["ItemAttributes"]["Title"])) continue;

            $title = '<a href="'.$item["DetailPageURL"].'" target="_blank">'.lang($item["ItemAttributes"]["Title"]).'</a>';
            echo '<td><div style="text-align: center;">'.$title.'</div><div style="text-align: center;">'.$image.'</div></td>';
        }
        echo '</tr></table>';

    }

}

?>
