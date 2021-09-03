<?php
namespace TangHoong\ChatBlock;

class ChatBlock
{
    public $rawData;
    public $colonList;
    public $narratorList;
    public $rolesList;
    public $roles;
    public $lines;
    public $dialogue;
    public $output;
    public $SettingBreakPoint;
    public $SettingWhitelistTag;
    public $settings;
    public $currentCast;
    function __construct($newObj=null)
    {
        // default
        $this->rawData = '';
        $this->currentCast = '';
        $this->rolesList = [];
        $this->SettingBreakPoint = "_ADVANCE_";
        $this->SettingWhitelistTag = [
            'p','h1','h2','h3','h4','h5','h6','linebreak',
            'image','imagecard','mp3','background','youtube','decision',
            'rawscript','rawscriptquote','codeblock','link',
            'narrator','profilecard',
        ];
        $this->colonList = [':','：'];
        $this->narratorList = ['Narrator','narrator','系统','旁白'];
        // default setting
        $oriObj = [
            'allowForkScript' => null,
            'chatHeaderSize'  => 'normal',
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
    public function feed($rawData='')
    {
        $this->rawData = $rawData;
        $chat['casts']       = [];
        $chat['lines']       = [];
        $chat['warnings']    = [];
        $rolesData = strstr($rawData, $this->SettingBreakPoint);
        $linesData = strstr($rawData, $this->SettingBreakPoint, true);
        if($rolesData != false)
        { // structure roles
            $rolesArray = array_values(array_filter(explode(PHP_EOL,$rolesData)));
            foreach($rolesArray as $roleKey => $roleVal)
            {
                $tempCast = [];
                $tempArray = explode("@",$roleVal);
                if(isset($tempArray) && count($tempArray) > 1)
                {
                    list($name, $img)  = $tempArray;
                    $tempCast['name']  = $name;
                    $tempCast['img']   = $img;
                    array_push($chat['casts'],$tempCast);
                    array_push($this->rolesList,$name);
                    array_push($this->SettingWhitelistTag,$name);
                }
            }
        }
        if($linesData != false)
        { // structure lines // reading image header settings, render with name + image
            $linesArray = array_values(array_filter(explode(PHP_EOL,$linesData)));
            foreach($linesArray as $lineKey => $lineVal)
            {
                if($lineVal != $this->SettingBreakPoint)
                {
                    $tempLine = [];
                    foreach($this->colonList as $colon)
                    {
                        $tempArray = explode($colon,$lineVal);
                        if(
                            isset($tempArray) && count($tempArray) > 1 
                            && (in_array($tempArray[0],$this->SettingWhitelistTag) || in_array($tempArray[0],$this->narratorList))
                        )
                        { // whitelisted
                            list($name, $sentence) = $tempArray;
                            $tempLine['name']  = $name;
                            $tempLine['sentence']   = $sentence;
                            array_push($chat['lines'],$tempLine);
                        }else
                        { // Lines that not match with standard, only work in single colon as index
                            if(!in_array($tempArray[0],$this->narratorList))
                            {
                                array_push($chat['warnings'],$tempArray[0]);
                            }
                        }
                    }// colon loop
                }
            }
        }else{ // structure lines // Without those image header settings, allow them to render by name only
            $linesArray = array_values(array_filter(explode(PHP_EOL,$rawData)));
            foreach($linesArray as $lineKey => $lineVal)
            {
                if($lineVal != $this->SettingBreakPoint)
                {
                    $tempLine = [];
                    foreach($this->colonList as $colon)
                    {
                        $tempArray = explode($colon,$lineVal);
                        if(isset($tempArray) && count($tempArray) > 1)
                        { // whitelisted
                            list($name, $sentence) = $tempArray;
                            $tempLine['name']  = $name;
                            $tempLine['sentence']   = $sentence;
                            array_push($chat['lines'],$tempLine);

                            if(!in_array($name,$this->narratorList))
                            { // Exclude narrator
                                array_push($this->rolesList,$name); // first line name as main cast
                                array_unique($this->rolesList);
                            }
                        }else
                        { // Lines that not match with standard, only work in single colon as index
                            if(!in_array($tempArray[0],$this->narratorList))
                            {
                                array_push($chat['warnings'],$tempArray[0]);
                            }
                        }
                    }
                }
            }
            $tempRoles = array_diff($this->rolesList, $this->SettingWhitelistTag);
            $tempRoles = array_values(array_unique($tempRoles));
            foreach($tempRoles as $tempRolesKey)
            {
                $tempCast = [];
                $tempCast['name']  = $tempRolesKey;
                $tempCast['img']   = null;
                array_push($chat['casts'], $tempCast);
            }
        }
        $this->dialogue = $chat;
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
     * Set Narrator
     */
    public function setNarrator($narratorArray = []){
        $this->narratorList = $narratorArray;
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
            $tempHtml .= '<div class="chat-name">';
            // $tempHtml .= '<img class="chat-header" src="'.$this->loadChatHeaderImg($cast['name']).'">'.$cast['name'];
            switch($this->settings->chatHeaderSize)
            {
                default:
                case 'small':
                    $tempHtml .= '<img class="chat-header-s" src="'.$this->loadChatHeaderImg($cast['name']).'">'.$cast['name'];
                break;
                case 'normal':
                    $tempHtml .= '<img class="chat-header" src="'.$this->loadChatHeaderImg($cast['name']).'">'.$cast['name'];
                break;
                case 'large':
                    $tempHtml .= '<img class="chat-header-xl" src="'.$this->loadChatHeaderImg($cast['name']).'">'.$cast['name'];
                break;
            }
            $tempHtml .= '</div>';
        }
        $tempHtml .= '</div>';
        $tempHtml .= '</div>';
        return $tempHtml;
    }
    /**
     * Using default html rendered chat blocks
     */
    public function render(){
        $tempHtml = '<div class="chatblock">';
        // foreach($this->dialogue['warnings'] as $line)
        // {
        //     $tempHtml .= $this->render_warningsblock($line);
        // }
        foreach($this->dialogue['lines'] as $dialogue)
        {
            switch($dialogue['name'])
            {
                case 'h1': 
                case 'h2': 
                case 'h3': 
                case 'h4': 
                case 'h5': 
                case 'h6': 
                    $tempHtml .= $this->render_heading($dialogue);
                break;
                case 'p': 
                    $tempHtml .= $this->render_text($dialogue);
                break;
                case 'rawscriptquote': 
                    $tempHtml .= $this->render_rawdata($dialogue,$this->rawData);
                break;
                case 'rawscript': 
                    $tempHtml .= $this->render_rawdata_full($dialogue,$this->rawData);
                break;
                case 'codeblock': 
                    $tempHtml .= $this->render_codeblock($dialogue);
                break;
                case 'linebreak': 
                    $tempHtml .= '<hr/>';
                break;
                case 'image': 
                    $tempHtml .= $this->render_image_holder($dialogue);
                break;
                case 'imagecard': 
                    $tempHtml .= $this->render_imagecard_holder($dialogue);
                break;
                case 'mp3': 
                case 'background': 
                    $tempHtml .= $this->render_sound_holder($dialogue);
                break;
                case 'youtube': 
                    $tempHtml .= $this->render_video_holder($dialogue);
                break;
                case 'decision': 
                    $tempHtml .= $this->render_decisions_holder($dialogue);
                break;
                // case 'narrator': 
                //     $tempHtml .= $this->role_narrator($dialogue);
                // break;
                default: 
                    if(in_array($dialogue['name'],$this->narratorList))
                    { // Custom narrator
                        $tempHtml .= $this->role_narrator($dialogue);
                    }else{
                        if( isset($this->rolesList[0]) && $this->rolesList[0] == $dialogue['name'])
                        { // maincast
                            $tempHtml .= $this->role_rightSide($dialogue);
                        }else{
                            $tempHtml .= $this->role_leftSide($dialogue);
                        }
                    }
                break;
            }
        }
        $tempHtml .= '</div>';
        $tempHtml .= '<hr/>';
        $tempHtml .= $this->render_rawdata_full(null,$this->rawData);
        // $this->output = $tempHtml;
        // return $this->output;
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
    /**
     * Recursively merges two objects and returns a resulting object.
     * @param object $obj1 The base object
     * @param object $obj2 The merge object
     * @return object The merged object
     */
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
        $link = $this->fn_valid_link($dialogue['sentence']);
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
        // $your_array = explode("\n", $rawData);
        // $arr = explode("\n", $your_array);
        $tempHtml  = '<pre><code>'.($rawData).'</code></pre>';
        return $tempHtml;
    }
    private function render_rawdata_full($dialogue, $rawData)
    {
        // $your_array = explode("\n", $rawData);
        // $arr = explode("\n", $your_array);
        $tempHtml  = '<div class="readingStory-changes well margin-top-2x padding-sm rawscript-chatblock-container">';
        $tempHtml .= '<a class="btn btn-default btn-xs" data-toggle="collapse" data-target="#readingStory-changes-chatblock">显示原始对话剧本</a>';
        if(isset($this->settings->allowForkScript))
        {
            $tempHtml .= '<div id="rawscript-chatblock-editor" class="rawscript-chatblock-editor">';
            $tempHtml .= '<form method="POST" target="_blank" action="'.$this->settings->allowForkScript.'">';
            $tempHtml .= '<button type="submit" class="btn btn-default btn-xs">拷贝对话剧本</button><br/>';
            $tempHtml .= '<textarea name="rawscript">'.$rawData.'</textarea>';
            $tempHtml .= '</div>';
            $tempHtml .= '</form>';
        }
        $tempHtml .= '<pre id="readingStory-changes-chatblock" class="margin-top-lg collapse"><code>'.($rawData).'</code></pre>';
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
        $sentence  = $this->fn_filter($dialogue['sentence']);
        // $sentence  = ($dialogue['sentence']);
        $tempHtml  = '<pre><code>'.$sentence.'</code></pre>';
        return $tempHtml;
    }
    private function render_text($dialogue)
    {
        $sentence  = $this->fn_filter($dialogue['sentence']);
        $tempHtml  = '<div class="imessage">';
        $tempHtml .= '<p class="comment">'.$sentence.'</p>';
        $tempHtml .= '</div>';
        return $tempHtml;
    }
    private function render_heading($dialogue)
    {
        $link = $this->fn_valid_link($dialogue['sentence']);
        $tempHtml   = '<div class="imessage">';
        $tempHtml  .= '<'.strtolower($dialogue['name']).'>';
        $tempHtml  .= $dialogue['sentence'];
        $tempHtml  .= '</'.strtolower($dialogue['name']).'>';
        $tempHtml  .= '</div>';
        return $tempHtml;
    }
    private function render_image_holder($dialogue)
    {
        $link = $this->fn_valid_link($dialogue['sentence']);
        $tempHtml   = '<div class="container-image">';
        $tempHtml  .= '<img src="'.$link.'" alt="Image" style="width:100%;height:100%;">';
        $tempHtml  .= '</div>';
        return $tempHtml;
    }
    private function render_sound_holder($dialogue)
    {
        $link = $this->fn_valid_link($dialogue['sentence']);
        $tempHtml   = '<div class="container-mp3">';
        $tempHtml  .= '<audio controls loop style="width:100%;">';
        $tempHtml  .= '<source src="'.$link.'" type="audio/mpeg">';
        $tempHtml  .= 'Your browser does not support the audio element.';
        $tempHtml  .= '</audio>';
        if($dialogue['name'] == 'Background')
        {
        $tempHtml  .= '<div class="text-muted text-bold text-center">背景循环音乐</div>';
        }
        $tempHtml  .= '</div>';
        return $tempHtml;
    }
    private function render_video_holder($dialogue)
    {
        $link = $this->fn_valid_link($dialogue['sentence']);
        $tempHtml   = '<div class="container-youtube">';
        $tempHtml  .= '<iframe frameborder="0" width="100%" height="90%" src="'.$link.'"></iframe>';
        $tempHtml  .= '</div>';
        return $tempHtml;
    }
    private function render_decisions_holder($dialogue)
    {
        $paramItems = explode('=',$dialogue['sentence']);
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
    private function fn_filter($dialogue)
    {
        $newStr = strip_tags($dialogue,"<b><i><u>");
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
        $sentence  = $this->fn_filter($dialogue['sentence']);
        $tempHtml  = '<div class="imessage">';
        $tempHtml .= '<p class="narrator">'.$sentence.'</p>';
        $tempHtml .= '</div>';
        return $tempHtml;
    }
    private function role_leftSide($dialogue)
    {
        $sentence = $this->fn_filter($dialogue['sentence']);
        $tempHtml  = '<div class="imessage">';
        if($this->currentCast !== $dialogue['name'])
        {
            $this->currentCast = $dialogue['name'];
            $tempHtml .= '<div class="chat-name chat-name-them">';
            $chatHeaderImg = $this->loadChatHeaderImg($dialogue['name']);
            if($chatHeaderImg == false)
            {
                $tempHtml .= $dialogue['name'];
            }else{
                switch($this->settings->chatHeaderSize)
                {
                    default:
                    case 'small':
                        $tempHtml .= '<img class="chat-header-s" src="'.$this->loadChatHeaderImg($dialogue['name']).'">'.$dialogue['name'];
                    break;
                    case 'normal':
                        $tempHtml .= '<img class="chat-header" src="'.$this->loadChatHeaderImg($dialogue['name']).'">'.$dialogue['name'];
                    break;
                    case 'large':
                        $tempHtml .= '<img class="chat-header-xl" src="'.$this->loadChatHeaderImg($dialogue['name']).'">'.$dialogue['name'];
                    break;
                }
            }
            $tempHtml .= '</div>';
        }
        $tempHtml .= '<p class="from-them">'.$sentence.'</p>';
        $tempHtml .= '</div>';
        return $tempHtml;
    }
    private function role_rightSide($dialogue)
    {
        $sentence  = $this->fn_filter($dialogue['sentence']);
        $tempHtml  = '<div class="imessage">';
        if($this->currentCast !== $dialogue['name'])
        {
            $this->currentCast = $dialogue['name'];
            $tempHtml .= '<div class="chat-name chat-name-me">';
            $chatHeaderImg = $this->loadChatHeaderImg($dialogue['name']);
            if($chatHeaderImg == false)
            {
                $tempHtml .= $dialogue['name'];
            }else{
                switch($this->settings->chatHeaderSize)
                {
                    default:
                    case 'small':
                        $tempHtml .= '<img class="chat-header-s" src="'.$this->loadChatHeaderImg($dialogue['name']).'">'.$dialogue['name'];
                    break;
                    case 'normal':
                        $tempHtml .= '<img class="chat-header" src="'.$this->loadChatHeaderImg($dialogue['name']).'">'.$dialogue['name'];
                    break;
                    case 'large':
                        $tempHtml .= '<img class="chat-header-xl" src="'.$this->loadChatHeaderImg($dialogue['name']).'">'.$dialogue['name'];
                    break;
                }
            }
            $tempHtml .= '</div>';
        }
        $tempHtml .= '<p class="from-me">'.$sentence.'</p>';
        $tempHtml .= '</div>';
        return $tempHtml;
    }
    private function loadChatHeaderImg($castName)
    {
        foreach($this->dialogue['casts'] as $cast)
        {
            if($cast['name'] == $castName)
            {
                return $cast['img'];
            }
        }
        return false; // If not match
    }
} // EOF
?>
