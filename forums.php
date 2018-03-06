<?php
/*
USE PDO CONNECTION TO INITIALIZE CLASS
*/
include_once("forums.class.php");
$core = new PDO("mysql:host=;dbname=DATABASE","USERNAME","PASSWORD")
if(isset($_SESSION['userid'])) {
	/*GETTING DATA FROM FORMS*/
	if(isset($_GET['topic'])&&isset($_GET['page'])) {
		switch($_GET['page']) {
			case "answer":
			if(isset($_POST['message'])) {
				if(!empty($_POST['message'])) {
				$core->answer($dbh, $_GET['topic'], $_SESSION['userid'], $_POST['message']);
				}
			}
			break;
			case "edit":
			if(isset($_POST['message'])) {
				if(!empty($_POST['message'])) {
					if($rowuinfo['rank'] >= 1) {
						$querym = $dbh->prepare("SELECT * FROM messages WHERE id=:id");
					}
					else {
						$querym = $dbh->prepare("SELECT * FROM messages WHERE id=:id AND user_id=:uid");
						$querym->bindValue(":uid", $_SESSION['userid']);
					}
						$querym->bindValue(":id", $_GET['mid']);

						$querym->execute();
						foreach($querym as $rowm) {
							if($rowm['user_id'] == $_SESSION['userid']) {
								$core->update_answer($dbh, $_GET['mid'], $_POST['message']); /*Updating existing topic*/	
							}
						}
				}
			}
		}
		
	} 
	elseif(isset($_GET['page'])) {
		if($_GET['page'] == 'newtopic') {
			if(isset($_POST['title'])&&isset($_POST['message'])) {
				$core->new_topic($dbh, $_POST['title'], $_POST['message'], $_SESSION['userid'], $_GET['board']); /*New topic*/
			}
		}
	}
	
    /*INITIALIZE BBC CODE FILE HERE IF YOU WANT IT EXTERNALLY*/
    $core->assing("\[b\](.*)\[\/b\]", "<b>$1</b>");
	$core->assing("\[sub\](.*)\[\/b\]", "<sub>$1</sub>");
	$core->assing("\[sup\](.*)\[\/b\]", "<sup>$1</sup>");
	$core->assing("\[u\](.*)\[\/u\]", "<ins>$1</ins>");
	$core->assing("\[i\](.*)\[\/i\]", "<i>$1</i>");
	$core->assing("\[em\](.*)\[\/em\]", "<em>$1</em>");
	$core->assing("\[del\](.*)\[\/del\]", "<del>$1</del>");
	$core->assing("\[mark\](.*)\[\/mark\]", "<mark>$1</mark>");
	$core->assing("\[small\](.*)\[\/small\]", "<small>$1</small>");
	$core->assing("\[url=(.*)\](.*)\[\/url\]", "<a href='$1'>$2</a>");
	$core->assing("\[li\](.*)\[\/li\]", "<li>$1</li>");
	$core->assing("\[code\](.*)\[\/code\]", "<div class='code'><pre>$1</pre></div>");
 ?>
               <h2>Foorumit</h2>
			   
			   <div class="forums">
					<?php
						if(!isset($_GET['board'])&&!isset($_GET['topic'])&&!isset($_GET['page'])) { 
						/**PRINTING ALL FORUM BOARDS**/
						$query = $dbh->prepare("SELECT * FROM boards");
						$query->execute();
								echo '<div class="forum_title">Foorumit</div>';
						foreach($query as $row) {
								echo '<div class="board"><a href="forums?board='.$row['id'].'">'.$row['title'].'</a><p class="description">'.nl2br($row['description']).'</p></div>';
						}
						/****************************/
						}/*MESSAGES AND TOPIC ITSELF*/
						elseif(isset($_GET['topic'])) {
							$query3 = $dbh->prepare("SELECT * FROM forum_topics WHERE id=:id"); // get topic info
							$query3->bindValue(":id", $_GET['topic']);
							$query3->execute();
							foreach($query3 as $row3) {
								
								echo '<div class="forum_title">'.$row3['topic'].'</div>';
								echo '<span class="right"><a href="forums?topic='.$_GET['topic'].'&page=answer#form">Vastaa</a></span>';
								echo '<a href="forums?board='.$row3['board_id'].'" class="goback"><<<</a><hr>';
								$query = $dbh->prepare("SELECT * FROM messages WHERE topic_id=:id"); //get messages to topic
								$query->bindValue(":id", $_GET['topic']);
								$query->execute();
								foreach($query as $row) {
									$query2 = $dbh->prepare("SELECT * FROM users WHERE id=:id"); // select username with user id
									$query2->bindValue(":id", $row['user_id']);
									$query2->execute();
									foreach($query2 as $row2) {
										$msg = $core->parser($row['message']);
										echo ($row['user_id'] == $_SESSION['userid']||$rowuinfo['rank'] >= 1 ? $editlink = "<span class='right'><a href='forums?topic=".$_GET['topic']."&mid=".$row['id']."&page=edit#form'>Edit message</a></span>":"");
										echo '<div class="message"><div class="userinfo">
										<b>'.$row2['username'].':</b></div>
										<br>'.nl2br($msg).'</div>';
									}
								}
							}
							if(isset($_GET['page'])) {
								switch($_GET['page']) {
								case "answer":
								/*ANSWER FORM*/
								echo '
								<form action="forums?topic='.$_GET['topic'].'&page=answer" method="POST">
									<h2 id="form">Vastaa</h2>
									Viesti:<br><textarea name="message" rows="15" cols="90"></textarea>
									<br><input type="submit" value="Lähetä">
									</form>';
								break;
								case "edit": /*If your usersystem uses some kind of rank system to differ from normal users*/
								if($rowuinfo['rank'] >= 1) {
								$querymsg = $dbh->prepare("SELECT * FROM messages WHERE id=:id");
								} else {
								$querymsg = $dbh->prepare("SELECT * FROM messages WHERE id=:id AND user_id=:uid");
								$querymsg->bindValue(":uid", $_SESSION['userid']);
								}
								$querymsg->bindValue(":id", $_GET['mid']);
								$querymsg->execute();
								foreach($querymsg as $rowmsg) {
									/*EDIT FORM*/
								echo '
									<form action="forums?topic='.$_GET['topic'].'&mid='.$_GET['mid'].'&page=edit" method="POST">

									<h2 id="form">Muokkaa vastausta</h2>
									Viesti:<br><textarea name="message" rows="15" cols="90">'.$rowmsg['message'].'</textarea>
									<br><input type="submit" value="Lähetä">
									</form>';
								}
								/**       **/
								break;
								}
							}
						}						
						elseif(isset($_GET['board'])) {
							/**get topics to board**/
							$query = $dbh->prepare("SELECT * FROM forum_topics WHERE board_id=:boardid ORDER BY id DESC"); 
							$query->bindValue(":boardid", $_GET['board']);
							$query->execute();
							$query2 = $dbh->prepare("SELECT * FROM boards WHERE id=:boardid");
							$query2->bindValue(":boardid", $_GET['board']);
							$query2->execute();
							foreach($query2 as $row) {
								echo '<div class="forum_title">'.$row['title'].'<p class="description">'.$row['description'].'</p></div>';
								echo '<a href="forums" class="goback"><<<</a><br>';
								echo '<span class="right"><a href="forums?board='.$_GET['board'].'&page=newtopic#form">Uusi</a></span><br><hr>';
								foreach($query as $row) {
									echo '<div class="topic"><a href="forums?topic='.$row['id'].'">'.$row['topic'].'</a><span class="date">'.$row['lastupdated'].'</span></div>';
								}
							}
							if(isset($_GET['page'])) {
								if($_GET['page'] == 'newtopic') {
									echo '
									<form action="forums?board='.$_GET['board'].'&page=newtopic" method="post">
									<h2 id="form">New topic</h2>
									Title:<br>
									<input type="text" name="title"><br>
									Msg<br>
									<textarea name="message" rows="15" cols="90"></textarea><br>
									<input type="submit" value="Lähetä">
									</form>';
								}
							}
						}	

					?>
					</div>
			   </div>
<?php
} else {
header("Location: TOSOMEPAGE SOMEWHERE");
}
?>