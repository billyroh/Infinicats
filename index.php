<!DOCTYPE html>
<html>
<head>
<title>INFINITE CATS</title>
<link href='style.css' rel='stylesheet' type='text/css'>
<script src='lib/jquery-1.7.1.min.js'></script>
<script>
var next_max_tag_id;
var i = 0;
var didLoad = false;
var tag = $("#tag").val();

$(function() {
	start();
});

function start() {
	tag = $("#tag").val();
	loadPictures();
	setInterval(function() {
		loadPictures(next_max_tag_id);
	}, 60000);
}

function loadPictures(tag_id) {
	$.ajax({
		type: "GET",
		dataType: "jsonp",
		cache: false,
		url: "https://api.instagram.com/v1/tags/" + tag + "/media/recent?client_id=eb65e0f7615f4f839f9819b02fe8757b&max_tag_id=" + tag_id,
		success: function(rtn) {
			next_max_tag_id = rtn.pagination.next_max_tag_id;
			var dataLength = rtn.data.length;
			var docHeight = $(document).height()/2 + 200;
			timedLoop();
			i = 0;

			function timedLoop () {
				setInterval(function() {
					var imageLink = rtn.data[i].link;
					var imageID = rtn.data[i].id;
					var imageURL = rtn.data[i].images.standard_resolution.url;
					var randomWidth = Math.floor(Math.random() * 25) + 15 + "%";
					var randomLeft = Math.floor(Math.random() * 100) - 15 + "%";

					// Do not add photo if there is an excessive number of photos on screen.
					if ($("#photo-wrapper > a").size() > 6) return;

					// Return if photo doesn't have current tag.
					if (rtn.data[i].tags.indexOf(tag) == -1)
						return;

					$('#photo-wrapper').append("<a target='_blank' href='" + imageLink + "' id='link" + imageID + "'><div class='photo' id='" + imageID + 
					"'style='bottom: -600px; width:" + randomWidth + "; left:" + randomLeft + ";'><img src='" + imageURL + "'></div></a>");

					$("#" + imageID).css('z-index', $("#" + imageID).width());

					// Parallax animation. Larger the image (ie. closer the image) the faster it moves.
					var scrollTime = 999999/($("#" + imageID).width() * 0.3);
					$("#" + imageID).animate({
						bottom: docHeight * 2
						}, scrollTime, 'linear',
						function() {
						$("#link" + imageID).remove();
						// Known bug: Some images append themselves back in after remove() is called.
						// The delay() then remove() are a hacky way to alleviate the symptoms.
						// Bug becomes more and more relevant longer it runs.
						$("#link" + imageID).delay(10000);
						$("#link" + imageID).remove();
					});
					
					i++;
					if (i < dataLength)
						timedLoop();
				}, 3000);
			}
     	}
 	});
}

function getNewTag() {
	tag = $("#tag").val();
	tag = tag.replace(/[^a-zA-Z 0-9]+/g,'');
	$("#photo-wrapper").animate({
		opacity: 0
	}, 900, function(){
		$("#photo-wrapper").empty();
		$("#photo-wrapper").css('opacity', 1);
		window.history.pushState(null, null, "?q=" + tag);
		start();
	});
}

</script>
</head>

<body>
<div id='photo-wrapper'></div>
<div id='tag-wrapper'>
<?php
	if (!isset($_GET["q"]))
		$tag = "cat";
	else
		$tag = $_GET["q"];
	echo "<input type='text' id='tag' value='$tag' />";
?>
<div id='credits'><p>A dumb thing lovingly crafted by <a href='twitter.com/billyroh'>@billyroh</a>.<br />Photos from <a href='instagr.am'>Instagram</a>. Please enjoy.</div>
</div>
<script>
$("#tag").keypress(function(e) {
    if(e.keyCode == 13) {
        getNewTag();
    }
});
</script>
</body>
</html>