
<?php
require_once 'vendor/autoload.php';
require_once 'includes/db-connect.php';

use Dotenv\Dotenv;

try {

    $clientId = GOOGLE_CLIENT_ID;
    $clientSecret = GOOGLE_CLIENT_SECRET;
    $redirectUri = GOOGLE_REDIRECT_URI;
    
    if (!$clientId || !$clientSecret || !$redirectUri) {
        throw new Exception('Required OAuth credentials not found in environment variables');
    }
    
    $client = new Google_Client();
    $client->setClientId($clientId);
    $client->setClientSecret($clientSecret);
    $client->setRedirectUri($redirectUri);
    
    // Add all required scopes
    $client->addScope(Google_Service_Classroom::CLASSROOM_COURSES);
    $client->addScope(Google_Service_Classroom::CLASSROOM_ANNOUNCEMENTS);
    $client->addScope(Google_Service_Classroom::CLASSROOM_COURSEWORK_STUDENTS);
    $client->addScope(Google_Service_Classroom::CLASSROOM_COURSEWORK_ME);
    $client->addScope(Google_Service_Classroom::CLASSROOM_ROSTERS);
    $client->addScope(Google_Service_Classroom::CLASSROOM_PROFILE_EMAILS);
    $client->addScope(Google_Service_Classroom::CLASSROOM_PROFILE_PHOTOS);
    $client->addScope(Google_Service_Classroom::CLASSROOM_GUARDIANLINKS_STUDENTS);
    $client->addScope(Google_Service_Classroom::CLASSROOM_COURSEWORKMATERIALS);
    $client->addScope(Google_Service_Classroom::CLASSROOM_TOPICS);
    $client->addScope(Google_Service_Classroom::CLASSROOM_STUDENT_SUBMISSIONS_ME_READONLY);
    $client->addScope(Google_Service_Classroom::CLASSROOM_PUSH_NOTIFICATIONS);
    $client->addScope(Google_Service_Drive::DRIVE_FILE);
    $client->addScope('email');
    $client->addScope('profile');
    $client->addScope('openid');
    
    $authUrl = $client->createAuthUrl();
    
} catch (Exception $e) {
    error_log('OAuth Setup Error: ' . $e->getMessage());
    die('Configuration Error: ' . $e->getMessage());
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cognitive Campus</title>


    <!-- Bootstrap core CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <!-- Custom styles for this template -->
    <style>
        * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root {
    --black-color: #2b2a2a;
    --white-color: #f2f2f2;
    --primary-color: #5f61e6;
    --bg-opacity-primary: #5f61e641;
    --heading-title: #566a7f;
    --container-bg: #f5f5f9;
}


body{
     font-family: "Poppins", sans-serif;
}


/* width */
::-webkit-scrollbar {
    width: 3px;
  }
  
  /* Track */
  ::-webkit-scrollbar-track {
    background: #f1f1f1;
  }
  
  /* Handle */
  ::-webkit-scrollbar-thumb {
    background: var(--primary-color)
  }
  
  /* Handle on hover */
  ::-webkit-scrollbar-thumb:hover {
    background: #555;
  }



.bg-primary-main{
    background-color: var(--primary-color);
    color: white;
    transition: 0.3s;
}

.bg-primary-main:hover{
    background-color: var(--black-color);
    color: white;
    transition: 0.3s;
}


.bg-container{
    background-color: var(--container-bg);
}

.text-primary-main{
    color: var(--primary-color);
}

.label{
    background-color: var(--bg-opacity-primary);
    color: var(--primary-color);
}

.heading-title{
    color: var(--heading-title);
}

.bg-main-color {
    background: rgba(255, 255, 255, 0.8); /* Semi-transparent white for glass effect */
    backdrop-filter: blur(10px); /* Glass effect */
    transition: background-color 0.3s; /* Smooth transition for the background color */
    border: 1px solid white;
}

/* Style for when the navbar is scrolled */
.bg-main-color.scrolled {
    background: white; /* Solid white background when scrolled */
    box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
}




.hero-section {
    height: 600px;
    color: var(--white-color);
    background-image: linear-gradient(to top, #bdc2e8 0%, #bdc2e8 1%, #e6dee9 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    text-align: center;
    position: relative;
    overflow: hidden;
    border-bottom-left-radius: 30px;
    border-bottom-right-radius: 30px;
}

.overlap-image {
    width: 100%;
    max-width: 900px;
    margin: -10% auto 0;
    position: relative;
    display: block;
    overflow: hidden;
    perspective: 1000px; /* Apply perspective to the container */
    transform-style: preserve-3d; /* Needed for children to preserve 3D position */
    box-shadow: rgba(0, 0, 0, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px;
}

.overlap-image img {
    transition: transform 0.5s ease, box-shadow 0.5s ease;
    border-radius: 10px;
    width: 100%; /* Make sure the image fills the container */
    height: auto; /* Maintain aspect ratio */
}

.features-section {
    position: relative;
    z-index: 2;
    padding-top: 0;
}

.arrow-location{
position: relative;
height: 300px;
}

/* Container holding the navigation buttons */
.swiper-navigation {
    display: flex;
    justify-content: center; /* Center the buttons in the container */
    align-items: center;
    margin-top: 50px; /* Adjust based on layout */
    position: absolute; /* Keep absolute positioning */
    width: 150px; /* Auto width based on content */
  }
  
  .swiper-button-prev, .swiper-button-next {
    background-color: var(--bg-opacity-primary) !important;
    color: var(--primary-color) !important;
    padding: 30px;
    border-radius: 10px;
  }



  /* Add this to your existing CSS */

.team-member .card {
    border-radius: 15px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.team-member .card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.pricing-section .card {
    border-radius: 15px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.pricing-section .card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.pricing-section .card h2 {
    font-size: 2.5rem;
    color: var(--primary-color);
}

.pricing-section .card ul li {
    font-size: 1.1rem;
    margin-bottom: 10px;
}

.pricing-section .card ul li i {
    color: var(--primary-color);
}

.google-btn {
    background-color: #fff;
    color: #333;
    border: 2px solid #ddd;
    border-radius: 10px;
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    font-weight: 500;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
}
.google-btn:hover {
    background-color: #f8f9fa;
}
.google-icon {
    width: 24px;
    height: 24px;
    margin-right: 10px;
}

.swiper-container {
    height: 100%;
}

.swiper-slide {
    height: auto !important;
}

.card-text {
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 5;
    -webkit-box-orient: vertical;
}

.btn-primary {
            background-color: #696cff;
            border-color: #696cff;
            box-shadow: 0 0.125rem 0.25rem 0 rgba(105, 108, 255, 0.4);
            display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 1.5rem;
        }
        .btn-primary:hover {
            background-color: #6062e8;
            border-color: #6062e8;
        }

        .btn-outline-primary {
            background-color: white;
            display: flex;
    align-items: center;
    justify-content: center;
            border-color: #696cff;
            padding: 10px 15px;
            color:#696cff;
            box-shadow: 0 0.125rem 0.25rem 0 rgba(105, 108, 255, 0.4);
        }
        .btn-outline-primary:hover {
            background-color: #696cff;
            border-color: #696cff;
        }
    </style>
<link rel="icon" type="image/x-icon" href="img/Logo/Cognitive Campus Logo.png" class="favicon-icon" />

    <!-- poppins font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">


    <!-- font-awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />



    <!-- swiper cdn -->
    <link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"
/>


  </head>
  <body>



<header>
<nav class="navbar navbar-expand-lg fixed-top" style="margin-top: 10px;">
<div class="container bg-main-color p-3 rounded-3">
<a class="navbar-brand" href="#">Cognitive Campus</a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
<span class="navbar-toggler-icon"></span>
</button>
<div class="collapse navbar-collapse" id="navbarSupportedContent">
<ul class="navbar-nav mx-auto mb-2 mb-lg-0">
<li class="nav-item">
<a class="nav-link active" aria-current="page" href="#">Home</a>
</li>
<li class="nav-item">
<a class="nav-link" href="#pricing">Pricing</a>
</li>
<li class="nav-item">
<a class="nav-link" href="#testemonials">Testemonials</a>
</li>
<li class="nav-item">
<a class="nav-link" href="#our-team">Our Team</a>
</li>
</ul>
<div class="d-flex">
<a href="<?php echo $authUrl; ?>" class="btn google-btn">
    <img src="img/Icons/Google.png" alt="Google Icon" class="google-icon">
    <span>Sign in with Google</span>
</a>
<!-- Add these buttons alongside your existing Google Sign-in button -->
<a href="register.php" class="btn btn-sm btn-primary mx-2">Register</a>
<a href="login.php" class="btn btn-sm btn-outline-primary">Login</a>


</div>
</div>
</div>
</nav>
</header>
 



<section class="hero-section">
  <h1 class="text-primary-main">Welcome to Cognitive Campus <br> Where Code Meets Innovation</h1>
  <p class="py-2 text-muted">Student Productivity Enhancement System <br> using Large Language Model</p>
    <div class="text-dark">
    <a href="<?php echo $authUrl; ?>" class="btn google-btn mb-3">
    <img src="img/Icons/Google.png" alt="Google Icon" class="google-icon">
    <span>Sign in with Google</span>
</a>
    </div>

</section>





<div class="overlap-image">
  <img src="img/screenshot.png" alt="Innovative Solutions" class="img-fluid w-100">
</div>

<br><br><br><br>
<section class="pricing-section mt-5 text-center" id="pricing">

<div class="heading">
    <span class="label px-3 rounded-2 p-2">PRICING PLANS</span>
    <h2 class="mt-3 heading-title">Choose the plan that's right for you</h2>
    <p class="text-muted">We offer flexible pricing plans to suit your needs.</p>
</div>

<div class="container mt-5">
<div class="row justify-content-center">
    <div class="col-md-4 mt-3 p-0 mb-5">
        <div class="card p-4 border border-end-0 rounded-0 h-100">
            <div class="card-body">
                <h3 class="text-primary-main">Basic</h3>
                <p class="text-muted">Essential features to get started.</p>
                <h2>Free <small>/lifetime</small></h2>
                <ul class="list-unstyled my-4 text-start border-top pt-4">
                    <li><i class="fas fa-check text-primary-main me-2"></i>5 Custom Projects</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>3 Teamspace Projects</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>10 Branches Per Project</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>5 Friend Invites Per Project</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>Uni Active Subjects up to 6</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>Extra Subjects up to 6</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>Feedback</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>Course Notes</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>Project Notes</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>10 LLM Summarization Requests /Day</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>50 LLM Chat Requests Per Day</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>10 LLM Requests in Notes</li>
                </ul>
                <button class="btn btn-sm text-primary-main">Register for Free</button>
            </div>
        </div>
    </div>
    <div class="col-md-4 p-0 mb-5">
        <div class="card p-4 border border-primary rounded-0 h-100">
            <div class="card-body">
                <h3 class="text-primary-main">Standard</h3>
                <p class="text-muted">Advanced features for growing businesses.</p>
                <h2>$19.99</h2>
                <ul class="list-unstyled my-4 text-start border-top pt-4">
                    <li><i class="fas fa-check text-primary-main me-2"></i>15 Custom Projects</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>12 Teamspace Projects</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>30 Branches Per Project</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>10 Friend Invites Per Project</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>Uni Active Subjects up to 18</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>Extra Subjects up to 18</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>Feedback</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>Course Notes</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>Project Notes</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>20 LLM Summarization Requests /Day</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>100 LLM Chat Requests Per Day</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>20 LLM Requests in Notes</li>
                </ul>
                <button class="btn btn-sm text-primary-main">Buy Now</button>
            </div>
        </div>
    </div>
    <div class="col-md-4 mt-3 p-0 mb-5">
        <div class="card p-4 border border-start-0 rounded-0 h-100">
            <div class="card-body">
                <h3 class="text-primary-main">Premium</h3>
                <p class="text-muted">Comprehensive features for enterprises.</p>
                <h2>$49.99</h2>
                <ul class="list-unstyled my-4 text-start border-top pt-4">
                    <li><i class="fas fa-check text-primary-main me-2"></i>Unlimited Custom Projects</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>Unlimited Teamspace Projects</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>Unlimited Branches Per Project</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>Unlimited Friend Invites Per Project</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>Unlimited Uni Active Subjects</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>Unlimited Extra Subjects</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>Feedback</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>Course Notes</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>Project Notes</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>100 LLM Summarization Requests /Day</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>500 LLM Chat Requests Per Day</li>
                    <li><i class="fas fa-check text-primary-main me-2"></i>100 LLM Requests in Notes</li>
                </ul>
                <button class="btn btn-sm text-primary-main">Buy Now</button>
            </div>
        </div>
    </div>
</div>
</div>
</section>



<section class="testemonials-section bg-container" id="testemonials">
<div class="container py-5">
    <div class="row">
        <div class="col-md-3 arrow-location">
            <div class="heading m-0">
                <span class="label px-3 rounded-2 p-2">Customer Reviews</span>
                <h4 class="mt-3 heading-title">What people say</h4>
                <p class="text-muted mb-0">See what our customers have to say about their experience.</p>
            </div>
            <div class="swiper-navigation">
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="swiper-container" style="overflow: hidden">
                <div class="swiper-pagination"></div>
                <div class="swiper-wrapper">
                    <?php
                    $stmt = $db->prepare("SELECT * FROM feedback where on_landing = 1 ORDER BY datetime DESC");
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                            $formatted_date = date('d M Y', strtotime($row['datetime']));
                            $rating = $row['rating'];
                            $message = $row['message'];
                            $userName = $row['userName'];
                            $userEmail = $row['userEmail'];
                            $userPicture = $row['userPicture'];
                    ?>
                    <div class="swiper-slide h-100">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column">
                                <div class="flex-grow-1">
                                    <p class="card-text text-muted mb-3"><?= htmlspecialchars($message) ?></p>
                                </div>
                                <div class="mt-auto">
                                    <div class="d-flex align-items-center mb-2">
                                        <?= str_repeat('<i class="fa-solid fa-star text-warning"></i>', $rating) . str_repeat('<i class="fa-regular fa-star text-warning"></i>', 5 - $rating) ?>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <img src="<?= htmlspecialchars($userPicture) ?>" alt="User" class="img-fluid rounded-circle" style="width: 50px; height: 50px; object-fit: cover;">
                                        <div class="ms-3">
                                            <h6 class="mb-0"><?= htmlspecialchars($userName) ?></h6>
                                           
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                        endwhile;
                    endif;
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
</section>


<?php include("index-team-members.php")?>




    <!-- bootstrap script -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

    <!-- Place this at the end of your body tag -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/vanilla-tilt@1.7.0/dist/vanilla-tilt.min.js"></script>

    <!-- swiper cdn -->
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

    <!-- custom script.js -->
    <script>
        VanillaTilt.init(document.querySelector(".overlap-image"), {
    max: 8,
    speed: 200,
    glare: true,
    "max-glare": 0.5
  });
  

  document.addEventListener('DOMContentLoaded', (event) => {
    window.addEventListener('scroll', function() {
      const navbar = document.querySelector('.bg-main-color');
      // You can adjust the "50" to the height you want the effect to start changing
      navbar.classList.toggle('scrolled', window.scrollY > 50);
    });
  });

  
  var swiper = new Swiper('.swiper-container', {
    slidesPerView: 1,
    spaceBetween: 30,
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
    breakpoints: {
      640: {
        slidesPerView: 1,
        spaceBetween: 20,
      },
      768: {
        slidesPerView: 3,
        spaceBetween: 30,
      },
    },
  });
  
  
  
    </script>



  </body>
</html>
