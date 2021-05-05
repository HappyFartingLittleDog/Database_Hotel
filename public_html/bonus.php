<html>
    <head>
        <title>CPSC 304 PHP/Oracle Demonstration</title>
    </head>

    <body>
    <a href="main.php"> Back to Main </a><br/>
        
<hr />

<h2>Insert Values into Bonus</h2>
<form method="POST" action="bonus.php"> <!--refresh page when submitted-->
    <input type="hidden" id="insertBonusRequest" name="insertBonusRequest">
    Bonus_Number: <input type="text" name="bonus_number"> <br /><br />
    Amount: <input type="text" name="amount"> <br /><br />
    Work_Number: <input type="text" name="work_number"> <br /><br />
    FD_Address: <input type="text" name="fd_address"> <br /><br />
    FD_Name: <input type="text" name="fd_name"> <br /><br />
    Hotel_Address: <input type="text" name="hotel_address"> <br /><br />
    Hotel_name:<input type="text" name="hotel_name"> <br /><br />

    <input type="submit" value="Insert" name="insertSubmit"></p>
</form>

<hr/>

<h2>Delete Values from Bonus</h2>
<form method="POST" action="bonus.php"> <!--refresh page when submitted-->
    <input type="hidden" id="deleteBonusRequest" name="deleteBonusRequest">
    Bonus_Number: <input type="text" name="bonus_number"> <br /><br />
    <input type="submit" value="Delete" name="deleteSubmit"></p>
</form>


<hr/>
<h2>Display the Tuples in  Bonus</h2>
        <form method="GET" action="bonus.php"> <!--refresh page when submitted-->
            <input type="hidden" id="displayTableRequest" name="displayTableRequest">
            <input type="submit"  name="displayTable"></p>
        </form>

</body>
</html>



<?php
//this tells the system that it's no longer just parsing html; it's now parsing PHP
$success = True; //keep track of errors so it redirects the page only if there are no errors
$db_conn = NULL; // edit the login credentials in connectToDB()
$show_debug_alert_messages = False; // set to True if you want alerts to show you which methods are being triggered (see how it is used in debugAlertMessage())
function debugAlertMessage($message) {
    global $show_debug_alert_messages;
    if ($show_debug_alert_messages) {
        echo "<script type='text/javascript'>alert('" . $message . "');</script>";
    }
}
function executePlainSQL($cmdstr) { //takes a plain (no bound variables) SQL command and executes it
    //echo "<br>running ".$cmdstr."<br>";
    global $db_conn, $success;
    $statement = OCIParse($db_conn, $cmdstr); 
    //There are a set of comments at the end of the file that describe some of the OCI specific functions and how they work
    if (!$statement) {
        echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
        $e = OCI_Error($db_conn); // For OCIParse errors pass the connection handle
        echo htmlentities($e['message']);
        $success = False;
    }
    $r = OCIExecute($statement, OCI_DEFAULT);
    if (!$r) {
        echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
        $e = oci_error($statement); // For OCIExecute errors pass the statementhandle
        echo htmlentities($e['message']);
        $success = False;
    }
    return $statement;
}
function executeBoundSQL($cmdstr, $list) {
    /* Sometimes the same statement will be executed several times with different values for the variables involved in the query.
In this case you don't need to create the statement several times. Bound variables cause a statement to only be
parsed once and you can reuse the statement. This is also very useful in protecting against SQL injection. 
See the sample code below for how this function is used */
    global $db_conn, $success;
    $statement = OCIParse($db_conn, $cmdstr);
    if (!$statement) {
        echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
        $e = OCI_Error($db_conn);
        echo htmlentities($e['message']);
        $success = False;
    }
    foreach ($list as $tuple) {
        foreach ($tuple as $bind => $val) {
            //echo $val;
            //echo "<br>".$bind."<br>";
            OCIBindByName($statement, $bind, $val);
            unset ($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype
        }
        $r = OCIExecute($statement, OCI_DEFAULT);
        if (!$r) {
            echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
            $e = OCI_Error($statement); // For OCIExecute errors, pass the statementhandle
            echo htmlentities($e['message']);
            echo "<br>";
            $success = False;
        }
    }
}
function connectToDB() {
    global $db_conn;
    // Your username is ora_(CWL_ID) and the password is a(student number). For example, 
    // ora_platypus is the username and a12345678 is the password.
    $db_conn = OCILogon("ora_pyuke", "a78099959", "dbhost.students.cs.ubc.ca:1522/stu");
    if ($db_conn) {
        debugAlertMessage("Database is Connected");
        return true;
    } else {
        debugAlertMessage("Cannot connect to Database");
        $e = OCI_Error(); // For OCILogon errors pass no handle
        echo htmlentities($e['message']);
        return false;
    }
}
function disconnectFromDB() {
    global $db_conn;
    debugAlertMessage("Disconnect from Database");
    OCILogoff($db_conn);
}
function handleInsertBonusRequest() {
    global $db_conn;
    //Getting the values from user and insert data into the table
    $tuple = array (
        ":bind1" => $_POST['bonus_number'],
        ":bind2" => $_POST['work_number'],
        ":bind3" => $_POST['amount'],
        ":bind4" => $_POST['FD_address '],
        ":bind5" => $_POST['FD_name '],
        ":bind6" => $_POST['hotel_address'],
        ":bind7" => $_POST['hotel_name' ]
    );
    $alltuples = array (
        $tuple
    );
    executeBoundSQL("insert into Car values (:bind1, :bind2, :bind3, :bind4, :bind5, :bind6, :bind7)", $alltuples);
    OCICommit($db_conn);
}
function handleDeleteBonusrRequest() {
    global $db_conn;
    $to_be_delete = $_POST['bonus_number'];
    // you need the wrap the old name and new name values with single quotations
    executePlainSQL("DELETE FROM Bonus WHERE bonus_number=  $to_be_delete " );
    OCICommit($db_conn);
}
function printResult($result) { //prints results from a select statement
    echo "<br>Retrieved data from table bonus:<br>";
    echo "<table>";
    echo "<tr><th>bonus_number/th><th>work_number</th><th>amount</th><th>FD_address</th><th>FD_name</th><th>hotel_address</th><th>hotel_name</th></tr>";
    while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
        echo "<tr><td>" . $row["BONUS_NUMBER"] . "</td><td>" . $row["WORK_NUMBER"] . "</td><td>" . $row["AMOUNT"] . "</td><td>" . $row["FD_ADDRESS"] .  "</td><td>" . $row["FD_NAME"] . "</td><td>" . $row["HOTEL_ADDRESS"] ."</td><td>" . $row["HOTEL_NAME"] . "</td></tr>";//or just use "echo $row[0]" 
    }
    echo "</table>";
}
function handleDisplayRequest() {
    global $db_conn;
    $result = executePlainSQL("SELECT * FROM Bonus");
    
    printResult($result);
    
}
function handleGETRequest() {
            if (connectToDB()) {
                if(array_key_exists('displayTable',$_GET)) {
                    handleDisplayRequest();
                }
                disconnectFromDB();
            }
        }
        function handlePOSTRequest() {
            if (connectToDB()) {
                if (array_key_exists('insertBonusRequest', $_POST)) {
                    handleInsertBonusRequest();
                }else if (array_key_exists('deleteBonusRequest', $_POST)) {
                    handleDeleteBonusRequest();
                }
                disconnectFromDB();
            }
        }
            if (isset($_POST['insertSubmit'])  || isset($_POST['deleteSubmit'])) {
                handlePOSTRequest();
            } else if (isset($_GET['displayTableRequest'])) {
                handleGETRequest();
            }
            ?>