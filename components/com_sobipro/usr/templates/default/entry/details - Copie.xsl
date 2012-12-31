<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8" />

<xsl:include href="../common/topmenu.xsl" />
<xsl:include href="../common/manage.xsl" />
<xsl:include href="../common/alphamenu.xsl" />

<xsl:template match="/entry_details">
<div class="SPDetails">
    <div>
      <xsl:apply-templates select="menu" />
      <xsl:apply-templates select="alphaMenu" />
    </div>
    <div style="clear:both;"/>

    <xsl:call-template name="manage" />
   
    <div class="SPDetailEntry container">
    <div class="row">
    <div class="span3 SPDetailEntry-Left">
      <div class="SPDE-Galery">
        <div class="carousel" data-interval="8000" data-autoplay="" data-transition="fade">
        <xsl:for-each select="entry/fields/*">
          <xsl:if test="contains(name(),'field_photo_')">
          <div class="carousel-item">
             <xsl:element name="img">
            <xsl:attribute name="src">
            <xsl:value-of select="data/@thumbnail" />
            </xsl:attribute>
            <xsl:attribute name="alt">
            <xsl:value-of select="entry/name" />
            </xsl:attribute>
          </xsl:element>
          </div>  
          </xsl:if>
        </xsl:for-each>
        </div>
      </div>
      
      <div class="SPDE-Map well">
        <xsl:text>{mosmap Largeur='100%'|Hauteur='400'|tooltip='</xsl:text>
        <xsl:value-of select="entry/name" /><xsl:text>'|text ='</xsl:text>
        <strong><xsl:value-of select="entry/name" /></strong><br/>
        <xsl:value-of select="entry/fields/field_street/data"/><br/>
        <xsl:value-of select="entry/fields/field_zip/data"/><xsl:text> </xsl:text>
        <xsl:value-of select="entry/fields/field_city/data"/>
        <xsl:text>'|address ='</xsl:text>
        <xsl:value-of select="entry/fields/field_street/data"/><xsl:text>, </xsl:text>
        <xsl:value-of select="entry/fields/field_zip/data"/><xsl:text> </xsl:text>
        <xsl:value-of select="entry/fields/field_city/data"/>
        <xsl:text>' |align='center'}</xsl:text>
      </div>
    </div>
    
    <div class="span3 SPDetailEntry-Sidebar pull-right">
          <div class="SPDE-Print"><a href="#" onClick="window.print()">Imprimer cette fiche détaillée</a></div>
          <div class="SPDE-Logo">
            <xsl:element name="img">
              <xsl:attribute name="src">
              <xsl:value-of select="entry/fields/field_logo/data/@thumbnail" />
              </xsl:attribute>
              <xsl:attribute name="alt">
              <xsl:value-of select="entry/name" />
              </xsl:attribute>
            </xsl:element>
          </div>
          <div class="SPDetailEntry-Sidebar-adresse">
            <div class="spField">
              <xsl:value-of select="entry/fields/field_street/data"/>
              <xsl:text> </xsl:text>
              <xsl:value-of select="entry/fields/field_street/@suffix"/>
            </div>
            <div class="spField">
              <xsl:value-of select="entry/fields/field_street2/data"/>
              <xsl:text> </xsl:text>
              <xsl:value-of select="entry/fields/field_street2/@suffix"/>
            </div>
            <div class="spField">
              <xsl:value-of select="entry/fields/field_zip/data"/>
              <xsl:text> </xsl:text>
              <xsl:value-of select="entry/fields/field_zip/@suffix"/>
              <xsl:value-of select="entry/fields/field_city/data"/>
              <xsl:text> </xsl:text>
              <xsl:value-of select="entry/fields/field_city/@suffix"/>
            </div>
          </div>
          <div class="SPDetailEntry-Sidebar-telephone">
            <xsl:if test="string-length(entry/fields/field_phone/data) &gt; 0">
            <div class="spField">
            <strong><xsl:value-of select="entry/fields/field_phone/label" />: </strong>
            <xsl:value-of select="entry/fields/field_phone/data"/>
            <xsl:text> </xsl:text>
            <xsl:value-of select="entry/fields/field_phone/@suffix"/>
            </div>
            </xsl:if>
            <xsl:if test="string-length(entry/fields/field_fax/data) &gt; 0">
            <div class="spField">
            <strong><xsl:value-of select="entry/fields/field_fax/label" />: </strong>
            <xsl:value-of select="entry/fields/field_fax/data"/>
            <xsl:text> </xsl:text>
            <xsl:value-of select="entry/fields/field_fax/@suffix"/>
            </div>
            </xsl:if>
            <xsl:if test="string-length(entry/fields/field_mobile/data) &gt; 0">
            <div class="spField">
            <strong><xsl:value-of select="entry/fields/field_mobile/label" />: </strong>
            <xsl:value-of select="entry/fields/field_mobile/data"/>
            <xsl:text> </xsl:text>
            <xsl:value-of select="entry/fields/field_mobile/@suffix"/>
            </div>
            </xsl:if>
          </div>
          <xsl:if test="string-length(entry/fields/field_email/data) &gt; 0">
          <div class="SPDetailEntry-Sidebar-email">
              <div class="spField" id="internet">          
              <a>
                 <xsl:attribute name="href">
                  <xsl:value-of select="entry/fields/field_email/data/a/@href" />
                 </xsl:attribute>
                 <strong><xsl:text>Envoyer un email</xsl:text></strong>
              </a>
            </div>
          </div>
          </xsl:if>
          <xsl:if test="string-length(entry/fields/field_site_internet/data) &gt; 0">
          <div class="SPDetailEntry-Sidebar-internet">
            <div class="spField" id="internet">          
              <a>
                 <xsl:attribute name="href">
                  <xsl:value-of select="entry/fields/field_site_internet/data/a/@href" />
                 </xsl:attribute>
                 <xsl:attribute name="target">
                  <xsl:text>_blank</xsl:text>
                 </xsl:attribute>
                 <strong><xsl:text>Visiter le site internet</xsl:text></strong>
              </a>
            </div>
          </div>
          </xsl:if>
          <xsl:if test="string-length(entry/fields/field_page_facebook/data) &gt; 0">
          <div class="SPDetailEntry-Sidebar-social">
            <div class="SPDetailEntry-Sidebar-facebook">
              <div class="spField" id="facebook">          
              <a>
                 <xsl:attribute name="href">
                  <xsl:value-of select="entry/fields/field_page_facebook/data/a/@href" />
                 </xsl:attribute>
                 <xsl:attribute name="target">
                  <xsl:value-of select="_blank" />
                 </xsl:attribute>
                 <strong><xsl:text>Visiter la page Facebook</xsl:text></strong>
              </a>
            </div>
            </div>
          </div>
          </xsl:if>
          <div class="SPDetailEntry-Sidebar-infos">
            <xsl:if test="string-length(entry/fields/field_horaires_ouverture/data) &gt; 0">
            <div class="SPDetailEntry-Sidebar-horaires">
              <div class="spField">
                <strong><xsl:value-of select="entry/fields/field_horaires_ouverture/label" />: </strong>
              </div>
              <div class="spField">
                <xsl:value-of select="entry/fields/field_horaires_ouverture/data" disable-output-escaping="yes"/>
                <xsl:text> </xsl:text>
                <xsl:value-of select="entry/fields/field_horaires_ouverture/@suffix"/>
              </div>
            </div>
            </xsl:if>
            
          </div>
        </div>
   
        <div class="span6 SPDetailEntry-Center">
          <h1 class="SPTitle"><xsl:value-of select="entry/name" /></h1>  
          <div class="spField" id="title">
          <xsl:value-of select="entry/fields/field_title/data"/>
          <xsl:text> </xsl:text>
          <xsl:value-of select="entry/fields/field_title/@suffix"/>
          </div>          
          <div class="spField" id="resume_activite">
          <xsl:value-of select="entry/fields/field_resume_activite/data" disable-output-escaping="yes"/>
          <xsl:text> </xsl:text>
          <xsl:value-of select="entry/fields/field_resume_activite/@suffix"/>
          </div>
          <div class="spField" id="activite_detaillee">
          <xsl:value-of select="entry/fields/field_activite_detaillee/data" disable-output-escaping="yes"/>
          <xsl:text> </xsl:text>
          <xsl:value-of select="entry/fields/field_activite_detaillee/@suffix"/>
          </div>
          
          <xsl:if test="count(entry/categories)">
            <div class="spEntryCats">
              <xsl:value-of select="php:function( 'SobiPro::Txt' , 'Catégorie:' )" /><xsl:text> </xsl:text>
              <xsl:for-each select="entry/categories/category">
                <a>
                <xsl:attribute name="href">
                <xsl:value-of select="@url" />
                </xsl:attribute>
                <xsl:value-of select="." />
                </a>
                <xsl:if test="position() != last()">
                <xsl:text> | </xsl:text>
                </xsl:if>
              </xsl:for-each>
            </div>
          </xsl:if>
            
          {loadposition social}
        </div>
        
        
     
    </div>
             
    </div>
    <div style="clear:both;"></div>
     
</div>
</xsl:template>
</xsl:stylesheet>