<?php
require 'test.lib.php';
require 'vendor/autoload.php';
use \TangHoong\ChatBlock\ChatBlock as ChatUI;
?>     
<?php
// Sources
$page = @$_GET['page'];
switch($page)
{
default:
$sample = file_get_contents('./tutorial/0-toc.txt');
break;
case '1': 
$sample = file_get_contents('./tutorial/1-basic.txt');
break;
case '2': 
$sample = file_get_contents('./tutorial/2-all-commands.txt');
break;
case '3': 
$sample = file_get_contents('./tutorial/3-advance.txt');
break;
case '4': 
$sample = file_get_contents('./tutorial/4-creative.txt');
break;
case '5': 
$sample = file_get_contents('./tutorial/5-tell-a-stories.txt');
break;
case '6': 
$sample = file_get_contents('./tutorial/6-starter.txt');
break;
case '99': 
$sample = getAPIData(5);
var_dump($sample);
break;

// Dev sample stories
case '100': 
$sample = file_get_contents('./sample/sample.txt');
break;
case '101': 
$sample = file_get_contents('./sample/emanyan.1.txt');
break;
case '102': 
$sample = file_get_contents('./sample/sample.2.txt');
break;
case '103': 
$sample = file_get_contents('./sample/sample.custom.colon.txt');
break;
case '104': 
$sample = file_get_contents('./sample/sep.1.txt');
break;
case '105': 
$sample = file_get_contents('./sample/starter.template.txt');
break;
case '106': 
$sample = file_get_contents('./sample/test.raw.txt');
break;
case '107': 
$sample = file_get_contents('./sample/story.2.1.txt');
break;
}
//
header('Content-Type: application/json');  // <-- header declaration
$cui = new ChatUI();
$cui->setColon(['：']);
$cui->setNarrator(['Narrator','narrator','系统','旁白']);
$cui->setBreakPoint('_I_LOVE_EMANYAN_');
$cui->feed($sample);
echo $cui->json();
?>
