<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Connect to database
$conn = new mysqli("localhost", "root", "", "attendance_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $course = $_POST['course'];
    $year_level = $_POST['year_level'];
    $date = $_POST['registration_date'];
    $time = $_POST['registration_time'];

    $sql = "INSERT INTO students (last_name, first_name, middle_name, course, year_level, registration_date, registration_time)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $last_name, $first_name, $middle_name, $course, $year_level, $date, $time);

    if ($stmt->execute()) {
        echo "<script>alert('Student added successfully!'); window.location='dashboard.php?page=dashboard';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Student</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <h2>Add New Student</h2>
    <form method="POST" action="">
        <label>Last Name:</label>
        <input type="text" name="last_name" required><br>

        <label>First Name:</label>
        <input type="text" name="first_name" required><br>

        <label>Middle Name:</label>
        <input type="text" name="middle_name"><br>

        <label>Course:</label>
        <input type="text" name="course" required><br>

        <label>Year Level:</label>
        <input type="text" name="year_level" required><br>

        <label>Registration Date:</label>
        <input type="date" name="registration_date" value="<?php echo date('Y-m-d'); ?>" required><br>

        <label>Registration Time:</label>
        <input type="time" name="registration_time" value="<?php echo date('H:i'); ?>" required><br>

        <button type="submit">Add Student</button>
        <a href="dashboard.php?page=dashboard">Cancel</a>
    </form>
</body>
</html>
