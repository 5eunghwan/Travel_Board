<?php
session_start();
require_once('db_connection.php');

// 페이지당 게시물 수
$limit = 10; // 페이지당 게시물 수
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // 현재 페이지 번호
$offset = ($page - 1) * $limit; // 오프셋 계산

// 검색어와 옵션 가져오기
$search_option = isset($_GET['search_option']) ? $_GET['search_option'] : 'title';
$search_input = isset($_GET['search_input']) ? $_GET['search_input'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : ''; // 카테고리 추가

// 게시물 총 개수 가져오기
if ($search_input) {
    // 검색어가 있을 때
    $stmt_count = $conn->prepare("SELECT COUNT(*) FROM Post WHERE $search_option LIKE ? AND (category = ? OR ? = '')");
    $search_param = "%$search_input%"; // 검색어를 포함한 패턴
    $stmt_count->bind_param("sss", $search_param, $category, $category);
} else {
    // 검색어가 없을 때
    $stmt_count = $conn->prepare("SELECT COUNT(*) FROM Post WHERE (category = ? OR ? = '')");
    $stmt_count->bind_param("ss", $category, $category);
}

$stmt_count->execute();
$count_result = $stmt_count->get_result();
$total_posts = $count_result->fetch_row()[0]; // 총 게시물 수

// 총 페이지 수 계산
$total_pages = ceil($total_posts / $limit); // 전체 페이지 수 계산

// 게시물 가져오기 쿼리
if ($search_input) {
    $stmt = $conn->prepare("
        SELECT p.*, u.nickname, COALESCE(l.likes_count, 0) AS likes_count, COALESCE(c.comment_count, 0) AS comment_count 
        FROM Post p 
        JOIN User u ON p.idnum = u.idnum 
        LEFT JOIN (SELECT postnum, COUNT(*) AS likes_count FROM Likes GROUP BY postnum) l ON p.postnum = l.postnum 
        LEFT JOIN (SELECT postnum, COUNT(*) AS comment_count FROM Comment GROUP BY postnum) c ON p.postnum = c.postnum
        WHERE $search_option LIKE ? AND (p.category = ? OR ? = '')
        ORDER BY p.postdate DESC
        LIMIT ?, ?
    ");
    $stmt->bind_param("ssiii", $search_param, $category, $category, $offset, $limit);
} else {
    $stmt = $conn->prepare("
        SELECT p.*, u.nickname, COALESCE(l.likes_count, 0) AS likes_count, COALESCE(c.comment_count, 0) AS comment_count 
        FROM Post p 
        JOIN User u ON p.idnum = u.idnum 
        LEFT JOIN (SELECT postnum, COUNT(*) AS likes_count FROM Likes GROUP BY postnum) l ON p.postnum = l.postnum 
        LEFT JOIN (SELECT postnum, COUNT(*) AS comment_count FROM Comment GROUP BY postnum) c ON p.postnum = c.postnum
        WHERE (p.category = ? OR ? = '')
        ORDER BY p.postdate DESC
        LIMIT ?, ?
    ");
    $stmt->bind_param("ssii", $category, $category, $offset, $limit);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>여행 정보 공유 게시판</title>
    <link rel="stylesheet" href="css/css.css">
</head>
<body>
    <div class="header">
        <div class="login_wrap">
              <?php if (isset($_SESSION['nickname'])): ?>
                 <div class="welcome-message">
                   <span class="welcome-text"><?php echo htmlspecialchars($_SESSION['nickname']); ?>님 환영합니다!</span>
                   <a href="logout.php" class="logout">로그아웃</a>
                   </div>
              <?php else: ?>
         <a href="login.php" class="login">로그인</a>
          <a href="signup.php" class="signup">회원가입</a>
<?php endif; ?>
        </div>
    </div>
    
    <div class="board_wrap">
        <div class="board_title">
            <a href="list.php">
                <strong>여행 정보 공유 게시판</strong>
            </a>
        </div>
        <div class="category_wrap">
            <a href="list.php?category=여행후기" class="category_item <?php echo $category === '여행후기' ? 'active' : ''; ?>">여행 후기</a>
            <a href="list.php?category=여행정보" class="category_item <?php echo $category === '여행정보' ? 'active' : ''; ?>">여행 정보</a>
            <a href="list.php?category=여행팁" class="category_item <?php echo $category === '여행팁' ? 'active' : ''; ?>">여행 팁</a>
            <a href="list.php" class="category_item <?php echo !$category ? 'active' : ''; ?>">전체보기</a> <!-- 전체보기 추가 -->
        </div>

        <!-- 검색창 추가 -->
        <div class="search_wrap">
            <div class="search_container">
                <form action="list.php" method="GET">
                    <select name="search_option" id="search_option">
                        <option value="title" <?php echo $search_option === 'title' ? 'selected' : ''; ?>>제목</option>
                        <option value="content" <?php echo $search_option === 'content' ? 'selected' : ''; ?>>내용</option>
                    </select>
                    <input type="text" name="search_input" id="search_input" placeholder="검색어를 입력하세요" value="<?php echo htmlspecialchars($search_input); ?>">
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>"> <!-- 카테고리 유지 -->
                    <button type="submit" id="search_btn">검색</button>
                </form>
            </div>
        </div>

        <div class="board_list_wrap">
            <div class="board_list">
                <div class="top">
                    <div class="num">글 번호</div>
                    <div class="title">제목</div>
                    <div class="writer">글쓴이</div>
                    <div class="date">작성일</div>
                    <div class="hits">조회</div>
                    <div class="likes">좋아요</div>
                </div>

                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div>
                            <div class="num"><?php echo htmlspecialchars($row['postnum']); ?></div>
                            <div class="title">
                                <a href="view.php?postnum=<?php echo htmlspecialchars($row['postnum']); ?>">
                                    <?php echo htmlspecialchars($row['title']); ?> [<?php echo $row['comment_count']; ?>]
                                </a>
                            </div>
                            <div class="writer"><?php echo htmlspecialchars($row['nickname']); ?></div>
                            <div class="date"><?php echo date('Y.m.d', strtotime($row['postdate'])); ?></div>
                            <div class="hits"><?php echo htmlspecialchars($row['hits']); ?></div>
                            <div class="likes"><?php echo htmlspecialchars($row['likes_count']); ?></div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no_data">게시물이 없습니다.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bt_wrap">
             <?php if (isset($_SESSION['idnum'])): // 로그인 상태일 때 ?>
               <a href="write.php" class="write_button">글 작성</a>
            <?php endif; ?>
        </div>

        <div class="pagination">
          <!-- 이전 페이지 버튼 -->
          <a href="list.php?page=<?php echo max(1, $page - 1); ?>&search_option=<?php echo $search_option; ?>&search_input=<?php echo urlencode($search_input); ?>&category=<?php echo urlencode($category); ?>" class="prev-button">◀ 이전</a>

          <!-- 페이지 번호 -->
          <?php for ($i = 1; $i <= $total_pages; $i++): ?>
          <a href="list.php?page=<?php echo $i; ?>&search_option=<?php echo $search_option; ?>&search_input=<?php echo urlencode($search_input); ?>&category=<?php echo urlencode($category); ?>" 
           class="<?php echo $i === $page ? 'active' : ''; ?>">
            <?php echo $i; ?>
           </a>
           <?php endfor; ?>

         <!-- 다음 페이지 버튼 -->
           <a href="list.php?page=<?php echo min($total_pages, $page + 1); ?>&search_option=<?php echo $search_option; ?>&search_input=<?php echo urlencode($search_input); ?>&category=<?php echo urlencode($category); ?>" class="next-button">다음 ▶</a>
        </div>
    </div>
</body>
</html>
