<?


//getScholarResults("covid-19", false);

/**
 * Get the results from google Scholar for the given keywords as a JSON array
 */
function getScholarResults($encodedTerms, $useCached = true) {

    error_log("Getting scholar results for: $encodedTerms");

    // Do we have a cached version?
    if ($useCached) {
        $cachedPath = "./includes/data/cached/scholar/$encodedTerms.json";
        if (file_exists($cachedPath)) {
            error_log("Using cached results");
            return file_get_contents($cachedPath);
        }
    }

    $scholarQuery = "https://scholar.google.ca/scholar?hl=en&as_sdt=0%2C5&q=$encodedTerms&btnG=";

    $doc = new DOMDocument();
    @$doc->loadHTMLFile($scholarQuery);
    $xpath = new DOMXpath($doc);

    // get search results div
    $results = $xpath->query("//div[contains(@class, 'gs_scl')]");

    $json = array("items" => array());

    foreach ($results as $result) {

        $titleNode = $xpath->query("descendant::h3[contains(@class, 'gs_rt')]/a", $result);
        $urlNode = $xpath->query("descendant::h3[contains(@class, 'gs_rt')]/a/@href", $result);
        $descriptionNode = $xpath->query("descendant::div[contains(@class, 'gs_rs')]", $result);
        $hostnameNode = $xpath->query("descendant::div[contains(@class, 'gs_ggs')]//a", $result);
        $citedByNode = $xpath->query("descendant::div[@class='gs_fl']//a[3]", $result);

        //var_dump($descriptionNode->saveXML($result));

        $title = DOMinnerHTML($titleNode[0]);
        $url = $urlNode[0]->nodeValue;

        
        $description = $descriptionNode[0] != null ? DOMinnerHTML($descriptionNode[0]) : "";
        $hostname = $hostnameNode[0]->nodeValue;
        $citedBy = $citedByNode[0]->nodeValue;

        // TODO remove the [HTML] part from the  hostname
        /*
        error_log("Title: $title");
        error_log("URL: $url");
        error_log("Description: $description");
        error_log("Hostname: $hostname");
        error_log("Cited By: $citedBy");
*/
        $item = array("htmlTitle" => $title,
            'displayLink' => $hostname,
            "link" => $url,
            "htmlSnippet" => $description,
            "citedBy" => $citedBy);

        array_push($json['items'], $item);
    } 
    $json = json_encode($json);

    if ($useCached) {
        // cache the json
        $cached = fopen($cachedPath, "w") or error_log("Unable to open file at $cachedPath");
        fwrite($cached, $json);
        fclose($cached);
    }

    return $json;
}

/**
 * Get the inner HTML of the DOM Node
 */
function DOMinnerHTML(DOMNode $element) { 
    $innerHTML = ""; 

    //var_dump($element);
    $children  = $element->childNodes;

    foreach ($children as $child) 
    { 
        $innerHTML .= $element->ownerDocument->saveHTML($child);
    }

    return $innerHTML; 
}