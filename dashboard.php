<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// DB connection
$conn = new mysqli("localhost", "root", "", "attendance_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Add Student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_student'])) {
    $school_id = $_POST['school_id'];
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $course = $_POST['course'];
    $year_level = $_POST['year_level'];
    date_default_timezone_set('Asia/Manila');
    $date = date('Y-m-d');
    $time = date('H:i');

   $sql = "INSERT INTO students (school_id, last_name, first_name, middle_name, course, year_level, registration_date, registration_time)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssss", $school_id, $last_name, $first_name, $middle_name, $course, $year_level, $date, $time);

    if ($stmt->execute()) {
        echo "<script>alert('Student added successfully!'); window.location='dashboard.php?page=list';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM students WHERE id=$id");
    echo "<script>alert('Student deleted successfully!'); window.location='dashboard.php?page=list';</script>";
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_student'])) {
    $id = $_POST['id'];
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $course = $_POST['course'];
    $year_level = $_POST['year_level'];

    $sql = "UPDATE students SET last_name=?, first_name=?, middle_name=?, course=?, year_level=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $last_name, $first_name, $middle_name, $course, $year_level, $id);

    if ($stmt->execute()) {
        echo "<script>alert('Student updated successfully!'); window.location='dashboard.php?page=list';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance System</title>
    <link rel="stylesheet" href="dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <ul class="menu-list">
        <li>
            <a href="dashboard.php?page=dashboard" class="menu-item" data-page="dashboard">
                <i class="fas fa-home"></i><span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="dashboard.php?page=attendance" class="menu-item" data-page="attendance">
                <i class="fas fa-qrcode"></i><span>QR Code Attendance</span>
            </a>
        </li>
        <li>
            <a href="dashboard.php?page=event" class="menu-item" data-page="event">
                <i class="fas fa-calendar"></i><span>Event</span>
            </a>
        </li>
    </ul>
    <ul class="logout-list">
        <li>
            <a href="logout.php" class="menu-item logout-btn" data-page="logout">
                <i class="fas fa-sign-out-alt"></i><span>Logout</span>
            </a>
        </li>
    </ul>
</div>

<!-- Main Content -->
<div class="content">
<?php if ($page == "dashboard") { ?>
    
    <h1>Dashboard</h1>
    
    
    <p>Welcome to the Attendance System Dashboard.</p>
    

    <!-- Student Management Cards -->
    <div class="options">
        <div class="card" onclick="window.location='dashboard.php?page=list'">📋 Student Overview</div>
        
    </div>
    <?php
// Fetch total students
$total_students = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'];

// Fetch count per course
$course_data = $conn->query("SELECT course, COUNT(*) as count FROM students GROUP BY course");
?>

<h2>📊 Course Analysis</h2>
<table class="analysis-table">
    <tr>
        <th>Course</th>
        <th>Students</th>
        <th>Percentage</th>
    </tr>
    <?php 
    if ($course_data->num_rows > 0) {
        while ($row = $course_data->fetch_assoc()) {
            $percentage = ($total_students > 0) ? round(($row['count'] / $total_students) * 100, 2) : 0;
            echo "<tr>
                    <td>".$row['course']."</td>
                    <td>".$row['count']."</td>
                    <td>".$percentage."%</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='3'>No data available</td></tr>";
    }
    ?>

    

<?php } elseif ($page == "list") { ?>
    <h1><i class="fas fa-users"></i> Student List</h1>
    <a href="dashboard.php?page=dashboard" class="back-btn">
    <i class="fas fa-arrow-left"></i> 
    
</a>


    <table>
   <table>
<tr>
    <th>No.</th><th>School ID</th><th>Last Name</th><th>First Name</th><th>Middle Name</th>
    <th>Course</th><th>Year</th><th>Date</th><th>Time</th><th>Actions</th>
</tr>


        <?php
        $sql = "SELECT * FROM students ORDER BY id ASC";
        $result = $conn->query($sql);
        $no = 1;
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
               echo "<tr>
        <td>".$no++."</td>
        <td>".$row['school_id']."</td>
        <td>".$row['last_name']."</td>
        <td>".$row['first_name']."</td>
        <td>".$row['middle_name']."</td>
        <td>".$row['course']."</td>
        <td>".$row['year_level']."</td>
        <td>".$row['registration_date']."</td>
        <td>".$row['registration_time']."</td>
        <td>
            <a href='dashboard.php?page=edit&id=".$row['id']."'>✏️</a>
            <a href='dashboard.php?delete=".$row['id']."' onclick=\"return confirm('Delete student?');\">🗑️</a>
        </td>
    </tr>";

            }
        } else {
            echo "<tr><td colspan='9'>No students found</td></tr>";
        }
        ?>
    </table>

    <!-- Add Student Modal -->
<div id="addStudentModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="document.getElementById('addStudentModal').style.display='none'">&times;</span>
    <h2><i class="fas fa-user-plus"></i> Add Student</h2>
    <form method="POST" action="" class="form-box">
        <input type="hidden" name="add_student" value="1">
        <input type="text" name="school_id" placeholder="School ID" required>
        <input type="text" name="last_name" placeholder="Last Name" required>
        <input type="text" name="first_name" placeholder="First Name" required>
        <input type="text" name="middle_name" placeholder="Middle Name">
        
        <select name="course" required>
            <option value="" disabled selected>Select Course</option>
            <option value="BSIT">BSIT</option>
            <option value="BSBA">BSBA</option>
            <option value="BSED">BSED</option>
            <option value="BEED">BEED</option>
            <option value="BSHM">BSHM</option>
            <option value="BSCRIM">BSED-GENSCI</option>
        </select>
        
        <select name="year_level" required>
            <option value="" disabled selected>Select Year Level</option>
            <option value="1st Year">1st Year</option>
            <option value="2nd Year">2nd Year</option>
            <option value="3rd Year">3rd Year</option>
            <option value="4th Year">4th Year</option>
        </select>
        
        <button type="submit">Add Student</button>
    </form>
  </div>
</div>

    <!-- Extra Row with Add Student Button -->

<tr>
    <td colspan="9" style="text-align:center; padding:15px;">
        <button onclick="document.getElementById('addStudentModal').style.display='block'" class="add-student-inline">
            ➕ Register Student
        </button>
    </td>
</tr>






<?php } elseif ($page == "edit" && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $edit = $conn->query("SELECT * FROM students WHERE id=$id")->fetch_assoc();
?>
    <h1><i class="fas fa-edit"></i> Edit Student</h1>
    <form method="POST" action="" class="form-box">
        <input type="hidden" name="update_student" value="1">
        <input type="hidden" name="id" value="<?php echo $edit['id']; ?>">
        <input type="text" name="school_id" value="<?php echo $edit['school_id']; ?>" readonly>
        <input type="text" name="last_name" value="<?php echo $edit['last_name']; ?>" required>
        <input type="text" name="first_name" value="<?php echo $edit['first_name']; ?>" required>
        <input type="text" name="middle_name" value="<?php echo $edit['middle_name']; ?>">
        <select name="course" required>
            <option value="BSIT" <?php if($edit['course']=="BSIT") echo "selected"; ?>>BSIT</option>
            <option value="BSBA" <?php if($edit['course']=="BSBA") echo "selected"; ?>>BSBA</option>
            <option value="BSED" <?php if($edit['course']=="BSED") echo "selected"; ?>>BSED</option>
            <option value="BEED" <?php if($edit['course']=="BEED") echo "selected"; ?>>BEED</option>
            <option value="BSHM" <?php if($edit['course']=="BSHM") echo "selected"; ?>>BSHM</option>
            <option value="BSCRIM" <?php if($edit['course']=="BSCRIM") echo "selected"; ?>>BSCRIM</option>
        </select>
        <select name="year_level" required>
            <option value="1st Year" <?php if($edit['year_level']=="1st Year") echo "selected"; ?>>1st Year</option>
            <option value="2nd Year" <?php if($edit['year_level']=="2nd Year") echo "selected"; ?>>2nd Year</option>
            <option value="3rd Year" <?php if($edit['year_level']=="3rd Year") echo "selected"; ?>>3rd Year</option>
            <option value="4th Year" <?php if($edit['year_level']=="4th Year") echo "selected"; ?>>4th Year</option>
        </select>
        <button type="submit">Update Student</button>
    </form>

<?php } elseif ($page == "attendance") { ?>
    <h1><i class="fas fa-qrcode"></i> QR Code Attendance</h1>
    <div class="card">📷 QR Scanner Placeholder</div>
    <div class="card">📜 Attendance Logs</div>

<?php } elseif ($page == "event") { ?>
    <h1><i class="fas fa-calendar"></i> Event Management</h1>
    <div class="card">➕ Add New Event</div>
    <div class="card">📅 Upcoming Events</div>
    <div class="card">📝 Event Reports</div>
<?php } ?>
</div>

<!-- JavaScript -->
<script>
const sidebar = document.getElementById("sidebar");
sidebar.classList.add("expanded");
sidebar.addEventListener("mouseenter", () => sidebar.classList.add("expanded"));
sidebar.addEventListener("mouseleave", () => sidebar.classList.remove("expanded"));
const urlParams = new URLSearchParams(window.location.search);
const currentPage = urlParams.get("page") || "dashboard";
document.querySelectorAll(".sidebar .menu-item").forEach(link => {
    if (link.dataset.page === currentPage) link.classList.add("active");
});
document.querySelector(".logout-btn").addEventListener("click", function(e) {
    e.preventDefault();
    if (confirm("Are you sure you want to log out?")) {
        window.location.href = this.getAttribute("href");
    }
});


</script>

</body>
</html>
<?php $conn->close(); ?>
