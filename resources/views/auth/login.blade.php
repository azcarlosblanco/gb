<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gilbert_Bolona | Login</title>
    <link href="font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="{{ asset('css/all.css') }}" rel="stylesheet">
</head>

<body class="gray-bg">

    <div class="middle-box text-center loginscreen animated fadeInDown">
        <div>
            <div>
                <h1 class="logo-name">IN+</h1>
            </div>
            <h3>Welcome</h3>
            
            <p>Login in. To see it in action.</p>
            <form class="m-t" role="form" method="POST" action="/auth/login">
                {!! csrf_field() !!}

                <div class="form-group">
                    <input type="email" class="form-control" placeholder="Username" required="" name="email">
                </div>
                <div class="form-group">
                    <input type="password" class="form-control" placeholder="Password" required="" name="password">
                </div>
                <button type="submit" class="btn btn-primary block full-width m-b">Login</button>
                
                <!-- Display Validation Errors -->
                @include('commons/errors')

                <a href="#"><small>Forgot password?</small></a> 
            </form>
        </div>
    </div>

    <!-- Mainly scripts -->
    <script src="{{ asset('js/all.js') }}"></script>

</body>

</html>
