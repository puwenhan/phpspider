<?php

include "phpspider/config.php";
include "phpspider/worker.php";
include "phpspider/rolling_curl.php";
include "phpspider/db.php";
include "phpspider/cache.php";
include "phpspider/cls_query.php";
include "user.php";
include "phpspider/cls_curl.php";

$cookie = trim(file_get_contents("cookie.txt"));
/*
$curl = new rolling_curl();
$curl->set_cookie($cookie);
$curl->set_gzip(true);
$curl->callback = function($response, $info, $request, $error) {

    echo 'callback'.PHP_EOL;
    preg_match("@http://www.zhihu.com/people/(.*?)/about@i", $request['url'], $out);
    $username = $out[1];
    if (empty($response)) 
    {
        file_put_contents("./timeout/".$username."_info.json", json_encode($info)."\n", FILE_APPEND);
        file_put_contents("./timeout/".$username."_error.json", json_encode($error)."\n", FILE_APPEND);
    }
    else 
    {
        $data = get_user_about($response);
        if (empty($data)) 
        {
            file_put_contents("./timeout_data.txt", $request['url']."\n", FILE_APPEND);
        }
        else 
        {
            file_put_contents("./html/".$username.".json", json_encode($data));
        }
    }

};
for ($i = 0; $i < 5; $i++) 
{
    $username = get_user_queue();
    echo $username.PHP_EOL;
    $username = addslashes($username);
    $url = "http://www.zhihu.com/people/{$username}/about";
    $curl->get($url);
}
$data = $curl->execute();
echo $data.PHP_EOL;
exit;
*/

$w = new worker();
$w->count = 2;
$w->is_once = true;
$w->log_show = false;

$count = 100;        // 每个进程循环多少次
$w->on_worker_start = function($worker) use ($count) {

    //echo $worker->worker_pid . " --- " . $worker->worker_id."\n";
    $cookie = trim(file_get_contents("cookie.txt"));

    $curl = new rolling_curl();
    $curl->set_cookie($cookie);
    $curl->set_gzip(true);
    $curl->callback = function($response, $info, $request, $error) {

        preg_match("@http://www.zhihu.com/people/(.*?)/about@i", $request['url'], $out);
        $username = $out[1];
        if (empty($response)) 
        {
            var_dump($info);
            file_put_contents("./timeout/".$username."_info.json", json_encode($info)."\n", FILE_APPEND);
            file_put_contents("./timeout/".$username."_error.json", json_encode($error)."\n", FILE_APPEND);
        }
        else 
        {
            $data = get_user_about($response);
            if (empty($data)) 
            {
                file_put_contents("./timeout_data.txt", $request['url']."\n", FILE_APPEND);
            }
            else 
            {
                preg_match("@http://www.zhihu.com/people/(.*?)/about@i", $request['url'], $out);
                file_put_contents("./html/".$out[1].".json", json_encode($data));
            }
        }

    };

    for ($i = 0; $i < $count; $i++) 
    {
        $username = get_user_queue();
        $username = addslashes($username);
        $url = "http://www.zhihu.com/people/{$username}/about";
        $curl->get($url);
        $data = $curl->execute();
    }
}; 

$w->run();

