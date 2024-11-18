<?php
# Property Identifiers
$propertyManagementName = "foo-bar-apartments"; # Retrieve from property management website
$baseDomain = "foobarapartments.securecafenet.com"; # Retrieve from property management website
$baseURL = "https://$baseDomain/residentservices/foo-bar-apartments"; # Retrieve from property management website
$propertyName = "Foo Bar"; # Friendly name used for page title

# Property Login Credentials
$username = "bob%40example.com"; # Encoded username
$password = "Password123"; # Encoded password

# Generic Cloudflare Clearance Cookie: cf_clearance cookie from website (this is a sample cookie value, not real!)
$cfClearanceCookie = "a8sd6y89yhfuhruohofuihzefusfhasdasdd.89rgywefdhsyfosdy-1.1.1.1-8eysdfohsdfkhsdfhsdfhosiudfhusidhfsfsdfuhsueo.we8yfg8ewydfiuwsefyikusydfao8sdhsdfd";

# Path to curl-impersonate-chrome (download it here: https://github.com/lwthiker/curl-impersonate)
# OpenSSL is rejected by TLS handshake analysis, so use curl-impersonate-chrome, which uses BoringSSL, same as Chrome
$curl = "curl-impersonate-chrome";

# --------- All settings are mandatory. You MUST explicitly set ALL settings above this line. Ones below you can leave as default. ---------

# User Agent, can be tweaked:
$userAgent = "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36";

# Poll Frequency
$cacheSeconds = 3600 * 6; # Only make once request per 6 hours max

# Caching Settings. Will be created if they don't already exist
$cookieFile = '/tmp/rentcafe_cookies.txt'; # File in which to store cookies between intermediate steps
$htmlFile = '/tmp/rentcafe_html.txt'; # File in which to store cached version of scraped HTML

# Debug Mode
$debug = false;
?>