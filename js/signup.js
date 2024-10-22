function checkUsername() {
    const username = document.getElementById('username').value;
    
    // AJAX 요청 생성
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'check_username.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        document.getElementById('username_msg').textContent = this.responseText; // 결과를 표시
    };
    xhr.send('username=' + username);
}

function validateForm() {
    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('password_confirm').value;

    if (password !== passwordConfirm) {
        document.getElementById('password_msg').textContent = "비밀번호가 일치하지 않습니다.";
        return false;
    }
    return true;
}
