<?php
session_start();
require_once('db_connection.php');

// 로그인 여부 확인
if (!isset($_SESSION['idnum'])) {
    echo json_encode(['success' => false, 'message' => '로그인 후 좋아요를 누를 수 있습니다.']);
    exit();
}

// POST 요청으로 게시물 번호와 좋아요 상태를 받아옴
if (isset($_POST['postnum']) && isset($_POST['liked'])) {
    $postnum = intval($_POST['postnum']);
    $liked = filter_var($_POST['liked'], FILTER_VALIDATE_BOOLEAN); // true 또는 false로 변환
    $idnum = $_SESSION['idnum'];

    if ($liked) {
        // 좋아요 추가
        $stmt = $conn->prepare("INSERT INTO Likes (postnum, idnum) VALUES (?, ?)");
        $stmt->bind_param("ii", $postnum, $idnum);
        if ($stmt->execute()) {
            // 좋아요 수 업데이트
            $stmt_like_count = $conn->prepare("SELECT COUNT(*) AS like_count FROM Likes WHERE postnum = ?");
            $stmt_like_count->bind_param("i", $postnum);
            $stmt_like_count->execute();
            $result = $stmt_like_count->get_result();
            $like_count = $result->fetch_assoc()['like_count'];

            // Post 테이블의 좋아요 수 업데이트
            $stmt_update_post = $conn->prepare("UPDATE Post SET likes_count = ? WHERE postnum = ?");
            $stmt_update_post->bind_param("ii", $like_count, $postnum);
            $stmt_update_post->execute();

            echo json_encode(['success' => true, 'new_like_count' => $like_count]);
        } else {
            echo json_encode(['success' => false, 'message' => '좋아요 처리에 실패했습니다.']);
        }
    } else {
        // 좋아요 취소
        $stmt = $conn->prepare("DELETE FROM Likes WHERE postnum = ? AND idnum = ?");
        $stmt->bind_param("ii", $postnum, $idnum);
        if ($stmt->execute()) {
            // 좋아요 수 업데이트
            $stmt_like_count = $conn->prepare("SELECT COUNT(*) AS like_count FROM Likes WHERE postnum = ?");
            $stmt_like_count->bind_param("i", $postnum);
            $stmt_like_count->execute();
            $result = $stmt_like_count->get_result();
            $like_count = $result->fetch_assoc()['like_count'];

            // Post 테이블의 좋아요 수 업데이트
            $stmt_update_post = $conn->prepare("UPDATE Post SET likes_count = ? WHERE postnum = ?");
            $stmt_update_post->bind_param("ii", $like_count, $postnum);
            $stmt_update_post->execute();

            echo json_encode(['success' => true, 'new_like_count' => $like_count]);
        } else {
            echo json_encode(['success' => false, 'message' => '좋아요 취소에 실패했습니다.']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
}
?>
