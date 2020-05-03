<?




getSatiricalWebsites();

function getSatiricalWebsites() {

    $doc = new DOMDocument();
    $satiricalWebsitesWiki = "https://en.wikipedia.org/wiki/List_of_satirical_news_websites";
    $doc->loadHTMLFile($satiricalWebsitesWiki);
    $xpath = new DOMXpath($doc);
var_dump($xpath->document->childNodes);

    //$xpath->registerNamespace("x", "http://www.w3.org/1999/xhtml");
    $tdDOMs = $xpath->query('//table//tr/td[1]//a/@href');

    $websites = array();

    $keywords;
    $i = 0;
    foreach($tdDOMs as $node) {
        //echo "{$node->tagName} -> {$node->nodeValue} ";

        // go to the wiki page for the site itself
        $docSiteWiki = new DOMDocument();
        @$docSiteWiki->loadHTMLFile("https://en.wikipedia.org" . $node->nodeValue);
        $docSiteXpath = new DOMXpath($docSiteWiki);
        

        $urlNodes = $docSiteXpath->query("//table[contains(@class, 'infobox')]//*[(text()='Website') or (text()='URL')]/following::td/a");   

        $url = $i;
        if ($urlNodes->length > 0) {
            $url = $urlNodes[0]->nodeValue;
        } else {
            $urlNodes = $docSiteXpath->query("//a[text()='Official website']/@href");
            if ($urlNodes->length > 0) {
                $url = $urlNodes[0]->nodeValue;
            }
        }
        
        $nameNodes = $docSiteXpath->query("//h1[@id='firstHeading']");

        //echo "# " . $nameNodes->length;
        $name = $nameNodes[0]->nodeValue;
         echo $url . " " . $name . PHP_EOL;
        // add to the list
        $websites[$url] = $name;

        $i++;
    }


    var_dump(json_encode($websites, JSON_PRETTY_PRINT));


}