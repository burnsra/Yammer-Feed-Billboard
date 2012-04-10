<?php
require_once(dirname(__FILE__)."/secrets.php");
?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="css/style.css" />
<script src="https://assets.yammer.com/platform/yam.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script>
  
	var popX;
	var popY;
	var popW;
	var popH;
	var inRequest = false;
	var timeout_ms = 20000
	var messageLimit = 10;
		
	var userData = {};	
	var link_window = null;
	var thisPageData = {};
  $(document).ready(function(){
	yam.config({appId: "<?php echo $consumer_key;?>"});
	

	$("#main_div").append("<BR>screen size : " + screen.width + " ,  " + screen.height);
	
	
	// browser outer width
	$("#main_div").append("<br>browser : " + window.outerWidth + " , " + window.outerHeight + " : position: " + window.screenX + " , " + window.screenY );
	
	$("#clickhere").click(function(){
		//alert("clicked");
	
		// get screen size, and this windows size and position.
		// then we'll calculate where the popup goes
		// screen : screen.width , screen.height
		// browser : window.outerWidth , window.outerHeight
		
		// goal: popup is as big as it can be, doesn't overlap with this window.
		var sizeBelow = screen.height - window.outerHeight - window.screenY;
		var sizeAbove = window.screenY;
		var sizeLeft = window.screenX;
		var sizeRight = screen.width - window.outerWidth - window.screenX
		// should screen go above, below, right, or left  of this screen?
		var areaBelow = sizeBelow * screen.width;
		var areaAbove = sizeAbove * screen.width;
		var areaLeft = sizeLeft * screen.height;
		var areaRight = sizeRight * screen.height;
		
		var zone = "below";
		var max = areaBelow;
		if (areaAbove > max){
			zone = "above";
			max = areaAbove;
		}
		if(areaLeft > max){
			zone = "left";
			max = areaLeft			
		}
		if(areaRight > max){
			zone = "right";
			max = areaRight;
		}		
//		alert("b" + areaBelow + " a " + areaAbove + " r " + areaRight + " l " + areaLeft + "zone " + zone);
		if(zone =="below"){
			popX = 0;
			popY = screen.height - sizeBelow;
			popW = screen.width;
			popH = sizeBelow;
		}else if(zone == "above"){
			popX = 0;
			popY = 0;
			popW = screen.width;
			popH = sizeAbove;
		}else if(zone == "left"){
			popX = 0;
			popY = 0;
			popW = sizeLeft;
			popH = screen.height;
		}else if(zone == "right"){
			popX = screen.width - sizeRight;
			popY = 0;
			popW = sizeRight;
			popH = screen.height;
		}
		// note: when first running on a new browser, this part needs to be commented out.
		
		yam.getLoginStatus( function(response) {
		  if (response.authResponse) {    
			//alert("not logged");
			// logged in and connected user, someone you know?  
		  } else { 
			//alert("logged in");
			// no user session available, someone you don�t know
		  }  
		});		
	
		yam.login( function (response) {
		  if (response.authResponse) {  
			//alert("good login");
			// user successfully logged in? 
		  } else {
			//alert("bad login");
			// user cancelled login?  
		  }
		});
		
		doRequest();

	//		sleep(30000);
		
		
	});
	
  
  });
  

  function doRequest(){
	inRequest = true;
	var data = "search="+escape('#dashboard');
	yam.request(
		  { 
/*
		  url: "/api/v1/messages/following"
		  , data: "limit=5&threaded=true"
*/
		
		
		  url: "/api/v1/search"
		  , data: "search="+escape('#dashboard')
		  , method: "GET"
		  , success: function (msgs) { 
			var string = "";			
			var length = msgs.messages.messages.length;
			//alert("len " + length);
			var i= 0;
			doMessages(msgs, length, i);
			inRequest = false;
//			alert("string " + string);
		  }
		  , error: function (msg) { alert("Data Not Saved: " + msg); }
		  }
	); 
	//setTimeout("doRequest()", 5000);
  }
  

	function doMessages(msgs, length, i){
		var msg = msgs.messages.messages[i];
		
		/*
		var text = msg.body.rich;
		$("#main_div").html(text);
		$("#main_div").html(text);
		*/
		var num_attach = 0;
		// links might be in a few different places in the returned json.
		// one place is the message attachments
		$(msg.attachments).each(function (key2, attachment){
			//string += " \nattachment: " + attachment.type ;
			//string += " \nweb_url : " + attachment.web_url ;
			if(link_window == null){
			//	alert("opening new " + attachment.web_url);
			//	$("#frame").attr("src",attachment.web_url);
				var dims = "width="+popW+",height="+popH+",top="+popY+",left="+popX;
				link_window = window.open(attachment.web_url, "_blank", dims);
				if(link_window.moveTo){
				//	link_window.moveTo(popX, popY);
				}else{
			//		alert("no moveTo");
				}
			}else{
			//	$("#frame").attr("src",attachment.web_url);
			//	alert("opening " + attachment.web_url);
				link_window.location = attachment.web_url;
			}
			num_attach++;
			return false; // we're just going to break after the first attachment, for now.			
		});		
		// if that didn't work, look in the msg.body.urls array
		if(num_attach == 0){
			// look in the msg.body.urls array
			$(msg.body.urls).each(function (key2, web_url){
				//string += " \nattachment: " + attachment.type ;
				//string += " \nweb_url : " + attachment.web_url ;
				if(link_window == null){
				//	alert("opening new " + attachment.web_url);
				//	$("#frame").attr("src",attachment.web_url);
					var dims = "width="+popW+",height="+popH+",top="+popY+",left="+popX;
					link_window = window.open(web_url, "_blank", dims);
					if(link_window.moveTo){
						link_window.moveTo(popX, popY);
					}
				}else{
				//	$("#frame").attr("src",attachment.web_url);
					//alert("opening " + attachment.web_url);
					link_window.location = web_url;
				}
				num_attach++;
				return false; // we're just going to break after the first attachment, for now.			
			});			
			
		}
		
		if(num_attach == 0 && link_window != null){
	
			link_window.close();
			link_window = null;
		}
		
		// let's also show the conversation thread:
		var thread_id = msg.thread_id;
		yam.request(
			  { url: "/api/v1/messages/in_thread/"+thread_id
			  , method: "GET"
			  , data: "limit=5"
			  , success: function (threads) { 
				var string = "";			
				var threadlen = threads.messages.length;
				var j= 0;
				doThreads(thread_id, threads, threadlen, j);
	//			alert("string " + string);
			  }
			  , error: function (msg) {
			  alert("thread Data Not Saved: " + msg); 
			  }
			  }
		);		
		

		
		i++;
		if(i < length){
			setTimeout(function(){doMessages(msgs, length, i)}, timeout_ms);
		}else{
			setTimeout("doRequest()", timeout_ms);
		
		}
	}
  
	function doThreads(root_id, threads, length, j){
		// assemble array of thread info.
		var threadArray = {};
		var k=0;
		$(threads.messages).each(function(key, message){
			threadArray[message.id] = message;
			threadArray[message.id].children = [];
			var sender_id = message.sender_id;
			getUserData(sender_id, "user"+sender_id);
			k++;
		});
		$.each(threadArray, function (tk, threadM){
			if(threadM.replied_to_id != null){
				threadArray[threadM.replied_to_id].children.push(threadM.id);
			}
		});
		
		var threadText = buildThreadText(threadArray, root_id, 1);
		$("#main_div").html(threadText);
		//alert("len " + length);
	}
	
	function buildThreadText(threadArray, id, depth){
		var thisMessage = threadArray[id];
		var prepend = "";//Array(depth + 1).join("-");
		var userdivclass = "user"+thisMessage.sender_id;
		var user = userData[thisMessage.sender_id];
		
		var message_date = thisMessage.created_at;
		
		var userNameString = "";
		var userImgString = "";
		if(user){
			userNameString= formatUserName(user);
			userImgString= formatUserImage(user);
		}
		var string = "\n<div class='message_and_children depth"+depth+"'><div class='single_message'>"+
					prepend+"\n<div class='user userimg "+userdivclass + "img'>"+userImgString+
					"</div><div class='message_contents'><div class='user username "+userdivclass+"name'>"
					+userNameString+"</div>\n<div class='message_text'>" 
					+thisMessage.body.rich+"</div><div class='message_date'>created: "+message_date+"</div></div></div>";		
			
		thisMessage.children.sort(function(a,b){
			var date1 = new Date(threadArray[a].created_at);
			var date2 = new Date(threadArray[b].created_at);
			return ((date1 < date2) ? -1 : ((date1 > date2) ? 1 : 0));
			
		});		
		$.each(thisMessage.children, function(tk, child){			
			string += "\n" + buildThreadText(threadArray, child, depth + 1);
		});
		
		return string + "</div>\n";	
	}
  
	function getUserData(user_id, div_class){
		var user;
		if(userData[user_id]){

		}else{
	
			yam.request(
				{ url: "/api/v1/users/"+user_id
				  , method: "GET"
				  , data: "limit="+messageLimit
				  , success: function (user) { 
					userData[user_id] = user;
					placeUserData(user, div_class);
				  }
				  , error: function (msg) {
				  alert("user Data Not Saved: " + msg); 
				  }
				}
			);		
		}
	}
	

  
	function formatUserString(user){
		var string = "";
		string += formatUserImage(user);
		string += formatUserName(user);
		return string;
	}
	
	function formatUserImage(user){
		var string = "";
		string += "<img class='user_image' src='"+user.mugshot_url+"' />";
		return string;	
	}
	
	function formatUserName(user){
		var string = "";
		string += user.full_name+":";
		return string;	
	}	
	
	function placeUserData(user, div_class){
		placeUserName(user, div_class);
		placeUserImage(user, div_class);
	}
	
	function placeUserImage(user, div_class){
		$("."+div_class+"img").html(formatUserImage(user));
	}
	
	function placeUserName(user, div_class){
		$("."+div_class+"name").html(formatUserName(user));
	}
  
  	function sleep(ms)
	{
		var dt = new Date();
		dt.setTime(dt.getTime() + ms);
		while (new Date().getTime() < dt.getTime());
	}
  
</script>
</head>
<body>
<div id="main_div">make this window a skinny one, down the left side of the screen, on your primary monitor (if you have multiple).<BR>Then <div id='clickhere'><b><u>click here to start</u></b></div></div>
<!--
<div id="framediv" style="position:absolute; left:77; top:77; width:377; height:377; clip:rect(0,381,381,0); background:#FFF;">
<iframe id="frame"  width="377" height="377" marginwidth="0" marginheight="0" frameborder="no" scrolling="yes" style="border-width:2px; border-color:#333; background:#FFF; border-style:solid;"></iframe>
</div>
-->
</body>
</html>

