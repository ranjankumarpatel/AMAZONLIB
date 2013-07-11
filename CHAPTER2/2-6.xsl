<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:aws="http://webservices.amazon.com/AWSECommerceService/2011-08-01" exclude-result-prefixes="aws">
  <xsl:output method="html"/>
  <xsl:template match="aws:ItemLookupResponse">
    <html>
      <body>
        <table cellpadding="2" cellspacing="0">
          <tr>
            <td>
              <a href="{aws:Items/aws:Item/aws:DetailPageURL}" target="_blank">
                <xsl:value-of select="aws:Items/aws:Item/aws:ItemAttributes/aws:Title"/>
              </a>
            </td>
          </tr>
        </table>
      </body>
    </html>
  </xsl:template>
</xsl:stylesheet>
