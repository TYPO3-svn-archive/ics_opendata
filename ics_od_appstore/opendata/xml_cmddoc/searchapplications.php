<?php
/*
 * $Id$
 */
$doc = new DOMDocument();
$xsl = new XSLTProcessor();
$doc->load('documentationapi.xsl');
$xsl->importStyleSheet($doc);
$doc->load('searchapplications.xml');
echo $xsl->transformToXML($doc);
