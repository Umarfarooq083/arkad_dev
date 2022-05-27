<!doctype html>
<html lang="en">
<head>

</head>
<body>
<p><strong>Hello {{$user_data->first_name}} {{$user_data->last_name}},</strong></p>
<p>Forgot the password?</p>
<p>We&rsquo;ve received a request to reset the password for the ARKAD&rsquo;s account associated with (
    <a href="">{{$user_data->email}}</a>).</p>
<p>&nbsp;No need to worry we&rsquo;ve your password. <a href="{{$url}}">Click here</a> to reset your password.
<p><br/>Thank you!</p>
</body>
</html>
