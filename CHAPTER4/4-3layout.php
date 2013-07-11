<html>
<head>
<title><?php global $asin; if (is_null($asin)) echo 'Browse Converse All-Stars';  else echo 'Browse Specific Shoe Variations'; ?></title>
</head>
<body>
<table width="1000" border="1" cellpadding="2" cellspacing="2">
  <tr>
    <td colspan="2"><?php theHeader(); ?></td>
  </tr>
  <tr>
    <td height="250"><?php global $asin; if (is_null($asin)) theProductWindow();  else theVariationWindow(); ?>
    </td>
  </tr>
  </table>
</body>
</html>
