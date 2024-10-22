<?php
session_start();
require_once 'db_connection.php'; // 데이터베이스 연결

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // 비밀번호 해싱
    $nickname = $_POST['nickname'];

    // 중복 아이디 확인
    $stmt = $conn->prepare("SELECT * FROM User WHERE id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('이미 사용 중인 아이디입니다.'); history.back();</script>";
    } else {
        // 회원가입
        $stmt = $conn->prepare("INSERT INTO User (id, password, nickname) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $id, $password, $nickname);

        if ($stmt->execute()) {
            // 회원가입 성공 후 로그인 화면으로 리디렉션
            echo "<script>alert('회원가입이 완료되었습니다. 로그인해주세요.'); window.location.href='login.php';</script>";
        } else {
            echo "<script>alert('회원가입에 실패했습니다.'); history.back();</script>";
        }
    }
}
?>
