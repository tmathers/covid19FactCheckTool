<? 


$search =  htmlspecialchars($_GET["search"]);

$keywordList = ["china", "coronavirus", "covid-19", "biotechnology", "bioweapon", "government", "biological weapons", "biotech", "bioweapons", "pandemic", "outbreak", "wuhan", "virus", "vaccine", "masks", "face masks", "ventilators", "n95 masks", "Trump", "disinfectants", "hand sanitzer", "antibodies", "tests", "anti-viral", "anti-viral drugs"];


// Some fake news stories
$website = "https://www.theonion.com/biden-campaign-fundraising-email-reminds-donors-sexual-1843181076";
$webstite = "https://www.naturalnews.com/2020-05-01-communist-china-militarizing-biotechnology-coronavirus-no-surpise.html";

//$siteContents = file_get_contents("$search);

$doc = new DOMDocument();
@$doc->loadHTMLFile($website);
$xpath = new DOMXpath($doc);

// get the metadata DOM elements
$keywordsDOM =  $xpath->query('/html/head/meta[@name="keywords"]/@content');
$descriptionDOM =  $xpath->query('/html/head/meta[@name="keywords"]/@description');

// Get the keyword list
$keywords;
foreach($keywordsDOM as $node) {
  $keywords .= "{$node->nodeValue} ";
}

//echo "\n keywords $keywords \n";


// get the description
$description;
foreach($descriptionDOM as $node) {
  $description .= "{$node->nodeValue} ";
}
$snopesSearchUrl = $snopesSearch . $node->nodeValue;



displayResults($search, true);

/**
 * display the search results from Google Custom Search.
 * https://developers.google.com/custom-search/v1/using_rest
 * 
 */
function displayResults($keywords, $test) {
  
  $googleApiKey = "AIzaSyCVmegM3Q5rBYN66EdZG-1dq9GTDXLc4KM";
  $searchId = "009803564125926803573:p0ctntonaga";
  $query = "https://www.googleapis.com/customsearch/v1?key=$googleApiKey&cx=$searchId&q=$keywords";
  //echo $query;
  
  $json;
  if ($test) {
     $json = file_get_contents("./includes/search_data.json");
  } else {
     $json = file_get_contents($query);
  }
 

 
  $items = json_decode($json)->items;

  $html;  // html out

  foreach ($items as $item) {
    $html .= "<h3><a href=\"{$item->link}\">{$item->title}</a></h3>";
    $html .= "<p>{$item->htmlSnippet}</p>";
  }

  echo $html;
}


