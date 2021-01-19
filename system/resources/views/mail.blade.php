<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Send E-mail</title>
</head>
<body>
  <script>
    alert('{{ $message }}');
    window.location.href="{!! $_SERVER['HTTP_REFERER'] !!}";
  </script>
</body>
</html>
