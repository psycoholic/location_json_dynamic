 <?php
    // Build Json file
    $data = '{
        "@context": "http:\/\/schema.org",
        "@type": "Organization",
        "name": "Boekhouder Offertes",
        "url": "https:\/\/boekhouder-offertes.com",
        "sameAs": ["https:\/\/www.facebook.com\/BoekhouderOffertes", "https:\/\/twitter.com\/BoekhouderNL", "https:\/\/www.linkedin.com\/company\/boekhouder-offertes", "https:\/\/plus.google.com\/114509194088621733280"],
        "logo": "https:\/\/boekhouder-offertes.com\/wp-content\/uploads\/2018\/03\/boekhouder-offertes.svg",
        "description": "Altijd de beste boekhouder offertes uit uw omgeving.",
        "address": {
            "@type": "PostalAddress",
            "addressCountry": "Netherlands"
        },
        "areaServed": "",
        "contactPoint": {
            "@type": "ContactPoint",
            "contactType": "Sales",
            "email": "info@boekhouder-offertes.com",
            "url": "https:\/\/boekhouder-offertes.com"
        }
    }';
    // Set values and create regex for variables
    $url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    $currentPage = $_SERVER['REQUEST_URI'];
    $currentServer = $_SERVER['SERVER_NAME'];
    $currentDirectory = getcwd();
    $currentHome = get_home_url();
    $social_data = [];
    // Get the current logo
    $custom_logo_id = get_theme_mod( 'custom_logo' );
    $image = wp_get_attachment_image_src( $custom_logo_id , 'full' );

    // Regex for stripping name out of url and removing mime type
    $homeReplaced = str_replace('https://', '', $currentHome);
    $withoutExtension = substr($homeReplaced, 0, strrpos($homeReplaced, ".")); // Remove everything after . 
    $homeTrimmed = preg_replace("/[\W\-]/", ' ', $withoutExtension); // Replace all non alpha numeric with space's 
    $homeFormatted = ucfirst($homeTrimmed);

    // Regex check page name for - perform check to replace string before delimeter.
    $string_pos = strpos($currentPage, '-');
    $preg_result = [];
    preg_match('/-(.*)/', $currentPage, $preg_result); // Location name stripped after - and put in preg_result
    
    // Add delimeters and strip slashes
    $pageString = strval($currentPage) . "/"; 
    $loc = str_replace('/', '', $currentPage);
    $loc_name = str_replace('.php', '', $preg_result[1]);
    $loc_Trimmed = preg_replace("/[\W\-]/", ' ', $loc_name);
    $locFormatted = ucfirst($loc_Trimmed);  

    // Get Jetpack data from database
    $results = $GLOBALS['wpdb']->get_results( "SELECT * FROM {$wpdb->prefix}options WHERE `option_name`='jetpack_options'"  );
    // get object property out of the array
    $get_data = $results[0]->option_value;
    // Deserialize the data from database
    $php_data = unserialize($get_data);

    // Loop thrue the array, and get the url data 
    foreach($php_data['publicize_connections']['facebook'] as $value){
        $temp = $value['connection_data']['meta']['link'];
        array_push($social_data, $temp);
    }
    foreach($php_data['publicize_connections']['twitter'] as $value){
        $temp = $value['connection_data']['meta']['link'];
        array_push($social_data, $temp);
    }
    foreach($php_data['publicize_connections']['linkedin'] as $value){
        $temp = $value['connection_data']['id'];
        $temp = 'https://www.linkedin.com/company/' . $temp;
        array_push($social_data, $temp);
    }
    foreach($php_data['publicize_connections']['google_plus'] as $value){
        $temp = $value['external_id'];
        $temp = 'https://plus.google.com/' . $temp;
        array_push($social_data, $temp); 
    } 

    // Yoast meta description
    $yoast = get_post_meta(get_the_ID(), '_yoast_wpseo_metadesc', true); 
    if (empty($yoast)) {
        // Yoast has not been found, get standard meta data
        $yoast = get_post_meta(get_the_ID());
    } else {
        // Yoast has been found
        $yoast = $yoast;
    }
        // Decode the json data in php readable code
        $jsonString = json_decode($data, true);
        
        // Write the dynamic input to the Json file, and append it to the page
        $jsonString['areaServed'] = $locFormatted . ", Netherlands"; // Write location name based on url to the Json
        $jsonString['name'] =  $homeFormatted;
        $jsonString['url'] = $currentHome;
        $jsonString['logo'] = $image[0];      
        $jsonString['description'] = $yoast; 
        $jsonString['sameAs'] = $social_data; 
        $jsonString['contactPoint']['email'] = 'info@' . $homeReplaced;
        $jsonString['contactPoint']['url'] = $currentHome;

        // Re-encode the data
        $newJsonString = json_encode($jsonString, true);
        // Rewrite the data to readable data
        $new_data = '<script type="application/json">' . strval($newJsonString) . "<script>";

        // Write the updated json file to the content
         $data = $new_data;
         echo $data;
?>
