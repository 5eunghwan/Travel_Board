<?php
session_start(); // 세션 시작
require_once('db_connection.php'); // 데이터베이스 연결 파일 포함

// 게시물 번호가 URL에서 제공되었는지 확인
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
    echo "권한이 없습니다. 수정할 수 없습니다.";
    exit();
}

$post = $result->fetch_assoc(); // 게시물 정보 가져오기

// POST 요청 처리: 수정된 데이터를 저장
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category = $_POST['category'];
    $title = $_POST['title'];
    $content = $_POST['content'];

    // 게시물 업데이트
    $stmt_update = $conn->prepare("UPDATE Post SET category = ?, title = ?, content = ? WHERE postnum = ? AND idnum = ?");
    $stmt_update->bind_param("sssii", $category, $title, $content, $postnum, $idnum);

    if ($stmt_update->execute()) {
        header("Location: view.php?postnum=" . $postnum); // 수정 후 해당 게시물 보기 페이지로 이동
        exit();
    } else {
        echo "게시물 수정 중 오류가 발생했습니다: " . $stmt_update->error;
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>글 수정</title>
    <link rel="stylesheet" href="css/css.css">
</head>
<body>
    <div class="board_wrap">
        <div class="board_title">
            <a href="list.php">
                <strong>여행 정보 공유 게시판</strong>
            </a>
        </div>
        <div class="board_write_wrap">
            <form action="edit.php?postnum=<?php echo $postnum; ?>" method="POST"> <!-- 수정된 데이터를 다시 현재 페이지로 POST -->
                <div class="board_write">
                    <div class="title">
                        <dl>
                            <dt>제목</dt>
                            <dd><input type="text" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required></dd>
                        </dl>
                    </div>
                    <div class="info">
                        <dl>
                            <dt>카테고리</dt>
                            <dd>
                                <select name="category" id="category" required>
                                    <option value="여행후기" <?php echo $post['category'] == '여행후기' ? 'selected' : ''; ?>>여행 후기</option>
                                    <option value="여행정보" <?php echo $post['category'] == '여행정보' ? 'selected' : ''; ?>>여행 정보</option>
                                    <option value="여행팁" <?php echo $post['category'] == '여행팁' ? 'selected' : ''; ?>>여행 팁</option>
                                </select>
                            </dd>
                        </dl>
                    </div>
                    <div class="cont">
                        <textarea name="content" required><?php echo htmlspecialchars($post['content']); ?></textarea>
                    </div>
                </div>
                <div class="bt_wrap">
                    <button type="submit" class="button_style write_btn">수정</button> <!-- 수정 제출 버튼 -->
                    <a href="view.php?postnum=<?php echo $postnum; ?>" class="button_style cancel_btn">취소</a> <!-- 취소 버튼 -->
                </div>
            </form>
        </div>
    </div>
</body>
</html>
