<?php
    // Set the content type to JSON
    header("Content-Type: application/json");

    // Database connection parameters
    $host = 'localhost';
    $db = 'act8_api';
    $user = 'root';
    $pass = ''; 
    $charset = 'utf8mb4';

    // Data Source Name (DSN) for the PDO connection
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    // Options for PDO connection
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Enable exceptions for errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // Fetch associative arrays
        PDO::ATTR_EMULATE_PREPARES => false,  // Use real prepared statements
    ];

    // Try to establish the PDO connection
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        // Return an error message if the connection fails
        echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
        exit;  // Stop execution if connection fails
    }

    // Handle GET request for getting users
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getUsers') {
        // SQL query to fetch users along with their department name and total salary
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
        // Fetch all results
        $users = $stmt->fetchAll();
        // Return the results as JSON
        echo json_encode($users);
    } 
    // Handle GET request for getting departments
    elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getDepartments') {
        // SQL query to fetch all departments
        $stmt = $pdo->query("SELECT dnumber, dname FROM department");
        // Fetch all results
        $departments = $stmt->fetchAll();
        // Return the results as JSON
        echo json_encode($departments);
    } 
    // Handle GET request for getting total salary of a specific department
    elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getTotalSalary' && isset($_GET['dnumber'])) {
        // Get the department number from the request
        $dnumber = intval($_GET['dnumber']);
        // Prepare and execute the query to get the total salary for the department
        $stmt = $pdo->prepare("SELECT totalsalary FROM deptsal WHERE dnumber = ?");
        $stmt->execute([$dnumber]);
        // Fetch the result
        $totalsalary = $stmt->fetch();
        // Return the result as JSON
        echo json_encode($totalsalary);
    } 
    // Handle POST request for adding a new user
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get the input data from the request body
        $input = json_decode(file_get_contents('php://input'), true);
        // SQL query to insert a new user
        $sql = "INSERT INTO accounts (username, pass, email, dept_no, sal_no) VALUES (?, ?, ?, ?, ?)";
        // Prepare and execute the query
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$input['username'], $input['pass'], $input['email'], $input['dept_no'], $input['sal_no']]);
        // Return a success message
        echo json_encode(['message' => 'User added successfully']);
    }
?>
