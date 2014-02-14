<?php	

/*This script will send an email to all the faculty. The mail will contain the Rollno, Name and Attendance % of each 
student who are enrolled in each of thecourses taken by that particular faculty. 
A separate mail will be sent for each course withe the course name as teh subject of the mail. 
*/
include('dbconnect.php');
include('functions2.php');
$con = db_connect();
session_start();
$type = "Present";
//define the receiver of the email
//selecting id of each faculty
//$query = "SELECT id FROM faculty";
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
			$c_id = $row2['course_id'];
			$course_name = $row2['course_name'];
			//define the subject of the email
			$subject = $row2['course_name']; 
			//$output = "<html><head><style>table{border:solid 1px black;cell-padding:2px;}td{border:solid 1px black;}</style></head><body>";
			$output = "<table border='1'><tr border='1'><th>Roll Number</th><th>Attendance %</th><th>Name</th></tr>";
			$result5 = mysql_query("SELECT starting_date , finishing_date FROM course_details WHERE course_id='$c_id' ") or die(mysql_errno());
			$row5 = mysql_fetch_array($result5);
			$from = $row5['starting_date'];
			$till = $row5['finishing_date'];
			$con = db_connect();
			$t = class_details($con, $c_id, $from, $till);
			/*$query = "  SELECT reg.name , reg.id_no 
                	FROM registered reg , enrolled_details ed  
			WHERE ed.course_id='$c_id' AND reg.id_no=ed.id_no ";*/
			$result6 = mysql_query("  SELECT reg.name , reg.id_no 
                	FROM registered reg , enrolled_details ed  
			WHERE ed.course_id='$c_id' AND reg.id_no=ed.id_no ") or die(mysql_error());
			$stu_reg_list = array();
            $stu_roll_list = array();
            while ($row6 = mysql_fetch_array($result6)) {
                array_push($stu_reg_list, $row6['name']);
                array_push($stu_roll_list, $row6['id_no']);
            }
			
			
			$schedule = array();
            $schedule = get_schedule($schedule, $c_id, $con);
			//initialize holidays in holid array
            $holid = array();
            $holid = initialize_holid($holid, $from, $till, $con);
			//chart_initialize
            //setting up the chart table
            //actual report
            $chart = array();    
            $chart1= array();
            $chart2= array();
        
            //chart_initialize
            $chart = initialize_chart($chart, $chart1, $schedule, $from, $till, $con , $c_id);
			$i = 0;
            $stu_len = count($stu_reg_list);
            while ($i < $stu_len) {
				$rollno = $stu_roll_list[$i];
                $present_days = report($con,$rollno,$c_id,$from,$till, $type, $schedule , $holid , $chart, $t);
				if($t > 0){
				$percent = ($present_days / $t) * 100;
				$output .= "<tr border='1'><td>".$stu_roll_list[$i]."</td><td>". round($percent, 2) . "</td><td>".$stu_reg_list[$i]."</td></tr>";
				}
				else{
				$output .= "<tr border='1'><td>".$stu_roll_list[$i]."</td><td>". 0 . "</td><td>".$stu_reg_list[$i]."</td></tr>";
				}
				//report($con,$rollno,$c_id,$start_date,$today,$type , $schedule , $holid_till_today , $chart, $till_today);
                $i = $i + 1;
			}
			
			
			$output .= "</table>";
			//define the message to be sent. Each line should be separated with \n
			$message = $output;
			echo $message;
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