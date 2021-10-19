<?php
// require 'test.lib.php';
require 'vendor/autoload.php';
use \TangHoong\ChatBlock\ChatBlock as ChatUI;
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
        <title>Chatblock tutorial</title>
        <meta name="description" content="">
        <style>
            body{
                margin: 0;
                padding: 0;
                background:#eee;
            }
            .chatblock {
                padding: 20px;
            }
        </style>
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
            case '107': 
                $sample = file_get_contents('./sample/story.2.1.txt');
            break;
            case 'youfeng': 
                $sample = file_get_contents('./sample/youfeng.txt');
            break;
            case 'links': 
                $sample = file_get_contents('./sample/links.txt');
            case '108': 
                $sample = file_get_contents('./sample/test.raw.2.txt');
            break;
            case 'report': 
                $sample = file_get_contents('./sample/report.txt');
            break;
        }
        //
        // start from here
        // $cui = new ChatUI();
        $cui = new ChatUI([
            'allowForkScript' => 'https://editor.chatnovel.app/',  // default:null
            'mainCastColor'  => '#198754',
            'castColorMode'  => 'palette', // none, random, palette 
            'chatHeaderSize' => 'large'  // default:normal ,small, large
        ]);
        // $cui->setColon([':']); // en, default
        $cui->setColon(['：']); // zh
        $cui->setNarrator(['Narrator','narrator','系统','旁白']);
        echo sprintf('<style>%s</style>', $cui->renderCss());
        $cui->feed($sample);
        echo $cui->render();
        ?>
        <style>
        /* .rawscript-chatblock-container{
            height: 30px;
        } */
        .rawscript-chatblock-editor button{
            display: block;  
        }
        </style>
        <script async defer>
            /* Ignore below, no copy needed */
            function toggleHeight() {
                var rawscriptContainer = document.querySelector(".rawscript-chatblock-container"); 
                var rawscriptContainerToggle = document.querySelector(".rawscript-chatblock-container a"); 
                var toggleStatus = rawscriptContainerToggle.dataset.toggle;
                if( rawscriptContainerToggle.dataset.toggle == 'collapse' )
                {
                    rawscriptContainerToggle.dataset.toggle = 'expand';
                    rawscriptContainer.style.height = '100%';
                }else{
                    rawscriptContainerToggle.dataset.toggle = 'collapse';
                    rawscriptContainer.style.height = '30px';
                }
            }
            document.addEventListener('DOMContentLoaded', function() {
                var rawscriptContainerToggle = document.querySelector(".rawscript-chatblock-container a"); 
                rawscriptContainerToggle.addEventListener("click", toggleHeight, false);
            });
        </script>
    </body>
</html>