<?xml version="1.0" encoding="UTF-8"?>

<!-- $Id$ -->
<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" exclude-result-prefixes="fo">
    <xsl:output method="xml" version="1.0" omit-xml-declaration="no" indent="yes"/>
    <xsl:param name="logo"/>
    <xsl:param name="tipo_documento"/>
    <xsl:param name="qrc"/>
    <xsl:param name="cadena_original"/>
    <xsl:param name="importe_letra"/>
    <xsl:variable name="smallcase" select="'abcdefghijklmnopqrstuvwxyz'" />
    <xsl:variable name="uppercase" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'" />
    <xsl:template match="Comprobante">
        <fo:root 
            xmlns:fo="http://www.w3.org/1999/XSL/Format"
            font-size="8pt"
            line-height="10pt"
            space-after.optimum="10pt"
            color="black"
            font-family="Helvetica">
            <fo:layout-master-set>
                <fo:simple-page-master master-name="simpleA4" page-height="29.7cm" page-width="21cm" margin-top="1.5cm" margin-bottom="3cm" margin-left="1cm" margin-right="1cm">
                    <fo:region-body region-name="xsl-region-body" margin-bottom="4.5cm" margin-top="3.5cm"/>
                    <fo:region-before region-name="xsl-region-before" extent="3cm"/>
                    <fo:region-after region-name="xsl-region-after" extent="7cm"/>
                </fo:simple-page-master>
            </fo:layout-master-set>
            <fo:page-sequence master-reference="simpleA4" initial-page-number="1">
                <fo:static-content flow-name="xsl-region-before">
                    <fo:block padding-bottom="15pt">
                        <fo:table table-layout="fixed" width="100%" border-collapse="separate">
                            <fo:table-column column-width="4cm"/>
                            <fo:table-column column-width="9.5cm"/>
                            <fo:table-column column-width="5cm"/>
                            <fo:table-body>
                                <fo:table-cell>
                                    <fo:block padding="2pt 2pt 2pt 2pt">
                                        <fo:external-graphic 
                                            width="100%"
                                            content-height="100%"
                                            content-width="scale-to-fit"
                                            src="{$logo}"/>
                                    </fo:block>
                                </fo:table-cell>
                                <fo:table-cell>
                                    <xsl:apply-templates select="Emisor"/>
                                </fo:table-cell>
                                <fo:table-cell border-width="0.5px" border-style="solid" border-color="#6BA5D9" height="3cm">
                                    <fo:block text-align="center">
                                        <fo:table>
                                            <fo:table-column column-width="100%"/>
                                            <fo:table-header>
                                                <fo:table-row width="100%">
                                                    <fo:table-cell color="white" background-color="#6BA5D9" font-weight="bold" font-size="10pt">
                                                        <xsl:call-template name="folioComprobante">
                                                                <xsl:with-param name="tipo" select="$tipo_documento" />
                                                                <xsl:with-param name="serie" select="@Serie" />
                                                                <xsl:with-param name="folio" select="@Folio" />
                                                        </xsl:call-template>
                                                    </fo:table-cell>
                                                </fo:table-row>
                                            </fo:table-header>
                                            <fo:table-body>
                                                <fo:table-row width="100%">
                                                    <fo:table-cell>
                                                        <fo:block>Lugar de Expedición</fo:block>
                                                    </fo:table-cell>
                                                </fo:table-row>
                                                <fo:table-row width="100%">
                                                    <fo:table-cell>
                                                        <fo:block>C.P. <xsl:value-of select="@LugarExpedicion"/></fo:block>
                                                    </fo:table-cell>
                                                </fo:table-row>
                                                <fo:table-row width="100%">
                                                    <fo:table-cell>
                                                        <fo:block>Fecha de Expedición</fo:block>
                                                    </fo:table-cell>
                                                </fo:table-row>
                                                <fo:table-row width="100%">
                                                    <fo:table-cell>
                                                        <fo:block>
                                                            <xsl:call-template name="formatdate">
                                                                <xsl:with-param name="DateTimeStr" select="@Fecha" />
                                                            </xsl:call-template>
                                                        </fo:block>
                                                    </fo:table-cell>
                                                </fo:table-row>
                                                <fo:table-row width="100%">
                                                    <fo:table-cell>
                                                        <fo:block>Tipo de Comprobante</fo:block>
                                                    </fo:table-cell>
                                                </fo:table-row>
                                                <fo:table-row width="100%">
                                                    <fo:table-cell>
                                                        <xsl:call-template name="tipoComprobante">
                                                            <xsl:with-param name="tc" select="@TipoDeComprobante" />
                                                        </xsl:call-template>
                                                    </fo:table-cell>
                                                </fo:table-row>
                                            </fo:table-body>
                                        </fo:table>
                                    </fo:block>
                                </fo:table-cell>
                            </fo:table-body>
                        </fo:table>
                    </fo:block>
                </fo:static-content>
                <fo:static-content flow-name="xsl-region-after">
                    <fo:block font-size="8pt" font-weight="bold" >
                        <fo:table>
                            <fo:table-column column-width="3.3cm"/>
                            <fo:table-column column-width="6.2cm"/>
                            <fo:table-column column-width="3.3cm"/>
                            <fo:table-column column-width="6.2cm"/>
                            <fo:table-body>
                                <xsl:call-template name="formatObservaciones">
                                    <xsl:with-param name="observaciones" select="Addenda/Observaciones"/>
                                </xsl:call-template>
                                <fo:table-row>
                                    <fo:table-cell number-columns-spanned="4">
                                        <fo:block>
                                            <fo:table padding-top="10px">
                                                <fo:table-column column-width="12cm"/>
                                                <fo:table-column column-width="7cm"/>
                                                <fo:table-body>
                                                    <fo:table-row>
                                                        <fo:table-cell border-width="3px 0px 0px 0px"  border-style="solid" border-color="#6BA5D9">
                                                            <fo:block>
                                                                <fo:table>
                                                                    <fo:table-body>
                                                                        <fo:table-row>
                                                                            <fo:table-cell>
                                                                                <fo:block>Importe con Letra: <xsl:value-of select="$importe_letra"/></fo:block>
                                                                            </fo:table-cell>
                                                                        </fo:table-row>
                                                                    </fo:table-body>
                                                                </fo:table>
                                                            </fo:block>
                                                        </fo:table-cell>
                                                        <fo:table-cell border-width="3px 0px 0px 0px"  border-style="solid" border-color="#6BA5D9">
                                                            <fo:block>
                                                                <fo:table>
                                                                    <fo:table-column column-width="3.5cm"/>
                                                                    <fo:table-column column-width="3.5cm"/>
                                                                    <fo:table-body>
                                                                        <fo:table-row>
                                                                            <fo:table-cell>
                                                                                <fo:block>SubTotal:</fo:block>
                                                                            </fo:table-cell>
                                                                            <fo:table-cell text-align="right">
                                                                                <fo:block>
                                                                                    <xsl:value-of select="format-number(@SubTotal, '#,##0.00')"/>
                                                                                </fo:block>
                                                                            </fo:table-cell>
                                                                        </fo:table-row>
                                                                        <xsl:call-template name="formatDiscount">
                                                                            <xsl:with-param name="discount" select="@Descuento"/>
                                                                        </xsl:call-template>
                                                                        <xsl:for-each select="/Comprobante/Impuestos/Traslados/Traslado">
                                                                            <fo:table-row>
                                                                                <fo:table-cell>
                                                                                    <fo:block>
                                                                                        <xsl:call-template name="formatTax">
                                                                                            <xsl:with-param name="tax" select="@Impuesto"/>
                                                                                            <xsl:with-param name="rate" select="@TasaOCuota"/>
                                                                                            <xsl:with-param name="type" select="@TipoFactor"/>
                                                                                            <xsl:with-param name="add" select="'+'"/>
                                                                                        </xsl:call-template>
                                                                                    </fo:block>
                                                                                </fo:table-cell>
                                                                                <fo:table-cell text-align="right">
                                                                                    <fo:block>
                                                                                        <xsl:value-of select="format-number(@Importe, '#,##0.00')"/>
                                                                                    </fo:block>
                                                                                </fo:table-cell>
                                                                            </fo:table-row>
                                                                        </xsl:for-each>
                                                                        <xsl:for-each select="/Comprobante/Impuestos/Retenciones/Retencion">
                                                                            <fo:table-row>
                                                                                <fo:table-cell>
                                                                                    <fo:block>
                                                                                        <xsl:call-template name="formatTax">
                                                                                            <xsl:with-param name="tax" select="@Impuesto"/>
                                                                                            <xsl:with-param name="rate" select="@TasaOCuota"/>
                                                                                            <xsl:with-param name="type" select="@TipoFactor"/>
                                                                                            <xsl:with-param name="add" select="'-'"/>
                                                                                        </xsl:call-template>
                                                                                    </fo:block>
                                                                                </fo:table-cell>
                                                                                <fo:table-cell text-align="right">
                                                                                    <fo:block>
                                                                                        <xsl:value-of select="format-number(@Importe, '#,##0.00')"/>
                                                                                    </fo:block>
                                                                                </fo:table-cell>
                                                                            </fo:table-row>
                                                                        </xsl:for-each>
                                                                        <fo:table-row>
                                                                            <fo:table-cell>
                                                                                <fo:block>Total:</fo:block>
                                                                            </fo:table-cell>
                                                                            <fo:table-cell text-align="right">
                                                                                <fo:block>
                                                                                    <xsl:value-of select="format-number(@Total, '#,##0.00')"/>
                                                                                </fo:block>
                                                                            </fo:table-cell>
                                                                        </fo:table-row>
                                                                    </fo:table-body>
                                                                </fo:table>
                                                            </fo:block>
                                                        </fo:table-cell>
                                                    </fo:table-row>
                                                </fo:table-body>
                                            </fo:table>
                                        </fo:block>
                                    </fo:table-cell>
                                </fo:table-row>
                                <fo:table-row>
                                    <fo:table-cell border-color="#D2D1D2" border-width="0.5px" border-style="solid">
                                        <fo:block background-color="#D2D1D2">No. Certificado Digital</fo:block>
                                    </fo:table-cell>
                                    <fo:table-cell border-color="#D2D1D2" border-width="0.5px" border-style="solid">
                                        <fo:block text-align="center">
                                            <xsl:value-of select="/Comprobante/@NoCertificado"/>
                                        </fo:block>
                                    </fo:table-cell>
                                    <fo:table-cell border-color="#D2D1D2" border-width="0.5px" border-style="solid">
                                        <fo:block background-color="#D2D1D2">Certificado Digital SAT</fo:block>
                                    </fo:table-cell>
                                    <fo:table-cell border-color="#D2D1D2" border-width="0.5px" border-style="solid">
                                        <fo:block text-align="center">
                                            <xsl:value-of select="Complemento/TimbreFiscalDigital/@NoCertificadoSAT"/>
                                        </fo:block>
                                    </fo:table-cell>
                                </fo:table-row>
                                <fo:table-row>
                                    <fo:table-cell border-color="#D2D1D2" border-width="0.5px" border-style="solid">
                                        <fo:block background-color="#D2D1D2">Folio Fiscal</fo:block>
                                    </fo:table-cell>
                                    <fo:table-cell border-color="#D2D1D2" border-width="0.5px" border-style="solid">
                                        <fo:block text-align="center">
                                            <xsl:value-of select="Complemento/TimbreFiscalDigital/@UUID"/>
                                        </fo:block>
                                    </fo:table-cell>
                                    <fo:table-cell border-color="#D2D1D2" border-width="0.5px" border-style="solid">
                                        <fo:block background-color="#D2D1D2">Fecha de Certificación</fo:block>
                                    </fo:table-cell>
                                    <fo:table-cell border-color="#D2D1D2" border-width="0.5px" border-style="solid">
                                        <fo:block text-align="center">
                                            <xsl:call-template name="formatdate">
                                                <xsl:with-param name="DateTimeStr" select="Complemento/TimbreFiscalDigital/@FechaTimbrado"/>
                                            </xsl:call-template>
                                        </fo:block>
                                    </fo:table-cell>
                                </fo:table-row>
                                <xsl:call-template name="formatRelatedCFDI">
                                    <xsl:with-param name="relaciones" select="CfdiRelacionados"/>
                                </xsl:call-template>
                                <fo:table-row>
                                    <fo:table-cell>
                                        <fo:block padding-top="10px">
                                            <fo:table>
                                                <fo:table-column column-width="3cm"/>
                                                <fo:table-column column-width="16cm"/>
                                                <fo:table-body>
                                                    <fo:table-row>
                                                        <fo:table-cell>
                                                            <fo:block padding="2pt 2pt 2pt 2pt">
                                                                <fo:external-graphic
                                                                    width="2.75cm"
                                                                    content-height="2.75cm"
                                                                    content-width="scale-to-fit"
                                                                    src="{$qrc}"/>
                                                            </fo:block>
                                                        </fo:table-cell>
                                                        <fo:table-cell>
                                                            <fo:block>
                                                                <fo:table font-size="9pt">
                                                                    <fo:table-body>
                                                                        <fo:table-row>
                                                                            <fo:table-cell>
                                                                                <fo:block color="white" background-color="#6BA5D9" font-weight="bold">
                                                                                    Cadena original del complemento de certificación digital del SAT
                                                                                </fo:block>
                                                                            </fo:table-cell>
                                                                        </fo:table-row>
                                                                        <fo:table-row>
                                                                            <fo:table-cell>
                                                                                <fo:block font-size="6pt" line-height="8pt">
                                                                                    <xsl:value-of select="$cadena_original"/>
                                                                                </fo:block>
                                                                            </fo:table-cell>
                                                                        </fo:table-row>
                                                                        <fo:table-row>
                                                                            <fo:table-cell>
                                                                                <fo:block color="white" background-color="#6BA5D9" font-weight="bold">
                                                                                    Sello Digital del Emisor
                                                                                </fo:block>
                                                                            </fo:table-cell>
                                                                        </fo:table-row>
                                                                        <fo:table-row>
                                                                            <fo:table-cell>
                                                                                <fo:block font-size="6pt" line-height="8pt" overflow="auto">
                                                                                    <xsl:value-of select="Complemento/TimbreFiscalDigital/@SelloCFD"/>
                                                                                </fo:block>
                                                                            </fo:table-cell>
                                                                        </fo:table-row>
                                                                        <fo:table-row>
                                                                            <fo:table-cell>
                                                                                <fo:block color="white" background-color="#6BA5D9" font-weight="bold">
                                                                                    Sello Digital del SAT
                                                                                </fo:block>
                                                                            </fo:table-cell>
                                                                        </fo:table-row>
                                                                        <fo:table-row>
                                                                            <fo:table-cell>
                                                                                <fo:block font-size="6pt" line-height="8pt">
                                                                                    <xsl:value-of select="Complemento/TimbreFiscalDigital/@SelloSAT"/>
                                                                                </fo:block>
                                                                            </fo:table-cell>
                                                                        </fo:table-row>
                                                                    </fo:table-body>
                                                                </fo:table>
                                                            </fo:block>
                                                        </fo:table-cell>
                                                    </fo:table-row>
                                                </fo:table-body>
                                            </fo:table>
                                        </fo:block>
                                    </fo:table-cell>
                                </fo:table-row>
                                <fo:table-row>
                                    <fo:table-cell padding-top="5px" number-columns-spanned="4" font-size="8pt">
                                        <fo:block>
                                            * Este documento es una representación impresa de un Comprobante Fiscal Digital a través de Internet
                                        </fo:block>
                                    </fo:table-cell>
                                </fo:table-row>
                                <fo:table-row>
                                    <fo:table-cell  padding-top="15px" number-columns-spanned="4">
                                        <fo:block color="#729B9C" font-size="6pt" font-weight="bold" text-align="center">
                                            Facturado por: DETI DESARROLLO Y TRANSFERENCIA DE INFORMATICA S.A. DE C.V. Texcoco Edo. de Méx. Tel. 01 595 9250401 http://detisa.com.mx
                                        </fo:block>
                                    </fo:table-cell>
                                </fo:table-row>
                                <fo:table-row>
                                    <fo:table-cell  padding-top="10px" number-columns-spanned="4">
                                        <fo:block color="#729B9C" font-size="6pt" text-align="right">
                                            <fo:block>Página <fo:page-number/> de <fo:page-number-citation ref-id="theEnd"/>    </fo:block>
                                        </fo:block>
                                    </fo:table-cell>
                                </fo:table-row>
                            </fo:table-body>
                        </fo:table>
                    </fo:block>
                </fo:static-content>
                <fo:flow flow-name="xsl-region-body">
                    <fo:block>
                        <fo:table table-layout="fixed" width="100%" border-collapse="separate">
                            <fo:table-column column-width="19cm"/>
                            <fo:table-body>
                                <fo:table-row>
                                    <fo:table-cell>
                                        <fo:block>
                                            <fo:table table-layout="fixed" width="100%" border-collapse="separate">
                                                <fo:table-column column-width="10cm"/>
                                                <fo:table-column column-width="6cm"/>
                                                <fo:table-body>
                                                    <fo:table-row>
                                                        <xsl:apply-templates select="Receptor"/>
                                                        <fo:table-cell>
                                                            <fo:block>
                                                                <fo:table>
                                                                    <fo:table-column column-width="2.5cm"/>
                                                                    <fo:table-column column-width="2.5cm"/>
                                                                    <fo:table-column column-width="2.5cm"/>
                                                                    <fo:table-column column-width="2.5cm"/>
                                                                    <fo:table-body>
                                                                        <fo:table-row>
                                                                            <fo:table-cell number-columns-spanned="4">
                                                                                <fo:block color="#729B9C" font-weight="bold">Datos Generales del Comprobante</fo:block>
                                                                            </fo:table-cell>
                                                                        </fo:table-row>
                                                                        <fo:table-row>
                                                                            <fo:table-cell>
                                                                                <fo:block font-weight="bold">Moneda</fo:block>
                                                                            </fo:table-cell>
                                                                            <fo:table-cell>
                                                                                <fo:block>
                                                                                    <xsl:value-of select="@Moneda"/>
                                                                                </fo:block>
                                                                            </fo:table-cell>
                                                                            <fo:table-cell>
                                                                                <fo:block font-weight="bold">Tipo de Cambio</fo:block>
                                                                            </fo:table-cell>
                                                                            <fo:table-cell>
                                                                                <fo:block>
                                                                                    <xsl:value-of select="@TipoCambio"/>
                                                                                </fo:block>
                                                                            </fo:table-cell>
                                                                        </fo:table-row>
                                                                        <fo:table-row>
                                                                            <fo:table-cell>
                                                                                <fo:block font-weight="bold">Forma de Pago</fo:block>
                                                                            </fo:table-cell>
                                                                            <fo:table-cell number-columns-spanned="3">
                                                                                <xsl:call-template name="formaPago">
                                                                                    <xsl:with-param name="fp" select="@FormaPago"/>
                                                                                </xsl:call-template>
                                                                            </fo:table-cell>
                                                                        </fo:table-row>
                                                                        <fo:table-row>
                                                                            <fo:table-cell>
                                                                                <fo:block font-weight="bold">Método de Pago</fo:block>
                                                                            </fo:table-cell>
                                                                            <fo:table-cell number-columns-spanned="3">
                                                                                <xsl:call-template name="metodoPago">
                                                                                    <xsl:with-param name="mp" select="@MetodoPago"/>
                                                                                </xsl:call-template>
                                                                            </fo:table-cell>
                                                                        </fo:table-row>
                                                                        <fo:table-row>
                                                                            <fo:table-cell>
                                                                                <fo:block font-weight="bold">Versión CFDI</fo:block>
                                                                            </fo:table-cell>
                                                                            <fo:table-cell number-columns-spanned="3">
                                                                                <fo:block>
                                                                                    <xsl:value-of select="@Version"/>
                                                                                </fo:block>
                                                                            </fo:table-cell>
                                                                        </fo:table-row>
                                                                    </fo:table-body>
                                                                </fo:table>
                                                            </fo:block>
                                                        </fo:table-cell>
                                                    </fo:table-row>
                                                </fo:table-body>
                                            </fo:table>
                                        </fo:block>
                                    </fo:table-cell>
                                </fo:table-row>
                                <fo:table-row>
                                    <fo:table-cell>
                                        <fo:block>
                                            <fo:table 
                                                table-layout="fixed" 
                                                width="100%" 
                                                border-collapse="separate"  
                                                padding-top="10pt">
                                                <fo:table-column column-width="2cm"/>
                                                <fo:table-column column-width="2cm"/>
                                                <fo:table-column column-width="2cm"/>
                                                <fo:table-column column-width="1.5cm"/>
                                                <fo:table-column column-width="7cm"/>
                                                <fo:table-column column-width="2cm"/>
                                                <fo:table-column column-width="2.5cm"/>
                                                <fo:table-header>
                                                    <fo:table-row text-align="center" color="white" background-color="#6BA5D9" font-weight="bold" font-size="10pt">
                                                        <fo:table-cell>
                                                            <fo:block>Clave</fo:block>
                                                        </fo:table-cell>
                                                        <fo:table-cell>
                                                            <fo:block>ID.</fo:block>
                                                        </fo:table-cell>
                                                        <fo:table-cell>
                                                            <fo:block>Cantidad</fo:block>
                                                        </fo:table-cell>
                                                        <fo:table-cell>
                                                            <fo:block>Unidad</fo:block>
                                                        </fo:table-cell>
                                                        <fo:table-cell>
                                                            <fo:block>Descripción</fo:block>
                                                        </fo:table-cell>
                                                        <fo:table-cell>
                                                            <fo:block>Precio U.</fo:block>
                                                        </fo:table-cell>
                                                        <fo:table-cell>
                                                            <fo:block>Importe</fo:block>
                                                        </fo:table-cell>
                                                    </fo:table-row>
                                                </fo:table-header>
                                                <fo:table-body>
                                                    <xsl:for-each select="Conceptos/Concepto">
                                                        <fo:table-row>
                                                            <fo:table-cell text-align="center">
                                                                <fo:block>
                                                                    <xsl:value-of select="@ClaveProdServ"/>
                                                                </fo:block>
                                                            </fo:table-cell>
                                                            <fo:table-cell text-align="center">
                                                                <fo:block>
                                                                    <xsl:value-of select="@NoIdentificacion"/>
                                                                </fo:block>
                                                            </fo:table-cell>
                                                            <fo:table-cell text-align="center">
                                                                <fo:block>
                                                                    <xsl:value-of select="@Cantidad"/>
                                                                </fo:block>
                                                            </fo:table-cell>
                                                            <fo:table-cell text-align="center">
                                                                <fo:block>
                                                                    <xsl:value-of select="@ClaveUnidad"/>
                                                                </fo:block>
                                                            </fo:table-cell>
                                                            <fo:table-cell text-align="center">
                                                                <fo:block>
                                                                    <xsl:value-of select="@Descripcion"/>
                                                                </fo:block>
                                                            </fo:table-cell>
                                                            <fo:table-cell text-align="right">
                                                                <fo:block>
                                                                    <xsl:value-of select="format-number(@ValorUnitario, '#,##0.0000')"/>
                                                                </fo:block>
                                                            </fo:table-cell>
                                                            <fo:table-cell text-align="right">
                                                                <fo:block>
                                                                    <xsl:value-of select="format-number(@Importe, '#,##0.00')"/>
                                                                </fo:block>
                                                            </fo:table-cell>
                                                        </fo:table-row>
                                                    </xsl:for-each>
                                                </fo:table-body>
                                            </fo:table>
                                        </fo:block>
                                    </fo:table-cell>
                                </fo:table-row>
                            </fo:table-body>
                        </fo:table>
                        <fo:block id="theEnd"/>
                    </fo:block>
                </fo:flow>
            </fo:page-sequence>
        </fo:root>
    </xsl:template>

    <xsl:template match="Emisor">
        <fo:block text-align="center">
            <fo:table>
                <fo:table-body>
                    <fo:table-row>
                        <fo:table-cell>
                            <fo:block font-weight="bold" font-size="10pt">
                                <xsl:value-of select="@Nombre"/>
                            </fo:block>
                        </fo:table-cell>
                    </fo:table-row>
                    <fo:table-row>
                        <fo:table-cell font-weight="bold" font-size="10pt">
                            <fo:block>R.F.C. <xsl:value-of select="@Rfc"/></fo:block>
                        </fo:table-cell>
                    </fo:table-row>
                    <fo:table-row>
                        <fo:table-cell>
                            <xsl:call-template name="regimenFiscal">
                                <xsl:with-param name="rf" select="@RegimenFiscal" />
                            </xsl:call-template>
                        </fo:table-cell>
                    </fo:table-row>
                </fo:table-body>
            </fo:table>
        </fo:block>
    </xsl:template>

    <xsl:template match="Receptor">
        <fo:table-cell text-align="left">
            <fo:table>
                <fo:table-body>
                    <fo:table-row>
                        <fo:table-cell>
                            <fo:block color="#729B9C" font-weight="bold">Receptor del Comprobante Fiscal</fo:block>
                        </fo:table-cell>
                    </fo:table-row>
                    <fo:table-row>
                        <fo:table-cell>
                            <fo:block>
                                <xsl:value-of select="translate(@Nombre, $smallcase, $uppercase)"/>
                            </fo:block>
                        </fo:table-cell>
                    </fo:table-row>
                    <fo:table-row>
                        <fo:table-cell>
                            <fo:block font-weight="bold">R.F.C. <xsl:value-of select="@Rfc"/></fo:block>
                        </fo:table-cell>
                    </fo:table-row>
                    <fo:table-row>
                        <fo:table-cell>
                            <xsl:call-template name="usoCfdi">
                                <xsl:with-param name="desc">Uso del CFDI: </xsl:with-param>
                                <xsl:with-param name="uso" select="@UsoCFDI" />
                            </xsl:call-template>
                        </fo:table-cell>
                    </fo:table-row>
                </fo:table-body>
            </fo:table>
        </fo:table-cell>
    </xsl:template>

    <!-- Plantillas para catálogos. mover a un archivo separado -->
    <xsl:template name="usoCfdi">
        <xsl:param name="uso" />
        <xsl:param name="desc" />
        <fo:block>
            <fo:inline font-weight="bold">
                <xsl:value-of select="$desc"/> 
                <xsl:value-of select="$uso"/>
            </fo:inline> - 
            <xsl:choose>
                <xsl:when test="$uso='G01'">Adquisición de mercancias</xsl:when>
                <xsl:when test="$uso='G02'">Devoluciones, descuentos o bonificaciones</xsl:when>
                <xsl:when test="$uso='G03'">Gastos en general</xsl:when>
                <xsl:when test="$uso='I01'">Construcciones</xsl:when>
                <xsl:when test="$uso='I02'">Mobilario y equipo de oficina por inversiones</xsl:when>
                <xsl:when test="$uso='I03'">Equipo de transporte</xsl:when>
                <xsl:when test="$uso='I04'">Equipo de computo y accesorios</xsl:when>
                <xsl:when test="$uso='I05'">Dados, troqueles, moldes, matrices y herramental</xsl:when>
                <xsl:when test="$uso='I06'">Comunicaciones telefónicas</xsl:when>
                <xsl:when test="$uso='I07'">Comunicaciones satelitales</xsl:when>
                <xsl:when test="$uso='I08'">Otra maquinaria y equipo</xsl:when>
                <xsl:when test="$uso='D01'">Honorarios médicos, dentales y gastos hospitalarios.</xsl:when>
                <xsl:when test="$uso='D02'">Gastos médicos por incapacidad o discapacidad</xsl:when>
                <xsl:when test="$uso='D03'">Gastos funerales.</xsl:when>
                <xsl:when test="$uso='D04'">Donativos.</xsl:when>
                <xsl:when test="$uso='D05'">Intereses reales efectivamente pagados por créditos hipotecarios (casa habitación).</xsl:when>
                <xsl:when test="$uso='D06'">Aportaciones voluntarias al SAR.</xsl:when>
                <xsl:when test="$uso='D07'">Primas por seguros de gastos médicos.</xsl:when>
                <xsl:when test="$uso='D08'">Gastos de transportación escolar obligatoria.</xsl:when>
                <xsl:when test="$uso='D09'">Depósitos en cuentas para el ahorro, primas que tengan como base planes de pensiones.</xsl:when>
                <xsl:when test="$uso='D10'">Pagos por servicios educativos (colegiaturas)</xsl:when>
                <xsl:when test="$uso='P01'">Por definir</xsl:when>
                <xsl:otherwise>Desconocido</xsl:otherwise>
            </xsl:choose>
        </fo:block>
    </xsl:template>
  
    <xsl:template name="regimenFiscal">
        <xsl:param name="rf" />
        <fo:block>
            <fo:inline font-weight="bold">
                Régimen Fiscal: 
                <xsl:value-of select="$rf" />
            </fo:inline> - 
            <xsl:choose>
                <xsl:when test="$rf=601">General de Ley Personas Morales</xsl:when>
                <xsl:when test="$rf=603">Personas Morales con Fines no Lucrativos</xsl:when>
                <xsl:when test="$rf=606">Arrendamiento</xsl:when>
                <xsl:when test="$rf=607">Régimen de Enajenación o Adquisición de Bienes</xsl:when>
                <xsl:when test="$rf=608">Demás ingresos</xsl:when>
                <xsl:when test="$rf=609">Consolidación</xsl:when>
                <xsl:when test="$rf=610">Residentes en el Extranjero sin Establecimiento Permanente en México</xsl:when>
                <xsl:when test="$rf=611">Ingresos por Dividendos (socios y accionistas)</xsl:when>
                <xsl:when test="$rf=612">Personas Físicas con Actividades Empresariales y Profesionales</xsl:when>
                <xsl:when test="$rf=614">Ingresos por intereses</xsl:when>
                <xsl:when test="$rf=615">Régimen de los ingresos por obtención de premios</xsl:when>
                <xsl:when test="$rf=616">Sin obligaciones fiscales</xsl:when>
                <xsl:when test="$rf=620">Sociedades Cooperativas de Producción que optan por diferir sus ingresos</xsl:when>
                <xsl:when test="$rf=621">Incorporación Fiscal</xsl:when>
                <xsl:when test="$rf=622">Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras</xsl:when>
                <xsl:when test="$rf=623">Opcional para Grupos de Sociedades</xsl:when>
                <xsl:when test="$rf=624">Coordinados</xsl:when>
                <xsl:when test="$rf=628">Hidrocarburos</xsl:when>
                <xsl:when test="$rf=629">De los Regímenes Fiscales Preferentes y de las Empresas Multinacionales</xsl:when>
                <xsl:when test="$rf=630">Enajenación de acciones en bolsa de valores</xsl:when>
                <xsl:otherwise>Desconocido</xsl:otherwise>
            </xsl:choose>
        </fo:block>
    </xsl:template>

    <xsl:template name="formaPago">
        <xsl:param name="fp" />
        <fo:block>
            <fo:inline font-weight="bold">
                <xsl:value-of select="$fp" />
            </fo:inline> - 
            <xsl:choose>
                <xsl:when test="$fp=01">Efectivo</xsl:when>
                <xsl:when test="$fp=02">Cheque nominativo</xsl:when>
                <xsl:when test="$fp=03">Transferencia electrónica de fondos</xsl:when>
                <xsl:when test="$fp=04">Tarjeta de crédito</xsl:when>
                <xsl:when test="$fp=05">Monedero electrónico</xsl:when>
                <xsl:when test="$fp=06">Dinero electrónico</xsl:when>
                <xsl:when test="$fp=08">Vales de despensa</xsl:when>
                <xsl:when test="$fp=12">Dación en pago</xsl:when>
                <xsl:when test="$fp=13">Pago por subrogación</xsl:when>
                <xsl:when test="$fp=14">Pago por consignación</xsl:when>
                <xsl:when test="$fp=15">Condonación</xsl:when>
                <xsl:when test="$fp=17">Compensación</xsl:when>
                <xsl:when test="$fp=23">Novación</xsl:when>
                <xsl:when test="$fp=24">Confusión</xsl:when>
                <xsl:when test="$fp=25">Remisión de deuda</xsl:when>
                <xsl:when test="$fp=26">Prescripción o caducidad</xsl:when>
                <xsl:when test="$fp=27">A satisfacción del acreedor</xsl:when>
                <xsl:when test="$fp=28">Tarjeta de débito</xsl:when>
                <xsl:when test="$fp=29">Tarjeta de servicios</xsl:when>
                <xsl:when test="$fp=99">Por definir</xsl:when>
                <xsl:otherwise>Desconocido</xsl:otherwise>
            </xsl:choose>
        </fo:block>
    </xsl:template>

    <xsl:template name="metodoPago">
        <xsl:param name="mp" />
        <fo:block>
            <fo:inline font-weight="bold">
                <xsl:value-of select="$mp"/>
            </fo:inline> - 
            <xsl:choose>
                <xsl:when test="$mp='PUE'">Pago en una sola exhibición</xsl:when>
                <xsl:when test="$mp='PIP'">Pago inicial y parcialidades</xsl:when>
                <xsl:when test="$mp='PPD'">Pago en parcialidades o diferido</xsl:when>
                <xsl:otherwise>Desconocido</xsl:otherwise>
            </xsl:choose>
        </fo:block>
    </xsl:template>

    <xsl:template name="folioComprobante">
        <xsl:param name="tipo" />
        <xsl:param name="serie" />
        <xsl:param name="folio" />
        <fo:block>
            <xsl:value-of select="$tipo"/><xsl:value-of select="' : '"/>
             <xsl:choose>
                <xsl:when test="$serie">
                    <xsl:value-of select="$serie"/><xsl:value-of select="'-'"/>
                </xsl:when>
            </xsl:choose>
            <xsl:value-of select="$folio"/>
        </fo:block>
    </xsl:template>        

    <xsl:template name="tipoComprobante">
        <xsl:param name="tc" />
        <fo:block>
            <fo:inline font-weight="bold">
                <xsl:value-of select="$tc"/>
            </fo:inline> - 
            <xsl:choose>
                <xsl:when test="$tc='I'">Ingresos</xsl:when>
                <xsl:when test="$tc='E'">Egresos</xsl:when>
                <xsl:when test="$tc='T'">Traslado</xsl:when>
                <xsl:when test="$tc='N'">Nómina</xsl:when>
                <xsl:when test="$tc='P'">Pago</xsl:when>
                <xsl:otherwise>Desconocido</xsl:otherwise>
            </xsl:choose>
        </fo:block>
    </xsl:template>
  
    <!-- Plantillas para formateo de datos. mover a un archivo separado -->
    <xsl:template name="formatdate">
        <xsl:param name="DateTimeStr" />
        <xsl:variable name="datestr">
            <xsl:value-of select="substring-before($DateTimeStr,'T')" />
        </xsl:variable>
        <xsl:variable name="timestr">
            <xsl:value-of select="substring-after($DateTimeStr,'T')" />
        </xsl:variable>
        <xsl:variable name="mm">
            <xsl:value-of select="substring($datestr,6,2)" />
        </xsl:variable>
        <xsl:variable name="dd">
            <xsl:value-of select="substring($datestr,9,2)" />
        </xsl:variable>
        <xsl:variable name="yyyy">
            <xsl:value-of select="substring($datestr,1,4)" />
        </xsl:variable>
        <xsl:value-of select="concat($yyyy,'/', $mm, '/', $dd, ' ', $timestr)" />
    </xsl:template>

    <xsl:template name="formatRelatedCFDI">
        <xsl:param name="relaciones"/>
        <xsl:choose>
            <xsl:when test="$relaciones">
                <xsl:variable name="tipo" select="$relaciones/@TipoRelacion" />
                <xsl:for-each select="$relaciones/CfdiRelacionado">
                    <fo:table-row>
                        <fo:table-cell border-color="#D2D1D2" border-width="0.5px" border-style="solid">
                            <fo:block background-color="#D2D1D2">CFDI Relacionado</fo:block>
                        </fo:table-cell>
                        <fo:table-cell border-color="#D2D1D2" border-width="0.5px" border-style="solid">
                            <fo:block text-align="center">
                                <xsl:value-of select="@UUID"/>
                            </fo:block>
                        </fo:table-cell>
                        <fo:table-cell border-color="#D2D1D2" border-width="0.5px" border-style="solid">
                            <fo:block background-color="#D2D1D2">Tipo de Relacion</fo:block>
                        </fo:table-cell>
                        <fo:table-cell border-color="#D2D1D2" border-width="0.5px" border-style="solid">
                            <fo:block text-align="center">
                                <xsl:call-template name="formatRelationship">
                                    <xsl:with-param name="tipo" select="$tipo"/>
                                </xsl:call-template>
                            </fo:block>
                        </fo:table-cell>
                    </fo:table-row>
                </xsl:for-each>
            </xsl:when>
        </xsl:choose>
    </xsl:template>

    <xsl:template name="formatRelationship">
        <xsl:param name="tipo" />
        <fo:block>
            <fo:inline font-weight="bold">
                <xsl:value-of select="$tipo"/>
            </fo:inline> - 
            <xsl:choose>
                <xsl:when test="$tipo='01'">Nota de crédito de los documentos relacionados</xsl:when>
                <xsl:when test="$tipo='02'">Nota de débito de los documentos relacionados</xsl:when>
                <xsl:when test="$tipo='03'">Devolución de mercancía sobre facturas o traslados previos</xsl:when>
                <xsl:when test="$tipo='04'">Sustitución de los CFDI previos</xsl:when>
                <xsl:when test="$tipo='05'">Traslados de mercancias facturados previamente</xsl:when>
                <xsl:when test="$tipo='06'">Factura generada por los traslados previos</xsl:when>
                <xsl:otherwise>Desconocido</xsl:otherwise>
            </xsl:choose>
        </fo:block>
    </xsl:template>
  
    <xsl:template name="formatObservaciones">
        <xsl:param name="observaciones"/>
        <xsl:choose>
            <xsl:when test="$observaciones">
                <xsl:for-each select="$observaciones/Observacion">
                    <fo:table-row>
                        <fo:table-cell number-columns-spanned="4">
                            <fo:block>
                                <fo:inline font-size="6pt" font-style="italic" color="gray">
                                    <xsl:value-of select="@Descripcion"/>
                                </fo:inline>
                            </fo:block>
                        </fo:table-cell>
                    </fo:table-row>
                </xsl:for-each>
            </xsl:when>
        </xsl:choose>
    </xsl:template>

    <xsl:template name="formatDiscount">
        <xsl:param name="discount"/>
        <xsl:choose>
            <xsl:when test="$discount">
                <fo:table-row>
                    <fo:table-cell>
                        <fo:block>
                            <fo:inline font-family="Courier">(-)</fo:inline> Descuento </fo:block>
                    </fo:table-cell>
                    <fo:table-cell text-align="right">
                        <fo:block>
                            <xsl:value-of select="format-number($discount, '#,##0.00')"/>
                        </fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </xsl:when>
        </xsl:choose>
    </xsl:template>

    <xsl:template name="formatTax">
        <xsl:param name="tax"/>
        <xsl:param name="rate"/>
        <xsl:param name="type"/>
        <xsl:param name="add"/>
        <xsl:choose>
            <xsl:when test="$tax='001'">
                <fo:inline font-family="Courier">(<xsl:value-of select="$add"/>)</fo:inline> ISR </xsl:when>
            <xsl:when test="$tax='002'">
                <fo:inline font-family="Courier">(<xsl:value-of select="$add"/>)</fo:inline> IVA </xsl:when>
            <xsl:when test="$tax='003'">
                <fo:inline font-family="Courier">(<xsl:value-of select="$add"/>)</fo:inline> IEPS </xsl:when>
        </xsl:choose>
        <xsl:choose>
            <xsl:when test="$type='Tasa'">
                <xsl:call-template name="formatRate">
                    <xsl:with-param name="rate" select="$rate"/>
                </xsl:call-template>
            </xsl:when>
            <xsl:when test="$type='Cuota'">
                <xsl:value-of select="$rate"/>
            </xsl:when>
        </xsl:choose>    
    </xsl:template>

    <xsl:template name="formatRate">
        <xsl:param name="rate"/>
        <xsl:value-of select="$rate*100"/> %
    </xsl:template>
</xsl:stylesheet>
