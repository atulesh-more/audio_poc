<html>
<head>
<title>Audio Publishing Component</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta charset="UTF-8">
<link rel="stylesheet" href="css/bootstrap.min.css">
<script src="js/external/adapter-latest.js"></script>
<script	src="js/external/jquery-3.4.1.min.js"></script>
		

<style>
video 
{
	width: 100%;
	max-width: 640px;
}
/* Space out content a bit */
body {
	padding-top: 20px;
	padding-bottom: 20px;
}

/* Everything but the jumbotron gets side spacing for mobile first views */
.header, .marketing, .footer {
	padding-right: 15px;
	padding-left: 15px;
}

/* Custom page header */
.header {
	padding-bottom: 20px;
	border-bottom: 1px solid #e5e5e5;
}
/* Make the masthead heading the same height as the navigation */
.header h3 {
	margin-top: 0;
	margin-bottom: 0;
	line-height: 40px;
}

/* Custom page footer */
.footer {
	padding-top: 19px;
	color: #777;
	border-top: 1px solid #e5e5e5;
}

/* Customize container */
@media ( min-width : 768px) {
	.container {
		max-width: 730px;
	}
}

.container-narrow>hr {
	margin: 30px 0;
}

/* Main marketing message and sign up button */
.jumbotron {
	text-align: center;
	border-bottom: 1px solid #e5e5e5;
}

/* Responsive: Portrait tablets and up */
@media screen and (min-width: 768px) {
	/* Remove the padding we set earlier */
	.header, .marketing, .footer {
		padding-right: 0;
		padding-left: 0;
	}
	/* Space out the masthead */
	.header {
		margin-bottom: 30px;
	}
	/* Remove the bottom border on the jumbotron for visual effect */
	.jumbotron {
		border-bottom: 0;
	}
}
</style>
</head>
<body>
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-8 col-md-8">		

				<div class="jumbotron">

					<p>
						<audio id="localVideo" autoplay controls muted></audio>
					</p>

					<div style="margin-top:10px; margin-bottom:20px;" class="col-sm-6 col-sm-offset-3">Microphone Gain: <br><input type=range id="volume_change_input" min=0 max=1 value=1 step=0.01></div>

					<p>
						<input type="text" class="form-control" value="stream1"
							id="streamName" placeholder="Type stream name">
					</p>
					<p>
						<button class="btn btn-info" disabled
							id="start_publish_button">Start Audio Stream</button>
						<button class="btn btn-info" disabled
							id="stop_publish_button">Stop Audio Stream</button>
					</p>

								<span class="label label-success" id="broadcastingInfo" style="font-size:14px;display:none"
									style="display: none">Publishing</span>

				</div>
			</div>
			<div class="col-lg-4 col-md-4" >
				<p>Archived  <img src="images/refresh-flat.png" width="30" height="30" style="position:cursor" onClick="loadArchive()">
				</p>
				<p>
					<ul class="list-group" id="listArhive">
					  
					 
					</ul>
				</p>

			</div>
		</div>			

	</div><!--/container-->
</body>
<script type="module">
	import {WebRTCAdaptor} from "./js/webrtc_adaptor.js"

	var start_publish_button = document.getElementById("start_publish_button");
	start_publish_button.addEventListener("click", startPublishing, false);
	var stop_publish_button = document.getElementById("stop_publish_button");
	stop_publish_button.addEventListener("click", stopPublishing, false);

	var streamNameBox = document.getElementById("streamName");
	
	var streamId;

	var volume_change_input = document.getElementById("volume_change_input");
	volume_change_input.addEventListener("change", changeVolume);

	function changeVolume(){
	/**
   	* Change the gain levels on the input selector.
   	*/
   	if(document.getElementById('volume_change_input') != null){
   		webRTCAdaptor.currentVolume = this.value;
       	if(webRTCAdaptor.soundOriginGainNode != null){
       		webRTCAdaptor.soundOriginGainNode.gain.value = this.value; // Any number between 0 and 1.
       	}
   	}
    }

	function startPublishing() {
		streamId = streamNameBox.value;
		webRTCAdaptor.publish(streamId);
	}

	function stopPublishing() {
		webRTCAdaptor.stop(streamId);
	}
	
    function startAnimation() {

        $("#broadcastingInfo").fadeIn(800, function () {
          $("#broadcastingInfo").fadeOut(800, function () {
        	var state = webRTCAdaptor.signallingState(streamId);
            if (state != null && state != "closed") {
            	var iceState = webRTCAdaptor.iceConnectionState(streamId);
            	if (iceState != null && iceState != "failed" && iceState != "disconnected") {
              		startAnimation();
            	}
            }
          });
        });

      }

	var pc_config = null;

	var sdpConstraints = {
		OfferToReceiveAudio : false,
		OfferToReceiveVideo : false

	};
	
	var mediaConstraints = {
		video : false,
		audio : true
	};

	var appName = location.pathname.substring(0, location.pathname.lastIndexOf("/")+1);
	var websocketURL = "ws://" + location.hostname + ":" + location.port + appName + "websocket";
	
	if (location.protocol.startsWith("https")) {
		websocketURL = "wss://" + location.hostname + ":" + location.port + appName + "websocket";
	}
	websocketURL="wss://wivew.com:5443/WebRTCApp/websocket";
	
	var webRTCAdaptor = new WebRTCAdaptor({
		websocket_url : websocketURL,
		mediaConstraints : mediaConstraints,
		peerconnection_config : pc_config,
		sdp_constraints : sdpConstraints,
		localVideoId : "localVideo",
		debug:true,
		callback : function(info, description) {
			if (info == "initialized") {
				console.log("initialized");
				start_publish_button.disabled = false;
				stop_publish_button.disabled = true;
			} else if (info == "publish_started") {
				//stream is being published
				console.log("publish started");
				start_publish_button.disabled = true;
				stop_publish_button.disabled = false;
				startAnimation();
			} else if (info == "publish_finished") {
				//stream is being finished
				console.log("publish finished");
				start_publish_button.disabled = false;
				stop_publish_button.disabled = true;
			}
			else if (info == "closed") {
				//console.log("Connection closed");
				if (typeof description != "undefined") {
					console.log("Connecton closed: " + JSON.stringify(description));
				}
			}
		},
		callbackError : function(error, message) {
			//some of the possible errors, NotFoundError, SecurityError,PermissionDeniedError
            
			console.log("error callback: " +  JSON.stringify(error));
			var errorMessage = JSON.stringify(error);
			if (typeof message != "undefined") {
				errorMessage = message;
			}
			var errorMessage = JSON.stringify(error);
			if (error.indexOf("NotFoundError") != -1) {
				errorMessage = "Camera or Mic are not found or not allowed in your device";
			}
			else if (error.indexOf("NotReadableError") != -1 || error.indexOf("TrackStartError") != -1) {
				errorMessage = "Camera or Mic is being used by some other process that does not let read the devices";
			}
			else if(error.indexOf("OverconstrainedError") != -1 || error.indexOf("ConstraintNotSatisfiedError") != -1) {
				errorMessage = "There is no device found that fits your video and audio constraints. You may change video and audio constraints"
			}
			else if (error.indexOf("NotAllowedError") != -1 || error.indexOf("PermissionDeniedError") != -1) {
				errorMessage = "You are not allowed to access camera and mic.";
			}
			else if (error.indexOf("TypeError") != -1) {
				errorMessage = "Video/Audio is required";
			}
		
			alert(errorMessage);
		}
	});
</script>
<script type="text/javascript">

function loadArchive()
	{

		$("#listArhive").empty();
		$.ajax({

			dataType:"json",
			data:"{}",
			url:"https://wivew.com:5443/WebRTCApp/rest/v2/vods/list/0/10",
			success:function(data){

				//alert(data)
				for(var x=0;x<data.length;x++)
				{
					//alert(data[x].streamName);
					//url from Ant https://wivew.com:5443/WebRTCApp/streams/data[x].streamName
					//url from cloud https://cloud-videos.fra1.digitaloceanspaces.com/streams/data[x].streamName
					var d = new Date(data[x].creationDate);
					$("#listArhive").append("<li class=\"list-group-item\">"+data[x].streamId+" &nbsp; <span style='font-size:9px'>"+d.toDateString()+ "</span><audio src = \"https://cloud-videos.fra1.digitaloceanspaces.com/streams/"+data[x].streamName+"\" controls ></audio><p>cloud url:<span style='font-size:9px;color:blue'>https://cloud-videos.fra1.digitaloceanspaces.com/streams/"+data[x].streamName+"</span></p></li>");
					
					

				}

			},
			error:function(){


			}




		})//ajax



	}

</script>
</html>
