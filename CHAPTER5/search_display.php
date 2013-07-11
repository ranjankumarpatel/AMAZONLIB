<?php
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Find Seller</title>
</head>
<body>
<form action="" method="get" name="sellerfinder" target="_self" id="seller_finder">
<h3>NOTE: Amazon has deprecated the CustomerContentLookup operation as well as Multi-operation requests, so this script no longer works very well.</h3>
  <p>Find a merchant selling a product with ASIN:
    <input name="asin" type="text" value="<?php echo $asin ?>" size="10" maxlength="10">
  </p>
  <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;in 
    <select name="condition" id="condition">
      <option value="All" <?php select_condition('All')?> >All</option>
      <option value="New" <?php select_condition('New')?> >New</option>
      <option value="Used" <?php select_condition('Used')?> >Used</option>
      <option value="Refurbished" <?php select_condition('Refurbished')?> >Refurbished</option>
      <option value="Collectible" <?php select_condition('Collectible')?> >Collectible</option>
    </select> 
    condition
</p>
  <p>...and located in these states: </p>
  <p>
    <select name="states[]" size="5" multiple id="states">
          <option value="All" <?php select_state('All')?> >All</option>
          <option VALUE="AL" <?php select_state('AL')?> >AL Alabama</option>
          <option VALUE="AK" <?php select_state('AK')?> >AK Alaska</option>
          <option VALUE="AZ" <?php select_state('AZ')?> >AZ Arizona</option>
          <option VALUE="AR" <?php select_state('AR')?> >AR Arkansas</option>
          <option VALUE="CA" <?php select_state('CA')?> >CA California</option>
          <option VALUE="CO" <?php select_state('CO')?> >CO Colorado</option>
          <option VALUE="CT" <?php select_state('CT')?> >CT Connecticut</option>
          <option VALUE="DE" <?php select_state('DE')?> >DE Delaware</option>
          <option VALUE="DC" <?php select_state('DC')?> >DC District of Columbia</option>
          <option VALUE="FL" <?php select_state('FL')?> >FL Florida</option>
          <option VALUE="GA" <?php select_state('GA')?> >GA Georgia</option>
          <option VALUE="HI" <?php select_state('HI')?> >HI Hawaii</option>
          <option VALUE="ID" <?php select_state('ID')?> >ID Idaho</option>
          <option VALUE="IL" <?php select_state('IL')?> >IL Illinois</option>
          <option VALUE="IN" <?php select_state('IN')?> >IN Indiana</option>
          <option VALUE="IA" <?php select_state('IA')?> >IA Iowa</option>
          <option VALUE="KS" <?php select_state('KS')?> >KS Kansas</option>
          <option VALUE="KY" <?php select_state('KY')?> >KY Kentucky</option>
          <option VALUE="LA" <?php select_state('LA')?> >LA Louisiana</option>
          <option VALUE="ME" <?php select_state('ME')?> >ME Maine</option>
          <option VALUE="MD" <?php select_state('MD')?> >MD Maryland</option>
          <option VALUE="MA" <?php select_state('MA')?> >MA Massachusetts</option>
          <option VALUE="MI" <?php select_state('MI')?> >MI Michigan</option>
          <option VALUE="MN" <?php select_state('MN')?> >MN Minnesota</option>
          <option VALUE="MS" <?php select_state('MS')?> >MS Mississippi</option>
          <option VALUE="MO" <?php select_state('MO')?> >MO Missouri</option>
          <option VALUE="MT" <?php select_state('MT')?> >MT Montana</option>
          <option VALUE="NE" <?php select_state('NE')?> >NE Nebraska</option>
          <option VALUE="NV" <?php select_state('NV')?> >NV Nevada</option>
          <option VALUE="NH" <?php select_state('NH')?> >NH New Hampshire</option>
          <option VALUE="NJ" <?php select_state('NJ')?> >NJ New Jersey</option>
          <option VALUE="NM" <?php select_state('NM')?> >NM New Mexico</option>
          <option VALUE="NY" <?php select_state('NY')?> >NY New York</option>
          <option VALUE="NC" <?php select_state('NC')?> >NC North Carolina</option>
          <option VALUE="ND" <?php select_state('ND')?> >ND North Dakota</option>
          <option VALUE="OH" <?php select_state('OH')?> >OH Ohio</option>
          <option VALUE="OK" <?php select_state('OK')?> >OK Oklahoma</option>
          <option VALUE="OR" <?php select_state('OR')?> >OR Oregon</option>
          <option VALUE="PA" <?php select_state('PA')?> >PA Pennsylvania</option>
          <option VALUE="RI" <?php select_state('RI')?> >RI Rhode Island</option>
          <option VALUE="SC" <?php select_state('SC')?> >SC South Carolina</option>
          <option VALUE="SD" <?php select_state('SD')?> >SD South Dakota</option>
          <option VALUE="TN" <?php select_state('TN')?> >TN Tennessee</option>
          <option VALUE="TX" <?php select_state('TX')?> >TX Texas</option>
          <option VALUE="UT" <?php select_state('UT')?> >UT Utah</option>
          <option VALUE="VT" <?php select_state('VT')?> >VT Vermont</option>
          <option VALUE="VA" <?php select_state('VA')?> >VA Virginia</option>
          <option VALUE="WA" <?php select_state('WA')?> >WA Washington</option>
          <option VALUE="WV" <?php select_state('WV')?> >WV West Virginia</option>
          <option VALUE="WI" <?php select_state('WI')?> >WI Wisconsin</option>
          <option VALUE="WY" <?php select_state('WY')?> >WY Wyoming</option>
    </select> 
  (select as many as you like) </p>
  <p>
    <input type="submit" name="Submit" value="Submit">
  </p>
</form>
<hr />
