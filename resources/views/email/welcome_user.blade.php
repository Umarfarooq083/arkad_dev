<!doctype html>
<html lang="en">
<body>
<p><strong>Hi {{$first_name}} {{$last_name}}</strong></p>
<p>Welcome A new account has been created for you at AKAD&rsquo;s Rental Equipment Management system; and you have been
    issued with a username and password.</p>
<p>You can change your password anytime.</p>
<p>Your current login information is now:</p>
<p><b>Username</b>: {{$user_name}}</p>
<p><b>Password</b>: {{$password}}</p>
<p>To start using ARKAD&rsquo;s management portal kindly login at:</p>
<p><b><a href="https://erp.arkad.com.sa/ascenthome/">https://erp.arkad.com.sa/ascenthome/</a></b><br/><br/>Thank
    you!</p>
<p><b><a href="{{route('verify_token',['token'=>$token])}}">Click here to verify your email</a></b><br/><br/>Thank
    you!</p>


</body>
</html>
