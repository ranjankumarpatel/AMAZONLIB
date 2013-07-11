<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Watch Compare</title>
</head>
<body>
<form action="" method="post" name="watch_search" target="_self" id="watch_search">
  <hr />
  <p><b>Find Watches:</b> Type:
    <select name="type" id="type">
      <option value="all" <?php select_type('all')?> >All</option>
      <option value="mens" <?php select_type('mens')?> >Mens</option>
      <option value="womens" <?php select_type('womens')?> >Womens</option>
      <option value="childrens" <?php select_type('childrens')?> >Childrens</option>
    </select>
    Brand:
    <select name="brand" id="brand">
      <option value="all" <?php select_brand('all')?> >All</option>
      <option value="Anne%20Klein" <?php select_brand('Anne%20Klein')?> >Anne Klein</ option>
      <option value="Seiko" <?php select_brand('Seiko')?> >Seiko</option>
  <option value="Timex" <?php select_brand('Timex')?> >Timex</option>
      <option value="Skagen" <?php select_brand('Skagen')?> >Skagen</option>
      <option value="Fossil" <?php select_brand('Fossil')?> >Fossil</option>
      <option value="Guess" <?php select_brand('Guess')?> >Guess</option>
    </select>
    Price:
    <select name="price" id="price">
      <option value="all" <?php select_price('all')?> >All</option>
      <option value="0-5000" <?php select_price('0-5000')?> >$1-$50</option>
      <option value="5000-10000" <?php select_price('5000-10000')?> >$50-$100</ option>
      <option value="10000-25000" <?php select_price('10000-25000')?> >$100-$250</ option>
      <option value="25000-50000" <?php select_price('25000-50000')?> >$250-$500</ option>
      <option value="50000-100000" <?php select_price('50000-100000')?> >$500-$1000</ option>
      <option value="100000-500000" <?php select_price('100000-500000')?> >$1000-$5000</option>
      <option value="500000-9999999" <?php select_price('500000-9999999')?> >$5000-?</option>
    </select>
    Material:
    <select name="material" id="material">
      <option value="all" <?php select_material('all')?> >All</option>
      <option value="stainless steel" <?php select_material('stainless steel')?> > stainless steel</option>
      <option value="glass" <?php select_material('glass')?> >glass</option>
      <option value="resin" <?php select_material('resin')?> >resin</option>
      <option value="rubber" <?php select_material('rubber')?> >rubber</option>
      <option value="diamond" <?php select_material('diamond')?> >diamond</option>
      <option value="gold" <?php select_material('gold')?> >gold</option>
      <option value="gem" <?php select_material('gem')?> >gem</option>
    </select>
  </p>
<p>Sort Results By:
    <select name="sort" id="sort">
      <option value="salesrank" <?php select_sort('salesrank')?> >Bestselling</ option>
      <option value="reviewrank" <?php select_sort('reviewrank')?> >Customer Reviews: High to Low</option>
      <option value="price" <?php select_sort('price')?> >Price: Low to High</option>
      <option value="-price" <?php select_sort('-price')?> >Price: High to Low</option>
    </select>
    <input type="submit" name="search" value="Search">
  </p>
</form>
<hr />
<?php theContent() ?>
</body>
</html>
