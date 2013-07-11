<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:aws="http://webservices.amazon.com/AWSECommerceService/2011-08-01">
<xsl:output method="html"/>
  <!-- the variable productdata holds the entire product node minus -->
  <!-- the items which do not have any pricing information          -->
  <xsl:variable name="productdata" select="/aws:ItemSearchResponse/aws:Items/aws:Item[count(aws:VariationSummary/aws:LowestPrice/aws:FormattedPrice) != 0 or count(aws:OfferSummary/aws:LowestNewPrice/aws:FormattedPrice) != 0]"/>
  <xsl:variable name="maxitems" select="/aws:*/aws:OperationRequest/aws:Arguments/aws:Argument[@Name='maxitems']/@Value"/>
  <xsl:variable name="maxtitlelen" select="/aws:*/aws:OperationRequest/aws:Arguments/aws:Argument[@Name='maxtitlelen']/@Value"/>
  <xsl:variable name="searchindex" select="/aws:*/aws:OperationRequest/aws:Arguments/aws:Argument[@Name='SearchIndex']/@Value"/>
  <xsl:variable name="no-image-url">
    <xsl:choose>
      <xsl:when test="($searchindex)='Video' ">http://g-images.amazon.com/images/G/01/video/icons/video-no-image.gif</xsl:when>
      <xsl:when test="($searchindex)='Books' ">http://g-images.amazon.com/images/G/01/books/icons/books-no-image.gif</xsl:when>
      <xsl:when test="($searchindex)='Kitchen' ">http://g-images.amazon.com/images/G/01/kitchen/placeholder-icon.gif</xsl:when>
      <xsl:when test="($searchindex)='Jewelry' ">http://g-images.amazon.com/images/G/01/jewelry/nav/jewelry-icon-no-image-avail.gif</xsl:when>
      <xsl:when test="($searchindex)='Apparel' ">http://g-images.amazon.com/images/G/01/apparel/general/apparel-no-image.gif</xsl:when>
      <xsl:when test="($searchindex)='GourmetFood' ">http://g-images.amazon.com/images/G/01/gourmet/gourmet-no-image.gif</xsl:when>
      <xsl:otherwise>http://g-images.amazon.com/images/G/01/v9/icons/no-picture-icon.gif</xsl:otherwise>
    </xsl:choose>
  </xsl:variable>
<xsl:template match="/">
    <table bgcolor="ECF8FF">
      <tr>
        <div style="text-align:center">Product Sense</div>
      </tr>
      <xsl:for-each select="$productdata[position() &lt; $maxitems+1]">
        <tr>
          <td>
            <xsl:choose>
              <xsl:when test="count(aws:SmallImage/aws:URL) != 0">
                <img src="{aws:SmallImage/aws:URL}" alt="{aws:ItemAttributes/aws:Title}"/>
              </xsl:when>
              <xsl:otherwise>
                <img src="{$no-image-url}" alt="{aws:ItemAttributes/aws:Title}"/>
              </xsl:otherwise>
            </xsl:choose>
          </td>
          <td>
            <a href="{aws:DetailPageURL}" title="{aws:ItemAttributes/aws:Title}" target="_blank">
              <xsl:choose>
                <xsl:when test="string-length(aws:ItemAttributes/aws:Title) &gt;  $maxtitlelen">
                  <xsl:value-of select="concat(substring(aws:ItemAttributes/aws:Title, 1, $maxtitlelen),'...')"/>
                </xsl:when>
                <xsl:otherwise>
                  <xsl:value-of select="aws:ItemAttributes/aws:Title"/>
                </xsl:otherwise>
              </xsl:choose>
            </a>
            <div>
List Price:
<xsl:choose>
                <xsl:when test="count(aws:ItemAttributes/aws:ListPrice/aws:FormattedPrice) != 0">
                  <xsl:value-of select="aws:ItemAttributes/aws:ListPrice/aws:FormattedPrice"/>
                </xsl:when>
                <xsl:otherwise>
									N/A
								</xsl:otherwise>
              </xsl:choose>
            </div>
            <div>
Your Price: 
<xsl:choose>
                <xsl:when test="count(aws:VariationSummary/aws:LowestPrice/aws:FormattedPrice) != 0">
                  <xsl:value-of select="aws:VariationSummary/aws:LowestPrice/aws:FormattedPrice"/>
                </xsl:when>
                <xsl:when test="count(aws:OfferSummary/aws:LowestNewPrice/aws:FormattedPrice) != 0">
                  <xsl:value-of select="aws:OfferSummary/aws:LowestNewPrice/aws:FormattedPrice"/>
                </xsl:when>
                <xsl:otherwise>
									N/A
								</xsl:otherwise>
              </xsl:choose>
              <br/>
            </div>
          </td>
        </tr>
      </xsl:for-each>
    </table>
  </xsl:template>
</xsl:stylesheet>