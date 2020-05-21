<?

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

/**
 * Get the inner HTML of the DOM Node
 */
function DOMinnerHTML(DOMNode $element) { 
    $innerHTML = ""; 

    //var_dump($element);
    $children  = $element->childNodes;

    foreach ($children as $child) { 
        $innerHTML .= $element->ownerDocument->saveHTML($child);
    }

    return $innerHTML; 
}