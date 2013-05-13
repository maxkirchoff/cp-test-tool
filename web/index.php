<?php
$parent_dir = dirname(__DIR__);

$browscap_file = $parent_dir . "/vendor/browscap/Browscap.php";
require_once $browscap_file;

$db_file = $parent_dir . "/src/db.php";
require_once $db_file;
use NightStalker\PdoDriver;

$conf_file = $parent_dir . "/src/conf/conf.php";
$config = include $conf_file;

if (isset($_GET['campaign']) && in_array($_GET['campaign'], $config['known_campaigns']))
{
        $campaign = $_GET['campaign'];
}
else
{
    echo "404 Homie.";
    die();
}

$cache_location = sys_get_temp_dir() . '/browscap_cache';

if (! file_exists($cache_location))
{
    mkdir($cache_location);
}

$bc = new Browscap($cache_location);

// grab an array, easier to deal with
$current_browser = $bc->getBrowser(null, true);

$current_browser = lowercase_everything($current_browser);

if (isset($current_browser['platform']) && isset($current_browser['browser']))
{
    switch (strtolower($current_browser['platform']))
    {
        case 'ios':
            $browser_stack = 'ios-' . $current_browser['browser'];
            if (strpos($current_browser['browser_name'], 'fbios'))
            {
                $browser_stack = $browser_stack . '-facebook';
            }
            break;
        case 'android':
            $browser_stack = 'android-' . $current_browser['browser'];
            break;
        default:
            $browser_stack = 'desktop-' . $current_browser['browser'];
            break;
    }
}
else
{
    $browser_stack = 'unknown';
}

if (array_key_exists($browser_stack, $config['pixels']))
{
    $pixel = $config['pixels'][$browser_stack];
}
else
{
    $pixel = $config['pixels']['unknown'];
}

PdoDriver::init($config);
$pdo_driver = new \NightStalker\PdoDriver();

$insert_sql = "INSERT INTO visits (campaign,user_agent,browser_stack,pixel_used,referer,server_var,created)
                VALUES (:campaign,:user_agent,:browser_stack,:pixel_used,:referer,:server_var,:created)";
$insert_query = $pdo_driver->handle()->prepare($insert_sql);

$dt = new DateTime('now', new DateTimeZone('America/Los_Angeles'));
$created = $dt->format('Y-m-d H:i:s');

$params = array(
    ':campaign' => $campaign,
    ':user_agent' => json_encode($current_browser),
    ':referer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null,
    ':server_var' => json_encode($_SERVER),
    ':browser_stack' => $browser_stack,
    ':pixel_used' => $pixel,
    ':created' => $created
);

$insert_query->execute($params);

function lowercase_everything(array $current_browser)
{
    // lower some stuffs
    $current_browser = array_change_key_case($current_browser, CASE_LOWER);

    // Lower case all the first level values
    foreach ($current_browser as &$value)
    {
        // if it's not a string IDGAF right now
        if (is_string($value))
        {
            $value = strtolower($value);
        }
    }

    return $current_browser;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $campaign; ?> Campaign!</title>
    <script type="text/javascript">
        var fb_param = {};
        fb_param.pixel_id = '<?php echo $pixel; ?>';
        fb_param.value = '0.00';
        (function(){
            var fpw = document.createElement('script');
            fpw.async = true;
            fpw.src = '//connect.facebook.net/en_US/fp.js';
            var ref = document.getElementsByTagName('script')[0];
            ref.parentNode.insertBefore(fpw, ref);
        })();
    </script>
    <noscript><img height="1" width="1" alt="" style="display:none" src="https://www.facebook.com/offsite_event.php?id=<?php echo $pixel; ?>&amp;value=0" /></noscript>
    <meta name="viewport" content="width='400px', initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
</head>
<body>
<div style="margin: 200px, auto; width: 400px; text-align: center">
    <h2>OMG Tacos are so hard to eat!</h2>
    <p>
        <img src='img/taco.gif' />
    </p>
    <p>
        Your Campaign: <?php echo $campaign; ?>
    </p>
    <p>
        Your Browser Stack: <?php echo $browser_stack; ?>
    </p>
    <p>
        Your Pixel: <?php echo $pixel; ?>
    </p>
</div>
</body>