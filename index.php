
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset=utf-8>
</head>

<body>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script type="text/javascript" src='remix.js'></script>
<script type="text/javascript">

var apiKey = 'TX2IDAM1HXOO99YPB';
var trackID1 = 'TRVIYTM13CE6CE2052';
var trackID2 = 'TRTHKUB13CE6CED0DA';
var trackID3 = 'TRWXQQA133FBD61572';
var trackID4 = 'TROTOZX13CE9E9F9AA';
var trackID5 = 'TRDFDXK13CE9EBD025';
var trackID6 = 'TRXBXLN13CE9ED4D96';
var trackURL = 'http://majorglitch.net/wp/wp-content/uploads/2014/04/Track02.mp3';
var trackURL2 = 'http://majorglitch.net/wp/wp-content/uploads/2014/04/audio/Track02.mp3';
var trackURL3 = 'http://majorglitch.net/wp/wp-content/uploads/2014/04/audio/Track03.mp3';
var trackURL4 = 'http://majorglitch.net/wp/wp-content/uploads/2014/04/audio/Track04.mp3'
var trackURL5 = 'http://majorglitch.net/wp/wp-content/uploads/2014/04/audio/Track05.mp3'
var trackURL6 = 'http://majorglitch.net/wp/wp-content/uploads/2014/04/audio/rebeccaturner.mp3'
var remixer;
var remixer2;
var remixer3;
var remixer4;
var player;
var player2;
var player3;
var player4;

var track;
var track2;
var track3;

var remixed;
var remixed2;
var remixed3;
var ultimateRemix;

function init() {
    if (window.webkitAudioContext === undefined) {
        error("Sorry, this app needs advanced web audio. Your browser doesn't"
            + " support it. Try the latest version of Chrome");
    } else {
        var context = new webkitAudioContext();
        remixer = createJRemixer(context, $, apiKey);
        player = remixer.getPlayer();
        remixer4 = createJRemixer(context, $, apiKey);
        player4 = remixer4.getPlayer();
        $("#info1").text("Loading analysis data...");
        ultimateRemix = new Array();

        remixer.remixTrackById(trackID4, trackURL4, function(t, percent) {
            track = t;

            $("#info1").text(percent + "% of the first track loaded");
            if (percent == 100) {
                $("#info1").text(percent + "% of the first track loaded, analyzing...");
            }

            if (track.status == 'ok') {
                remixed = new Array();
                // Do the remixing here!
                for (var i=0; i < track.analysis.bars.length; i++) {
                    if (i % 4 == 0) {
                        remixed.push(track.analysis.bars[i]);
                        
                    }
                }
                addToUltimate(remixed);
                        
                $("#info1").text("1st track complete!");
            }
        });
       
        
        remixer2 = createJRemixer(context, $, apiKey);
        player2 = remixer2.getPlayer();
        remixer2.remixTrackById(trackID5, trackURL5, function(t, percent) {
            track2 = t;

            $("#info2").text(percent + "% of 2nd track loaded");
            if (percent == 100) {
                $("#info2").text(percent + "% of track 2nd track loaded, analyzing...");
            }

            if (track2.status == 'ok') {
                remixed2 = new Array();
                for (var i=0; i < track2.analysis.bars.length; i++) {
                    if (i % 3 == 0) {
                        remixed2.push(track2.analysis.bars[i]);
                        
                        
                    }
                }
                addToUltimate(remixed2);
                
                $("#info2").text("2nd track complete!");
            }
        });
       
        remixer3 = createJRemixer(context, $, apiKey);
        player3 = remixer2.getPlayer();
        remixer3.remixTrackById(trackID6, trackURL6, function(t, percent) {
            track3 = t;

            $("#info3").text(percent + "% of  3rd track loaded");
            if (percent == 100) {
                $("#info3").text(percent + "% of 3rd track loaded, analyzing...");
            }

            if (track3.status == 'ok') {
                remixed3 = new Array();
                for (var i=0; i < track3.analysis.bars.length; i++) {
                    if (i % 4 == 0) {
                        remixed3.push(track3.analysis.bars[i]);
                        
                    }
                }
                addToUltimate(remixed3);
             $("#info3").text("3rd track complete!");     
            }
           
        });

    }
    shuffleArray(ultimateRemix);
    fisherYates(ultimateRemix);
}

function shuffleArray(array) {
    for (var i = array.length - 1; i > 0; i--) {
        var j = Math.floor(Math.random() * (i + 1));
        var temp = array[i];
        array[i] = array[j];
        array[j] = temp;
    }
    return array;
}

function fisherYates ( myArray ) {
  var i = myArray.length, j, tempi, tempj;
  if ( i == 0 ) return false;
  while ( --i ) {
     j = Math.floor( Math.random() * ( i + 1 ) );
     tempi = myArray[i];
     tempj = myArray[j];
     myArray[i] = tempj;
     myArray[j] = tempi;
   }
}
 
function addToUltimate( myArray) {
    
    $.merge(ultimateRemix, myArray);
    ultimateRemix = shuffleArray(ultimateRemix);
}       

window.onload = init;
</script>

<center><img src="http://2.bp.blogspot.com/-2_Z_mkZwbHc/T1bPYLIT0PI/AAAAAAAAFJ4/QXKAY8rqkYo/s1600/franz_liszt_257775.jpg"></center>
<center><h1>Liszt's Sonata in B Minor, for People That Know Every Recurring Theme</h1></center>


<div id='info1'></div>
<div id='info2'></div>
<div id='info3'></div>

<button onClick="player.play(0, remixed); player2.play(0, remixed2); player3.play(0, remixed3);">Stack That Concurrently</button>
<button onClick="player.stop(); player2.stop(); player3.stop();">Stop!</button></br>
<button onClick="player4.play(0,ultimateRemix);">Shuffle It</button>
<button onClick="player4.stop();">Stop!</button>

</body>



</html>
