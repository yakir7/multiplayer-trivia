<?


session_start();
$player = session_id();

$gameid = $_GET['g'];
$filename = "games/".$gameid.".txt";
if (!file_exists($filename) && !isset($_GET['g'])){
	$gameid = substr(hash('ripemd160', rand(0, 999999999999999)), 0, 10);
	$game = [
		'players'=>new stdClass(),
		'questions' => json_decode( file_get_contents("https://opentdb.com/api.php?amount=10&category=9&difficulty=easy")),
		'answers' =>new stdClass()
	];
	
	file_put_contents( "games/".$gameid.".txt" , json_encode($game) );

	//echo $gameid;
}
$filename = "games/".$gameid.".txt";
$game = json_decode(file_get_contents($filename));

$timerd="";
echo "<html lang=\"en\"><head><title>Trivia With Friends</title><body><div class=\"box0\">";
if (!isset( $game->players->$player )){

	if ($_GET["nick"]){
		$nick = preg_replace('/[^A-Za-z ]/', '', $_GET["nick"]);
		if ($nick!=""){
			$game->players->$player = $nick;
			$game->answers->$player = [];

			file_put_contents( $filename , json_encode($game) );
			$game = json_decode(file_get_contents($filename)); 
		}
	}
	if (!$_GET["nick"] || !$nick){
		if (count((array)$game->players)>0){
			echo "<div class=\"box1 msg\"><b>".count((array)$game->players)."</b> Players answered this game.<br>Play the game to see their answers.</div>";
		}
		echo "<br/><br/>pick Nickname: <br/><br/><input type='text' id='nick'  placeholder='Only english letters' /><br/><br/><button onclick=\"location.href=location.href.split('?')[0]+'?g=".$gameid."&nick='+nick.value\">Start</button>";

	}
} 

if (isset( $game->players->$player )){
	$ans=$game->answers->$player;
	$ques=$game->questions->results;

	echo "<div class=\"box1 ltr1\">Hello <span class=\"pname\">". ($game->players->$player).",</span></div>";

	if ( count($ans) < count($ques) ){
		if ( isset($_GET['a']) && $_GET['q']==count($ans)){
			$que=$ques[ count($ans) ];
			if ($_GET['a'] == $que->correct_answer ){
				echo "<div class=\"box1 iscor\">You are correct!</div>";
				$game->answers->$player[] = 1;
			}else{
				echo "<div class=\"box1 isnotcor\">You are incorrect! The correct answer is:  <br><b>".($que->correct_answer)."</b></div>";
				$game->answers->$player[] = 0;
			}
			file_put_contents( $filename , json_encode($game) );
			$coran = array();
			$incoran = array();
			foreach ($game->answers as $key => $value){
				if ($key != $player){
					if ($value[count($ans)]==1){
						$coran[]= $game->players->$key;
					}else{
						$incoran[]= $game->players->$key;
					}
				}
			}
			$cort = "";
			foreach ($coran as $i=>$tnick){
			//for ($i=0; $i<count($coran); $i++){
				if ($i == count($coran)-1 && $i!=0){
					$cort.=" and ".$tnick." ";
				}else{
					if ($i==0){
						$cort.=" ".$tnick." ";
					}else{
						$cort.=" ".$tnick.", ";
					}
				}
			}
			$incort = "";
			foreach ($incoran as $i=>$tnick){
			//for ($i=0; $i<count($incoran); $i++){
				if ($i == count($incoran)-1 && $i!=0){
					$incort.=" and ".$tnick." ";
				}else{
					if ($i==0){
						$incort.=" ".$tnick." ";
					}else{
						$incort.=" ".$tnick.", ";
					}
				}
			}
			if (count($coran)>0){
				echo "<div class=\"box1 anscor\"><span class=\"pname\">".$cort."</span> answered this correctly.</div>";
			}
			if (count($incoran)>0){
				echo "<div class=\"box1 ansincor\"><span class=\"pname\">".$incort."</span> answered this incorrectly.</div>";
			}
		}else if(count($ans)>0){
			echo "<div class=\"box1 msg\">you need to select an answer.</div>";
		}
		$ans=$game->answers->$player;
		if (count($ans) < count($ques) ){
			$que=$ques[ count($ans) ];

			//echo json_encode( $que )."<br>"; 
			if (count($ans)>0){ 
				$timerd = ".timer {display:block;}"; 
			}
			echo "<div class=\"box1 ltr1 que\"><b>Question ".(count($ans)+1)." out of ".count($ques)."</b>:<br>";
			echo $que->question."</div>";

			$answers = $que->incorrect_answers;
			array_splice( $answers, rand(0, count($answers) ), 0, $que->correct_answer );

			for ($i=0; $i< count($answers); $i++ ){
				echo "<a class=\"nolink\" href='?g=".$gameid."&a=".urlencode($answers[$i])."&q=".(count($ans))."'><div class=\"box1 opt\"><span class=\"optsp\">".$answers[$i]."</span></div></a>";
			}
		}
	}
	if (count($ans) == count($ques) ){
		$score = 0;
		for ($s=0; $s< count($ans); $s++ ){
			$score+=$ans[$s];
		}
		echo "<div class=\"box1 msg\">you finished the game!<br>your score is <b>".$score."</b> out of <b>".count($ans)."</b></div>";
		//var $scores = stdClass();
		echo "<div class=\"box1 ltr1 scrbrd\"><center>Scoreboard</center>";
		foreach ($game->answers as $key => $value){
			$tscore=0;
			foreach ($value as $ansb){
				$tscore+=$ansb;
			}
			//$scores->($game->players->$key) = $tscore;
			echo "<br><span class=\"pname\">".$game->players->$key."</span> got <b>".$tscore."</b> out of <b>".count($value)."</b>";
		}
		echo "<br><br></div>";


	}

	$url= explode("?", "$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]")[0]."?g=".$gameid;
	echo "<div class=\"box1 ltr1\">Challenge a friend:</div><input id=\"gameurl\" type=\"text\" size=\"30\" disabled value=\"".$url."\" /><button onclick=\"myFunction('gameurl')\"> Copy </button>";
	echo "<br>Or:<br><a href='?'>Create a new game<br>with new questions</a>";
}
//echo "<br><br><br><hr>".json_encode($game);
?>
<div class="popup" id="popup">Copied!</div>
<div class="popuptimer" id="popuptimer">Time's up!</div>
<div class="timer" id="timer"></div>
</div>
<style>


	body { font-family: Arial}
	.box0 { width:100%; }
	.box0 , .box1{border-color: black; border-width: 1px; margin: 1vh; padding: 1vh;  border-radius: 2vh; text-align: center; margin-left: auto; margin-right: auto;}
	.ltr1 { padding-left: 1vh; text-align:left; direction: ltr; }
	.pname { font-weight: bold; }
	.iscor { background: green; color: white;}
	.isnotcor { background: red; color: white;}
	.msg { background: yellow; }
	.que, .opt { background: #c0ccff; }
	.opt:hover { background: yellow; }
	.anscor { background: lightgreen; }
	.ansincor { background: #ffbbbb; }
	.scrbrd { border-style: solid; }
	.nolink  { color: black; text-decoration: none; }
	button {background:#7777ff; color:white; padding:1vh; border-radius: 1vh;}
	.popup { background: #77ff77; color: black; padding: 4vh; border-radius: 2vh; position: fixed; left:40%;bottom: 5vh; font-size: 40pt; display:none;}
	.popuptimer { background: #77ff77; color: black; padding: 4vh; border-radius: 2vh; position: fixed; left:40%;top: 30vh; font-size: 5vh; display:none;}

	.show { display:block;}
	.timer { background: #ffff0060; color: black; padding: 2vh; border-radius: 5vh; position: fixed; right:1vh;top: 1vh; font-size: 70pt; display:none;}

	<?= $timerd ?>

	#nick { font-size: 3vh; }
	@media (orientation: landscape) {
		.timer { right:30%;font-size: 50pt;}
	  .box1 {width:30%;}
	}

	@media (orientation: portrait) {

	   .box1 {width:90%;}
	   button,body { font-size: 36pt;}
	   input{ font-size: 28pt;}
	   
	}
</style>
<script>
var time=20;
timer.innerHTML =time;
window.setInterval( function(){ 
	time--;
	timer.innerHTML =time;
	if (<?= count($ans) ?> <10 && <?= count($ans) ?> >0){
		if (time==0){
		popuptimer.classList.add("show");
		}else if (time==-1){
			location.href=location.href.split('?')[0]+'?g=<?= $gameid ?>&q=<?= count($ans) ?>&a=undefined';
		}
	} 
}, 1000);

function myFunction(el) {
  var selectText = document.getElementById(el);
var range = document.createRange();
range.selectNode(selectText);
window.getSelection().addRange(range);
document.execCommand('Copy');
window.getSelection().removeAllRanges();
popup.classList.add("show");
window.setTimeout(function(){ popup.classList.remove("show"); }, 2000 );
}
</script>
</body></html>



