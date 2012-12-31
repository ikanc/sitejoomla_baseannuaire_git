<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
    <xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>
    <xsl:template name="contact">
        <xsl:param name="field" />
        <div id="{$field/@nid}_container" class="spContactForm"
             title="{php:function( 'SobiPro::Txt' , 'AFCF_TPL_CONTACT_TITLE' )}" style="height:400px; width:520px">
            <form id="{$field/@nid}_form" action="index.php" >
                
                <xsl:if test="//visitor/@id = 0">

                    <label>
                        <xsl:value-of select="php:function('SobiPro::Txt', 'AFCF_TPL_YOUR_NAME')"/>* :
          </label>
                    <input type="text" name="spcform[name]" class="required"/>

                    <label>
                        <xsl:value-of select="php:function('SobiPro::Txt', 'AFCF_TPL_YOUR_EMAIL')"/>* :
          </label>
                     <input type="text" name="spcform[email]" class="required"/>
                </xsl:if>
                <label>
                    <xsl:value-of select="php:function('SobiPro::Txt', 'AFCF_TPL_MSG')"/>* :
        </label>
                <textarea name="spcform[report]" class="required" cols="50" rows="10"/>
                <div>
                    <button class="btn spCfCloseBt">
                        <xsl:value-of select="php:function('SobiPro::Txt', 'AFCF_TPL_CLOSE_FORM')"/>
                    </button>
          
                    <button class="btn spCfSendBt" id="{$field/@nid}_send_button">
                        <xsl:value-of select="php:function('SobiPro::Txt', 'AFCF_TPL_SEND_FORM')"/>
                    </button>
                    <input type="hidden" name="{$field/@token}" value="1"/>
                    <input type="hidden" name="format" value="raw"/>
                    <input type="hidden" name="sid" value="{//entry/@id}"/>
                    <input type="hidden" name="fid" value="{$field/@id}"/>
                </div>
            </form>
        </div>
    </xsl:template>
</xsl:stylesheet>
