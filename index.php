<?php
# --- RentCafe RSS ---
#
# Simple PHP script that generates a valid RSS feed
# for the property management bulletin board
# used by Yardi RentCafe properties

# Load settings from this file
require_once('config.php');

function initError($msg) {
	# IE will not show the custom error message if less than 512 bytes,
	# so don't respond with 500 to IE
	if (!strstr($_SERVER['HTTP_USER_AGENT'], "Trident")) {
		http_response_code(500);
	}
	header("Content-Type: text/html");
	die("<html><body>$msg</body></html>");
}

if (!isset($propertyManagementName, $baseDomain, $baseURL, $username, $password, $cfClearanceCookie, $userAgent, $curl, $cacheSeconds, $cookieFile, $htmlFile)) {
	initError("One or more mandatory settings is not set, please check your config.php");
}
if (!file_exists($curl)) {
	initError("Missing path to curl-impersonate-chrome, download from https://github.com/lwthiker/curl-impersonate and configure in config.php");
}

# Step 1: Extract an auth token
if (!file_exists($htmlFile) || (filemtime($htmlFile) < (time() - $cacheSeconds))) {
	shell_exec("echo '' > $cookieFile"); # Reset cookies (ftruncate does not take a filename)

	$url = "$baseURL/userlogin?ReturnUrl=%2Fresidentservices%2F$propertyManagementName%2Fdashboard";
	$cmd = "$curl -c $cookieFile '$url' -H 'authority: $baseDomain' -H 'user-agent: $userAgent'";
	$html = shell_exec($cmd);

	if ($html === false) {
		http_response_code(500);
		die("Couldn't curl webpage");
	}

	$auth = strstr($html, "__RequestVerificationToken");
	if (!$auth) {
		http_response_code(500);
		file_put_contents("/var/www/html/rentcafe/html.html", $html);
		die("Couldn't find RequestVerificationToken");
	}
	$auth = strstr($auth, "value");
	/* Skip value=" */
	/* Tokens are 155 chars long */
	$auth = substr($auth, 7, 155); # This is the auth token to use when authenticating
	if ($debug) {
		error_log("Captured auth token from HTML: $auth", 0);
	}

	# Step 2: Login
	$url = "$baseURL/userlogin?handler=Login";
	# The cf_clearance cookie is good for 1 year.
	#
	$cmd = "$curl -L -b $cookieFile -c $cookieFile '$url' -H 'authority: $baseDomain' -H 'origin: https://$baseDomain' -H 'user-agent: $userAgent' -H 'cookie: cf_clearance=$cfClearanceCookie' --data-raw 'Email=$username&Password=$password&__RequestVerificationToken=$auth'";
	$html = shell_exec($cmd);

	if ($html == false) {
		die("Stage 2 failed: $cmd");
	}

	# Step 3: Load the dashboard page

	$url = "$baseURL/dashboard";
	$cmd = "$curl -L -b $cookieFile '$url' -H 'authority: $baseDomain' -H 'origin: https://$baseDomain' -H 'user-agent: $userAgent' -H 'referer: $baseURL/userlogin?ReturnUrl=%2Fresidentservices%2F$propertyManagementName%2Fdashboard' -H 'cookie: cf_clearance=$cfClearanceCookie'";
	$html = shell_exec($cmd);

	if ($html == false) {
		die("Stage 3 failed: $cmd");
	}
	if (!strstr($html, "__RequestVerificationToken")) {
		die("Token missing in final!");
	}
	file_put_contents($htmlFile, $html);
} else {
	# Load cached file
	$html = file_get_contents($htmlFile);
}

$html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);

libxml_use_internal_errors(true);
$doc = new DOMDocument();
$doc->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
$body_elements = $doc->getElementsByTagName('body');
$body = $body_elements->item(0);
$bb = $doc->getElementById('bb-container'); # 1 level deeper than #bulletinBoardSection
$ul = $bb->getElementsByTagName('ul')[0];
$li_list = $ul->getElementsByTagName('li');
?>
<?xml version="1.0" encoding="UTF-8" ?><rss version="2.0"><channel>
<title><?php echo $propertyName; ?> Bulletin Board</title>
<link><?php echo $baseURL; ?></link>
<description>Recent postings from your neighbors!</description>
<language>en-us</language>
<lastBuildDate><?php echo date('r'); ?></lastBuildDate>
<pubDate><?php echo date('r'); ?></pubDate>
<?php
foreach ($li_list as $li) {
	# This li has id postSection_XXXXXXX and class post-item
	$card = $li->getElementsByTagName('div')[0]; # Only div, has class card]
	$cardBody = $card->getElementsByTagName('div')[0];
	$dFlex = $cardBody->getElementsByTagName('div')[0];
	$dFlexDivs = $dFlex->getElementsByTagName('div');
	# user-avatar class is element 0 (we ignore this). user-info is element 1.
	if (count($dFlexDivs) < 2) {
		# This can happen... but we don't actually lose anything in this case...
		continue;
	}
	$userInfo = $dFlexDivs[1];
	$userDivs = $userInfo->getElementsByTagName('div');
	# div 0: contains author name and post date
	# div 1: contains post body
	# div 2: contains comment form (which we ignore)
	# div 3: contains replies (which we ignore)
	# For some reason, though, div 1 shows up at index 3
	$metadata = $userDivs[0];

	# inside metadata, first div contains author name, second contains date
	$metadataDivs = $metadata->getElementsByTagName('div');
	$authorName = $metadataDivs[0]->getElementsByTagName('span')[0]->textContent;
	$postDate = $metadataDivs[1]->getElementsByTagName('span')[0]->textContent;
	$bodyData = $userDivs[3];
	$bodyText = $bodyData->getElementsByTagName('p')[0]->nodeValue;
	#echo "$authorName / $postDate / $bodyText <br>";
	echo "<item>";
	echo "\t<title>" . htmlspecialchars($authorName, ENT_QUOTES | ENT_SUBSTITUTE) . "</title>";
	echo "\t<description><![CDATA[ " . htmlspecialchars($bodyText, ENT_QUOTES | ENT_SUBSTITUTE) . " ]]></description>";
	$date = strtotime($postDate);
	# Since we don't have a time, just the date, add 10 hours so that the date is correct when not in UTC
	$date += (3600 * 10);
	echo "\t<pubDate>" . date('r', $date) . "</pubDate>";
	echo "</item>";
}
?>
</channel></rss>