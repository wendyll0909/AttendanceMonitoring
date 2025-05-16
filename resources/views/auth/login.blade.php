<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nietes Design Builders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poetsen+One&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/NDBLogo.png') }}">
    <style>
        body {
            background-image: url('https://images.unsplash.com/photo-1504307651254-35680f356dfd?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .overlay {
            background-color: rgba(0, 0, 0, 0.6);
            height: 100%;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
        }
        .hero-content {
            max-width: 800px;
            padding: 20px;
        }
        .hero-content h1 {
            font-size: 3rem;
            font-weight: bold;
            color:rgb(255, 255, 255);
            margin-bottom: 20px;
        }
        .hero-content .tagline {
            font-size: 1.5rem;
            font-weight: 700;
            font-family: 'Poetsen One', sans-serif;
            font-style: italic;
            margin-bottom: 20px;
            color: rgb(248, 230, 230);
        }
        .hero-content p {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }
        .construction-icon {
            font-size: 4rem;
            color: #cf5c5c;
            margin-bottom: 20px;
        }
        .login-container {
            background-color: rgba(42, 45, 46, 0.9);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
            width: 100%;
            max-width: 400px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeIn 0.5s ease-out forwards;
            display: none;
        }
        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .login-container.active {
            display: block;
        }
        .login-container h2 {
            color: #ffffff;
            text-align: center;
            margin-bottom: 20px;
        }
        .form-label,
        .register-link p {
            color: white;
        }
        .alert {
            color: white;
            margin-bottom: 15px;
            padding: 12px;
            border-radius: 8px;
            font-size: 1rem;
            line-height: 1.5;
        }
        .alert-success {
            background-color:rgb(63, 128, 33); 
            border: 1px solidrgb(47, 128, 0);
            opacity: 0;
            animation: fadeInAlert 0.5s ease-out forwards;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        @keyframes fadeInAlert {
            to {
                opacity: 1;
            }
        }
        .alert-danger {
            background-color: #dc3545;
            padding: 10px;
            border-radius: 5px;
        }
        .register-link {
            text-align: center;
            margin-top: 15px;
        }
        .register-link a {
            color: #9b5555;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
        .btn-custom, .btn-primary {
            background-color: #b81d1d;
            border-color: #800000;
            color: #000;
            padding: 12px 30px;
            font-size: 1.1rem;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .btn-custom:hover, .btn-primary:hover {
            background-color: #800000;
            border-color: #800000;
        }
    </style>
</head>
<body>
    <div class="overlay">
        <div class="hero-content" id="hero-content">
            <div class="construction-icon"> 
                <img src="{{ asset('assets/img/NDBLogo.png') }}" class="img-fluid me-3" style="max-width: 120px;" alt="Company Logo">
            </div>
            <br>
            <h1>Nietes Design Builders</h1>
            <br>
            <div class="tagline">Crafting Your Vision, Brick by Brick</div>
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            <p>Building your dreams with precision and passion. Explore our expert construction and design services.</p>
            <button class="btn-custom" onclick="showLogin()">Get Started</button>
        </div>
        <div class="login-container @if ($errors->any()) active @endif" id="login-container">
            <h2>Login</h2>
            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                </div>
            @endif
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            <div class="register-link">
                <p>Don't have an account? <a href="{{ route('register') }}">Register here</a></p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showLogin() {
            document.getElementById('hero-content').style.display = 'none';
            document.getElementById('login-container').classList.add('active');
        }

        // Check for errors on page load and keep login container visible if errors exist
        window.onload = function() {
            @if ($errors->any())
                document.getElementById('hero-content').style.display = 'none';
                document.getElementById('login-container').classList.add('active');
            @endif
        };
    </script>
</body>
</html>