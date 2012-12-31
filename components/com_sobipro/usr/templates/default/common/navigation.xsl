<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" encoding="UTF-8"/>
	<xsl:template match="navigation" name="navigation">
		<div class="pagination">
			<ul>
				<xsl:if test="count( sites/* ) &gt; 0">
					
					<xsl:for-each select="sites/site">
						<xsl:variable name="limit">
							<xsl:choose>
								<xsl:when test="../../current_site &lt; 4">
									<xsl:value-of select="8 - ../../current_site" />
								</xsl:when>
								<xsl:when test="../../current_site &gt; count( ../../sites/* ) - 8">
									<xsl:value-of select="7 - ( ../../all_sites  - ../../current_site )" />
								</xsl:when>
								<xsl:otherwise>4</xsl:otherwise>
							</xsl:choose>
						</xsl:variable>
						<xsl:variable name="show">
							<xsl:choose>
								<xsl:when test="(.) &gt; ( ../../current_site - $limit ) and (.) &lt; ../../current_site">1</xsl:when>
								<xsl:when test="(.) &lt; ( ../../current_site + $limit ) and (.) &gt; ../../current_site">2</xsl:when>
								<xsl:when test="(.) = ../../current_site">3</xsl:when>
								<xsl:when test="number(.) != (.)">4</xsl:when>
								<xsl:otherwise>0</xsl:otherwise>
							</xsl:choose>
						</xsl:variable>
						<xsl:if test="$show &gt; 0">
						
							<li>
							<xsl:attribute name="class">
								
								<xsl:choose>
										<xsl:when test="@url">
											<xsl:value-of select="enabled" />
											<xsl:text>enabled</xsl:text>
										</xsl:when>
										<xsl:otherwise>
											
											<xsl:text>disabled</xsl:text>
												
										</xsl:otherwise>
								</xsl:choose>
							</xsl:attribute>
							
							<xsl:choose>
										<xsl:when test="@url">
											<a href="{@url}"><xsl:value-of select="." /></a>
										</xsl:when>
										<xsl:otherwise>
											
											<a href="#"><xsl:value-of select="." /></a>
												
										</xsl:otherwise>
							</xsl:choose>
								
							</li>
						</xsl:if>
					</xsl:for-each>

				</xsl:if>
			</ul>
		</div>
	</xsl:template>
</xsl:stylesheet>
