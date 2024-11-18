# rentcafe-rss

**RSS feed generator for RentCafe property bulletin boards**

If your property management company (e.g. apartment complex) utilizes Yardi's RentCafe portal, you may be familiar with it: a horrible, bloated, buggy interface, one that typically has a bulletin board for neighbors to chat, frequently to buy or sell stuff. The RentCafe system has a setting to ostensibly email tenants when a new message is posted, but the setting doesn't really work as described - certainly not more than 20% of the time. Additionally, the online bulletin board viewer is, like much of the "modern" web, simply awful - seriously, a fifth-grader could write a better one. The backend APIs easily provide all the messages, but the frontend obfuscates this by just frustratingly showing a few at a time.

The solution? An RSS feed, of course! This simple PHP script scrapes your property management's website (authenticated as you) and curates an RSS feed that you can add to your favorite feedreader. Life is short. Don't waste time with bloated RentCafe systems trying to read the bulletin board. Let the news come to you.

## Setup and Installation

1. Clone this repo to a location on your webserver, ideally internal or only accessible to your feedreader.
2. Based on the provided sample config (`config.sample.php`), set up your configuration in `config.php`. You'll need some of the links and identifiers from your property's resident portal website, and you'll need to encode your credentials here.
   - Download the special version of `curl` required from https://github.com/lwthiker/curl-impersonate
   - To get the CF cookie, you can use the Application tab in your browser's developer tools on a request to your property's website, which will allow you to easily grab the cookie's value
3. That's it! Configure the URL to the script in your favorite feedreader. Never log in to your property's resident portal again (well, until the next time you pay your rent...)
