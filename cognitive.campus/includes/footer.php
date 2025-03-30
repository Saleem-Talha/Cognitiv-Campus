
<style>
.ai-btn {
    width: 46px !important;
    height: 46px !important;
    padding: 20px;
    border-radius: 50% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    box-shadow: 0 2px 20px 4px rgba(105, 108, 255, 0.6) !important;
    background: #696cff !important;
    transition: all 0.2s ease-in-out !important;
    position: fixed !important;
}

.ai-btn i {
    font-size: 22px !important;
    line-height: 1 !important;
}

.chat-with-ai .ai-btn {
    right: 100px !important;
    bottom: 3.3rem !important;
}

.buy-now .ai-btn {
    right: 2rem !important;
    bottom: 4rem !important;
}
</style>


<div class="chat-with-ai">   
    <a href="ai-chat.php" class="btn btn-primary ai-btn" style="text-decoration:none;"> <i class="bx bx-message-dots"></i></a>
</div>


<!-- Plus Button -->
<div class="buy-now">
    <div class="dropup">
        <button class="btn btn-primary btn-buy-now" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bx bx-plus"></i>
        </button>
        <ul class="dropdown-menu mb-3 border" aria-labelledby="dropdownMenuButton">
            <li><a class="dropdown-item" href="notes-projects.php">Projects</a></li>
            <li><a class="dropdown-item" href="notes-course.php">Course</a></li>
        </ul>
    </div>
</div>


<!-- Footer -->
<footer class="content-footer footer bg-footer-theme">
    <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
        <div class="mb-2 mb-md-0">
            ©
            <script>
                document.write(new Date().getFullYear());
            </script>
            All rights reserved by <b>Cognitive Campus</b>
        </div>
    </div>
</footer>