<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <title>WPN nano control panel</title>
        <meta charset="uft-8">
        <link rel="stylesheet" href="/style.css" type="text/css">
    </head>

    <body>
        <h1>WPN nano control panel</h1> 
    <?php

        $parsed_url = parse_url($_SERVER['REQUEST_URI']);

        define('WPNXM_DIR', __DIR__ . '/../..');

        error_reporting(E_ERROR);
        //error_reporting(E_ALL ^ E_NOTICE);


        // slipt by /
        $parts=  explode("/", $parsed_url["path"]); 
        array_shift($parts); // remove empty first part
        
        //first path is daemon name, second path is "start"/"stop"
        if (isset($parts[0]) && isset($parts[1])){
          if ($parts[1]=="start"){
              startDaemon($parts[0]);
          }elseif ($parts[1]=="stop"){
              stopDaemon($parts[0]);
          }
        };

        //construct status page
        echo '<div id=daemons class=daemons>';
        
        //get running processes
        $proceses = shell_exec(WPNXM_DIR . '\bin\tools\process.exe');

        $daemons = (object)[
                "nginx"=>"Nginx"
                ,"php-cgi"=>"PHP"
                ,"mysqld"=>"MariaDB"
                ,"memcached"=>"MemCached"
            ];
        
        foreach ($daemons as $daemon => $title) {
            $isRunning = (strpos($proceses, $daemon) !== false);
            echo '<div class=daemon>';
            echo "<div class=daemon-name>$title</div>"; 
            echo '<div class=status><div class="indicator '. ($isRunning? 'running':'stopped') . '"></div></div>'; 
            echo '<div class=control>';
            echo '<a class="stop ' . ($isRunning?'enabled':'disabled'). '" href="/' . $daemon . '/stop">stop</a>';
            echo '<a class="start ' . ($isRunning?'disabled':'enabled'). '" href="/' . $daemon . '/start">start</a>';
            echo '</div></div>'; //close control, close daemon
        }
        
        echo '</div>'; //close daemons
        echo '<a class="refresh" href="/">refresh</a>';
        
        //debug
        //echo "<pre>$proceses</pre>";

//-----------------
//helper functions
//-----------------        

    function startDaemon($daemon, $options = '')
    {
        $console = WPNXM_DIR . '\bin\tools\RunHiddenConsole.exe ';

        switch ($daemon) {
            case 'nginx':
                $nginx_folder = WPNXM_DIR . '\bin\nginx';
                chdir($nginx_folder); //requierd for nginx
                exec("start $console nginx.exe $options"); 
                break;
            
            case 'php-cgi':
                $folder = WPNXM_DIR . '\bin\php';
                chdir($folder); //requierd for nginx
                exec("start $console php-cgi.exe -b localhost:9100 $options"); 
                break;
            
            case 'mysqld':
                $mysqld_folder = WPNXM_DIR . '\bin\mariadb\bin';
                chdir($mysqld_folder); //change to folder
                exec("start $console mysqld.exe $options"); 
                break;
            
            case 'memcached':
                $memcached_daemon = WPNXM_DIR . '\bin\memcached\memcached.exe ';
                exec($console . $memcached_daemon . $options);
                break;
            
            default:
                throw new \InvalidArgumentException(
                    sprintf(__METHOD__. '() has no command for the daemon: "%s"', $daemon)
                );
        }
    }

    function stopDaemon($daemon)
    {
        $console = WPNXM_DIR . '\bin\tools\RunHiddenConsole.exe ';
        $process_kill = WPNXM_DIR . '\bin\tools\Process.exe -k  ';

        switch ($daemon) {
            case 'nginx':
            case 'mysqld':
            case 'memcached':
            case 'php-cgi':
                exec($console . $process_kill . $daemon . '.exe');
                break;
            default:
                throw new \InvalidArgumentException(
                    sprintf(__METHOD__. '() has no command for the daemon: "%s"', $daemon)
                );
        }
    }
    ?>
        
        <script>
            window.onbeforeunload = function(){
                document.getElementById('daemons').style.opacity=.5;
            };
        </script>
        
    </body>
</html>



            