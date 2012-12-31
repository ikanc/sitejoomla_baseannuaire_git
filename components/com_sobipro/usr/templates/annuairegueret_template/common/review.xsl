<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
    <xsl:import href="navigation.xsl"/>
    <xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>

    <xsl:template name="reviews">
        <div id="sprrMsg" style="display:none; margin: 5px;">
            <div style="margin-top: 20px; padding: 0 .7em;">
                <p id="sprrMsgIn" style="margin-top: 5px;">
                    <i class="icon-info-sign icon-large"></i><xsl:text> </xsl:text><span style="float: left; margin-right: .3em;"></span>
                </p>
            </div>
        </div>
        <div style="clear: both;"/>
        <xsl:if test="count( //reviews/* )">
            <div style="clear: both;"/>
            <xsl:for-each select="//reviews/review">
                <xsl:call-template name="reviewsReportForm"/>
                <div class="sprrReviewCont sprrReviewContRow{position() mod 2}">
                    <div class="sprrRevAuthorHead">
                        <xsl:value-of select="php:function( 'SobiPro::Txt', 'SPRRA.REV_AUTHOR_HEAD', string(author), string(@date) )"/>
						
                    </div>
                    <div class="sprrRevOar">
                        <xsl:call-template name="ratingOut">
                            <xsl:with-param name="selected" select="round( @oar )"/>
                        </xsl:call-template>
                    </div>
                    <div class="sprrRevTitle">
                        <xsl:value-of select="title"/><xsl:text> </xsl:text>
                        <span>(<xsl:value-of select="format-number( @oar, '#0.00')"/>)
                        </span>
                    </div>
                    <div class="sprrRevText">
                        "<xsl:value-of select="input/text"/>"
                    </div>
                    <xsl:if test="count(input/positives/*) and //review_form/settings/positive_negative = 1">
                        <div class="sprrRevPosText">
                            <strong><xsl:value-of select="php:function( 'SobiPro::Txt', 'SPRRA.REV_POSITIVE_POINTS' )"/>:
                            </strong>
                            <xsl:for-each select="input/positives/*">
                                <xsl:value-of select="."/>
                                <xsl:if test="not( position() = last() )">
                                    <xsl:text>, </xsl:text>
                                </xsl:if>
                            </xsl:for-each>
                        </div>
                    </xsl:if>
                    <xsl:if test="count(input/negatives/*) and //review_form/settings/positive_negative = 1">
                        <div class="sprrRevNegText">
                            <strong><xsl:value-of select="php:function( 'SobiPro::Txt', 'SPRRA.REV_NEGATIVE_POINTS' )"/>:
                            </strong>
                            <xsl:for-each select="input/negatives/*">
                                <xsl:value-of select="."/>
                                <xsl:if test="not( position() = last() )">
                                    <xsl:text>, </xsl:text>
                                </xsl:if>
                            </xsl:for-each>
                        </div>
                    </xsl:if>
                    <xsl:if test="count(ratings/*)">
                        <div class="sprrRevRatings">
                            <xsl:for-each select="ratings/rating">
                                <div class="spRatingField">
                                    <div class="spRatingLabel">
                                        <span>
                                            <xsl:value-of select="@label"/>
                                        </span>
                                    </div>
                                    <xsl:call-template name="ratingOut">
                                        <xsl:with-param name="selected" select="."/>
                                    </xsl:call-template>
                                    <div class="spRatingNum">
                                        <xsl:text>&#160;(</xsl:text><xsl:value-of select="."/><xsl:text>/10)</xsl:text>
                                    </div>
                                </div>
                            </xsl:for-each>
                        </div>
                    </xsl:if>
                    <xsl:if test="count( //reviews/report_form/* )">
                        <button class="sprrRevReport">
                            <xsl:attribute name="name">
                                <xsl:value-of select="@id"/>
                            </xsl:attribute>
                            <xsl:value-of select="//reviews/report_form/texts/text[@label='report']"/>
                        </button>
                        <div style="clear:both"/>
                    </xsl:if>
                </div>
                <br/>
            </xsl:for-each>
            <xsl:apply-templates select="//reviews/navigation"/>
        </xsl:if>
    </xsl:template>


    <xsl:template name="reviewsReportForm">
        <xsl:if test="count( //reviews/report_form/* )">
            <div id="sprrRevReportForm" style="display: none"
                 title="{//reviews/report_form/texts/text[@label='window_title']}">
                <form target="#">
                    <div class="sprrRevReportLabel">
                        <xsl:value-of select="//reviews/report_form/texts/text[@label='select_subject']"/>
                    </div>
                    <div class="sprrRevReportField">
                        <select name="reviewReport[reason]">
                            <xsl:for-each select="//reviews/report_form/subjects/subject">
                                <option>
                                    <xsl:value-of select="."/>
                                </option>
                            </xsl:for-each>
                        </select>
                    </div>
                    <xsl:if test="//reviews/report_form/texts/text[@label='enter_name']">
                        <div class="sprrRevReportLabel">
                            <xsl:value-of select="//reviews/report_form/texts/text[@label='enter_name']"/>
                        </div>
                        <div class="sprrRevReportField">
                            <input type="text" name="reviewReport[author]"/>
                        </div>
                        <div class="sprrRevReportLabel">
                            <xsl:value-of select="//reviews/report_form/texts/text[@label='enter_email']"/>
                        </div>
                        <div class="sprrRevReportField">
                            <input type="text" name="reviewReport[email]"/>
                        </div>
                    </xsl:if>
                    <div class="sprrRevReportLabel">
                        <xsl:value-of select="//reviews/report_form/texts/text[@label='enter_subject']"/>
                    </div>
                    <div class="sprrRevReportField">
                        <input type="text" name="reviewReport[subject]"/>
                    </div>
                    <div class="sprrRevReportLabel">
                        <xsl:value-of select="//reviews/report_form/texts/text[@label='enter_text']"/>
                    </div>
                    <div class="sprrRevReportField">
                        <textarea rows="5" cols="15" name="reviewReport[message]"/>
                    </div>
                    <input type="hidden" name="reviewReport[rid]" id="reviewReportRid" value=""/>
                    <input type="hidden" name="{php:function( 'SobiPro::Token' )}" id="reviewReportRid" value="1"/>
                </form>
                <button id="sprrRevReportFormBt" style="display:none">
                    <xsl:value-of select="//reviews/report_form/texts/text[@label='send_bt']"/>
                </button>
            </div>
        </xsl:if>
    </xsl:template>

    <xsl:template name="reviewForm">
        <xsl:if test="//review_form/settings/review_enabled = 1">
            <script type="text/javascript">
                jQuery( function() {
                jQuery( '#spNegReview' ).tagsInput( { 'defaultText':'<xsl:value-of
                    select="php:function( 'SobiPro::Txt', 'SPRRA.FORM_ADD_POINT' )"/>','width':'470px','height':'55px' }
                );
                jQuery( '#spPosReview' ).tagsInput( { 'defaultText':'<xsl:value-of
                    select="php:function( 'SobiPro::Txt', 'SPRRA.FORM_ADD_POINT' )"/>','width':'470px','height':'55px' }
                );
                } );
            </script>
            <input id="spRrShowForm" type="button"
                   class="btn" style="float:right;">
                <xsl:attribute name="value">
                    <xsl:value-of select="php:function( 'SobiPro::Txt', 'SPRRA.FORM_WRITE_OWN_BT' )"/>
                </xsl:attribute>
            </input>
            <div id="spReviewCont" style="display:none;">
                <form id="sprr" target="#">
                    <fieldset>
                        
                        <xsl:if test="//review_form/settings/review_enabled">
                            <div id="spRevTitleCont">
                                <label><span class="spRevLabel">
                                    <xsl:value-of select="php:function( 'SobiPro::Txt', 'SPRRA.FORM_REV_TITLE' )"/>
                                </span></label>
                                <input name="spreview[title]" id="spRevTitle" type="text"/>
                            </div>
                            <div id="spRevCont">
                                <label><span class="spRevLabel">
                                    <xsl:value-of select="php:function( 'SobiPro::Txt', 'SPRRA.FORM_REVIEW' )"/>
                                </span></label>
                                <textarea name="spreview[review]" id="spReview"/>
                            </div>
                            <xsl:if test="//review_form/settings/rating_enabled">
                                <div id="spRatingCont">
                                    <xsl:call-template name="ratingForm"/>
                                </div>
                                <div style="clear: both;"/>
                            </xsl:if>
                            <xsl:if test="//review_form/settings/positive_negative = 1">
                                <div id="spPosReviewCont">
                                    <label><span class="spRevLabel">
                                        <xsl:value-of
                                                select="php:function( 'SobiPro::Tooltip', php:function( 'SobiPro::Txt', 'SPRRA.REV_POSITIVE_POINTS_EXPL' ), php:function( 'SobiPro::Txt', 'SPRRA.FORM_POS_REVIEW' ) )"
                                                disable-output-escaping="yes"/>
                                    </span></label>
                                    <input name="spreview[pos_review]" id="spPosReview" type="text"/>
                                </div>
                                <div id="spNegReviewCont">
                                    <label><span class="spRevLabel">
                                        <xsl:value-of
                                                select="php:function( 'SobiPro::Tooltip', php:function( 'SobiPro::Txt', 'SPRRA.REV_NEGATIVE_POINTS_EXPL' ), php:function( 'SobiPro::Txt', 'SPRRA.FORM_NEG_REVIEW' ) )"
                                                disable-output-escaping="yes"/>
                                    </span></label>
                                    <input name="spreview[neg_review]" id="spNegReview" type="text"/>
                                </div>
                            </xsl:if>
                            <xsl:if test="//review_form/settings/name_required = 1">
                                <div id="spRevVisNameCont">
                                    <label><span class="spRevLabel">
                                        <xsl:value-of
                                                select="php:function( 'SobiPro::Txt', 'SPRRA.FORM_VISITOR_NAME' )"/>
                                        <xsl:text>: </xsl:text>
                                    </span></label>
                                    <input name="spreview[visitor]" id="spRevVisName" type="text"/>
                                </div>
                            </xsl:if>
                            <xsl:if test="//review_form/settings/email_required = 1">
                                <div id="spRevVisMailCont">
                                    <label><span class="spRevLabel">
                                        
                                        <xsl:value-of
                                                select="php:function( 'SobiPro::Txt', 'SPRRA.FORM_VISITOR_MAIL' )"/>
                                        <xsl:text>: </xsl:text>
                                    </span></label>
                                    <input name="spreview[vmail]" id="spRevVisMail" type="text"/>
                                </div>
                            </xsl:if>
                        </xsl:if>
                        <input type="hidden" name="{//review_form/settings/token}" value="1"/>
                        <input type="hidden" name="spreview[sid]" value="{//entry/@id}"/>
                    </fieldset>
                </form>
                <div id="spSendBt">
                    <input id="spRrHideForm" type="button"
                           class="btn">
                        <xsl:attribute name="value">
                            <xsl:value-of select="php:function( 'SobiPro::Txt', 'SPRRA.FORM_CANCEL_BT' )"/>
                        </xsl:attribute>
                    </input>
                    <input type="submit" id="spRrSubmit"
                           class="btn btn-primary">
                        <xsl:attribute name="value">
                            <xsl:value-of select="php:function( 'SobiPro::Txt', 'SPRRA.FORM_SUBMIT_BT' )"/>
                        </xsl:attribute>
                    </input>
                </div>
            </div>
        </xsl:if>
    </xsl:template>

    <xsl:template name="ratingForm">
        
        <xsl:for-each select="//review_form/fields/field">
            <div class="spRatingField">
                <div class="spRatingLabel">
                    <span>
                        
                        <xsl:value-of select="label"/>
                    </span>
                </div>
                <xsl:variable name="fid">
                    <xsl:value-of select="@id"/>
                </xsl:variable>
                <xsl:for-each select="( //* )[ position() &lt;= 10 ]">
                    <input type="radio" class="sprrstar" value="{position()}" name="sprating[{$fid}]"/>
                </xsl:for-each>
            </div>
            <div style="clear: both;"/>
        </xsl:for-each>
    </xsl:template>

    <xsl:template name="ratingSummary">
        <xsl:param name="reviews"/>
        <xsl:if test="count( reviews/summary_rating )">
            <div class="sprrSumRating">
                <div class="sprrSumRatingHead">
                    <xsl:value-of
                            select="php:function( 'SobiPro::Txt', 'SPRRA.RATING_SUM_HEAD', string(reviews/summary_rating/overall/@count) )"/>
                </div>
                <div class="sprrSumRevOar">
                    <xsl:call-template name="ratingOut">
                        <xsl:with-param name="selected"
                                        select="format-number( reviews/summary_rating/overall, '#0.00')"/>
                    </xsl:call-template>
                </div>
                <div class="spRatingNum">
                    (<xsl:value-of select="reviews/summary_rating/overall"/>)
                </div>
                <div style="clear: both;"/>
                <div class="spRatingSummDetails">
                    <xsl:for-each select="reviews/summary_rating/fields/field">
                        <div class="spRatingLabel">
                            <span>
                                <xsl:value-of select="@label"/>
                            </span>
                        </div>
                        <xsl:call-template name="ratingOut">
                            <xsl:with-param name="selected" select="format-number( ., '#.00' )"/>
                        </xsl:call-template>
                        <div class="spRatingNum">
                            <xsl:text>&#160;</xsl:text><xsl:value-of select="."/><xsl:text>/10</xsl:text>
                        </div>
                        <div style="clear: both;"/>
                    </xsl:for-each>
                </div>
                <!--
                    something for Google - it's hidden because these class names are too obvious
                    and it can be that it will destroy the formatting if some other extension defines it too
                -->
                <span class="hreview-aggregate" style="display:none">
                    <span class="item">
                        <span class="fn">
                            <xsl:value-of select="/entry_details/entry/name"/>
                        </span>
                    </span>
                    <span class="rating">
                        <span class="average">
                            <xsl:value-of select="reviews/summary_rating/overall"/>
                        </span>
                        <span class="best">
                            <span class="value-title" title="10"/>
                        </span>
                        <span class="worst">
                            <span class="value-title" title="1"/>
                        </span>
                    </span>
                    <span class="count">
                        <xsl:value-of select="reviews/summary_rating/overall/@count"/>
                    </span>
                    Reviews
                </span>
                <div style="clear: both;"/>
            </div>
        </xsl:if>
    </xsl:template>

    <xsl:template name="ratingStars">
        <div class="sprrRatingStars">
            <xsl:call-template name="ratingOut">
                <xsl:with-param name="selected" select="round( reviews/summary_rating/overall )"/>
            </xsl:call-template>
        </div>
    </xsl:template>

    <xsl:template name="ratingOut">
        <xsl:param name="selected"/>
        <xsl:for-each select="( //* )[ position() &lt;= 10 ]">
            <div style="width: 8px;">
                <xsl:attribute name="class">
                    <xsl:choose>
                        <xsl:when test="position() &lt;= $selected">
                            <xsl:text>star-rating star-rating-applied star-rating-readonly star-rating-on</xsl:text>
                        </xsl:when>
                        <xsl:otherwise>star-rating star-rating-applied star-rating-readonly</xsl:otherwise>
                    </xsl:choose>
                </xsl:attribute>
                <a href="#">
                    <xsl:attribute name="style">
                        <xsl:choose>
                            <xsl:when test="position() mod 2">margin-left: 0px;</xsl:when>
                            <xsl:otherwise>margin-left: -8px;</xsl:otherwise>
                        </xsl:choose>
                    </xsl:attribute>
                </a>
            </div>
        </xsl:for-each>
    </xsl:template>
</xsl:stylesheet>
