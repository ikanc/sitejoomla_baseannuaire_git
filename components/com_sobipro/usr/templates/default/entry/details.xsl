<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8" />

<xsl:include href="../common/topmenu.xsl" />
<xsl:include href="../common/manage.xsl" />
<xsl:include href="../common/alphamenu.xsl" />
<xsl:include href="../common/review.xsl" />

<xsl:template match="/entry_details">
<div class="SPDetails">
    
  <div>
      <xsl:apply-templates select="menu" />
      <xsl:apply-templates select="alphaMenu" />
    </div>
  
<div style="clear:both;"/>
<div class="SPDetailEntry">

<div style="float:left;"><xsl:call-template name="manage" /></div>
<div style="float:right;"><xsl:call-template name="ratingStars"/></div>
<div style="clear:both;"/>
<h2 class="SPTitle"><xsl:value-of select="entry/name" /></h2>
<div class="spField" id="title">
  <xsl:value-of select="entry/fields/field_title/data"/>
  <xsl:text> </xsl:text>
  <xsl:value-of select="entry/fields/field_title/@suffix"/>
</div>  
  
<div class="taa-tabs minimal hide-title cross-fade">
<section>
  <h1><i class="icon-pushpin icon-large"></i> Description</h1>
                              
          <div class="spField" id="resume_activite">
          <xsl:value-of select="entry/fields/field_resume_activite/data" disable-output-escaping="yes"/>
          <xsl:text> </xsl:text>
          <xsl:value-of select="entry/fields/field_resume_activite/@suffix"/>
          </div>
      
      <div class="SPDE-Logo hidden-phone">
            <xsl:element name="img">
              <xsl:attribute name="src">
              <xsl:value-of select="entry/fields/field_logo/data/@image" />
              </xsl:attribute>
              <xsl:attribute name="alt">
              <xsl:value-of select="entry/name" />
              </xsl:attribute>
            </xsl:element>
          </div>
      
          <div class="spField" id="activite_detaillee">
          <xsl:value-of select="entry/fields/field_activite_detaillee/data" disable-output-escaping="yes"/>
          <xsl:text> </xsl:text>
          <xsl:value-of select="entry/fields/field_activite_detaillee/@suffix"/>
          </div>
      
		<div class="SPDE-Website">
			<xsl:if test="string-length(entry/fields/field_site_internet/data) &gt; 0">
			  
				<div class="spField" id="internet">          
				  <a>
					 <xsl:attribute name="href">
					  <xsl:value-of select="entry/fields/field_site_internet/data/a/@href" />
					 </xsl:attribute>
					 <xsl:attribute name="target">
					  <xsl:text>_blank</xsl:text>
					 </xsl:attribute>
					 Visiter le site internet
				  </a>
				</div>
			  
			</xsl:if>
		</div>
	  
		<div class="SPDE-Links">	   
			<xsl:if test="string-length(entry/fields/field_facebook/data) &gt; 0">
				
			  <div class="spField" id="facebook">          
			  <a>
				 <xsl:attribute name="href">
				  <xsl:value-of select="entry/fields/field_facebook/data/a/@href" />
				 </xsl:attribute>
				 <xsl:attribute name="target">
				  <xsl:value-of select="_blank" />
				 </xsl:attribute>
				 <i class="icon-facebook icon-large"></i>
			  </a>
			  </div>
			  
			</xsl:if>
			<xsl:if test="string-length(entry/fields/field_twitter/data) &gt; 0">
				
			  <div class="spField" id="twitter">          
			  <a>
				 <xsl:attribute name="href">
				  <xsl:value-of select="entry/fields/field_twitter/data/a/@href" />
				 </xsl:attribute>
				 <xsl:attribute name="target">
				  <xsl:value-of select="_blank" />
				 </xsl:attribute>
				 <i class="icon-twitter icon-large"></i>
			  </a>
			  </div>
			  
			</xsl:if>
			<xsl:if test="string-length(entry/fields/field_googleplus/data) &gt; 0">
				
			  <div class="spField" id="googleplus">          
			  <a>
				 <xsl:attribute name="href">
				  <xsl:value-of select="entry/fields/field_googleplus/data/a/@href" />
				 </xsl:attribute>
				 <xsl:attribute name="target">
				  <xsl:value-of select="_blank" />
				 </xsl:attribute>
				 <i class="icon-google-plus icon-large"></i>
			  </a>
			  </div>
			  
			</xsl:if>
		</div>
 
    <div class="SPDE-Galery">
        <div id="spdecarousel" class="carousel slide">
      <div class="carousel-inner">
        <xsl:for-each select="entry/fields/*">
          <xsl:if test="contains(name(),'field_photo_')">
          <div class="item">
           <xsl:element name="img">
          <xsl:attribute name="src">
          <xsl:value-of select="data/@image" />
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
    </div>
    
	<div class="SPDE-Tags">
		<xsl:if test="count(entry/fields/field_tags/data/*)">
			<i class="icon-tags icon-large"></i>
		   
			<xsl:text> </xsl:text>
			<xsl:copy-of select="entry/fields/field_tags/data/*" />
		</xsl:if>
	</div>
    
       
</section>
<section>
  <h1> <i class="icon-info-sign icon-large"></i> Infos pratiques</h1>
    
    <div class="SPDetailEntry-Sidebar">
          
      <div class="SPDE-Print hidden-phone">
      <a href="#" style="float:right;" onClick="window.print()"><i class="icon-print"></i> Imprimer cette fiche</a>
      </div>
          
      <address>
          <div class="SPDetailEntry-Sidebar-adresse">
      <strong><xsl:value-of select="entry/name" /></strong>  
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
      </address>
      <address>
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
       </address>
        </div>
    <div class="SPDE-Map">
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
    <div class="directions-show closed"><xsl:text>Afficher/masquer les détails de l'itinéraire</xsl:text></div>
      </div>
</section>
<section>
  <h1><i class="icon-comments icon-large"></i> Commentaires</h1>
  <xsl:call-template name="ratingSummary"/>
    <xsl:call-template name="reviewForm"/>
    <div style="clear: both;"/><br/>
    <xsl:call-template name="reviews"/>
  
</section>
<section>
  <h1><i class="icon-phone icon-large"></i> Contact</h1>
  <div id='contact'>
    <xsl:call-template name="contact">
            <xsl:with-param name="field" select="/entry_details/entry/fields/field_contact/data"/>
          </xsl:call-template>  
  </div>
</section>
     
</div> <!--tabs-->

   {loadposition social}
    
</div> <!--container-->
<div style="clear:both;"></div>
     
</div>
</xsl:template>
</xsl:stylesheet>