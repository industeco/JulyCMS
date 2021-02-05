<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Message Validation Failed</title>
  <style>
    body {font-size: 14px; color: #212121;}
    .message-validation-result {
      min-width: 480px;
      max-width: 720px;
      width: 80%;
      margin: 40px auto;
      padding: 40px;
      box-sizing: border-box;
      border-radius: 2px;
      background-color: #ffffff;
      border: 1px dashed #333;
    }
    h1 {
      margin-top: 0;
      color: orangered;
      text-align: center;
    }
    ul,ol {padding-left: 20px;}
    code {padding:3px .5em; background-color:#f1f1f1; border-radius:2px;}
    blockquote {
      margin-left: 0;
      margin-right: 0;
      padding: 10px 20px;
      border-radius: 2px;
      color: #515151;
      background-color: #f1f1f1;
      font-size: 13px;
      word-wrap: break-word;
      word-break: break-all;
    }
    .errors {color: orangered;}
    #btn_back {
      display: block;
      width: 100px;
      height: 32px;
      margin: 40px auto 0;
      line-height: 33px;
      padding: 0 15px;
      box-sizing: border-box;
      border: none;
      border-radius: 2px;
      color: #ffffff;
      background-color: #1867c0;
      cursor: pointer;
      box-shadow: 0 3px 1px -2px rgba(0,0,0,.2), 0 2px 2px 0 rgba(0,0,0,.14), 0 1px 5px 0 rgba(0,0,0,.12);
    }
  </style>
</head>
<body>
  <div class="message-validation-result">
    <h1>Message Validation Failed</h1>
    <ol>
      @foreach ($errors as $name => $messages)
      <li>
        <p><b>{{ $fields[$name] ?? $name }}:</b></p>
        <ul class="errors">
          @foreach ($messages as $message)
          <li>{{ $message }}</li>
          @endforeach
        </ul>
      </li>
      @endforeach
    </ol>
    <button id="btn_back">BACK</button>
  </div>
  <script>
    document.getElementById('btn_back').onclick = function() {
      history.go(-1);
    };
  </script>
</body>
</html>
