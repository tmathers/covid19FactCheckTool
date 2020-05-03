<? 

include("data/fake_site_list.php");
include("keywords.php");

$search =  htmlspecialchars($_GET["search"]);
$isUrl = false;

// Is it a URL or search terms?
if (substr( $search, 0, 7 ) === "http://" || substr( $search, 0, 8 ) === "https://") {
  $isUrl = true;
}

$keywordList = ["china", "coronavirus", "covid-19", "biotechnology", "bioweapon", "government", "biological weapons", "biotech", "bioweapons", "pandemic", "outbreak", "wuhan", "virus", "vaccine", "masks", "face masks", "ventilators", "n95 masks", "Trump", "disinfectants", "hand sanitzer", "antibodies", "tests", "anti-viral", "anti-viral drugs"];


// Some fake news stories
$website = "https://www.theonion.com/biden-campaign-fundraising-email-reminds-donors-sexual-1843181076";
$webstite = "https://www.naturalnews.com/2020-05-01-communist-china-militarizing-biotechnology-coronavirus-no-surpise.html";

// Get the keywords...
$keywords;

// If it's a URL get the keywords from the page
if ($isUrl) {

  $domain = getDomain($search);

  $doc = new DOMDocument();
  @$doc->loadHTMLFile($search);
  $xpath = new DOMXpath($doc);

  // get the metadata DOM elements
  $keywordsNode =  $xpath->query('/html/head/meta[@name="keywords"]/@content');
  $descriptionNode =  $xpath->query('/html/head/meta[@name="keywords"]/@description');
  $titleNode = $xpath->query('/html/head/title');

  // Get the keyword list
  $keywords;
  foreach($keywordsNode as $node) {
    $keywords .= "{$node->nodeValue} ";
  }

  // get the description
  $description;
  foreach($descriptionNode as $node) {
    $description .= "{$node->nodeValue} ";
  }

  // Get the title
  $title = "Unknown";
  if ($titleNode->length > 0) {
    $title = $titleNode[0]->nodeValue;
  }

  error_log("Article Keywords: $keywords");
  error_log("Article Title: $title");
  error_log("Article description: $description");


  echo '
  <div class="card mb-4"><div class="card-body"><dl class="row mb-0 pb-0">
    <dt class="col-sm-2">Title:</dt>
    <dd class="col-sm-9"><b class="text-dark">'.$title.'</b></dd>
    <dt class="col-sm-2">URL:</dt>
    <dd class="col-sm-9"><a href="'.$search.'">'.$search.'</a></dd>
    <dt class="col-sm-2">Website:</dt>
    <dd class="col-sm-9"><a class="mb-0" href="http://'.$domain.'">'.$domain.'</a></dd>
  </dl></div></div>';


  // check the domain against known fake news sources

  $fakeSites = json_decode($fakeSitesJson);

  // Is it satirical ?
  if (isset($fakeSites->satirical->sites->$domain)) {
    echo '<div class="alert alert-danger" role="alert">';
    echo '<h5><span class="pr-1">&#9888;</span> Satirical Website</h5>';
    echo "The publisher of this article, <a class='alert-link' href='http://$domain'>$domain</a> is known to publish content that is satirical in nature. ";
    echo "<a class='alert-link' href='" . $fakeSites->satirical->source . "'>[Source]</a>";
    echo '</div>';
  // Is it fake?
  } else if (in_array($domain, $fakeSites->fake->sites)) {
    echo '<div class="alert alert-danger" role="alert">';
    echo '<h5><span class="pr-1">&#9888;</span>  Fake News Source</h5>';
    echo "The publisher of this article <a class='alert-link' href='$domain'>$domain</a> is known to publish false or misleading content. ";
    echo "<a class='alert-link' href='" . $fakeSites->satirical->source . "'>[Source]</a>";
    echo '</div>';
  }

  $keywords = $title;

// Otherwise just use the provided search terms
} else {
  $keywords = $search;

  echo '<div class="card mb-4"><div class="card-body"><dl class="row mb-0 pb-0">
    <dt class="col-sm-3">Question:</dt>
    <dd class="col-sm-9"><b class="text-secondary">'.$search.'</b></dd>
  </div></div></dl>';
}

echo '<h4 class="pb-2 pt-2">Fact Check Results</h4>';


// display the results
$keywordQuery = $isUrl ? getKeywords($keywords) : $keywords;
error_log("QUERY Params: $keywordQuery");
displayResults(urlencode($keywordQuery), false /* test */);

/**
 * display the search results from Google Custom Search.
 * https://developers.google.com/custom-search/v1/using_rest
 * 
 */
function displayResults($keywords, $test) {
  
  // Get the results
  $json;
  // Do we have a cached version?
  $cachedPath = "./includes/data/cached/$keywords.json";
  if (file_exists($cachedPath)) {
    error_log("Using cached results");
    $json = file_get_contents($cachedPath);
  } else {

    if ($test) {
      $json = file_get_contents("./includes/data/search_data.json");
    } else {
      $googleApiKey = "AIzaSyCVmegM3Q5rBYN66EdZG-1dq9GTDXLc4KM";
      $searchId = "009803564125926803573:p0ctntonaga";
      $query = "https://www.googleapis.com/customsearch/v1?key=$googleApiKey&cx=$searchId&q=$keywords";
      error_log("Running Query: $query");
      $json = file_get_contents($query);

      // cache the json
      $cached = fopen($cachedPath, "w") or error_log("Unable to open file at $cachedPath");
      fwrite($cached, $json);
      fclose($cached);
    }
  }

  $items = json_decode($json)->items;

  $html;  // html out

  if (count($items) > 0) {

    // Print the results list
    foreach ($items as $item) {
      $html .= "<h5 class='pb-0 mb-0'><a href=\"{$item->link}\">{$item->htmlTitle}</a></h5>";
      $html .= "<p class='text-secondary pt-0 mt-0 mb-0'>{$item->link}</p>";
      $html .= "<p>{$item->htmlSnippet}</p>";
    }
  } else {
    $html = "<p>Nothing found.</p>";
  }

  echo $html;
}

function getDomain($search) {
  if (!preg_match('#^http(s)?://#', $search)) {
      $search = 'http://' . $search;
  }
  $urlParts = parse_url($search);
  // remove www
  return preg_replace('/^www\./', '', $urlParts['host']);
}
