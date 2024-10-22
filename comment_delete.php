<?php
session_start();
require_once('db_connection.php');

// 댓글 번호와 게시물 번호가 URL에서 제공되었는지 확인
if (!isset($_GET['commentnum']) || !isset($_GET['postnum'])) {
    echo "잘못된 접근입니다.";
    exit();
}

$commentnum = $_GET['commentnum'];
$postnum = $_GET['postnum'];

// 현재 로그인한 사용자의 ID 가져오기
$idnum = $_SESSION['idnum'] ?? null;

// 댓글 삭제
$stmt = $conn->prepare("DELETE FROM Comment WHERE commentnum = ? AND idnum = ?");
$stmt->bind_param("ii", $commentnum, $idnum);

if ($stmt->execute()) {
    echo "<script>alert('댓글이 삭제되었습니다.'); window.location.href = 'view.php?postnum=$postnum';</script>";
} else {
    echo "<script>alert('댓글 삭제에 실패했습니다.'); window.location.href = 'view.php?postnum=$postnum';</script>";
}
?>
