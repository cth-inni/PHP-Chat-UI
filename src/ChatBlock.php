<?php
namespace TangHoong\ChatBlock;

class ChatBlock
{
    public $roles;
    public $lines;
    public $output;
    public $rawData;
    public $settings;
    public $dialogue;
    public $colonList;
    public $emojiList;
    public $rolesList;
    public $colorsList;
    public $currentCast;
    public $narratorList;
    public $SettingBreakPoint;
    public $SettingWhitelistTag;
    function __construct($newObj=null)
    {
        // default
        $this->libpath   = 'https://github.com/tanghoong/phpchatblock/'; // use for checking
        $this->version   = '0.2.33'; // Change before each commit
        $this->linebreak = '\r\n'; // Window, Linux
        $this->https     = 'https:'; // Window, Linux
        // Settings
        $this->rawData = '';
        $this->currentCast = '';
        $this->SettingCommand = "=";
        $this->SettingBreakPoint = "_ADVANCE_";
        $this->emojiList = [];
        $this->rolesList = [];
        $this->colonList = [':'];
        $this->codedColon = ['_CODEDCOLON_'];
        $this->narratorList = ['narrator'];
        $this->colorsList = [
            'a4dab7', '91d2a8', '8ec6a1', '8ec1a0', '8bbc9c', // a4dab7 Fox Color Palette
            'a2c4c9', '8eabaf', '94a5a7', '899ea1', '869799', // Good Friend Tweetle Color Palette
            'dac38a', 'caba7b', 'beaa79', 'a99b70', '978a62', // Wirtschaft und Recht Color Palette
        ];
        $this->SettingWhitelistTag = [
            'p','h1','h2','h3','h4','h5','h6','linebreak','link', // article
            'image','imagecard','profilecard', // rich media
            '#','##','###','####','#####','######','---', // render interface
            'alert','success','warning','danger', // system cast
            'codeblock','showquote','devtools', // raw
            'mp3','background','youtube','decision', // experincement tags
        ];
        $this->SettingBlacklistTag = [
            'rawscript','rawquote', // retired tags
        ];
        // default setting
        $oriObj = [
            'devTools'          => false,
            'allowForkScript'   => null,
            'extraImageClass'   => null,
            'chatHeaderSize'    => 'normal',
            'mainCastColor'     => '#248bf5',
            'castColorMode'     => 'none', // none, random, palette (15 colors)
            'castsColorsRange'  => '100,200', // 0 - 255
        ];
        if(is_null($newObj))
        {
            $defObj = $oriObj;
        }else{
            $defObj = $this->_mergeRecursively((object)$oriObj,(object)$newObj);
        }
        // merged setting
        $this->settings = (object)$defObj;
    }
    /**
     * To allow using as Json format for frontend rendering
     */
    public function rawdata(){
        return $this->dialogue;
    }
    /**
     * To allow using as Json format for frontend rendering
     */
    public function json(){
        return json_encode($this->dialogue);
    }
    /**
     * Set Colon
     */
    public function setColon($colonArray = []){
        $this->colonList = $colonArray;
    }
    /**
     * Set Emoji
     */
    public function setEmoji($emojiArray = []){
        $this->emojiList = $emojiArray;
    }
    /**
     * Set Narrator
     */
    public function setNarrator($narratorArray = []){
        $this->narratorList = $narratorArray;
    }
    /**
     * Set LineBreak
     */
    public function setLineBreak($newLineBreak = ''){
        $this->linebreak = $newLineBreak;
    }    
    /**
     * Set Breakpoint
     */
    public function setBreakPoint($newBreakPoint = ''){
        $this->SettingBreakPoint = $newBreakPoint;
    }
    /**
     * Reserved for others formation
     */
    public function output(){
        return $this->output;
    }
    /**
     * Color selection
     */
    public function paletteColor ()
    {
        $color = array_shift($this->colorsList);
        return '#'.$color;
    }
    /**
     * Random Color selection
     */
    public function randomColor ($rangeVal = '0,255')
    {
        $minMaxVal = explode(',',$rangeVal);
        $minVal = $minMaxVal[0];
        $maxVal = $minMaxVal[1];
        // Make sure the parameters will result in valid colours
        $minVal = $minVal < 0 || $minVal > 255 ? 0 : $minVal;
        $maxVal = $maxVal < 0 || $maxVal > 255 ? 255 : $maxVal;
    
        // Generate 3 values
        $r = mt_rand($minVal, $maxVal);
        $g = mt_rand($minVal, $maxVal);
        $b = mt_rand($minVal, $maxVal);
    
        // Return a hex colour ID string
        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }
    /**
     * Show error message
     */
    public function showWarnings(){
        $tempHtml = '<div class="chatblock">';
        foreach($this->dialogue['warnings'] as $line)
        {
            $tempHtml .= $this->render_warningsblock($line);
        }
        $tempHtml .= '</div>';
        return $tempHtml;
    }
    /**
     * Show error message
     */
    public function showCasts(){
        $tempHtml  = '<div class="chatblock" style="overflow-x:auto;">';
        $tempHtml .= '<div class="imessage casts-list" style="margin:0 !important;">';
        foreach($this->dialogue['casts'] as $cast)
        {
            $chatColor = $this->loadCastColor($cast['name']);
            $tempHtml .= '<div class="cast btnGotoHead" data-castid="'.$cast['castId'].'">';
            $tempHtml .= '<div class="square disable-select" style="border-color:'.$chatColor.';background:#38A899 url('.$this->loadChatHeaderImg($cast['name']).')center center/60px 60px no-repeat;" role="text" aria-label="'.trim($cast['name']).'" alt="'.trim($cast['name']).'">'.trim($cast['name']).'</div>';
            $tempHtml .= '</div>';
        }
        $tempHtml .= '</div>';
        $tempHtml .= '</div>';
        return $tempHtml;
    }

    public function feed($rawData='')
    {
        $this->rawData = $rawData;
        $chat['warnings']    = [];
        $chat['settings']    = [];
        $chat['scenes']      = [];
        $chat['casts']       = [];
        $chat['lines']       = [];

        try {
            $chat['settings'] = $this->_buildSettingsFeed($rawData);
            $chat['lines']    = $this->_buildLinesFeed($rawData);     // Always must first run
            $chat['casts']    = $this->_buildCastsFeed($rawData);
            $chat['scenes']   = $this->_buildSceneFeed($rawData);
        }
        catch(Exception $e) {
            $chat['warnings']    = $e->getMessage();
        }
        $this->dialogue = $chat;
    }
    private function _buildSettingsFeed($rawData) {
        $proceedData = [];
        $linesData = strstr($rawData, $this->SettingBreakPoint, true);
        $rolesData = strstr($rawData, $this->SettingBreakPoint);
        if($rolesData !== false && $linesData !== false)
        { // Advance mode
            $rolesArray = array_values(array_filter(explode(PHP_EOL,$rolesData)));
            foreach($rolesArray as $roleKey => $roleVal)
            {
                if(preg_match('/settings=\{[^}]+\}/i',$roleVal))
                { // Matched settings={JSONstring}
                    $tempScenes = [];
                    $tempData = strstr($roleVal, $this->SettingCommand);
                    $tempData = ltrim($tempData, $this->SettingCommand);
                    $meta = strstr($roleVal, $this->SettingCommand, true);
                    $proceedData = json_decode($tempData,true);
                }
            }
        }
        return $proceedData;
    }
    private function _buildSceneFeed($rawData) {
        $proceedData = [];
        $linesData = strstr($rawData, $this->SettingBreakPoint, true);
        $rolesData = strstr($rawData, $this->SettingBreakPoint);
        if($rolesData !== false && $linesData !== false)
        { // Advance mode
            $rolesArray = array_values(array_filter(explode(PHP_EOL,$rolesData)));
            foreach($rolesArray as $roleKey => $roleVal)
            {
                if(preg_match('/scene+\-+[0-9]{1,3}=\{[^}]+\}/i',$roleVal))
                { // Matched scene-[1-999]={JSONstring}
                    $tempScenes = [];
                    $tempData = strstr($roleVal, $this->SettingCommand);
                    $tempData = ltrim($tempData, $this->SettingCommand);
                    $meta = strstr($roleVal, $this->SettingCommand, true);
                    $proceedData[$meta] = json_decode($tempData,true);
                }
            }
        }
        return $proceedData;
    }
    private function _buildCastsFeed($rawData) {
        $proceedData = [];
        array_unique($this->rolesList); // Unique cast list
        $linesData = strstr($rawData, $this->SettingBreakPoint, true);
        $rolesData = strstr($rawData, $this->SettingBreakPoint);
        $tempRoles = array_diff($this->rolesList, $this->SettingWhitelistTag);
        $tempRoles = array_values(array_unique($tempRoles));
        foreach($tempRoles as $tempRolesKey)
        {
            $tempCast = [];
            $tempCast['name']    = $tempRolesKey;
            $tempCast['castId']  = uniqid();
            $tempCast['color']     = null;
            $tempCast['img']     = null;
            switch($this->settings->castColorMode)
            {
                case 'random':
                    $tempCast['color']   = $this->randomColor($this->settings->castsColorsRange);
                break;
                case 'palette':
                    $tempCast['color']   = $this->paletteColor();
                break;
                case 'none':
                    $tempCast['color']   = '#cccccc';
                break;
            }
            array_push($proceedData, $tempCast);
        }
        if($rolesData !== false && $linesData !== false)
        { // Advance mode
            $castIndexCounter = 0;
            $rolesArray = array_values(array_filter(explode(PHP_EOL,$rolesData)));
            foreach($rolesArray as $roleKey => $roleVal)
            {
                // Advance Settings
                if(preg_match('/@/i',$roleVal))
                { // match name@meta_data
                    $tempCast = [];
                    $tempArray = explode("@",$roleVal);
                    if(isset($tempArray) && count($tempArray) > 1)
                    {
                        list($name, $img)  = $tempArray;
                        foreach($proceedData as $castKey => $castData)
                        {
                            if(isset($castData['name']) && $castData['name'] == $name)
                            {
                                if($castIndexCounter == 0)
                                { // Shift to first as main cast
                                    $newCastdata = [];
                                    $newCastdata['name']  = $name;
                                    $newCastdata['castId']= uniqid();
                                    $newCastdata['color'] = $proceedData[$castKey]['color'];
                                    $newCastdata['img']   = $img;
                                    unset($proceedData[$castKey]);
                                    array_unshift($proceedData,$newCastdata);
                                }else
                                { // Update img only
                                    $proceedData[$castKey]['img'] = $img;
                                }
                            }
                        }
                        $castIndexCounter++; // Only apply to first cast setting
                    }
                }
                // End
            }
        }
        return $proceedData;
    }
    private function _buildLinesFeed($rawData) {
        $proceedData = [];
        $linesArray = array_values(array_filter(explode(PHP_EOL,$rawData))); // string to array
        foreach($linesArray as $lineKey => $lineVal)
        {
            if(trim($lineVal) != $this->SettingBreakPoint)
            {
                $tempLine = [];
                $tempLine['_type']     = null;
                $tempLine['_line']     = null;
                $tempLine['_castname'] = null;
                $tempLine['_context']  = null;
                $tempLine['emojis']    = [];
                $detectFlag = false;
                $tempLine['_line']   = $lineVal;
                foreach($this->colonList as $tempColon)
                {
                    if( $detectFlag == false )
                    {
                        $castname = strstr($lineVal, $tempColon, true);
                        $content  = preg_replace('/'.$tempColon.'/', '', strstr($lineVal, $tempColon), 1); // replace on first match only
                        $checkValid = substr($castname, 0, 2); // Comment script to ignore
                        if(!in_array($castname,$this->SettingBlacklistTag) && $checkValid != '//')
                        {
                            if($castname == false && $content == false)
                            {
                                $tempLine['_type']  = 'line';
                            }else
                            { // 
                                $tempLine['_type']  = 'talk';
                                $tempLine['_castname']   = $castname;
                                $newLine = $this->replaceEmojiTexttoImage($content);
                                $tempLine['emojis']     = $newLine['emojis'];
                                $tempLine['_context']   = $newLine['_context'];
                                if(!$this->in_arrayi($castname,$this->narratorList))
                                { // Exclude narrator
                                    array_push($this->rolesList,$castname); // Build Cast list
                                }
                                $detectFlag = true;
                                break;
                            }
                        }
                    }
                }
                array_push($proceedData,$tempLine);
            }else
            { // Quit if hit $this->SettingBreakPoint
                break;
            }
        }
        return $proceedData;
    }
    private function in_arrayi($needle, $haystack)
    {
        return in_array(strtolower($needle), array_map('strtolower', $haystack));
    }
    private function _mergeRecursively($obj1, $obj2) {
        if (is_object($obj2)) {
            $keys = array_keys(get_object_vars($obj2));
            foreach ($keys as $key) {
                if (
                    isset($obj1->{$key})
                    && is_object($obj1->{$key})
                    && is_object($obj2->{$key})
                ) {
                    $obj1->{$key} = $this->_mergeRecursively($obj1->{$key}, $obj2->{$key});
                } elseif (isset($obj1->{$key})
                && is_array($obj1->{$key})
                && is_array($obj2->{$key})) {
                    $obj1->{$key} = $this->_mergeRecursively($obj1->{$key}, $obj2->{$key});
                } else {
                    $obj1->{$key} = $obj2->{$key};
                }
            }
        } elseif (is_array($obj2)) {
            if (
                is_array($obj1)
                && is_array($obj2)
            ) {
                $obj1 = array_merge_recursive($obj1, $obj2);
            } else {
                $obj1 = $obj2;
            }
        }

        return $obj1;
    }
    private function replaceEmojiTexttoImage($source='') {
        $tempEmoji = [];
        $newTarget = $source;
        // Emoji replace - start
        preg_match_all("/\:([^:]*)\:/", $source, $tempMatchedEmojiArray); // All matched emoji to Array
        $tempMatchedEmojiArray   = array_unique(array_merge(...array_values($tempMatchedEmojiArray))); // emoji Array flatten
        foreach($tempMatchedEmojiArray as $emoji)
        {
            if(preg_match("/\:([^:]*)\:/", $emoji))
            { // only valid emoji value
                array_push($tempEmoji, $emoji);
            }
        }
        if(count($tempEmoji) > 0)
        { // If emoji detected
            foreach($tempEmoji as $emoji)
            {
                $selectedEmoji = trim($emoji,':');
                if(isset($this->emojiList[$selectedEmoji]))
                { // If exist in source's list
                    $selectedEmoji = '<img class="emoji-icon" alt="'.$selectedEmoji.'" src="'.$this->emojiList[$selectedEmoji].'" />';
                }
                $newTarget = preg_replace('/'.$emoji.'/', $selectedEmoji, $newTarget);
            }
        }
        // Emoji replace - end
        return [
            'emojis'   => $tempEmoji,
            '_context' => $newTarget
        ];
    }





    /**
     * Using default html rendered chat blocks
     */
    public function render(){
        $tempHtml  = '<div class="chatblock">';
        $tempHtml .= '<section class="vf-80">';
        // foreach($this->dialogue['warnings'] as $line)
        // {
        //     $tempHtml .= $this->render_warningsblock($line);
        // }
        foreach($this->dialogue['lines'] as $dialogue)
        {
            if(trim($dialogue['_line']) != $this->SettingBreakPoint)
            {
                switch($dialogue['_castname'])
                {
                    case '#': // h1
                        $this->currentCast = null;
                        $tempHtml .= $this->md_render_heading($dialogue,1);
                    break;
                    case '##': // h2
                        $this->currentCast = null;
                        $tempHtml .= $this->md_render_heading($dialogue,2);
                    break;
                    case '###': // h3
                        $this->currentCast = null;
                        $tempHtml .= $this->md_render_heading($dialogue,3);
                    break;
                    case '####': // h4
                        $this->currentCast = null;
                        $tempHtml .= $this->md_render_heading($dialogue,4);
                    break;
                    case '#####':  // h5
                        $this->currentCast = null;
                        $tempHtml .= $this->md_render_heading($dialogue,5);
                    break;
                    case '######':  // h6
                        $this->currentCast = null;
                        $tempHtml .= $this->md_render_heading($dialogue,6);
                    break;
                    case '---': // scene
                        $this->currentCast = null;
                        $tempHtml .= $this->render_cutscene($dialogue);
                    break;
                    case 'h1': 
                    case 'h2': 
                    case 'h3': 
                    case 'h4': 
                    case 'h5': 
                    case 'h6': 
                        $this->currentCast = null;
                        $tempHtml .= $this->render_heading($dialogue);
                    break;
                    case 'linebreak': 
                        $this->currentCast = null;
                        $tempHtml .= '<br/>';
                    break;
                    case 'p': 
                        $this->currentCast = null;
                        $tempHtml .= $this->render_text($dialogue,'p');
                    break;
                    case 'link': 
                        $this->currentCast = null;
                        $tempHtml .= $this->render_reflink($dialogue);
                    break;
                    case 'showquote': 
                        $this->currentCast = null;
                        $tempHtml .= $this->render_rawdata($dialogue,$this->rawData);
                    break;
                    case 'rawdata_full': 
                        $this->currentCast = null;
                        $tempHtml .= $this->render_rawdata_full($dialogue,$this->rawData);
                    break;
                    case 'codeblock': 
                        $this->currentCast = null;
                        $tempHtml .= $this->render_codeblock($dialogue);
                    break;
                    case 'image': 
                        $this->currentCast = null;
                        $tempHtml .= $this->render_image_holder($dialogue);
                    break;
                    case 'imagecard': 
                        $this->currentCast = null;
                        $tempHtml .= $this->render_imagecard_holder($dialogue);
                    break;
                    case 'mp3': 
                    case 'background': 
                        $this->currentCast = null;
                        $tempHtml .= $this->render_sound_holder($dialogue);
                    break;
                    case 'youtube': 
                        $this->currentCast = null;
                        $tempHtml .= $this->render_video_holder($dialogue);
                    break;
                    case 'decision': 
                        $this->currentCast = null;
                        $tempHtml .= $this->render_decisions_holder($dialogue);
                    break;
                    case 'devtools': 
                        $this->currentCast = null;
                        $tempHtml .= $this->renderDev($dialogue,$dialogue['_line']);
                    break;
                    default: 
                        if(in_array($dialogue['_castname'],$this->narratorList))
                        { // Custom narrator
                            $this->currentCast = null;
                            $tempHtml .= $this->role_narrator($dialogue);
                        }else{
                            if(isset($this->dialogue['casts'][0]) && $this->dialogue['casts'][0]['name'] == $dialogue['_castname'])
                            { // maincast
                                // $this->currentCast = null;
                                $tempHtml .= $this->renderRoleSide($dialogue,'right',$this->settings->mainCastColor);
                            }else{
                                if(!is_null($dialogue['_context']))
                                { // others cast
                                    // $this->currentCast = null;
                                    $tempHtml .= $this->renderRoleSide($dialogue,'left',$this->loadCastColor($dialogue['_castname']));
                                }else
                                { // Normal text
                                    $this->currentCast = null;
                                    $tempHtml .= $this->render_text($dialogue,'sentence');
                                }
                            }
                        }
                    break;
                }
            }else
            { // Quit if hit $this->SettingBreakPoint
                break;
            }
        }
        $tempHtml .= '</section>';
        $tempHtml .= '</div>';
        if($this->settings->devTools)
        {
            $tempHtml .= $this->render_rawdata_full(null,$this->rawData);
        }
        return $tempHtml;
    }
    private function renderDev($dialogue=null,$option='')
    {
        $tempHtml = '';
        switch($option)
        {
            case '--show-data':
                if(!is_null($dialogue))
                {
                    $tempHtml  .= $this->render_rawdata($dialogue,$this->rawData);
                }
            break;
            case '--info':
                $tempHtml  .= '<pre><code id="devShowInfo">';
                $tempHtml  .= '<p><b>Version</b><br/>'.$this->version.'</p>';
                $tempHtml  .= '<p><b>Breakpoint</b><br/>'.$this->SettingBreakPoint.'</p>';
                $tempHtml  .= '<p><b>Colon list</b><br/>'.implode(',',$this->colonList).'</p>';
                $tempHtml  .= '<p><b>Linebreak</b><br/>'.$this->linebreak.'</p>';
                $tempHtml  .= '<p><b>Narrator list</b><br/>'.implode(',',$this->narratorList).'</p>';
                $tempHtml  .= '<p><b>Unused Palette Colors List</b><br/>'.implode(',',$this->colorsList).'</p>';
                $tempHtml  .= '<p><b>White List</b><br/>'.implode(',',$this->SettingWhitelistTag).'</p>';
                $tempHtml  .= '<p><b>Settings</b><br/>'.json_encode($this->settings).'</p>';
                $tempHtml  .= '</code></pre>';
            break;
            case '--show-raw-data':
                $tempHtml  .= '<pre><code id="devShowRawData">';
                $tempHtml  .= var_export($this->rawdata(), true);
                $tempHtml  .= '</code></pre>';
            break;
            case '--show-json':
                $tempHtml  .= '<pre><code id="devShowJson">';
                $tempHtml  .= $this->json();
                $tempHtml  .= '</code></pre>';
            break;
        }
        return $tempHtml;
    }
    public static function renderJs()
    {
        ob_start();
        require 'chatblock.js';
        return ob_get_clean();
    }
    public static function renderCss()
    {
        ob_start();
        require 'chatblock.css';
        // echo $this->dynamicCss();
        return ob_get_clean();
    }
    // Dynamic
    private function dynamicCss()
    {
        // $tempCss  = '';
        // $tempCss .= '.chatblock .imessage .chat-header {width: '.$this->settings->chatHeaderSize.';height: '.$this->settings->chatHeaderSize.';}';
        // return $tempCss;
    }
    // Multimedia
    private function render_imagecard_holder($dialogue)
    {
        $link = $this->fn_valid_link($dialogue['_context']);
        $url_components = parse_url($link);
        parse_str($url_components['query'], $params);
        $title = (isset($params['title'])?str_replace('+',' ',$params['title']):null);
        $desc = (isset($params['desc'])?str_replace('+',' ',$params['desc']):null);
        $tempHtml   = '';
        $tempHtml  .= '<div class="flip-card">';
        $tempHtml  .= '<div class="flip-card-inner">';
        $tempHtml  .= '<div class="flip-card-front">';
        $tempHtml  .= '<img src="'.$link.'" alt="imagecard" style="width:100%;height:100%;">';
        $tempHtml  .= '</div>';
        $tempHtml  .= '<div class="flip-card-back">';
        if($title)
        {
            $tempHtml  .= '<h1>'.$title.'</h1>';
        }
        if($desc)
        {
            $tempHtml  .= '<p>'.$desc.'</p>';
        }
        $tempHtml  .= '</div>';
        $tempHtml  .= '</div>';
        $tempHtml  .= '</div>';
        return $tempHtml;
    }
    private function render_rawdata($dialogue, $rawData)
    {
        $tempHtml  = '';
        $tempArray = [];
        if(isset($dialogue['_line']) && $dialogue['_line'] == '--show-data')
        { // Show all
            $minMaxVal = null;
            $minVal = 0; 
            $maxVal = 100; 
            $tempHtml  = '<pre><code>'.($rawData).'</code></pre>';
        }else{ // Show range
            $getLineNo = strstr($dialogue['_line'], ':');
            $getLineNo = ltrim($getLineNo, ':');
            $minMaxVal = explode(',',$getLineNo);
            if(isset($minMaxVal[0]) && isset($minMaxVal[1]))
            {
                $maxVal = ($minMaxVal[1] < $minMaxVal[0])? 100: $minMaxVal[1];
                $minVal = ($minMaxVal[0] < 0)? 0: $minMaxVal[0];
                $arrData = $this->dialogue['lines'];
                for($i = $minVal-1; $i < $maxVal; $i++)
                {
                    if(isset($arrData[$i]))
                    {
                        array_push($tempArray, ($i+1).' '.$arrData[$i]['_line']);
                    }
                }
                $tempArray = array_values($tempArray);
                $tempArray = implode('<br/>',array_values($tempArray));
                $tempHtml  = '<pre><code class="quoteCodeRange">'.nl2br($tempArray).'</code></pre>';
            }
        }
        return $tempHtml;
    }
    private function render_rawdata_full($dialogue, $rawData)
    {
        $ts = time();
        $tempHtml  = '<div id="chatblock-devtools" class="readingStory-changes well margin-top-2x padding-sm rawscript-chatblock-container">';
        $tempHtml .= '<hr/>';
        $tempHtml .= '<a class="btn btn-default btn-xs" data-toggle="collapse" data-target="#readingStory-changes-chatblock-'.$ts.'">Toggle Devtools</a>';
        if(isset($this->settings->allowForkScript))
        {
            $tempHtml .= '<div id="rawscript-chatblock-editor" class="rawscript-chatblock-editor">';
            $tempHtml .= '<form method="POST" target="_blank" action="'.$this->settings->allowForkScript.'">';
            $tempHtml .= '<button type="submit" class="btn btn-default btn-xs">Debug Scripts</button><br/>';
            $tempHtml .= '<textarea class="d-none" name="rawscript">'.$rawData.'</textarea>';
            $tempHtml .= '</div>';
            $tempHtml .= '</form>';
        }
        $tempHtml .= '<div id="readingStory-changes-chatblock-'.$ts.'" class="margin-top-lg collapse">';
            $tempHtml .= '<pre><code class="quoteCode">'.($rawData).'</code>';
            $tempHtml .= '<hr/>';
            $tempHtml .= $this->renderDev(null,'--info'); // debug 
            $tempHtml .= '<hr/>';
            $tempHtml .= $this->renderDev(null,'--show-json'); // debug
        $tempHtml .= '</div>';
        $tempHtml .= '</div>';

        return $tempHtml;
    }
    private function render_warningsblock($lines)
    {
        $tempHtml  = '<pre><code>Line "'.($lines).'" does not recognized.</code></pre>';
        return $tempHtml;
    }
    private function render_codeblock($dialogue)
    {
        $sentence  = ($dialogue['_line']);
        $tempHtml  = '<pre><code>'.$sentence.'</code></pre>';
        return $tempHtml;
    }
    private function render_reflink($dialogue)
    {
        $sentence  = ($dialogue['_context']);
        $tempArray = explode($this->SettingCommand,$sentence);
        $tempHtml  = '<div class="imessage">';
        $tempHtml .= '<p class="narrator">';
        $tempHtml .= '<img alt="svgImg" src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHg9IjBweCIgeT0iMHB4Igp3aWR0aD0iMjQiIGhlaWdodD0iMjQiCnZpZXdCb3g9IjAgMCAyNCAyNCIKc3R5bGU9IiBmaWxsOiMwMDAwMDA7Ij48cGF0aCBkPSJNIDUgMyBDIDMuOTA2OTM3MiAzIDMgMy45MDY5MzcyIDMgNSBMIDMgMTkgQyAzIDIwLjA5MzA2MyAzLjkwNjkzNzIgMjEgNSAyMSBMIDE5IDIxIEMgMjAuMDkzMDYzIDIxIDIxIDIwLjA5MzA2MyAyMSAxOSBMIDIxIDEyIEwgMTkgMTIgTCAxOSAxOSBMIDUgMTkgTCA1IDUgTCAxMiA1IEwgMTIgMyBMIDUgMyB6IE0gMTQgMyBMIDE0IDUgTCAxNy41ODU5MzggNSBMIDguMjkyOTY4OCAxNC4yOTI5NjkgTCA5LjcwNzAzMTIgMTUuNzA3MDMxIEwgMTkgNi40MTQwNjI1IEwgMTkgMTAgTCAyMSAxMCBMIDIxIDMgTCAxNCAzIHoiPjwvcGF0aD48L3N2Zz4="/>';
        $tempHtml .= '<a href="'.$tempArray[1].'" target="_blank">';
        $tempHtml .= '<b>'.$tempArray[0].'</b>';
        $tempHtml .= '</a>';
        $tempHtml .= '</p>';
        $tempHtml .= '</div>';
        return $tempHtml;
    }
    private function render_text($dialogue,$type)
    {
        if($type == 'p'){
            $sentence  = $this->fn_stripTags($dialogue['_context']);
        }elseif($type == 'sentence'){
            $sentence  = $this->fn_stripTags($dialogue['_line']);
        }
        $tempHtml  = '<div class="imessage">';
        $tempHtml .= '<p class="comment-full disable-select">'.$sentence.'</p>';
        $tempHtml .= '</div>';
        return $tempHtml;
    }
    private function render_heading($dialogue)
    {
        $sentence  = $this->fn_stripTags($dialogue['_context']);
        $tempHtml   = '<div class="imessage text-center disable-select">';
        $tempHtml  .= '<'.strtolower($dialogue['_castname']).'>';
        $tempHtml  .= $sentence;
        $tempHtml  .= '</'.strtolower($dialogue['_castname']).'>';
        $tempHtml  .= '</div>';
        return $tempHtml;
    }
    private function md_render_heading($dialogue,$headingLevel)
    {
        $sentence  = $this->fn_stripTags($dialogue['_context']);
        $tempHtml   = '<div class="imessage text-center disable-select">';
        $tempHtml  .= '<h'.$headingLevel.'>';
        $tempHtml  .= $sentence;
        $tempHtml  .= '</h'.$headingLevel.'>';
        $tempHtml  .= '</div>';
        return $tempHtml;
    }
    private function render_cutscene($dialogue)
    {
        $tempHtml  = '</section>';
        $tempHtml .= '<hr/>';
        if(isset($dialogue['_context']) && $dialogue['_context'] != '')
        {
            $tempHtml .= '<p class="text-center">'.$dialogue['_context'].'</p>';
            $tempHtml .= '<hr/>';
        }
        $tempHtml .= '<section class="vf-80">';
        return $tempHtml;
    }
    private function render_image_holder($dialogue)
    {
        $link = $this->fn_valid_link($dialogue['_context']);
        $tempHtml   = '<div class="container-image">';
        $extraClass = '';
        if($this->settings->extraImageClass)
        {
            $extraClass = $this->settings->extraImageClass;
        }
        $tempHtml  .= '<img src="'.$this->https.$link.'" class="'.$extraClass.'" alt="Image" style="width:100%;height:100%;">';
        $tempHtml  .= '</div>';
        return $tempHtml;
    }
    private function render_sound_holder($dialogue)
    {
        $link = $this->fn_valid_link($dialogue['_context']);
        $tempHtml   = '<div class="container-mp3">';
        $tempHtml  .= '<audio controls loop style="width:100%;">';
        $tempHtml  .= '<source src="'.$link.'" type="audio/mpeg">';
        $tempHtml  .= 'Your browser does not support the audio element.';
        $tempHtml  .= '</audio>';
        if($dialogue['_castname'] == 'Background')
        {
        $tempHtml  .= '<div class="text-muted text-bold text-center">背景循环音乐</div>';
        }
        $tempHtml  .= '</div>';
        return $tempHtml;
    }
    private function render_video_holder($dialogue)
    {
        $link = $this->fn_valid_link($dialogue['_context']);
        $tempHtml   = '<div class="container-youtube">';
        $tempHtml  .= '<iframe frameborder="0" width="100%" height="90%" src="'.$this->https.'//www.youtube.com/embed/'.$link.'"></iframe>';
        $tempHtml  .= '</div>';
        return $tempHtml;
    }
    private function render_decisions_holder($dialogue)
    {
        $paramItems = explode('=',$dialogue['_context']);
        $optionList = explode(',',$paramItems[1]);
        $tempHtml   = '<p class="text-center comment">'.$paramItems[0].'</p>';
        $tempHtml  .= '<div class="container-decision">';
        foreach($optionList as $option)
        {
            $tempHtml  .= '<div class="decision-option" data-choose="'.$option.'">'.$option.'</div>';
        }
        $tempHtml  .= '</div>';
        return $tempHtml;
    }
    // Misc
    private function fn_stripTags($dialogue)
    {
        // return ($dialogue); // Strip all tag
        return strip_tags($dialogue); // Strip all tag
    }
    private function fn_filter($dialogue)
    {
        $newStr = strip_tags($dialogue); // Strip all tag
        // Bold, Italic, Code, Delete - start
        $regex = '([*-_`])((?:(?!\1).)+)\1';
        preg_match_all("~$regex~", $newStr, $matches, PREG_SET_ORDER);
        foreach($matches as $set)
        {
            switch($set[1])
            {
                case '`': $tag = 'code'; break;
                case '-': $tag = 'del';  break;
                case '_': $tag = 'em';   break;
                case '*': $tag = 'b';    break;
                default:  $tag = null;   break;
            }
            if(!is_null($tag))
            {
                $newStr = str_replace($set[0], "<$tag>{$set[2]}</$tag>", $newStr);
            }
        }
        // Bold, Italic, Code, Delete - end
        // @,# - start
        $regex = '([\@\#])((?:\S(?!\S\1))+)'; // start with @ or #, end with whitespace, no whitespace in between
        preg_match_all("~$regex~", $newStr, $matches2, PREG_SET_ORDER);
        foreach($matches2 as $set2)
        {
            switch($set2[1])
            {
                case '@': $tag = 'cast';  break;
                case '#': $tag = 'topic'; break;
                default:  $tag = null;    break;
            }
            if(!is_null($tag))
            {
                $newStr = str_replace($set2[0], "<span class=\"chat-label chat-label-{$tag}\">{$set2[2]}</span>", $newStr);
            }
        }
        // @,# - end
        $newStr = str_replace($this->linebreak,'<br/>',$newStr); // Allow to multiples lines
        return trim($newStr);
    }
    private function fn_valid_link($dialogue)
    {
        return $dialogue;
        // $url = filter_var($dialogue, FILTER_SANITIZE_URL);
        // if (filter_var($url, FILTER_VALIDATE_URL)) {
        //   return $url;
        // }
        // return false;
    }
    // Chat Blocks
    private function role_narrator($dialogue)
    {
        $sentence  = $this->fn_stripTags($dialogue['_context']);
        $tempHtml  = '<div class="imessage">';
        $tempHtml .= '<p class="narrator disable-select">'.$sentence.'</p>';
        $tempHtml .= '</div>';
        return $tempHtml;
    }
    // New role side (left|right) - start
    private function renderRoleSide($dialogue,$direction='left',$color='#CCC')
    {
        if($direction == 'left')
        {
            $classDirection = 'them';
        }else{
            $classDirection = 'me';
        }
        // Normal
        $tempHtml  = '<div class="imessage">';
        if($this->currentCast !== $dialogue['_castname'])
        {
            $this->currentCast = $dialogue['_castname'];
            $tempHtml .= '<div class="chat-name chat-name-'.$classDirection.' disable-select">';
            $chatHeaderImg = $this->loadChatHeaderImg($dialogue['_castname']);
            if($chatHeaderImg == false)
            {
                $tempHtml .= '<b style="color:'.$color.'!important;">'.trim($dialogue['_castname']).'</b>';
            }else{
                switch($this->settings->chatHeaderSize)
                {
                    default:
                    case 'small':
                        $tempHtml .= '<img class="chat-header-s" src="'.$this->https.$this->loadChatHeaderImg($dialogue['_castname']).'">'.'<b style="color:'.$color.'!important;">'.trim($dialogue['_castname']).'</b>';
                    break;
                    case 'normal':
                        $tempHtml .= '<img class="chat-header" src="'.$this->https.$this->loadChatHeaderImg($dialogue['_castname']).'">'.'<b style="color:'.$color.'!important;">'.trim($dialogue['_castname']).'</b>';
                    break;
                    case 'large':
                        $tempHtml .= '<img class="chat-header-xl" src="'.$this->https.$this->loadChatHeaderImg($dialogue['_castname']).'">'.'<b style="color:'.$color.'!important;">'.trim($dialogue['_castname']).'</b>';
                    break;
                }
            }
            $tempHtml .= '</div>';
        }
        //
        if(preg_match('/'.$this->SettingCommand.'/i',$dialogue['_context'])) {
            $dataPath = strstr($dialogue['_context'], $this->SettingCommand);
            $dataPath = ltrim($dataPath, $this->SettingCommand);
            $ext = strstr($dialogue['_context'], $this->SettingCommand, true);
            $context  = '';
            switch($ext){
                case 'image':
                    $extraClass = '';
                    if($this->settings->extraImageClass)
                    {
                        $extraClass = $this->settings->extraImageClass;
                    }
                    $context  = '<img src="'.$dataPath.'" class="'.$extraClass.'" alt="Image" style="width:100%;height:100%;">';
                break;
                case 'mp3':
                    $context   = '<audio controls style="width:100%;min-width:300px;">';
                    $context  .= '<source src="'.$dataPath.'" type="audio/mpeg">';
                    $context  .= 'Your browser does not support the audio element.';
                    $context  .= '</audio>';
                break;
                case 'youtube':
                    $context  = '<iframe frameborder="0" width="100%" height="90%" src="'.$this->https.'//www.youtube.com/embed/'.$dataPath.'"></iframe>';
                break;
                default:
                    $context = ($dialogue['_context']);
                break;
            }
            $tempHtml .= '<p class="from-'.$classDirection.' disable-select" style="background-color:'.$color.'!important;">'.$context.'</p>';
        }else{
            $sentence = $this->fn_filter($dialogue['_context']);
            $tempHtml .= '<p class="from-'.$classDirection.' disable-select" style="background-color:'.$color.'!important;">'.$sentence.'</p>';
        }
        $tempHtml .= '</div>';
        return $tempHtml;
    }
    // New role side (left|right) - end
    private function loadCastColor($castName)
    {
        foreach($this->dialogue['casts'] as $cast)
        {
            if($cast['name'] == $castName)
            {
                if(isset($cast['color']))
                {
                    return $cast['color'];
                }
            }
        }
        return false; // If not match        
    }
    private function loadChatHeaderImg($castName)
    {
        foreach($this->dialogue['casts'] as $cast)
        {
            if($cast['name'] == $castName)
            {
                if(isset($cast['img']))
                {
                    return $cast['img'];
                }
            }
        }
        return false; // If not match
    }
    // https://stackoverflow.com/questions/18254566/file-get-contents-seems-to-add-extra-returns-to-the-data
    private function convertEOL($string, $to = "\n")
    {   
        return preg_replace("/\r\n|\r|\n/", $to, $string);
    }
    public function starttime() {
        $r = explode( ' ', microtime() );
        $r = $r[1] + $r[0];
        return $r;
    }
        
    public function endtime($starttime) {
        $r = explode( ' ', microtime() );
        $r = $r[1] + $r[0];
        $r = round($r - $starttime,4);
        return '<strong>Execution Time</strong>: '.$r.' seconds&nbsp;&nbsp;<br />';
    }
} // EOF
?>
