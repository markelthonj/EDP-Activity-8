<?php
    // Set the content type of the response to JSON
    header("Content-Type: application/json");

    // Database connection parameters
    $host = 'localhost'; // Database host
    $db = 'adet_api'; // Database name
    $user = 'root'; // Database user
    $pass = ''; // Database password
    $charset = 'utf8mb4'; // Character set for the connection

    // Data Source Name (DSN) for the PDO connection
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    
    // Options for the PDO connection
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Throw exceptions on errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // Fetch results as associative arrays
        PDO::ATTR_EMULATE_PREPARES => false,  // Disable emulation of prepared statements
    ];

    // Attempt to establish a PDO connection to the database
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        // Return an error message in JSON format if the connection fails
        echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
        exit;  // Stop further script execution if the connection fails
    }

    // Handle GET request to retrieve users
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getUsers') {
        // SQL query to fetch user details along with their department name and total salary
        $stmt = $pdo->query("
            SELECT 
                a.userid, a.username, a.pass, a.email, 
                d.dname, s.totalsalary
            FROM 
                accounts a
            JOIN 
                department d ON a.dept_no = d.dnumber
            JOIN 
                deptsal s ON a.sal_no = s.dnumber
        ");
        // Fetch all user records
        $users = $stmt->fetchAll();
        // Return the user records as JSON
        echo json_encode($users);
    } 
    // Handle GET request to retrieve departments
    elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getDepartments') {
        // SQL query to fetch all departments
        $stmt = $pdo->query("SELECT dnumber, dname FROM department");
        // Fetch all department records
        $departments = $stmt->fetchAll();
        // Return the department records as JSON
        echo json_encode($departments);
    } 
    // Handle GET request to retrieve total salary for a specific department
    elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getTotalSalary' && isset($_GET['dnumber'])) {
        // Get the department number from the query parameter
        $dnumber = intval($_GET['dnumber']);
        // Prepare and execute the query to fetch the total salary for the specified department
        $stmt = $pdo->prepare("SELECT totalsalary FROM deptsal WHERE dnumber = ?");
        $stmt->execute([$dnumber]);
        // Fetch the total salary record
        $totalsalary = $stmt->fetch();
        // Return the total salary record as JSON
        echo json_encode($totalsalary);
    } 
    // Handle POST request to add a new user
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get the input data from the request body and decode it from JSON
        $input = json_decode(file_get_contents('php://input'), true);
        // SQL query to insert a new user record
        $sql = "INSERT INTO accounts (username, pass, email, dept_no, sal_no) VALUES (?, ?, ?, ?, ?)";
        // Prepare and execute the insertion query with the provided input data
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$input['username'], $input['pass'], $input['email'], $input['dept_no'], $input['sal_no']]);
        // Return a success message in JSON format
        echo json_encode(['message' => 'User added successfully']);
    }
?>
