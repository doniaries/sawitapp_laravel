<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Success Mandiri</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1a237e;
            --accent: #ffd600;
            --text-main: #2c3e50;
            --bg-gradient: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg-gradient);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-main);
            overflow: hidden;
        }

        .container {
            text-align: center;
            padding: 2rem;
            max-width: 600px;
            width: 90%;
            z-index: 10;
        }

        .logo-wrapper {
            margin-bottom: 2rem;
            position: relative;
            display: inline-block;
        }

        .logo {
            width: 150px;
            height: auto;
            filter: drop-shadow(0 10px 15px rgba(0,0,0,0.1));
            animation: float 4s ease-in-out infinite;
        }

        .logo-shadow {
            width: 80px;
            height: 10px;
            background: rgba(0,0,0,0.1);
            margin: 10px auto 0;
            border-radius: 50%;
            filter: blur(5px);
            animation: shadow 4s ease-in-out infinite;
        }

        .error-code {
            font-size: 8rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, var(--primary), #3949ab);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            opacity: 0.9;
        }

        .error-message {
            font-size: 1.5rem;
            font-weight: 400;
            margin-bottom: 2rem;
            color: #555;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(26, 35, 126, 0.3);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(26, 35, 126, 0.4);
            background: #283593;
        }

        /* Pure CSS Animated Background Elements */
        .circle {
            position: absolute;
            background: rgba(26, 35, 126, 0.03);
            border-radius: 50%;
            z-index: 1;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        @keyframes shadow {
            0%, 100% { transform: scale(1); opacity: 0.4; }
            50% { transform: scale(0.6); opacity: 0.2; }
        }

        /* Background blur circles */
        .c1 { width: 400px; height: 400px; top: -100px; left: -100px; }
        .c2 { width: 300px; height: 300px; bottom: -50px; right: -50px; }
    </style>
</head>
<body>
    <div class="circle c1"></div>
    <div class="circle c2"></div>

    <div class="container">
        <div class="logo-wrapper">
            <img src="{{ asset('images/default-logo.png') }}" alt="Success Mandiri Logo" class="logo">
            <div class="logo-shadow"></div>
        </div>

        <div class="error-code">@yield('code')</div>
        <h1 class="error-message">@yield('message')</h1>

        <a href="{{ url('/') }}" class="btn">Kembali ke Beranda</a>
    </div>
</body>
</html>
