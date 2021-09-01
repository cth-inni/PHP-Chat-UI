<?php
require 'test.lib.php';
require 'vendor/autoload.php';
use \TangHoong\ChatBlock\ChatBlock as ChatUI;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Chatblock tutorial</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body>        
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
        }
        //
        // $cui = new ChatUI([
        //     ....
        // ]);
        // $cui->setDefaultChatImgHeader('/img/path.ext');
        // start from here
        $cui = new ChatUI();
        // Setting
        // $cui->setColon([':','：']);
        $cui->setColon(['：']);
        $cui->setNarrator(['Narrator','narrator','系统','旁白']);
        $cui->setBreakPoint('_EMANYAN_');
        echo sprintf('<style>%s</style>', $cui->renderCss());
        $cui->feed($sample);
        echo $cui->showCasts(); // For header introduction, header button
        echo $cui->showWarnings();
        echo $cui->render();


        // Output for other format
        // echo $cui->output();
        // ?>
        
    </body>
</html>