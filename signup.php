<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원가입</title>
    <link rel="stylesheet" href="css/signup.css">
    <script src="js/signup.js" defer></script>
</head>
<body>
    <div class="signup_container">
        <h2>회원가입</h2>
        
        <form action="signup_process.php" method="POST" onsubmit="return validateForm()">
            <div class="input_group">
                <label for="username">아이디</label>
                <input type="text" id="username" name="id" required>
                <button type="button" class="check_btn" onclick="checkUsername()">중복 확인</button>
                <p id="username_msg" class="error_msg"></p>
            </div>
            <div class="input_group">
                <label for="password">비밀번호</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="input_group">
                <label for="password_confirm">비밀번호 확인</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
                <p id="password_msg" class="error_msg"></p>
            </div>
            <div class="input_group">
                <label for="nickname">닉네임</label>
                <input type="text" id="nickname" name="nickname" required>
            </div>
            <button type="submit" class="signup_btn">회원가입</button>
        </form>
        <div class="signup_footer">
            <p>이미 계정이 있으신가요? <a href="login.php">로그인</a></p>
        </div>
    </div>
</body>
</html>
