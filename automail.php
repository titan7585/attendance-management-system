

<?php	

/*This script will send an email to all the faculty. The mail will contain the Rollno, Name and Attendance % of each 
student who are enrolled in each of thecourses taken by that particular faculty. 
A separate mail will be sent for each course withe the course name as teh subject of the mail. 
*/

include('dbconnect.php');
session_start();

//define the receiver of the email
//selecting id of each faculty
$result = mysql_query("SELECT id FROM faculty") or die(mysql_error());

    while ($row = mysql_fetch_array($result)){
		//$to = $row['email'];
		$id = $row['id'];
		//getting emailid of the faculty
		$result4 = mysql_query("select email_id from registered where id_no='$id'") or die(mysql_error());
		$row4 = mysql_fetch_array($result4);
		$to = $row4['email_id'];
		//getting course id and course name of the courses taken by the faculty
		$result2 = mysql_query("select course_id, course_name from course_details where id_no='$id'") or die(mysql_error());
		while ($row2 = mysql_fetch_array($result2)){
			$course_id = $row2['course_id'];
			$course_name = $row2['course_name'];
			//define the subject of the email
			$subject = $row2['course_name']; 
			//$output = "<html><head><style>table{border:solid 1px black;cell-padding:2px;}td{border:solid 1px black;}</style></head><body>";
			$output = "<table border='1'><tr border='1'><th>Roll Number</th><th>Attendance %</th><th>Name</th></tr>";
			//getting the list of all the students enrolled in that particular course
			$result3 = mysql_query("select id_no from enrolled_details where course_id = '$course_id'") or die(mysql_error());
			while ($row3 = mysql_fetch_array($result3)){
				$student_id = $row3['id_no'];
				//echo $_SESSION[$student_id] . "<br><br>";
				$result4 = mysql_query("select name from registered where id_no='$student_id'") or die(mysql_error());
				$row4 = mysql_fetch_array($result4);
				$stud_name = $row4['name'];
				//echo $_SESSION[$stud_name];
				//teh session values are coming from the functions.php page, print_chart()
				$output .= "<tr border='1'><td>".$row3['id_no']."</td><td>". $_SESSION[$student_id] . "</td><td>".$_SESSION[$stud_name]."</td></tr>";
			}
			$output .= "</table>";
			//define the message to be sent. Each line should be separated with \n
			$message = $output;
			//define the headers we want passed. Note that they are separated with \r\n
			$headers = "From: your_mail@gmail.com\r\nReply-To: your_mail@gmail.com\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
			//send the email
			$mail_sent = @mail( $to, $subject, $message, $headers);
			//if the message is sent successfully print "Mail sent". Otherwise print "Mail failed" 
			echo $mail_sent ? "Mail sent to $id <br>" : "Mail failed to $to<br>";
		}

		
	}	
	
	echo "<a href = 'facindex.php'>Go To Faculty Home Page</a>";
?>