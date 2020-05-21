<?

include_once($_SERVER["DOCUMENT_ROOT"] . "/includes/model/util.php");

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

//var_dump(searchMediaBias("foxnews.com"));
//var_dump(searchMediaBias("asdfasdf"));

/**
 * NOT USED
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