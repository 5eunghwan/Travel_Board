<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>로그인</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="board_wrap"></div>
    <div class="login_container">
        <h2>회원 로그인</h2>
        <form action="login_process.php" method="POST">
            <div class="input_group">
                <label for="username">아이디</label>
                <input type="text" id="username" name="id" required> <!-- name을 id로 변경 -->
            </div>
            <div class="input_group">
                <label for="password">비밀번호</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="login_btn">로그인</button>
            
        </form>
        <div class="bt_wrap">
         <a href="list.php" class="button_style">홈으로</a> <!-- 목록 보기 버튼 -->
         </div>

        <div class="login_footer">
            <p>계정이 없으신가요? <a href="signup.php">회원가입</a></p>
        </div>
    </div>
</body>
</html>
