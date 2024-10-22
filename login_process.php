<?php
session_start();
require_once 'db_connection.php'; // db_connection.php 파일을 포함합니다.

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id']; // 'username'을 가져옵니다.
    $password = $_POST['password'];

    // 비밀번호 해시 확인을 위한 쿼리
    $stmt = $conn->prepare("SELECT idnum, nickname, password FROM User WHERE id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // 비밀번호 확인
        if (password_verify($password, $user['password'])) {
            // 로그인 성공
            $_SESSION['idnum'] = $user['idnum'];  // 사용자 ID 저장
            $_SESSION['nickname'] = $user['nickname'];  // 닉네임 저장
            header("Location: list.php"); // 메인 페이지로 리디렉션
            exit(); // 스크립트 종료
        } else {
            echo "<script>alert('비밀번호가 올바르지 않습니다.'); history.back();</script>";
        }
    } else {
        echo "<script>alert('아이디가 존재하지 않습니다.'); history.back();</script>";
    }
}
?>
