<!-- HTML -->
<div class="card mb-4">
  <div class="d-flex align-items-end row">
    <div class="col-sm-7">
      <div class="card-body">
        <h5 class="card-title text-primary">Welcome</h5>
        <div class="d-flex align-items-center mb-3">
          <div class="avatar avatar-online me-2">
            <img src="<?php echo $userProfile; ?>" alt class="w-px-60 h-60 rounded-circle img-fluid" />
          </div>   
          <h3 class="fw-medium d-block text-primary mb-0"><?php echo $userName ?></h3>
        </div>  
        <div class="quote-container">
          <p class="mb-4 quote-text" id="rotating-quote"></p>
        </div>
        <a href="ai-chat.php" class="btn btn-sm btn-outline-primary">Chat with AI</a>
      </div>
    </div>
    <div class="col-sm-5 text-center text-sm-left">
      <div class="card-body pb-0 px-0 px-md-4">
        <img src="assets/img/illustrations/girl-doing-yoga-light.png" height="140" alt="View Badge User" 
             data-app-dark-img="illustrations/man-with-laptop-dark.png" 
             data-app-light-img="illustrations/man-with-laptop-light.png" />
      </div>
    </div>
  </div>
</div>

<!-- Add this style section in your head tag or stylesheet -->
<style>
.quote-container {
    position: relative;
    min-height: 80px;
    display: flex;
    align-items: center;
}

.quote-text {
    opacity: 1;
    position: absolute;
    transition: all 0.5s ease-in-out;
    width: 100%;
}

.quote-text.fade-out {
    opacity: 0;
    transform: translateY(20px);
}

.quote-text.fade-in {
    opacity: 1;
    transform: translateY(0);
}

/* Adjust avatar and name alignment */
.avatar img {
    object-fit: cover;
}

.d-flex.align-items-center {
    justify-content: flex-start;
    width: 100%;
}
</style>

<!-- Add this script section at the end of your body tag -->
<script>
const quotes = [
    {
        text: "The only way to do great work is to love what you do.",
        author: "Steve Jobs"
    },
    {
        text: "Success is not final, failure is not fatal: it is the courage to continue that counts.",
        author: "Winston Churchill"
    },
    {
        text: "Don't watch the clock; do what it does. Keep going.",
        author: "Sam Levenson"
    },
    {
        text: "The future depends on what you do today.",
        author: "Mahatma Gandhi"
    },
    {
        text: "Focus on being productive instead of busy.",
        author: "Tim Ferriss"
    }
];

let currentQuoteIndex = 0;

function displayQuote() {
    const quoteElement = document.getElementById('rotating-quote');
    
    // Add fade-out class
    quoteElement.classList.add('fade-out');
    
    // Wait for fade-out animation to complete
    setTimeout(() => {
        // Update quote text
        currentQuoteIndex = (currentQuoteIndex + 1) % quotes.length;
        const quote = quotes[currentQuoteIndex];
        quoteElement.innerHTML = `"${quote.text}" - ${quote.author}`;
        
        // Remove fade-out and add fade-in
        quoteElement.classList.remove('fade-out');
        quoteElement.classList.add('fade-in');
        
        // Remove fade-in class after animation completes
        setTimeout(() => {
            quoteElement.classList.remove('fade-in');
        }, 500);
    }, 500);
}

// Display initial quote
const initialQuote = quotes[0];
document.getElementById('rotating-quote').innerHTML = `"${initialQuote.text}" - ${initialQuote.author}`;

// Rotate quotes every 7 seconds
setInterval(displayQuote, 7000);
</script>