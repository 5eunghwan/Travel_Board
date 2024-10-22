<?php
session_start();
require_once('db_connection.php'); // 데이터베이스 연결 파일 포함

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postnum = $_POST['postnum'];
    $comment_content = $_POST['comment_content'];
    $idnum = $_SESSION['idnum']; // 로그인한 사용자의 ID

    // 댓글 데이터 삽입
    $stmt = $conn->prepare("INSERT INTO Comment (postnum, idnum, comment_content) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $postnum, $idnum, $comment_content);

    if ($stmt->execute()) {
        // 성공적으로 삽입되면 게시물 보기 페이지로 리디렉션
        header("Location: view.php?postnum=" . $postnum);
        exit();
    } else {
        echo "댓글 작성에 실패했습니다.";
    }
}
?>
