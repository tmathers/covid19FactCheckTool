<? 

include("data/fake_site_list.php");
include("data/reliable_sources.php");
include("data/tlds.php");
include("keywords.php");
include("searchScholar.php");

$search =  htmlspecialchars($_GET["search"]);
$isUrl = false;

// Is it a URL or search terms?
if (substr( $search, 0, 7 ) === "http://" || substr( $search, 0, 8 ) === "https://") {
  $isUrl = true;
}

// Get the keywords...
$keywords;

// If it's a URL get the keywords from the page
if ($isUrl) {

  $domain = getDomain($search);
  $domainParts = explode(".", $domain);
  $tld = count($domainParts) > 1 ? $domainParts[count($domainParts) - 1] : "";
  $tldInfo = $TLDs[$tld];

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
  if (strlen($description) == 0) {
    $descriptionNode2 = $xpath->query('/html/head/meta[@name="description"]/@content');
      foreach($descriptionNode2 as $node) {
        $description .= "{$node->nodeValue} ";
      }
  }

  // Get the title
  $title = "Unknown";
  if ($titleNode->length > 0) {
    $title = $titleNode[0]->nodeValue;
  }

  error_log("URL: $search");
  error_log("Article Keywords: $keywords");
  error_log("Article Title: $title");
  error_log("Article description: $description");
  error_log("Domain: $domain");

  $siteInfoList = json_decode(file_get_contents("./includes/data/reliable_sources_list.json"));
  //var_dump($$siteInfoList);  

  echo "
  <h4><a href='$search'>$title</a></h4>
  <!--<h6>$search</h6>-->";

  echo '
  <div class="card mb-4"><div class="card-body">
    <h4 class="card-title">Website Analysis</h4>';
    
    // check the domain against known fake news sources
    $fakeSites = json_decode($fakeSitesJson);
    // Is it satirical ?
    if (isset($fakeSites->satirical->sites->$domain)) {
      echo '<div class="alert alert-danger" role="alert">';
      echo '<h5><span class="pr-1">&#9888;</span> Satirical Website</h5>';
      echo "The publisher of this article, <a class='alert-link' href='http://$domain'>$domain</a> is known to publish content that is satirical in nature. ";
      echo "[<a class='alert-link' href='" . $fakeSites->satirical->source . "'>Source</a>]";
      echo '</div>';
    // Is it fake?
    } else if (in_array($domain, $fakeSites->fake->sites)) {
      echo '<div class="alert alert-danger" role="alert">';
      echo '<h5><span class="pr-1">&#9888;</span>  Fake News Source</h5>';
      echo "The publisher of this article <a class='alert-link' href='$domain'>$domain</a> is known to publish false or misleading content. ";
      echo "<a class='alert-link' href='" . $fakeSites->satirical->source . "'>[Source]</a>";
      echo '</div>';
    }

  echo '
    <dl class="row mb-0 pb-0">
      <dd class="col-sm-9"><a class="mb-0" href="http://'.$domain.'">'.$domain.'</a></dd>';
    // Display site reliability info from wikipedia
    if (isset($tldInfo)) { 
      echo '
      <dd class="col-sm-9"><b>.'.$tld. '</b> ('.$tldInfo[0]. ') - ' . $tldInfo[1].'</a></dd>';
    }  

    // Display site reliability info from wikipedia
    if (isset($siteInfoList->$domain)) { 
      $info = $siteInfoList->$domain;

      echo '
          <dd class="col-sm-9">'.$info->name.'</dd>

          <dd class="col-sm-9"><h6>Wikipedia</h6></dd>
          <dd class="col-sm-9">'. $reliability[$info->rating]['emoji'] . ' ' . $reliability[$info->rating]['text'].'</dd>
          <dd class="col-sm-9">'.$info->description.' [<a href="https://en.wikipedia.org/wiki/Wikipedia:Reliable_sources/Perennial_sources">Wikipedia</a>]</dd>
      ';
    } else {
      echo '
          <dd class="col-sm-9">'. $reliability['No consensus']['emoji'] . ' Unknown</dd>
          <dd class="col-sm-9">No additional info about this source.</dd>';
    }
    echo '
  </dl></div></div>';

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

  $newsItems = isset(json_decode($json)->items) ? json_decode($json)->items : array();

  $newsHtml;  // html out
  $academicHtml;
  
  // Assemble the News fact check results list
  foreach ($newsItems as $item) {
    $newsHtml .= "<h5 class='pb-0 mb-0'><a href=\"{$item->link}\">{$item->htmlTitle}</a></h5>";
    $newsHtml .= "<p class='text-secondary pt-0 mt-0 mb-0'>{$item->link}</p>";
    $newsHtml .= "<p>{$item->htmlSnippet}</p>";
  }

  if (count($newsItems) == 0) {
    $newsHtml = "<p>Nothing found.</p>";
  }


  // Get Academic results
  $json = getScholarResults($keywords);
  $academicItems = json_decode($json)->items;
  //var_dump(json_decode($json));

  // Assemble the Academic fact check results list
  foreach ($academicItems as $item) {
    $academicHtml .= "<h5 class='pb-0 mb-0'><a href=\"{$item->link}\">{$item->htmlTitle}</a></h5>";
    $academicHtml .= "<p class='text-secondary pt-0 mt-0 mb-0'>{$item->link}</p>";
    $academicHtml .= "<p>{$item->htmlSnippet}</p>";
  }

  if (count($academicHtml) == 0) {
    $academicHtml = "<p>Nothing found.</p>";
  }
  

  echo '
  <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" id="home-tab" data-toggle="tab" role="tab" aria-controls="home" aria-selected="true" href="#home">News</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" id="profile-tab" data-toggle="tab" role="tab" aria-controls="profile" aria-selected="false" href="#profile">Academic</a>
    </li>
  </ul>
  <div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
      ' . $newsHtml . '
    </div>
    <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
      ' . $academicHtml . '
    </div>
  </div>';
  
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
