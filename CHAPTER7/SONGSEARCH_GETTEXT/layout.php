<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo TO_ENCODING ?>" />
<title><?php echo gettext('Song Title Search') ?></title>
</head>
<body>
<table width="100%" border="1">
  <tr>
    <td width="100%" height="100"><div align="center">
        <h3><?php echo gettext('Song Title Search') ?></h3>
      </div>
      <form action="" method="post" enctype="multipart/form-data" name="musicsearch" target="_self" id="musicsearch" accept-charset="<?php echo TO_ENCODING ?>">
        <p> <?php echo gettext('Enter Song Title Keywords') ?>:&nbsp;
          <input name="songtitle" type="text" id="songtitle" value="" size="50" maxlength="70" />
&nbsp;&nbsp;
          <input type="submit" name="submit" value="<?php echo gettext('Submit') ?>" />
        </p>
      </form>
      <div style="text-align: right;"><?php echo gettext('Choose a site to search') ?>:&nbsp;<a href="<?php echo$_SERVER['PHP_SELF'] ?>?locale=<?php echo LOCALE_DE ?>"><img src="de.png" border="0" /></a>&nbsp;<a href="<?php echo$_SERVER['PHP_SELF'] ?>?locale=<?php echo LOCALE_US ?>"><img src="us.png" border="0" /></a>&nbsp;<a href="<?php echo$_SERVER['PHP_SELF'] ?>?locale=<?php echo LOCALE_UK ?>"><img src="uk.png" border="0" /></a>&nbsp;<a href="<?php echo$_SERVER['PHP_SELF'] ?>?locale=<?php echo LOCALE_JP ?>"><img src="jp.png" border="0"/></a>&nbsp;<a href="<?php echo$_SERVER['PHP_SELF'] ?>?locale=<?php echo LOCALE_FR ?>"><img src="fr.png" border="0"/></a><b>amazon.<?php echo DOMAIN ?></b></div></td>
  </tr>
  <tr><td>
    <?php theResults() ?>
  </td></tr>
</table>
</body>
</html>
