<?


$biasEmojis = array(
    "Right" => "⏩",
    "Left" => "⏪",
    "Left-Center" => "⬅️",
    "Center" => "↔️",
    "Right-Center" => "➡️",
    "Pro-Science" => "🔬",
    "Conspiracy/Pseudoscience" => "💩",
    "Questionable Source" => "🚫",
    "Satire" => "🤡"
);

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

        $title = $titleNode[0] != null ? DOMinnerHTML($titleNode[0]) : "";
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

//var_dump(searchMediaBias("foxnews.com"));
//var_dump(searchMediaBias("asdfasdf"));

/**
 * Search media bias
 */
function searchMediaBias($domain) {

    $scholarQuery = "https://mediabiasfactcheck.com/?s=$domain";

    $doc = new DOMDocument();
    @$doc->loadHTMLFile($scholarQuery);
    $xpath = new DOMXpath($doc);

    // get search results div
    $biasNode = $xpath->query("//header[@class='loop-data']");
    $descNode = $xpath->query("//div[@class='mh-excerpt']");

    $bias = isset($biasNode[0]) ? trim($biasNode[0]->nodeValue) : "";
    $desc = isset($descNode[0]) ? $descNode[0]->nodeValue : "";

    return array('bias' => $bias, 'desc' => $desc);

}


//getMediaBiasData();
/**
 * Search media bias
 */
function getMediaBiasData() {

    // url path variable => bias name
    $biases = array(
        "right" => "Right", 
        "left" => "Left",
        "leftcenter" => "Left-Center",
        "center" => "Center",
        "right-center" => "Right-Center",
        "pro-science" => "Pro-Science",
        "conspiracy" => "Conspiracy/Pseudoscience",
        "fake-news" => "Questionable Source",
        "satire" => "Satire"
    );

    $data = array();

    foreach ($biases as $bias => $biasName) {
        $query = "https://mediabiasfactcheck.com/$bias";

        $doc = new DOMDocument();
        @$doc->loadHTMLFile($query);
        $xpath = new DOMXpath($doc);

        // table rows
        $tdNodes = $xpath->query("//table[@id='mbfc-table']//td");
        
        foreach ($tdNodes as $tdNode) {
            $urlNode = $xpath->query("descendant::a/@href", $tdNode);

            $url = $urlNode[0] != null ? DOMinnerHTML($urlNode[0]) : "";
            
            $tdVal = $tdNode->nodeValue;
            $parts = preg_split("/[()]/", $tdVal);
            $domain = getDomain($parts[count($parts) - 2]);

            //echo $tdVal . " DOMAIN " . $domain . PHP_EOL;

            if ($domain == ".") {
                continue;
            }

            $name = $parts[0];
            $domain = utf8_encode($domain);
            //echo "URL = " . $domain . " name = " . $parts[0] . PHP_EOL;
            if (isset($data[$domain])) {
                //echo "domain $domain is set: " . var_dump($data[$domain]['biases']) . PHP_EOL;

                array_push($data[$domain]['biases'], $biasName);
            } else {
                $data[$domain] = array(
                    'biases' => array($biasName),
                    'url' => $url,
                    'name' => utf8_encode($name)
                );
            }
        }
    }

    //$data = iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($data));
    $json = json_encode($data);

//echo print_r($data, true);
//echo "*** " . json_last_error()  ;

    $path = "./data/mediaBias.json";

    $file = fopen($path, "w") or error_log("Unable to open file at $path");
    fwrite($file, $json);
    fclose($file);

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

function getDomain($search) {
  if (!preg_match('#^http(s)?://#', $search)) {
      $search = 'http://' . $search;
  }
  $urlParts = parse_url($search);
  $host = $urlParts['host'];

  // remove subdomain
  $host_names = explode(".", $host);
  return $host_names[count($host_names)-2] . "." . $host_names[count($host_names)-1];
}