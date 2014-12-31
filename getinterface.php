 <script src="intranet/js/jquery-2.1.3.min.js" type="text/javascript"></script>

<?php ## Interface Stats
    $output = exec('ifstat' . ' -q -i ' . 'eth0'. ' 0.1 1');
    $output = preg_replace('/\s+/', ' ', $output);
    echo 'DOWN: ' . str_replace(' ', 'Kbps   UP: ', trim($output)) . 'Kbps' . PHP_EOL;
?>

