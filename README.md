# PHP Chat Block
A dialogue chat block display component for php project.
A UI component for conversational novel community.
Started Date: 30 Aug, 2021
Updated Date: 23 Oct, 2021
Author: Tang Hoong

# Description
Just dump the string format which exactly same as sample.txt then ChatBlock will render them into perfect and nice chat blocks.

# How
Follow the format from sample.txt:
```
A: I like apple.
B: I like apple too.
```
then
```
require __DIR__ ."/ChatBlock.php";
use TangHoong\ChatBlock\ChatBlock as ChatUI;

$cui = new ChatUI([
    'allowForkScript' => 'https://editor.chatnovel.app/',  // default:null
    'mainCastColor'  => '#198754',
    'castColorMode'  => 'palette', // none, random, palette 
    'chatHeaderSize' => 'large'  // default:normal,small,large
]);
$cui->setColon(['：',':']); // both
$cui->setNarrator(['系统','旁白','Narrator','narrator',]);
echo sprintf('<style>%s</style>', $cui->renderCss());
$content = str_replace(["\n"], ["\r\n"], $rawscript); // Depends If code on Window or Linux
if(!is_null($content))
{
  $cui->feed($content);
  echo $cui->render();
}
```

# Roadmap
- [] 顏文字
- [] emoji (studying)
- [] 文字轉語音 (studying)
- [] Sound clip(mp3 only)
- [] action button
- [] Invalid Image handle
- [] Change chat background (image, video)
- [] reverse stories (switch lines to major based on header)
- [] export format (txt,json,csv,custom)
- [] On click only show the next chat
- [] Personal Profile for each casts
- [] Blocks by Comment

# Resources refer from
[Codepen IMessage css](https://codepen.io/AllThingsSmitty/pen/jommGQ?editors=1000)  
[profile card](https://codepen.io/nicolaspavlotsky/pen/wqGgLO?editors=1100)  

# Changelog

## Oct 2021
- [x] Optimize the cast() positions
- [x] Optimize the feed() loop, solve seperator detection bug
- [x] Script showquote:5-10 (allow include the raw script in certain line)
- [x] Add chat block color (none, random, palette)
- [x] Allow bold, italic, code, underline, delete line in chat block
- [x] Add benchmark mstime
- [x] Allow `@name` & `#tag` inline
- [x] Allow fork script to live editor
- [x] Live editor

## Sep 2021
- [x] default narrator ['Narrator','narrator','系统','旁白'];
- [x] default colon [':','：'];
- [x] replace --- with _ADVANCE_
- [x] custom setBreakPoint('_I_LOVE_EMANYAN_')
- [x] setting template
- [x] Allow @, # in string
- [x] Added rawscript (will toggle use site class)
- [x] Fix p, h1-h6 container
- [x] Allow adjust head size
- [x] Show once header if the lines belongs to same person
- [x] Allow show unformatted line as warning messages (only works when single colon as index)
- [x] Allow export as json 
- [x] showCasts()

## Aug 2021
- [x] rename css file to chatblock.css
- [x] adding chatblock.js
- [x] H1-H6 tag
- [x] p tag
- [x] Fix narrator display
- [x] Conversation index (default as #)
- [x] minimal format (now can run without the characters settings)
- [x] linebreak
- [x] Directly wrote as paragraph will be ignore. (php error free)
- [x] adding 'rawscript#' for display the raw data before parse.
- [x] setColon() fixed
- [x] setNarrator() fixed
- [x] Colon array (default as #)
- [x] Narrator array (default as narrator)
- [x] stater template
- [x] dev tutorial （with sample）
- [x] allow easy mode use first line as main cast