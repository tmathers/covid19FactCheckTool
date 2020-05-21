 <?

include_once($_SERVER["DOCUMENT_ROOT"] . "/includes/model/util.php");

// Google custom search API key
$API_KEY = "AIzaSyCw7pWHSXJuzTgC7e0USGjv_7bslYNdRig";

// Search keys to use
$FACTCHECK_SEARCH_ID = "009803564125926803573:v4fcyqskqnl";
$SNOPES_SEARCH_ID = "009803564125926803573:p0ctntonaga";

// Use cached results?
$useCached = true;

// Time to expire cache in hours
$CACHE_EXPIRY = 24;

/**
 * Get the fact check results for the given custom search.
 * See https://developers.google.com/custom-search/v1/using_rest
 *
 * @param $kewords     The keywords to search for
 * @param $searchId    The ID of the custom search
 * @return             Array of results
 */ 
function getFactCheckResults($keywords, $searchId) {

    global $API_KEY, $useCached, $CACHE_EXPIRY;

    error_log("Getting fact-check results for: $keywords");
     
    // Get the results
    $json;
    $cachedPath = $_SERVER["DOCUMENT_ROOT"] . "/includes/data/cached/$searchId-$keywords.json";

    if ($useCached) {
        // Do we have a cached version?

        if (file_exists($cachedPath)) {

            // Is it expired?
            if (time() - filemtime($cachedPath) > $CACHE_EXPIRY * 3600) {
                error_log("Cached result is expired");
            } else {
                error_log("Using cached results");
                $json = file_get_contents($cachedPath);
            }
        }
    }

    if (! isset($json)) {    

        $query = "https://www.googleapis.com/customsearch/v1?key=$API_KEY&cx=$searchId&q=$keywords";
        error_log("Running Query: $query");
        $json = file_get_contents($query);

        // cache the json
        $cached = fopen($cachedPath, "w") or error_log("Unable to open file at $cachedPath");
        fwrite($cached, $json);
        fclose($cached);
    }

    $items = isset(json_decode($json)->items) ? json_decode($json)->items : array();

    return $items;
 }

 /**
 * Get the results from google Scholar for the given keywords as a JSON array
 */
function getScholarResults($encodedTerms, $useCached = true) {

    global $useCached, $CACHE_EXPIRY;

    error_log("Getting scholar results for: $encodedTerms");

    // Do we have a cached version?
    if ($useCached) {
        $cachedPath = $_SERVER["DOCUMENT_ROOT"] . "/includes/data/cached/scholar/$encodedTerms.json";
        if (file_exists($cachedPath)) {

            // Is it expired?
            if (time() - filemtime($cachedPath) > $CACHE_EXPIRY * 3600) {
                error_log("Cached result is expired");
            } else {
                error_log("Using cached results");
                return file_get_contents($cachedPath);
            }
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
