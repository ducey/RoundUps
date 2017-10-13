<?php

// Set your authorization header with your Starling Developer token
$authorization = "Authorization: Bearer {starling_developer_token}";

// Set your IFTTT WebHooks id:
$webhooksId = "{ifttt_webhooks_id}";

// Set the from and to dates to be the first and last days of previous month
$month_start = new DateTime("first day of last month");
$month_end = new DateTime("last day of last month");

// Get transactions from Starling Bank
$process = curl_init("https://api.starlingbank.com/api/v1/transactions?from=" . $month_start->format('Y-m-d') . "&to=" . $month_end->format('Y-m-d'));
curl_setopt($process, CURLOPT_HTTPHEADER, array(
    $authorization)                                                           
);
curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
$starling = curl_exec($process);
curl_close($process);
$starlingTransResp = json_decode($starling);
$starlingTrans = $starlingTransResp->{'_embedded'}->{'transactions'};
$starlingTransOut = "";
$i = 1;
foreach($starlingTrans as $tran) {

    $amnt = abs(number_format((float)$tran->{'amount'}, 2, '.', ''));

    $diff = ceil($amnt) - $amnt;
    $starlingTotalRoundUps += $diff;

}

$output = array(
    "round_ups" => number_format((float)$starlingTotalRoundUps, 2, '.', '')
);

// Optional: You can now make a call to the IFTTT Webhooks. Note: You will need to change the {event_id} here to match what you entered in the IFTTT Webhooks config.
$process = curl_init("https://maker.ifttt.com/trigger/{event_id}/with/key/" . $webhooksId . "?value1=".
number_format((float)$starlingTotalRoundUps, 2, '.', ''));
$ifttt = curl_exec($process);
curl_close($process);


// Optional: You can either output JSON
// echo json_encode($output);


$rssfeed = '<?xml version="1.0" encoding="ISO-8859-1"?>';
    $rssfeed .= '<rss version="2.0">';
    $rssfeed .= '<channel>';
    $rssfeed .= '<title>RoundUps</title>';
    $rssfeed .= '<link>https://www.yourwebsite.com</link>';
    $rssfeed .= '<description>This is an example RSS feed</description>';
    $rssfeed .= '<language>en-gb</language>';
    $rssfeed .= '<copyright>Copyright (C) 2017 your website.com</copyright>';
    $rssfeed .= '<item>';
    $rssfeed .= '<title>' . number_format((float)$starlingTotalRoundUps, 2, '.', '') .'</title>';
    $rssfeed .= '<description>' . number_format((float)$starlingTotalRoundUps, 2, '.', '') . '</description>';
    $rssfeed .= '<link>https://www.yourwebsite.com</link>';
    $rssfeed .= '<pubDate>' . $month_end->format("D, d M Y H:i:s O") . '</pubDate>';
    $rssfeed .= '<guid>0</guid>';
    $rssfeed .= '</item>';
    $rssfeed .= '</channel>';
    $rssfeed .= '</rss>';

// Optional: You can also output in RSS feed format. This will need a bit of tweaking to add a new item for each month to make the IFTTT RSS Feed trigger work.
// echo $rssfeed;


?>







