// src/Template/Articles/json/index.ctp
// Do some formatting and manipulation on
// the $recipes array.

$xml = Xml::fromArray(['response' => $articles]);
echo $xml->asXML();