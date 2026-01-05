<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        .container {
            max-width: 500px;
            margin: auto;
            background: #ffffff;
            border-radius: 10px;
            padding: 25px;
            font-family: Arial;
            border: 1px solid #e6e6e6;
        }
        .title {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 10px;
            text-align: center;
        }
        .otp-box {
            background: #f3f3f3;
            padding: 15px;
            text-align: center;
            border-radius: 8px;
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 5px;
        }
        .footer {
            font-size: 12px;
            margin-top: 18px;
            text-align: center;
            color: #555;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="title">Kode Verifikasi Reset Password</div>

    <p>Gunakan kode berikut untuk melanjutkan proses reset password kamu:</p>

    <div class="otp-box">
        {{ $otp }}
    </div>

    <p style="text-align:center;margin-top:15px;">
        Kode ini berlaku selama <b>5 menit</b>.
    </p>

    <div class="footer">
        Jika kamu tidak meminta reset password, abaikan email ini.
    </div>
</div>

</body>
</html>
