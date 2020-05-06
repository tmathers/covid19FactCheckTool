<?

require 'rake-php-plus-master/src/AbstractStopwordProvider.php';
require 'rake-php-plus-master/src/StopwordArray.php';
require 'rake-php-plus-master/src/StopwordsPatternFile.php';
require 'rake-php-plus-master/src/StopwordsPHP.php';
require 'rake-php-plus-master/src/RakePlus.php';
require 'rake-php-plus-master/src/ILangParseOptions.php';
require 'rake-php-plus-master/src/LangParseOptions.php';

include 'data/stopwords.php';

use DonatelloZa\RakePlus\RakePlus;

/*$title = "Big Pharma is rigging everything to make sure approved coronavirus “treatments” don’t actually work at all, while things that do work are discredited or criminalized – NaturalNews.com";

$content = "The real game plan that’s emerging now in the globalist war against humanity is to criminalize or attack all the things that might work to stop the coronavirus while making sure “gold standard” treatments approved by the FDA are medically worthless.

In other words, if something is effective against the coronavirus, it will be discredited and destroyed.

If something is not effective against the coronavirus, it will be celebrated, approved and adopted as the “gold standard” for treatment.

Want examples? Take a look at the coordinated media attack on hydroxychloroquine, an anti-malaria drug that can’t generate billions of dollars for Big Pharma because it’s an off-patent generic drug that costs just pennies per dose. Before the coronavirus pandemic, this drug was widely touted as extremely safe by the WHO and FDA, and no one in medicine or the media had linked it to any increase in heart-related side effects."
;
*/
/*
$title="Communist China has been militarizing biotechnology for years, so their engineering of the coronavirus is no surprise – NaturalNews.com";
print_r(getKeywords($title));

$title = "North and South Korea exchange gunfire across border at guard post | CBC News";

print_r(getKeywords($title));

$title = "Biden Campaign Fundraising Email Reminds Donors Sexual Assault Allegations Don’t Bury Themselves";
print_r(getKeywords($title));

$title = "Nancy Pelosi Assures Democratic Reps They Don’t Need To Try Being Productive During Stressful Pandemic";
$title = "U.K. COVID-19 death toll surpasses 32,000, making it deadliest coronavirus outbreak in Europe";
getKeyWords($title);

//var_dump($stopWords);
*/
function getKeyWords($input) {

require 'data/stopwords.php';

    error_log("Getting keywords for input=$input");
    
    // Remove the token that is the host name and other unwanted symbols
    $token = strtok($input, " ");
    $onlyTitle = "";
    while ($token !== false) {

        //$token = preg_replace('/[^a-zA-Z0-9 -]/', '', $token); // only take alphanumerical characters, but keep the spaces and dashes too…

       

        /*if (in_array($token, $stopWords)) {
            echo "$$$$$ stop word : $token";
            $token = strtok(" ");
            continue;
        }
*/
        //echo "TOKEN[$token]" . ($token == "-") .PHP_EOL;
        if (strpos($token, ".") == false && $token != "-" && $token != "–" && $token != "|"
            && strtolower($token) != "news") {
            $onlyTitle .= $token . " ";
        }
        $token = strtok(" ");
    }
    //echo " only title=" . $onlyTitle . PHP_EOL;

    $rake = RakePlus::create($onlyTitle);
    $phrases = $rake->sortByScore('desc')->scores();
    //var_dump($phrases);
    
    $keywords = "";
    foreach ($phrases as $key => $score) {

        //echo strlen($keywords) . PHP_EOL;
        $words = explode(" ", $key);
        foreach ($words as $word) {
            if (in_array($word, $stopWords)) {
                continue;
            }

            if (strlen($keywords) > 0) {
                $keywords .= "|";
            }
            $keywords .= $word; 
        }
        
    }

    error_log("keyword list=$keywords");
    return $keywords;
}
/*
    $text = "This is some text. This is some text. Vending Machines are great.";
    $text ="Communist China has been militarizing biotechnology for years, so their engineering of the coronavirus is no surprise – NaturalNews.com";
    $words = extractCommonWords($text);
    echo implode(',', array_keys($words));

    function extractCommonWords($string){
          $stopWords = array('i','a','about','an','and','are','as','at','be','by','com','de','en','for','from','how','in','is','it','la','of','on','or','that','the','this','to','was','what','when','where','who','will','with','und','the','www');
     
          $string = preg_replace('/\s\s+/i', '', $string); // replace whitespace
          $string = trim($string); // trim the string
          $string = preg_replace('/[^a-zA-Z0-9 -]/', '', $string); // only take alphanumerical characters, but keep the spaces and dashes too…
          $string = strtolower($string); // make it lowercase
     
          preg_match_all('/\b.*?\b/i', $string, $matchWords);
          $matchWords = $matchWords[0];
     
          foreach ( $matchWords as $key=>$item ) {
              if ( $item == '' || in_array(strtolower($item), $stopWords) || strlen($item) <= 3 ) {
                  unset($matchWords[$key]);
              }
          }   
          $wordCountArr = array();
          if ( is_array($matchWords) ) {
              foreach ( $matchWords as $key => $val ) {
                  $val = strtolower($val);
                  if ( isset($wordCountArr[$val]) ) {
                      $wordCountArr[$val]++;
                  } else {
                      $wordCountArr[$val] = 1;
                  }
              }
          }
          arsort($wordCountArr);
          $wordCountArr = array_slice($wordCountArr, 0, 10);
          return $wordCountArr;
    }
*/