<?php

# parse CSV file
$changes = array_map('str_getcsv', file($argv[1]));

# prepare cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FAILONERROR, true);
curl_setopt($ch, CURLOPT_COOKIE, 'test=seo');

foreach($changes as $changes){
    $url = $changes[0];
    $titleExpected = $changes[1];
    $descriptionExpected = $changes[2];
    
    # execute cURL
    curl_setopt($ch, CURLOPT_URL, $url);
    $data = curl_exec($ch);
    
    # check URL
    if (curl_errno($ch)) {
        echo sprintf("Wrong URL: %s!\n%s\n\n", $url, curl_error($ch));
    }
    else {
        # parse response
        $doc = new DOMDocument();
        @$doc->loadHTML($data);
        $nodes = $doc->getElementsByTagName('title');
        $title = $nodes->item(0)->nodeValue;
        $metas = $doc->getElementsByTagName('meta');
        
        # check Title
        if($title != $titleExpected)
            echo sprintf("Wrong Title on page %s!\nExpected: '%s', but actual: '%s'.\n\n",
                $url, $titleExpected, $title);
        
        # check Meta Description
        for ($i = 0; $i < $metas->length; $i++){
            $meta = $metas->item($i);
            if($meta->getAttribute('name') == 'description'){
                $description = $meta->getAttribute('content');
                if($description != $descriptionExpected)
                    echo sprintf("Wrong Meta Description on page %s!\nExpected: '%s', but actual: '%s'.\n\n",
                        $url, $descriptionExpected, $description);
                break;
                }
            }
    }
}

# close cURL
curl_close($ch);