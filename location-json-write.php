<?php
/*
Plugin Name:  Dynamic text area
Plugin URI:   http://www.waydesign.nl/dynamictextarea
Description:  Retrieves file with same page slug as the url, gets the content from within a .docx file. Added support for page name shortcode [pagename]
Version:      0.6
Author:       http://www.waydesign.nl
Author URI:   http://www.waydesign.nl
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  wporg
Domain Path:  /languages
Shortcode Syntax: Default:[textload] || [textload type="textone"] || [textload type="texttwo"] || [textload type="textthree..."]
*/

// Plugin updater

require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://advandm297.297.axc.nl/dynamic-text-area.json',
	__FILE__,
	'dynamic_text_area_updater'
);

// Plugin registration
register_activation_hook( __FILE__, array( 'dynamic_input', 'install' ) );

/**
 * Retrieve the matched file and convert it in the readDocx function
 * Explode the file, and return in a array, define wich textarea should be retrieved.
 * based on what shortcode attribute has been used
 * Shortcode Syntax: Default:[textload] || [textload type="textone"] || [textload type="texttwo"] || [textload type="textthree..."]
 */
    class dynamic_input {

        // Plugin install
        static function install() {
            // do not generate any output here
        }

        // Convert .docx file's to text
        public function readDocx($filePath) {
            // Create new ZIP archive
            $zip = new ZipArchive;
            $dataFile = 'word/document.xml';
            // Open received archive file
            if (true === $zip->open($filePath)) {
                // If done, search for the data file in the archive
                if (($index = $zip->locateName($dataFile)) !== false) {
                    // If found, read it to the string
                    $data = $zip->getFromIndex($index);
                    // Close archive file
                    $zip->close();
                    // Load XML from a string
                    // Skip errors and warnings
                    $xml = new DOMDocument();
                    $xml->loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
                    // Return data without XML formatting tags
        
                    $contents = explode('\n',strip_tags($xml->saveXML()));
                    $text = '';
                    foreach($contents as $i=>$content) {
                        $text .= $contents[$i];
                    }
                    return $text;
                }
                $zip->close();
            }
            // In case of failure return empty string
            return "";
        }      
        /**
         * Retrieve a .docx file from the server, with the same name as the page name.
         * explode thrue the .docx file, with placholders and write the area's to the page with shortcode
         */
        public function dynamic_text_area( $atts ){
            // Define global vars for admin options
            global $currentFolder;
            global $currentExtension;

            // File location
            $currentDirectory = getcwd();
            $currentPage = $_SERVER['REQUEST_URI'];
            // Replace slashes for permalink compatibility
            $currentPage = '/' . preg_replace("/\//", "", $currentPage); 
            $file = $currentDirectory . $currentFolder . $currentPage . $currentExtension;
            $url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

            // Convert the document to text, and to html after that. 
            $class_ref = new dynamic_input;
            $converted_document = &$class_ref->readDocx($file);
            $html_document = html_entity_decode ($converted_document);
            // Add delimeters for preg_match
            $dirString = strval($currentPage) . '/';

            // Check if the current pagename slug exists in the url
            if (preg_match($dirString, $url)) {
                // Retrieve the content and explode the array
                $text = explode('[placeholder dynamic text]', $html_document);
            } else {
                echo "Pagename and url dont match". "<br>";
                $text = the_post();
            }
            // Check for shortcode attribute used, retrieve according text
            extract( shortcode_atts( array(
                'type' => 'myvalue'

            ), $atts ) );

            switch( $type ){
                case 'textone': 
                    $output = $text[1];
                    break;
                case 'texttwo': 
                    $output = $text[2];
                    break;
                case 'textthree': 
                    $output = $text[3];
                    break;
                case 'textfour': 
                    $output = $text[4];
                    break;
                case 'textfive': 
                    $output = $text[5];
                    break;
                case 'textsix': 
                    $output = $text[6];
                    break;
                case 'textseven': 
                    $output = $text[7];
                    break;
                case 'texteight': 
                    $output = $text[8];
                    break;
                case 'textnine': 
                    $output = $text[9];
                    break;
                case 'textten': 
                    $output = $text[10];
                    break;
                case 'texteleven': 
                    $output = $text[11];
                    break;  
                case 'texttwelve': 
                    $output = $text[12];
                    break;   
                case 'textthirteen': 
                    $output = $text[13];
                    break;          
                case 'textfourteen': 
                    $output = $text[14];
                    break;   
                case 'textfifteen': 
                    $output = $text[15];
                    break;   
                case 'textsixteen': 
                    $output = $text[16];
                    break;                                         
                default:
                    $output = $text[0];
                    break;
            }
            $class_ref->if_page_has_dynamic_text($output);
            return $output;
        } // end of dynamic_text_area func

        /**
         * Add second shortcode for dynamic pagename use
         * [pagename]
         */
        public function dynamic_pagename( $atts ){
            // Retrieve the file location from site directory with same name as the page slug.
            $current_page_name = $_SERVER['REQUEST_URI'];
            $current_page_name = preg_replace("/[\W\-]/", ' ', $current_page_name);
            $page_name = strval(ucwords($current_page_name));
        
            return $page_name;
        }
        /**
         * Add shortcode for getting number for each location
         * [telnr]
         */
        public function dynamic_number( $atts ){
            /**
             * Get page name, set variables and perform regex
             * to get the formatted location name out of the page url
             */

            // Set Variables
            $preg_result = [];
            $loc_name;
            $loc_var;
            $fallback_number;
            $tel_number;
            $current_page_name = $_SERVER['REQUEST_URI'];
            
            // Regex operations
            $current_page = preg_replace("/\//", "", $current_page_name); 
            preg_match('/-(.*)/', $current_page_name, $preg_result);
            $loc_name = $preg_result[0];
            $current_page_name = preg_replace("/[\W\-]/", ' ', $loc_name);

            // Set formatted page name
            $page_name = strval(ucwords($current_page_name));
            
            // Get the Csv file and turn it into a array
            $currentDirectory = getcwd();
            $file = $currentDirectory . '/teksten/nummers.csv';
            $csv = array_map('str_getcsv', file($file));

            $loc_var = trim($page_name);
            $fallback_number = $csv[0][1];
            
            /**
             *  Loop thrue each item and check if th page name matches
             *  if it matches get the second array item and return it to the shortcode
             *  In the case the array item doesnt have a number, it returns a fallback number.
             */  
            foreach ($csv as $item){
                $newitem = '/' . strval($item[0]) . '/';
                $newNumb = trim(strval($item[1]));
                    // replace clutter
                    $newNumb = preg_replace('/[;]/', '', $newNumb);
                    $newitem = preg_replace('/[,]/', '', $newitem);
                    $fallback_number = preg_replace('/[;]/', '', $fallback_number);
                    if (preg_match($newitem, $loc_var)){
                        $tel_number = $newNumb;
                        // in case the number array is empty, set fallback
                        if (empty($newNumb) || !isset($newNumb))  {
                            $tel_number = $fallback_number;
                        }
                    }
            }

            return $tel_number;
        }
        /**
         * Build function to check if content has been written to page
         * And send these values to ...
         */
        function if_page_has_dynamic_text($arr){
            // Build location url
            $location_url = 'http://' . $_SERVER['SERVER_NAME'] . '/locaties';
            if (!empty($arr)) {
                echo $arr;
                $site = file_get_contents($location_url);
                $dom = new DOMdocument;

                @$dom->loadHTML($site);
            
                $links = $dom->getElementsByTagName('a');

                foreach ($links as $link){
                    echo $link->nodevalue;
                    echo $link->getAttribute('href'), '<br>';
                }
            }
        }

} // end of class

// Add Shortcode's
add_shortcode( 'textload', array( 'dynamic_input', 'dynamic_text_area' ));
add_shortcode( 'pagename', array( 'dynamic_input', 'dynamic_pagename' ));
add_shortcode('telnr', array( 'dynamic_input', 'dynamic_number' ));

// Add menu item options for changing folder and extension

// Global variables
$currentFolder = "/teksten";
$currentExtension = ".docx";

function settings_dynamic_text(){
    // Add settings page
        add_options_page('DynamicTextOptions', 'Dynamic text shortcode', 'manage_options', 'dytext', 'dynamic_text_options');

}
function dynamic_text_options(){
    if(!current_user_can('manage_options')){
        wp_die(_('You do not have sufficient permissions to access this page.'));
    }
    echo '<div class="wrap">';
    echo '<form method="post" action="">
          <strong>Folder for tekst : </strong><input type="text" name="folderInput"></input><br>
          <strong>Type extensie gebruikt : </strong><input type="text" name="extInput"></input>
          <input type="submit">
          </form>
          ';
    echo '</div>';
    $currentFolder = $_POST['folderInput'];
    $currentExtension = $_POST['extInput'];
    echo $currentFolder;
    echo $currentExtension;
}
add_action('admin_menu', 'settings_dynamic_text');
?>
