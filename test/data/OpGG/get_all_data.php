<?php
/**
 * Grabs a snapshot of data from http://na.op.gg. For use in mock testing. Do
 * not spam this script! It will send n^2 requests at op.gg!
 */

$ch = curl_init();

$stats = [ 'win', 'lose', 'picked', 'banned' ];

$leagues = [
	'all'        => '',
	'bronze'     => 'bronze',
	'silver'     => 'silver',
	'gold'       => 'gold',
	'platinum'   => 'platinum',
	'diamond'    => 'diamond',
	'master'     => 'master',
	'master'     => 'master',
	'challenger' => 'challenger',
];
curl_setopt_array($ch, [
	CURLOPT_URL => 'http://na.op.gg/statistics/ajax2/champion/',
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_HTTPHEADER => [
		'Accept: */*',
		'Accept-Encoding: gzip, deflate',
		'Accept-Language: en-US,en;q=0.9',
		'Connection: keep-alive',
		'Content-Length: 50',
		'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
		'Cookie: customLocale=en_US; _hist=Shiphtur%24Szyx%20x%24Doublelift%24ArzelFallen%24BlazingWill%24Devin%20May%20Cry%24LL%20Stylish%24Ze%20Lump',
		'Host: na.op.gg',
		'Origin: http://na.op.gg',
		'Referer: http://na.op.gg/statistics/champion/',
		'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
		'X-NewRelic-ID: VQcEVlFSDBAHXFNWDgMBVg==',
		'X-Requested-With: XMLHttpRequest	',
	],
]);

$post_data = [
	'type'   => '',
	'league' => '',
	'period' => 'month',
	'mapId'  => '1',
	'queue'  => 'ranked',
];

foreach ($stats as $type) {
	$post_data['type'] = $type;
	if (!file_exists(__DIR__ . "/$type")) mkdir(__DIR__ . "/$type");
	foreach ($leagues as $folder => $league) {
		$path = __DIR__ . "/$type/$folder";
		if (!file_exists($path)) mkdir($path);
		
		$post_data['league'] = $league;
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));

		$response = curl_exec($ch);
		sleep(2); // being polite to their servers

		if (!curl_error($ch)) {
			file_put_contents("$path/data.html", $response);
		}
		else {
			throw new \Exception("curl error: " . curl_error());
		}
	}
}