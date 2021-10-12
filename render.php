<?php
// require 'test.lib.php';
require 'vendor/autoload.php';
use \TangHoong\ChatBlock\ChatBlock as ChatUI;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Preview</title>
<meta name="description" content="">
</head>
<body>

<?php
// Sources
$rawscript = @$_POST['rawscript'];
$rawscript = nl2br($rawscript);
$breaks = array("<br />","<br>","<br/>");  
// $rawscript = str_ireplace($breaks, "\r\n", $rawscript); // Linux
$rawscript = str_ireplace($breaks, "\r", $rawscript); // Window
// $rawscript = file_get_contents('./sample/test.raw.txt'); // OK
// var_dump($rawscript);
// print_r($rawscript);
$cui = new ChatUI([
'allowForkScript' => 'https://editor.chatnovel.app/',  // default:null
// 'allowForkScript' => 'editor.php',  // default:null
'chatHeaderSize' => 'large' // default:normal,small,large
]);
$cui->setColon(['：']);
$cui->setNarrator(['Narrator','narrator','系统','旁白']);
// $cui->setBreakPoint('_I_LOVE_EMANYAN_');
echo sprintf('<style>%s</style>', $cui->renderCss());
$cui->feed($rawscript);
echo $cui->showWarnings();
// echo $cui->showCasts(); // For header introduction, header button
echo $cui->render();
?>
</body>
</html>