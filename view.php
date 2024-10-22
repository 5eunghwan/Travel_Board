<?php
session_start();
require_once('db_connection.php');

// 게시물 번호가 URL에서 제공되었는지 확인
if (!isset($_GET['postnum'])) {
    echo "잘못된 접근입니다.";
    exit();
}

$postnum = $_GET['postnum'];

// 조회수 증가
$stmt_increment = $conn->prepare("UPDATE Post SET hits = hits + 1 WHERE postnum = ?");
$stmt_increment->bind_param("i", $postnum);
$stmt_increment->execute();

// 게시물 정보 가져오기
$stmt = $conn->prepare("SELECT p.*, u.nickname FROM Post p JOIN User u ON p.idnum = u.idnum WHERE p.postnum = ?");
$stmt->bind_param("i", $postnum);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "게시물을 찾을 수 없습니다.";
    exit();
}

$post = $result->fetch_assoc();

// 댓글 가져오기
$stmt_comments = $conn->prepare("SELECT c.*, u.nickname FROM Comment c JOIN User u ON c.idnum = u.idnum WHERE c.postnum = ? ORDER BY c.comment_date DESC");
$stmt_comments->bind_param("i", $postnum);
$stmt_comments->execute();
$comments = $stmt_comments->get_result();
$comment_count = $comments->num_rows; // 댓글 개수 계산

// 현재 사용자가 좋아요를 눌렀는지 확인
$user_idnum = isset($_SESSION['idnum']) ? $_SESSION['idnum'] : null;
$stmt_like_check = $conn->prepare("SELECT * FROM Likes WHERE postnum = ? AND idnum = ?");
$stmt_like_check->bind_param("ii", $postnum, $user_idnum);
$stmt_like_check->execute();
$like_result = $stmt_like_check->get_result();
$is_liked = $like_result->num_rows > 0;

// 좋아요 수 가져오기
$stmt_like_count = $conn->prepare("SELECT COUNT(*) AS like_count FROM Likes WHERE postnum = ?");
$stmt_like_count->bind_param("i", $postnum);
$stmt_like_count->execute();
$like_count_result = $stmt_like_count->get_result();
$like_count = $like_count_result->fetch_assoc()['like_count'];
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - 여행 정보 공유 게시판</title>
    <link rel="stylesheet" href="css/css.css">
    <script>
        function handleLike(postnum) {
            const isLoggedIn = <?php echo json_encode(isset($_SESSION['idnum'])); ?>;
            if (!isLoggedIn) {
                alert("로그인 후 좋아요를 누를 수 있습니다.");
                return;
            }

            const button = document.getElementById(`like-button-${postnum}`);
            const liked = button.classList.toggle('liked');
            const likeCountDisplay = document.getElementById(`like-count-${postnum}`);
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "like_process.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    likeCountDisplay.innerText = response.new_like_count;
                    button.innerText = liked ? '취소' : '좋아요';
                }
            };
            xhr.send("postnum=" + postnum + "&liked=" + liked);
        }
    </script>
</head>
<body>
    <div class="board_wrap">
        <div class="board_title">
            <strong><a href="list.php">여행 정보 공유 게시판</a></strong>
        </div>
        <div class="board_view_wrap">
            <div class="board_view">
            <div class="title">
                <?php echo htmlspecialchars($post['title']); ?> [<strong><?php echo htmlspecialchars($post['category']); ?></strong>]
            </div>
                <div class="info">
                    <dl>
                        <dt>번호</dt>
                        <dd><?php echo $post['postnum']; ?></dd>
                    </dl>
                    <dl>
                        <dt>글쓴이</dt>
                        <dd><?php echo htmlspecialchars($post['nickname']); ?></dd>
                    </dl>
                    <dl>
                        <dt>작성일</dt>
                        <dd><?php echo date("Y.m.d", strtotime($post['postdate'])); ?></dd>
                    </dl>
                    <dl>
                        <dt>조회</dt>
                        <dd><?php echo $post['hits']; ?></dd>
                    </dl>
                    <dl>
                        <dt>좋아요</dt>
                        <dd>
                            <span id="like-count-<?php echo $postnum; ?>"><?php echo $like_count; ?></span>
                            <button id="like-button-<?php echo $postnum; ?>" class="<?php echo $is_liked ? 'liked' : ''; ?>" onclick="handleLike(<?php echo $postnum; ?>)">
                                <?php echo $is_liked ? '취소' : '좋아요'; ?>
                            </button>
                        </dd>
                    </dl>
                </div>
                <div class="cont">
                    <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                </div>
            </div>

            <!-- 댓글 리스트 -->
            <div class="comment_wrap">
                <h3>댓글 (<?php echo $comment_count; ?>)</h3>
                <?php while ($comment = $comments->fetch_assoc()): ?>
                    <div class="comment">
                        <div class="comment_info">
                            <strong><?php echo htmlspecialchars($comment['nickname']); ?></strong>
                            <span><?php echo date("Y.m.d H:i", strtotime($comment['comment_date'])); ?></span>
                            <?php if (isset($_SESSION['idnum']) && $_SESSION['idnum'] === $comment['idnum']): ?>
                                <a href="comment_delete.php?commentnum=<?php echo $comment['commentnum']; ?>&postnum=<?php echo $postnum; ?>" onclick="return confirm('정말로 이 댓글을 삭제하시겠습니까?');">삭제</a>
                            <?php endif; ?>
                        </div>
                        <div class="comment_content">
                            <?php echo nl2br(htmlspecialchars($comment['comment_content'])); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- 댓글 입력 폼 -->
            <div class="comment_form">
                <h4>댓글 작성</h4>
                <form action="comment_process.php" method="POST">
                    <input type="hidden" name="postnum" value="<?php echo $postnum; ?>">
                    <?php if (isset($_SESSION['idnum'])): ?>
                        <textarea name="comment_content" placeholder="댓글을 입력하세요" required></textarea>
                        <button type="submit">작성</button>
                    <?php else: ?>
                        <p>로그인 후 댓글을 작성할 수 있습니다.</p>
                    <?php endif; ?>
                </form>
            </div>

            <!-- 수정 버튼 -->
            <div class="bt_wrap">
                <a href="list.php" class="on">목록</a>
                <?php if (isset($_SESSION['idnum']) && $_SESSION['idnum'] === $post['idnum']): ?>
                   <a href="edit.php?postnum=<?php echo $post['postnum']; ?>">수정</a>
                   <button class="button_style delete_btn" onclick="confirmDelete(<?php echo $post['postnum']; ?>)">삭제</button>
                <?php else: ?>
                   <a href="#" style="pointer-events: none; opacity: 0.5;">수정 (작성자만 가능)</a>
                   <a href="#" style="pointer-events: none; opacity: 0.5;">삭제 (작성자만 가능)</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
    // 삭제 확인 경고
    function confirmDelete(postnum) {
        if (confirm("정말로 이 게시물을 삭제하시겠습니까?")) {
            window.location.href = "delete.php?postnum=" + postnum;
        }
    }
</script>
</body>
</html>
