<?




getReliableWebsites();

function getReliableWebsites() {

    $doc = new DOMDocument();
    $link = "https://en.wikipedia.org/wiki/Wikipedia:Reliable_sources/Perennial_sources";
    $doc->loadHTMLFile($link);
    $xpath = new DOMXpath($doc);

    $trDOMs = $xpath->query('//table[contains(@class, "perennial-sources")]/tbody/tr');



    $websites = array();

    $keywords;
    $i = 0;
    foreach ($trDOMs as $row) {

        //var_dump($row->nodeValue);
        
        $nameNode = $xpath->query("descendant::td[1]", $row);
        $ratingNode = $xpath->query("descendant::td[2]//a/@title", $row);
        $descriptionNode = $xpath->query("descendant::td[5]", $row);
        $linkNode = $xpath->query("descendant::td[6]/a[1]/@href", $row);

/*
        error_log("Name : " . $nameNode[0]->nodeValue);
        error_log("Description : " . $descriptionNode[0]->nodeValue);
        error_log("Link : " . $linkNode[0]->nodeValue);
*/
        $ratingParts = explode("#", $ratingNode[0]->nodeValue);
        $rating = $ratingParts[count($ratingParts) - 1];
        $name = trim(preg_replace('/\s+/', '', $nameNode[0]->nodeValue));
        $url = urldecode($linkNode[0]->nodeValue);

        $websites[$url] = array(
            "name" => $name,
            "rating" => $rating,
            "description" => $descriptionNode[0]->nodeValue
        );   

        $i++;

        if ($i > 5) {
             var_dump(json_encode($websites, JSON_PRETTY_PRINT));
            return;
        }
    }


}