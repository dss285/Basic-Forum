<?php
class forums {
	public $vars = array();
	public function answer($dbh, $topid, $sessionid, $message) {
		$message = htmlentities($message);
		$query = $dbh->prepare("INSERT INTO messages (topic_id,user_id,message) VALUES (:topid,:uid,:message)");
		$query->bindValue(":topid", $topid);
		$query->bindValue(":uid", $sessionid);
		$query->bindValue(":message", $message);
		$query->execute();
				header("Location: /forums");
	}
	public function update_answer($dbh, $mid, $message) {
		$message = htmlentities($message);
		$query = $dbh->prepare("UPDATE messages SET message = :msg WHERE id = :mid");
		$query->bindValue(":msg", $message);
		$query->bindValue(":mid", $mid);
		$query->execute();
	}
	public function new_topic($dbh, $title, $message, $uid, $boardid) {
		$message = htmlentities($message);
		$title = htmlentities($title);
		$query = $dbh->prepare("INSERT INTO forum_topics (topic, lastupdated, user_id, board_id) VALUES (:title, :updated, :userid, :boardid)");
		$query->bindValue(":title",$title);
		$query->bindValue(":updated", date("d.m.Y  H:i:s"));
		$query->bindValue(":userid",$uid);
		$query->bindValue(":boardid",$boardid);
		$query->execute();
		$lastid = $dbh->lastInsertId();
		$query2 = $dbh->prepare("INSERT INTO messages (topic_id, user_id, message) VALUES (:topid, :uid, :msg)");
		$query2->bindValue(":topid",$lastid);
		$query2->bindValue(":uid",$uid);
		$query2->bindValue(":msg",$message);
		$query2->execute();
		header("Location: forums?board=".$boardid);
		}
	public function edit_board($dbh, $title, $description, $id) {
		$query = $dbh->prepare("UPDATE boards SET description = :desc, title = :title WHERE id = :id");
		$query->bindValue(":title", $title);
		$query->bindValue(":desc", $description);
		$query->bindValue(":id", $id);
		$query->execute();
	}
	public function new_board($dbh, $title, $description) {
		$query = $dbh->prepare("INSERT INTO boards (title, description) VALUES (:title, :description)");
		$query->bindValue(":title",$title);
		$query->bindValue(":description", $description);
		$query->execute();
	}
	public function delete_board($dbh, $id) {
		$query = $dbh->prepare("DELETE FROM boards WHERE id=:id");
		$query->bindValue(":id", $id);
		$query->execute();
	}
	public function assing($key, $value) {
			$this->vars[$key] = $value;
	}
	public function parser($msg) {
			foreach($this->vars as $key => $value) {
				$pattern = '/'.$key.'/s';
				$msg = preg_replace($pattern, $value, $msg);
			}
			return $msg;
	}
}
?>