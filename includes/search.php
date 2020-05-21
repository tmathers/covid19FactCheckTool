<? 

include("data/fake_site_list.php");
include("data/tlds.php");
include("model/keywords.php");
include("model/FactCheckModel.php");
include("model/WebsiteModel.php");

$search =  filter_input(INPUT_POST | INPUT_GET, 'search', FILTER_SANITIZE_SPECIAL_CHARS);
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
  $mediaBiasList = json_decode(file_get_contents("./includes/data/mediaBias.json"));

  //var_dump($$siteInfoList);  

  echo "
  <h5 class='pb-4'>Fact checking article \"<a href='$search'>$title</a>\"...</h5>
  ";

  echo '
  <div class="card mb-4">
    <div class="card-header">

      <h5 class="card-title">Website Analysis</h5>
      <a class="mb-0" href="http://'.$domain.'">'.$domain.'</a>
    </div>
    <div class="card-body">';
    
    // check the domain against known fake news sources
    $fakeSites = json_decode($fakeSitesJson);
    // Is it satirical ?
    /*if (isset($fakeSites->satirical->sites->$domain)) {
      echo '<div class="alert alert-danger" role="alert">';
      echo '<h5><span class="pr-1">&#9888;</span> Satirical Website</h5>';
      echo "The publisher of this article is known to publish content that is satirical in nature. ";
      echo "[<a class='alert-link' href='" . $fakeSites->satirical->source . "'>Source</a>]";
      echo '</div>';
    // Is it fake?
    } else if (in_array($domain, $fakeSites->fake->sites)) {
      echo '<div class="alert alert-danger" role="alert">';
      echo '<h5><span class="pr-1">&#9888;</span>  Fake News Source</h5>';
      echo "The publisher of this article is known to publish false or misleading content. ";
      echo "[<a class='alert-link' href='" . $fakeSites->fake->source . "'>Source</a>]";
      echo '</div>';
    }*/

  echo '
    <dl class="row mb-0 pb-0">';

    if (isset($siteInfoList->$domain)) {
      $info = $siteInfoList->$domain;
      echo     '<dd class="col-sm-9">'.$info->name.'</dd>';
    }

    // Display site reliability info from wikipedia
    if (isset($tldInfo)) { 
      echo '
      <dd class="col-sm-9"><b>Domain info: </b>' . $tldInfo[0] . '. '. $tldInfo[1].'</dd>';
    }  

    // Display site reliability info from wikipedia
    if (isset($siteInfoList->$domain) || isset($fakeSites->satirical->sites->$domain) || isset($fakeSites->fake->sites->$domain)) { 
      $info = $siteInfoList->$domain;

      echo '
          <hr class="w-100">

          <dd class="col-sm-9"><h6>Reliability</h6></dd>';

        if (isset($fakeSites->satirical->sites->$domain)) {
            echo '<dd class="col-sm-9"><span class="pr-1">&#9888;</span>  Satircial [<a href="{$fakeSites->satirical->source} ">More</a>]</dd>';
            
          // Is it fake?
          } else if (in_array($domain, $fakeSites->fake->sites)) {
            echo '<dd class="col-sm-9"><span class="pr-1">🚫</span>  Fake News Source [<a href="{$fakeSites->fake->source} ">More</a>]</dd>';
          }

        if (isset($siteInfoList->$domain)) {
          echo '
          <dd class="col-sm-9"><span class="pr-1">'. $reliability[$info->rating]['emoji'] . '</span>  ' . $reliability[$info->rating]['text'].'</dd>
          <dd class="col-sm-9">'.$info->description.' [<a href="https://en.wikipedia.org/wiki/Wikipedia:Reliable_sources/Perennial_sources">More</a>]</dd>
          ';
      }
    } else {
      echo '
          <dd class="col-sm-9">'. $reliability['No consensus']['emoji'] . ' Unknown</dd>
          <dd class="col-sm-9">No additional info about this source.</dd>';
    }

    
    if (isset($mediaBiasList->$domain)) {
      $biasInfo = $mediaBiasList->$domain;

      echo '<hr class="w-100">
      
          <dd class="col-sm-9"><h6>Media Bias</h6></dd>';
      foreach ($biasInfo->biases as $bias) {
          
          echo '<dd class="col-sm-9"><span class="pr-1">' . $biasEmojis[$bias] . '</span>  ' . $bias .' [<a href="' . $biasInfo->url . '">More</a>]</dd>
          ';
      }
    }

    echo '
  </dl></div></div>';

  $keywords = $title;

// Otherwise just use the provided search terms
} else {
  $keywords = $search;

}

echo '<h4 class="pb-2 pt-2">Fact Check Results</h4>';


// display the results
$keywordQuery = $isUrl ? getKeywords($keywords) : $keywords;
error_log("QUERY Params: $keywordQuery");

$snopesResults = getFactCheckResults(urlencode($keywordQuery), $SNOPES_SEARCH_ID);
$factcheckResults = getFactCheckResults(urlencode($keywordQuery), $FACTCHECK_SEARCH_ID);

// Get Academic results
$json = getScholarResults(urlencode($keywordQuery));
$academicResults = json_decode($json)->items;
//var_dump(json_decode($json));

$snopesHtml = getResultsHtml($snopesResults);
$factcheckHtml = getResultsHtml($factcheckResults);
$academicHtml = getResultsHtml($academicResults);


echo '
<ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
  <li class="nav-item">
    <a class="nav-link active" id="snopes-tab" data-toggle="tab" role="tab" aria-controls="snopes" aria-selected="true" href="#snopes">Snopes.com</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="factcheck-tab" data-toggle="tab" role="tab" aria-controls="factcheck" aria-selected="false" href="#factcheck">Factcheck.org</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="profile-tab" data-toggle="tab" role="tab" aria-controls="profile" aria-selected="false" href="#profile">Academic</a>
  </li>
</ul>
<div class="tab-content" id="myTabContent">
  <div class="tab-pane fade show active" id="snopes" role="tabpanel" aria-labelledby="snopes-tab">
    ' . $snopesHtml . '
  </div>
  <div class="tab-pane fade show" id="factcheck" role="tabpanel" aria-labelledby="factcheck-tab">
    ' . $factcheckHtml . '
  </div>
  <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
    ' . $academicHtml . '
  </div>
</div>';


function getResultsHtml($items) {

  $newsHtml;  // html out
  
  // Assemble the News fact check results list
  foreach ($items as $item) {
    $newsHtml .= "<h5 class='pb-0 mb-0'><a href=\"{$item->link}\">{$item->htmlTitle}</a></h5>";
    $newsHtml .= "<p class='text-secondary pt-0 mt-0 mb-0'>{$item->link}</p>";
    $newsHtml .= "<p>{$item->htmlSnippet}</p>";
  }

  if (count($items) == 0) {
    $newsHtml = "<p>Nothing found.</p>";
  }
  return $newsHtml;
}