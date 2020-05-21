<?

//getReliableWebsites();

function getReliableWebsites() {

    
    $doc = new DOMDocument();
    $link = "https://en.wikipedia.org/wiki/Wikipedia:Reliable_sources/Perennial_sources";
    $doc->loadHTMLFile($link);
    $xpath = new DOMXpath($doc);

    $trDOMs = $xpath->query('//table[contains(@class, "perennial-sources")]/tbody/tr');



    $websites = array();

    $keywords;
    $i = 0;
    $skipped = 0;
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
        $name = trim(preg_replace('/\s+/', ' ', $nameNode[0]->nodeValue));
        
        $url = urldecode($linkNode[0]->nodeValue);
        $url = str_replace('"', '',($url));
        $hostParts = explode(":", $url);
        $host = $hostParts[count($hostParts) - 1];

        if (strlen($url) == 0) {
            $skipped++;
            continue;
        }

        $websites[$host] = array(
            "name" => $name,
            "rating" => $rating,
            "description" => $descriptionNode[0]->nodeValue
        );   

        $i++;
    }

    error_log("Found $i websites");
    error_log("Skipped $skipped websites");
    $json = json_encode($websites, JSON_PRETTY_PRINT);

    $path = "reliable_sources_list.json";
    if (file_exists($path)) {
        error_log("File exists, not overwriting");
        return;
    }

    $cached = fopen($path, "w") or error_log("Unable to open file at $path");
    fwrite($cached, $json);
    fclose($cached);

}