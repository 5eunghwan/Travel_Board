<?php
$servername = "localhost"; // 데이터베이스 서버
$username = "root"; // 데이터베이스 사용자 이름
$password = ""; // 데이터베이스 비밀번호 (XAMPP 기본값: 빈 문자열)
$dbname = "travel_board"; // 사용할 데이터베이스 이름

// 데이터베이스 연결
$conn = new mysqli($servername, $username, $password, $dbname);

// 연결 오류 체크
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
