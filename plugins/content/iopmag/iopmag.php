<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
  
// Import library dependencies
jimport('joomla.event.plugin');
jimport('joomla.plugin.plugin');

class plgContentIopmag extends JPlugin
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
    function plgContentIopmag(&$subject) {
    	
        parent::__construct($subject);
        $this->loadLanguage();
        $this->plugin = JPluginHelper::getPlugin('content', 'iopmag');
        $this->params = new JRegistry($this->plugin->params);
		$document	= JFactory::getDocument();
		$docType = $document->getType();		
		if($docType != 'html')
		{
			return; 
		}				
        // load current language
        $this->loadLanguage();
		$document->addStyleSheet(JURI::base(). "plugins/content/iopmag/iopmag.css");

		//JHTML::_('behavior.modal');
    }

    //Joomla 1.6 and > function 
    public function onContentPrepare($context, &$row, &$params, $page = 0) {
    	if ($context == 'com_finder.indexer') {
    		return true;
    	}
    	
        if (is_object($row)) {

            if (preg_match("#{iop*(.*?)}#", strtolower($row->text))) {
                    $row->text = $this->createMagItem( $row );
            }
        }

        return true;
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
    	preg_match_all("#{iop (.*?)}(.*?){/iop}#", $text->text, $arr_matches ); /*!< First all classes are obtained that are used icw iop delimiter. */
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
    
    
    private function _createMagItem( $text ) {
    
    /*
        ook navigatie items maken met tekst daarin om een gebruiker te laten klikkerdieklikken.
    */
    
        $arr_matches = array();
        // Get all the iop magazine tags
        preg_match_all("#{iop (.*?)}(.*?){/iop}#", $text->text, $arr_matches ); /*!< First all classes are obtained that are used icw iop delimiter. */


        //eigenlijk magItems array strippen van gekke karakters voor diegenen die foute code invoeren spaties en ; en : # @ alles eruit.
        // Parse each tag according to its attribute
        foreach( $arr_matches[1] as $magItem )
        {
            $magItemParams = explode(' ',$magItem);
            $magItemType = $magItemParams[ 0 ]; // Contains the string of what to do with the iop magazine item.
            $magItemOptions = array();
            //$regex = "#{iop qt}(.*?){/iop}#s";
            $regex = "#{iop " . $magItem . "}(.*?){/iop}#"; //Find a match with the whole story in between { ... }
            					            
            // process tags
            if( preg_match_all( $regex, $text->text, $matches, PREG_PATTERN_ORDER ) ) {
            //print_r( $matches );
                foreach ( $matches[0] as $key => $match ) {
                    
                    $toFollow = '';
                   //print_r( $match );
                    
                    foreach ( $magItemParams as $ItemParam )
                    {
                    $ItemParam = strip_tags( $ItemParam );
                     
                        $magItemOptionsArray = explode( '=', $ItemParam );
                        $magItemOptionsArray[ 1 ] = str_replace( '_', ' ', $magItemOptionsArray[ 1 ] );
                        switch( $magItemOptionsArray[ 0 ] )
                        {
                            case 'st': /* style which is parsed here. */
                                $parsedValue = str_replace( 'color:', 'color:#', $magItemOptionsArray[ 1 ] );
                                break;
                            case 'tl': /* title */
                                $parsedValue = $magItemOptionsArray[ 1 ];
                                break;
                            case 'fl':                            
                                $toFollow = str_replace( '@', '', $magItemOptionsArray[ 1 ] );                         
                                $copen = '<div class="iopmag-'.$magItemType.'">';
                                $cclose = '</div>';
                                $quoteDivOpen = $copen . '<a href="https://twitter.com/' . $toFollow . '" class="twitter-follow-button" data-show-count="false">Follow '.$magItemOptionsArray[ 1 ].'</a>';
                                $quoteDivClose = "<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>" . $cclose;
                                $parsedValue = $quoteDivOpen . $quoteDivClose;                                
                                break;                                
                            case 'ht':                            
                                $copen = '<div class="iopmag-'.$magItemType.'">';
                                $cclose = '</div>';                              
                                $quoteDivOpen = $copen .'<a href="https://twitter.com/intent/tweet?button_hashtag='.$magItemOptionsArray[ 1 ].'" class="twitter-hashtag-button" data-related="'.$toFollow.'" data-url="">Tweet #'.$magItemOptionsArray[ 1 ].'</a>';
                                $quoteDivClose = "<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>" . $cclose;
                                $parsedValue = $quoteDivOpen . $quoteDivClose;
                                //echo '<textarea>'.$parsedValue.'</textarea>';
                                break;  
                            case 'fdb':
                                $copen = '<div class="iopmag-'.$magItemType.'">';
                                $cclose = '</div>';
                                $quoteDivOpen = $copen . '<div class="iopmag-'.$magItemType.'-fdb"><a href="'.$magItemOptionsArray[ 1 ].'">Send Feedback!</a></div>';
                                $quoteDivClose = '' . $cclose;
                                $parsedValue = $quoteDivOpen . $quoteDivClose;  
                                //echo '<textarea>'.$parsedValue.'</textarea>';                      
                                break;                          
                            default:
                                break;
                                
                                
                                
                        }
                        $magItemOptions[ $magItemOptionsArray[ 0 ] ] = $parsedValue;

                    }

                    $quoteDivOpen = '<div class="iopmag-' . $magItemType . '"';
                    
                    if ( isset( $magItemOptions[ 'st' ] ) ){
                        $quoteDivOpen .= ' style="'.$magItemOptions[ 'st' ]; //Add inline style
                    }
  
                    $quoteDivOpen .= '>';
                    
                    if ( isset( $magItemOptions[ 'tl' ] ) ){
                        $quoteDivOpen .= '<div class="iopmagoption-title">'.$magItemOptions[ 'tl' ].'</div>'; //Add a title element
                    }
                     
                     
                    $quoteDivOpen .= '<div class="iopmagcontent">'; 
                    $quoteDivClose = '</div></div>';
             
                    switch ( $magItemType ) {   /*!< Additional mark-up for magazine items. */
                    
                        case 'qt':
                            break;
                            
                        case 'qtl': /*!< Intentionally fall through. */
                        case 'qtr':
                            $quoteDivOpen .= '<div class="iopmag-justforaborder">';
                            $quoteDivClose .= '</div>';
                            break;
                            
                        case 'social': /*!< Overwrite everything. */
                            
                            $copen = '<div class="iopmag-'.$magItemType.'">';
                            $cclose = '</div>';
                            $quoteDivOpen = ''; 
                            $quoteDivClose = '';                           
                            $quoteDivOpen = $copen . '<a href="https://twitter.com/share" class="twitter-share-button" data-via="iopmedia" data-hashtags="yda">Tweet</a>';
                            $quoteDivClose = "<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>" . $cclose;
                            // better put the stuff in an array and just add above on the fly and parse here each element....
                            if ( isset( $magItemOptions[ 'fl' ] ) ){ //Add a follow button if given.
                                $quoteDivClose .= $magItemOptions[ 'fl' ];
                            }
                            if ( isset( $magItemOptions[ 'ht' ] ) ){ //Add a hashtag button if given.
                                $quoteDivClose .= $magItemOptions[ 'ht' ];
                            }
                            if ( isset( $magItemOptions[ 'fdb' ] ) ){ //Add a feedback button if given.
                                $quoteDivClose .= $magItemOptions[ 'fdb' ];
                            }
                            $quoteDivClose .= '<div style="clear:both"></div>';
                            break;   
                        default:
                            break;
                    }

                    //preg_replace("/{.+?}/", "", $match);
                    $matchReplacement = $match;
                    $matchReplacement = preg_replace("#{/iop}#", $quoteDivClose, $matchReplacement );
                    $matchReplacement = preg_replace("#{iop ".$magItem."}#", $quoteDivOpen, $matchReplacement );
                    $matchReplacement = $this->parseSheBangs( $matchReplacement );
                    $text->text = preg_replace( "#".$match."#", $matchReplacement , $text->text);
                }
            }
		}
      
		return $text->text; /*!< No items found to be inserted... */
	}
	
	private function parseSheBangs( $ctext )
	{
	    
	    $ctext = preg_replace("#!h1#", '<h1>', $ctext );
        $ctext = preg_replace("#~h1#", '</h1>', $ctext );
        
        return $ctext;
	    
	}

        /**
         * Plugin method with the same name as the event will be called automatically.
         */
         //function <EventName>()
         //{
                /*
                 * Plugin code goes here.
                 * You can access database and application objects and parameters via $this->db,
                 * $this->app and $this->params respectively
                 */
                //return true;
        //}
}
?>