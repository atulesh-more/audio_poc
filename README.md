# audio_poc
audio recording POC
front_end is for web work. Simply place this folder in any web server and access index.php
backend folder belongs to antmedia app. We need to pace WebRTCApp folder in Ant media's /usr/local/antmedia/webapps.
In front end side index.php , we are calling loadArchive() function for loading all recorded audio from remoate cloud bucket.
In index.php, we must keep path of Ant media app websocketURL="wss://domain.com:5443/WebRTCApp/websocket";
SSL/HTTPS must require for webRTC to work.
