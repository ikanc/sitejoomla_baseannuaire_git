<?xml version="1.0" encoding="UTF-8"?>
<!-- 
 SobiPro Template SobiRestara
 Authors: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 Copyright (C) 2012 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 Released under Sigsiu.NET Template License V1
 -->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>    
  <xsl:template match="/entry_details">
    <div class="SPDetails">
      <div class="SobiPro componentheading">
        <xsl:value-of select="section" />
      </div>
      <div style="clear:both;" />
	  <script>window.print();</script>
      <h1 class="SPTitle"><xsl:value-of select="entry/name" /></h1>
      <xsl:for-each select="entry/fields/*">
        <div>
          <xsl:attribute name="class">
            <xsl:value-of select="@css_class" />
          </xsl:attribute>
          <xsl:if test="label/@show = 1">
            <xsl:if test="string-length( data )">
              <strong><xsl:value-of select="label" /><xsl:text>: </xsl:text></strong>
            </xsl:if>          
          </xsl:if>
          <xsl:choose>
            <xsl:when test="count(data/*)">
              <xsl:copy-of select="data/*"/>  
            </xsl:when>
            <xsl:otherwise>
              <xsl:if test="string-length( data ) &gt; 0">  
                <xsl:value-of select="data" disable-output-escaping="yes" />
              </xsl:if>
            </xsl:otherwise>
          </xsl:choose>
          <xsl:text> </xsl:text>
          <xsl:value-of select="@suffix"/>
        </div>
      </xsl:for-each>
      <div class="spclear" />
    </div>
  </xsl:template>
</xsl:stylesheet>