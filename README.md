# audio_poc
audio recording POC
(1)front_end is for web work. Simply place this folder in any web server and access index.php
(2)backend folder belongs to antmedia app. We need to pace WebRTCApp folder in Ant media's /usr/local/antmedia/webapps.
(3)In front end side index.php , we are calling loadArchive() function for loading all recorded audio from remoate cloud bucket.
(4)In index.php, we must keep path of Ant media app websocketURL="wss://domain.com:5443/WebRTCApp/websocket";
(5)SSL/HTTPS must require for webRTC to work.
