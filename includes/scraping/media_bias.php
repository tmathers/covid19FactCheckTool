<?

include_once($_SERVER["DOCUMENT_ROOT"] . "/includes/model/util.php");


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
