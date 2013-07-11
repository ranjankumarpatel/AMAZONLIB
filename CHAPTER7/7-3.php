<?php
define('LOCALE_DE', 'deu_deu');
define('LOCALE_US', 'us_us');
define('LOCALE_JP', 'jpn_jpn');

define('MSG_DOMAIN', 'example');

bindtextdomain('example', './7-3locale');
textdomain('example');

$country= LOCALE_JP;

switch ($country) {
    case LOCALE_US:
    define('TO_ENCODING','ISO-8859-1');
    putenv("LANG=".LOCALE_US);
    setlocale(LC_ALL, LOCALE_US);
    bind_textdomain_codeset(MSG_DOMAIN, TO_ENCODING);
    break;
    case LOCALE_DE:
    define ('TO_ENCODING', 'ISO-8859-1');
    putenv("LANG=".LOCALE_DE);
    setlocale(LC_ALL, LOCALE_DE);
    bind_textdomain_codeset(MSG_DOMAIN, TO_ENCODING);
    break;
    case LOCALE_JP:
    define('TO_ENCODING','Shift_JIS');
    putenv("LANG=".LOCALE_JP);
    setlocale(LC_ALL, LOCALE_JP);
    bind_textdomain_codeset(MSG_DOMAIN, TO_ENCODING);
    break;
    default:
    die('Unknown Locale');
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo TO_ENCODING ?> ">
</head>
<body>
<form accept-charset="<?php echo TO_ENCODING ?>" method="post" name="lang_stuff" target="_self">
<?php echo gettext('Search for Books') ?> 
<input type="text" name="tinput" />
<input type="submit" value="<?php echo gettext('Submit') ?>" />
</form>
</body>
</html>
