<xsl:stylesheet
    version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl"
    exclude-result-prefixes="php"
>
    <xsl:output method="xml" encoding="UTF-8" indent="no"/>

    <xsl:template match="youtube">
        <div class="videoWrapper">
            <iframe src="http://www.youtube.com/embed/{@id}" frameborder="0"></iframe>
        </div>
    </xsl:template>

    <xsl:template match="x">
        <p>y</p>
    </xsl:template>

    <xsl:template match="code">
        <xsl:copy-of select="php:function('__CLASS__::highlight', string(.), string(@lang))" />
    </xsl:template>

    <xsl:template match="img/@src">
        <xsl:attribute name="src">
            <xsl:copy-of select="php:function('__CLASS__::imageUrl', string(.))" />
        </xsl:attribute>
    </xsl:template>

    <xsl:template match="a/@href">
        <xsl:attribute name="href">
            <xsl:copy-of select="php:function('__CLASS__::linkUrl', string(.))" />
        </xsl:attribute>
    </xsl:template>

    <!-- the identity template -->
    <xsl:template match="@*|node()">
        <xsl:copy>
            <xsl:apply-templates select="@*|node()"/>
        </xsl:copy>
    </xsl:template>

</xsl:stylesheet>
