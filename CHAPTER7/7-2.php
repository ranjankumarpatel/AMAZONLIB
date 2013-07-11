<?php
$country='de';

switch ($country) {
    case 'us':
    define('TO_ENCODING','ISO-8859-1');
    define('FROM_ENCODING','UTF-8');
    define('LABEL1','Search for Books');
    define('LABEL2','Submit');
    break;
    case 'de':
    define('TO_ENCODING','ISO-8859-1');
    define('FROM_ENCODING','UTF-8');
    define('LABEL1','Suche nach Büchern');
    define('LABEL2','Senden');
    break;
    case 'jp':
    define('TO_ENCODING','Shift_JIS');
    define('FROM_ENCODING','UTF-8');
    define('LABEL1','本のための調査');
    define('LABEL2','送りなさい');
    break;
    default:
    die('Unknown Country');
}
?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo TO_ENCODING ?> ">
</head>
<body>
<form accept-charset="<?php echo TO_ENCODING ?>" method="post" name="lang_stuff" target="_self">
<?php echo mb_convert_encoding(LABEL1,TO_ENCODING,FROM_ENCODING) ?> 
<input type="text" name="tinput" />
<input type="submit" value="<?php echo mb_convert_encoding(LABEL2,TO_ENCODING,FROM_ENCODING) ?>" />
</form>
</body>
</html>
