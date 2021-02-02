<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Message Success</title>
</head>
<body>
  <script>
    alert('Message has been sent! We will contact you soon.');
    location.href="{{ $back_to }}";
  </script>
</body>
</html>
