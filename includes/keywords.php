<?

require 'rake-php-plus-master/src/AbstractStopwordProvider.php';
require 'rake-php-plus-master/src/StopwordArray.php';
require 'rake-php-plus-master/src/StopwordsPatternFile.php';
require 'rake-php-plus-master/src/StopwordsPHP.php';
require 'rake-php-plus-master/src/RakePlus.php';
require 'rake-php-plus-master/src/ILangParseOptions.php';
require 'rake-php-plus-master/src/LangParseOptions.php';

use DonatelloZa\RakePlus\RakePlus;
/*
$title = "Big Pharma is rigging everything to make sure approved coronavirus “treatments” don’t actually work at all, while things that do work are discredited or criminalized – NaturalNews.com";

$content = "The real game plan that’s emerging now in the globalist war against humanity is to criminalize or attack all the things that might work to stop the coronavirus while making sure “gold standard” treatments approved by the FDA are medically worthless.

In other words, if something is effective against the coronavirus, it will be discredited and destroyed.

If something is not effective against the coronavirus, it will be celebrated, approved and adopted as the “gold standard” for treatment.

Want examples? Take a look at the coordinated media attack on hydroxychloroquine, an anti-malaria drug that can’t generate billions of dollars for Big Pharma because it’s an off-patent generic drug that costs just pennies per dose. Before the coronavirus pandemic, this drug was widely touted as extremely safe by the WHO and FDA, and no one in medicine or the media had linked it to any increase in heart-related side effects."
;

$title="Communist China has been militarizing biotechnology for years, so their engineering of the coronavirus is no surprise – NaturalNews.com";
print_r(getKeywords($title));

$title = "North and South Korea exchange gunfire across border at guard post | CBC News";

print_r(getKeywords($title));

$title = "Biden Campaign Fundraising Email Reminds Donors Sexual Assault Allegations Don’t Bury Themselves";
print_r(getKeywords($title));
*/

function getKeyWords($input) {

    error_log("Getting keywords for input=$input");
    
    // Remove the token that is the host name and other unwanted symbols
    $token = strtok($input, " ");
    $onlyTitle = "";
    while ($token !== false) {
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
        if (strlen($keywords) > 0) {
            $keywords .= "|";
        }
        $keywords .= $key; 
    }

    error_log("keyword list=$keywords");
    return $keywords;
}