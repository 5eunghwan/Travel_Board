<?php
session_start();
require_once('db_connection.php');

// 게시물 번호가 전달되었는지 확인
if (!isset($_GET['postnum'])) {
    echo "잘못된 접근입니다.";
    exit();
}

$postnum = $_GET['postnum'];

// 사용자가 로그인했는지 확인
if (!isset($_SESSION['idnum'])) {
    header("Location: login.php"); // 로그인하지 않았다면 로그인 페이지로 리다이렉트
    exit();
}

// 게시물 작성자인지 확인
$idnum = $_SESSION['idnum'];
$stmt = $conn->prepare("SELECT * FROM Post WHERE postnum = ? AND idnum = ?");
$stmt->bind_param("ii", $postnum, $idnum);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "권한이 없습니다. 게시물을 삭제할 수 없습니다.";
    exit();
}

// 1. 댓글 삭제
$stmt_delete_comments = $conn->prepare("DELETE FROM Comment WHERE postnum = ?");
$stmt_delete_comments->bind_param("i", $postnum);
$stmt_delete_comments->execute();

// 2. 좋아요 기록 삭제
$stmt_delete_likes = $conn->prepare("DELETE FROM Likes WHERE postnum = ?"); // $ 기호 추가
$stmt_delete_likes->bind_param("i", $postnum);
$stmt_delete_likes->execute();

// 3. 게시물 삭제
$stmt_delete_post = $conn->prepare("DELETE FROM Post WHERE postnum = ?");
$stmt_delete_post->bind_param("i", $postnum);

if ($stmt_delete_post->execute()) {
    header("Location: list.php"); // 성공적으로 삭제 후 목록 페이지로 리다이렉트
    exit();
} else {
    echo "게시물 삭제 중 오류가 발생했습니다: " . $stmt_delete_post->error;
}
?>
