<?php
session_start();
require_once 'db_connection.php'; // 데이터베이스 연결 파일

// 로그인 체크
if (!isset($_SESSION['idnum'])) {
    header("Location: login.php");
    exit();
}

// POST 데이터 가져오기
$idnum = $_SESSION['idnum'];
$category = $_POST['category'];
$title = $_POST['title'];
$content = $_POST['content'];

// SQL 쿼리 준비
$stmt = $conn->prepare("INSERT INTO Post (idnum, category, title, content) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isss", $idnum, $category, $title, $content);

// 쿼리 실행
if ($stmt->execute()) {
    echo "<script>alert('글 작성이 완료되었습니다.'); location.href='list.php';</script>";
} else {
    echo "<script>alert('글 작성에 실패하였습니다.'); history.back();</script>";
}

// 자원 해제
$stmt->close();
$conn->close();
?>
