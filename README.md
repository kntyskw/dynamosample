dynamosocial
============

1. Deploy on a PHP-enabled web server
2. Run composer.sh or equivalent commands
3. Copy common-sample.inc.php to common.inc.php and edit according to your environment

To get Facebook event notifications

1. Create Facebook application
2. Set $fb_verify_token parameter in common.inc.php (any string which is hard to guess is fine)
3. Create subscription for user feeds (See Facebook Realtime API guide: http://developers.facebook.com/docs/reference/api/realtime/) 
