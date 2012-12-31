<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
    <xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>
    <xsl:template name="report">
        <xsl:param name="field" />
        <div id="{$field/@nid}_container" class="spContactForm"
             title="{php:function( 'SobiPro::Txt' , 'AFCF_TPL_REPORT_TITLE' )}" style="height:400px; width:520px">
            <form id="{$field/@nid}_form" action="index.php" >
                <div class="spEmailName">
                    Email:<input type="text" name="email"/>
                    <br/>
                    Name:<input type="text" name="name"/>
                </div>
                <xsl:if test="//visitor/@id = 0">
                    <div>
                        <xsl:value-of select="php:function('SobiPro::Txt' , 'AFCF_TPL_YOUR_NAME')"/>
                        <input type="text" name="spcform[name]" class="required"/>
                    </div>
                    <div>
                        <xsl:value-of select="php:function('SobiPro::Txt' , 'AFCF_TPL_YOUR_EMAIL')"/>
                        <input type="text" name="spcform[email]" class="required"/>
                    </div>
                </xsl:if>
                <div>
                    <xsl:value-of select="php:function('SobiPro::Txt' , 'AFCF_TPL_REASON')"/>
                    <select name="spcform[reason]" class="required">
                        <option>
                            <xsl:value-of select="php:function('SobiPro::Txt', 'AFCF_TPL_REASON_SPAM')"/>
                        </option>
                        <option>
                            <xsl:value-of select="php:function('SobiPro::Txt', 'AFCF_TPL_REASON_BROKEN_LINK')"/>
                        </option>
                        <option>
                            <xsl:value-of select="php:function('SobiPro::Txt', 'AFCF_TPL_REASON_WRONG')"/>
                        </option>
                        <option>
                            <xsl:value-of select="php:function('SobiPro::Txt', 'AFCF_TPL_REASON_CAT')"/>
                        </option>
                        <option>
                            <xsl:value-of select="php:function('SobiPro::Txt', 'AFCF_TPL_REASON_OTHER')"/>
                        </option>
                    </select>
                </div>
                <div>
                    <xsl:value-of select="php:function('SobiPro::Txt', 'AFCF_TPL_REPORT')"/>
                    <textarea name="spcform[report]" class="required" cols="50" rows="10"/>
                </div>
                <div>
                    <button class="spCfCloseBt">
                        <xsl:value-of select="php:function('SobiPro::Txt', 'AFCF_TPL_CLOSE_FORM')"/>
                    </button>
                    <button class="spCfSendBt" id="{$field/@nid}_send_button">
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
