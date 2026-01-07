<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Failure Animation</title>
  <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,400i,700,900&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>
  <style>
    /* Reset styles */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    /* Body styles */
    body {
      text-align: center;
      padding: 20px;
      background: linear-gradient(135deg, #fbc2eb, #a6c1ee); /* Gradient background */
      font-family: "Nunito Sans", "Helvetica Neue", sans-serif;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    /* Card styles */
    .card {
      background: #fff;
      padding: 40px 30px;
      border-radius: 15px;
      box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 450px;
      text-align: center;
      transition: all 0.3s ease;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    }

    /* Heading styles */
    h1 {
      color: #e53935; /* Red for error */
      font-weight: 900;
      font-size: 36px;
      margin-bottom: 10px;
    }

    /* Paragraph styles */
    p {
      color: #555;
      font-size: 16px;
      line-height: 1.6;
      margin-bottom: 20px;
    }

    /* Countdown styles */
    .countdown {
      font-size: 18px;
      font-weight: bold;
      color: #d84315; /* Deep orange color */
    }

    /* Lottie animation container */
    .animation-container {
      margin-bottom: 20px;
    }

    dotlottie-player {
      width: 100%;
      max-width: 400px;
      height: auto;
      margin: 0 auto;
    }

    /* Responsive Design */
    @media (max-width: 600px) {
      .card {
        padding: 20px;
        max-width: 90%;
      }

      h1 {
        font-size: 28px;
      }

      p {
        font-size: 14px;
      }

      dotlottie-player {
        max-width: 300px;
      }
    }

    @media (max-width: 400px) {
      h1 {
        font-size: 24px;
      }
      p {
        font-size: 13px;
      }
    }
  </style>
  <script>
    let countdown = 3;
    function updateCountdown() {
      if (countdown > 0) {
        document.getElementById("countdown").innerText = countdown;
        countdown--;
        setTimeout(updateCountdown, 1000);
      } else {
        window.location.href = "https://pasawin.com/"; // Redirect after countdown
      }
    }
    window.onload = updateCountdown;
  </script>
</head>
<body>
  <div class="card">
    <div class="animation-container">
      <dotlottie-player 
        src="https://lottie.host/fd8cda41-62c2-4eb1-a3b8-74ee1420c609/sN5S2fRBYW.lottie" 
        background="transparent" 
        speed="1" 
        loop 
        autoplay>
      </dotlottie-player>
    </div>

    <h1>Failed</h1>
    <p>Oops! Something went wrong with your transaction.<br>Please try again later.</p>

    <p class="countdown">Redirecting to home in <span id="countdown">3</span> seconds...</p>
  </div>
</body>
</html>
