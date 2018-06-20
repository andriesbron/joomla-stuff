<?php
/**
 * @package     iopmediachannel
 * @subpackage  iopmediachannel
 *
 * @copyright   Copyright (C) 2017 Andries Bron, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Iopmediachannel is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Iopmediachannel is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Radenium.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
 
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Import library dependencies
jimport('joomla.event.plugin');
jimport('joomla.plugin.plugin');

class plgContentIopmediachannel extends JPlugin
{
        /**
         * Load the language file on instantiation. Note this is only available in Joomla 3.1 and higher.
         * If you want to support 3.0 series you must override the constructor
         *
         * @var    boolean
         * @since  3.1
         */
        protected $autoloadLanguage = true;

        /*
        function __construct(& $subject, $config)
        {
            $this->loadLanguage();
            
            
            parent::__construct($subject, $config);
        }    
        */
        
    //Constructor
    // @todo add like buttons and store what people like in this video.
    // @todo add subtitles
    function plgContentIopmediachannel(&$subject) {
        
        parent::__construct($subject);
        $this->loadLanguage();
        $this->plugin = JPluginHelper::getPlugin('content', 'iopmediachannel');
        $this->params = new JRegistry($this->plugin->params);
        $document = JFactory::getDocument();
        $docType = $document->getType();        
        if($docType != 'html')
        {
            return; 
        }                
        // load current language
        $this->loadLanguage();
        $document->addStyleSheet(JURI::base(). "plugins/content/iopmediachannel/iopmediachannel.css");
        $document->addScript('plugins'.DIRECTORY_SEPARATOR.'content'.DIRECTORY_SEPARATOR.'iopmediachannel'.DIRECTORY_SEPARATOR.'iopmediachannel.js');
        //JHTML::_('behavior.modal');
    }

    //Joomla 1.6 and > function 
    public function onContentPrepare($context, &$row, &$params, $page = 0) {
        if ($context == 'com_finder.indexer') {
            return true;
        }
        
        /*
        echo "<pre>";
        print_r($context);
        print_r($row->introtext);
        echo "<hr />";
        print_r($row->text);
        echo "</pre>";
        */
        if (is_object($row)) {
            if (preg_match("#{iop_mediachannel*(.*?)}#", strtolower($row->text))) {
                $row->text = $this->renderMediaChannel( $row->text );
                $row->introtext = $this->renderMediaChannel( $row->introtext );
                
            }
        }
        return true;
    }

    public function renderMediaChannel( $text ) {
        $arr_matches = array();
        // Find all matches of iopmediachannel plugin
        preg_match_all("#{iop_mediachannel (.*?)}(.*?){/iop}#", $text, $arr_matches );
        //echo "<pre>";
        //print_r($arr_matches);
        $items = array();
        $i = 0;
        foreach( $arr_matches[0] as $item ) {
            // The plugin string = $item[0]
            // The plugin attributes = $item[1]
            // The plugin content = $item[2]
            $_attribs = array();
            $attribs = array(
                "type"=>""
                ,"src"=>""
            );
            preg_match_all("#\s*(.*?)\s*=\s*\"(.*?)\"\s*#", $arr_matches[1][$i], $_attribs );
            $j = 0;
            foreach( $_attribs[0] as $a ) {
                $attribs[$_attribs[1][$j]] = $_attribs[2][$j];
                $j += 1;
                
            }
            $items[] = array(
                "attribs"=>$attribs
                , "content"=>$arr_matches[2][$i]
                , "query"=>$arr_matches[0][$i]
                
            );
            $i += 1;
            
        }
        
        //echo "<h1>All Items</h1>";
        //print_r($items);
        //echo "</pre>";
        foreach($items as $media) {
            switch($media['attribs']['type']){
                case "video":
                    $parsed = $this->parseVideoItem($media);
                    break;
                    
                case "live":
                    $parsed = $this->parseLive($media);
                    break;
                    
                case "directory":
                    $parsed = $this->parseDirectory($media);
                    break;
                    
                default:
                    $parsedtext = "No IOP Media Channel Information Found...";
                    break;
            
            }
            $parsedtext = str_replace($media["query"], $parsed, $text);
            
        }
        return $parsedtext;
        
    }
    
    
    function parseDirectory($item) {
        return "Directory scanning not implemented...";
    }
    
    
    function parseLive($item) {
        return "Live not implemented...";
    }
    
    
    function parseVideoItem($item) {
        // parse the src attribute, if it is .m3u8 or stuff like that return the hls player.
        $parsedhtml = "";
        if ( substr($item['attribs']['src'], ".m3u8") !== false) {
            $parsedhtml = $this->getHlsplayer($item['attribs']['src']);
        }
        
        
        if ($this->params->get("stickyplayer") == "1") {
            $parsedhtml =$this->getStickyContainer($parsedhtml);
        }
        
        $parsedhtml = "<div id=\"iop_logo_icon\"></div>".$parsedhtml;
        
        return $parsedhtml;
    }
    
    
    function getStickyContainer($content, $rightcolumn="") {
        $html = "
\n        <div id=\"nav_height_keeper_inline\">
\n            <div id=\"nav_height_keeper\">
\n                <div id=\"nav_height_keeper_body\">
\n                    <div id=\"sticky_container_content\">
\n                        ".$content."
\n                    </div>
\n                    <div id=\"sticky_container_right_column\">
\n                        ".$rightcolumn."
\n                    </div>
\n                </div>
\n            </div>
\n        </div>
\n        <div class=\"clr\"></div>
\n        ";

        return $html;
    }
    
    
    function getHlsplayer($src) {
        $html = "
\n        <div id=\"video_container\">
\n            <script src=\"https://cdn.jsdelivr.net/npm/hls.js@latest\"></script>
\n            <video class=\"video_player\" id=\"video\" controls autoplay=\"1\" width=\"600px\" height=\"337px\"></video>
\n            <script>
\n                var isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
\n                //var isSafari = navigator.vendor && navigator.vendor.indexOf('Apple') > -1 && navigator.userAgent && !navigator.userAgent.match('CriOS');
\n                if ( isSafari ) {
\n                    var video = document.getElementById('video');
\n                    var source = document.createElement('source');
\n                    source.setAttribute('src', '".$src."');
\n                    video.appendChild(source);
\n                    video.load();
\n                    video.play();
\n
\n                } else if(Hls.isSupported()) {
\n                    var video = document.getElementById('video');
\n                    var hls = new Hls();
\n                    hls.loadSource('".$src."');
\n                    hls.attachMedia(video);
\n                    hls.on(Hls.Events.MANIFEST_PARSED,function() {
\n                        video.play();
\n                    });
\n                }
\n                 // hls.js is not supported on platforms that do not have Media Source Extensions (MSE) enabled.
\n                 // When the browser has built-in HLS support (check using `canPlayType`), we can provide an HLS manifest (i.e. .m3u8 URL) directly to the video element throught the `src` property.
\n                 // This is using the built-in support of the plain video element, without using hls.js.
\n                  else if (video.canPlayType('application/vnd.apple.mpegurl')) {
\n                    video.src = '".$item['attribs']['src']."';
\n                    video.addEventListener('canplay',function() {
\n                      video.play();
\n                    });
\n                  }
\n                </script>
\n            </div>
\n        ";
        
        return $html;
    }

    /**
     * $plugin = JPluginHelper::getPlugin('my_plugin_type', 'my_plugin');
$pluginParams = new JRegistry($plugin->params);
$param1 = $pluginParams->get('param1');
     * @param unknown $text
     * @return unknown
     */
    private function createMagItem( $text ) {
        $arr_matches = array();
        preg_match_all("#{iopmediachannel (.*?)}(.*?){/iop}#", $text->text, $arr_matches ); /*!< First all classes are obtained that are used icw iop delimiter. */
        foreach( $arr_matches[1] as $magazine_item )
        {
            $magazine_item_parts = explode(' ',$magazine_item);
            $item_option = $magazine_item_parts[ 0 ]; // Contains the string of what to do with the iop magazine item.
            
            // Find magazine items in the content with the particular attribute:
            $attributes = array();
            $regex = "#{iop " . $item_option. "}(.*?){/iop}#"; //Find a match with the whole story in between { ... }
            if( preg_match_all( $regex, $text->text, $matches, PREG_PATTERN_ORDER ) ) {
                foreach ( $matches[0] as $key => $match ) {
                    foreach ( $magazine_item_parts as $part ) {
                        $part = strip_tags( $part );
                        $item_attributes = explode( '=', $part );
                        
                        //Now I can do stuff with the attributes.
                    }
                }
            }
            
            $leader = "<div>";
            $trailer = "</div>";
            
            switch ( $item_option ) {   /*!< Additional mark-up for magazine items. */
                
                case "qt": // Legacy...
                case "quote":
                    $color = "color:#".$this->params->get("quote_color").";";
                    if ( $this->params->get("quote_style") == "0" ) {
                        $style_plus = "border-left:".$this->params->get("quote_boldness")."pt solid;";
                    }
                    
                    $style = $color.$style_plus;
                    $leader = "<div class=\"plg_mag_quote_style_option_".$this->params->get("quote_style")."\" style=\"".$style."\">";
                    $trailer = "</div>";
                    break;
                    
                case "slideshow":
                    
                    break;
                    
                case "footnote":
                    //count foot notes and add them to the bottom of the text and insert a href # link.
                    break;
            }
            
            
            // Get the thing into style
            $matchReplacement = $match;
            $matchReplacement = preg_replace("#{/iop}#", $trailer, $matchReplacement );
            $matchReplacement = preg_replace("#{iop ".$item_option."}#", $leader, $matchReplacement );
            
            $text->text = preg_replace( "#".$match."#", $matchReplacement , $text->text);
        }
        
        return $text->text;
    }
}
?>