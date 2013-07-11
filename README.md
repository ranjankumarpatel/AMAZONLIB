amazonlib 2.1.1  February 19th, 2012
Web site: www.awsbook.com 
Author: Jason Levitt 

[NOTE: as of February 21st, 2012, Amazon has deprecated (e.g. turned off) a
bunch of functionality in the Product Advertising API. Though I'm sure there are
good technical reasons for doing so, the changes break many of the examples
in the book. In any case, I've fixed the remaining examples to work with
the August 1st, 2011 WSDL]

This archive contains all of the code samples from the book 
"The Web Developer's Guide To Amazon E-Commerce Service:
developing web applications using Amazon Web Services and PHP" 
which is described on  http://www.awsbook.com

The code samples, all written using PHP (a few use XSLT), illustrate various 
features of Amazon's E-Commerce Service 4.0 (now called Amazon Product 
Advertising API). Some of the examples require PHP5 (which
you should be using anyway :-). Some PHP extensions or PEAR libraries 
are required to run a few of the scripts. Programs that illustrate the 
APIs for the Amazon Inventory Management System and the Merchants@ API 
are also included. 

Amazon has deprecated (e.g. removed support for) several operations
that my sample code uses, including MultiOperation and ListLookup. These
code samples no longer work.  Many of the detailed elements, especially for
items in the photo and jewelry section, are no longer returns. So, my
watchcompare sample in Chapter 4 doesn't return watch details.

amazonlib 2.1.1 is a minor update to amazonlib 2.1 that primarily
addresses Amazon's support for previous versions of their API.
Amazon's January, 2012 notice states:

"As part of our continued effort to ensure that the Product Advertising API 
is an efficient and effective advertising tool, we’ve identified opportunities 
to streamline the API. To this end we will be removing support for Version 
other then 2011-08-01 on Feb 21, 2012."

So, amazonlib 2.1.1 ensures that support for version 2011-08-01 of the API is
included and also makes sure that everything runs on the the latest NuSOAP and
PEAR SOAP. 

Some changes:

* All examples tested under PHP 5.3.6
* NuSOAP 0.9.5 and PEAR SOAP 0.13 were used for examples requiring those APIs
* Updated everything to use the 2011-08-01 WSDL and REST versions
* Added a PEAR SOAP sample (2-7a.php) though it's not clear that
  either the PEAR SOAP or NuSOAP parsers can correctly parse all
  possible Amazon XML responses. 
* All examples now have the digital signature code included
* A few minor bug fixes
* Fixed some browse nodes that had gone away

A couple of things to note:

* Nusoap now runs under PHP5 (the problem before was a namespace collision 
  with PHP5's built-in SOAP parser).  
* Amazon E-Commerce Service (ECS) had been renamed to the
  Amazon Associates Web Service (AAWS), but it is now called
  the Amazon Product Advertising API.
============
