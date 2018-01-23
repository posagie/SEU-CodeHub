<?php
require_once "heading.php";
require_once "mydb.php";
$jsfile = 'https://cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js';
$jsfile2 = 'https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js';
printDocHeading("start.css", "SEU CodeHub", $jsfile, $jsfile2);
print "<body>\n<div class='heading'>\n";
print "<h1> SEU CodeHub </h1>\n";
print "</div>\n";
//print "<BR>\n";
print "<div id='parent'>\n";
session_start();
if(isset($_SESSION['signed_in']) && $_SESSION['signed_in'] == true)
{
    if(isset($_GET['uclick'])){
        userProfile();
    }
    else if(isset($_GET['cclick'])){
        if($_POST['threadmade']){
            checkThread();
        }
        else{
            threadCategoriesPage();
        }
    }
    else if(isset($_GET['tclick'])){
        threadPage();
        if($_POST['post']){
            processReply();
        }
    }
    else{
        if($_POST['threadmade']){
            checkThread();
        }
        else{
            mainPage($msg);
        }
    }
}
else{
    if(empty($_POST) || $_POST['logout']){
        showLogin($logerr);
    }
    elseif($_POST['create']){
        showRegistrationForm($name, $pass, $pass2, $email, $phone, $major, $minor, $error);
    }
    if($_POST['created']){
        checkReginfo();
    }
    if($_POST['login']){
        checkLogin();
    }
}
//________________________________Login page____________________________________
function showLogin($logerr){
        $self = $_SERVER['PHP_SELF'];
        print
        "<form id='login' method = 'post' action = '$self'>\n".
        "<fieldset>\n".
        "<legend>Log In</legend>\n".
        "<input type = 'text' placeholder = 'Username' name = 'user'/>\n".
        "<BR><BR>\n".
	    "<input type = 'password' placeholder = 'Password' name = 'pass'/>\n".
	    "<BR><BR>\n".
        "<input type = 'submit' name = 'login' value = 'Log In'/>\n".
        "<BR><BR>\n".
        "<input type = 'submit' name = 'create' value = 'Create Account'/>\n".
        "<BR><BR>\n";
    	if($logerr){
	        print $logerr;
	    }
    	print "</fieldset>\n";
    	print "</form>\n";
}

//__________________________"Create an account" page____________________________
function showRegistrationForm($name, $pass, $pass2, $email, $phone, $major, $minor, $error){
    print "<form id='login' method = 'post' action = '$self'>\n";
    print "<fieldset>\n";
    print "<legend>Create an Account</legend>";
	print "<input type = 'text' name = 'username' placeholder = 'Username' value = '$name'/>\n <BR><BR>";
	print "<input type = 'password' name = 'password' placeholder = 'Password' value = '$pass'/>\n <BR><BR>";
	print "<input type = 'password' name = 'password2' placeholder = 'Re-type Password' value = '$pass2'/>\n <BR><BR>";
	print "<input type = 'text' name = 'email' placeholder = 'Email' value = '$email'/>\n <BR><BR>";
	print "<input type = 'text' name = 'phone' placeholder = 'Phone number' value = '$phone'/>\n <BR><BR>";
	print "<input type = 'text' name = 'major' placeholder = 'Major' value = '$major'/>\n <BR><BR>";
	print "<input type = 'text' name = 'minor' placeholder = 'Minor' value = '$minor'/>\n <BR><BR>";
	print "<input type = 'submit' name = 'created' value = 'Create Account'/>\n";
	print "<BR><BR>";
	if($error){
	    print $error;
	}
	print "</fieldset>\n";
	print "</form>\n";

}
//____________________________check log in info_________________________________
function checkLogin(){
    $db = dbConnect();
    $user = htmlentities($_POST['user'], ENT_QUOTES);
    $pass = htmlentities($_POST['pass'], ENT_QUOTES);
    if($user=="" || $pass==""){
        $logerr = "Please fill in all fields";
        showLogin($logerr);
    }
    else{
        $query2 = "SELECT sUsername, sPassword FROM Students WHERE sUsername = '$user'";
        $result = $db->Execute($query2);
        $row = $result->FetchRow();
        $name = $row['sUsername'];
        $word =  $row['sPassword'];
        if($user==$name && $pass==$word){
            $query = "SELECT sID FROM Students WHERE sUsername = '$name'";
            $result = $db->Execute($query);
            $row = $result->FetchRow();
            $_SESSION['userID'] = $row[0];
        	$_SESSION['signed_in'] = true;
        	$_SESSION['username'] = $name;
            mainPage($msg);
        }
        else{
            $logerr = "Invalid Username or Password";
            showLogin($logerr);
        }
    }
}
//__________________________check registration info_____________________________
function checkReginfo(){
    $db = dbConnect();
    $name = htmlentities($_POST['username'], ENT_QUOTES);
    $pass = htmlentities($_POST['password'], ENT_QUOTES);
    $pass2 = htmlentities($_POST['password2'], ENT_QUOTES);
    $email = htmlentities($_POST['email'], ENT_QUOTES);
    $phone = htmlentities($_POST['phone'], ENT_QUOTES);
    $major = htmlentities($_POST['major'], ENT_QUOTES);
    $minor = htmlentities($_POST['minor'], ENT_QUOTES);
    $query = "SELECT sUsername FROM Students WHERE sUsername = '$name'";
    $result = $db->Execute($query);
    $row = $result->FetchRow();
    $name2 = $row['sUsername'];
    if($name=="" || $pass=="" || $pass2=="" || $email=="" || $phone=="" || $major=="" || $minor==""){
        $error = "Please fill in all fields";
    }
    if($pass != $pass2){
        $error = "Re-type your password accurately";
    }
    if($name == $name2){
        $error = "That username is already taken";
    }
    if($error){
        showRegistrationForm($name, $pass, $pass2, $email, $phone, $major, $minor, $error);
    }
    else{
        uploadRegInfo($name, $pass, $email, $phone, $major, $minor);
        $logerr = "Success! Your account has been created";
        showLogin($logerr);
    }
}

//__________________________Upload registration info____________________________
function uploadRegInfo($name, $pass, $email, $phone, $major, $minor){
        $db = dbConnect();
        $query = "INSERT INTO Students (sUsername, sPassword, sEmail, sPhonenumber, sMajor, sMinor) VALUES ('$name', '$pass', '$email', '$phone', '$major', '$minor') ";
	    $result = $db->Execute($query);
}

//______________________________Main page_______________________________________
function mainPage($msg){
if($_POST['logout']){
    unset($_SESSION['cat']);
    unset($_SESSION['signed_in']);
    $logerr = "You've logged out";
    showLogin($logerr);
}
else if($_POST['nuThread']){
    createThreadForm($title, $terror);
}
else if($_POST['contacts']){
    contactsPage();
}
else{
    print "<form method = 'post' action = '$self'>\n";
    print "<div id='menu'>";
    print "<input type = 'submit' name = 'logout' value = 'Log Out'/> ";
    print "<input type = 'submit' name = 'nuThread' value = 'Create Thread'/> ";
    print "<input type = 'submit' name = 'contacts' value = 'Contacts'/> ";
    print "</div>";
    print "<BR><BR>";
    print "<div id='content'>";
    if($msg){
         print '<h4>'. $msg . '</h4><BR>';
    }
    else{
        echo '<h4>Welcome, ' . $_SESSION['username'] . '!</h4>'.'<BR>';
    }
    print "<BR><BR>";

    //Table of categories_________________v
        echo '<table border="3">
            <tr>
                <th>Category</th>
                <th>Last Thread in Category</th>
            </tr>';
    //'classwork' row_____________________v
        echo '<tr>';
        $cat1 = "Classwork";
            echo '<td class="leftpart">';
                echo '<h3>Classwork</h3>';
                echo '<a href="index.php?cclick='. $cat1 .'">See all threads</a>';
            echo '</td>';
            echo '<td class="midpart">';
                formatLastTopic($cat1);
            echo '</td>';
        echo '</tr>';
    //'events' row________________________v
        echo '<tr>';
        $cat2 = "Events";
            echo '<td class="leftpart">';
                echo '<h3>Events</h3>';
                echo '<a href="index.php?cclick='. $cat2 .'">See all threads</a>';
            echo '</td>';
            echo '<td class="midpart">';
                formatLastTopic($cat2);
            echo '</td>';
        echo '</tr>';
    //'meetups' row_______________________v
        echo '<tr>';
        $cat3 = "Meetups";
            echo '<td class="leftpart">';
                echo '<h3>Meetups</h3>';
                echo '<a href="index.php?cclick='. $cat3 .'">See all threads</a>';
            echo '</td>';
            echo '<td class="midpart">';
                formatLastTopic($cat3);
            echo '</td>';
        echo '</tr>';
    //'employment' row____________________v
        echo '<tr>';
        $cat4 = "Employment";
            echo '<td class="leftpart">';
                echo '<h3>Employment</h3>';
                echo '<a href="index.php?cclick='. $cat4 .'">See all threads</a>';
            echo '</td>';
            echo '<td class="midpart">';
                formatLastTopic($cat4);
            echo '</td>';
        echo '</tr>';
    //'Projects' row______________________v
        echo '<tr>';
        $cat5 = "Projects";
            echo '<td class="leftpart">';
                echo '<h3>Projects</h3>';
                echo '<a href="index.php?cclick='. $cat5 .'">See all threads</a>';
            echo '</td>';
            echo '<td class="midpart">';
                formatLastTopic($cat5);
            echo '</td>';
        echo '</tr>';
    //'Other' row________________________v
        echo '<tr>';
        $cat6 = "Other";
            echo '<td class="leftpart">';
                echo '<h3>Other</h3>';
                echo '<a href="index.php?cclick='. $cat6 .'">See all threads</a>';
            echo '</td>';
            echo '<td class="midpart">';
                formatLastTopic($cat6);
            echo '</td>';
        echo '</tr>';
    print "</div>";
    print "</form>\n";
    }
}

//____________________________Create thread page________________________________
function  createThreadForm($title, $terror){
    if($_POST['home']){
        header('Location: index.php');
    }
    else{
        print "<form id='login' method = 'post' action ='$self'>\n";
        print "<div id= 'menu'>";
        print "<input type = 'submit' name = 'home' value = 'Home'/>";
        print "</div>";
        print "<BR><BR>";
        print "<legend>Create a Thread</legend>";
    	print "<input type = 'text' name = 'title' placeholder = 'Thread Title' value = '$title'/>\n <BR><BR>";
    	print "<h4>Choose a Category</h4><BR>";
    	print "<input type='radio' name='category' value='classwork'> Classwork<br>";
    	print "<input type='radio' name='category' value='events'> Events<br>";
    	print "<input type='radio' name='category' value='meetups'> Meetups<br>";
    	print "<input type='radio' name='category' value='employment'> Employment<br>";
    	print "<input type='radio' name='category' value='projects'> Projects<br>";
    	print "<input type='radio' name='category' value='other'> Other";
    	print "<BR><BR>";
    	print "<input type = 'submit' name = 'threadmade' value = 'Create Thread'/>\n";
    	if($terror){
    	    echo "<BR><BR>";
    	    echo $terror;
    	}
    	print "</form>\n";
    }
}

//____________________________check thread info_________________________________
function  checkThread(){
    $db = dbConnect();
    $title = htmlentities($_POST['title'], ENT_QUOTES);
    $cat = htmlentities($_POST['category'], ENT_QUOTES);
    $creator = htmlentities($_SESSION['username'], ENT_QUOTES);
    $query = "SELECT tTitle FROM Threads WHERE tTitle = '$title'";
    $result = $db->Execute($query);
    $row = $result->FetchRow();
    $title2 = $row['tTitle'];
    if($title == $title2){
        $terror= "This thread already exists";
    }
    if($cat==""){
        $terror= "Please pick a category";
    }
    if($title==""){
        $terror = "Please title your thread";
    }
    if($terror){
        createThreadForm($title, $terror);
    }
    else{
        uploadThread($title, $cat, $creator);
        if(isset($_GET['cclick'])){
            unset($_POST['threadmade']);
            unset($_POST['caThread']);
            header('Location: index.php?cclick='.$_GET['cclick']);
        }
        else{
            $msg = "Thread created!";
            mainPage($msg);
        }
    }
}

//__________________________Upload thread to database___________________________
function uploadThread($title, $cat, $creator){
        $db = dbConnect();
        $query = "INSERT INTO Threads (tTitle, tCategory, tCreator) VALUES ('$title', '$cat', '$creator') ";
	    $result = $db->Execute($query);
}

//______________________________Last topic column_______________________________
function formatLastTopic($cat){
    $db = dbConnect();
    $query = "SELECT *
        FROM Threads 
        WHERE tCategory = '$cat'
        ORDER  BY tID DESC";
    $result = $db->Execute($query);
    $row = $result->FetchRow();
    $title = $row['tTitle'];
    $name = $row['tCreator'];
    if(!$title || !$name){
         echo 'no topics';
    }
    else{
        echo '<a href="index.php?tclick='. $title .'"><h4>'.$title . '</h4></a><BR>';
        echo 'created by <a href="index.php?uclick='. $name .'">'. $name.'</a>';
    }
}

//______________________________User profile page_______________________________
function userProfile(){
    if($_POST['home']){
        header('Location: index.php');
    }
    else{
        print "<form method = 'post' action = '$self'>\n";
        print "<div id= 'menu'>";
        print "<input type = 'submit' name = 'home' value = 'Home'/>";
        print "</div>";
        print "<BR><BR>";
        print "<div id='content'>";
        print "<div id='userbar'>";
        print "<input type = 'submit' name = 'add' value = 'Add Contact'/>";
        print "</div>";
        $cname = $_SESSION['username'];
        $uname = $_GET['uclick'];
        echo "<h4> $uname </h4>";
        if($_POST['add']){
            if($cname == $uname){
                echo "You cannot add yourself";
            }
            else{
                pullContactID($cname, $uname);
            }
        }
    }
}

//________________________Pull contact ID from Students_________________________
function pullContactID($user1, $user2){
    $db = dbConnect();
    $query = "SELECT sID
            FROM Students
            WHERE sUsername = '$user1'";
    $query2 = "SELECT sID
            FROM Students
            WHERE sUsername = '$user2'";
    $result = $db->Execute($query);
    $result2 = $db->Execute($query2);
    $row = $result->FetchRow();
    $row2 = $result2->FetchRow();
    $id1 = $row[0];
    $id2 = $row2[0];
    insertContact($id1, $id2, $user2);
}

//________________________Insert contacts into database_________________________
function insertContact($user1, $user2, $uname2){
    $db = dbConnect();
    $query = "SELECT sID1, sID2 FROM UsertoContact WHERE sID1 = '$user1' AND sID2 = '$user2'";
    $result = $db->Execute($query);
    $row = $result->FetchRow();
    $res1 = $row[0];
    $res2 = $row[1];
    if($res1== $user1 && $res2== $user2){
        echo "This user is already in your contact list.";
    }
    else{
        $query2 = "INSERT INTO UsertoContact (sID1, sID2) VALUES ('$user1', '$user2')";
        $result2 = $db->Execute($query2);
        echo $uname2." is now your contact";
    }
}
//________________________________Contacts page_________________________________
function contactsPage(){
    if($_POST['home']){
        header('Location: index.php');
    }
    else{
        $db = dbConnect();
        $uID = $_SESSION['userID'];
        $query = "SELECT sID2 FROM UsertoContact WHERE sID1 = '$uID'";
        $result = $db->Execute($query);
        print "<form method = 'post' action = '$self'>\n";
        print "<div id= 'menu'>";
        print "<input type = 'submit' name = 'home' value = 'Home'/>";
        print "</div>";
        print "<BR><BR>";
        print "<div id='content'>";
        print "<h3>Contacts</h3>";
        echo '<table border="3">
            <tr>
                <th>Contact</th>
                <th>Last thread created</th>
            </tr>';
        while($row = $result->FetchRow()){
            $uID2 = $row[0];
            $query2 = "SELECT * FROM Students WHERE sID = '$uID2'";
            $result2 = $db->Execute($query2);
            $row2 = $result2->FetchRow();
            $uname = $row2['sUsername'];
            $email = $row2['sEmail'];
            $query3 = "SELECT * FROM Threads WHERE tCreator = '$uname'
                    ORDER  BY tID DESC";
            $result3 = $db->Execute($query3);
            $row3 = $result3->FetchRow();
            $tname = $row3['tTitle'];
            echo '<tr>';
                echo '<td class="leftpart">';
                    echo '<h4>'.$uname.'</h4>';
                    echo $email;
                echo '</td>';
                echo '<td class="midpart">';
                    echo '<a href="index.php?tclick='. $tname .'">'. $tname .'</a>';
                echo '</td>';
            echo '</tr>';
        }
    }
}
//________________________________Category page_________________________________
function threadCategoriesPage(){
    if($_POST['home']){
        unset($_SESSION['cat']);
        header('Location: index.php');
    }
    elseif($_POST['caThread']){
        createThreadForm($title="", $terror="");
        
    }
    else{
        print "<form method = 'post' action = '$self'>\n";
        print "<div id= 'menu'>";
            print "<input type = 'submit' name = 'home' value = 'Home'/>";
            print "<input type = 'submit' name = 'caThread' value = 'Create Thread'/> ";
        print "</div>";
        $cname = $_GET['cclick'];
        $_SESSION['cat'] = $cname;
        $db = dbConnect();
        $query = "SELECT * FROM Threads WHERE tCategory = '$cname' ORDER  BY tID DESC";
        $result = $db->Execute($query);
        print "<div id='container'>";
            print "<div class='row'>";
        		print "<div class='col-md-10 col-md-offset-1'>";
            	    print "<h2 align='center'>". $cname ." Threads</h2>";
            	print "</div>";
    		print "</div>";
            print "<div class='row'>";
            	print "<div class='col-md-10 col-md-offset-1' align='center'>";
            		print "<table id='myTable' class='display' cellspacing='0' width='100%'>";
            		print "<thead>";
                        print "<tr>";
                            print "<th>Thread Topic</th>";
                            print "<th>Creator</th>";
                            print "<th>Date Posted</th>";
                            print "<th>Last Reply</th>";
                        print "</tr>";
                    print "</thead>";
                    print "<tbody>";
                        while($row = $result->FetchRow()){
                            $title = $row['tTitle'];
                            $name = $row['tCreator'];
                            $time = $row['tTimestamp'];
                            echo '<tr>'.
                                '<td><a href="index.php?tclick='. $title .'">'. $title .'</a></td>'.
                                '<td><a href="index.php?uclick='. $name .'">'. $name .'</a></td>'.
                                '<td>'. $time .'</td>'.
                                '<td></td>'.
                            '</tr>';
                        }
                    print "</tbody>";
    		    print "</div>";
    		print "</div>";
		print "</div>";
    }
}
//___________________________________Thread_____________________________________
function threadPage(){
    if($_POST['home']){
        unset($_SESSION['cat']);
        header('Location: index.php');
    }
    else{
        $tname = $_GET['tclick'];
        print "<form method = 'post' action = '$self'>\n";
        print "<div id= 'menu'>";
            print "<input type = 'submit' name = 'home' value = 'Home'/>";
        print "</div>";
        print "<div id='container'>";
            print "<div class='row'>";
            	print "<div class='col-md-10 col-md-offset-1'>";
            	    print "<h2 align='center'>". $tname ."</h2>";
                print "</div>";
        	print "</div>";
        print "</div>";
        $db = dbconnect();
        $query = "SELECT * FROM Threads WHERE tTitle = '$tname'";
        $result = $db->Execute($query);
        $row = $result->FetchRow();
        $tID = $row['tID'];
        $tcat = $row['tCategory'];
        $query2 = "SELECT DISTINCT pID, pCreator, pContent, pTimestamp FROM Posts WHERE pThread = '$tname'";
        $result2 = $db->Execute($query2);
        $queryc = "SELECT DISTINCT pID, pCreator, pContent, pTimestamp FROM Posts WHERE pThread = '$tname'";
        $resultc = $db->Execute($queryc);
        $rowc = $resultc->FetchRow();
        if($rowc['pID'] == ""){
            print "<h3 align='center'> Be the first to post in this thread <BR><BR><input type = 'submit' name = 'reply' value = 'Post'/></h3>";
            if($_POST['reply']){
                replyPage();
            }
        }
        else{
            print "<h3 align='center'><input type = 'submit' name = 'reply' value = 'Create Post'/></h3>";
            if($_POST['reply']){
                replyPage();
            }
            print "<div id='content'>";
            echo '<table border="2">';
            echo '<tr>';
            while ($row2 = $result2->FetchRow()){
                    $name = $row2['pCreator'];
                    $cont = $row2['pContent'];
                    $time = $row2['pTimestamp'];
                        echo '<td class="leftpart">';
                            echo '<a href="index.php?uclick='. $name .'"><h4>'.$name.'</h4></a>';
                            echo $time;
                        echo '</td>';
                        echo '<td class="midpart">';
                            echo $cont;
                        echo '</td>';
                    echo '</tr>';
               // }
            }
            
        }
    }
}
//________________________________Reply section_________________________________
function replyPage(){
    print "<form method='post' action='$self'>";
    print "<div id='content'>";
    print "<textarea name='content'></textarea>";
    print "<BR>";
    print "<input type='submit' name = 'post' value='Submit' />";
    print "<input type='submit' name = 'cancel' value='Cancel' />";
    print "</div>";
    print "</form>";
}
//________________________________Process reply_________________________________
function processReply(){
    if(isset($_SESSION['cat'])){
        $cname = htmlentities($_SESSION['cat'], ENT_QUOTES);
    }
    $tname = htmlentities($_GET['tclick'], ENT_QUOTES);
    $post = htmlentities($_POST['content'], ENT_QUOTES);
    $poster = htmlentities($_SESSION['username'], ENT_QUOTES);
    if($post == ""){
        print "<h4 align= 'center'>Please enter a reply</h4>";
        replyPage();
    }
    else{
        $db = dbconnect();
        $query = "INSERT INTO Posts (pCreator, pContent, pThread) VALUES ('$poster', '$post', '$tname')";
        $result = $db->Execute($query);
        $query2 = "SELECT tID FROM Threads WHERE tTitle = '$tname' AND tCategory = '$cname'";
        $result2 = $db->Execute($query2);
        $row = $result2->FetchRow();
        $tID = $row['tID'];
        $query3 = "SELECT pID FROM Posts WHERE pCreator = '$poster' AND pContent = '$post'";
        $result3 = $db->Execute($query3);
        $row2 = $result3->FetchRow();
        $pID = $row2['pID'];
        $query4 = "INSERT INTO ThreadToPost (tID, pID) VALUES ('$tID', '$pID')";
        $result4 = $db->Execute($query4);
        header('Location: index.php?tclick='.$tname);
    }
}
?>