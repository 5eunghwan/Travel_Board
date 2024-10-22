<?php
// check_username.php
$servername = "localhost";
$username = "root"; // DB 사용자명
$password = ""; // DB 비밀번호
$dbname = "travel_board"; // DB 이름

// 연결 생성
$conn = new mysqli($servername, $username, $password, $dbname);

// 연결 확인
if ($conn->connect_error) {
    die("연결 실패: " . $conn->connect_error);
}

if (isset($_POST['username'])) {
    $username = $conn->real_escape_string($_POST['username']);
    
    $sql = "SELECT * FROM User WHERE id = '$username'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        echo "중복된 아이디입니다.";
    } else {
        echo "사용 가능한 아이디입니다.";
    }
}

$conn->close();
?>
