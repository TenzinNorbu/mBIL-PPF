<!DOCTYPE html>
<html>
<head>
<title>Password Change Notification!</title>
</head>
<body>
<p style="text-align: justify">
    Dear <strong>{{$user['name']}}</strong>,<br>
    Your password for the <strong>BIL PF/GF system</strong> of your account associated with <strong>{{$user['email']}}</strong> 
    will expire in 5 days<strong>&nbsp;({{ now()->addDays(5)->format('Y-m-d')}})</strong>.
    <p>To change your password,click on <a class="btn btn-primary" href="{{url('/ppfsystem/login')}}"><strong>Change Password.</strong></a>
    Please make sure to change your password before then, or later login to system will encounter errors.</p>
    <br>
</p>
<p>
    Thank you,<br>
    Bhutan Insurance Limited,<br>
    Providing Security, Building Confidence.
</p>
</body>
</html>