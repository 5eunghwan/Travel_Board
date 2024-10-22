<?php
session_start(); // 세션 시작
require_once('db_connection.php'); // 데이터베이스 연결 파일 포함

// 사용자가 로그인했는지 확인
if (!isset($_SESSION['idnum'])) {
    header("Location: login.php"); // 로그인하지 않았다면 로그인 페이지로 리다이렉트
    exit();
}

// POST 요청인지 확인
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 폼 데이터 가져오기
    $idnum = $_SESSION['idnum'];
    $category = $_POST['category'];
    $title = $_POST['title'];
    $content = $_POST['content'];

    // 데이터베이스에 데이터 삽입
    $stmt = $conn->prepare("INSERT INTO Post (idnum, category, title, content) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $idnum, $category, $title, $content); // 데이터 타입 정의

    if ($stmt->execute()) {
        header("Location: list.php"); // 성공적으로 작성된 후 목록 페이지로 리다이렉트
        exit();
    } else {
        echo "게시물 작성 중 오류가 발생했습니다: " . $stmt->error; // 오류 메시지 출력
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>글 작성</title>
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
            <form action="write.php" method="POST"> <!-- 폼 태그 추가 -->
                <div class="board_write">
                    <div class="title">
                        <dl>
                            <dt>제목</dt>
                            <dd><input type="text" name="title" placeholder="제목 입력" required></dd>
                        </dl>
                    </div>
                    <div class="info">
                        <dl>
                            <dt>카테고리</dt>
                            <dd>
                                <select name="category" id="category" required>
                                    <option value="여행후기">여행 후기</option>
                                    <option value="여행정보">여행 정보</option>
                                    <option value="여행팁">여행 팁</option>
                                </select>
                            </dd>
                        </dl>
                    </div>
                    <div class="cont">
                        <textarea name="content" placeholder="내용 입력" required></textarea>
                    </div>
                </div>
                <div class="bt_wrap">
                  <button type="submit" class="button_style write_btn">작성</button> <!-- 폼 제출 버튼 -->
                  <a href="list.php" class="button_style cancel_btn">취소</a>
                </div>
            </form> <!-- 폼 태그 닫기 -->
        </div>
    </div>
</body>
</html>
